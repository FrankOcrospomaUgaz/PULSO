<?php

namespace App\Http\Controllers;

use App\Exports\MovreservaReportExport;
use App\Http\Requests\MovreservaRequest;
use App\Models\Banco;
use App\Models\Billeteradigital;
use App\Models\Caja;
use App\Models\DetMovcaja;
use App\Models\DetMovventum;
use App\Models\Habitacion;
use App\Models\HistorialHabitacion;
use App\Models\Huesped;
use App\Models\Igv;
use App\Models\Mediopago;
use App\Models\Motivoreserva;
use App\Models\Movcaja;
use App\Models\Movhabitacion;
use App\Models\MovhabitacionHabitacion;
use App\Services\HabitacionAvailability;
use App\Models\Movimiento;
use App\Models\Movreserva;
use App\Models\MovreservaHabitacion;
use App\Models\Movventum;
use App\Models\Operacione;
use App\Models\Origenreserva;
use App\Models\Persona;
use App\Models\Personamaestro;
use App\Models\Piso;
use App\Models\Producto;
use App\Models\RelParametrosucursal;
use App\Models\RelTurnocaja;
use App\Models\Sucursal;
use App\Models\Tarjetum;
use App\Models\Tipodocumento;
use App\Models\Turno;
use App\Models\Ubigeo;
use App\Models\Unidad;
use Barryvdh\Snappy\Facades\SnappyPdf as Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\CorrelativoService;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Class MovReservaController
 */
class MovReservaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search'); // Obtener el término de búsqueda
        $estadoSeleccionado = $request->input('estado', 'EN ATENCIÓN'); // Estado por defecto: "EN ATENCIÓN"

        $query = Movreserva::where('sucursal_id', Auth::user()->persona->sucursal_id); // Iniciar la consulta base
        $perPage = $request->input('perPage', 10);
        // Obtener las columnas de la tabla 'movreserva'
        $columns = Schema::getColumnListing('movreserva');

        // Filtrar solo las columnas de tipo texto
        $textColumns = [];
        foreach ($columns as $column) {
            $columnType = DB::select("
                SELECT data_type
                FROM information_schema.columns
                WHERE table_name = 'movreserva' AND column_name = ?
            ", [$column]);

            if (isset($columnType[0]) && in_array($columnType[0]->data_type, ['character varying', 'text'])) {
                $textColumns[] = $column;
            }
        }

        if ($search) {
            $query->where(function ($q) use ($textColumns, $search) {
                foreach ($textColumns as $column) {
                    $q->orWhereRaw("LOWER($column) LIKE ?", ['%'.strtolower($search).'%']);
                }
            });

            // Búsqueda en la tabla movimiento
            $query->orWhereHas('movimiento', function ($q) use ($search) {
                $q->whereRaw('LOWER(numero) LIKE ?', ['%'.strtolower($search).'%'])
                    ->orWhereRaw('LOWER(comentario) LIKE ?', ['%'.strtolower($search).'%']);
            });

            // Búsqueda en persona y personamaestro a través de movimiento
            $query->orWhereHas('movimiento.persona.personamaestro', function ($q) use ($search) {
                $q->whereRaw('LOWER(nrodoc) LIKE ?', ['%'.strtolower($search).'%']);
            });
        }

        // Aplicar filtros de estado y habitación si existen en la solicitud
        if ($estadoSeleccionado !== '') {
            $query->where('estado', $estadoSeleccionado);
        }

        if ($request->has('habitacion') && $request->habitacion !== '') {
            $query->where('habitacionprincipal_id', $request->habitacion);
        }

        // $reservas = $query->orderBy('fechainicio', 'desc')->paginate(10);
        $reservas = $query->orderBy('id', 'desc')->paginate($perPage);

        // Obtener las operaciones para la vista
        $operaciones = Operacione::where('tabla_id', 77)->where('estado', true)->get();

        // Obtener habitaciones para el filtro
        $habitaciones = Habitacion::where('sucursal_id', Auth::user()->persona->sucursal_id)->get();

        // Definir estados posibles para el filtro
        $estados = ['TODAS', 'PENDIENTE', 'EN ATENCIÓN', 'FINALIZADO', 'ANULADO'];

        return view('movreserva.index', compact('reservas', 'operaciones', 'habitaciones', 'estados', 'estadoSeleccionado'))
            ->with('i', (request()->input('page', 1) - 1) * $reservas->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);
        $sucursal_id = $sucursal->id;

        // Obtener la fecha seleccionada o la actual si no se envía ninguna
        $fechaSeleccionada = $request->query('fecha', now()->format('Y-m-d'));
        $habitacionId = $request->query('habitacion', null); // ID de la habitación opcional
        $habitacionSeleccionada = null;
        if ($habitacionId !== null) {
            $habitacionSeleccionada = Habitacion::find($habitacionId);
        }
        // dd($habitacionId);
        // Obtener habitaciones de la sucursal
        // Obtener el valor de la hora predeterminada desde RelParametroSucursal
        $horaEntradaJson = RelParametroSucursal::where('sucursal_id', $sucursal->id)
            ->where('parametro_id', 7)
            ->value('valor');

        // Reemplazar corchetes por llaves y luego decodificar el JSON
        $horaEntrada = '12:00'; // Valor por defecto en caso de error
        if ($horaEntradaJson) {
            // Intentar arreglar el formato incorrecto del JSON
            $horaEntradaJson = str_replace(['[', ']'], ['{', '}'], $horaEntradaJson);

            // Ahora decodificar el JSON
            $decoded = json_decode($horaEntradaJson, true);
            $horaEntrada = $decoded['hora_entrada'] ?? $horaEntrada;
        }
        // dd($horaEntrada);
        // Obtener productos o servicios disponibles
        $productos = Producto::with(['relProductosucursal', 'relListaUnidadSucursal'])
            ->whereHas('relProductosucursal', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id);
            })
            ->get();

        // Formatear los productos para el autocomplete
        $motivos_reserva = Motivoreserva::where('situacion', true)->orderBy('orden')->get();
        $origenreserva = Origenreserva::where('situacion', true)->get();

        // Obtener todas las personas de la sucursal
        $personas = Persona::where('sucursal_id', $sucursal_id)->get();

        // Formatear datos para autocompletar personas
        // Obtener responsables disponibles
        $responsables = Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 1);
        })->where('sucursal_id', $sucursal_id)->get();

        // Obtener tipo de cambio actual
        $tipoCambio = RelParametrosucursal::where('sucursal_id', $sucursal_id)
            ->where('parametro_id', 10) // ID del parámetro para tipo de cambio
            ->value('valor') ?? 3.676; // Valor predeterminado si no hay registro
        $pisos = Piso::where('sucursal_id', $persona->sucursal_id)->orderBy('id')->get();
        $habitaciones = Habitacion::with(['tipohabitacion', 'sucursal', 'piso'])
            ->where('sucursal_id', $persona->sucursal_id)
            ->whereIn('situacion', ['Disponible', 'Limpieza'])
            ->orderBy('numero')
            ->get();
        $acompanantesIds = [];

        $principalId = session('principal');
        // Pasar los acompañantes a la vista
        $personaCreada = $principalId ? Persona::find($principalId) : null;
        $mediospago = Mediopago::where('situacion', true)->get();
        $paises = Ubigeo::where('tipo', '4')->where('ubigeo_id', 151)->get();

        // Convertir el array a JSON
        // $detallesMovimientosJson = json_encode($detallesMovimientosJson);
        return view('movreserva.create', compact(
            'habitaciones',
            'sucursal_id',
            'mediospago',
            'responsables',
            'tipoCambio',
            'motivos_reserva',
            'pisos',
            'origenreserva',
            'personaCreada',
            'habitaciones',
            'sucursal',
            'acompanantesIds',
            'horaEntrada',
            'habitacionSeleccionada',
            'fechaSeleccionada' // Enviar la fecha seleccionada a la vista
        ));
    }

    public function atender(Request $request, $id)
    {
        $habitacion_id = $request->input('habitacion_id');

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);
        $sucursal_id = $sucursal->id;

        // Obtener la reserva a atender
        $movreserva = Movreserva::with(['movimiento.persona', 'habitacion', 'movimiento.responsable'])
            ->findOrFail($id);
        $mediospago = Mediopago::where('situacion', true)->get();
        $movreserva_habitacion = MovreservaHabitacion::where('habitacion_id', $habitacion_id)->where('movreserva_id', $movreserva->id)->first();
        // Obtener la fecha seleccionada o la de la reserva si no se envía ninguna
        $fechaSeleccionada = $request->query('fecha', $movreserva->fecha_vencimiento ?? now()->format('Y-m-d'));

        // Obtener habitaciones de la sucursal
        $habitaciones = Habitacion::with(['tipohabitacion', 'sucursal', 'piso'])
            ->where('sucursal_id', $persona->sucursal_id)
            ->whereIn('situacion', ['Disponible', 'Limpieza'])
            ->orderBy('numero')
            ->get();

        // Obtener productos o servicios disponibles
        $productos = Producto::with(['relProductosucursal', 'relListaUnidadSucursal'])
            ->whereHas('relProductosucursal', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id);
            })
            ->get();

        // Formatear los productos para el autocomplete
        $motivos_reserva = Motivoreserva::where('situacion', true)->orderBy('orden')->get();
        $origenreserva = Origenreserva::where('situacion', true)->get();

        // Obtener todas las personas de la sucursal
        $personas = Persona::where('sucursal_id', $sucursal_id)->get();

        // Formatear datos para autocompletar personas
        // Obtener responsables disponibles
        $responsables = Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 1); // Asumiendo que 1 es el rol de responsable
        })->where('sucursal_id', $sucursal_id)->get();

        // Obtener tipo de cambio actual
        $tipoCambio = RelParametrosucursal::where('sucursal_id', $sucursal_id)
            ->where('parametro_id', 10) // ID del parámetro para tipo de cambio
            ->value('valor') ?? 3.676; // Valor predeterminado si no hay registro

        $pisos = Piso::where('sucursal_id', $persona->sucursal_id)->orderBy('id')->get();
        // Obtener las habitaciones asignadas a la reserva
        $habitacionesAsignadas = collect(json_decode($movreserva->json_habitaciones, true))->pluck('id')->toArray();
        $acompanantesIds = json_decode($movreserva->json_acompañantes ?? '[]', true);
        $acompanantesIds = is_array($acompanantesIds) ? collect($acompanantesIds)->flatten()->toArray() : [];

        // Si aún es un string, decodificarlo nuevamente
        if (is_string($acompanantesIds)) {
            $acompanantesIds = json_decode($acompanantesIds, true);
        }
        $horaEntradaJson = RelParametroSucursal::where('sucursal_id', $sucursal->id)
            ->where('parametro_id', 7)
            ->value('valor');

        // Reemplazar corchetes por llaves y luego decodificar el JSON
        $horaEntrada = '12:00'; // Valor por defecto en caso de error
        if ($horaEntradaJson) {
            // Intentar arreglar el formato incorrecto del JSON
            $horaEntradaJson = str_replace(['[', ']'], ['{', '}'], $horaEntradaJson);

            // Ahora decodificar el JSON
            $decoded = json_decode($horaEntradaJson, true);
            $horaEntrada = $decoded['hora_entrada'] ?? $horaEntrada;
        }
        // Ahora sí hacemos la consulta
        $acompanantesIds = Huesped::where('movreserva_id', $movreserva->id)
            ->where('habitacion_id', '!=', null)
            ->pluck('persona_id')
            ->toArray();

        $acompanantes = Huesped::where('movreserva_id', $movreserva->id)
            ->whereNotNull('habitacion_id')
            ->get()
            ->map(function ($huesped) {
                return [
                    'id' => $huesped->persona_id,
                    'habitacion_id' => $huesped->habitacion_id,
                ];
            })
            ->values()
            ->toArray();

        $habitacionId = $request->query('habitacion', null); // ID de la habitación opcional
        $habitacionSeleccionada = null;
        if ($habitacionId !== null) {
            $habitacionSeleccionada = Habitacion::find($habitacionId);
        }
        $atender = true;

        // dd($acompanantes);
        // dd(json_decode($movreserva->json_acompañantes ?? '[]', true));
        // $huespedes = \App\Models\Persona::whereIn('id', $acompanantesIds)->get();
        return view('movreserva.atender', compact(
            'movreserva',
            'habitaciones',
            'sucursal_id',
            'responsables',
            'tipoCambio',
            'motivos_reserva',
            'pisos',
            'origenreserva',
            'sucursal',
            'fechaSeleccionada',
            'habitacionesAsignadas',
            'acompanantesIds',
            'acompanantes',
            'horaEntrada',
            'habitacionSeleccionada',
            'mediospago',
            'atender',
            'habitacion_id',
            'movreserva_habitacion'
            //    'huespedes'
        ));
    }

    public function atenderstore(Request $request)
    {
        // Validar los datos entrantes
        $request->validate([
            'movreserva_id' => 'required|exists:movreserva,id',
        ]);

        DB::beginTransaction();

        try {
            $jsonHabitaciones = json_decode($request->detalles_habitaciones_json, true);
            $habitacionPrincipal = count($jsonHabitaciones) > 0 ? $jsonHabitaciones[0]['id'] : null;
            $movReserva = Movreserva::findOrFail($request->movreserva_id);

            // Obtener la fecha actual
            // Obtener la fecha actual
            $fechaHoy = now()->toDateString();
            $fechaVencimiento = $request->fechavencimiento;

            // 📌 Validar que la fecha de vencimiento no sea mayor a hoy
            if ($fechaVencimiento > $fechaHoy) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'errors' => 'No se puede atender la reserva porque la fecha de check-in no puede ser en el futuro.',
                    'fecha_vencimiento' => $fechaVencimiento,
                    'fecha_actual' => $fechaHoy,
                    'redirect_url' => route('calendarioreservas.index'),
                ], 422);
            }
            // Validar si la habitación está ocupada
            $habitacionCheck = Habitacion::find($request->habitacion_id);

            if ($habitacionCheck && $habitacionCheck->situacion === 'Ocupada') {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'errors' => 'No se puede realizar el check-in. La habitación aún se encuentra ocupada.',
                    'redirect_url' => route('calendarioreservas.index'),
                ], 422);
            }

            // Actualizar todos los datos de la reserva antes de atenderla
            $movReserva->update([
                'habitacionprincipal_id' => $habitacionPrincipal,
                'habitacion_principal' => Habitacion::find($habitacionPrincipal)->numero,
                'json_habitaciones' => json_encode($jsonHabitaciones),
                'estado' => 'EN ATENCIÓN',
                'motivo_id' => $request->motivo_id,
                'origen_id' => $request->origen_id,
                'celular' => $request->celular,
                'email' => $request->email,
                'nropersonas' => $request->nropersonas,
                'observacion' => $request->comentario,
                'preciohabitacion' => $request->tarifa_habitacion[0],
                'json_acompañantes' => json_encode(
                    collect($request->acompanantes)->pluck('id'),
                    JSON_UNESCAPED_UNICODE
                ),
                'fechainicio' => now(),
            ]);

            $detalleHabitacion = MovreservaHabitacion::where('movreserva_id', $movReserva->id)
                ->where('habitacion_id', $request->habitacion_id)
                ->first();

            if ($detalleHabitacion) {
                $index = collect($request->tarifa_habitacion)->keys()->search($detalleHabitacion->habitacion_id);
                $tarifa = is_numeric($index)
                    ? $request->tarifa_habitacion[$index]
                    : $detalleHabitacion->tarifa ?? 0;

                $detalleHabitacion->update([
                    'situacion' => 'EN ATENCIÓN',
                    'fechacheckin' => now(),
                    'fechacheckout' => null,
                    'early_checkin' => $request->early ?? 0.00,
                    'late_checkout' => $request->late ?? 0.00,
                    'dias' => $request->dias ?? 1,
                    'tarifa' => $tarifa,
                    // 'total' => $tarifa * ($request->dias ?? 1),
                ]);
            }

            $habitacion = Habitacion::find($movReserva->habitacionprincipal_id);

            // Crear el registro de movhabitacion
            // Verificar si ya existe un movhabitacion para esta reserva
            $movhabitacion = Movhabitacion::where('movreserva_id', $movReserva->id)->first();
            $situacion = $movReserva->adelanto >= $movReserva->total ? 'P' : 'A';

            if (! $movhabitacion) {
                $movimientoHab = Movimiento::create([
                    'numero' => $this->generateNumeroMovHabitacion(Auth::user()->persona->sucursal_id),
                    'fecha' => now(),
                    'usuario_id' => Auth::id(),
                    'usuario' => Auth::user()->name,
                    'persona_id' => $request->persona_id,
                    'persona' => Persona::find($request->persona_id)->nombres.' '.Persona::find($request->persona_id)->apellidos,
                    'responsable_id' => Auth::user()->persona_id,
                    'responsable' => Auth::user()->persona->nombres.' '.Auth::user()->persona->apellidos,
                    'comentario' => $request->comentario ?? '',
                    'situacion' => 'A',
                    'movimiento_id' => $movReserva->movimiento_id,
                    'tipomovimiento_id' => 8,
                    'tipodocumento_id' => 11,
                    'sucursal_id' => Auth::user()->persona->sucursal_id,
                ]);

                // Si no existe, lo creamos
                $movhabitacion = Movhabitacion::create([
                    'descuento' => 0,
                    'fechaingreso' => $movReserva->fechainicio,
                    'fechasalida' => $movReserva->fechainicio->copy()->addDays($movReserva->dias),
                    'dias' => $movReserva->dias,
                    'horas' => null,
                    'modalidad' => 'D',
                    'total' => $movReserva->total,
                    'preciohabitacion' => $movReserva->total / max($movReserva->dias, 1),
                    'situacion' => $situacion,
                    'comentario' => $movReserva->observacion,
                    'moneda' => 'PEN',
                    'modo' => $movReserva->modo,
                    'habitacion_id' => $movReserva->habitacionprincipal_id,
                    'movimiento_id' => $movimientoHab->id,
                    'sucursal_id' => Auth::user()->persona->sucursal_id,
                    'movreserva_id' => $movReserva->id,
                ]);

                // Obtener todas las ventas asociadas a este movreserva_id
                $movventas = Movventum::where('movreserva_id', $movReserva->id)->get();

                // Actualizar el campo movhabitacion_id de cada venta
                foreach ($movventas as $movventa) {
                    $movventa->update(['movhabitacion_id' => $movhabitacion->id]);
                }
            }

            // Obtener las habitaciones asociadas a la reserva
            $detalleshabitaciones = MovreservaHabitacion::where('movreserva_id', $movReserva->id)->get();

            // Obtener el movhabitacion relacionado con la reserva
            $movHabitacion = Movhabitacion::where('movreserva_id', $movReserva->id)->first();

            if ($movHabitacion) {
                $habitacionAtendida = collect($detalleshabitaciones)
                    ->firstWhere('habitacion_id', $request->habitacion_id);

                if ($habitacionAtendida) {
                    MovhabitacionHabitacion::create([
                        'movhabitacion_id' => $movHabitacion->id,
                        'habitacion_id' => $habitacionAtendida->habitacion_id,
                        'principal' => $habitacionAtendida->principal,
                        'tarifa' => $habitacionAtendida->tarifa,
                        'fechacheckin' => now(),
                        'fechacheckout' => null,
                        'dias' => $habitacionAtendida->dias ?? 1,
                        'horas' => null,
                        'total' => $detalleHabitacion->total,
                        'modalidad' => 'D',
                        'situacion' => $situacion,
                        'json_habitacion' => $habitacionAtendida->json_habitacion,
                        'early_checkin' => $request->early ?? 0.00,
                        'late_checkout' => $request->late ?? 0.00,
                    ]);
                }
            }

            // Obtener el ID de la caja activa
            $cajaId = Caja::where('situacion', 'A')
                ->where('sucursal_id', Auth::user()->persona->sucursal_id)
                ->first()
                ->id;

            // Obtener el último turno activo
            $ultimoTurno = RelTurnocaja::where('situacion', 'A')
                ->whereHas('movcajainicio', function ($query) use ($cajaId) {
                    $query->where('caja_id', $cajaId);
                })
                ->latest('fechainicio')
                ->first();

            // Crear historial de la habitación
            HistorialHabitacion::create([
                'sucursal_id' => Auth::user()->persona->sucursal_id,
                'movhabitacion_id' => $movhabitacion->id,
                'relturnocaja_id' => $ultimoTurno->id,
                'situacion_pago' => 'N',
                'situacion_checkout' => 'N',
            ]);
            $acompanantes = is_string($request->acompanantes)
                ? json_decode($request->acompanantes, true)
                : $request->acompanantes;

            if (! empty($acompanantes) && is_array($acompanantes)) {
                foreach ($acompanantes as $acompanante) {
                    if (is_array($acompanante) && isset($acompanante['id']) && isset($acompanante['habitacion_id'])) {
                        Huesped::create([
                            'persona_id' => $acompanante['id'],
                            'movhabitacion_id' => $movhabitacion->id,
                            'habitacion_id' => $acompanante['habitacion_id'],
                        ]);
                    }
                }
            } else {
                Huesped::create([
                    'persona_id' => $request->persona_id,
                    'movhabitacion_id' => $movhabitacion->id,
                    'habitacion_id' => $request->habitacion_id,
                ]);
            }

            // Recorrer todas las habitaciones asociadas a la reserva y marcarlas como "Ocupada"
            foreach ($detalleshabitaciones as $detalle) {
                $habitacion = Habitacion::find($request->habitacion_id);

                if ($habitacion) {
                    $habitacion->situacion = 'Ocupada';
                    $habitacion->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reserva actualizada con éxito y cambiada a EN ATENCIÓN.',
                'redirect_url' => route('calendarioreservas.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al actualizar la reserva.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function anular($id)
    {
        DB::beginTransaction();

        try {
            // Obtener la venta y su movimiento
            $MOVRESERVA = Movreserva::findOrFail($id);
            $movimiento = Movimiento::find($MOVRESERVA->movimiento_id);

            // Marcar el movimiento como anulado
            $movimiento->update(['situacion' => 'D']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento de reserva anulado correctamente.',
                'redirect_url' => route('calendarioreservas.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MovreservaRequest $request)
    {
        try {
            DB::beginTransaction();

            // Obtener datos generales
            $usuario = Auth::user();
            $responsable = Persona::find($request->responsable_id);
            $personausuario = $request->persona_id ? Persona::find($usuario->persona_id) : null;
            $sucursal = Sucursal::find($personausuario->sucursal_id);
            $tipodocumento = Tipodocumento::find(9);
            $persona = $request->persona_id ? Persona::find($request->persona_id) : null;
            // Obtener JSON de habitaciones seleccionadas
            $jsonHabitaciones = json_decode($request->detalles_habitaciones_json, true);
            $habitacionPrincipal = count($jsonHabitaciones) > 0 ? $jsonHabitaciones[0]['id'] : null;

            // Calcular rango de fechas de la nueva reserva
            $nuevaFechaInicio = new Carbon($request->fechavencimiento);
            $nuevaFechaFin = (clone $nuevaFechaInicio)->addDays($request->dias);
            $modoReserva = $request->switch_state ?? 'unico';

            // Verificar cruces de reservas
            foreach ($jsonHabitaciones as $habitacion) {
                $habitacionId = $habitacion['id'];

                // Buscar reservas candidatas para la misma habitación que no estén anuladas
                $reservasCandidatas = Movreserva::where(function ($q) use ($habitacionId) {
                    $q->where('habitacionprincipal_id', $habitacionId)
                        ->orWhereHas('movreserva_habitacion', function ($qh) use ($habitacionId) {
                            $qh->where('habitacion_id', $habitacionId);
                        });
                })
                    ->whereHas('movimiento', function ($query) {
                        $query->where('situacion', 'A');
                    })
                    ->where('estado', '!=', 'ANULADO')
                    ->with(['movreserva_habitacion' => function ($q) use ($habitacionId) {
                        $q->where('habitacion_id', $habitacionId);
                    }])
                    ->get();

                foreach ($reservasCandidatas as $reservaConflicto) {
                    $detalleHab = $reservaConflicto->movreserva_habitacion->first();

                    // Verificar si la reserva o el detalle de la habitación está finalizado
                    $esFinalizado = ($reservaConflicto->estado === 'FINALIZADO') || ($detalleHab && $detalleHab->situacion === 'FINALIZADO');

                    $inicioRaw = $detalleHab?->fechacheckin ?? $reservaConflicto->fechainicio ?? $reservaConflicto->fechavencimiento;
                    if (!$inicioRaw) {
                        continue;
                    }
                    $inicioReserva = Carbon::parse($inicioRaw)->startOfDay();

                    if ($esFinalizado) {
                        // Para reservas finalizadas, la fecha de fin es la fecha de salida real (fechacheckout o fechasalida)
                        $finRaw = $detalleHab?->fechacheckout ?? $reservaConflicto->fechasalida;
                        if ($finRaw) {
                            $fechaFinConflicto = Carbon::parse($finRaw);
                        } else {
                            $dias = (int) ($detalleHab?->dias ?? $reservaConflicto->dias ?? 1);
                            $fechaFinConflicto = (new Carbon($inicioRaw))->addDays(max($dias, 1));
                        }
                    } else {
                        // Para reservas no finalizadas, la fecha de fin es la salida programada
                        $dias = (int) ($detalleHab?->dias ?? $reservaConflicto->dias ?? 1);
                        $finProgramado = (new Carbon($reservaConflicto->fechavencimiento))->addDays(max($dias, 1));
                        $finRaw = $reservaConflicto->fechasalida ?? $detalleHab?->fechacheckout;
                        $fechaFinConflicto = $finRaw ? Carbon::parse($finRaw) : $finProgramado;
                    }

                    // Si la nueva reserva inicia justo cuando termina la anterior (misma fecha o posterior), no hay conflicto
                    if ($nuevaFechaInicio->gte($fechaFinConflicto) || $nuevaFechaInicio->isSameDay($fechaFinConflicto)) {
                        continue;
                    }

                    // Si la nueva reserva termina antes o el mismo día que inicia la anterior, no hay conflicto
                    if ($nuevaFechaFin->lte($inicioReserva) || $nuevaFechaFin->isSameDay($inicioReserva)) {
                        continue;
                    }

                    // Si hay conflicto real, cancelamos y retornamos error
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'redirect_url' => route('calendarioreservas.index'),
                        'errors' => 'Conflicto de fechas: La habitación ya tiene una reserva en ese rango de fechas.',
                        'conflicto' => [
                            'habitacion_id' => $habitacionId,
                            'movreserva_id' => $reservaConflicto->id,
                            'fecha_vencimiento' => $reservaConflicto->fechavencimiento,
                            'dias' => $reservaConflicto->dias,
                        ],
                    ], 422);
                }
            }

            // Crear Movimiento
            $movimiento = Movimiento::create([
                'numero' => $this->generateNumeroMovReserva($sucursal->id),
                'fecha' => now(),
                'usuario_id' => $usuario->id,
                'usuario' => $usuario->name,
                'responsable_id' => $responsable->id,
                'responsable' => "{$responsable->nombres} {$responsable->apellidos}",
                'persona_id' => $request->persona_id,
                'persona' => $persona ? "{$persona->nombres} {$persona->apellidos}" : 'N/A',
                'comentario' => $request->comentario ?? '',
                'situacion' => 'A',
                'tipomovimiento_id' => $tipodocumento->tipomovimiento_id,
                'tipodocumento_id' => $tipodocumento->id,
                'sucursal_id' => $sucursal->id,
            ]);

            // Crear MovReserva
            $movReserva = Movreserva::create([
                'movimiento_id' => $movimiento->id,
                'fechainicio' => null,
                'fechavencimiento' => $request->fechavencimiento.' '.($request->horavencimiento ?? '00:00:00'),
                'fechasalida' => null,
                'fechacancelacion' => null,
                'dias' => $request->dias,
                'moneda' => 'PEN',
                'tipocambio' => $request->tipocambio ?? 3.71,
                'total' => $request->total,
                'adelanto' => $request->adelanto,
                'habitacionprincipal_id' => $habitacionPrincipal,
                'habitacion_principal' => Habitacion::find($habitacionPrincipal)->numero,
                'json_habitaciones' => json_encode($jsonHabitaciones),
                'estado' => 'PENDIENTE',
                'motivo_id' => $request->motivo_id,
                'origen_id' => $request->origen_id,
                'mediopagoadelanto_id' => $request->medio_pago,
                'sucursal_id' => $sucursal->id,
                'celular' => $request->celular,
                'email' => $request->email,
                'nropersonas' => $request->nropersonas,
                'observacion' => $request->comentario,
                'preciohabitacion' => $modoReserva === 'unico'
                    ? $request->tarifa_habitacion_unico
                    : $request->tarifa_habitacion[0],
                'modo' => ($modoReserva === 'unico') ? 'U' : 'G',
                'json_acompañantes' => json_encode($request->acompanantes, JSON_UNESCAPED_UNICODE),
            ]);

            $caja = Caja::where('sucursal_id', $sucursal->id)->first();
            $turnoCaja = RelTurnocaja::conCajaActiva($caja->id)->first();
            $turnoId = $turnoCaja ? $turnoCaja->movcajainicio->turno_id : $request->turno_id;

            if ($request->adelanto > 0) {
                $igvPredeterminado = RelParametrosucursal::where('sucursal_id', $sucursal->id)
                    ->where('parametro_id', 8) // ID del parámetro para IGV
                    ->value('valor'); // Tomamos directamente el campo 'valor'
                $igvSeleccionado = $request->input('igv_id') == 0 ? $igvPredeterminado : $request->input('igv_id');
                $igvObjeto = Igv::find($igvSeleccionado);
                $movimientoVenta = Movimiento::create([
                    'numero' => $this->generateNumero(9),
                    'fecha' => now(),
                    'usuario_id' => $usuario->id,
                    'usuario' => $usuario->name,
                    'responsable_id' => $responsable->id,
                    'responsable' => "{$responsable->nombres} {$responsable->apellidos}",
                    'persona_id' => $request->persona_id,
                    'persona' => $persona ? "{$persona->nombres} {$persona->apellidos}" : 'N/A',
                    'comentario' => $request->notas ?? '',
                    'situacion' => 'A',
                    'movimiento_id' => $movimiento->id,
                    'tipomovimiento_id' => 2,
                    'tipodocumento_id' => 9,
                    'sucursal_id' => $request->sucursal_id,
                ]);
                // Si no existe, creamos un nuevo registro Movventa
                $movventa = Movventum::create([
                    'fecha' => now(),
                    'usuario_id' => $usuario->id,
                    'usuario' => $usuario->name,
                    'responsable_id' => $responsable->id,
                    'responsable' => "{$responsable->nombres} {$responsable->apellidos}",
                    'persona_id' => $usuario->persona_id,
                    'persona' => $usuario->persona ? "{$usuario->persona->nombres} {$usuario->persona->apellidos}" : 'N/A',
                    'comentario' => $request->comentario ?? '',
                    'serie' => $tipodocumento->abreviatura.''.$caja->serie,
                    'anio' => now()->year,
                    'tipo_detalle' => 'DETALLADO',
                    'tipo_pago' => 'CONTADO',
                    'estado' => 'N',
                    'moneda' => 'PEN',
                    'tipocambio' => 3.71,
                    'subtotal' => $request->adelanto - $request->adelanto * $igvObjeto->porcentaje_igv / 100,
                    'igv' => $request->adelanto * $igvObjeto->porcentaje_igv / 100,
                    'total' => $request->adelanto,
                    'movimiento_id' => $movimientoVenta->id,
                    'sucursal_id' => $sucursal->id,
                    'json_sucursal' => json_encode(Sucursal::find($sucursal->id)),
                    'movreserva_id' => $movReserva->id,
                ]);
                $unidad = Unidad::find(17);

                DetMovventum::create([
                    'movventa_id' => $movventa->id,
                    'tipo_detalle' => 'DETALLADO',
                    'codigo' => '-',
                    'descripcion' => $request->descripcion_alojamiento ?? 'Reserva de habitación',
                    'producto_id' => null,
                    'json_producto' => '[]',
                    'unidad_id' => 17,
                    'json_unidad' => $unidad ? json_encode($unidad) : null,
                    'igv_id' => $igvObjeto->id, // Asignar el igv_id desde la relación
                    'json_igv' => $igvObjeto ? json_encode($igvObjeto) : null,
                    'cantidad' => 1,
                    'monto' => $request->adelanto,
                    'dcto_porcentaje' => $request->descuento ?? 0,
                    'monto_original' => $request->adelanto,
                    'comentario' => $request->comentario ?? '',
                    'det_categoria' => '[]',
                    'opc_categoria' => '[]',
                    'det_movventa_id' => null,
                    'complementos' => json_encode([]),
                    'sucursal_id' => $sucursal->id,
                ]);
                $movimientoCaja = Movimiento::create([
                    'numero' => $this->generateNumeroMovcaja($sucursal->id, $caja->id, 4),
                    'fecha' => now(),
                    'usuario_id' => $usuario->id,
                    'usuario' => $usuario->name,
                    'responsable_id' => $responsable->id,
                    'responsable' => "{$responsable->nombres} {$responsable->apellidos}",
                    'persona_id' => $request->persona_id,
                    'persona' => $persona ? "{$persona->nombres} {$persona->apellidos}" : 'N/A',
                    'comentario' => $request->notas ?? '',
                    'situacion' => 'A',
                    'tipomovimiento_id' => 4,
                    'tipodocumento_id' => 7,
                    'sucursal_id' => $sucursal->id,
                    'movimiento_id' => $movimientoVenta->id,
                ]);

                // Crear Movcaja por adelanto
                $movcaja = Movcaja::create([
                    'conceptopago_id' => 4,
                    'moneda' => $request->moneda ?? 'PEN',
                    'tipocambio' => $request->tipocambio ?? 3.71,
                    'total' => $request->adelanto,
                    'caja_id' => $caja->id,
                    'caja' => $caja->numero,
                    'turno_id' => $turnoId,
                    'json_turno' => json_encode(Turno::find($turnoId)),
                    'movimiento_id' => $movimientoCaja->id,
                    'sucursal_id' => $sucursal->id,
                ]);

                $mediopago = Mediopago::find($request->medio_pago);

                $detalle = DetMovcaja::create([
                    'movcaja_id' => $movcaja->id,
                    'tipo' => 'Pagado',
                    'fechavencimiento' => now(),
                    'fechapago' => now(),
                    'mediopago_id' => $request->medio_pago,
                    'mediopago' => $mediopago->descripcion ?? '',
                    'numero' => '',
                    // Inicializar campos dinámicos
                    'tarjeta_id' => null,
                    'tarjeta' => '',
                    'banco_id' => null,
                    'banco' => '',
                    'billeteradigital_id' => null,
                    'billeteradigital' => '',
                    'pos_id' => null,
                    'pos' => '',

                    'monto' => $request->adelanto,
                    'comentario' => $request->comentario ?? '',
                    'situacion' => 'A',
                    'sucursal_id' => $sucursal->id,
                ]);

                // Según el tipo de mediopago, completar campos relacionados
                switch ((int) $request->medio_pago) {
                    case 2: // Tarjeta (ej: BCP - Crédito)
                        if (! empty($request->tarjeta)) {
                            $partes = explode(' - ', $request->tarjeta);
                            if (count($partes) >= 2) {
                                $nombreTarjeta = trim($partes[0]);
                                $tipoTarjeta = strtolower(trim($partes[1]));
                                $tipo = str_contains($tipoTarjeta, 'débito') ? 'D' : 'C';

                                $detalle->update([
                                    'tarjeta_id' => Tarjetum::where('descripcion', $nombreTarjeta)
                                        ->where('tipo', $tipo)
                                        ->value('id'),
                                    'tarjeta' => $request->tarjeta,
                                ]);
                            }
                        }

                        if (! empty($request->banco)) {
                            $detalle->update([
                                'banco_id' => Banco::where('descripcion', trim($request->banco))->value('id'),
                                'banco' => trim($request->banco),
                            ]);
                        }
                        break;

                    case 3: // Transferencia
                    case 4: // Depósito
                        if (! empty($request->banco)) {
                            $detalle->update([
                                'banco_id' => Banco::where('descripcion', trim($request->banco))->value('id'),
                                'banco' => trim($request->banco),
                            ]);
                        }
                        break;

                    case 5: // Billetera digital (ej: Yape, Plin)
                        $detalle->update([
                            'billeteradigital_id' => Billeteradigital::where('descripcion', 'Yape')->value('id'),
                            'billeteradigital' => 'Yape',
                        ]);
                        break;

                        // case 1: Efectivo → No hace nada extra
                }
            }

            // **Guardar habitaciones en movreserva_habitacion**
            foreach ($jsonHabitaciones as $index => $habitacion) {
                $habitacionId = $habitacion['id'];
                $tarifa = $modoReserva === 'unico'
                    ? ($request->tarifa_habitacion_unico ?? $habitacion['precio'] ?? 0)
                    : ($request->tarifa_habitacion[$index] ?? $habitacion['precio'] ?? 0);

                MovreservaHabitacion::create([
                    'movreserva_id' => $movReserva->id,
                    'habitacion_id' => $habitacionId,
                    'principal' => ($modoReserva === 'unico') ? true : ($index === 0), // Si es único, es principal; si es grupal, la primera habitación es principal
                    'tarifa' => $tarifa,
                    'fechacheckin' => null,
                    'fechacheckout' => null, // Se deja en null hasta que se realice el checkout
                    'dias' => $request->dias ?? 1, // Si no hay días definidos, se asume 1
                    'total' => $tarifa * $request->dias ?? 0.00, // Calcular total basado en días
                    'situacion' => 'PENDIENTE', // Nueva reserva inicia en estado pendiente
                    'json_habitacion' => json_encode($habitacion),
                    'early_checkin' => $request->early ?? 0.00,
                    'late_checkout' => $request->late ?? 0.00,

                ]);
            }

            // Obtener acompañantes y asignar habitaciones correspondientes
            $acompanantes = is_string($request->acompanantes)
                ? json_decode($request->acompanantes, true)
                : $request->acompanantes;

            // Si no hay acompañantes, no hacer nada
            if (! empty($acompanantes)) {

                // Si es necesario, asegurarse de que cada acompañante sea un objeto con 'id' y 'habitacion_id'
                foreach ($acompanantes as $index => $acompanante) {
                    // Si el acompañante es un número, convertirlo a un objeto con 'id' y 'habitacion_id' en null
                    if (is_numeric($acompanante)) {
                        $acompanantes[$index] = ['id' => $acompanante, 'habitacion_id' => null];
                    }
                }

                // Eliminar los huéspedes anteriores del 'movreserva' solo si hay acompañantes
                Huesped::where('movreserva_id', $movReserva->id)->delete();

                // Ahora procesamos los acompañantes
                foreach ($acompanantes as $acompanante) {
                    $habitacionId = $request->input("habitacion_acompanantes.{$acompanante['id']}");

                    // Verificar que el acompañante tiene un ID y asignarle la habitación correspondiente
                    $huesped = new Huesped; // Se crea un nuevo huésped

                    // Asignar los datos del acompañante
                    $huesped->persona_id = $acompanante['id'];
                    $huesped->movreserva_id = $movReserva->id;
                    $huesped->habitacion_id = $habitacionId;

                    // Guardar el huésped
                    $huesped->save();
                }
            }

            session()->forget('acompanantes');
            session()->forget('principal');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reserva creada exitosamente.',
                'redirect_url' => route('calendarioreservas.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    private function generateNumero($tipoDocumentoId, $reservar = true)
    {
        // Obtener el tipo de documento
        $tipoDocumento = Tipodocumento::find($tipoDocumentoId);

        if (!$tipoDocumento) {
            return response()->json(['success' => false, 'message' => 'Tipo de documento no encontrado']);
        }

        // Obtener la abreviatura (ej. B, F, etc.)
        $abreviatura = $tipoDocumento->abreviatura ?? 'N/A';

        // Obtener la persona del usuario autenticado
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);

        // Obtener la primera caja activa de la sucursal del usuario
        $caja = Caja::where('situacion', 'A')
            ->where('sucursal_id', $persona->sucursal_id)
            ->first();

        if (!$caja) {
            return response()->json(['success' => false, 'message' => 'No hay caja activa para esta sucursal']);
        }

        // Formar la serie completa: abreviatura + serie de caja (ej. B001, F001)
        $serie = $abreviatura . str_pad($caja->serie, 3, '0', STR_PAD_LEFT);

        if ($reservar) {
            return CorrelativoService::nextNumeroVenta($persona->sucursal_id, $tipoDocumentoId, $serie);
        }

        return CorrelativoService::peekNumeroVenta($persona->sucursal_id, $tipoDocumentoId, $serie);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $reserva = Movreserva::find($id);

        return view('movreserva.show', compact('reserva'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);
        $sucursal_id = $sucursal->id;
        $habitacion_id = $request->input('habitacion_id');

        // Obtener la reserva a atender
        $movreserva = Movreserva::with(['movimiento.persona', 'habitacion', 'movimiento.responsable', 'habitaciones'])
            ->findOrFail($id);
        $movreserva_habitacion = MovreservaHabitacion::where('habitacion_id', $habitacion_id)->where('movreserva_id', $id)->first();
        // dd( $habitacion_id );
        $mediospago = Mediopago::where('situacion', true)->get();

        $habitaciones = Habitacion::with(['tipohabitacion', 'sucursal', 'piso'])
            ->where('sucursal_id', $persona->sucursal_id)
            ->whereIn('situacion', ['Disponible', 'Limpieza'])
            ->orderBy('numero')
            ->get();

        // Obtener productos o servicios disponibles
        $productos = Producto::with(['relProductosucursal', 'relListaUnidadSucursal'])
            ->whereHas('relProductosucursal', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id);
            })
            ->get();

        // Formatear los productos para el autocomplete
        $motivos_reserva = Motivoreserva::where('situacion', true)->orderBy('orden')->get();
        $origenreserva = Origenreserva::where('situacion', true)->get();

        // Obtener todas las personas de la sucursal
        $personas = Persona::where('sucursal_id', $sucursal_id)->get();

        // Formatear datos para autocompletar personas
        // Obtener responsables disponibles
        $responsables = Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 1); // Asumiendo que 1 es el rol de responsable
        })->where('sucursal_id', $sucursal_id)->get();

        // Obtener tipo de cambio actual
        $tipoCambio = RelParametrosucursal::where('sucursal_id', $sucursal_id)
            ->where('parametro_id', 10) // ID del parámetro para tipo de cambio
            ->value('valor') ?? 3.676; // Valor predeterminado si no hay registro

        $pisos = Piso::where('sucursal_id', $persona->sucursal_id)->orderBy('id')->get();
        // Obtener las habitaciones asignadas a la reserva
        $habitacionesAsignadas = collect(json_decode($movreserva->json_habitaciones, true))->pluck('id')->toArray();

        $horaEntradaJson = RelParametroSucursal::where('sucursal_id', $sucursal->id)
            ->where('parametro_id', 7)
            ->value('valor');

        // Reemplazar corchetes por llaves y luego decodificar el JSON
        $horaEntrada = '12:00'; // Valor por defecto en caso de error
        if ($horaEntradaJson) {
            // Intentar arreglar el formato incorrecto del JSON
            $horaEntradaJson = str_replace(['[', ']'], ['{', '}'], $horaEntradaJson);

            // Ahora decodificar el JSON
            $decoded = json_decode($horaEntradaJson, true);
            $horaEntrada = $decoded['hora_entrada'] ?? $horaEntrada;
        }

        $acompanantesIds = Huesped::where('movreserva_id', $movreserva->id)
            ->where('habitacion_id', '!=', null)
            ->pluck('persona_id')
            ->toArray();

        $acompanantes = Huesped::where('movreserva_id', $movreserva->id)
            ->whereNotNull('habitacion_id')
            ->get()
            ->map(function ($huesped) {
                return [
                    'id' => $huesped->persona_id,
                    'habitacion_id' => $huesped->habitacion_id,
                ];
            })
            ->values()
            ->toArray();

        // dd(json_decode($movreserva->json_acompañantes ?? '[]', true));
        // $huespedes = \App\Models\Persona::whereIn('id', $acompanantesIds)->get();
        return view('movreserva.edit', compact(
            'movreserva',
            'habitaciones',
            'sucursal_id',
            'responsables',
            'tipoCambio',
            'motivos_reserva',
            'pisos',
            'origenreserva',
            'sucursal',
            'habitacionesAsignadas',
            'acompanantesIds',
            'acompanantes',
            'horaEntrada',
            'mediospago',
            'movreserva_habitacion'
            //    'huespedes'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MovreservaRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            // Obtener la reserva y el movimiento asociado
            $movReserva = Movreserva::findOrFail($id);
            $movimiento = Movimiento::findOrFail($movReserva->movimiento_id);
            // Obtener datos generales
            $usuario = Auth::user();
            $responsable = Persona::find($request->responsable_id);
            $persona = $request->persona_id ? Persona::find($request->persona_id) : null;
            $sucursal = Sucursal::find($movReserva->sucursal_id);
            $tipodocumento = Tipodocumento::find(9);

            // Obtener JSON de habitaciones seleccionadas
            $jsonHabitaciones = json_decode($request->detalles_habitaciones_json, true);

            // 🔒 VALIDAR HABITACIONES OCUPADAS SOLO SI NO ESTÁ PENDIENTE
            if ($movReserva->estado !== 'PENDIENTE') {

                // IDs nuevos solicitados
                $nuevasHabitacionesIds = collect($jsonHabitaciones)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->toArray();

                // Habitaciones actualmente asociadas a la reserva
                $habitacionesActualesIds = MovreservaHabitacion::where('movreserva_id', $movReserva->id)
                    ->pluck('habitacion_id')
                    ->toArray();

                // Habitaciones realmente nuevas
                $habitacionesANotificar = array_diff($nuevasHabitacionesIds, $habitacionesActualesIds);

                // Buscar si alguna está ocupada
                $habitacionOcupada = Habitacion::whereIn('id', $habitacionesANotificar)
                    ->where('situacion', 'Ocupada')
                    ->first();

                if ($habitacionOcupada) {
                    throw new \Exception(
                        "La habitación {$habitacionOcupada->numero} figura como OCUPADA. 
            Verifique y realice el CHECK-OUT correspondiente antes de asignarla a un nuevo huésped."
                    );
                }
            }

            $habitacionPrincipal = count($jsonHabitaciones) > 0 ? $jsonHabitaciones[0]['id'] : null;
            // IDs de habitaciones que vienen del formulario
            $nuevasHabitacionesIds = collect($jsonHabitaciones)->pluck('id')->map(fn ($id) => (int) $id)->toArray();

            // Obtener las habitaciones anteriores
            $habitacionesAnteriores = MovreservaHabitacion::where('movreserva_id', $movReserva->id)->get();

            // Grupal 1 habitación quitada + 1 agregada: traspasar EN ATENCIÓN (ej. cambiar 102 por 104 sin perder estado)
            $traspasoHabitacionNuevaId = null;
            $traspasoDesdeMovReservaHabitacion = null;
            $idsAnterioresReserva = $habitacionesAnteriores->pluck('habitacion_id')->map(fn ($x) => (int) $x)->toArray();
            $habitacionesQuitadasReserva = array_values(array_diff($idsAnterioresReserva, $nuevasHabitacionesIds));
            $habitacionesAgregadasReserva = array_values(array_diff($nuevasHabitacionesIds, $idsAnterioresReserva));
            if (
                $movReserva->modo === 'G'
                && count($habitacionesQuitadasReserva) === 1
                && count($habitacionesAgregadasReserva) === 1
            ) {
                $filaViejaMovReservaHab = MovreservaHabitacion::where('movreserva_id', $movReserva->id)
                    ->where('habitacion_id', $habitacionesQuitadasReserva[0])
                    ->first();
                if ($filaViejaMovReservaHab && $filaViejaMovReservaHab->situacion === 'EN ATENCIÓN') {
                    $traspasoHabitacionNuevaId = (int) $habitacionesAgregadasReserva[0];
                    $traspasoDesdeMovReservaHabitacion = [
                        'situacion' => $filaViejaMovReservaHab->situacion,
                        'fechacheckin' => $filaViejaMovReservaHab->fechacheckin,
                        'fechacheckout' => $filaViejaMovReservaHab->fechacheckout,
                        'principal' => $filaViejaMovReservaHab->principal,
                        'early_checkin' => $filaViejaMovReservaHab->early_checkin,
                        'late_checkout' => $filaViejaMovReservaHab->late_checkout,
                        'json_habitacion' => $filaViejaMovReservaHab->json_habitacion,
                    ];
                }
            }

            // Grupal: una quitada + una agregada — situación física de la nueva según la fila MRH quitada (pendiente → Reservada, no Ocupada)
            $filaMovReservaHabitacionQuitada1a1 = null;
            if (
                $movReserva->modo === 'G'
                && count($habitacionesQuitadasReserva) === 1
                && count($habitacionesAgregadasReserva) === 1
            ) {
                $filaMovReservaHabitacionQuitada1a1 = MovreservaHabitacion::where('movreserva_id', $movReserva->id)
                    ->where('habitacion_id', $habitacionesQuitadasReserva[0])
                    ->first();
            }

            $situacionFisicaNuevaHabitacionEnGrupo = function (int $habitacionIdNueva) use (
                $movReserva,
                $filaMovReservaHabitacionQuitada1a1,
                $habitacionesAgregadasReserva
            ): string {
                if ($movReserva->modo !== 'G') {
                    return 'Ocupada';
                }
                $esCambio1a1 = $filaMovReservaHabitacionQuitada1a1
                    && count($habitacionesAgregadasReserva) === 1
                    && $habitacionIdNueva === (int) $habitacionesAgregadasReserva[0];
                if ($esCambio1a1) {
                    return $filaMovReservaHabitacionQuitada1a1->situacion === 'EN ATENCIÓN' ? 'Ocupada' : 'Reservada';
                }

                return 'Ocupada';
            };

            // Actualizar las habitaciones que han sido modificadas
            if ($movReserva->estado !== 'PENDIENTE') {

                // 🔁 Actualizar habitaciones anteriores
                foreach ($habitacionesAnteriores as $habitacionAnterior) {

                    if (! in_array($habitacionAnterior->habitacion_id, $nuevasHabitacionesIds)) {

                        $habitacionAnteriorObj = Habitacion::find($habitacionAnterior->habitacion_id);

                        // 🔒 Solo liberar si estaba ocupada por ESTA reserva
                        if ($habitacionAnteriorObj) {
                            HabitacionAvailability::markAvailableIfNoActiveStay((int) $habitacionAnteriorObj->id);
                        }

                    } else {

                        $habitacionNueva = collect($jsonHabitaciones)
                            ->firstWhere('id', $habitacionAnterior->habitacion_id);

                        if ($habitacionNueva) {
                            $habitacionAnteriorObj = Habitacion::find($habitacionAnterior->habitacion_id);

                            if ($habitacionAnteriorObj) {
                                $situacion = HabitacionAvailability::hasActiveStay((int) $habitacionAnteriorObj->id)
                                    ? 'Ocupada'
                                    : ucfirst(strtolower($habitacionNueva['situacion']));

                                $habitacionAnteriorObj->update([
                                    'situacion' => $situacion,
                                ]);
                            }
                        }
                    }
                }

                // 🔁 Marcar habitaciones nuevas según estado de la línea MRH (grupal 1:1 pendiente → Reservada)
                if ($movReserva->estado === 'EN ATENCIÓN') {
                    foreach ($nuevasHabitacionesIds as $habitacionIdNueva) {
                        if (
                            ! $habitacionesAnteriores->pluck('habitacion_id')->contains($habitacionIdNueva)
                        ) {
                            Habitacion::where('id', $habitacionIdNueva)
                                ->update(['situacion' => $situacionFisicaNuevaHabitacionEnGrupo((int) $habitacionIdNueva)]);
                        }
                    }
                }
            }

            // Ahora, las habitaciones nuevas que no están en las anteriores (misma regla que arriba)
            foreach ($nuevasHabitacionesIds as $habitacionIdNueva) {
                $habitacionNueva = Habitacion::find($habitacionIdNueva);

                if (! $habitacionesAnteriores->pluck('habitacion_id')->contains($habitacionIdNueva) && $movReserva->estado == 'EN ATENCIÓN') {
                    $habitacionNueva->update(['situacion' => $situacionFisicaNuevaHabitacionEnGrupo((int) $habitacionIdNueva)]);
                }
            }

            // Eliminar relaciones que ya no están
            MovreservaHabitacion::where('movreserva_id', $movReserva->id)
                ->whereNotIn('habitacion_id', $nuevasHabitacionesIds)
                ->delete();
            // Obtener el objeto Movhabitacion asociado a la movreserva
            $movHab = Movhabitacion::where('movreserva_id', $movReserva->id)->whereIn('situacion', ['A', 'P'])->first();

            // Reasignar alojamiento por habitación al nuevo número (misma fila MHH, evita perder check-in real)
            if (
                $movHab
                && $movReserva->modo === 'G'
                && $traspasoDesdeMovReservaHabitacion
                && count($habitacionesQuitadasReserva) === 1
                && count($habitacionesAgregadasReserva) === 1
            ) {
                MovhabitacionHabitacion::where('movhabitacion_id', $movHab->id)
                    ->where('habitacion_id', $habitacionesQuitadasReserva[0])
                    ->update(['habitacion_id' => $habitacionesAgregadasReserva[0]]);
            }

            // Verificar si el objeto Movhabitacion existe y tiene el campo 'id'
            if ($movHab) {
                // Si movHab existe, eliminamos las habitaciones que ya no están en la lista de nuevas habitaciones
                $movhabelimi = MovhabitacionHabitacion::where('movhabitacion_id', $movHab->id)
                    ->whereNotIn('habitacion_id', $nuevasHabitacionesIds)
                    ->delete();
                // dd($movhabelimi);
            }

            // Actualizar Movimiento
            $movimiento->update([
                'usuario_id' => $usuario->id,
                'usuario' => $usuario->name,
                'responsable_id' => $responsable->id,
                'responsable' => "{$responsable->nombres} {$responsable->apellidos}",
                'persona_id' => $request->persona_id,
                'persona' => $persona ? "{$persona->nombres} {$persona->apellidos}" : 'N/A',
                'comentario' => $request->comentario ?? '',
                'situacion' => 'A',
            ]);

            // Actualizar MovReserva
            $movReserva->update([
                'fechavencimiento' => $request->fechavencimiento.' '.($request->horavencimiento ?? '00:00:00'),
                'dias' => $request->dias,
                'total' => $request->total,
                'adelanto' => $request->adelanto,
                'habitacionprincipal_id' => $habitacionPrincipal,
                'habitacion_principal' => Habitacion::find($habitacionPrincipal)->numero,
                'json_habitaciones' => json_encode($jsonHabitaciones),
                'motivo_id' => $request->motivo_id,
                'origen_id' => $request->origen_id,
                'mediopagoadelanto_id' => $request->medio_pago,
                'celular' => $request->celular,
                'email' => $request->email,
                'nropersonas' => $request->nropersonas,
                'observacion' => $request->comentario,
                'preciohabitacion' => $request->tarifa_habitacion[0],
                'json_acompañantes' => json_encode($request->acompanantes, JSON_UNESCAPED_UNICODE),
            ]);

            $caja = Caja::where('sucursal_id', $sucursal->id)->first();
            $turnoCaja = RelTurnocaja::conCajaActiva($caja->id)->first();
            $turnoId = $turnoCaja ? $turnoCaja->movcajainicio->turno_id : $request->turno_id;

            $totalVentasExistentes = Movventum::where('movreserva_id', $movReserva->id)
                ->whereHas('movimiento', function ($query) {
                    $query->where('situacion', 'A');
                })
                ->sum('total');

            if ($request->adelanto > $totalVentasExistentes) {

                $igvPredeterminado = RelParametrosucursal::where('sucursal_id', $sucursal->id)
                    ->where('parametro_id', 8) // ID del parámetro para IGV
                    ->value('valor'); // Tomamos directamente el campo 'valor'
                $igvSeleccionado = $request->input('igv_id') == 0 ? $igvPredeterminado : $request->input('igv_id');
                $igvObjeto = Igv::find($igvSeleccionado);

                // Si existe una venta con monto menor, calculamos la diferencia
                $diferencia = $request->adelanto - $totalVentasExistentes;
                // Crear Movimiento
                $nuevoMovimiento = Movimiento::create([
                    'numero' => $this->generateNumero($tipodocumento->id),
                    'fecha' => now(),
                    'usuario_id' => $usuario->id,
                    'usuario' => $usuario->name,
                    'responsable_id' => $responsable->id,
                    'responsable' => "{$responsable->nombres} {$responsable->apellidos}",
                    'persona_id' => $request->persona_id,
                    'persona' => $persona ? "{$persona->nombres} {$persona->apellidos}" : 'N/A',
                    'comentario' => $request->comentario ?? '',
                    'situacion' => 'A',
                    'tipomovimiento_id' => $tipodocumento->tipomovimiento_id,
                    'tipodocumento_id' => $tipodocumento->id,
                    'sucursal_id' => $sucursal->id,
                ]);
                // Crear una nueva venta para la diferencia
                $movventa = Movventum::create([
                    'fecha' => now(),
                    'usuario_id' => $usuario->id,
                    'usuario' => $usuario->name,
                    'responsable_id' => $responsable->id,
                    'responsable' => "{$responsable->nombres} {$responsable->apellidos}",
                    'persona_id' => $usuario->persona_id,
                    'persona' => $usuario->persona ? "{$usuario->persona->nombres} {$usuario->persona->apellidos}" : 'N/A',
                    'comentario' => $request->comentario ?? '',
                    'serie' => $tipodocumento->abreviatura.''.$caja->serie,
                    'anio' => now()->year,
                    'tipo_detalle' => 'DETALLADO',
                    'tipo_pago' => 'CONTADO',
                    'estado' => 'N',
                    'moneda' => 'PEN',
                    'tipocambio' => 3.71,
                    'subtotal' => $diferencia - $diferencia * $igvObjeto->porcentaje_igv / 100,
                    'igv' => $diferencia * $igvObjeto->porcentaje_igv / 100,
                    'total' => $diferencia,
                    'movimiento_id' => $nuevoMovimiento->id,
                    'sucursal_id' => $sucursal->id,
                    'json_sucursal' => json_encode(Sucursal::find($sucursal->id)),
                    'movreserva_id' => $movReserva->id,
                    'movhabitacion_id' => $movHab ? $movHab->id : null,
                ]);
                $unidad = Unidad::find(17);

                DetMovventum::create([
                    'movventa_id' => $movventa->id,
                    'tipo_detalle' => 'DETALLADO',
                    'codigo' => '-',
                    'descripcion' => $request->descripcion_alojamiento ?? 'Reserva de habitación',
                    'producto_id' => null,
                    'json_producto' => '[]',
                    'unidad_id' => 17,
                    'json_unidad' => $unidad ? json_encode($unidad) : null,
                    'igv_id' => $igvObjeto->id, // Asignar el igv_id desde la relación
                    'json_igv' => $igvObjeto ? json_encode($igvObjeto) : null,
                    'cantidad' => 1,
                    'monto' => $diferencia,
                    'dcto_porcentaje' => $request->descuento ?? 0,
                    'monto_original' => $diferencia,
                    'comentario' => $request->comentario ?? '',
                    'det_categoria' => '[]',
                    'opc_categoria' => '[]',
                    'det_movventa_id' => null,
                    'complementos' => json_encode([]),
                    'sucursal_id' => $sucursal->id,
                ]);

                $movimientoCaja = Movimiento::create([
                    'numero' => $this->generateNumeroMovcaja($sucursal->id, $caja->id, 4),
                    'fecha' => now(),
                    'usuario_id' => $usuario->id,
                    'usuario' => $usuario->name,
                    'responsable_id' => $responsable->id,
                    'responsable' => "{$responsable->nombres} {$responsable->apellidos}",
                    'persona_id' => $request->persona_id,
                    'persona' => $persona ? "{$persona->nombres} {$persona->apellidos}" : 'N/A',
                    'comentario' => $request->notas ?? '',
                    'situacion' => 'A',
                    'tipomovimiento_id' => 4,
                    'tipodocumento_id' => 7,
                    'sucursal_id' => $sucursal->id,
                    'movimiento_id' => $nuevoMovimiento->id,
                ]);

                // Crear Movcaja por adelanto
                $movcaja = Movcaja::create([
                    'conceptopago_id' => 4,
                    'moneda' => $request->moneda ?? 'PEN',
                    'tipocambio' => $request->tipocambio ?? 3.71,
                    'total' => $diferencia,
                    'caja_id' => $caja->id,
                    'caja' => $caja->numero,
                    'turno_id' => $turnoId,
                    'json_turno' => json_encode(Turno::find($turnoId)),
                    'movimiento_id' => $movimientoCaja->id,
                    'sucursal_id' => $sucursal->id,
                ]);

                $mediopago = Mediopago::find($request->medio_pago);

                $detalle = DetMovcaja::create([
                    'movcaja_id' => $movcaja->id,
                    'tipo' => 'Pagado',
                    'fechavencimiento' => now(),
                    'fechapago' => now(),
                    'mediopago_id' => $request->medio_pago,
                    'mediopago' => $mediopago->descripcion ?? '',
                    'numero' => '',
                    'tarjeta_id' => null,
                    'tarjeta' => '',
                    'banco_id' => null,
                    'banco' => '',
                    'billeteradigital_id' => null,
                    'billeteradigital' => '',
                    'pos_id' => null,
                    'pos' => '',

                    'monto' => $diferencia,
                    'comentario' => $request->comentario ?? '',
                    'situacion' => 'A',
                    'sucursal_id' => $sucursal->id,
                ]);

                // Según el tipo de mediopago, completar campos relacionados
                switch ((int) $request->medio_pago) {
                    case 2: // Tarjeta (ej: BCP - Crédito)
                        if (! empty($request->tarjeta)) {
                            $partes = explode(' - ', $request->tarjeta);
                            if (count($partes) >= 2) {
                                $nombreTarjeta = trim($partes[0]);
                                $tipoTarjeta = strtolower(trim($partes[1]));
                                $tipo = str_contains($tipoTarjeta, 'débito') ? 'D' : 'C';

                                $detalle->update([
                                    'tarjeta_id' => Tarjetum::where('descripcion', $nombreTarjeta)
                                        ->where('tipo', $tipo)
                                        ->value('id'),
                                    'tarjeta' => $request->tarjeta,
                                ]);
                            }
                        }

                        if (! empty($request->banco)) {
                            $detalle->update([
                                'banco_id' => Banco::where('descripcion', trim($request->banco))->value('id'),
                                'banco' => trim($request->banco),
                            ]);
                        }
                        break;

                    case 3: // Transferencia
                    case 4: // Depósito
                        if (! empty($request->banco)) {
                            $detalle->update([
                                'banco_id' => Banco::where('descripcion', trim($request->banco))->value('id'),
                                'banco' => trim($request->banco),
                            ]);
                        }
                        break;

                    case 5: // Billetera digital (ej: Yape, Plin)
                        $detalle->update([
                            'billeteradigital_id' => Billeteradigital::where('descripcion', 'Yape')->value('id'),
                            'billeteradigital' => 'Yape',
                        ]);
                        break;
                }
            }

            foreach ($jsonHabitaciones as $index => $habitacion) {
                $movReservaHabitacion = MovreservaHabitacion::firstOrNew([
                    'movreserva_id' => $movReserva->id,
                    'habitacion_id' => $habitacion['id'],
                ]);

                $movReservaHabitacion->fechacheckin = $movReserva->fechavencimiento;
                $movReservaHabitacion->fechacheckout = null;
                $movReservaHabitacion->early_checkin = $request->early ?? 0.00;
                $movReservaHabitacion->late_checkout = $request->late ?? 0.00;
                $movReservaHabitacion->dias = $request->dias ?? 1;
                $movReservaHabitacion->tarifa = $request->tarifa_habitacion[$index] ?? 0;
                $movReservaHabitacion->total = $movReservaHabitacion->tarifa * $movReservaHabitacion->dias;

                // Reserva grupal: cada habitación mantiene PENDIENTE / EN ATENCIÓN según su check-in.
                // Antes se forzaba $movReserva->estado a todas y al guardar edición se igualaban mal.
                if ($movReserva->modo !== 'G') {
                    $movReservaHabitacion->situacion = $movReserva->estado;
                } elseif (! $movReservaHabitacion->exists) {
                    $hidNueva = (int) $habitacion['id'];
                    if (
                        $traspasoDesdeMovReservaHabitacion
                        && $traspasoHabitacionNuevaId
                        && $hidNueva === $traspasoHabitacionNuevaId
                    ) {
                        $movReservaHabitacion->situacion = $traspasoDesdeMovReservaHabitacion['situacion'];
                        if (! empty($traspasoDesdeMovReservaHabitacion['fechacheckin'])) {
                            $movReservaHabitacion->fechacheckin = $traspasoDesdeMovReservaHabitacion['fechacheckin'];
                        }
                        $movReservaHabitacion->fechacheckout = $traspasoDesdeMovReservaHabitacion['fechacheckout'];
                        $movReservaHabitacion->early_checkin = $traspasoDesdeMovReservaHabitacion['early_checkin'];
                        $movReservaHabitacion->late_checkout = $traspasoDesdeMovReservaHabitacion['late_checkout'];
                        if ($traspasoDesdeMovReservaHabitacion['json_habitacion'] !== null && $traspasoDesdeMovReservaHabitacion['json_habitacion'] !== '') {
                            $movReservaHabitacion->json_habitacion = $traspasoDesdeMovReservaHabitacion['json_habitacion'];
                        }
                        $movReservaHabitacion->principal = $traspasoDesdeMovReservaHabitacion['principal'];
                    } elseif ($movReserva->estado === 'EN ATENCIÓN') {
                        $movReservaHabitacion->situacion = 'PENDIENTE';
                    } else {
                        $movReservaHabitacion->situacion = $movReserva->estado;
                    }
                }

                $movReservaHabitacion->save();
            }

            // Verificar si existe Movhabitacion
            $movHabitacion = Movhabitacion::where('movreserva_id', $movReserva->id)
                ->whereIn('situacion', ['A', 'P'])
                ->first();

            if ($movHabitacion) {
                $movHabitacion->update([
                    'fechaingreso' => $movReserva->fechainicio,
                    'fechasalida' => Carbon::parse($movReserva->fechainicio)->addDays($movReserva->dias),
                    'dias' => $movReserva->dias,
                    'total' => $movReserva->total,
                    'preciohabitacion' => $movReserva->total / max($movReserva->dias, 1),
                    'comentario' => $movReserva->observacion,
                    'habitacion_id' => $movReserva->habitacionprincipal_id,
                ]);

                foreach ($jsonHabitaciones as $index => $habitacion) {
                    // Actualizar o crear MovreservaHabitacion
                    $movReservaHabitacion = MovreservaHabitacion::firstOrNew([
                        'movreserva_id' => $movReserva->id,
                        'habitacion_id' => $habitacion['id'],
                    ]);

                    // Grupal + EN ATENCIÓN: no pisar fechas reales de check-in (p. ej. traspaso 102→104)
                    if ($movReserva->modo !== 'G' || $movReservaHabitacion->situacion !== 'EN ATENCIÓN') {
                        $movReservaHabitacion->fechacheckin = $movReserva->fechavencimiento;
                        $movReservaHabitacion->fechacheckout = null;
                    } elseif (empty($movReservaHabitacion->fechacheckin)) {
                        $movReservaHabitacion->fechacheckin = $movReserva->fechavencimiento;
                    }
                    $movReservaHabitacion->dias = $request->dias ?? 1;
                    $movReservaHabitacion->tarifa = $request->tarifa_habitacion[$index] ?? 0;
                    $movReservaHabitacion->total = $movReservaHabitacion->tarifa * $movReservaHabitacion->dias;

                    $movReservaHabitacion->save();

                    if ($movReserva->modo === 'G' && $movReservaHabitacion->situacion !== 'EN ATENCIÓN') {
                        MovhabitacionHabitacion::where('movhabitacion_id', $movHabitacion->id)
                            ->where('habitacion_id', $habitacion['id'])
                            ->delete();

                        continue;
                    }

                    // Único o grupal con check-in: sincronizar movhabitacion_habitacion
                    $movHabitacionHabitacion = MovhabitacionHabitacion::firstOrNew([
                        'movhabitacion_id' => $movHabitacion->id,
                        'habitacion_id' => $habitacion['id'],
                    ]);

                    $preservarFechasMhh = $movReserva->modo === 'G'
                        && $movReservaHabitacion->situacion === 'EN ATENCIÓN'
                        && $movHabitacionHabitacion->exists
                        && $movHabitacionHabitacion->fechacheckin;
                    $mhhCheckin = $preservarFechasMhh ? $movHabitacionHabitacion->fechacheckin : null;
                    $mhhCheckout = $preservarFechasMhh ? $movHabitacionHabitacion->fechacheckout : null;

                    $movHabitacionHabitacion->principal = $habitacion['principal'] ?? 0;
                    $movHabitacionHabitacion->tarifa = $movReservaHabitacion->tarifa;
                    $movHabitacionHabitacion->dias = $movReservaHabitacion->dias;
                    $movHabitacionHabitacion->total = $movReservaHabitacion->total;
                    $movHabitacionHabitacion->fechacheckin = $mhhCheckin ?? $movReserva->fechavencimiento;
                    $movHabitacionHabitacion->modalidad = 'D';
                    $movHabitacionHabitacion->fechacheckout = $preservarFechasMhh ? $mhhCheckout : null;
                    $movHabitacionHabitacion->situacion = $movHabitacion->situacion;
                    $movHabitacionHabitacion->json_habitacion = json_encode($habitacion, JSON_UNESCAPED_UNICODE);

                    $movHabitacionHabitacion->save();
                }
            }

            // Obtener acompañantes y asignar habitaciones correspondientes
            $acompanantes = is_string($request->acompanantes)
                ? json_decode($request->acompanantes, true)
                : $request->acompanantes;

            // Si es necesario, asegurarse de que cada acompañante sea un objeto con 'id' y 'habitacion_id'
            foreach ($acompanantes as $index => $acompanante) {
                // Si el acompañante es un número, convertirlo a un objeto con 'id' y 'habitacion_id' en null
                if (is_numeric($acompanante)) {
                    $acompanantes[$index] = ['id' => $acompanante, 'habitacion_id' => null];
                }
            }

            // Eliminar los huéspedes anteriores del 'movreserva'
            Huesped::where('movreserva_id', $movReserva->id)->delete();

            // Ahora procesamos los acompañantes
            foreach ($acompanantes as $acompanante) {
                $habitacionId = $request->input("habitacion_acompanantes.{$acompanante['id']}");

                // Verificar que el acompañante tiene un ID y asignarle la habitación correspondiente
                $huesped = new Huesped; // Se crea un nuevo huésped

                // Asignar los datos del acompañante
                $huesped->persona_id = $acompanante['id'];
                $huesped->movreserva_id = $movReserva->id;
                $huesped->habitacion_id = $habitacionId;

                // Guardar el huésped
                $huesped->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reserva actualizada exitosamente.',
                'redirect_url' => route('calendarioreservas.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
                'redirect_url' => route('calendarioreservas.index'),
            ], 500);
        }
    }

    public function verDetalles(Request $request, $id)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);
        $sucursal_id = $sucursal->id;
        $habitacion_id = $request->input('habitacion_id');

        // Obtener la reserva a atender
        $movreserva = Movreserva::with(['movimiento.persona', 'habitacion', 'movimiento.responsable', 'habitaciones'])
            ->findOrFail($id);
        $movreserva_habitacion = MovreservaHabitacion::where('habitacion_id', $habitacion_id)->where('movreserva_id', $id)->first();
        // dd( $habitacion_id );
        $mediospago = Mediopago::where('situacion', true)->get();

        $habitaciones = Habitacion::with(['tipohabitacion', 'sucursal', 'piso'])
            ->where('sucursal_id', $persona->sucursal_id)
            ->whereIn('situacion', ['Disponible', 'Limpieza'])
            ->orderBy('numero')
            ->get();

        // Obtener productos o servicios disponibles
        $productos = Producto::with(['relProductosucursal', 'relListaUnidadSucursal'])
            ->whereHas('relProductosucursal', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id);
            })
            ->get();

        // Formatear los productos para el autocomplete
        $motivos_reserva = Motivoreserva::where('situacion', true)->orderBy('orden')->get();
        $origenreserva = Origenreserva::where('situacion', true)->get();

        // Obtener todas las personas de la sucursal
        $personas = Persona::where('sucursal_id', $sucursal_id)->get();

        // Formatear datos para autocompletar personas
        // Obtener responsables disponibles
        $responsables = Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 1); // Asumiendo que 1 es el rol de responsable
        })->where('sucursal_id', $sucursal_id)->get();

        // Obtener tipo de cambio actual
        $tipoCambio = RelParametrosucursal::where('sucursal_id', $sucursal_id)
            ->where('parametro_id', 10) // ID del parámetro para tipo de cambio
            ->value('valor') ?? 3.676; // Valor predeterminado si no hay registro

        $pisos = Piso::where('sucursal_id', $persona->sucursal_id)->orderBy('id')->get();
        // Obtener las habitaciones asignadas a la reserva
        $habitacionesAsignadas = collect(json_decode($movreserva->json_habitaciones, true))->pluck('id')->toArray();

        $horaEntradaJson = RelParametroSucursal::where('sucursal_id', $sucursal->id)
            ->where('parametro_id', 7)
            ->value('valor');

        // Reemplazar corchetes por llaves y luego decodificar el JSON
        $horaEntrada = '12:00'; // Valor por defecto en caso de error
        if ($horaEntradaJson) {
            // Intentar arreglar el formato incorrecto del JSON
            $horaEntradaJson = str_replace(['[', ']'], ['{', '}'], $horaEntradaJson);

            // Ahora decodificar el JSON
            $decoded = json_decode($horaEntradaJson, true);
            $horaEntrada = $decoded['hora_entrada'] ?? $horaEntrada;
        }

        $acompanantesIds = Huesped::where('movreserva_id', $movreserva->id)
            ->where('habitacion_id', '!=', null)
            ->pluck('persona_id')
            ->toArray();

        $acompanantes = Huesped::where('movreserva_id', $movreserva->id)
            ->whereNotNull('habitacion_id')
            ->get()
            ->map(function ($huesped) {
                return [
                    'id' => $huesped->persona_id,
                    'habitacion_id' => $huesped->habitacion_id,
                ];
            })
            ->values()
            ->toArray();

        // dd(json_decode($movreserva->json_acompañantes ?? '[]', true));
        // $huespedes = \App\Models\Persona::whereIn('id', $acompanantesIds)->get();
        return view('movreserva.detalles', compact(
            'movreserva',
            'habitaciones',
            'sucursal_id',
            'responsables',
            'tipoCambio',
            'motivos_reserva',
            'pisos',
            'origenreserva',
            'sucursal',
            'habitacionesAsignadas',
            'acompanantesIds',
            'acompanantes',
            'horaEntrada',
            'mediospago',
            'movreserva_habitacion'
            //    'huespedes'
        ));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            Movreserva::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reserva eliminada correctamente.',
                'redirect_url' => route('movreserva.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function filter(Request $request)
    {
        try {
            $sucursal_id = Auth::user()->persona->sucursal_id;
            $search = $request->input('search');
            $perPage = $request->input('perPage', 10); // Capturar el número de registros por página
            $estadoSeleccionado = $request->input('estado', 'EN ATENCIÓN'); // Estado por defecto: "EN ATENCIÓN"

            // Iniciar la consulta base
            $query = Movreserva::with('movimiento')
                ->where('sucursal_id', $sucursal_id);

            // Obtener las columnas de la tabla 'movreserva'
            $columns = Schema::getColumnListing('movreserva');

            // Filtrar solo las columnas de tipo texto
            $textColumns = [];
            foreach ($columns as $column) {
                $columnType = DB::select("
                    SELECT data_type
                    FROM information_schema.columns
                    WHERE table_name = 'movreserva' AND column_name = ?
                ", [$column]);

                if (isset($columnType[0]) && in_array($columnType[0]->data_type, ['character varying', 'text'])) {
                    $textColumns[] = $column;
                }
            }

            if ($search) {
                $query->where(function ($q) use ($textColumns, $search) {
                    foreach ($textColumns as $column) {
                        $q->orWhereRaw("LOWER($column) LIKE ?", ['%'.strtolower($search).'%']);
                    }
                });

                // Búsqueda en la tabla movimiento
                $query->orWhereHas('movimiento', function ($q) use ($search) {
                    $q->whereRaw('LOWER(numero) LIKE ?', ['%'.strtolower($search).'%'])
                        ->orWhereRaw('LOWER(comentario) LIKE ?', ['%'.strtolower($search).'%']);
                });

                // Búsqueda en persona y personamaestro a través de movimiento
                $query->orWhereHas('movimiento.persona.personamaestro', function ($q) use ($search) {
                    $q->whereRaw('LOWER(nrodoc) LIKE ?', ['%'.strtolower($search).'%']);
                });
            }

            // 📌 Aplicar filtros de estado y habitación
            if ($estadoSeleccionado !== 'TODAS') {
                if ($estadoSeleccionado === 'ANULADO') {
                    // Si el estado es ANULADO, filtrar aquellos cuyo movimiento tenga situación 'D'
                    $query->whereHas('movimiento', function ($q) {
                        $q->where('situacion', 'D');
                    });
                } else {
                    $query->where('estado', $estadoSeleccionado);
                }
            }

            if ($request->has('habitacion') && $request->habitacion !== '') {
                $query->whereHas('habitaciones', function ($q) use ($request) {
                    $q->where('habitacion_id', $request->habitacion);
                });
            }

            // $reservas = $query->orderBy('fechainicio', 'desc')->paginate($perPage);
            $reservas = $query->orderBy('id', 'desc')->paginate(10);
            $reservas->getCollection()->transform(function ($reserva) {
                // Resolver movhabitacion usando el helper del modelo
                $reserva->movhabitacion = $reserva->movhabitacion();

                return $reserva;
            });

            // Obtener las operaciones para la vista
            $operaciones = Operacione::where('tabla_id', 77)->where('estado', true)->get();

            // Obtener habitaciones para el filtro
            $habitaciones = Habitacion::where('sucursal_id', $sucursal_id)->get();

            // Definir estados posibles para el filtro
            $estados = ['TODAS', 'PENDIENTE', 'EN ATENCIÓN', 'FINALIZADO', 'AUSENTE'];

            return view('movreserva.index', compact('reservas', 'operaciones', 'habitaciones', 'estados', 'estadoSeleccionado'))
                ->with('i', (request()->input('page', 1) - 1) * $reservas->perPage());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function generarPDF($id)
    {
        // Buscar la reserva con relaciones
        $reserva = Movreserva::with([
            'habitacion.tipohabitacion',
            'movimiento.persona',
            'movreserva_habitacion.habitacion.tipohabitacion',
        ])->findOrFail($id);

        $sucursal = Sucursal::findOrFail($reserva->sucursal_id);
        $cliente = $reserva->movimiento->Persona;

        $logoDataUri = null;
        if (!empty($sucursal->logo)) {
            $logoRelativePath = ltrim(str_replace('\\', '/', $sucursal->logo), '/');
            $logoCandidates = [public_path($logoRelativePath)];

            if (str_starts_with($logoRelativePath, 'storage/')) {
                $logoCandidates[] = storage_path('app/public/'.substr($logoRelativePath, strlen('storage/')));
            }

            foreach ($logoCandidates as $logoPath) {
                if (!is_file($logoPath) || !is_readable($logoPath)) {
                    continue;
                }

                $mimeType = mime_content_type($logoPath) ?: 'image/png';
                $logoDataUri = 'data:'.$mimeType.';base64,'.base64_encode(file_get_contents($logoPath));
                break;
            }
        }

        $habitacionesData = [];
        $totalReserva = 0;

        if ($reserva->modo === 'G') {
            // Modo grupal → recorrer todas las habitaciones
            foreach ($reserva->movreserva_habitacion as $det) {
                $habitacionesData[] = [
                    'habitacion' => $det->habitacion->numero ?? 'N/A',
                    'tipo' => $det->habitacion->tipohabitacion->nombre ?? 'N/A',
                    'costo' => $det->tarifa ?? 0,
                    'dias' => $det->dias ?? 0,
                    'total' => $det->total ?? 0,
                ];
                $totalReserva += $det->total ?? 0;
            }
        } else {
            // Modo unitario → solo la habitación principal
            $habitacion = $reserva->habitacion;
            $habitacionesData[] = [
                'habitacion' => $habitacion->numero ?? 'N/A',
                'tipo' => $habitacion->tipohabitacion->nombre ?? 'N/A',
                'costo' => $reserva->preciohabitacion ?? 0,
                'dias' => $reserva->dias ?? 0,
                'total' => $reserva->total ?? 0,
            ];
            $totalReserva = $reserva->total ?? 0;
        }

        $reservaData = [
            'id' => $reserva->id,
            'fecha' => $reserva->fechavencimiento ? Carbon::parse($reserva->fechavencimiento)->format('d/m/Y') : null,
            'nombre_cliente' => trim(($cliente->nombres ?? '').' '.($cliente->apellidos ?? '')),
            'telefono' => $cliente->telefonofijo ?? '-',
            'movil' => $reserva->celular ?? '-',
            'email' => $reserva->email ?? '-',
            'direccion' => $cliente->direccion ?? 'N/A',
            'moneda' => $reserva->moneda ?? 'PEN',
            'total' => $totalReserva,
            'adelanto' => $reserva->adelanto ?? 0,
            'saldo' => ($totalReserva - ($reserva->adelanto ?? 0)),
            'habitaciones' => $habitacionesData,
            'nropersonas' => $reserva->nropersonas,
            'nronoches' => $reserva->dias,
            'estado' => $reserva->estado,
            'observacion' => $reserva->observacion ?? 'N/A',
            'modo' => $reserva->modo,
        ];

        $pdf = Pdf::loadView('movreserva.ficha', compact('reservaData', 'sucursal', 'logoDataUri'));

        return $pdf->stream("Reserva_{$reserva->id}.pdf");
    }

    public function habitacionesDisponibles(Request $request)
    {
        $fecha = $request->input('fecha');
        $sucursal_id = Auth::user()->persona->sucursal_id;

        // === 1️⃣ Buscar reservas activas que cruzan con la fecha ===
        $reservasOcupadas = MovreservaHabitacion::with([
            'habitacion:id,numero,tipohabitacion_id,piso_id',
            'habitacion.tipohabitacion:id,nombre',
            'habitacion.piso:id,nombre',
            'movreserva:id,fechavencimiento,fechainicio,fechasalida,dias,estado,movimiento_id,habitacionprincipal_id,total',
            'movreserva.movimiento:id,situacion,numero,fecha',
        ])
            ->whereHas('movreserva', function ($q) use ($sucursal_id, $fecha) {
                $q->whereHas('movimiento', function ($query) {
                    $query->where('situacion', '!=', 'D'); // no descartados
                })
                    ->where('sucursal_id', $sucursal_id)
                    ->whereRaw("
                ?::date >= COALESCE(fechainicio::date, fechavencimiento::date)
                AND ?::date < (COALESCE(fechainicio, fechavencimiento) + INTERVAL '1 day' * dias)::date
            ", [$fecha, $fecha]);
            })
            ->get();

        // === 2️⃣ Obtener IDs de habitaciones ocupadas por reserva ===
        $habitacionesOcupadasIds = $reservasOcupadas->pluck('habitacion_id')->unique();

        // === 3️⃣ Habitaciones DISPONIBLES (incluye Disponible y Limpieza) ===
        $habitacionesDisponibles = Habitacion::with('tipohabitacion', 'piso')
            ->whereNotIn('id', $habitacionesOcupadasIds)
            ->where('sucursal_id', $sucursal_id)
            ->whereIn('situacion', ['Disponible', 'Limpieza'])
            ->orderBy('numero')
            ->get()
            ->groupBy('piso_id');

        // === 4️⃣ Habitaciones con situación = "Ocupada" ===
        $habitacionesOcupadas = Habitacion::with('tipohabitacion', 'piso')
            ->where('situacion', 'Ocupada')
            ->where('sucursal_id', $sucursal_id)
            ->orderBy('numero')
            ->get()
            ->groupBy('piso_id');

        // === 5️⃣ Respuesta JSON ===
        return response()->json([
            'disponibles' => $habitacionesDisponibles,
            'ocupadas' => $habitacionesOcupadas,
        ]);
    }

    public function report(Request $request)
    {
        try {
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal = Sucursal::findOrFail($persona->sucursal_id);

            // Cajas y operaciones (puedes ajustar tabla_id si es necesario)
            $cajas = Caja::where('sucursal_id', $sucursal->id)->orderBy('situacion', 'ASC')->get();
            $operaciones = Operacione::where('operaciones.tabla_id', 77) // tabla_id 77 para movreserva
                ->where('operaciones.estado', true)
                ->join('rel_operacionsucursal as ros', 'operaciones.id', '=', 'ros.operacion_id')
                ->where('ros.sucursal_id', $sucursal->id)
                ->select('operaciones.*')
                ->orderBy('ros.orden', 'asc')
                ->get();

            $search = trim((string) $request->input('search', ''));
            $perPage = (int) $request->input('perPage', 10);
            $estadoSeleccionado = $request->input('estado', 'EN ATENCIÓN');
            $habitacionId = $request->input('habitacion');
            $origenId = $request->input('origen');
            $movimientoSituacion = $request->input('movimiento_situacion', 'TODAS');
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');

            $query = $this->buildReservaReportQuery($request, $sucursal->id);

            $reservas = $query->orderBy('id', 'desc')->paginate($perPage)->appends($request->query());
            $reservas->getCollection()->transform(function ($reserva) {
                $reserva->movhabitacion = $reserva->movhabitacion();

                return $reserva;
            });

            // Cargar habitaciones para el filtro
            $habitaciones = Habitacion::where('sucursal_id', $sucursal->id)->get();
            $origenes = Origenreserva::where('situacion', true)->orderBy('descripcion')->get();
            $estados = ['TODAS', 'PENDIENTE', 'EN ATENCIÓN', 'FINALIZADO', 'ANULADO', 'AUSENTE'];
            $movimientoSituaciones = ['TODAS', 'Activado', 'Desactivado'];

            $totalReservas = $reservas->where('estado', '!=', 'ANULADO')->count();
            $totalMonto = $reservas->where('estado', '!=', 'ANULADO')->sum('total');
            $totalDescuento = $reservas->where('estado', '!=', 'ANULADO')->sum('descuento');
            $totalPagado = $reservas->where('estado', '!=', 'ANULADO')->sum('adelanto');

            return view('movreserva.report', compact(
                'reservas',
                'operaciones',
                'cajas',
                'habitaciones',
                'origenes',
                'estados',
                'movimientoSituaciones',
                'search',
                'perPage',
                'estadoSeleccionado',
                'habitacionId',
                'origenId',
                'movimientoSituacion',
                'fechaInicio',
                'fechaFin',
                'totalReservas',
                'totalMonto',
                'totalDescuento',
                'totalPagado'
            ))->with('i', (request()->input('page', 1) - 1) * $reservas->perPage());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function generarPDFReporte(Request $request)
    {
        $sucursal = Sucursal::findOrFail(Auth::user()->persona->sucursal_id);
        $reservas = $this->buildReservaReportQuery($request, $sucursal->id)
            ->orderBy('id', 'desc')
            ->get();

        $totalReservado = $reservas->sum('total');

        $pdf = Pdf::loadView('movreserva.reportepdf', [
            'reservas' => $reservas,
            'fecha_inicio' => $request->input('fecha_inicio'),
            'fecha_fin' => $request->input('fecha_fin'),
            'sucursal' => $sucursal,
            'totalReservado' => $totalReservado,
        ])->setPaper('A4', 'landscape');

        return $pdf->stream('reservas_habitaciones.pdf');
    }

    public function exportarExcelReporte(Request $request)
    {
        $sucursalId = Auth::user()->persona->sucursal_id;
        $reservas = $this->buildReservaReportQuery($request, $sucursalId)
            ->orderBy('id', 'desc')
            ->get();

        return Excel::download(
            new MovreservaReportExport(
                $reservas,
                $request->input('fecha_inicio'),
                $request->input('fecha_fin')
            ),
            'reservas_habitaciones.xlsx'
        );
    }

    private function buildReservaReportQuery(Request $request, int $sucursalId)
    {
        $search = trim((string) $request->input('search', ''));
        $estadoSeleccionado = $request->input('estado', 'EN ATENCIÓN');
        $habitacionId = $request->input('habitacion');
        $origenId = $request->input('origen');
        $movimientoSituacion = $request->input('movimiento_situacion', 'TODAS');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $query = Movreserva::with([
            'movimiento.tipomovimiento',
            'movimiento.persona.personamaestro',
            'movimiento.responsable.personamaestro',
            'movimiento.responsable.usuario',
            'habitaciones',
            'origen',
            'mediopago',
        ])->where('sucursal_id', $sucursalId);

        if ($estadoSeleccionado && $estadoSeleccionado !== 'TODAS') {
            $query->where('estado', $estadoSeleccionado);
        }

        if (!empty($habitacionId)) {
            $query->where(function ($innerQuery) use ($habitacionId) {
                $innerQuery->where('habitacionprincipal_id', $habitacionId)
                    ->orWhereHas('habitaciones', function ($habitacionQuery) use ($habitacionId) {
                        $habitacionQuery->whereKey($habitacionId);
                    });
            });
        }

        if (!empty($origenId)) {
            $query->where('origen_id', $origenId);
        }

        if ($movimientoSituacion && $movimientoSituacion !== 'TODAS') {
            $situacion = $movimientoSituacion === 'Activado' ? 'A' : 'D';
            $query->whereHas('movimiento', function ($movimientoQuery) use ($situacion) {
                $movimientoQuery->where('situacion', $situacion);
            });
        }

        if (!empty($fechaInicio)) {
            $query->whereDate('fechainicio', '>=', $fechaInicio);
        }

        if (!empty($fechaFin)) {
            $query->whereDate('fechainicio', '<=', $fechaFin);
        }

        if ($search !== '') {
            $searchLower = strtolower($search);

            $query->where(function ($searchQuery) use ($searchLower) {
                $searchQuery->whereRaw('LOWER(COALESCE(codreserva, \'\')) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(COALESCE(celular, \'\')) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(COALESCE(email, \'\')) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('CAST(id AS TEXT) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereHas('movimiento', function ($movimientoQuery) use ($searchLower) {
                        $movimientoQuery->whereRaw('LOWER(CAST(numero AS TEXT)) LIKE ?', ["%{$searchLower}%"])
                            ->orWhereRaw('LOWER(COALESCE(comentario, \'\')) LIKE ?', ["%{$searchLower}%"])
                            ->orWhereRaw('LOWER(COALESCE(persona, \'\')) LIKE ?', ["%{$searchLower}%"])
                            ->orWhereRaw('LOWER(COALESCE(responsable, \'\')) LIKE ?', ["%{$searchLower}%"]);
                    })
                    ->orWhereHas('movimiento.persona.personamaestro', function ($personaQuery) use ($searchLower) {
                        $personaQuery->whereRaw('LOWER(COALESCE(nrodoc, \'\')) LIKE ?', ["%{$searchLower}%"])
                            ->orWhereRaw('LOWER(COALESCE(nombrecompleto, \'\')) LIKE ?', ["%{$searchLower}%"]);
                    })
                    ->orWhereHas('habitaciones', function ($habitacionQuery) use ($searchLower) {
                        $habitacionQuery->whereRaw('LOWER(COALESCE(numero, \'\')) LIKE ?', ["%{$searchLower}%"]);
                    })
                    ->orWhereHas('origen', function ($origenQuery) use ($searchLower) {
                        $origenQuery->whereRaw('LOWER(COALESCE(descripcion, \'\')) LIKE ?', ["%{$searchLower}%"]);
                    });
            });
        }

        return $query;
    }

    private function generateNumeroMovReserva(int $sucursal_id): string
    {
        // Obtener el último movimiento asociado a movreserva
        // en esa sucursal, ordenado por creación real
        $ultimoMovimiento = Movreserva::join(
            'movimiento',
            'movreserva.movimiento_id',
            '=',
            'movimiento.id'
        )
            ->where('movreserva.sucursal_id', $sucursal_id)
            ->whereNull('movreserva.deleted_at')
            ->whereNull('movimiento.deleted_at')
            ->orderBy('movreserva.created_at', 'desc') // orden REAL
            ->orderBy('movreserva.id', 'desc')
            ->select('movimiento.numero')
            ->first();

        // Extraer número actual
        $ultimoNumero = $ultimoMovimiento
            ? (int) $ultimoMovimiento->numero
            : 0;

        // Incrementar y formatear a 8 dígitos
        return str_pad($ultimoNumero + 1, 8, '0', STR_PAD_LEFT);
    }

    private function generateNumeroMovHabitacion(int $sucursal_id): string
    {
        // Obtener el último movimiento asociado a movhabitacion
        // en esa sucursal, ordenado por creación real
        $ultimoMovimiento = \App\Models\Movhabitacion::join(
            'movimiento',
            'movhabitacion.movimiento_id',
            '=',
            'movimiento.id'
        )
            ->where('movhabitacion.sucursal_id', $sucursal_id)
            ->whereNull('movhabitacion.deleted_at')
            ->whereNull('movimiento.deleted_at')
            ->orderBy('movhabitacion.created_at', 'desc') // orden REAL
            ->orderBy('movhabitacion.id', 'desc')
            ->select('movimiento.numero')
            ->first();

        // Extraer número actual
        $ultimoNumero = $ultimoMovimiento
            ? (int) $ultimoMovimiento->numero
            : 0;

        // Incrementar y formatear
        return str_pad($ultimoNumero + 1, 8, '0', STR_PAD_LEFT);
    }

    private function generateNumeroMovcaja($sucursal_id, $caja_id, $conceptopago_id = null)
    {

        // Obtener el último número registrado en la sucursal y caja específicas
        $ultimoMovimiento = Movcaja::join('movimiento', 'movcaja.movimiento_id', '=', 'movimiento.id')
            ->where('movcaja.sucursal_id', $sucursal_id)
            ->where('movcaja.conceptopago_id', $conceptopago_id)
            ->where('movcaja.caja_id', $caja_id)

            ->orderBy('movimiento.numero', 'desc')
            ->select('movimiento.numero')
            ->first();

        // Extraer el número y convertirlo a entero
        $ultimoNumero = $ultimoMovimiento ? (int) $ultimoMovimiento->numero : 0;

        // Incrementar el número en 1 y formatearlo con ceros iniciales
        $nuevoNumero = str_pad($ultimoNumero + 1, 8, '0', STR_PAD_LEFT);

        return $nuevoNumero;
    }
}
