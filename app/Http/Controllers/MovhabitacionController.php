<?php

namespace App\Http\Controllers;

use App\Models\Housekeeping;
use App\Models\HousekeepingDetalle;
use App\Models\Movhabitacion;
use App\Models\Banco;
use App\Models\Billeteradigital;
use App\Models\Caja;
use App\Models\Categorium;
use App\Models\DetMovcaja;
use App\Models\DetMovventum;
use App\Models\DetMovhabitacion;
use App\Models\Facturador;
use App\Models\Movreserva;
use Carbon\Carbon;
use App\Models\MovhabitacionHabitacion;
use Barryvdh\Snappy\Facades\SnappyPdf as Pdf;
use App\Models\Habitacion;
use App\Models\HistorialHabitacion;
use App\Models\Huesped;
use App\Models\Igv;
use App\Models\Mediopago;
use App\Models\Movcaja;
use App\Models\Movimiento;
use App\Models\MovreservaHabitacion;
use App\Models\Movventum;
use App\Models\Operacione;
use App\Models\Pasarelapago;
use App\Models\Persona;
use App\Models\Piso;
use App\Models\Producto;
use App\Models\RelParametrosucursal;
use App\Models\RelProductosucursal;
use App\Models\RelTurnocaja;
use App\Models\Stockproducto;
use App\Models\Sucursal;
use App\Models\Tarjetum;
use App\Models\Tipodocumento;
use App\Models\Tipohabitacion;
use App\Models\Turno;
use App\Models\Unidad;
use App\Services\HabitacionAvailability;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Services\CorrelativoService;

/**
 * Class MovhabitacionController
 * @package App\Http\Controllers
 */
class MovhabitacionController extends Controller
{
    /**
     * Reservas con fechavencimiento hoy: padre PENDIENTE, o grupo (G) en EN ATENCIÓN con al menos una línea MRH pendiente (check-in parcial).
     */
    private function obtenerMovreservasHoyParaControlHabitaciones()
    {
        return Movreserva::whereDate('fechavencimiento', now())
            ->whereHas('movimiento', function ($query) {
                $query->where('situacion', 'A');
            })
            ->where(function ($q) {
                $q->where('estado', 'PENDIENTE')
                    ->orWhere(function ($q2) {
                        $q2->where('estado', 'EN ATENCIÓN')
                            ->where('modo', 'G')
                            ->whereHas('movreserva_habitacion', function ($q3) {
                                $q3->where('situacion', 'PENDIENTE');
                            });
                    });
            })
            ->with('movreserva_habitacion.habitacion')
            ->get();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);

            // Obtener habitaciones y pisos
            $Habitaciones = Habitacion::where('sucursal_id', $persona->sucursal_id)->orderBy('numero')->get();
            $Pisos = Piso::where('sucursal_id', $persona->sucursal_id)->orderBy('id')->get();
            $Categorias = Tipohabitacion::where('sucursal_id', $persona->sucursal_id)->orderBy('id')->get();

            // Agregar detalles por piso
            $Pisos = $Pisos->map(function ($piso) use ($Habitaciones) {
                $piso->habitacionesOcupadas = $Habitaciones->where('piso_id', $piso->id)->where('situacion', 'Ocupada')->count();
                $piso->habitacionesDisponibles = $Habitaciones->where('piso_id', $piso->id)->where('situacion', 'Disponible')->count();
                $piso->habitacionesMantenimiento = $Habitaciones->where('piso_id', $piso->id)->where('situacion', 'Mantenimiento')->count();
                $piso->habitacionesLimpieza = $Habitaciones->where('piso_id', $piso->id)->where('situacion', 'Limpieza')->count();

                return $piso;
            });

            // Totales generales para "General"
            $habitacionesOcupadasGeneral = $Habitaciones->where('situacion', 'Ocupada')->count();
            $habitacionesDisponiblesGeneral = $Habitaciones->where('situacion', 'Disponible')->count();
            $habitacionesMantenimientoGeneral = $Habitaciones->where('situacion', 'Mantenimiento')->count();
            $habitacionesLimpiezaGeneral = $Habitaciones->where('situacion', 'Limpieza')->count();

            // Obtener movimientos de habitaciones ocupadas CON su detalle
            $movHabitacionesOcupadas = Movhabitacion::whereHas('movhabitacion_habitacion.habitacion', function ($query) {
                $query->where('situacion', 'Ocupada');
            })

                ->where('sucursal_id', $persona->sucursal_id)
                ->where('situacion', '!=', 'C') // No canceladas
                ->where('situacion', '!=', 'D') // No canceladas

                ->with([
                    'movhabitacion_habitacion.habitacion', // Relación con detalle y habitación
                    'huespeds.persona' // Relación con huéspedes
                ])
                ->get();

            // Lista de habitaciones ocupadas (separadas por detalle de movhabitacion)
            $movimientosDetalle = collect();

            foreach ($movHabitacionesOcupadas as $movimiento) {
                foreach ($movimiento->movhabitacion_habitacion as $detalle) {
                    $movimientosDetalle->push([
                        'movhabitacion_id' => $movimiento->id,
                        'habitacion_id' => $detalle->habitacion_id,
                        'habitacion' => $detalle->habitacion->numero ?? 'N/A',
                        'fechaingreso' => $movimiento->fechaingreso,
                        'fechasalida' => $movimiento->fechasalida,
                        'total' => $movimiento->total,
                        'situacion' => $detalle->situacion,
                        'tarifa' => $detalle->tarifa,
                        'comentario' => $movimiento->comentario,
                        'principal' => $detalle->principal,
                        'cliente' => optional($movimiento->huespeds->first()?->persona)->nombres . ' ' .
                            optional($movimiento->huespeds->first()?->persona)->apellidos
                    ]);
                }
            }

            // Agrupar clientes por habitación
            $clientesPorHabitacion = [];
            foreach ($movimientosDetalle as $movimiento) {
                $clientesPorHabitacion[$movimiento['habitacion_id']] = $movimiento['cliente'] ?: ' ';
            }


            //dd( $movHabitacionesOcupadas);
            // Agrupar las horas por habitación
            $horasPorHabitacion = $movimientosDetalle->map(function ($mov) {
                return [
                    'habitacion_id' => $mov['habitacion_id'],
                    'hora_inicio' => Carbon::parse($mov['fechaingreso'])->format('Y-m-d H:i:s'),
                    'hora_fin' => Carbon::parse($mov['fechasalida'])->format('Y-m-d H:i:s'),
                    'situacion' => $mov['situacion'],
                ];
            });



            // Obtener reservas del día (incluye grupo en atención con habitaciones aún pendientes)
            $movreservahoy = $this->obtenerMovreservasHoyParaControlHabitaciones();
            // Operaciones de pasarelapago
            $operaciones = Operacione::where('tabla_id', 68)
                ->where('estado', true)
                ->get();
            $usuario = Auth::user();
            $personasucursal = Persona::find($usuario->persona_id);
            $sucursal = Sucursal::find($personasucursal->sucursal_id);
            $modalidadtarifario = RelParametroSucursal::where('sucursal_id', $sucursal->id)
                ->whereHas('parametro', function ($query) {
                    $query->where('descripcion', 'Modalidad tarifario');
                })
                ->value('valor'); // esto devuelve directamente un string como 'Si' o 'No'

            $vista = RelParametroSucursal::where('sucursal_id', $sucursal->id)
                ->whereHas('parametro', function ($query) {
                    $query->where('descripcion', 'Vista control de habitaciones');
                })
                ->value('valor');

            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal = Sucursal::find($persona->sucursal_id);
            $caja = Caja::where('situacion', 'A')->where('sucursal_id', $sucursal->id)->first();
            $cajaId = $caja->id ?? null;
            // Obtener el último turno activo o cerrado para la caja
            $turnoCaja = RelTurnocaja::where('situacion', 'A') // Turno activo
                ->whereHas('movcajainicio', function ($query) use ($cajaId) {
                    $query->where('caja_id', $cajaId);
                })
                ->latest('fechainicio') // Último turno
                ->first();

            // La vista de habitaciones permanece disponible con la caja cerrada.
            // Las acciones que generan movimientos se bloquean desde app.js.
            $cajaAbierta = (bool) $turnoCaja;

            if (!$turnoCaja) {
                $totales = ['ventas' => 0];
            }

            // Obtener movimientos con detalles
            $movimientos = $turnoCaja ? Movcaja::with(['detMovcaja.Mediopago', 'conceptopago'])
                ->where('id', '>=', $turnoCaja->movcajainicio_id) // Desde el inicio del turno
                ->where('caja_id', $cajaId)
                ->whereHas('movimiento', function ($query) {
                    $query->where('situacion', 'A'); // Filtrar movimientos con situación 'A'
                })
                ->orderBy('created_at', 'asc')
                ->get() : collect();


            $totales = $turnoCaja ? [


                'ventas' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->conceptopago_id == 4;
                })->flatMap->detMovcaja->where('tipo', 'Pagado')->sum('monto'),



            ] : ($totales ?? ['ventas' => 0]);
            // dd($totales);
            DB::commit();
            if ($vista === 'Normal') {
                return view('movhabitacion.index', compact(
                    'Habitaciones',
                    'operaciones',
                    'Pisos',
                    'Categorias',
                    'habitacionesOcupadasGeneral',
                    'habitacionesDisponiblesGeneral',
                    'habitacionesMantenimientoGeneral',
                    'habitacionesLimpiezaGeneral',
                    'horasPorHabitacion',
                    'movimientosDetalle',
                    'clientesPorHabitacion',
                    'modalidadtarifario',
                    'movreservahoy',
                    'movHabitacionesOcupadas',
                    'totales',
                    'cajaAbierta'
                ));
            } else {
                return view('movhabitacion.indexv2', compact(
                    'Habitaciones',
                    'operaciones',
                    'Pisos',
                    'Categorias',
                    'habitacionesOcupadasGeneral',
                    'habitacionesDisponiblesGeneral',
                    'habitacionesMantenimientoGeneral',
                    'habitacionesLimpiezaGeneral',
                    'horasPorHabitacion',
                    'movimientosDetalle',
                    'clientesPorHabitacion',
                    'modalidadtarifario',
                    'movreservahoy',
                    'movHabitacionesOcupadas',
                    'totales',
                    'cajaAbierta'
                ));
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
                'redirect_url' => route('movhabitacion.index'),
            ], 500);
        }
    }

    public function generarCodigoAprobacionAnulacion(Request $request)
    {
        $correoSanitizado = $this->normalizeCorreoAdmin($request->input('correo'));
        $request->merge(['correo' => $correoSanitizado]);

        $request->validate([
            'telefonos' => 'nullable|string',
            'telefonos.*' => 'nullable|numeric',
            'correo' => 'nullable|email',
            'send_type' => 'required|string',
            'comentario' => 'nullable|string',
            'movhabitacion_id' => 'nullable|integer',
            'habitacion_numero' => 'nullable|string',
        ]);

        $sendType = $request->input('send_type');
        $telefonos = $request->input('telefonos', []);
        $correoRaw = $request->input('correo');
        $correo = $this->normalizeCorreoAdmin($correoRaw) ?: Auth::user()->email;
        $codigo = rand(100000, 999999);
        $comentario = $request->input('comentario', '');
        $movhabitacionId = $request->input('movhabitacion_id');
        $habitacionNumero = $request->input('habitacion_numero', '');

        if (!$habitacionNumero && $movhabitacionId) {
            $detalle = MovhabitacionHabitacion::with('habitacion')
                ->where('movhabitacion_id', $movhabitacionId)
                ->orderByDesc('principal')
                ->first();
            $habitacionNumero = $detalle?->habitacion?->numero ?? '';
        }

        if (is_string($telefonos)) {
            $decoded = json_decode($telefonos, true);
            $telefonos = is_array($decoded) ? $decoded : [$telefonos];
        } elseif (!is_array($telefonos)) {
            $telefonos = $telefonos === null ? [] : [(string) $telefonos];
        }

        if ($sendType === 'whatsapp' && !empty($telefonos)) {
            $resultados = [];
            foreach ($telefonos as $telefono) {
                $res = $this->enviarWhatsAppAnulacionAlojamiento(
                    $codigo,
                    $telefono,
                    $comentario,
                    $habitacionNumero,
                    $movhabitacionId
                );
                $resultados[] = [
                    'telefono' => $telefono,
                    'respuesta' => $res->original ?? $res,
                ];
            }

            return response()->json([
                'success' => true,
                'codigo' => $codigo,
                'send_type' => 'whatsapp',
                'destinatarios' => $telefonos,
                'message' => 'Codigos enviados por WhatsApp a: ' . implode(', ', $telefonos),
                'detalles' => $resultados,
                'detalle' => [
                    'tipo_envio' => 'whatsapp',
                    'destinatarios' => $telefonos,
                    'habitacion_numero' => $habitacionNumero,
                    'movhabitacion_id' => $movhabitacionId,
                    'comentario' => $comentario,
                    'envios' => $resultados,
                ],
            ]);
        }

        if ($sendType === 'email' && $correo) {
            return $this->enviarCorreoAnulacionAlojamiento(
                $codigo,
                $correo,
                $habitacionNumero,
                $comentario,
                $movhabitacionId
            );
        }

        return response()->json([
            'success' => false,
            'send_type' => $sendType,
            'message' => 'Tipo de envio no valido o falta numero de telefono/correo.',
            'detalle' => [
                'send_type' => $sendType,
                'telefonos' => $telefonos,
                'correo_raw' => $correoRaw,
                'correo_normalizado' => $correo,
            ],
        ], 400);
    }

    private function enviarWhatsAppAnulacionAlojamiento($codigo, $telefono, $comentario, $habitacionNumero = '', $movhabitacionId = null)
    {
        $auth_token = env('META_AUTH_TOKEN');
        $from = "202127742990342";
        $apiVersion = "v19.0";
        $sucursal = Sucursal::find(Auth::user()->persona->sucursal_id);

        try {
            $client = new Client();

            if (!preg_match('/^\+?51/', $telefono)) {
                $telefono = '51' . ltrim($telefono, '0');
            }

            $header = [
                ['type' => 'text', 'text' => 'HotelHub | Anulacion de alojamiento'],
            ];

            $detalleTexto = 'Detalle de anulacion';
            if ($habitacionNumero) {
                $detalleTexto .= ": Habitacion {$habitacionNumero}";
            }
            if ($movhabitacionId) {
                $detalleTexto .= " | Alojamiento #{$movhabitacionId}";
            }

            $body = [
                ['type' => 'text', 'text' => $sucursal->nombrecomercial ?? 'HotelHub'],
                ['type' => 'text', 'text' => Auth::user()->name ?? 'Usuario'],
                ['type' => 'text', 'text' => (string) $codigo],
                ['type' => 'text', 'text' => '5 minuto(s)'],
                ['type' => 'text', 'text' => "Accion: Anulacion | Tipo: Alojamiento | Motivo: {$comentario}"],
                ['type' => 'text', 'text' => $detalleTexto],
                ['type' => 'text', 'text' => 'HotelHub - Soluciones inteligentes para negocios'],
            ];

            $fields = [
                'messaging_product' => "whatsapp",
                'to' => $telefono,
                'type' => 'template',
                'template' => [
                    'name' => 'protected_actions',
                    'language' => ['code' => 'es'],
                    'components' => [
                        ['type' => 'header', 'parameters' => $header],
                        ['type' => 'body', 'parameters' => $body],
                    ],
                ],
            ];

            $sendResponse = $client->request('POST', "https://graph.facebook.com/$apiVersion/$from/messages", [
                'headers' => [
                    'Authorization' => "Bearer $auth_token",
                    'Content-Type' => 'application/json',
                ],
                'json' => $fields,
            ]);

            $data = json_decode($sendResponse->getBody(), true);

            if (empty($data['messages']) || ($data['messages'][0]['status'] ?? '') !== 'sent') {
                return response()->json([
                    'success' => false,
                    'send_type' => 'whatsapp',
                    'destinatario' => $telefono,
                    'message' => 'Mensaje no aceptado por WhatsApp',
                    'errors' => 'Mensaje no aceptado por WhatsApp',
                    'detail' => $data,
                ], 500);
            }

            return response()->json([
                'success' => true,
                'send_type' => 'whatsapp',
                'destinatario' => $telefono,
                'message' => 'Codigo enviado exitosamente por WhatsApp.',
                'detail' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'send_type' => 'whatsapp',
                'destinatario' => $telefono,
                'message' => 'Error al enviar codigo por WhatsApp: ' . $e->getMessage(),
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    private function enviarCorreoAnulacionAlojamiento($codigo, $correo, $habitacionNumero = '', $comentario = '', $movhabitacionId = null)
    {
        $detalle = 'Anulacion de alojamiento';
        if ($habitacionNumero) {
            $detalle .= " | Habitacion {$habitacionNumero}";
        }
        if ($movhabitacionId) {
            $detalle .= " | Alojamiento #{$movhabitacionId}";
        }

        $texto = "Codigo de aprobacion: $codigo\nDetalle: $detalle\nMotivo: $comentario";

        $fields = [
            'token' => config('services.comprobantes_mail.token'),
            "to" => [$correo],
            "bcc" => [],
            "cc" => [],
            "subject" => "Codigo de Aprobacion - Anulacion de Alojamiento",
            "body" => $texto,
            "attachments" => [],
        ];

        $client = new Client();
        try {
            $response = $client->post("https://facturae-garzasoft.com/facturacion/enviaEmail/envia_correo_json.php", [
                'json' => $fields,
            ]);

            $body = (string) $response->getBody();
            if ($response->getStatusCode() !== 200) {
                return response()->json([
                    'success' => false,
                    'codigo' => null,
                    'send_type' => 'email',
                    'destinatario' => $correo,
                    'message' => 'Error al enviar correo: HTTP ' . $response->getStatusCode(),
                    'detail' => $body,
                    'detalle' => [
                        'tipo_envio' => 'email',
                        'destinatario' => $correo,
                        'habitacion_numero' => $habitacionNumero,
                        'movhabitacion_id' => $movhabitacionId,
                        'comentario' => $comentario,
                        'error' => $body,
                    ],
                ], $response->getStatusCode());
            }

            $datos = json_decode($body);
            if (!isset($datos->code) || $datos->code != 0) {
                return response()->json([
                    'success' => false,
                    'codigo' => null,
                    'send_type' => 'email',
                    'destinatario' => $correo,
                    'message' => 'Error al enviar correo: ' . ($datos->message ?? 'Respuesta invalida del servidor de correo'),
                    'detail' => $body,
                    'detalle' => [
                        'tipo_envio' => 'email',
                        'destinatario' => $correo,
                        'habitacion_numero' => $habitacionNumero,
                        'movhabitacion_id' => $movhabitacionId,
                        'comentario' => $comentario,
                        'respuesta_proveedor' => $datos,
                    ],
                ], 500);
            }

            return response()->json([
                'success' => true,
                'codigo' => $codigo,
                'send_type' => 'email',
                'destinatario' => $correo,
                'message' => "Correo enviado exitosamente a {$correo}.",
                'detalle' => [
                    'tipo_envio' => 'email',
                    'destinatario' => $correo,
                    'habitacion_numero' => $habitacionNumero,
                    'movhabitacion_id' => $movhabitacionId,
                    'comentario' => $comentario,
                    'respuesta_proveedor' => $datos,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'codigo' => null,
                'send_type' => 'email',
                'destinatario' => $correo,
                'message' => 'Ocurrio un error al enviar el correo: ' . $e->getMessage(),
                'detail' => $e->getMessage(),
                'detalle' => [
                    'tipo_envio' => 'email',
                    'destinatario' => $correo,
                    'habitacion_numero' => $habitacionNumero,
                    'movhabitacion_id' => $movhabitacionId,
                    'comentario' => $comentario,
                    'error_exception' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    private function normalizeCorreoAdmin($correoRaw): ?string
    {
        if (!$correoRaw) {
            return null;
        }

        $sanitized = str_replace(['&quot;', '\u0022'], '"', trim($correoRaw));
        $sanitized = trim($sanitized, "\"' ");
        $sanitized = preg_replace('/\\s+/', ' ', $sanitized);

        if (str_contains($sanitized, ',')) {
            $partes = array_filter(array_map('trim', explode(',', $sanitized)));
            return $partes[0] ?? null;
        }

        return $sanitized ?: null;
    }

    private function obtenerFechaFinReserva(MovreservaHabitacion $detalleReserva, Carbon $inicioReserva): Carbon
    {
        if (!empty($detalleReserva->fechacheckout)) {
            return Carbon::parse($detalleReserva->fechacheckout);
        }

        $reserva = $detalleReserva->movreserva;
        if ($reserva && !empty($reserva->fechasalida)) {
            return Carbon::parse($reserva->fechasalida);
        }

        $dias = (int) ($reserva->dias ?? 0);
        return $inicioReserva->copy()->addDays(max($dias, 1));
    }

    private function validarCruceConReservasActivas(array $habitacionesIds, $fechaIngreso, $fechaSalida): void
    {
        $inicioCheckin = Carbon::parse($fechaIngreso);
        $finCheckin = Carbon::parse($fechaSalida);

        if ($finCheckin->lessThanOrEqualTo($inicioCheckin)) {
            throw new \Exception('La fecha/hora de salida debe ser mayor a la de ingreso.');
        }

        $detallesReserva = MovreservaHabitacion::with(['movreserva.movimiento', 'habitacion'])
            ->whereIn('habitacion_id', $habitacionesIds)
            ->whereIn('situacion', ['PENDIENTE', 'EN ATENCIÓN'])
            ->whereHas('movreserva.movimiento', function ($query) {
                $query->where('situacion', 'A');
            })
            ->get();

        $habitacionesCruzadas = [];
        foreach ($detallesReserva as $detalleReserva) {
            $inicioReservaRaw = $detalleReserva->fechacheckin
                ?? optional($detalleReserva->movreserva)->fechavencimiento
                ?? optional($detalleReserva->movreserva)->fechainicio;

            if (empty($inicioReservaRaw)) {
                continue;
            }

            $inicioReserva = Carbon::parse($inicioReservaRaw);
            $finReserva = $this->obtenerFechaFinReserva($detalleReserva, $inicioReserva);
            $hayCruce = $inicioCheckin->lt($finReserva) && $finCheckin->gt($inicioReserva);

            if ($hayCruce) {
                $numeroHabitacion = optional($detalleReserva->habitacion)->numero ?? $detalleReserva->habitacion_id;
                $habitacionesCruzadas[(string) $numeroHabitacion] = true;
            }
        }

        if (!empty($habitacionesCruzadas)) {
            $listaHabitaciones = implode(', ', array_keys($habitacionesCruzadas));
            throw new \Exception('No se puede registrar el check-in directo. Existe una reserva cruzada en: ' . $listaHabitaciones);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {


            // Convertir 'acompanantes' en un arreglo si llega como cadena JSON
            $acompanantes = $request->input('acompanantes');
            if (!is_array($acompanantes)) {
                $acompanantes = json_decode($acompanantes, true);
            }
            $usuario = Auth::user();
            $personasucursal = Persona::find($usuario->persona_id);
            $sucursal = Sucursal::find($personasucursal->sucursal_id);
            // Asegurarte de que 'acompanantes' siempre sea un arreglo válido
            $request->merge(['acompanantes' => $acompanantes ?: []]);

            $jsonHabitaciones = json_decode($request->detalles_habitaciones_json, true);
            $habitacionPrincipal = count($jsonHabitaciones) > 0 ? $jsonHabitaciones[0]['id'] : null;

            $habitacionesSeleccionadas = collect($jsonHabitaciones ?? [])
                ->pluck('id')
                ->filter()
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values();

            if ($habitacionesSeleccionadas->isEmpty()) {
                throw new \Exception('Debe seleccionar al menos una habitacion para realizar el check-in.');
            }

            // Evita doble check-in concurrente: bloquea filas de habitaciones y valida ocupacion activa.
            $habitacionesBloqueadas = Habitacion::whereIn('id', $habitacionesSeleccionadas->all())
                ->lockForUpdate()
                ->get();

            if ($habitacionesBloqueadas->count() !== $habitacionesSeleccionadas->count()) {
                throw new \Exception('Una o mas habitaciones seleccionadas no existen o no estan disponibles.');
            }

            $habitacionesOcupadasIds = MovhabitacionHabitacion::whereIn('habitacion_id', $habitacionesSeleccionadas->all())
                ->whereIn('situacion', ['A', 'P'])
                ->whereHas('movhabitacion', function ($query) {
                    $query->whereIn('situacion', ['A', 'P']);
                })
                ->lockForUpdate()
                ->pluck('habitacion_id')
                ->unique()
                ->values();

            if ($habitacionesOcupadasIds->isNotEmpty()) {
                $habitacionesOcupadasTexto = Habitacion::whereIn('id', $habitacionesOcupadasIds->all())
                    ->pluck('numero')
                    ->implode(', ');
                throw new \Exception('No se puede registrar el check-in. Habitaciones ya ocupadas: ' . $habitacionesOcupadasTexto);
            }

            $personaPrincipal = Persona::find($request->persona_principal);
            if ($personaPrincipal->no_grato) {
                throw new \Exception('La persona seleccionada como huésped principal está marcada como no grata.');
            }

            // Buscar caja aperturada para la sucursal
            $caja = Caja::where('situacion', 'A')->where('sucursal_id', $sucursal->id)->first();

            if (!$caja) {

                throw new \Exception('No hay ninguna caja aperturada en esta sucursal.');
            }

            $cajaId = $caja->id;

            // Obtener el último turno activo o cerrado para la caja
            $ultimoTurno = RelTurnocaja::where('situacion', 'A') // Turno activo
                ->whereHas('movcajainicio', function ($query) use ($cajaId) {
                    $query->where('caja_id', $cajaId);
                })
                ->latest('fechainicio')
                ->first();

            // Crear el movimiento principal
            $movimiento = Movimiento::create([
                'numero' => $this->generateNumeroMovHabitacion(Auth::user()->persona->sucursal_id),
                'fecha' => now(),
                'usuario_id' => Auth::id(),
                'usuario' => Auth::user()->name,
                'persona_id' => $request->persona_principal,
                'persona' => Persona::find($request->persona_principal)->nombres . ' ' . Persona::find($request->persona_principal)->apellidos,
                'responsable_id' => Auth::user()->persona_id,
                'responsable' => Auth::user()->persona->nombres . ' ' . Auth::user()->persona->apellidos,
                'comentario' => $request->comentario ?? '',
                'situacion' => 'A',
                'tipomovimiento_id' => 8,
                'tipodocumento_id' => 11,
                'sucursal_id' => Auth::user()->persona->sucursal_id,
            ]);


            $total = 0;
            $modalidad = '';
            $dias = null;
            $horas = null;

            if ($request->switch_state === 'dias') {
                // Modalidad días ('D')
                $dias = $request->dias_totales;
                $modalidad = 'D';
            } elseif ($request->switch_state === 'horas') {
                // Modalidad horas ('H')
                $horas = $request->horas_totales;

                $modalidad = 'H';
            }
            // Obtener la fecha y hora actual
            $fechaActual = now(); // Fecha y hora actual
            $fechaIngreso = $fechaActual->toDateString(); // Fecha actual
            $horaIngreso = $fechaActual->toTimeString(); // Hora actual

            // Verificar la modalidad
            if ($modalidad === 'D') {
                // Modalidad 'D' (días)
                $fechaIngreso = $request->fechaingreso;
                $fechaSalida = $request->fechasalida;
            } else {
                // Modalidad 'H' (horas)
                // Sumar las horas al tiempo actual
                $horaingreso = Carbon::parse($request->horaingreso);
                $horasalida = Carbon::parse($request->horasalida);

                if ($horaingreso > $horasalida) {
                    // Si la hora de ingreso es mayor, la salida es al día siguiente
                    $fechaSalida = $fechaActual->copy()->addDay()->setTimeFrom($horasalida);
                } else {
                    // Si no, simplemente se usa la misma fecha
                    $fechaSalida = $fechaActual->copy()->setTimeFrom($horasalida);
                }

                // La fecha de ingreso es la fecha y hora actuales
                $fechaIngreso = $fechaActual->toDateTimeString(); // Fecha y hora actual
            }

            $this->validarCruceConReservasActivas($habitacionesSeleccionadas->all(), $fechaIngreso, $fechaSalida);

            $movhabitacion = Movhabitacion::create([
                'descuento' => 0,
                'fechaingreso' => $fechaIngreso,
                'fechasalida' => $fechaSalida,
                'dias' => $modalidad === 'D' ? $dias : null,
                'horas' => $modalidad === 'H' ? $horas : null,
                'modalidad' => $modalidad,
                'total' => $request->total,
                'preciohabitacion' => $request->precioHabitacion,
                'situacion' => 'A',
                'comentario' => $request->comentario,
                'moneda' => 'PEN',
                'habitacion_id' => $habitacionPrincipal,
                'movimiento_id' => $movimiento->id,
                'sucursal_id' => Auth::user()->persona->sucursal_id,
            ]);

            if ($movhabitacion) {
                $contadorDetalles = 0; // 🔹 contador de habitaciones registradas

                foreach ($jsonHabitaciones as $index => $habitacion) {
                    $habitacionId = $habitacion['id'];
                    $tarifa = $request->tarifa_habitacion[$index] ?? $habitacion['precio'] ?? 0;

                    DB::table('movhabitacion_habitacion')->insert([
                        'movhabitacion_id' => $movhabitacion->id,
                        'habitacion_id' => $habitacionId,
                        'fechacheckin' => now(),
                        'modalidad' => $modalidad,
                        'dias' => $modalidad === 'D' ? $dias : null,
                        'horas' => $modalidad === 'H' ? $horas : null,
                        'principal' => $index === 0,
                        'tarifa' => $tarifa,
                        'total' => $tarifa * ($modalidad === 'D' ? $dias : 1),
                        'json_habitacion' => json_encode($habitacion, JSON_UNESCAPED_UNICODE),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $contadorDetalles++; // 🔹 Incrementar cada vez que se inserta un detalle

                    // Actualizar la habitación como ocupada
                    $habitacionModel = Habitacion::find($habitacionId);
                    if ($habitacionModel) {
                        $habitacionModel->situacion = 'Ocupada';
                        $habitacionModel->save();
                    }
                }

                // 🔹 Actualizar el campo "modo" según la cantidad de detalles
                $movhabitacion->modo = $contadorDetalles > 1 ? 'G' : 'U';
                $movhabitacion->save();
            }



            HistorialHabitacion::create([
                'sucursal_id' => Auth::user()->persona->sucursal_id,
                'movhabitacion_id' => $movhabitacion->id,
                'relturnocaja_id' => $ultimoTurno->id,
                'situacion_pago' => 'N',
                'situacion_checkout' => 'N',

            ]);

            // Registrar al pasajero principal como huésped
            Huesped::create([
                'persona_id' => $request->persona_principal,
                'movhabitacion_id' => $movhabitacion->id,
                'habitacion_id' => $movhabitacion->habitacion_id,
            ]);

            // Registrar acompañantes como huéspedes
            $acompanantesRaw = $request->input('acompanantes', []);

            if (!is_array($acompanantesRaw)) {
                $acompanantesRaw = json_decode($acompanantesRaw, true) ?? [];
            }

            $acompanantes = [];

            foreach ($acompanantesRaw as $item) {
                if (is_int($item)) {
                    // Caso: [2808]
                    $acompanantes[] = [
                        'id' => $item,
                        'habitacion_id' => $habitacionPrincipal
                    ];
                } elseif (is_array($item) && isset($item['id'])) {
                    // Caso futuro: [{id: 2808, habitacion_id: 73}]
                    $acompanantes[] = [
                        'id' => $item['id'],
                        'habitacion_id' => $item['habitacion_id'] ?? $habitacionPrincipal
                    ];
                }
            }

            $request->merge(['acompanantes' => $acompanantes]);

            $habitacionAcompanantes = $request->input('habitacion_acompanantes', []);
            $acompanantesInsertados = [];

            foreach ($acompanantes as $acompanante) {
                $personaId = null;
                $habitacionId = $habitacionPrincipal;

                if (is_numeric($acompanante)) {
                    $personaId = (int) $acompanante;
                } elseif (is_array($acompanante)) {
                    $personaId = isset($acompanante['id']) ? (int) $acompanante['id'] : null;
                    $habitacionId = $acompanante['habitacion_id'] ?? $habitacionId;
                }

                if (!$personaId || $personaId === (int) $request->persona_principal) {
                    continue;
                }

                if (is_array($habitacionAcompanantes) && isset($habitacionAcompanantes[$personaId])) {
                    $habitacionId = $habitacionAcompanantes[$personaId];
                }

                $key = $personaId . '-' . $habitacionId;
                if (isset($acompanantesInsertados[$key])) {
                    continue;
                }

                Huesped::create([
                    'persona_id' => $personaId,
                    'movhabitacion_id' => $movhabitacion->id,
                    'habitacion_id' => $habitacionId,
                ]);

                $acompanantesInsertados[$key] = true;
            }



            session()->forget('acompanantes');
            session()->forget('principal');

            DB::commit();
            // Obtener los números de habitación desde el JSON recibido
            $numerosHabitaciones = collect($jsonHabitaciones)
                ->pluck('numero') // extrae el campo numero
                ->filter()        // elimina nulos o vacíos
                ->values();       // reindexa

            // Contar cuántas habitaciones hay
            $cantidad = $numerosHabitaciones->count();

            // Generar mensaje dinámico
            if ($cantidad === 1) {
                $mensaje = 'Check-In registrado exitosamente en la habitación ' . $numerosHabitaciones->first();
            } elseif ($cantidad > 1) {
                $mensaje = 'Check-In registrado exitosamente en las habitaciones ' . $numerosHabitaciones->implode(', ');
            } else {
                $mensaje = 'Check-In registrado exitosamente.';
            }

            // Enviar respuesta final
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'redirect_url' => route('movhabitacion.index'),
                'habitaciones' => $numerosHabitaciones,
                'habitacion_id' => $habitacionPrincipal
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
                'redirect_url' => route('movhabitacion.index'),
            ], 500);
        }
    }
    public function updatecheckin(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $acompanantes = $request->input('acompanantes', []);
            if (!is_array($acompanantes)) {
                $acompanantes = json_decode($acompanantes, true) ?? [];
            }

            $habitacionReferencia = Habitacion::findOrFail($request->habitacion_id ?? $id);
            $movhabitacion = Movhabitacion::whereHas('movhabitacion_habitacion', function ($query) use ($habitacionReferencia) {
                $query->where('habitacion_id', $habitacionReferencia->id);
            })
                ->whereIn('situacion', ['A', 'P'])
                ->with('movhabitacion_habitacion')
                ->firstOrFail();

            $movimiento = $movhabitacion->movimiento;
            $detallesActuales = MovhabitacionHabitacion::where('movhabitacion_id', $movhabitacion->id)->get()->keyBy('habitacion_id');
            $idsActuales = $detallesActuales->keys()->map(fn($idActual) => (int) $idActual)->values()->all();

            $personaPrincipal = Persona::findOrFail($request->persona_principal);
            if ($personaPrincipal->no_grato) {
                throw new \Exception('La persona seleccionada como huesped principal esta marcada como no grata.');
            }

            $detallesHabitacionesJson = json_decode($request->detalles_habitaciones_json, true);
            if (!is_array($detallesHabitacionesJson) || empty($detallesHabitacionesJson)) {
                throw new \Exception('Debe seleccionar al menos una habitacion para actualizar el check-in.');
            }

            $habitacionesSeleccionadas = collect($detallesHabitacionesJson)
                ->map(function ($habitacion) {
                    return [
                        'id' => (int) ($habitacion['id'] ?? 0),
                        'numero' => $habitacion['numero'] ?? '',
                        'tipo' => $habitacion['tipo'] ?? '',
                        'situacion' => $habitacion['situacion'] ?? '',
                        'precio' => (float) ($habitacion['precio'] ?? 0),
                    ];
                })
                ->filter(fn($habitacion) => $habitacion['id'] > 0)
                ->unique('id')
                ->values();

            if ($habitacionesSeleccionadas->isEmpty()) {
                throw new \Exception('No se encontraron habitaciones validas para actualizar.');
            }

            $tarifasRequest = $request->input('tarifa_habitacion', []);
            $habitacionesSeleccionadas = $habitacionesSeleccionadas->map(function ($habitacion, $index) use ($tarifasRequest) {
                $tarifa = $tarifasRequest[$index] ?? $habitacion['precio'];
                $habitacion['precio'] = is_numeric($tarifa) ? (float) $tarifa : 0;
                return $habitacion;
            })->values();

            $idsNuevas = $habitacionesSeleccionadas->pluck('id')->map(fn($idNueva) => (int) $idNueva)->values()->all();
            $idsAgregadas = array_values(array_diff($idsNuevas, $idsActuales));
            $idsEliminadas = array_values(array_diff($idsActuales, $idsNuevas));
            $habitacionPrincipalId = (int) $idsNuevas[0];

            if (!empty($idsAgregadas)) {
                $habitacionesBloqueadas = Habitacion::whereIn('id', $idsAgregadas)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->pluck('id')
                    ->map(fn($habitacionId) => (int) $habitacionId)
                    ->all();

                if (count($habitacionesBloqueadas) !== count($idsAgregadas)) {
                    throw new \Exception('Una o mas habitaciones seleccionadas ya no existen.');
                }
            }

            if (!empty($idsAgregadas)) {
                $ocupadasPorOtroMov = MovhabitacionHabitacion::whereIn('habitacion_id', $idsAgregadas)
                    ->where('movhabitacion_id', '!=', $movhabitacion->id)
                    ->whereIn('situacion', ['A', 'P'])
                    ->whereHas('movhabitacion', function ($query) {
                        $query->whereIn('situacion', ['A', 'P']);
                    })
                    ->pluck('habitacion_id')
                    ->unique()
                    ->values()
                    ->all();

                if (!empty($ocupadasPorOtroMov)) {
                    $habitacionesOcupadas = Habitacion::whereIn('id', $ocupadasPorOtroMov)
                        ->pluck('numero')
                        ->implode(', ');
                    throw new \Exception('No se puede actualizar. Las habitaciones ya estan ocupadas: ' . $habitacionesOcupadas);
                }
            }

            $precioHabitacion = $request->precioHabitacion;
            $modalidad = '';
            $dias = null;
            $horas = null;

            if ($request->switch_state === 'dias') {
                $dias = $request->dias_totales;
                $modalidad = 'D';
            } elseif ($request->switch_state === 'horas') {
                $horas = $request->horas_totales;
                $modalidad = 'H';
            }

            $fechaIngreso = $request->fechaingreso;
            $fechaSalida = $request->fechasalida;
            if ($modalidad === 'H') {
                $fechaActual = now();
                $horaingreso = Carbon::parse($request->horaingreso);
                $horasalida = Carbon::parse($request->horasalida);

                if ($horaingreso > $horasalida) {
                    $fechaSalida = $fechaActual->copy()->addDay()->setTimeFrom($horasalida);
                } else {
                    $fechaSalida = $fechaActual->copy()->setTimeFrom($horasalida);
                }

                $fechaIngreso = $fechaActual->toDateTimeString();
            }

            $this->validarCruceConReservasActivas($idsNuevas, $fechaIngreso, $fechaSalida);

            $movimiento->update([
                'persona_id' => $request->persona_principal,
                'persona' => $personaPrincipal->nombres . ' ' . $personaPrincipal->apellidos,
                'comentario' => $request->comentario ?? '',
            ]);

            $movhabitacion->update([
                'fechaingreso' => $fechaIngreso,
                'fechasalida' => $fechaSalida,
                'dias' => $dias,
                'horas' => $horas,
                'modalidad' => $modalidad,
                'total' => $request->total,
                'preciohabitacion' => $precioHabitacion,
                'comentario' => $request->comentario,
                'habitacion_id' => $habitacionPrincipalId,
                'modo' => count($idsNuevas) > 1 ? 'G' : 'U',
            ]);

            $totalMovventa = Movventum::where('movhabitacion_id', $movhabitacion->id)
                ->whereHas('movimiento', function ($query) {
                    $query->where('situacion', '!=', 'D');
                })
                ->sum('total');
            $saldoPendiente = round((float) $movhabitacion->total - (float) $totalMovventa, 2);
            $situacionPago = $saldoPendiente <= 0 ? 'P' : 'A';

            $movhabitacion->update([
                'situacion' => $situacionPago,
            ]);

            if (!empty($idsEliminadas)) {
                MovhabitacionHabitacion::where('movhabitacion_id', $movhabitacion->id)
                    ->whereIn('habitacion_id', $idsEliminadas)
                    ->delete();

                foreach ($idsEliminadas as $habitacionEliminadaId) {
                    HabitacionAvailability::markCleaningIfNoActiveStay((int) $habitacionEliminadaId);
                }
            }

            foreach ($habitacionesSeleccionadas as $index => $habitacionData) {
                $habitacionId = (int) $habitacionData['id'];
                $tarifaHabitacion = (float) ($habitacionData['precio'] ?? 0);
                $diasCalculados = $modalidad === 'D' ? (float) ($dias ?? 0) : null;
                $totalDetalle = $modalidad === 'D' ? $tarifaHabitacion * $diasCalculados : $tarifaHabitacion;

                $habitacionModelo = Habitacion::find($habitacionId);
                if (!$habitacionModelo) {
                    throw new \Exception('No se encontro la habitacion seleccionada con ID: ' . $habitacionId);
                }

                $payloadDetalle = [
                    'dias' => $dias,
                    'horas' => $horas,
                    'modalidad' => $modalidad,
                    'total' => $totalDetalle,
                    'tarifa' => $tarifaHabitacion,
                    'principal' => $index === 0,
                    'json_habitacion' => json_encode($habitacionData, JSON_UNESCAPED_UNICODE),
                    'fechacheckin' => $fechaIngreso,
                    'fechacheckout' => $fechaSalida,
                    'notificado_checkout_10' => false,
                    'situacion' => $situacionPago,
                ];

                $detalleExistente = $detallesActuales->get($habitacionId);
                if ($detalleExistente) {
                    $detalleExistente->update($payloadDetalle);
                } else {
                    MovhabitacionHabitacion::create(array_merge($payloadDetalle, [
                        'movhabitacion_id' => $movhabitacion->id,
                        'habitacion_id' => $habitacionId,
                    ]));
                }

                $habitacionModelo->update([
                    'situacion' => 'Ocupada',
                ]);
            }

            Huesped::where('movhabitacion_id', $movhabitacion->id)->delete();

            Huesped::create([
                'persona_id' => $request->persona_principal,
                'movhabitacion_id' => $movhabitacion->id,
                'habitacion_id' => $habitacionPrincipalId,
            ]);

            $habitacionesPermitidas = array_flip($idsNuevas);
            $acompanantesInsertados = [];
            foreach ($acompanantes as $acompanante) {
                if (is_numeric($acompanante)) {
                    $personaId = (int) $acompanante;
                    $habitacionId = $habitacionPrincipalId;
                } elseif (is_array($acompanante) && isset($acompanante['id'])) {
                    $personaId = (int) $acompanante['id'];
                    $habitacionId = (int) ($acompanante['habitacion_id'] ?? $habitacionPrincipalId);
                } else {
                    continue;
                }

                if ($personaId === (int) $request->persona_principal || $personaId <= 0) {
                    continue;
                }

                if (!isset($habitacionesPermitidas[$habitacionId])) {
                    $habitacionId = $habitacionPrincipalId;
                }

                $key = $personaId . '-' . $habitacionId;
                if (isset($acompanantesInsertados[$key])) {
                    continue;
                }

                Huesped::create([
                    'movhabitacion_id' => $movhabitacion->id,
                    'habitacion_id' => $habitacionId,
                    'persona_id' => $personaId,
                ]);

                $acompanantesInsertados[$key] = true;
            }

            $movhabitacion->refresh();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Check-In actualizado exitosamente.',
                'redirect_url' => route('movhabitacion.index'),
                'habitacion_id' => $movhabitacion->habitacion_id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
                'redirect_url' => route('movhabitacion.index'),
            ], 500);
        }
    }

    private function obtenerRecepcionistasSucursal(int $sucursalId, ?int $personaIdIncluir = null)
    {
        $ids = DB::table('persona as p')
            ->select('p.id')
            ->distinct()
            ->leftJoin('users as u', 'u.persona_id', '=', 'p.id')
            ->leftJoin('rolpersona as r', 'r.persona_id', '=', 'p.id')
            ->where('p.sucursal_id', $sucursalId)
            ->whereNull('p.deleted_at')
            ->where(function ($query) {
                $query->whereIn('u.perfil_id', [12, 10, 11, 19])
                    ->whereIn('r.rol_id', [1, 2]);
            })
            ->pluck('p.id')
            ->all();

        if ($personaIdIncluir && !in_array($personaIdIncluir, $ids, true)) {
            $ids[] = $personaIdIncluir;
        }

        if (empty($ids)) {
            return collect();
        }

        return Persona::with('personamaestro')
            ->whereIn('id', $ids)
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();
    }

    private function generateNumero(
        $tipoDocumentoId,
        $reservar = true,
        ?Tipodocumento $tipoDocumento = null,
        ?Persona $persona = null,
        ?Caja $caja = null
    )
    {
        // Obtener el tipo de documento
        $tipoDocumento ??= Tipodocumento::find($tipoDocumentoId);

        if (!$tipoDocumento) {
            return response()->json(['success' => false, 'message' => 'Tipo de documento no encontrado']);
        }

        // Obtener la abreviatura (ej. B, F, etc.)
        $abreviatura = $tipoDocumento->abreviatura ?? 'N/A';

        // Obtener la persona del usuario autenticado
        $persona ??= Auth::user()->persona;

        // Obtener la primera caja activa de la sucursal del usuario
        $caja ??= Caja::where('situacion', 'A')
            ->where('sucursal_id', $persona->sucursal_id)
            ->first();

        if (!$caja) {
            return response()->json(['success' => false, 'message' => 'No hay caja activa para esta sucursal']);
        }

        // Formar la serie completa: abreviatura + serie de caja (ej. B001, F001)
        $serie = $abreviatura . str_pad($caja->serie, 3, "0", STR_PAD_LEFT);

        if ($reservar) {
            return CorrelativoService::nextNumeroVenta($persona->sucursal_id, $tipoDocumentoId, $serie);
        }

        return CorrelativoService::peekNumeroVenta($persona->sucursal_id, $tipoDocumentoId, $serie);
    }

    private function numeroDesdeComprobanteRequest(Request $request): ?string
    {
        $numeroComprobante = (string) $request->input('numero_comprobante', '');

        return preg_match('/-(\d+)$/', $numeroComprobante, $matches)
            ? $matches[1]
            : null;
    }

    public function checkin($id, $personaCreadaId = null)
    {
        // Obtener la habitación y la información del usuario
        $habitacion = Habitacion::with('tipohabitacion')->findOrFail($id);
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);

        // Cargar la lista de personas
        $personas = Persona::where('sucursal_id', $sucursal->id)->get();
        // Cargar los acompañantes de la sesión
        $acompanantesIds = session('acompanantes', []);
        $principalId = session('principal');
        // dd($principalId);
        // Preparar datos adicionales para la vista
        $currentDateTime = now();
        $initialDate = $currentDateTime->format('Y-m-d\TH:i');
        $initialTime = $currentDateTime->format('H:i');

        // Obtener el valor de la hora predeterminada desde RelParametroSucursal
        $horaSalidaJson = RelParametroSucursal::where('sucursal_id', $sucursal->id)
            ->where('parametro_id', 7)
            ->value('valor');
        $modalidadtarifario = RelParametroSucursal::where('sucursal_id', $sucursal->id)
            ->whereHas('parametro', function ($query) {
                $query->where('descripcion', 'Modalidad tarifario');
            })
            ->value('valor'); // esto devuelve directamente un string como 'Si' o 'No'

        $modificarPreciosCheckin = RelParametrosucursal::where('sucursal_id', $sucursal->id)
            ->whereHas('parametro', function ($query) {
                $query->where('descripcion', 'Modificar precios en checkin');
            })
            ->value('valor') ?? 'No';

        $permiteModificarTarifaCheckin = strcasecmp(trim((string) $modificarPreciosCheckin), 'Si') === 0;

        // Reemplazar corchetes por llaves y luego decodificar el JSON
        $horaSalida = '12:00'; // Valor por defecto en caso de error
        if ($horaSalidaJson) {
            // Intentar arreglar el formato incorrecto del JSON
            $horaSalidaJson = str_replace(['[', ']'], ['{', '}'], $horaSalidaJson);

            // Ahora decodificar el JSON
            $decoded = json_decode($horaSalidaJson, true);
            $horaSalida = $decoded['hora_salida'] ?? $horaSalida;
        }

        // Determinar la fecha de salida basada en la hora de entrada
        $currentHour = $currentDateTime->hour;
        if ($currentHour >= 0 && $currentHour < 5) {
            // Si la hora es entre las 12:00 AM y las 5:00 AM, la salida será el mismo día
            $defaultSalidaDate = $currentDateTime->format('Y-m-d') . 'T' . $horaSalida;
        } else {
            // Si no, la salida será al día siguiente
            $defaultSalidaDate = $currentDateTime->addDay()->format('Y-m-d') . 'T' . $horaSalida;
        }

        // Pasar los acompañantes a la vista
        $personaCreada = $principalId ? Persona::find($principalId) : null;

        $pisos = Piso::where('sucursal_id', $persona->sucursal_id)->orderBy('id')->get();
        $habitaciones = Habitacion::with(['tipohabitacion', 'sucursal', 'piso'])
            ->where('sucursal_id', $persona->sucursal_id)
            ->whereIn('situacion', ['Disponible', 'Limpieza'])
            ->orderBy('numero')
            ->get();

        $recepcionistas = $this->obtenerRecepcionistasSucursal($sucursal->id, $user->persona_id);

        return view('movhabitacion.checkin', compact(
            'habitacion',
            'personas',
            'initialDate',
            'initialTime',
            'defaultSalidaDate',
            'sucursal',
            'pisos',
            'habitaciones',
            'personaCreada',
            'principalId',
            'modalidadtarifario',
            'permiteModificarTarifaCheckin',
            'acompanantesIds',
            'recepcionistas' // Aquí pasas los IDs de los acompañantes
        ));
    }

    public function editCheckin($habitacion_id)
    {
        $movhabitacion = Movhabitacion::whereHas('movhabitacion_habitacion', function ($query) use ($habitacion_id) {
            $query->where('habitacion_id', $habitacion_id);
        })
            ->whereIn('situacion', ['A', 'P'])
            ->with([
                'movhabitacion_habitacion.habitacion.tipohabitacion', // Cargar todo
            ])
            ->firstOrFail();

        $habitacion = $movhabitacion->habitacion;
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);

        $personas = Persona::where('sucursal_id', $sucursal->id)->get();
        $modalidadtarifario = RelParametroSucursal::where('sucursal_id', $sucursal->id)
            ->whereHas('parametro', function ($query) {
                $query->where('descripcion', 'Modalidad tarifario');
            })
            ->value('valor');

        $jsonPrecioHoras = json_decode($habitacion->tipohabitacion->json_precio_horas, true);

        $initialDate = $movhabitacion->fechaingreso ? Carbon::parse($movhabitacion->fechaingreso)->format('Y-m-d\\TH:i') : now()->format('Y-m-d\\TH:i');
        $initialTime = $movhabitacion->horaingreso ? Carbon::parse($movhabitacion->horaingreso)->format('H:i') : now()->format('H:i');
        $defaultSalidaDate = $movhabitacion->fechasalida ? Carbon::parse($movhabitacion->fechasalida)->format('Y-m-d\\TH:i') : now()->addDay()->format('Y-m-d\\TH:i');
        $horaSalidaCalculada = $movhabitacion->horasalida ? Carbon::parse($movhabitacion->horasalida)->format('H:i') : now()->addHours(1)->format('H:i');

        $pisos = Piso::where('sucursal_id', $persona->sucursal_id)->orderBy('id')->get();
        $habitaciones = Habitacion::with(['tipohabitacion', 'sucursal', 'piso'])
            ->where('sucursal_id', $persona->sucursal_id)
            ->whereIn('situacion', ['Disponible', 'Limpieza', 'Ocupada'])
            ->orderBy('numero')
            ->get();

        $principalId = $movhabitacion->movimiento->persona_id ?? null;
        $personaCreada = $principalId ? Persona::find($principalId) : null;
        $acompanantesIds = Huesped::where('movhabitacion_id', $movhabitacion->id)
            ->where('habitacion_id', '!=', null)
            ->pluck('persona_id')
            ->toArray();
        $acompanantes = Huesped::where('movhabitacion_id', $movhabitacion->id)
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
        $recepcionistas = $this->obtenerRecepcionistasSucursal($sucursal->id, $user->persona_id);

        $detallesHabitaciones = $movhabitacion->movhabitacion_habitacion->map(function ($detalle) {
            $habitacionDetalle = $detalle->habitacion ?? Habitacion::find($detalle->habitacion_id);

            return [
                'id' => $habitacionDetalle->id ?? $detalle->habitacion_id,
                'numero' => $habitacionDetalle->numero ?? '',
                'tipo' => $habitacionDetalle->tipohabitacion->nombre ?? '',
                'situacion' => $habitacionDetalle->situacion ?? 'Disponible',
                'precio' => $detalle->tarifa ?? $habitacionDetalle->precio ?? 0,
                'principal' => (bool) ($detalle->principal ?? false),
            ];
        });
        $detallesHabitacionesJson = $detallesHabitaciones->toJson();

        return view('movhabitacion.edit_checkin', compact(
            'habitacion',
            'personas',
            'initialDate',
            'initialTime',
            'defaultSalidaDate',
            'sucursal',
            'pisos',
            'habitaciones',
            'personaCreada',
            'principalId',
            'modalidadtarifario',
            'acompanantesIds',
            'acompanantes',
            'jsonPrecioHoras',
            'movhabitacion',
            'horaSalidaCalculada',
            'habitacion_id',
            'recepcionistas',
            'detallesHabitaciones',
            'detallesHabitacionesJson'
        ));
    }
    public function checkout(Request $request, $id, $personaCreadaId = null)
    {
        try {

            $vista = $request->query('vista') ?? 'alojamiento';
            // Obtener la habitación ocupada por ID
            $habitacion = Habitacion::with('tipohabitacion')->findOrFail($id);
            $personaCreada = $personaCreadaId ? Persona::find($personaCreadaId) : null;

            // if ($habitacion->situacion !== 'Ocupada') {
            //     return redirect()->route('movhabitacion.index')->withErrors(['error' => 'La habitación no está ocupada.']);
            // }

            $movimientoHabitacion = Movhabitacion::whereHas('movhabitacion_habitacion', function ($query) use ($habitacion) {
                $query->where('habitacion_id', $habitacion->id); // Buscar en el detalle de habitaciones
            })
                ->whereIn('situacion', ['A', 'P']) // Filtrar por situación 'A' o 'P'
                ->with([
                    'movhabitacion_habitacion' => function ($query) use ($habitacion) {
                        $query->where('habitacion_id', $habitacion->id); // Asegurar que solo traiga la habitación deseada
                    }
                ])
                ->firstOrFail();

            // Obtener directamente la tarifa desde el detalle
            $movimientoHabitacion->tarifa = $movimientoHabitacion->movhabitacion_habitacion->first()->tarifa ?? 0;
            $movimientoHabitacion->situacion = $movimientoHabitacion->movhabitacion_habitacion->first()->situacion ?? 0;
            $movimientoHabitacion->early_checkin = $movimientoHabitacion->movhabitacion_habitacion->first()->early_checkin ?? 0;
            $movimientoHabitacion->late_checkout = $movimientoHabitacion->movhabitacion_habitacion->first()->late_checkout ?? 0;
            $movimientoHabitacion->total = $movimientoHabitacion->movhabitacion_habitacion->first()->total ?? 0;



            // dd($movimientoHabitacion->movreserva);
            // Obtener los productos consumidos relacionados con el movimiento
            $productosConsumidos = DetMovhabitacion::whereHas('movhabitacion', function ($query) use ($movimientoHabitacion) {
                $query->where('movimiento_id', $movimientoHabitacion->movimiento_id);
            })
                ->get()
                ->map(function ($producto) {
                    // Decodificar el JSON del campo json_producto
                    $datosProducto = json_decode($producto->json_producto, true);

                    // Formatear los datos en el formato esperado
                    return [
                        'producto_id' => $producto->producto_id, // ID del producto
                        'clasificacion' => $producto->producto->clasificacion,
                        'descripcion' => $datosProducto['descripcion'] ?? '', // Descripción del producto
                        'precio_compra' => $datosProducto['precio_compra'] ?? 0, // Precio de compra
                        'unidad' => $datosProducto['unidad'] ?? 'Unidad(es)', // Unidad
                        'cantidad' => $datosProducto['cantidad'] ?? 0, // Cantidad
                        'igv' => $datosProducto['igv'] ?? 0, // IGV
                        'precio_unitario' => $datosProducto['precio_unitario'] ?? 0, // Precio unitario
                        'total' => ($datosProducto['cantidad'] ?? 0) * ($datosProducto['precio_unitario'] ?? 0), // Total
                        'codigo' => $datosProducto['codigo'] ?? '', // Código del producto
                        'categoria' => $datosProducto['categoria'] ?? [],
                        'opc_categoria' => $datosProducto['opc_categoria'] ?? [],
                        'notas' => $datosProducto['notas'] ?? '', // Notas adicionales
                        'situacion' => $producto->situacion ?? '', // Notas adicionales

                    ];
                });
            $detallesJson = $productosConsumidos->toJson();
            // Calcular el total de los productos consumidos
            $totalProductosConsumidos = $productosConsumidos->reduce(function ($carry, $producto) {
                $cantidad = $producto['cantidad'] ?? 0;
                $precioUnitario = $producto['precio_unitario'] ?? 0;
                return $carry + ($cantidad * $precioUnitario);
            }, 0);
            // Calcular el total de los productos a pagar (situacion = 'N')
            $totalProductosAPagar = $productosConsumidos->reduce(function ($carry, $producto) {
                if ($producto['situacion'] === 'N') { // Solo si la situacion es 'N'
                    $cantidad = $producto['cantidad'] ?? 0;
                    $precioUnitario = $producto['precio_unitario'] ?? 0;
                    return $carry + ($cantidad * $precioUnitario);
                }
                return $carry;
            }, 0);

            //dd($totalProductosConsumidos);

            $huespedes = Huesped::where('movhabitacion_id', $movimientoHabitacion->id)
                ->where('habitacion_id', $id)
                ->get();

            if ($huespedes->isEmpty()) {
                // Si no hay huéspedes específicos para la habitación, obtener todos los del movhabitacion
                $huespedes = Huesped::where('movhabitacion_id', $movimientoHabitacion->id)->get();
            }

            // Otros cálculos y datos
            $sucursal_id = Auth::user()->persona->sucursal_id;
            $personas = Persona::where('sucursal_id', $sucursal_id)->get();

            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal = Sucursal::find($persona->sucursal_id);

            $categorias = Categorium::whereNull('categoria_id')->with('categorias')->get();

            $movhabitacion = Movhabitacion::with(['huespeds.persona', 'movhabitacion_habitacion'])
                ->whereHas('movhabitacion_habitacion', function ($query) use ($id) {
                    $query->where('habitacion_id', $id); // Filtrar por la habitación en el detalle
                })
                ->whereIn('situacion', ['A', 'P']) // Filtrar por situación 'A' o 'P'
                ->first();

            $huesped = Huesped::where('movhabitacion_id', $movhabitacion->id)->first();
            $clientePrincipal = $movhabitacion->movimiento->Persona ?? null;

            // Obtener los productos relacionados con la sucursal especificada
            $productos = Producto::with(['relProductosucursal', 'relListaUnidadSucursal'])
                ->whereHas('relProductosucursal', function ($query) use ($sucursal_id) {
                    $query->where('sucursal_id', $sucursal_id);
                })->where('clasificacion', 'Bien')
                ->get();
            $servicios = Producto::with(['relProductosucursal', 'relListaUnidadSucursal'])
                ->whereHas('relProductosucursal', function ($query) use ($sucursal_id) {
                    $query->where('sucursal_id', $sucursal_id);
                })->where('clasificacion', 'Servicio')
                ->get();

            // Formatear los productos para el autocomplete
            $tiposDocumento = Tipodocumento::where('tipomovimiento_id', 2)
                ->get();
            $tipoDocumentoParametro = RelParametrosucursal::where('sucursal_id', $sucursal->id)
                ->where('parametro_id', 10) // ID del parámetro para IGV
                ->value('valor'); // Tomamos directamente el campo 'valor'
            // Tipo de documento predeterminado (Ticket)
            $tipoDocumentoPredeterminado = $tiposDocumento->where('id', $tipoDocumentoParametro)->first();
            // Obtener la abreviatura del tipo de documento
            $abreviatura = $tipoDocumentoPredeterminado->abreviatura ?? 'N/A';
            // Obtener todas las cajas activas
            $cajas = Caja::where('situacion', 'A')->where('sucursal_id', $sucursal->id)->get();

            $caja = $cajas->first();

            // Determinar la serie
            $serie = $caja ? str_pad($caja->serie, 3, "0", STR_PAD_LEFT) : '001';
            $movimiento = Movimiento::find($movimientoHabitacion->movimiento_id);
            // Calcular el número correlativo (basado en registros existentes en `movimiento`)
            $correlativo = $movimiento->numero;
            // Formatear el número correlativo con ceros a la izquierda
            $correlativo = str_pad($correlativo, 8, "0", STR_PAD_LEFT);


            // Generar el número de comprobante
            $numeroComprobante = "{$abreviatura}{$serie}-{$correlativo}";

            // Preparar los datos de los huéspedes
            $mediospago = Mediopago::where('situacion', true)->get();
            $tarifaTotal = 0;

            // Obtener todas las habitaciones asociadas a la reserva
            $habitaciones = $movimientoHabitacion->movhabitacion_habitacion;

            foreach ($habitaciones as $hab) {
                // Si la modalidad es 'D', usar el total en vez de la tarifa
                $tarifaTotal += $hab->total;
            }

            // Construcción del array JSON
            $detallesMovimientosJson = [
                [
                    'tipo' => 'Pagado', // Tipo de pago
                    'monto' => ($movimientoHabitacion->situacion == 'A' ? $tarifaTotal : 0) + $totalProductosAPagar,
                    'medio_pago' => 'Efectivo',
                    'mediopago_id' => 1,
                    'notas' => 'Pago pendiente',
                ]
            ];

            // Convertir el array a JSON
            $detallesMovimientosJson = json_encode($detallesMovimientosJson); // Ahora es una cadena JSON

            // Preparar los datos de los huéspedes
            $medio_pagos = Mediopago::where('situacion', true)->get();
            $pos = Pasarelapago::where('situacion', true)->get(); // Tabla para P.O.S.
            $tarjetas = Tarjetum::where('situacion', true)->get(); // Tabla para Tarjetas
            $bancos = Banco::where('situacion', true)->get(); // Tabla para Bancos
            $billeteras = Billeteradigital::where('situacion', true)->get(); // Tabla para Billeteras Digitales
            $jsonPrecioHoras = json_decode($habitacion->tipohabitacion->json_precio_horas, true);


            return view('movhabitacion.checkout', compact(
                'habitacion',
                'medio_pagos',
                'pos',
                'tarjetas',
                'bancos',
                'billeteras',
                'movimientoHabitacion',
                'productosConsumidos',
                'huespedes',
                'clientePrincipal',
                'totalProductosConsumidos',
                'tiposDocumento',
                'tipoDocumentoPredeterminado',
                'numeroComprobante',
                'cajas',
                'detallesJson',
                'sucursal_id',
                'detallesMovimientosJson',
                'mediospago',
                'totalProductosAPagar',
                'jsonPrecioHoras',
                'personaCreada',
                'vista'
            ));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function ver($id)
    {
        try {
            // Obtener la habitación ocupada por ID
            $habitacion = Habitacion::with('tipohabitacion')->findOrFail($id);

            if ($habitacion->situacion !== 'Ocupada') {
                return redirect()->route('movhabitacion.index')->withErrors(['error' => 'La habitación no está ocupada.']);
            }

            // Obtener el movimiento actual de la habitación
            $movimientoHabitacion = Movhabitacion::where('habitacion_id', $habitacion->id)
                ->whereIn('situacion', ['A', 'P']) // Filtrar por situación 'A' o 'P'
                ->firstOrFail();


            // Obtener los productos consumidos relacionados con el movimiento
            $productosConsumidos = DetMovhabitacion::whereHas('movhabitacion', function ($query) use ($movimientoHabitacion) {
                $query->where('movimiento_id', $movimientoHabitacion->movimiento_id);
            })
                ->get()
                ->map(function ($producto) {
                    // Decodificar el JSON del campo json_producto
                    $datosProducto = json_decode($producto->json_producto, true);

                    // Formatear los datos en el formato esperado
                    return [
                        'producto_id' => $producto->producto_id, // ID del producto
                        'clasificacion' => $producto->producto->clasificacion,
                        'descripcion' => $datosProducto['descripcion'] ?? '', // Descripción del producto
                        'precio_compra' => $datosProducto['precio_compra'] ?? 0, // Precio de compra
                        'unidad' => $datosProducto['unidad'] ?? 'Unidad(es)', // Unidad
                        'cantidad' => $datosProducto['cantidad'] ?? 0, // Cantidad
                        'igv' => $datosProducto['igv'] ?? 0, // IGV
                        'precio_unitario' => $datosProducto['precio_unitario'] ?? 0, // Precio unitario
                        'total' => ($datosProducto['cantidad'] ?? 0) * ($datosProducto['precio_unitario'] ?? 0), // Total
                        'codigo' => $datosProducto['codigo'] ?? '', // Código del producto
                        'categoria' => $datosProducto['categoria'] ?? [],
                        'opc_categoria' => $datosProducto['opc_categoria'] ?? [],
                        'notas' => $datosProducto['notas'] ?? '', // Notas adicionales
                        'situacion' => $producto->situacion ?? '', // Notas adicionales

                    ];
                });
            $detallesJson = $productosConsumidos->toJson();
            // Calcular el total de los productos consumidos
            $totalProductosConsumidos = $productosConsumidos->reduce(function ($carry, $producto) {
                $cantidad = $producto['cantidad'] ?? 0;
                $precioUnitario = $producto['precio_unitario'] ?? 0;
                return $carry + ($cantidad * $precioUnitario);
            }, 0);
            // Calcular el total de los productos a pagar (situacion = 'N')
            $totalProductosAPagar = $productosConsumidos->reduce(function ($carry, $producto) {
                if ($producto['situacion'] === 'N') { // Solo si la situacion es 'N'
                    $cantidad = $producto['cantidad'] ?? 0;
                    $precioUnitario = $producto['precio_unitario'] ?? 0;
                    return $carry + ($cantidad * $precioUnitario);
                }
                return $carry;
            }, 0);

            //dd($totalProductosConsumidos);

            // Preparar los datos de los huéspedes
            $huespedes = Huesped::where('movhabitacion_id', $movimientoHabitacion->id)->get();

            // Otros cálculos y datos
            $sucursal_id = Auth::user()->persona->sucursal_id;
            $personas = Persona::where('sucursal_id', $sucursal_id)->get();

            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal = Sucursal::find($persona->sucursal_id);

            $categorias = Categorium::whereNull('categoria_id')->with('categorias')->get();

            $movhabitacion = Movhabitacion::with('huespeds.persona')
                ->where('habitacion_id', $id)
                ->whereIn('situacion', ['A', 'P']) // Filtrar por situación 'A' o 'P'
                ->first();
            $huesped = Huesped::where('movhabitacion_id', $movhabitacion->id)->first();
            $clientePrincipal = $huesped->persona ?? null;

            // Obtener los productos relacionados con la sucursal especificada
            $productos = Producto::with(['relProductosucursal', 'relListaUnidadSucursal'])
                ->whereHas('relProductosucursal', function ($query) use ($sucursal_id) {
                    $query->where('sucursal_id', $sucursal_id);
                })->where('clasificacion', 'Bien')
                ->get();
            $servicios = Producto::with(['relProductosucursal', 'relListaUnidadSucursal'])
                ->whereHas('relProductosucursal', function ($query) use ($sucursal_id) {
                    $query->where('sucursal_id', $sucursal_id);
                })->where('clasificacion', 'Servicio')
                ->get();

            // Formatear los productos para el autocomplete
            // Obtener los tipos de documento relacionados con `tipo_movimiento_id = 2`
            $tiposDocumento = Tipodocumento::where('tipomovimiento_id', 2)
                ->get();


            $tipoDocumentoParametro = RelParametrosucursal::where('sucursal_id', $sucursal->id)
                ->where('parametro_id', 10) // ID del parámetro para IGV
                ->value('valor'); // Tomamos directamente el campo 'valor'
            // Tipo de documento predeterminado (Ticket)
            $tipoDocumentoPredeterminado = $tiposDocumento->where('id', $tipoDocumentoParametro)->first();
            // Obtener la abreviatura del tipo de documento
            $abreviatura = $tipoDocumentoPredeterminado->abreviatura ?? 'N/A';
            // Obtener todas las cajas activas
            $cajas = Caja::where('situacion', 'A')->where('sucursal_id', $sucursal->id)->get();

            $caja = $cajas->first();

            // Determinar la serie
            $serie = $caja ? str_pad($caja->serie, 3, "0", STR_PAD_LEFT) : '001';
            $movimiento = Movimiento::find($movimientoHabitacion->movimiento_id);
            // Calcular el número correlativo (basado en registros existentes en `movimiento`)
            $correlativo = $movimiento->numero;
            // Formatear el número correlativo con ceros a la izquierda
            $correlativo = str_pad($correlativo, 8, "0", STR_PAD_LEFT);


            // Generar el número de comprobante
            $numeroComprobante = "{$abreviatura}{$serie}-{$correlativo}";

            // Preparar los datos de los huéspedes
            $huespedes = Huesped::where('movhabitacion_id', $movimientoHabitacion->id)->get();
            $mediospago = Mediopago::where('situacion', true)->get();
            $detallesMovimientosJson = [
                [
                    'tipo' => 'Pagado', // Tipo de pago
                    'monto' => ($movimientoHabitacion->situacion == 'A'
                        ? $movimientoHabitacion->total
                        : 0) + $totalProductosAPagar, // Condicional para sumar solo si situación es 'A'
                    'medio_pago' => 'Efectivo',
                    'mediopago_id' => 1,

                    'notas' => '',
                ]
            ];


            // Convertir el array a JSON
            $detallesMovimientosJson = json_encode($detallesMovimientosJson); // Ahora es una cadena JSON

            // Preparar los datos de los huéspedes
            $huespedes = Huesped::where('movhabitacion_id', $movimientoHabitacion->id)->get();
            $medio_pagos = Mediopago::where('situacion', true)->get();
            $pos = Pasarelapago::where('situacion', true)->get(); // Tabla para P.O.S.
            $tarjetas = Tarjetum::where('situacion', true)->get(); // Tabla para Tarjetas
            $bancos = Banco::where('situacion', true)->get(); // Tabla para Bancos
            $billeteras = Billeteradigital::where('situacion', true)->get(); // Tabla para Billeteras Digitales
            $jsonPrecioHoras = json_decode($habitacion->tipohabitacion->json_precio_horas, true);


            return view('movhabitacion.ver', compact(
                'habitacion',
                'medio_pagos',
                'pos',
                'tarjetas',
                'bancos',
                'billeteras',
                'movimientoHabitacion',
                'productosConsumidos',
                'huespedes',
                'clientePrincipal',
                'totalProductosConsumidos',
                'tiposDocumento',
                'tipoDocumentoPredeterminado',
                'numeroComprobante',
                'cajas',
                'detallesJson',
                'sucursal_id',
                'detallesMovimientosJson',
                'mediospago',
                'totalProductosAPagar',
                'jsonPrecioHoras',
            ));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    private function calcularSaldosCheckoutPendientes(Movhabitacion $movimientoHabitacion, float $totalAlojamiento): array
    {
        $totalMovventas = (float) Movventum::where('movhabitacion_id', $movimientoHabitacion->id)
            ->whereHas('movimiento', function ($query) {
                $query->where('situacion', '!=', 'D');
            })
            ->sum('total');

        $detallesHabitacion = DetMovhabitacion::where('movhabitacion_id', $movimientoHabitacion->id)
            ->get(['situacion', 'monto']);
        $totalDetP = (float) $detallesHabitacion->where('situacion', 'P')->sum('monto');
        $totalDetN = (float) $detallesHabitacion->where('situacion', 'N')->sum('monto');

        $totalProductosConsumidos = (float) DetMovhabitacion::whereHas('movhabitacion', function ($query) use ($movimientoHabitacion) {
            $query->where('movimiento_id', $movimientoHabitacion->movimiento_id);
        })
            ->get(['json_producto'])
            ->sum(function ($producto) {
                $datosProducto = json_decode($producto->json_producto, true);

                return ((float) ($datosProducto['cantidad'] ?? 0)) * ((float) ($datosProducto['precio_unitario'] ?? 0));
            });

        $saldoAlojamiento = max(0, $totalAlojamiento - $totalMovventas + $totalProductosConsumidos - $totalDetN);

        return [
            'total_movventas' => $totalMovventas,
            'total_det_n' => $totalDetN,
            'saldo_alojamiento' => $saldoAlojamiento,
            'alojamiento_ya_pagado' => $totalMovventas - $totalDetP,
            'saldo_productos' => $totalDetN,
        ];
    }

    private function validarCobroCheckout(Request $request, Movhabitacion $movimientoHabitacion): array
    {
        $totalAlojamiento = (float) $request->input('totalalojamiento', $movimientoHabitacion->total);
        $saldos = $this->calcularSaldosCheckoutPendientes($movimientoHabitacion, $totalAlojamiento);

        $montoAlojamiento = (float) $request->input('total_a_pagar_alojamiento', 0);
        $totalCobrar = (float) $request->input('total', 0);
        $tolerancia = 0.01;

        $totalPendiente = $saldos['saldo_alojamiento'] + $saldos['saldo_productos'];
        $soloProductosPendientes = $saldos['saldo_alojamiento'] <= $tolerancia && $saldos['saldo_productos'] > $tolerancia;

        if ($soloProductosPendientes) {
            $montoAlojamiento = 0.0;
        }

        if ($montoAlojamiento > $tolerancia && $saldos['saldo_alojamiento'] <= $tolerancia) {
            throw new \Exception('El alojamiento ya fue cobrado. Si agregó productos, días adicionales o late checkout, actualice la pantalla y vuelva a intentar.');
        }

        if ($montoAlojamiento > $saldos['saldo_alojamiento'] + $tolerancia) {
            throw new \Exception(
                'El monto de alojamiento excede el saldo pendiente (S/ '
                . number_format($saldos['saldo_alojamiento'], 2, '.', '')
                . '). Actualice la pantalla.'
            );
        }

        if ($totalPendiente <= $tolerancia && $totalCobrar > $tolerancia) {
            throw new \Exception('No hay saldo pendiente por cobrar. El alojamiento ya fue cobrado.');
        }

        if ($totalCobrar > $totalPendiente + $tolerancia) {
            throw new \Exception(
                'El monto total (S/ '
                . number_format($totalCobrar, 2, '.', '')
                . ') excede el saldo pendiente (S/ '
                . number_format($totalPendiente, 2, '.', '')
                . '). Actualice la pantalla.'
            );
        }

        return $saldos;
    }

    public function checkoutstore(Request $request, $id, $accion)
    {
        DB::beginTransaction();

        try {
            // Obtener datos principales
            $usuario = Auth::user();
            $responsable = $usuario->persona;
            $sucursal = Sucursal::findOrFail($responsable->sucursal_id);
            $housekeeping = null;
            if (in_array($accion, ['checkout', 'cobrar_checkout'], true)) {
                $housekeeping = RelParametroSucursal::where('sucursal_id', $sucursal->id)
                    ->whereHas('parametro', function ($query) {
                        $query->where('descripcion', 'housekeeping');
                    })
                    ->value('valor');
            }


            $persona = $request->persona_id ? Persona::find($request->persona_id) : null;
            // Obtener el registro Movhabitacion activo
            $movimientoHabitacion = Movhabitacion::whereHas('movhabitacion_habitacion', function ($query) use ($id) {
                $query->where('habitacion_id', $id);
            })
                ->whereIn('situacion', ['A', 'P'])
                ->with(['movhabitacion_habitacion', 'movimiento.movreserva'])
                ->lockForUpdate()
                ->firstOrFail();

            // Actualizar total en MovhabitacionHabitacion
            $movHabHabitacion = $movimientoHabitacion->movhabitacion_habitacion
                ->firstWhere('habitacion_id', $id);

            // El late ya se calcula en frontend (checkout). No recalcular aquí para evitar doble suma.
            $lateValue = max(0, (float) $request->input('late', 0));
            $request->merge(['late' => $lateValue]);

            // Actualizar campos en Movhabitacion
            $movimientoHabitacion->descuento = $request->descuento;
            $movimientoHabitacion->total = $request->totalalojamiento;
            $movimientoHabitacion->early_checkin = $request->early;
            $movimientoHabitacion->late_checkout = $lateValue;
            $movimientoHabitacion->save();

            if ($movHabHabitacion && $request->switch_state !== 'grupo') {
                $movHabHabitacion->dias = $request->dias ?? $request->dias_totales;
                $movHabHabitacion->early_checkin = $request->early;
                $movHabHabitacion->late_checkout = $lateValue;
                $movHabHabitacion->total = $request->totalalojamiento;
                $movHabHabitacion->save();
            }

            if (in_array($accion, ['cobrar', 'cobrar_checkout'], true)) {
                $saldosCheckout = $this->validarCobroCheckout($request, $movimientoHabitacion);

                if ($saldosCheckout['saldo_alojamiento'] <= 0.01 && $saldosCheckout['saldo_productos'] > 0.01) {
                    $request->merge([
                        'total_a_pagar_alojamiento' => 0,
                    ]);
                }
            }

            $facturadorController = new FacturadorController();
            $cajaActiva = Caja::where('situacion', 'A')
                ->where('sucursal_id', $sucursal->id)
                ->firstOrFail();
            $cajaId = $cajaActiva->id;
            // Obtener el último turno activo o cerrado para la caja
            $ultimoTurno = RelTurnocaja::where('situacion', 'A') // Turno activo
                ->whereHas('movcajainicio', function ($query) use ($cajaId) {
                    $query->where('caja_id', $cajaId);
                })
                ->latest('fechainicio') // Último turno
                ->first();
                 // Buscar el valor del IGV predeterminado en la tabla rel_parametrosucursal
            $igvPredeterminado = RelParametrosucursal::where('sucursal_id', $sucursal->id)
                ->where('parametro_id', 8) // ID del parámetro para IGV
                ->value('valor'); // Tomamos directamente el campo 'valor'
            $igvObjeto = Igv::find($igvPredeterminado);
            $sucursalVenta = (int) $request->sucursal_id === (int) $sucursal->id
                ? $sucursal
                : Sucursal::findOrFail($request->sucursal_id);
            $jsonSucursalVenta = json_encode($sucursalVenta);
            // Si es un checkout, actualiza la fecha de salida real

            if ($accion === 'checkout') {

                $movimientoHabitacion->total = $request->totalalojamiento;
                $movimientoHabitacion->early_checkin = $request->input('early', 0);
                $movimientoHabitacion->late_checkout = $lateValue;
                $movimientoHabitacion->save();


                if ($housekeeping === 'Si') {
                    $housekeeping = Housekeeping::create([
                        'fecha_programada' => now(),
                        'observaciones' => '',
                        'sucursal_id' => $persona->sucursal_id,
                    ]);

                    HousekeepingDetalle::create([
                        'jornada_id' => $housekeeping->id,
                        'habitacion_id' => $movimientoHabitacion->habitacion_id,
                        'empleado_id' => null,
                        'estado' => 'Pendiente',
                    ]);
                }

                // 🔹 Verificar si se cambia en grupo o solo una habitación
                if ($request->switch_state == 'grupo') {
                    // **Actualizar todas las habitaciones asociadas a este movhabitacion**
                    $habitacionesGrupo = MovhabitacionHabitacion::where('movhabitacion_id', $movimientoHabitacion->id)->pluck('habitacion_id');

                    // Cerrar detalles antes de recalcular el estado físico de cada cuarto.
                    MovhabitacionHabitacion::where('movhabitacion_id', $movimientoHabitacion->id)
                        ->update([
                            'situacion' => 'C',
                            'fechacheckout' => now(),
                        ]);

                    // Cerrar también el movhabitacion
                    $movimientoHabitacion->situacion = 'C';
                    $movimientoHabitacion->fecha_salida_real = now();
                    $movimientoHabitacion->save();

                    foreach ($habitacionesGrupo as $habitacionGrupoId) {
                        HabitacionAvailability::markCleaningIfNoActiveStay((int) $habitacionGrupoId);
                    }

                    // **Si hay una reserva asociada, actualizar su estado**
                    if ($movimientoHabitacion->movreserva_id !== null) {
                        $movreserva = Movreserva::find($movimientoHabitacion->movreserva_id);
                        if ($movreserva) {
                            $movreserva->estado = 'FINALIZADO';
                            $movreserva->fechasalida = now();
                            $movreserva->save();

                            // **Actualizar todas las habitaciones asociadas en movreserva_habitacion**
                            MovreservaHabitacion::where('movreserva_id', $movreserva->id)
                                ->update([
                                    'situacion' => 'FINALIZADO',
                                    'fechacheckout' => now()
                                ]);
                        }
                    }
                } else {
                    // **Modo Único**
                    // Cambiar el estado de la habitación a "Limpieza"
                    Habitacion::findOrFail($id);
                    // Cambiar solo la habitación específica en movhabitacion_habitacion
                    MovhabitacionHabitacion::where('movhabitacion_id', $movimientoHabitacion->id)
                        ->where('habitacion_id', $id) // Solo la habitación actual
                        ->update([
                            'situacion' => 'C',
                            'fechacheckout' => now(),
                        ]);
                    HabitacionAvailability::markCleaningIfNoActiveStay((int) $id);
                    // Cambiar solo la habitación específica en movhabitacion_habitacion
                    if (!is_null($movimientoHabitacion->movreserva_id)) {
                        // Cambiar solo la habitación específica en movreserva_habitacion
                        MovreservaHabitacion::where('movreserva_id', $movimientoHabitacion->movreserva_id)
                            ->where('habitacion_id', $id) // Solo la habitación actual
                            ->update([
                                'situacion' => 'FINALIZADO',
                                'fechacheckout' => now() // Registrar la fecha de salida
                            ]);

                        // Buscar y actualizar la reserva si existe
                        $movreserva = Movreserva::find($movimientoHabitacion->movreserva_id);
                        if ($movreserva) {
                            $movreserva->estado = 'FINALIZADO';
                            $movreserva->fechasalida = now();
                            $movreserva->save();
                        }
                    }


                    // 🔹 Verificar si es la última habitación pendiente
                    $pendientes = MovhabitacionHabitacion::where('movhabitacion_id', $movimientoHabitacion->id)
                        ->where('situacion', '!=', 'C') // Que aún no estén cerradas
                        ->count();

                    if ($pendientes == 0) {
                        // Si es la última, cerrar también el movhabitacion
                        $movimientoHabitacion->situacion = 'C'; // Cerrado
                        $movimientoHabitacion->fecha_salida_real = now();
                        $movimientoHabitacion->save();

                        if (!is_null($movimientoHabitacion->movreserva_id)) {

                            // Buscar y actualizar la reserva si existe
                            $movreserva = Movreserva::find($movimientoHabitacion->movreserva_id);
                            if ($movreserva) {
                                $movreserva->estado = 'FINALIZADO';
                                $movreserva->fechasalida = now();
                                $movreserva->save();
                            }
                        }
                    }
                }

                // Registrar el historial de la habitación
                HistorialHabitacion::create([
                    'sucursal_id' => Auth::user()->persona->sucursal_id,
                    'movhabitacion_id' => $movimientoHabitacion->id,
                    'relturnocaja_id' => $ultimoTurno->id,
                    'situacion_pago' => 'S',
                    'situacion_checkout' => 'S',
                ]);

                DB::commit();
                if ($request->vista == 'alojamiento') {
                    return response()->json([
                        'success' => true,
                        'message' => 'Checkout registrado exitosamente.',
                        'redirect_url' => route('movhabitacion.index'),
                    ]);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Checkout registrado exitosamente.',
                        'redirect_url' => route('calendarioreservas.index'),
                    ]);
                }
            } elseif ($accion === 'cobrar' && $movimientoHabitacion->situacion === 'P') {
                $caja = (int) $request->caja_id === (int) $cajaActiva->id
                    ? $cajaActiva
                    : Caja::findOrFail($request->caja_id);
                $tipodocumento = Tipodocumento::findOrFail($request->tipodocumento_id);
                $numeroVenta = $tipodocumento->abreviatura === 'T'
                    ? ($this->numeroDesdeComprobanteRequest($request) ?? $this->generateNumero($request->tipodocumento_id, true, $tipodocumento, $responsable, $cajaActiva))
                    : $this->generateNumero($request->tipodocumento_id, true, $tipodocumento, $responsable, $cajaActiva);

                $movimientoParaVenta = Movimiento::create([
                    'numero' => $numeroVenta,
                    'fecha' => now(),
                    'usuario_id' => $usuario->id,
                    'usuario' => $usuario->name,
                    'responsable_id' => $responsable->id,
                    'responsable' => "{$responsable->nombres} {$responsable->apellidos}",
                    'persona_id' => $request->persona_id,
                    'persona' => $persona ? "{$persona->nombres} {$persona->apellidos}" : 'N/A',
                    'comentario' => $request->notas ?? '',
                    'situacion' => 'A',
                    'tipomovimiento_id' => $tipodocumento->tipomovimiento_id,
                    'tipodocumento_id' => $request->tipodocumento_id,
                    'sucursal_id' => $request->sucursal_id,
                    'movimiento_id' => $movimientoHabitacion->movimiento_id,
                ]);

                $totalIgv = 0; // Variable para acumular el IGV total
                $totalSinIgv = 0; // Variable para acumular el subtotal (sin IGV)
                // Procesar detalles para obtener los igv_id de los productos
                $detalles = json_decode($request->input('detalles_json', '[]'), true);
                $relProductosPorProducto = collect();

                if ($request->total_a_pagar_alojamiento != 0) {
                    $tipoCobro = 'N';

                    if ($request->late > 0) {
                        $tipoCobro = 'L';
                    }
                    if ($request->early > 0) {
                        $tipoCobro = 'E';
                    }



                    $tipoCobro = 'N';

                    if ($request->late > 0 && $request->early > 0) {
                        $tipoCobro = 'N';
                    } elseif ($request->late > 0) {
                        $tipoCobro = 'L';
                    } elseif ($request->early > 0) {
                        $tipoCobro = 'E';
                    }
               
                    // Armar el array base


                    $datosMovventa = [
                        'fecha' => now(),
                        'usuario_id' => $usuario->id,
                        'usuario' => $usuario->name,
                        'responsable_id' => $responsable->id,
                        'responsable' => "{$responsable->nombres} {$responsable->apellidos}",
                        'persona_id' => $request->persona_id,
                        'persona' => $persona ? "{$persona->nombres} {$persona->apellidos}" : 'N/A',
                        'serie' => $tipodocumento->abreviatura . '' . $caja->serie,
                        'anio' => now()->year,
                        'tipo_detalle' => 'DETALLADO',
                        'tipo_pago' => $this->resolverTipoPago(json_decode($request->detalles_movimientos_json, true) ?: []),
                        'estado' => $request->estado ?? 'N',
                        'moneda' => $request->moneda,
                        'tipocambio' => 3.71,
                        'subtotal' => $request->total/($igvObjeto->porcentaje_igv / 100 + 1),
                        'igv' => $request->total - $request->total/($igvObjeto->porcentaje_igv / 100 + 1),
                        'total' => $request->total ?? $request->total_a_pagar_productos,
                        'movimiento_id' => $movimientoParaVenta->id,
                        'sucursal_id' => $request->sucursal_id,
                        'json_sucursal' => $jsonSucursalVenta,
                        'tipo_cobro' => $tipoCobro,
                        'movhabitacion_id' => $movimientoHabitacion->id,
                        'movreserva_id' => $movimientoHabitacion->movimiento->movreserva->id ?? null,

                    ];



                    // Si es L, E o LE, añadir movearlylate_id
                    if (in_array($tipoCobro, ['L', 'E']) && isset($movHabHabitacion)) {
                        $datosMovventa['movearlylate_id'] = $movHabHabitacion->id;
                    }
                    $unidad = Unidad::find(17);
                    $total_original = $request->total_a_pagar_alojamiento + $request->descuento;

                    // Crear el registro
                    $movventa = Movventum::create($datosMovventa);
                    $detalles = DetMovventum::create([
                        'movventa_id' => $movventa->id,
                        'tipo_detalle' => 'DETALLADO',
                        'codigo' => '-',
                        // 'descripcion' => $request->descripcion_alojamiento ?? 'Checkout de habitación',
                        'descripcion' => 'Servicio de alojamiento: Late checkout',
                        'producto_id' => null,
                        'json_producto' => '[]',
                        'unidad_id' => 17,
                        'json_unidad' => $unidad ? json_encode($unidad) : null,
                        'igv_id' => $igvObjeto->id, // Asignar el igv_id desde la relación
                        'json_igv' => $igvObjeto ? json_encode($igvObjeto) : null,
                        'cantidad' => 1,
                        'monto' => $request->total_a_pagar_alojamiento,
                        'dcto_porcentaje' => $total_original > 0
                            ? ($request->descuento / $total_original) * 100
                            : 0,
                        'monto_original' => $total_original,

                        'comentario' => $request->comentario ?? '',
                        'det_categoria' => '[]',
                        'opc_categoria' => '[]',
                        'det_movventa_id' => null,
                        'complementos' => json_encode([]),
                        'sucursal_id' => $request->sucursal_id,
                    ]);
                    // Si $detalles es un objeto de tipo DetMovventum, convertirlo en un arreglo
                    if ($detalles instanceof DetMovventum) {
                        $detalles = [$detalles->toArray()]; // Convertimos el objeto en un arreglo
                    } else {
                        // Si ya es un arreglo o vacío, lo dejamos como está
                        $detalles = $detalles ?? [];
                    }


                    if (in_array($tipoCobro, ['L', 'E', 'LE']) && isset($movHabHabitacion)) {
                        $earlyMonto = floatval($request->early);
                        $lateMonto = floatval($request->late);

                        if (str_contains($tipoCobro, 'E')) {
                            $movHabHabitacion->early_checkin = $earlyMonto;
                        }

                        if (str_contains($tipoCobro, 'L')) {
                            $movHabHabitacion->late_checkout = $lateMonto;
                        }

                        // Recalcular total correctamente: tarifa * (días u horas) + early + late
                        $tarifa = floatval($movHabHabitacion->tarifa);
                        $dias = intval($movHabHabitacion->dias);
                        $horas = intval($movHabHabitacion->horas);

                        // Detectar si modalidad es por horas o días
                        $modalidad = $movHabHabitacion->modalidad;

                        if ($modalidad === 'H') {
                            $totalBase = $tarifa * $horas;
                        } else {
                            $totalBase = $tarifa * $dias;
                        }



                        $movHabHabitacion->save();
                    }
                } else {
                    $productoIds = collect($detalles)
                        ->where('situacion', 'N')
                        ->pluck('producto_id')
                        ->filter()
                        ->unique()
                        ->values();
                    $relProductosPorProducto = RelProductosucursal::with('igv')
                        ->where('sucursal_id', $sucursal->id)
                        ->whereIn('producto_id', $productoIds)
                        ->get()
                        ->groupBy('producto_id')
                        ->map->first();

                    // Buscar el valor del IGV predeterminado en la tabla rel_parametrosucursal
                    foreach ($detalles as $detalle) {
                        if (isset($detalle['situacion']) && $detalle['situacion'] == 'N') {
                            // Obtener la relación del producto con la sucursal
                            $relProductoSucursal = $relProductosPorProducto->get($detalle['producto_id']);

                            // Verificar que exista la relación
                            if ($relProductoSucursal) {
                                // Obtener el porcentaje de IGV para este producto desde la relación
                                $igv = $relProductoSucursal->igv;

                                if ($igv) {
                                    $porcentajeIgv = $igv->porcentaje_igv;
                                    // Calcular el IGV para este detalle de producto
                                    $igvProducto = $detalle['precio_unitario'] * $detalle['cantidad'] * ($porcentajeIgv / 100);
                                    $totalIgv += $igvProducto; // Sumar al IGV total
                                }
                            }

                            // Acumular el subtotal (precio sin IGV)
                            $totalSinIgv += $detalle['precio_unitario'] * $detalle['cantidad'];
                        }
                    }

                    // Armar el array base
                    $datosMovventa = [
                        'fecha' => now(),
                        'usuario_id' => $usuario->id,
                        'usuario' => $usuario->name,
                        'responsable_id' => $responsable->id,
                        'responsable' => "{$responsable->nombres} {$responsable->apellidos}",
                        'persona_id' => $request->persona_id,
                        'persona' => $persona ? "{$persona->nombres} {$persona->apellidos}" : 'N/A',
                        'serie' => $tipodocumento->abreviatura . '' . $caja->serie,
                        'anio' => now()->year,
                        'tipo_detalle' => 'DETALLADO',
                        'tipo_pago' => $this->resolverTipoPago(json_decode($request->detalles_movimientos_json, true) ?: []),
                        'estado' => $request->estado ?? 'N',
                        'moneda' => $request->moneda,
                        'tipocambio' => 3.71,
                        'subtotal' => $totalSinIgv,
                        'igv' => $totalIgv,
                        'total' => $totalSinIgv + $totalIgv,
                        'movimiento_id' => $movimientoParaVenta->id,
                        'sucursal_id' => $request->sucursal_id,
                        'json_sucursal' => $jsonSucursalVenta,
                        'movhabitacion_id' => $movimientoHabitacion->id,
                        'movreserva_id' => $movimientoHabitacion->movimiento->movreserva->id ?? null,

                    ];

                    // Crear el registro
                    $movventa = Movventum::create($datosMovventa);
                }



                //   Filtrar los que no son 'P'
                $detalles = array_filter($detalles, function ($detalle) {
                    return isset($detalle['situacion']) && $detalle['situacion'] !== 'P';
                });
                if (!empty($detalles)) {
                    foreach ($detalles as $detalle) {
                        // Obtener la relación del producto con la sucursal
                        $relProductoSucursal = $relProductosPorProducto->get($detalle['producto_id']);

                        // Verificar que exista la relación
                        if ($relProductoSucursal) {
                            // Obtener el porcentaje de IGV para este producto desde la relación
                            $igv = $relProductoSucursal->igv;

                            if ($igv) {
                                $porcentajeIgv = $igv->porcentaje_igv;
                                // Calcular el IGV para este detalle de producto
                                $igvProducto = $detalle['precio_unitario'] * $detalle['cantidad'] * ($porcentajeIgv / 100);
                                $totalIgv += $igvProducto; // Sumar al IGV total
                            }
                        }

                        // Acumular el subtotal (precio sin IGV)
                        $totalSinIgv += $detalle['precio_unitario'] * $detalle['cantidad'];
                    }
                }

                // Procesar e insertar nuevos detalles en DetMovhabitacion
                if (!empty($detalles)) {
                    $productoIds = collect($detalles)->pluck('producto_id')->filter()->unique()->values();
                    $unidadesPorDescripcion = Unidad::whereIn(
                        'descripcion',
                        collect($detalles)->pluck('unidad')->filter()->unique()
                    )->get()->groupBy('descripcion')->map->first();
                    $productosPorId = Producto::with('categorium')
                        ->whereIn('id', $productoIds)
                        ->get()
                        ->keyBy('id');
                    $igvsPorId = Igv::whereIn(
                        'id',
                        $relProductosPorProducto->pluck('igv_id')->filter()->unique()
                    )->get()->keyBy('id');

                    // Restaurar el stock antes de eliminar los registros existentes
                    if ($movimientoHabitacion->movimiento) {
                        // Obtener los detalles existentes antes de eliminarlos
                        $detallesExistentes = DetMovhabitacion::where('movhabitacion_id', $movimientoHabitacion->id)->get();
                        $restauraciones = $detallesExistentes
                            ->groupBy(fn ($detalle) => $detalle->producto_id . ':' . $detalle->sucursal_id)
                            ->map->sum('cantidad');
                        $stocksExistentes = Stockproducto::whereIn(
                            'producto_id',
                            $detallesExistentes->pluck('producto_id')->filter()->unique()
                        )->whereIn(
                            'sucursal_id',
                            $detallesExistentes->pluck('sucursal_id')->filter()->unique()
                        )->get()
                            ->groupBy(fn ($stock) => $stock->producto_id . ':' . $stock->sucursal_id)
                            ->map->first();

                        foreach ($restauraciones as $claveStock => $cantidad) {
                            $stockProducto = $stocksExistentes->get($claveStock);
                            if ($stockProducto) {
                                $stockProducto->stockbase += $cantidad;
                                $stockProducto->save();
                            }
                        }

                        // Ahora eliminar los registros de det_movhabitacion
                        DetMovhabitacion::where('movhabitacion_id', $movimientoHabitacion->id)->where('situacion', 'N')->forceDelete();
                    }

                    $stocksPorProducto = Stockproducto::where('sucursal_id', $request->sucursal_id)
                        ->whereIn('producto_id', $productoIds)
                        ->get()
                        ->groupBy('producto_id')
                        ->map->first();

                    foreach ($detalles as $detalle) {
                        // Verificar si la situación es 'P'; si es así, omitir este detalle
                        if (isset($detalle['situacion']) && $detalle['situacion'] === 'P') {
                            continue; // Saltar a la siguiente iteración del bucle
                        }

                        // Obtener la relación del producto con la sucursal
                        $relProductoSucursal = $relProductosPorProducto->get($detalle['producto_id']);

                        // Obtener el igv_id directamente desde la relación
                        $igv_id = $relProductoSucursal ? $relProductoSucursal->igv_id : null;
                        $igvClase = $igvsPorId->get($igv_id);
                        $unidad = $unidadesPorDescripcion->get($detalle['unidad']);
                        // Obtener el producto usando el producto_id del detalle
                        $producto = $productosPorId->get($detalle['producto_id']);

                        // Obtener la categoría asociada al producto, si existe
                        $categoria = $producto && $producto->categorium ? $producto->categorium : null;

                        // Verificar si existe el registro en stockproducto
                        $stockProducto = $stocksPorProducto->get($detalle['producto_id']);

                        // Registrar detalles en DetMovventum independientemente de la clasificación
                        $detMovVenta = DetMovventum::create([
                            'movventa_id' => $movventa->id,
                            'situacion' => 'E',
                            'tipo_detalle' => 'DETALLADO',
                            'codigo' => $detalle['codigo'],
                            'descripcion' => $detalle['descripcion'],
                            'producto_id' => $detalle['producto_id'],
                            'json_producto' => json_encode($detalle),
                            'unidad_id' => $unidad ? $unidad->id : null,
                            'json_unidad' => $unidad ? json_encode($unidad) : null,
                            'igv_id' => $igv_id, // Asignar el igv_id desde la relación
                            'json_igv' => $igvClase ? json_encode($igvClase) : null,
                            'cantidad' => $detalle['cantidad'],
                            'monto' => $detalle['precio_unitario'] * $detalle['cantidad'],
                            'dcto_porcentaje' => $detalle['dcto_porcentaje'] ?? 0,
                            'monto_original' => $detalle['total'],
                            'comentario' => $detalle['notas'] ?? '',
                            'det_categoria' => json_encode($detalle['categoria'] ?? []),
                            'opc_categoria' => json_encode($detalle['opc_categoria'] ?? []),
                            'sucursal_id' => $request->sucursal_id,
                        ]);

                        // **Agregar DetMovHabitacion**
                        DetMovhabitacion::create([
                            'movhabitacion_id' => $movimientoHabitacion->id,
                            'tipo_detalle' => 'DETALLADO',
                            'codigo' => $detalle['codigo'],
                            'descripcion' => $detalle['descripcion'],
                            'producto_id' => $detalle['producto_id'],
                            'json_producto' => json_encode($detalle),
                            'unidad_id' => $unidad ? $unidad->id : null,
                            'json_unidad' => $unidad ? json_encode($unidad) : null,
                            'igv_id' => $igv_id, // Asignar el igv_id desde la relación
                            'json_igv' => $igvClase ? json_encode($igvClase) : null,
                            'cantidad' => $detalle['cantidad'],
                            'monto' => $detalle['precio_unitario'] * $detalle['cantidad'],
                            'dcto_porcentaje' => $detalle['dcto_porcentaje'] ?? 0,
                            'monto_original' => $detalle['total'],
                            'comentario' => $detalle['notas'] ?? '',
                            'det_categoria' => $categoria ? json_encode($categoria) : null,
                            'opc_categoria' => json_encode($detalle['opc_categoria'] ?? []),
                            'json_categoria' => $categoria ? json_encode($categoria) : null,
                            'sucursal_id' => $request->sucursal_id,
                            'situacion' => 'P',
                            'det_movventa_id' => $detMovVenta->id, // Relación con DetMovventum
                        ]);

                        // Descontar el stock según el nuevo detalle
                        if ($stockProducto) {
                            $stockProducto->stockbase -= $detalle['cantidad'];
                            $stockProducto->save();
                        }
                    }
                }

                // Crear nuevo registro en Movcaja
                $turnoCaja = RelTurnocaja::conCajaActiva($request->caja_id)
                    ->with('movcajainicio.turno')
                    ->first();
                $turnoId = $turnoCaja ? $turnoCaja->movcajainicio->turno_id : null;

                $movimientoCaja = Movimiento::create([
                    'numero' => $this->generateNumeroMovcaja($request->sucursal_id, $caja->id, 4),
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
                    'sucursal_id' => $request->sucursal_id,
                    'movimiento_id' => $movimientoParaVenta->id
                ]);

                $movcaja = Movcaja::create([
                    'conceptopago_id' => 4,
                    'moneda' => $request->moneda,
                    'tipocambio' => 3.71,
                    'total' => $request->total,
                    'caja_id' => $caja->id,
                    'caja' => $caja->numero,
                    'turno_id' => $turnoId,
                    'json_turno' => json_encode($turnoCaja?->movcajainicio?->turno),
                    'movimiento_id' => $movimientoCaja->id,
                    'sucursal_id' => $request->sucursal_id,
                ]);

                HistorialHabitacion::create([
                    'sucursal_id' => Auth::user()->persona->sucursal_id,
                    'movhabitacion_id' => $movimientoHabitacion->id,
                    'relturnocaja_id' => $turnoCaja->id,
                    'situacion_pago' => 'S',
                    'situacion_checkout' => 'N',

                ]);
                // Procesar Detalles de Movcaja
                // Antes no se mencionó edición en detalles movcaja, pero por coherencia también podríamos eliminar antes de crear
                $detallesMovCaja = json_decode($request->detalles_movimientos_json, true);
                $mediosPago = Mediopago::whereIn(
                    'id',
                    collect($detallesMovCaja)->pluck('mediopago_id')->filter()->unique()
                )->pluck('descripcion', 'id');

                foreach ($detallesMovCaja as $detalle) {
                    $detalleMovcaja = DetMovcaja::create([
                        'movcaja_id' => $movcaja->id,
                        'tipo' => $detalle['tipo'] ?? null,
                        'fechavencimiento' => !empty($detalle['fechavencimiento']) ? $detalle['fechavencimiento'] : null,
                        'fechapago' => now(),

                        // Medio de pago
                        'mediopago_id' => $detalle['mediopago_id'] ?? null,
                        'mediopago' => $detalle['mediopago_id']
                            ? ($mediosPago->get($detalle['mediopago_id']) ?? '')
                            : '',

                        'numero' => $detalle['numero'] ?? '',

                        // Inicializar campos dinámicos
                        'tarjeta_id' => null,
                        'tarjeta' => '',
                        'banco_id' => null,
                        'banco' => '',
                        'billeteradigital_id' => null,
                        'billeteradigital' => '',
                        'pos_id' => null,
                        'pos' => '',

                        // Monto y otros campos
                        'monto' => $detalle['monto'] ?? 0.00,
                        'comentario' => $detalle['comentario'] ?? '',
                        'situacion' => $detalle['situacion'] ?? 'A',
                        'sucursal_id' => $movcaja->sucursal_id,
                    ]);

                    // Obtener el medio de pago
                    $medioPago = strtolower($detalleMovcaja->mediopago);

                    // Si el medio de pago es "Efectivo", no registrar detalles adicionales
                    if ($medioPago === 'efectivo') {
                        continue;
                    }

                    $actualizacionesDetalleMovcaja = [];

                    // Recorrer el JSON de detalles si no es efectivo
                    foreach ($detalle['detalles'] ?? [] as $item) {
                        // Banco
                        if (!empty($item['banco'])) {
                            $bancoDescripcion = trim($item['banco']);
                            $actualizacionesDetalleMovcaja = array_merge($actualizacionesDetalleMovcaja, [
                                'banco_id' => Banco::where('descripcion', $bancoDescripcion)->value('id'),
                                'banco' => $bancoDescripcion
                            ]);
                        }

                        // Tarjeta
                        if (!empty($item['tarjeta'])) {
                            $partes = explode(' - ', $item['tarjeta']);
                            if (count($partes) >= 2) {
                                $nombreTarjeta = trim($partes[0]);
                                $tipoTarjeta = strtolower(trim($partes[1]));
                                $tipo = str_contains($tipoTarjeta, 'débito') ? 'D' : 'C';

                                $actualizacionesDetalleMovcaja = array_merge($actualizacionesDetalleMovcaja, [
                                    'tarjeta_id' => Tarjetum::where('descripcion', $nombreTarjeta)
                                        ->where('tipo', $tipo)
                                        ->value('id'),
                                    'tarjeta' => $item['tarjeta']
                                ]);
                            }
                        }

                        // Billetera Digital
                        if (!empty($item['billetera'])) {
                            $billeteraDescripcion = trim($item['billetera']);
                            $actualizacionesDetalleMovcaja = array_merge($actualizacionesDetalleMovcaja, [
                                'billeteradigital_id' => Billeteradigital::where('descripcion', $billeteraDescripcion)->value('id'),
                                'billeteradigital' => $billeteraDescripcion
                            ]);
                        }

                        // POS
                        if (!empty($item['pos'])) {
                            $posDescripcion = trim($item['pos']);
                            $actualizacionesDetalleMovcaja = array_merge($actualizacionesDetalleMovcaja, [
                                'pos_id' => Pasarelapago::where('descripcion', $posDescripcion)->value('id'),
                                'pos' => $posDescripcion
                            ]);
                        }
                    }

                    if ($actualizacionesDetalleMovcaja) {
                        $detalleMovcaja->update($actualizacionesDetalleMovcaja);
                    }
                }

                if ($tipodocumento->abreviatura !== 'T') {
                    // dd(collect($detalles));
                    $solicitudFacturacion = $facturadorController->enviarFacturacionElectronica(
                        $movventa,
                        collect($detalles),
                        [],
                        $persona,
                        $sucursalVenta,
                        Facturador::where('sucursal_id', $request->sucursal_id)->first(),
                        $movcaja,
                        collect($detallesMovCaja),
                        false
                    );
                    // 🔴 Si hubo un error lógico en la solicitud, cancelar la transacción
                    if (isset($solicitudFacturacion['success']) && $solicitudFacturacion['success'] === false) {
                        throw new \Exception($solicitudFacturacion['message'] ?? 'Error en la facturación electrónica');
                    }
                }
            } else {

                $tipodocumento = Tipodocumento::findOrFail($request->tipodocumento_id);
                $caja = (int) $request->caja_id === (int) $cajaActiva->id
                    ? $cajaActiva
                    : Caja::findOrFail($request->caja_id);

                $numeroVenta = $tipodocumento->abreviatura === 'T'
                    ? ($this->numeroDesdeComprobanteRequest($request) ?? $this->generateNumero($request->tipodocumento_id, true, $tipodocumento, $responsable, $cajaActiva))
                    : $this->generateNumero($request->tipodocumento_id, true, $tipodocumento, $responsable, $cajaActiva);

                $movimientoParaVenta = Movimiento::create([
                    'numero' => $numeroVenta,
                    'fecha' => now(),
                    'usuario_id' => $usuario->id,
                    'usuario' => $usuario->name,
                    'responsable_id' => $responsable->id,
                    'responsable' => "{$responsable->apellidos} {$responsable->nombres}",
                    'persona_id' => $request->persona_id,
                    'persona' => $persona ? "{$persona->apellidos} {$persona->nombres}" : 'N/A',
                    'comentario' => $request->comentario ?? '',
                    'situacion' => 'A',
                    'tipomovimiento_id' => $tipodocumento->tipomovimiento_id,
                    'tipodocumento_id' => $request->tipodocumento_id,
                    'sucursal_id' => $request->sucursal_id,
                    'movimiento_id' => $movimientoHabitacion->movimiento_id,
                ]);



                // Buscar el valor del IGV predeterminado en la tabla rel_parametrosucursal
                $igvPredeterminado = RelParametrosucursal::where('sucursal_id', $sucursal->id)
                    ->where('parametro_id', 8) // ID del parámetro para IGV
                    ->value('valor'); // Tomamos directamente el campo 'valor'
                $igvSeleccionado = $request->input('igv_id') == 0 ? $igvPredeterminado : $request->input('igv_id');
                $igvObjeto = Igv::find($igvSeleccionado);
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
                    'serie' => $tipodocumento->abreviatura . '' . $caja->serie,
                    'anio' => now()->year,
                    'tipo_detalle' => 'DETALLADO',
                    'tipo_pago' => $this->resolverTipoPago(json_decode($request->detalles_movimientos_json, true) ?: []),
                    'estado' => 'N',
                    'moneda' => $request->moneda,
                    'tipocambio' => 3.71,
                    'subtotal' => $request->total/($igvObjeto->porcentaje_igv / 100 + 1),
                    'igv' => $request->total - $request->total/($igvObjeto->porcentaje_igv / 100 + 1),
                    'total' => $request->total ?? $request->total_a_pagar_productos,
                    'movimiento_id' => $movimientoParaVenta->id,
                    'sucursal_id' => $request->sucursal_id,
                    'json_sucursal' => $jsonSucursalVenta,
                    'movhabitacion_id' => $movimientoHabitacion->id,
                    'movreserva_id' => $movimientoHabitacion->movimiento->movreserva->id ?? null,
                ]);
                $unidad = Unidad::find(17);
                $total_original = $request->total_a_pagar_alojamiento + $request->descuento;

                $detalles = DetMovventum::create([
                    'movventa_id' => $movventa->id,
                    'tipo_detalle' => 'DETALLADO',
                    'codigo' => '-',
                    'descripcion' => $request->descripcion_alojamiento ?? 'Checkout de habitación',
                    'producto_id' => null,
                    'json_producto' => '[]',
                    'unidad_id' => 17,
                    'json_unidad' => $unidad ? json_encode($unidad) : null,
                    'igv_id' => $igvObjeto->id, // Asignar el igv_id desde la relación
                    'json_igv' => $igvObjeto ? json_encode($igvObjeto) : null,
                    'cantidad' => 1,
                    'monto' => $request->total_a_pagar_alojamiento,
                    'dcto_porcentaje' => $total_original > 0
                        ? ($request->descuento / $total_original) * 100
                        : 0,
                    'monto_original' => $total_original,

                    'comentario' => $request->comentario ?? '',
                    'det_categoria' => '[]',
                    'opc_categoria' => '[]',
                    'det_movventa_id' => null,
                    'complementos' => json_encode([]),
                    'sucursal_id' => $request->sucursal_id,
                ]);
                if ($request->switch_state == 'grupo') {
                    // 🔹 Cambiar todas las habitaciones asociadas a este movimiento
                    MovhabitacionHabitacion::where('movhabitacion_id', $movimientoHabitacion->id)
                        ->update([
                            'situacion' => 'P',
                            'movventa_id' => $movventa->id
                        ]);

                    // También cambiar el estado del movimiento de habitación
                    $movimientoHabitacion->situacion = 'P'; // Pagado
                    $movimientoHabitacion->save();
                } else {
                    $totalVentasExistentes = Movventum::where('movhabitacion_id', $movimientoHabitacion->id)
                        ->sum('total'); // Suma todos los totales de las ventas asociadas
                    // dd($request->total_pagado, $request->total_a_pagar_alojamiento, $request->total, $totalVentasExistentes);
                    if ($request->total_pagado + $request->total < $request->totalalojamiento) {
                        // Si el total pagado es mayor o igual al total a pagar, marcar como pagado
                        $movimientoHabitacion->situacion = 'A'; // Pagado
                        MovhabitacionHabitacion::where('movhabitacion_id', $movimientoHabitacion->id)
                            ->where('habitacion_id', $id) // Pagado
                            ->update([
                                'situacion' => 'A',
                                'movventa_id' => $movventa->id
                            ]);
                    } else {
                        // Si no, marcar como pendiente
                        $movimientoHabitacion->situacion = 'P'; // Pendiente
                        MovhabitacionHabitacion::where('movhabitacion_id', $movimientoHabitacion->id)
                            ->where('habitacion_id', $id) // Pagado
                            ->update([
                                'situacion' => 'P',
                                'movventa_id' => $movventa->id
                            ]);
                    }


                    // Verificar si todas las habitaciones del movimiento están en 'P'
                    $pendientes = MovhabitacionHabitacion::where('movhabitacion_id', $movimientoHabitacion->id)
                        ->where('situacion', '!=', 'P') // Si hay alguna que no sea 'P'
                        ->count();

                    if ($pendientes == 0) {
                        // Si todas están en 'P', actualizar el movhabitacion
                        $movimientoHabitacion->situacion = 'P'; // Pagado
                        $movimientoHabitacion->save();
                    }
                }

                if ($movimientoHabitacion->movimiento) {
                    // Obtener los detalles existentes antes de eliminarlos
                    $detallesExistentes = DetMovhabitacion::where('movhabitacion_id', $movimientoHabitacion->id)->get();
                    $restauraciones = $detallesExistentes
                        ->groupBy(fn ($detalle) => $detalle->producto_id . ':' . $detalle->sucursal_id)
                        ->map->sum('cantidad');
                    $stocksExistentes = Stockproducto::whereIn(
                        'producto_id',
                        $detallesExistentes->pluck('producto_id')->filter()->unique()
                    )->whereIn(
                        'sucursal_id',
                        $detallesExistentes->pluck('sucursal_id')->filter()->unique()
                    )->get()
                        ->groupBy(fn ($stock) => $stock->producto_id . ':' . $stock->sucursal_id)
                        ->map->first();

                    foreach ($restauraciones as $claveStock => $cantidad) {
                        $stockProducto = $stocksExistentes->get($claveStock);
                        if ($stockProducto) {
                            $stockProducto->stockbase += $cantidad;
                            $stockProducto->save();
                        }
                    }

                    // Ahora eliminar los registros de det_movhabitacion
                    DetMovhabitacion::where('movhabitacion_id', $movimientoHabitacion->id)->where('situacion', 'N')->forceDelete();
                }
                // Si $detalles es un objeto de tipo DetMovventum, convertirlo en un arreglo
                if ($detalles instanceof DetMovventum) {
                    $detalles = [$detalles->toArray()]; // Convertimos el objeto en un arreglo
                } else {
                    // Si ya es un arreglo o vacío, lo dejamos como está
                    $detalles = $detalles ?? [];
                }

                // Obtener nuevos detalles desde el request (si hay)
                $nuevosDetalles = json_decode($request->input('detalles_json', '[]'), true);

                // Validar que realmente sean un array
                if (!is_array($nuevosDetalles)) {
                    $nuevosDetalles = [];
                }


                // Filtrar los que no son 'P' o los que no tienen el campo 'situacion'
                $nuevosDetalles = array_filter($nuevosDetalles, function ($nuevosDetalles) {
                    return !isset($nuevosDetalles['situacion']) || $nuevosDetalles['situacion'] !== 'P';
                });

                if (!empty($nuevosDetalles)) {
                    $productoIds = collect($nuevosDetalles)->pluck('producto_id')->filter()->unique()->values();
                    $unidadesPorDescripcion = Unidad::whereIn(
                        'descripcion',
                        collect($nuevosDetalles)->pluck('unidad')->filter()->unique()
                    )->get()->groupBy('descripcion')->map->first();
                    $productosPorId = Producto::with('categorium')
                        ->whereIn('id', $productoIds)
                        ->get()
                        ->keyBy('id');
                    $stocksPorProducto = Stockproducto::where('sucursal_id', $request->sucursal_id)
                        ->whereIn('producto_id', $productoIds)
                        ->get()
                        ->groupBy('producto_id')
                        ->map->first();

                    foreach ($nuevosDetalles as $detalle) {

                        if (isset($detalle['situacion']) && $detalle['situacion'] !== 'P') {
                            $unidad = isset($detalle['unidad'])
                                ? $unidadesPorDescripcion->get($detalle['unidad'])
                                : null;

                            // Obtener el producto usando el producto_id del detalle
                            $producto = $productosPorId->get($detalle['producto_id']);

                            // Obtener la categoría asociada al producto, si existe
                            $categoria = $producto && $producto->categorium ? $producto->categorium : null;

                            // Verificar si existe el registro en stockproducto
                            $stockProducto = $stocksPorProducto->get($detalle['producto_id']);

                            // Registrar detalles en DetMovventum independientemente de la clasificación
                            $detMovVenta = DetMovventum::create([
                                'movventa_id' => $movventa->id,
                                'situacion' => 'E',
                                'tipo_detalle' => 'DETALLADO',
                                'codigo' => $detalle['codigo'],
                                'descripcion' => $detalle['descripcion'],
                                'producto_id' => $detalle['producto_id'],
                                'json_producto' => json_encode($detalle),
                                'unidad_id' => $unidad ? $unidad->id : null,
                                'json_unidad' => $unidad ? json_encode($unidad) : null,
                                'igv_id' => $detalle['igv_id'] ?? null,
                                'cantidad' => $detalle['cantidad'],
                                'monto' => $detalle['precio_unitario'] * $detalle['cantidad'],
                                'dcto_porcentaje' => $detalle['dcto_porcentaje'] ?? 0,
                                'monto_original' => $detalle['total'],
                                'comentario' => $detalle['notas'] ?? '',
                                'det_categoria' => json_encode($detalle['categoria'] ?? []),
                                'opc_categoria' => json_encode($detalle['opc_categoria'] ?? []),
                                'sucursal_id' => $request->sucursal_id,
                            ]);

                            // **Agregar DetMovHabitacion**
                            DetMovhabitacion::create([
                                'movhabitacion_id' => $movimientoHabitacion->id,
                                'tipo_detalle' => 'DETALLADO',
                                'codigo' => $detalle['codigo'],
                                'descripcion' => $detalle['descripcion'],
                                'producto_id' => $detalle['producto_id'],
                                'json_producto' => json_encode($detalle),
                                'unidad_id' => $unidad ? $unidad->id : null,
                                'json_unidad' => $unidad ? json_encode($unidad) : null,
                                'igv_id' => $detalle['igv_id'] ?? null,
                                'cantidad' => $detalle['cantidad'],
                                'monto' => $detalle['precio_unitario'] * $detalle['cantidad'],
                                'dcto_porcentaje' => $detalle['dcto_porcentaje'] ?? 0,
                                'monto_original' => $detalle['total'],
                                'comentario' => $detalle['notas'] ?? '',
                                'det_categoria' => $categoria ? json_encode($categoria) : null,
                                'opc_categoria' => json_encode($detalle['opc_categoria'] ?? []),
                                'json_categoria' => $categoria ? json_encode($categoria) : null,
                                'sucursal_id' => $request->sucursal_id,
                                'situacion' => 'P',
                                'det_movventa_id' => $detMovVenta->id, // Relación con DetMovventum
                            ]);

                            // Descontar el stock según el nuevo detalle
                            if ($stockProducto) {
                                $stockProducto->stockbase -= $detalle['cantidad'];
                                $stockProducto->save();
                            }
                        }
                    }
                }

                // Unir los detalles existentes con los nuevos
                $detalles = array_merge($detalles, $nuevosDetalles);

                // Crear nuevo registro en Movcaja
                $turnoCaja = RelTurnocaja::conCajaActiva($request->caja_id)
                    ->with('movcajainicio.turno')
                    ->first();
                $turnoId = $turnoCaja ? $turnoCaja->movcajainicio->turno_id : null;

                $movimientoParaCaja = Movimiento::create([
                    'numero' => $this->generateNumeroMovcaja($request->sucursal_id, $caja->id, 4),
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
                    'sucursal_id' => $request->sucursal_id,
                    'movimiento_id' => $movimientoParaVenta->id
                ]);

                $movcaja = Movcaja::create([
                    'conceptopago_id' => 4,
                    'moneda' => $request->moneda,
                    'tipocambio' => 3.71,
                    'total' => $request->total ?? $request->total_a_pagar_productos,
                    'caja_id' => $caja->id,
                    'caja' => $caja->numero,
                    'turno_id' => $turnoId,
                    'json_turno' => json_encode($turnoCaja?->movcajainicio?->turno),
                    'movimiento_id' => $movimientoParaCaja->id,
                    'sucursal_id' => $request->sucursal_id,
                ]);

                if ($accion !== 'cobrar_checkout') {


                    HistorialHabitacion::create([
                        'sucursal_id' => Auth::user()->persona->sucursal_id,
                        'movhabitacion_id' => $movimientoHabitacion->id,
                        'relturnocaja_id' => $turnoCaja->id,
                        'situacion_pago' => 'S',
                        'situacion_checkout' => 'N',
                    ]);
                }

                $detallesMovCaja = json_decode($request->detalles_movimientos_json, true);
                $mediosPago = Mediopago::whereIn(
                    'id',
                    collect($detallesMovCaja)->pluck('mediopago_id')->filter()->unique()
                )->pluck('descripcion', 'id');

                foreach ($detallesMovCaja as $detalle) {
                    $detalleMovcaja = DetMovcaja::create([
                        'movcaja_id' => $movcaja->id,
                        'tipo' => $detalle['tipo'] ?? null,
                        'fechavencimiento' => !empty($detalle['fechavencimiento']) ? $detalle['fechavencimiento'] : null,
                        'fechapago' => now(),

                        // Medio de pago
                        'mediopago_id' => $detalle['mediopago_id'] ?? null,
                        'mediopago' => $detalle['mediopago_id']
                            ? ($mediosPago->get($detalle['mediopago_id']) ?? '')
                            : '',

                        'numero' => $detalle['numero'] ?? '',

                        // Inicializar campos dinámicos
                        'tarjeta_id' => null,
                        'tarjeta' => '',
                        'banco_id' => null,
                        'banco' => '',
                        'billeteradigital_id' => null,
                        'billeteradigital' => '',
                        'pos_id' => null,
                        'pos' => '',

                        // Monto y otros campos
                        'monto' => $detalle['monto'] ?? 0.00,
                        'comentario' => $detalle['comentario'] ?? '',
                        'situacion' => $detalle['situacion'] ?? 'A',
                        'sucursal_id' => $movcaja->sucursal_id,
                    ]);

                    // Obtener el medio de pago en minúsculas
                    $medioPago = strtolower($detalleMovcaja->mediopago);

                    // Si el medio de pago es "Efectivo", saltar la asignación de detalles adicionales
                    if ($medioPago === 'efectivo') {
                        continue;
                    }

                    $actualizacionesDetalleMovcaja = [];

                    // Recorrer el JSON de detalles si no es efectivo
                    foreach ($detalle['detalles'] ?? [] as $item) {
                        // Banco
                        if (!empty($item['banco'])) {
                            $bancoDescripcion = trim($item['banco']);
                            $actualizacionesDetalleMovcaja = array_merge($actualizacionesDetalleMovcaja, [
                                'banco_id' => Banco::where('descripcion', $bancoDescripcion)->value('id'),
                                'banco' => $bancoDescripcion
                            ]);
                        }

                        // Tarjeta
                        if (!empty($item['tarjeta'])) {
                            $partes = explode(' - ', $item['tarjeta']);
                            if (count($partes) >= 2) {
                                $nombreTarjeta = trim($partes[0]);
                                $tipoTarjeta = strtolower(trim($partes[1]));
                                $tipo = str_contains($tipoTarjeta, 'débito') ? 'D' : 'C';

                                $actualizacionesDetalleMovcaja = array_merge($actualizacionesDetalleMovcaja, [
                                    'tarjeta_id' => Tarjetum::where('descripcion', $nombreTarjeta)
                                        ->where('tipo', $tipo)
                                        ->value('id'),
                                    'tarjeta' => $item['tarjeta']
                                ]);
                            }
                        }

                        // Billetera Digital
                        if (!empty($item['billetera'])) {
                            $billeteraDescripcion = trim($item['billetera']);
                            $actualizacionesDetalleMovcaja = array_merge($actualizacionesDetalleMovcaja, [
                                'billeteradigital_id' => Billeteradigital::where('descripcion', $billeteraDescripcion)->value('id'),
                                'billeteradigital' => $billeteraDescripcion
                            ]);
                        }

                        // POS
                        if (!empty($item['pos'])) {
                            $posDescripcion = trim($item['pos']);
                            $actualizacionesDetalleMovcaja = array_merge($actualizacionesDetalleMovcaja, [
                                'pos_id' => Pasarelapago::where('descripcion', $posDescripcion)->value('id'),
                                'pos' => $posDescripcion
                            ]);
                        }
                    }

                    if ($actualizacionesDetalleMovcaja) {
                        $detalleMovcaja->update($actualizacionesDetalleMovcaja);
                    }
                }

                $facturadorController = new FacturadorController();
                // dd($detalles);

                if ($tipodocumento->abreviatura !== 'T') {

                    $solicitudFacturacion = $facturadorController->enviarFacturacionElectronica(
                        $movventa,
                        collect($detalles),
                        [],
                        $persona,
                        $sucursalVenta,
                        Facturador::where('sucursal_id', $request->sucursal_id)->first(),
                        $movcaja,
                        collect($detallesMovCaja),
                        false,
                        null,

                    );
                    // 🔴 Si hubo un error lógico en la solicitud, cancelar la transacción
                    if (isset($solicitudFacturacion['success']) && $solicitudFacturacion['success'] === false) {
                        throw new \Exception($solicitudFacturacion['message'] ?? 'Error en la facturación electrónica');
                    }
                }

                if ($accion === 'cobrar_checkout') {

                    $movimientoHabitacion->total = $request->totalalojamiento;
                    $movimientoHabitacion->early_checkin = $request->input('early', 0);
                    $movimientoHabitacion->late_checkout = $request->input('late', 0);
                    $movimientoHabitacion->save();
                    if ($housekeeping === 'Si') {
                        $housekeeping = Housekeeping::create([
                            'fecha_programada' => now(),
                            'observaciones' => '',
                            'sucursal_id' => $persona->sucursal_id,
                        ]);

                        HousekeepingDetalle::create([
                            'jornada_id' => $housekeeping->id,
                            'habitacion_id' => $movimientoHabitacion->habitacion_id,
                            'empleado_id' => null,
                            'estado' => 'Pendiente',
                        ]);
                    }
                    // 🔹 Verificar si se cambia en grupo o solo una habitación
                    if ($request->switch_state == 'grupo') {
                        // **Actualizar todas las habitaciones asociadas a este movhabitacion**
                        $habitacionesGrupo = MovhabitacionHabitacion::where('movhabitacion_id', $movimientoHabitacion->id)->pluck('habitacion_id');

                        // Cerrar detalles antes de recalcular el estado físico de cada cuarto.
                        MovhabitacionHabitacion::where('movhabitacion_id', $movimientoHabitacion->id)
                            ->update([
                                'situacion' => 'C',
                                'fechacheckout' => now(),
                            ]);

                        // Cerrar también el movhabitacion
                        $movimientoHabitacion->situacion = 'C';
                        $movimientoHabitacion->fecha_salida_real = now();
                        $movimientoHabitacion->save();

                        foreach ($habitacionesGrupo as $habitacionGrupoId) {
                            HabitacionAvailability::markCleaningIfNoActiveStay((int) $habitacionGrupoId);
                        }

                        // **Si hay una reserva asociada, actualizar su estado**
                        if ($movimientoHabitacion->movreserva_id !== null) {
                            $movreserva = Movreserva::find($movimientoHabitacion->movreserva_id);
                            if ($movreserva) {
                                $movreserva->estado = 'FINALIZADO';
                                $movreserva->fechasalida = now();
                                $movreserva->save();

                                // **Actualizar todas las habitaciones asociadas en movreserva_habitacion**
                                MovreservaHabitacion::where('movreserva_id', $movreserva->id)
                                    ->update([
                                        'situacion' => 'FINALIZADO',
                                        'fechacheckout' => now()
                                    ]);
                            }
                        }
                    } else {
                        // **Modo Único**
                        // Cambiar el estado de la habitación a "Limpieza"
                        Habitacion::findOrFail($id);

                        // Cambiar solo la habitación específica en movhabitacion_habitacion
                        MovhabitacionHabitacion::where('movhabitacion_id', $movimientoHabitacion->id)
                            ->where('habitacion_id', $id) // Solo la habitación actual
                            ->update([
                                'situacion' => 'C',
                                'fechacheckout' => now(),
                            ]);
                        HabitacionAvailability::markCleaningIfNoActiveStay((int) $id);
                        // Cambiar solo la habitación específica en movhabitacion_habitacion
                        if (!is_null($movimientoHabitacion->movreserva_id)) {
                            // Cambiar solo la habitación específica en movreserva_habitacion
                            MovreservaHabitacion::where('movreserva_id', $movimientoHabitacion->movreserva_id)
                                ->where('habitacion_id', $id) // Solo la habitación actual
                                ->update([
                                    'situacion' => 'FINALIZADO',
                                    'fechacheckout' => now() // Registrar la fecha de salida
                                ]);

                            // Buscar y actualizar la reserva si existe
                            $movreserva = Movreserva::find($movimientoHabitacion->movreserva_id);
                            if ($movreserva) {
                                $movreserva->estado = 'FINALIZADO';
                                $movreserva->fechasalida = now();
                                $movreserva->save();
                            }
                        }


                        // 🔹 Verificar si es la última habitación pendiente
                        $pendientes = MovhabitacionHabitacion::where('movhabitacion_id', $movimientoHabitacion->id)
                            ->where('situacion', '!=', 'C') // Que aún no estén cerradas
                            ->count();

                        if ($pendientes == 0) {
                            // Si es la última, cerrar también el movhabitacion
                            $movimientoHabitacion->situacion = 'C'; // Cerrado
                            $movimientoHabitacion->fecha_salida_real = now();
                            $movimientoHabitacion->save();

                            if (!is_null($movimientoHabitacion->movreserva_id)) {

                                // Buscar y actualizar la reserva si existe
                                $movreserva = Movreserva::find($movimientoHabitacion->movreserva_id);
                                if ($movreserva) {
                                    $movreserva->estado = 'FINALIZADO';
                                    $movreserva->fechasalida = now();
                                    $movreserva->save();
                                }
                            }
                        }
                    }

                    // Registrar el historial de la habitación
                    HistorialHabitacion::create([
                        'sucursal_id' => Auth::user()->persona->sucursal_id,
                        'movhabitacion_id' => $movimientoHabitacion->id,
                        'relturnocaja_id' => $ultimoTurno->id,
                        'situacion_pago' => 'S',
                        'situacion_checkout' => 'S',
                    ]);

                    DB::commit();
                }
            }

            if ($accion === 'cobrar_checkout' or $accion === 'cobrar') {

                $tipoimpresion = RelParametroSucursal::where('sucursal_id', $sucursal->id)
                    ->whereHas('parametro', function ($query) {
                        $query->where('descripcion', 'Tipo Impresión Comprobante');
                    })
                    ->value('valor');
                if ($tipoimpresion == 'Ticket') {
                    $pdfUrl = route('movventa.imprimirComprobante', ['id' => $movventa->id]);
                } else {
                    $pdfUrl = route('movventa.generarPDF', ['id' => $movventa->id]);
                }

                DB::commit();
                if (isset($solicitudFacturacion)) {
                    if ($request->vista == 'alojamiento') {
                        return response()->json([
                            'success' => true,
                            'message' => 'Checkout registrado con éxito.',
                            'redirect_url' => route('movhabitacion.index'),
                            'pdf_url' => $pdfUrl,

                            'facturacion_response' => $solicitudFacturacion
                        ]);
                    } else {
                        return response()->json([
                            'success' => true,
                            'pdf_url' => $pdfUrl,
                            'message' => 'Checkout registrado exitosamente.',
                            'redirect_url' => route('calendarioreservas.index'),
                            'facturacion_response' => $solicitudFacturacion
                        ]);
                    }
                } else {
                    if ($request->vista == 'alojamiento') {
                        return response()->json([
                            'success' => true,
                            'pdf_url' => $pdfUrl,
                            'message' => 'Checkout registrado exitosamente.',
                            'redirect_url' => route('movhabitacion.index')
                        ]);
                    } else {
                        return response()->json([
                            'success' => true,
                            'pdf_url' => $pdfUrl,
                            'message' => 'Checkout registrado exitosamente.',
                            'redirect_url' => route('calendarioreservas.index'),
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $payload = [
                'success' => false,
                'errors' => $e->getMessage(),
            ];
            if (in_array($accion, ['cobrar', 'cobrar_checkout'], true)) {
                $payload['redirect_url'] = route('movhabitacion.index');
            }

            return response()->json($payload, 500);
        }
    }
    public function checkouts(Request $request)
    {
        try {
            // Obtener la sucursal del usuario autenticado
            $sucursal_id = Auth::user()->persona->sucursal_id;

            // Obtener las operaciones relacionadas con la tabla 'pasarelapago'
            $operaciones = Operacione::where('tabla_id', 68)
                ->where('estado', true)
                ->get();

            // Consulta base con relaciones y filtro por sucursal
            $query = Movhabitacion::with(['habitacion', 'movimiento'])
                ->where('sucursal_id', $sucursal_id)
                ->orderBy('fechaingreso', 'desc');


            // Aplicar filtros dinámicos
            if ($request->has('habitacion') && $request->habitacion != '') {
                $query->whereHas('habitacion', function ($q) use ($request) {
                    $q->where('nombre', 'LIKE', '%' . $request->habitacion . '%');
                });
            }

            if ($request->has('cliente') && $request->cliente != '') {
                $query->whereHas('reserva.huespeds', function ($q) use ($request) {
                    $q->where('nombre', 'LIKE', '%' . $request->cliente . '%');
                });
            }

            if ($request->has('estado') && $request->estado != '') {
                $query->where('situacion', $request->estado);
            }
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal = Sucursal::findOrFail($persona->sucursal_id);

            $relTurnocajas = RelTurnocaja::where('sucursal_id', $persona->sucursal_id)
                ->orderBy('fechainicio', 'desc')
                ->paginate(10);
            // Por defecto: mostrar solo datos del turno en curso
            $turnoEnCursoId = RelTurnocaja::where('sucursal_id', $sucursal_id)
                ->whereNull('fechafin')
                ->latest('fechainicio')
                ->value('id');

            if ($turnoEnCursoId) {
                $movhabitacionIdsTurno = HistorialHabitacion::where('sucursal_id', $sucursal_id)
                    ->where('relturnocaja_id', $turnoEnCursoId)
                    ->pluck('movhabitacion_id')
                    ->unique()
                    ->values();

                if ($movhabitacionIdsTurno->isEmpty()) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('id', $movhabitacionIdsTurno);
                }
            }
            // Paginación
            $movimientoHabitacion = $query->paginate(10);

            return view('movhabitacion.checkouts', compact('movimientoHabitacion', 'operaciones', 'relTurnocajas', 'turnoEnCursoId'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
    public function filter(Request $request)
    {
        try {
            $sucursal_id = Auth::user()->persona->sucursal_id;
            $search = $request->input('search');
            $turnoRequest = $request->input('turno');
            $fechaInicio = $request->input('fechaInicio');
            $fechaFin = $request->input('fechaFin');
            $modalidad = $request->input('modalidad');
            $situacion = $request->input('situacion');
            $perPage = $request->input('perPage', 10);

            // Establecer fechas por defecto si están vacías
            if (!$fechaInicio || !$fechaFin) {
                $fechaInicio = Carbon::now()->startOfMonth();
                $fechaFin = Carbon::now()->endOfDay();
            } else {
                $fechaInicio = Carbon::parse($fechaInicio)->startOfDay();
                $fechaFin = Carbon::parse($fechaFin)->endOfDay();
            }

            $operaciones = Operacione::where('tabla_id', 68)
                ->where('estado', true)
                ->get();

            $query = Movhabitacion::with(['habitacion', 'movimiento.persona'])
                ->select('movhabitacion.*') // <-- esto asegura todas las columnas
                ->where('sucursal_id', $sucursal_id)
                ->orderBy('fechaingreso', 'desc');


            // Filtro por turno (igual criterio funcional a ventas: resolver IDs y filtrar el listado)
            if ($turnoRequest === "0" || $turnoRequest === 0) {
                // sin filtro adicional
            } elseif (!empty($turnoRequest)) {
                $turnoValido = RelTurnocaja::where('id', $turnoRequest)
                    ->where('sucursal_id', $sucursal_id)
                    ->exists();

                if (!$turnoValido) {
                    $query->whereRaw('1 = 0');
                } else {
                    $movhabitacionIdsTurno = HistorialHabitacion::where('sucursal_id', $sucursal_id)
                        ->where('relturnocaja_id', $turnoRequest)
                        ->pluck('movhabitacion_id')
                        ->unique()
                        ->values();

                    if ($movhabitacionIdsTurno->isEmpty()) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $query->whereIn('id', $movhabitacionIdsTurno);
                    }
                }
            }

            // Filtro de búsqueda: habitación o cliente
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('habitacion', function ($subQuery) use ($search) {
                        $subQuery->where('numero', 'LIKE', "%{$search}%");
                    })->orWhereHas('movimiento.persona', function ($subQuery) use ($search) {
                        $subQuery->where('nombres', 'LIKE', "%{$search}%");
                    });
                });
            }

            // Filtro por modalidad
            if (!empty($modalidad)) {
                $query->where('modalidad', $modalidad); // 'D' o 'H'
            }
            // Filtro por situación
            if (!empty($situacion)) {

                if ($situacion === 'A') {
                    // A = activos + pendientes
                    $query->whereIn('situacion', ['A', 'P']);
                } else {
                    // cualquier otra situación filtra normal
                    $query->where('situacion', $situacion);
                }
            }



            // Filtro por fechas
            if ($fechaInicio && $fechaFin) {
                $query->whereBetween('fechaingreso', [$fechaInicio, $fechaFin]);
            }
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);

            $movimientoHabitacion = $query->paginate($perPage);
            $relTurnocajas = RelTurnocaja::where('sucursal_id', $persona->sucursal_id)
                ->orderBy('fechainicio', 'desc')
                ->paginate(10);

            return view('movhabitacion.checkouts', compact('movimientoHabitacion', 'operaciones', 'relTurnocajas'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
    public function alojamientos(Request $request)
    {
        try {
            $sucursal_id = Auth::user()->persona->sucursal_id;

            $operaciones = Operacione::where('tabla_id', 68)
                ->where('estado', true)
                ->get();

            $perPage = (int) ($request->input('perPage', 20));
            if ($perPage <= 0) {
                $perPage = 20;
            }

            $query = Movhabitacion::query()
                ->with(['habitacion.tipohabitacion', 'habitacion.piso', 'huespeds.persona.personamaestro', 'habitaciones'])
                ->where('sucursal_id', $sucursal_id)
                ->whereNotIn('situacion', ['C', 'D'])
                ->whereNull('deleted_at')
                ->orderBy('fechaingreso', 'desc');

            if ($request->filled('habitacion')) {
                $termHabitacion = trim($request->habitacion);
                $query->whereHas('habitacion', function ($q) use ($termHabitacion) {
                    $q->where('numero', 'LIKE', '%' . $termHabitacion . '%');
                });
            }

            if ($request->filled('cliente')) {
                $termCliente = trim($request->cliente);
                $query->whereHas('huespeds.persona', function ($q) use ($termCliente) {
                    $q->where('nombres', 'LIKE', '%' . $termCliente . '%')
                        ->orWhere('apellidos', 'LIKE', '%' . $termCliente . '%');
                });
            }

            if ($request->filled('estado')) {
                $query->where('situacion', $request->estado);
            }

            $movimientoHabitacion = $query->paginate($perPage);

            $situaciones = ['En curso', 'Disponibles', 'En Limpieza', 'En mantenimiento'];
            $situacionactual = 'En curso';

            return view('movhabitacion.alojamientos', compact('movimientoHabitacion', 'operaciones', 'situaciones', 'situacionactual'));
        } catch (\Exception $e) {
            Log::error('Error en /alojamientos', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'url' => $request->fullUrl(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->getMessage()
                ], 500);
            }

            return response('Error en /alojamientos: ' . $e->getMessage(), 500);
        }
    }
    public function checkoutsproductos($movid)
    {
        try {
            // Obtener el movimiento actual de la habitación
            $movimientoHabitacion = Movhabitacion::find($movid);


            // Obtener los productos consumidos relacionados con el movimiento
            $productosConsumidos = DetMovhabitacion::whereHas('movhabitacion', function ($query) use ($movimientoHabitacion) {
                $query->where('movimiento_id', $movimientoHabitacion->movimiento_id);
            })
                ->get()
                ->map(function ($producto) {
                    // Decodificar el JSON del campo json_producto
                    $datosProducto = json_decode($producto->json_producto, true);

                    // Formatear los datos en el formato esperado
                    return [
                        'producto_id' => $producto->producto_id, // ID del producto
                        'clasificacion' => $producto->producto->clasificacion,
                        'descripcion' => $datosProducto['descripcion'] ?? '', // Descripción del producto
                        'precio_compra' => $datosProducto['precio_compra'] ?? 0, // Precio de compra
                        'unidad' => $datosProducto['unidad'] ?? 'Unidad(es)', // Unidad
                        'cantidad' => $datosProducto['cantidad'] ?? 0, // Cantidad
                        'igv' => $datosProducto['igv'] ?? 0, // IGV
                        'precio_unitario' => $datosProducto['precio_unitario'] ?? 0, // Precio unitario
                        'total' => ($datosProducto['cantidad'] ?? 0) * ($datosProducto['precio_unitario'] ?? 0), // Total
                        'codigo' => $datosProducto['codigo'] ?? '', // Código del producto
                        'categoria' => $datosProducto['categoria'] ?? [],
                        'opc_categoria' => $datosProducto['opc_categoria'] ?? [],
                        'notas' => $datosProducto['notas'] ?? '', // Notas adicionales
                        'situacion' => $producto->situacion ?? '', // Notas adicionales

                    ];
                });

            $detallesJson = $productosConsumidos->toJson();
            // Calcular el total de los productos consumidos
            $totalProductosConsumidos = $productosConsumidos->reduce(function ($carry, $producto) {
                $cantidad = $producto['cantidad'] ?? 0;
                $precioUnitario = $producto['precio_unitario'] ?? 0;
                return $carry + ($cantidad * $precioUnitario);
            }, 0);

            return view('movhabitacion.detalleproductos', compact('productosConsumidos', 'detallesJson', 'totalProductosConsumidos'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }


    public function productos($id)
    {
        $tipos_documento = Tipodocumento::all();
        $sucursal_id = Auth::user()->persona->sucursal_id;

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);
        $personas = Persona::where('sucursal_id', $sucursal->id)->get();

        $productos = Producto::with(['relProductosucursal', 'relListaUnidadSucursal'])
            ->whereHas('relProductosucursal', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id);
            })
            ->get();
        $categorias = Categorium::whereNull('categoria_id')
            ->whereHas('relCategoriasucursal', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id)->where('tipocarta', '!=', 'NINGUNA');
            })
            ->with('categorias')
            ->get();
        $cajas = Caja::where('situacion', 'A')->where('sucursal_id', $sucursal->id)->get();

        // Obtener el huésped principal asociado a la habitación
        $movhabitacion = Movhabitacion::with('huespeds.persona')
            ->where('habitacion_id', $id)
            ->whereIn('situacion', ['A', 'P']) // Filtrar por situación 'A' o 'P'
            ->first();
        $huesped = Huesped::where('movhabitacion_id', $movhabitacion->id)->first();
        $clientePrincipal = $huesped->persona ?? null;
        // Obtener los tipos de documento relacionados con `tipo_movimiento_id = 2`
        $tiposDocumento = Tipodocumento::where('tipomovimiento_id', 2)
            ->get();
        $tipoDocumentoParametro = RelParametrosucursal::where('sucursal_id', $sucursal->id)
            ->where('parametro_id', 10) // ID del parámetro para IGV
            ->value('valor'); // Tomamos directamente el campo 'valor'
        // Tipo de documento predeterminado (Ticket)
        $tipoDocumentoPredeterminado = $tiposDocumento->where('id', $tipoDocumentoParametro)->first();
        // Obtener la abreviatura del tipo de documento
        $abreviatura = $tipoDocumentoPredeterminado->abreviatura ?? 'N/A';

        // Asignar la primera caja de la colección si $movimientoCaja->caja no está disponible
        $caja = $cajas->first();

        // Determinar la serie
        $serie = $caja ? str_pad($caja->serie, 3, "0", STR_PAD_LEFT) : '001';
        // dd($movimientoHabitacion);
        $correlativo = $this->generateNumero($tipoDocumentoPredeterminado->id, false);
        // Formatear el número correlativo con ceros a la izquierda
        $correlativo = str_pad($correlativo, 8, "0", STR_PAD_LEFT);


        // Generar el número de comprobante
        $numeroComprobante = "{$abreviatura}{$serie}-{$correlativo}";
        // Formatear los productos para el autocomplete
        $vendedores = Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 1);
        })->where('sucursal_id', $sucursal->id)->get();

        // Ajuste dentro del map de $productosConsumidos
        $productosConsumidos = DetMovhabitacion::whereHas('movhabitacion', function ($query) use ($movhabitacion) {
            $query->where('movimiento_id', $movhabitacion->movimiento_id);
        })
            ->get()
            ->map(function ($producto) {
                $datosProducto = json_decode($producto->json_producto, true);

                return [
                    'producto_id' => $producto->producto_id,
                    'situacion' => $producto->situacion,

                    'descripcion' => $datosProducto['descripcion'] ?? '',
                    'precio_compra' => $datosProducto['precio_compra'] ?? 0,
                    'unidad' => $datosProducto['unidad'] ?? 'Unidad(es)',
                    'cantidad' => $datosProducto['cantidad'] ?? 0,
                    'igv' => $datosProducto['igv'] ?? 0,
                    'precio_unitario' => $datosProducto['precio_unitario'] ?? 0,
                    'total' => ($datosProducto['cantidad'] ?? 0) * ($datosProducto['precio_unitario'] ?? 0),
                    'codigo' => $datosProducto['codigo'] ?? '',
                    // Asegura que categoría sea siempre un string
                    'categoria' => $datosProducto['categoria'] ?? 'Sin Categoría',
                    // Convertir opc_categoria a objeto vacío si está vacío
                    'opc_categoria' => (object) ($datosProducto['opc_categoria'] ?? []),
                    'notas' => $datosProducto['notas'] ?? '',
                ];
            });

        // Ahora usa json_encode con JSON_UNESCAPED_UNICODE
        // Ajuste en el controlador
        $detallesMovimientosJson = json_encode($productosConsumidos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


        // Calcular el total de los productos consumidos con situación 'N'
        $totalProductosConsumidos = $productosConsumidos->filter(function ($producto) {
            return $producto['situacion'] === 'N';
        })->reduce(function ($carry, $producto) {
            $cantidad = $producto['cantidad'] ?? 0;
            $precioUnitario = $producto['precio_unitario'] ?? 0;
            return $carry + ($cantidad * $precioUnitario);
        }, 0);

        $medio_pagos = Mediopago::where('situacion', true)->get();
        $pos = Pasarelapago::where('situacion', true)->get(); // Tabla para P.O.S.
        $tarjetas = Tarjetum::where('situacion', true)->get(); // Tabla para Tarjetas
        $bancos = Banco::where('situacion', true)->get(); // Tabla para Bancos
        $billeteras = Billeteradigital::where('situacion', true)->get();
        return view('movhabitacion.productos', compact(
            'tipos_documento',
            'personas',
            'sucursal_id',
            'vendedores',
            'movhabitacion',
            'categorias',
            'cajas',
            'clientePrincipal',
            'id',
            'tipoDocumentoPredeterminado',
            'numeroComprobante',
            'tiposDocumento',
            'detallesMovimientosJson',
            'productosConsumidos',
            'totalProductosConsumidos',
            'medio_pagos',
            'pos',
            'tarjetas',
            'bancos',
            'billeteras'

        ));
    }

    public function servicios($id)
    {
        $tipos_documento = Tipodocumento::all();
        $sucursal_id = Auth::user()->persona->sucursal_id;
        $personas = Persona::where('sucursal_id', $sucursal_id)->get();

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);

        $productos = Producto::with(['relProductosucursal', 'relListaUnidadSucursal'])
            ->whereHas('relProductosucursal', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id);
            })
            ->get();
        // $categorias = Categorium::whereNull('categoria_id')
        //     ->whereHas('relCategoriasucursal', function ($query) use ($sucursal) {
        //         $query->where('sucursal_id', $sucursal->id);
        //     })
        //     ->with('categorias')
        //     ->get();

        // Obtener los productos filtrados por categoría, clasificación y sucursal
        $productoscards = Producto::where('clasificacion', 'Servicio') // Filtra por clasificación
            ->whereHas('relProductosucursal', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id); // Filtra por sucursal
            })
            ->with('relProductosucursal.igv', 'relListaUnidadSucursal', 'unidad') // Cargar relaciones necesarias
            ->get()
            ->map(function ($producto) {
                // Obtener el precio de venta y el IGV
                $ventaUnit = floatval($producto->relListaUnidadSucursal->precioventa ?? 0);
                $igv = floatval($producto->relProductosucursal->igv->porcentaje_igv ?? 0);

                // Devolver el producto con los campos necesarios
                return [
                    'id' => $producto->id,
                    'descripcion' => $producto->descripcion,
                    'unidad' => $producto->unidad->descripcion ?? 'Unidad',
                    'precio_total' => $ventaUnit * (1 + $igv / 100)
                ];
            });
        $cajas = Caja::where('situacion', 'A')->where('sucursal_id', $sucursal->id)->get();

        // Obtener el huésped principal asociado a la habitación
        $movhabitacion = Movhabitacion::with('huespeds.persona')
            ->where('habitacion_id', $id)
            ->whereIn('situacion', ['A', 'P']) // Filtrar por situación 'A' o 'P'
            ->first();
        $huesped = Huesped::where('movhabitacion_id', $movhabitacion->id)->first();
        $clientePrincipal = $huesped->persona ?? null;
        // Obtener los tipos de documento relacionados con `tipo_movimiento_id = 2`
        $tiposDocumento = Tipodocumento::where('tipomovimiento_id', 2)
            ->get();
        $tipoDocumentoParametro = RelParametrosucursal::where('sucursal_id', $sucursal->id)
            ->where('parametro_id', 10) // ID del parámetro para IGV
            ->value('valor'); // Tomamos directamente el campo 'valor'
        // Tipo de documento predeterminado (Ticket)
        $tipoDocumentoPredeterminado = $tiposDocumento->where('id', $tipoDocumentoParametro)->first();
        // Obtener la abreviatura del tipo de documento
        $abreviatura = $tipoDocumentoPredeterminado->abreviatura ?? 'N/A';
        // Obtener todas las cajas activas

        // Asignar la primera caja de la colección si $movimientoCaja->caja no está disponible
        $caja = $cajas->first();

        // Determinar la serie
        $serie = $caja ? str_pad($caja->serie, 3, "0", STR_PAD_LEFT) : '001';
        // dd($movimientoHabitacion);
        $correlativo = $this->generateNumero($tipoDocumentoPredeterminado->id, false);
        // Formatear el número correlativo con ceros a la izquierda
        $correlativo = str_pad($correlativo, 8, "0", STR_PAD_LEFT);


        // Generar el número de comprobante
        $numeroComprobante = "{$abreviatura}{$serie}-{$correlativo}";
        // Formatear los productos para el autocomplete
        $vendedores = Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 1);
        })->where('sucursal_id', $sucursal->id)->get();


        // Obtener los productos consumidos relacionados con el movimiento
        $productosConsumidos = DetMovhabitacion::whereHas('movhabitacion', function ($query) use ($movhabitacion) {
            $query->where('movimiento_id', $movhabitacion->movimiento_id);
        })
            ->get()
            ->map(function ($producto) {
                // Decodificar el JSON del campo json_producto
                $datosProducto = json_decode($producto->json_producto, true);

                // Formatear los datos en el formato esperado
                return [
                    'producto_id' => $producto->producto_id, // ID del producto
                    'situacion' => $producto->situacion,

                    'descripcion' => $datosProducto['descripcion'] ?? '', // Descripción del producto
                    'precio_compra' => $datosProducto['precio_compra'] ?? 0, // Precio de compra
                    'unidad' => $datosProducto['unidad'] ?? 'Unidad(es)', // Unidad
                    'cantidad' => $datosProducto['cantidad'] ?? 0, // Cantidad
                    'igv' => $datosProducto['igv'] ?? 0, // IGV
                    'precio_unitario' => $datosProducto['precio_unitario'] ?? 0, // Precio unitario
                    'total' => ($datosProducto['cantidad'] ?? 0) * ($datosProducto['precio_unitario'] ?? 0), // Total
                    'codigo' => $datosProducto['codigo'] ?? '', // Código del producto
                    'categoria' => $datosProducto['categoria'] ?? [],
                    'opc_categoria' => $datosProducto['opc_categoria'] ?? [],
                    'notas' => $datosProducto['notas'] ?? '', // Notas adicionales
                ];
            });
        $detallesMovimientosJson = json_encode($productosConsumidos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        // Calcular el total de los productos consumidos
        $totalProductosConsumidos = $productosConsumidos->filter(function ($producto) {
            return $producto['situacion'] === 'N';
        })->reduce(function ($carry, $producto) {
            $cantidad = $producto['cantidad'] ?? 0;
            $precioUnitario = $producto['precio_unitario'] ?? 0;
            return $carry + ($cantidad * $precioUnitario);
        }, 0);
        $medio_pagos = Mediopago::where('situacion', true)->get();
        $pos = Pasarelapago::where('situacion', true)->get(); // Tabla para P.O.S.
        $tarjetas = Tarjetum::where('situacion', true)->get(); // Tabla para Tarjetas
        $bancos = Banco::where('situacion', true)->get(); // Tabla para Bancos
        $billeteras = Billeteradigital::where('situacion', true)->get();
        return view('movhabitacion.servicios', compact(
            'tipos_documento',
            'personas',
            'sucursal_id',
            'movhabitacion',
            'vendedores',
            'productoscards',
            'productos',
            'cajas',
            'clientePrincipal',
            'id',
            'tipoDocumentoPredeterminado',
            'numeroComprobante',
            'tiposDocumento',
            'detallesMovimientosJson',
            'productosConsumidos',
            'totalProductosConsumidos',
            'medio_pagos',
            'pos',
            'tarjetas',
            'bancos',
            'billeteras'

        ));
    }
    public function storeProductos(Request $request)
    {
        DB::beginTransaction();

        try {
            // Obtener el estado del switch
            $switchState = $request->input('switch_state', 'habitacion'); // Por defecto es 'habitacion'
            // Validación general
            $rules = [
                'sucursal_id' => 'required|exists:sucursal,id',
                'moneda' => 'required|string|in:PEN,USD',
                'total' => 'required|numeric|min:0',
                'persona_id' => 'nullable|exists:persona,id',
                'comentario' => 'nullable|string',
            ];

            if ($switchState === 'pago_caja') {
                // Validaciones adicionales para "Pago Caja"
                $rules = array_merge($rules, [
                    'tipo_detalle' => 'required|string|in:DETALLADO,GLOSA',
                    'detalles_json' => 'required|json',
                    'detalles_movimientos_json' => 'required|json',
                    'caja_id' => 'required|exists:caja,id',
                ]);
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Datos generales
            $usuario = Auth::user();
            $personasucursal = Persona::find($usuario->persona_id);
            $sucursal = Sucursal::find($personasucursal->sucursal_id);
            $responsable = $usuario->persona;
            $persona = $request->persona_id ? Persona::find($request->persona_id) : null;
            $caja = Caja::findOrFail($request->caja_id);
            $tipodocumento = Tipodocumento::findOrFail($request->tipodocumento_id);

            // Buscar el movimiento relacionado con la habitación
            $movHabitacion = Movhabitacion::where('habitacion_id', $request->habitacion_id)
                ->whereIn('situacion', ['A', 'P']) // Filtrar por situación 'A' o 'P'
                ->first();

            if ($switchState === 'habitacion') {
                if (!$movHabitacion) {
                    return response()->json([
                        'success' => false,
                        'errors' => 'No se encontró un movimiento activo para la habitación seleccionada.'
                    ], 404);
                }

                // Restaurar el stock antes de eliminar los registros existentes
                if ($movHabitacion->movimiento) {
                    // Obtener los detalles existentes antes de eliminarlos
                    $detallesExistentes = DetMovhabitacion::where('movhabitacion_id', $movHabitacion->id)->get();

                    foreach ($detallesExistentes as $detalleExistente) {
                        // Restaurar el stock en base a los detalles eliminados
                        $stockProducto = Stockproducto::where('producto_id', $detalleExistente->producto_id)
                            ->where('sucursal_id', $detalleExistente->sucursal_id)
                            ->first();

                        if ($stockProducto) {
                            $stockProducto->stockbase += $detalleExistente->cantidad;
                            $stockProducto->save();
                        }
                    }

                    // Ahora eliminar los registros de det_movhabitacion
                    DetMovhabitacion::where('movhabitacion_id', $movHabitacion->id)->where('situacion', 'N')->forceDelete();
                }

                // Procesar e insertar nuevos detalles en DetMovhabitacion
                $detalles = json_decode($request->input('detalles_json', '[]'), true);

                foreach ($detalles as $detalle) {
                    // Verificar si la situación es 'P'; si es así, omitir este detalle
                    if (isset($detalle['situacion']) && $detalle['situacion'] === 'P') {
                        continue; // Saltar a la siguiente iteración del bucle
                    }

                    $unidad = Unidad::where('descripcion', $detalle['unidad'])->first();

                    // Obtener el producto usando el producto_id del detalle
                    $producto = Producto::with('categorium')->find($detalle['producto_id']);

                    // Obtener la categoría asociada al producto, si existe
                    $categoria = $producto && $producto->categorium ? $producto->categorium : null;

                    // Verificar si existe el registro en stockproducto
                    $stockProducto = Stockproducto::where('producto_id', $detalle['producto_id'])
                        ->where('sucursal_id', $request->sucursal_id)
                        ->first();

                    // **Agregar DetMovHabitacion**
                    DetMovhabitacion::create([
                        'movhabitacion_id' => $movHabitacion->id,
                        'tipo_detalle' => 'DETALLADO',
                        'codigo' => $detalle['codigo'],
                        'descripcion' => $detalle['descripcion'],
                        'producto_id' => $detalle['producto_id'],
                        'json_producto' => json_encode($detalle),
                        'unidad_id' => $unidad ? $unidad->id : null,
                        'json_unidad' => $unidad ? json_encode($unidad) : null,
                        'igv_id' => $detalle['igv_id'] ?? null,
                        'cantidad' => $detalle['cantidad'],
                        'monto' => $detalle['precio_unitario'] * $detalle['cantidad'],
                        'dcto_porcentaje' => $detalle['dcto_porcentaje'] ?? 0,
                        'monto_original' => $detalle['total'],
                        'comentario' => $detalle['notas'] ?? '',
                        'det_categoria' => $categoria ? json_encode($categoria) : null,
                        'opc_categoria' => json_encode($detalle['opc_categoria'] ?? []),
                        'json_categoria' => $categoria ? json_encode($categoria) : null,
                        'sucursal_id' => $request->sucursal_id,
                        'situacion' => 'N',
                    ]);

                    // Descontar el stock según el nuevo detalle
                    if ($stockProducto) {
                        $stockProducto->stockbase -= $detalle['cantidad'];
                        $stockProducto->save();
                    }
                }


                DB::commit();

                return response()->json([
                    'success' => true,

                    'message' => 'Producto agregado a la habitación exitosamente.',
                    'redirect_url' => route('movhabitacion.index')
                ]);
            } elseif ($switchState === 'pago_caja') {

                // Crear Movimiento
                $movimientoParaVenta = Movimiento::create([
                    'numero' => $this->generateNumero($request->tipodocumento_id),
                    'fecha' => now(),
                    'usuario_id' => $usuario->id,
                    'usuario' => $usuario->name,
                    'responsable_id' => $responsable->id,
                    'responsable' => " {$responsable->apellidos} {$responsable->nombres}",
                    'persona_id' => $request->persona_id,
                    'persona' => $persona ? "{$persona->apellidos} {$persona->nombres}" : 'N/A',
                    'comentario' => $request->comentario ?? '',
                    'situacion' => 'A',
                    'tipomovimiento_id' => Tipodocumento::find($request->tipodocumento_id)->tipomovimiento_id,
                    'tipodocumento_id' => $request->tipodocumento_id,
                    'sucursal_id' => $request->sucursal_id,
                    'movimiento_id' => $movHabitacion->movimiento_id,
                ]);

                // Procesar detalles para obtener los igv_id de los productos
                $detalles = json_decode($request->detalles_json, true);
                $totalIgv = 0; // Variable para acumular el IGV total
                $totalSinIgv = 0; // Variable para acumular el subtotal (sin IGV)

                foreach ($detalles as $detalle) {
                    if ($detalle['situacion'] == 'N') {
                        // Obtener la relación del producto con la sucursal
                        $relProductoSucursal = RelProductosucursal::where('producto_id', $detalle['producto_id'])
                            ->where('sucursal_id', $sucursal->id)
                            ->first();

                        // Verificar que exista la relación
                        if ($relProductoSucursal) {
                            // Obtener el porcentaje de IGV para este producto desde la relación
                            $igv = $relProductoSucursal->igv;

                            if ($igv) {
                                $porcentajeIgv = $igv->porcentaje_igv;
                                // Calcular el IGV para este detalle de producto
                                $igvProducto = $detalle['precio_unitario'] * $detalle['cantidad'] * ($porcentajeIgv / 100);
                                $totalIgv += $igvProducto; // Sumar al IGV total
                            }
                        }

                        // Acumular el subtotal (precio sin IGV)
                        $totalSinIgv += $detalle['precio_unitario'] * $detalle['cantidad'];
                    }
                }


                $movventa = Movventum::create([
                    'fecha' => now(),
                    'usuario_id' => $usuario->id,
                    'usuario' => $usuario->name,
                    'responsable_id' => $responsable->id,
                    'responsable' => "{$responsable->nombres} {$responsable->apellidos}",
                    'persona_id' => $request->persona_id,
                    'persona' => $persona ? "{$persona->nombres} {$persona->apellidos}" : 'N/A',
                    'serie' => $tipodocumento->abreviatura . '' . $caja->serie,
                    'anio' => now()->year,
                    'tipo_detalle' => $request->tipo_detalle,
                    'estado' => $request->estado ?? 'N',
                    'moneda' => $request->moneda,
                    'tipocambio' => 3.71,
                    'subtotal' => $request->total - $totalIgv,
                    'tipo_pago' => $this->resolverTipoPago(json_decode($request->detalles_movimientos_json, true) ?: []),
                    'igv' => $totalIgv,
                    'total' => $request->total,
                    'movimiento_id' => $movimientoParaVenta->id,
                    'sucursal_id' => $request->sucursal_id,
                    'json_sucursal' => json_encode(Sucursal::find($request->sucursal_id)),
                    'movhabitacion_id' => $movHabitacion->id,
                ]);


                // Restaurar el stock antes de eliminar los registros existentes
                if ($movHabitacion->movimiento) {
                    // Obtener los detalles existentes antes de eliminarlos
                    $detallesExistentes = DetMovhabitacion::where('movhabitacion_id', $movHabitacion->id)->get();

                    foreach ($detallesExistentes as $detalleExistente) {
                        // Restaurar el stock en base a los detalles eliminados
                        $stockProducto = Stockproducto::where('producto_id', $detalleExistente->producto_id)
                            ->where('sucursal_id', $detalleExistente->sucursal_id)
                            ->first();

                        if ($stockProducto) {
                            $stockProducto->stockbase += $detalleExistente->cantidad;
                            $stockProducto->save();
                        }
                    }

                    // Ahora eliminar los registros de det_movhabitacion
                    DetMovhabitacion::where('movhabitacion_id', $movHabitacion->id)->where('situacion', 'N')->forceDelete();
                }
                // Procesar e insertar nuevos detalles en DetMovhabitacion
                $detalles = json_decode($request->input('detalles_json', '[]'), true);

                foreach ($detalles as $detalle) {
                    // Verificar si la situación es 'P'; si es así, omitir este detalle
                    if (isset($detalle['situacion']) && $detalle['situacion'] === 'P') {
                        continue; // Saltar a la siguiente iteración del bucle
                    }

                    $unidad = Unidad::where('descripcion', $detalle['unidad'])->first();

                    // Obtener el producto usando el producto_id del detalle
                    $producto = Producto::with('categorium')->find($detalle['producto_id']);

                    // Obtener la categoría asociada al producto, si existe
                    $categoria = $producto && $producto->categorium ? $producto->categorium : null;

                    // Verificar si existe el registro en stockproducto
                    $stockProducto = Stockproducto::where('producto_id', $detalle['producto_id'])
                        ->where('sucursal_id', $request->sucursal_id)
                        ->first();

                    // Registrar detalles en DetMovventum independientemente de la clasificación
                    $detMovVenta = DetMovventum::create([
                        'movventa_id' => $movventa->id,
                        'situacion' => 'E',
                        'tipo_detalle' => 'DETALLADO',
                        'codigo' => $detalle['codigo'],
                        'descripcion' => $detalle['descripcion'],
                        'producto_id' => $detalle['producto_id'],
                        'json_producto' => json_encode($detalle),
                        'unidad_id' => $unidad ? $unidad->id : null,
                        'json_unidad' => $unidad ? json_encode($unidad) : null,
                        'igv_id' => $detalle['igv_id'] ?? null,
                        'cantidad' => $detalle['cantidad'],
                        'monto' => $detalle['precio_unitario'] * $detalle['cantidad'],
                        'dcto_porcentaje' => $detalle['dcto_porcentaje'] ?? 0,
                        'monto_original' => $detalle['total'],
                        'comentario' => $detalle['notas'] ?? '',
                        'det_categoria' => json_encode($detalle['categoria'] ?? []),
                        'opc_categoria' => json_encode($detalle['opc_categoria'] ?? []),
                        'sucursal_id' => $request->sucursal_id,
                    ]);

                    // **Agregar DetMovHabitacion**
                    DetMovhabitacion::create([
                        'movhabitacion_id' => $movHabitacion->id,
                        'tipo_detalle' => 'DETALLADO',
                        'codigo' => $detalle['codigo'],
                        'descripcion' => $detalle['descripcion'],
                        'producto_id' => $detalle['producto_id'],
                        'json_producto' => json_encode($detalle),
                        'unidad_id' => $unidad ? $unidad->id : null,
                        'json_unidad' => $unidad ? json_encode($unidad) : null,
                        'igv_id' => $detalle['igv_id'] ?? null,
                        'cantidad' => $detalle['cantidad'],
                        'monto' => $detalle['precio_unitario'] * $detalle['cantidad'],
                        'dcto_porcentaje' => $detalle['dcto_porcentaje'] ?? 0,
                        'monto_original' => $detalle['total'],
                        'comentario' => $detalle['notas'] ?? '',
                        'det_categoria' => $categoria ? json_encode($categoria) : null,
                        'opc_categoria' => json_encode($detalle['opc_categoria'] ?? []),
                        'json_categoria' => $categoria ? json_encode($categoria) : null,
                        'sucursal_id' => $request->sucursal_id,
                        'situacion' => 'P',
                        'det_movventa_id' => $detMovVenta->id, // Relación con DetMovventum
                    ]);

                    // Descontar el stock según el nuevo detalle
                    if ($stockProducto) {
                        $stockProducto->stockbase -= $detalle['cantidad'];
                        $stockProducto->save();
                    }
                }



                // Crear nuevo registro en Movcaja
                $turnoCaja = RelTurnocaja::conCajaActiva($request->caja_id)->first();
                $turnoId = $turnoCaja ? $turnoCaja->movcajainicio->turno_id : null;

                $movimientoParaCaja = Movimiento::create([
                    'numero' => $this->generateNumeroMovcaja($request->sucursal_id, $caja->id, 4),
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
                    'sucursal_id' => $request->sucursal_id,
                    'movimiento_id' => $movimientoParaVenta->id
                ]);

                $movcaja = Movcaja::create([
                    'conceptopago_id' => 4,
                    'moneda' => $request->moneda,
                    'tipocambio' => 3.71,
                    'total' => $request->total,
                    'caja_id' => $caja->id,
                    'caja' => $caja->numero,
                    'turno_id' => $turnoId,
                    'json_turno' => json_encode(Turno::find($turnoId)),
                    'movimiento_id' => $movimientoParaCaja->id,
                    'sucursal_id' => $request->sucursal_id,
                ]);
                // Procesar Detalles de Movcaja
                // Antes no se mencionó edición en detalles movcaja, pero por coherencia también podríamos eliminar antes de crear
                DetMovcaja::where('movcaja_id', $movcaja->id)->delete();

                $detallesMovCaja = json_decode($request->detalles_movimientos_json, true);

                foreach ($detallesMovCaja as $detalle) {
                    $mediopago = Mediopago::where('descripcion', $detalle['medio_pago'])->first();

                    DetMovcaja::create([
                        'movcaja_id' => $movcaja->id,
                        'tipo' => $detalle['tipo'],
                        'fechavencimiento' => !empty($detalle['fechavencimiento']) ? $detalle['fechavencimiento'] : null,
                        'fechapago' => now(),
                        'mediopago_id' => $mediopago ? $mediopago->id : null,
                        'mediopago' => $detalle['medio_pago'] ?? '',
                        'numero' => $detalle['numero'] ?? '',
                        'tarjeta_id' => $detalle['tarjeta_id'] ?? null,
                        'tarjeta' => $detalle['tarjeta'] ?? '',
                        'banco_id' => $detalle['banco_id'] ?? null,
                        'banco' => $detalle['banco'] ?? '',
                        'billeteradigital_id' => $detalle['billeteradigital_id'] ?? null,
                        'billeteradigital' => $detalle['billeteradigital'] ?? '',
                        'pos_id' => $detalle['pos_id'] ?? null,
                        'pos' => $detalle['pos'] ?? '',
                        'monto' => $detalle['monto'],
                        'comentario' => $detalle['comentario'] ?? '',
                        'situacion' => $detalle['situacion'] ?? 'A',
                        'sucursal_id' => $request->sucursal_id,
                    ]);
                }

                $facturadorController = new FacturadorController();
                $pdfUrl = route('movventa.imprimirComprobante', ['id' => $movventa->id]);

                $detallesFiltrados = collect($detalles)->filter(function ($detalle) {
                    return $detalle['situacion'] !== 'P';
                });

                if ($tipodocumento->abreviatura !== 'T') {
                    $solicitudFacturacion = $facturadorController->enviarFacturacionElectronica(
                        $movventa,
                        collect($detallesFiltrados),
                        [],
                        $persona,
                        Sucursal::find($request->sucursal_id),
                        Facturador::where('sucursal_id', $request->sucursal_id)->first(),
                        $movcaja,
                        collect($detallesMovCaja),
                        false
                    );
                    // 🔴 Si hubo un error lógico en la solicitud, cancelar la transacción
                    if (isset($solicitudFacturacion['success']) && $solicitudFacturacion['success'] === false) {
                        throw new \Exception($solicitudFacturacion['message'] ?? 'Error en la facturación electrónica');
                    }
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'pdf_url' => $pdfUrl,

                        'message' => 'Venta registrada con éxito.',
                        'redirect_url' => route('movhabitacion.index'),
                        'facturacion_response' => $solicitudFacturacion
                    ]);
                } else {
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'pdf_url' => $pdfUrl,

                        'message' => 'Venta registrada con éxito.',
                        'redirect_url' => route('movhabitacion.index'),
                    ]);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function actualizarComprobante($tipoDocumentoId)
    {
        // Obtener los tipos de documento relacionados con `tipo_movimiento_id = 2`
        $tiposDocumento = Tipodocumento::where('tipomovimiento_id', 2)->get();

        // Buscar el tipo de documento seleccionado por el ID
        $tipoDocumentoPredeterminado = $tiposDocumento->where('id', $tipoDocumentoId)->first();

        if (!$tipoDocumentoPredeterminado) {
            return response()->json(['success' => false, 'message' => 'Tipo de documento no encontrado']);
        }

        // Obtener la abreviatura del tipo de documento
        $abreviatura = $tipoDocumentoPredeterminado->abreviatura ?? 'N/A';

        // Obtener todas las cajas activas
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $cajas = Caja::where('situacion', 'A')->where('sucursal_id', $persona->sucursal_id)
            ->get();

        // Asignar la primera caja si no hay caja seleccionada
        $caja = $cajas->first();

        // Determinar la serie (con ceros a la izquierda)
        $serie = $caja ? str_pad($caja->serie, 3, "0", STR_PAD_LEFT) : '001';

        // Obtener el número correlativo del movimiento
        $movimiento = Movimiento::latest()->where('tipodocumento_id', $tipoDocumentoId)->where('situacion', 'A')->where('sucursal_id', $persona->sucursal_id)->first(); // Obtener el último movimiento
        $correlativo = $movimiento ? $movimiento->numero + 1 : 1; // Generar el próximo número

        // Formatear el número correlativo con ceros a la izquierda
        $correlativo = str_pad($correlativo, 8, "0", STR_PAD_LEFT);

        // Generar el número de comprobante
        $numeroComprobante = "{$abreviatura}{$serie}-{$correlativo}";

        return response()->json([
            'success' => true,
            'numero_comprobante' => $numeroComprobante
        ]);
    }

    /**
     * Deja de limpiar la habitación, marcándola como disponible.
     */
    public function dejarLimpiar($id, $opcion = null)
    {
        try {
            $habitacion = Habitacion::findOrFail($id);
            if (!HabitacionAvailability::markAvailableIfNoActiveStay((int) $habitacion->id)) {
                return response()->json([
                    'success' => false,
                    'errors' => 'No se puede liberar la habitación porque tiene un alojamiento activo.',
                ], 409);
            }
            $habitacionController = new HabitacionController();

            if ($opcion === 'CRUD') {
                $request = Request::capture();
                return $habitacionController->index($request);
            } else {
                return $this->index();
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
    public function Limpiar($id, $opcion = null)
    {
        try {
            $habitacion = Habitacion::findOrFail($id);
            $habitacion->situacion = 'Limpieza';  // Actualiza el estado de la habitación
            $habitacion->save();
            $habitacionController = new HabitacionController();

            if ($opcion === 'CRUD') {
                $request = Request::capture();
                return $habitacionController->index($request);
            } else {
                return $this->index();
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function mantenimiento($id, $opcion = null)
    {
        try {
            $habitacion = Habitacion::findOrFail($id);
            $habitacion->situacion = 'Mantenimiento';  // Actualiza el estado de la habitación
            $habitacion->save();
            $habitacionController = new HabitacionController();

            if ($opcion === 'CRUD') {
                $request = Request::capture();
                return $habitacionController->index($request);
            } else {
                return $this->index();
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function vistaGeneral()
    {
        try {
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);

            // Cargar habitaciones con la sucursal actual
            $habitaciones = Habitacion::with(['tipohabitacion', 'sucursal', 'piso'])
                ->where('sucursal_id', $persona->sucursal_id)
                ->get();

            return response()->json([
                'success' => true,
                'habitaciones' => $habitaciones,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function filtrarPorCategoria($categoriaId)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);

        $Habitaciones = Habitacion::with(['tipohabitacion', 'sucursal', 'piso'])
            ->where('sucursal_id', $persona->sucursal_id);

        if ($categoriaId !== 'todas') {
            $Habitaciones = $Habitaciones->whereHas('tipohabitacion', function ($query) use ($categoriaId) {
                $query->where('id', $categoriaId);
            });
        }

        $Habitaciones = $Habitaciones->orderBy('numero')->get();
        $Habitacionesparafiltrar = Habitacion::with('tipohabitacion', 'sucursal', 'piso')
            ->where('sucursal_id', $persona->sucursal_id)

            ->orderBy('numero')
            ->get();

        $Pisos = Piso::where('sucursal_id', $persona->sucursal_id)->orderBy('id')->get();
        $Categorias = Tipohabitacion::where('sucursal_id', $persona->sucursal_id)->orderBy('id')->get();

        // Agregar detalles por piso
        $Pisos = $Pisos->map(function ($piso) use ($Habitacionesparafiltrar) {
            $piso->habitacionesOcupadas = $Habitacionesparafiltrar->where('piso_id', $piso->id)->where('situacion', 'Ocupada')->count();
            $piso->habitacionesDisponibles = $Habitacionesparafiltrar->where('piso_id', $piso->id)->where('situacion', 'Disponible')->count();
            $piso->habitacionesLimpieza = $Habitacionesparafiltrar->where('piso_id', $piso->id)->where('situacion', 'Limpieza')->count();

            $piso->habitacionesMantenimiento = $Habitacionesparafiltrar->where('piso_id', $piso->id)->where('situacion', 'Mantenimiento')->count();
            return $piso;
        });

        // Obtener los totales generales para el nivel "General"
        $habitacionesOcupadasGeneral = $Habitacionesparafiltrar->where('situacion', 'Ocupada')->count();
        $habitacionesDisponiblesGeneral = $Habitacionesparafiltrar->where('situacion', 'Disponible')->count();
        $habitacionesMantenimientoGeneral = $Habitacionesparafiltrar->where('situacion', 'Mantenimiento')->count();
        $habitacionesLimpiezaGeneral = $Habitacionesparafiltrar->where('situacion', 'Limpieza')->count();


        $movHabitacionesOcupadas = Movhabitacion::whereHas('habitacion', function ($query) {
            $query->where('situacion', 'Ocupada');
        })
            ->where('sucursal_id', $persona->sucursal_id)
            ->where('situacion', '!=', 'C') // No canceladas
            ->with([
                'movhabitacion_habitacion.habitacion', // Relación con detalle y habitación
                'huespeds.persona' // Relación con huéspedes
            ])
            ->get();

        // Lista de habitaciones ocupadas (separadas por detalle de movhabitacion)
        $movimientosDetalle = collect();

        foreach ($movHabitacionesOcupadas as $movimiento) {
            foreach ($movimiento->movhabitacion_habitacion as $detalle) {
                $movimientosDetalle->push([
                    'movhabitacion_id' => $movimiento->id,
                    'habitacion_id' => $detalle->habitacion_id,
                    'habitacion' => $detalle->habitacion->numero ?? 'N/A',
                    'fechaingreso' => $movimiento->fechaingreso,
                    'fechasalida' => $movimiento->fechasalida,
                    'total' => $movimiento->total,
                    'comentario' => $movimiento->comentario,
                    'situacion' => $detalle->situacion,
                    'tarifa' => $detalle->tarifa,
                    'principal' => $detalle->principal,
                    'cliente' => optional($movimiento->huespeds->first()?->persona)->nombres . ' ' .
                        optional($movimiento->huespeds->first()?->persona)->apellidos
                ]);
            }
        }

        // Agrupar clientes por habitación
        $clientesPorHabitacion = [];
        foreach ($movimientosDetalle as $movimiento) {
            $clientesPorHabitacion[$movimiento['habitacion_id']] = $movimiento['cliente'] ?: ' ';
        }


        // Agrupar las horas por habitación (misma lógica que index(): por detalle, fechas completas)
        $horasPorHabitacion = $movimientosDetalle->map(function ($mov) {
            return [
                'habitacion_id' => $mov['habitacion_id'],
                'hora_inicio' => Carbon::parse($mov['fechaingreso'])->format('Y-m-d H:i:s'),
                'hora_fin' => Carbon::parse($mov['fechasalida'])->format('Y-m-d H:i:s'),
                'situacion' => $mov['situacion'],
            ];
        });
        $operaciones = Operacione::where('tabla_id', 68)
            ->where('estado', true)
            ->get();
        $movreservahoy = $this->obtenerMovreservasHoyParaControlHabitaciones();
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);
        $vista = RelParametroSucursal::where('sucursal_id', $sucursal->id)
            ->whereHas('parametro', function ($query) {
                $query->where('descripcion', 'Vista control de habitaciones');
            })
            ->value('valor');


        $caja = Caja::where('situacion', 'A')->where('sucursal_id', $sucursal->id)->first();
        $cajaId = $caja->id ?? null;
        // Obtener el último turno activo o cerrado para la caja
        $turnoCaja = RelTurnocaja::where('situacion', 'A') // Turno activo
            ->whereHas('movcajainicio', function ($query) use ($cajaId) {
                $query->where('caja_id', $cajaId);
            })
            ->latest('fechainicio') // Último turno
            ->first();
        $cajaAbierta = (bool) $turnoCaja;

        if (!$turnoCaja) {
            return response()->json([
                'success' => false,
                'errors' => 'No hay ninguna caja aperturada.',
            ], 404);
        }

        // Obtener movimientos con detalles
        $movimientos = Movcaja::with(['detMovcaja.Mediopago', 'conceptopago'])
            ->where('id', '>=', $turnoCaja->movcajainicio_id) // Desde el inicio del turno
            ->where('caja_id', $cajaId)
            ->whereHas('movimiento', function ($query) {
                $query->where('situacion', 'A'); // Filtrar movimientos con situación 'A'
            })
            ->orderBy('created_at', 'asc')
            ->get();


        $totales = [


            'ventas' => $movimientos->filter(function ($movcaja) {
                return $movcaja->movimiento->tipodocumento->tipomovimiento_id == 2 && $movcaja->conceptopago_id == 4;
            })->flatMap->detMovcaja->where('tipo', 'Pagado')->sum('monto'),



        ];
        $modalidadtarifario = RelParametroSucursal::where('sucursal_id', $sucursal->id)
            ->whereHas('parametro', function ($query) {
                $query->where('descripcion', 'Modalidad tarifario');
            })
            ->value('valor'); // esto devuelve directamente un string como 'Si' o 'No'

        DB::commit();
        if ($vista === 'Normal') {
            return view('movhabitacion.index', compact(
                'Habitaciones',
                'operaciones',
                'Pisos',
                'Categorias',
                'habitacionesOcupadasGeneral',
                'habitacionesDisponiblesGeneral',
                'habitacionesMantenimientoGeneral',
                'habitacionesLimpiezaGeneral',
                'horasPorHabitacion',
                'movimientosDetalle',
                'clientesPorHabitacion',
                'modalidadtarifario',
                'movreservahoy',
                'movHabitacionesOcupadas',
                'totales',
                'cajaAbierta'
            ));
        } else {
            return view('movhabitacion.indexv2', compact(
                'Habitaciones',
                'operaciones',
                'Pisos',
                'Categorias',
                'habitacionesOcupadasGeneral',
                'habitacionesDisponiblesGeneral',
                'habitacionesMantenimientoGeneral',
                'habitacionesLimpiezaGeneral',
                'horasPorHabitacion',
                'movimientosDetalle',
                'clientesPorHabitacion',
                'modalidadtarifario',
                'movreservahoy',
                'movHabitacionesOcupadas',
                'totales',
                'cajaAbierta'
            ));
        }
    }

    public function filtrarPorPiso($pisoId = null)
    {

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);

        $Habitaciones = Habitacion::with('tipohabitacion', 'sucursal', 'piso')
            ->where('sucursal_id', $persona->sucursal_id)
            ->when($pisoId && $pisoId != 0, function ($query) use ($pisoId) {
                return $query->where('piso_id', $pisoId);
            })
            ->orderBy('numero')
            ->get();

        $Habitacionesparafiltrar = Habitacion::with('tipohabitacion', 'sucursal', 'piso')
            ->where('sucursal_id', $persona->sucursal_id)

            ->orderBy('numero')
            ->get();


        $Pisos = Piso::where('sucursal_id', $persona->sucursal_id)->orderBy('id')->get();
        $Categorias = Tipohabitacion::where('sucursal_id', $persona->sucursal_id)->orderBy('id')->get();

        // Agregar detalles por piso
        $Pisos = $Pisos->map(function ($piso) use ($Habitacionesparafiltrar) {
            $piso->habitacionesOcupadas = $Habitacionesparafiltrar->where('piso_id', $piso->id)->where('situacion', 'Ocupada')->count();
            $piso->habitacionesDisponibles = $Habitacionesparafiltrar->where('piso_id', $piso->id)->where('situacion', 'Disponible')->count();
            $piso->habitacionesLimpieza = $Habitacionesparafiltrar->where('piso_id', $piso->id)->where('situacion', 'Limpieza')->count();

            $piso->habitacionesMantenimiento = $Habitacionesparafiltrar->where('piso_id', $piso->id)->where('situacion', 'Mantenimiento')->count();
            return $piso;
        });
   // Totales generales para "General"
        $habitacionesOcupadasGeneral = $Habitacionesparafiltrar->where('situacion', 'Ocupada')->count();
        $habitacionesDisponiblesGeneral = $Habitacionesparafiltrar->where('situacion', 'Disponible')->count();
        $habitacionesMantenimientoGeneral = $Habitacionesparafiltrar->where('situacion', 'Mantenimiento')->count();
        $habitacionesLimpiezaGeneral = $Habitacionesparafiltrar->where('situacion', 'Limpieza')->count();

            // Obtener movimientos de habitaciones ocupadas CON su detalle
            $movHabitacionesOcupadas = Movhabitacion::whereHas('movhabitacion_habitacion.habitacion', function ($query) {
                $query->where('situacion', 'Ocupada');
            })

                ->where('sucursal_id', $persona->sucursal_id)
                ->where('situacion', '!=', 'C') // No canceladas
                ->where('situacion', '!=', 'D') // No canceladas

                ->with([
                    'movhabitacion_habitacion.habitacion', // Relación con detalle y habitación
                    'huespeds.persona' // Relación con huéspedes
                ])
                ->get();

            // Lista de habitaciones ocupadas (separadas por detalle de movhabitacion)
            $movimientosDetalle = collect();

            foreach ($movHabitacionesOcupadas as $movimiento) {
                foreach ($movimiento->movhabitacion_habitacion as $detalle) {
                    $movimientosDetalle->push([
                        'movhabitacion_id' => $movimiento->id,
                        'habitacion_id' => $detalle->habitacion_id,
                        'habitacion' => $detalle->habitacion->numero ?? 'N/A',
                        'fechaingreso' => $movimiento->fechaingreso,
                        'fechasalida' => $movimiento->fechasalida,
                        'total' => $movimiento->total,
                        'situacion' => $detalle->situacion,
                        'tarifa' => $detalle->tarifa,
                        'comentario' => $movimiento->comentario,
                        'principal' => $detalle->principal,
                        'cliente' => optional($movimiento->huespeds->first()?->persona)->nombres . ' ' .
                            optional($movimiento->huespeds->first()?->persona)->apellidos
                    ]);
                }
            }


        // Agrupar clientes por habitación
        $clientesPorHabitacion = [];
        foreach ($movimientosDetalle as $movimiento) {
            $clientesPorHabitacion[$movimiento['habitacion_id']] = $movimiento['cliente'] ?: ' ';
        }

        // Agrupar las horas por habitación (misma lógica que index(): por detalle, fechas completas)
        $horasPorHabitacion = $movimientosDetalle->map(function ($mov) {
            return [
                'habitacion_id' => $mov['habitacion_id'],
                'hora_inicio' => Carbon::parse($mov['fechaingreso'])->format('Y-m-d H:i:s'),
                'hora_fin' => Carbon::parse($mov['fechasalida'])->format('Y-m-d H:i:s'),
                'situacion' => $mov['situacion'],
            ];
        });
        $pisoId = $pisoId ?? 'todos'; // Si no se especifica piso, selecciona 'todos' por defecto
        $operaciones = Operacione::where('tabla_id', 68)
            ->where('estado', true)
            ->get();
        $movreservahoy = $this->obtenerMovreservasHoyParaControlHabitaciones();

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);
        $vista = RelParametroSucursal::where('sucursal_id', $sucursal->id)
            ->whereHas('parametro', function ($query) {
                $query->where('descripcion', 'Vista control de habitaciones');
            })
            ->value('valor');


        $caja = Caja::where('situacion', 'A')->where('sucursal_id', $sucursal->id)->first();
        $cajaId = $caja->id ?? null;
        // Obtener el último turno activo o cerrado para la caja
        $turnoCaja = RelTurnocaja::where('situacion', 'A') // Turno activo
            ->whereHas('movcajainicio', function ($query) use ($cajaId) {
                $query->where('caja_id', $cajaId);
            })
            ->latest('fechainicio') // Último turno
            ->first();
        $cajaAbierta = (bool) $turnoCaja;

        if (!$turnoCaja) {
            return response()->json([
                'success' => false,
                'errors' => 'No hay ninguna caja aperturada.',
            ], 404);
        }

        // Obtener movimientos con detalles
        $movimientos = Movcaja::with(['detMovcaja.Mediopago', 'conceptopago'])
            ->where('id', '>=', $turnoCaja->movcajainicio_id) // Desde el inicio del turno
            ->where('caja_id', $cajaId)
            ->whereHas('movimiento', function ($query) {
                $query->where('situacion', 'A'); // Filtrar movimientos con situación 'A'
            })
            ->orderBy('created_at', 'asc')
            ->get();


        $totales = [


            'ventas' => $movimientos->filter(function ($movcaja) {
                return $movcaja->movimiento->tipodocumento->tipomovimiento_id == 2 && $movcaja->conceptopago_id == 4;
            })->flatMap->detMovcaja->where('tipo', 'Pagado')->sum('monto'),



        ];

        $modalidadtarifario = RelParametroSucursal::where('sucursal_id', $sucursal->id)
            ->whereHas('parametro', function ($query) {
                $query->where('descripcion', 'Modalidad tarifario');
            })
            ->value('valor'); // esto devuelve directamente un string como 'Si' o 'No'

        DB::commit();
        if ($vista === 'Normal') {
            return view('movhabitacion.index', compact(
                'Habitaciones',
                'operaciones',
                'Pisos',
                'Categorias',
                'habitacionesOcupadasGeneral',
                'habitacionesDisponiblesGeneral',
                'habitacionesMantenimientoGeneral',
                'habitacionesLimpiezaGeneral',
                'horasPorHabitacion',
                'movimientosDetalle',
                'clientesPorHabitacion',
                'modalidadtarifario',
                'movreservahoy',
                'movHabitacionesOcupadas',
                'totales',
                'pisoId',
                'cajaAbierta'
            ));
        } else {
            return view('movhabitacion.indexv2', compact(
                'Habitaciones',
                'operaciones',
                'Pisos',
                'Categorias',
                'habitacionesOcupadasGeneral',
                'habitacionesDisponiblesGeneral',
                'habitacionesMantenimientoGeneral',
                'habitacionesLimpiezaGeneral',
                'horasPorHabitacion',
                'movimientosDetalle',
                'clientesPorHabitacion',
                'modalidadtarifario',
                'movreservahoy',
                'movHabitacionesOcupadas',
                'totales',
                'pisoId',
                'cajaAbierta'
            ));
        }
    }

    public function filtrarPorEstado($estado)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);

        // Obtener todas las habitaciones de la sucursal
        $Habitaciones = Habitacion::with(['tipohabitacion', 'sucursal', 'piso'])
            ->where('sucursal_id', $persona->sucursal_id);

        // Filtrar por estado si no es "todos"
        if ($estado !== 'todos') {
            $Habitaciones = $Habitaciones->where('situacion', ucfirst($estado));
        }

        $Habitaciones = $Habitaciones->orderBy('numero')->get();
        $Habitacionesparafiltrar = Habitacion::with('tipohabitacion', 'sucursal', 'piso')
            ->where('sucursal_id', $persona->sucursal_id)

            ->orderBy('numero')
            ->get();

        $Pisos = Piso::where('sucursal_id', $persona->sucursal_id)->orderBy('id')->get();
        $Categorias = Tipohabitacion::where('sucursal_id', $persona->sucursal_id)->orderBy('id')->get();

        // Agregar detalles por piso
        $Pisos = $Pisos->map(function ($piso) use ($Habitacionesparafiltrar) {
            $piso->habitacionesOcupadas = $Habitacionesparafiltrar->where('piso_id', $piso->id)->where('situacion', 'Ocupada')->count();
            $piso->habitacionesDisponibles = $Habitacionesparafiltrar->where('piso_id', $piso->id)->where('situacion', 'Disponible')->count();
            $piso->habitacionesMantenimiento = $Habitacionesparafiltrar->where('piso_id', $piso->id)->where('situacion', 'Mantenimiento')->count();
            $piso->habitacionesLimpieza = $Habitacionesparafiltrar->where('piso_id', $piso->id)->where('situacion', 'Limpieza')->count();

            return $piso;
        });

       // Totales generales para "General"
        $habitacionesOcupadasGeneral = $Habitacionesparafiltrar->where('situacion', 'Ocupada')->count();
        $habitacionesDisponiblesGeneral = $Habitacionesparafiltrar->where('situacion', 'Disponible')->count();
        $habitacionesMantenimientoGeneral = $Habitacionesparafiltrar->where('situacion', 'Mantenimiento')->count();
        $habitacionesLimpiezaGeneral = $Habitacionesparafiltrar->where('situacion', 'Limpieza')->count();

            // Obtener movimientos de habitaciones ocupadas CON su detalle
            $movHabitacionesOcupadas = Movhabitacion::whereHas('movhabitacion_habitacion.habitacion', function ($query) {
                $query->where('situacion', 'Ocupada');
            })

                ->where('sucursal_id', $persona->sucursal_id)
                ->where('situacion', '!=', 'C') // No canceladas
                ->where('situacion', '!=', 'D') // No canceladas

                ->with([
                    'movhabitacion_habitacion.habitacion', // Relación con detalle y habitación
                    'huespeds.persona' // Relación con huéspedes
                ])
                ->get();

            // Lista de habitaciones ocupadas (separadas por detalle de movhabitacion)
            $movimientosDetalle = collect();

            foreach ($movHabitacionesOcupadas as $movimiento) {
                foreach ($movimiento->movhabitacion_habitacion as $detalle) {
                    $movimientosDetalle->push([
                        'movhabitacion_id' => $movimiento->id,
                        'habitacion_id' => $detalle->habitacion_id,
                        'habitacion' => $detalle->habitacion->numero ?? 'N/A',
                        'fechaingreso' => $movimiento->fechaingreso,
                        'fechasalida' => $movimiento->fechasalida,
                        'total' => $movimiento->total,
                        'situacion' => $detalle->situacion,
                        'tarifa' => $detalle->tarifa,
                        'comentario' => $movimiento->comentario,
                        'principal' => $detalle->principal,
                        'cliente' => optional($movimiento->huespeds->first()?->persona)->nombres . ' ' .
                            optional($movimiento->huespeds->first()?->persona)->apellidos
                    ]);
                }
            }


        // Agrupar clientes por habitación
        $clientesPorHabitacion = [];
        foreach ($movimientosDetalle as $movimiento) {
            $clientesPorHabitacion[$movimiento['habitacion_id']] = $movimiento['cliente'] ?: ' ';
        }

        // Agrupar las horas por habitación (misma lógica que index(): por detalle, fechas completas)
        $horasPorHabitacion = $movimientosDetalle->map(function ($mov) {
            return [
                'habitacion_id' => $mov['habitacion_id'],
                'hora_inicio' => Carbon::parse($mov['fechaingreso'])->format('Y-m-d H:i:s'),
                'hora_fin' => Carbon::parse($mov['fechasalida'])->format('Y-m-d H:i:s'),
                'situacion' => $mov['situacion'],
            ];
        });
        $operaciones = Operacione::where('tabla_id', 68)
            ->where('estado', true)
            ->get();
        $movreservahoy = $this->obtenerMovreservasHoyParaControlHabitaciones();

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);
        $vista = RelParametroSucursal::where('sucursal_id', $sucursal->id)
            ->whereHas('parametro', function ($query) {
                $query->where('descripcion', 'Vista control de habitaciones');
            })
            ->value('valor');


        $caja = Caja::where('situacion', 'A')->where('sucursal_id', $sucursal->id)->first();
        $cajaId = $caja->id ?? null;
        // Obtener el último turno activo o cerrado para la caja
        $turnoCaja = RelTurnocaja::where('situacion', 'A') // Turno activo
            ->whereHas('movcajainicio', function ($query) use ($cajaId) {
                $query->where('caja_id', $cajaId);
            })
            ->latest('fechainicio') // Último turno
            ->first();

        if (!$turnoCaja) {
            return response()->json([
                'success' => false,
                'errors' => 'No hay ninguna caja aperturada.',
            ], 404);
        }

        // Obtener movimientos con detalles
        $movimientos = Movcaja::with(['detMovcaja.Mediopago', 'conceptopago'])
            ->where('id', '>=', $turnoCaja->movcajainicio_id) // Desde el inicio del turno
            ->where('caja_id', $cajaId)
            ->whereHas('movimiento', function ($query) {
                $query->where('situacion', 'A'); // Filtrar movimientos con situación 'A'
            })
            ->orderBy('created_at', 'asc')
            ->get();


        $totales = [


            'ventas' => $movimientos->filter(function ($movcaja) {
                return $movcaja->movimiento->tipodocumento->tipomovimiento_id == 2 && $movcaja->conceptopago_id == 4;
            })->flatMap->detMovcaja->where('tipo', 'Pagado')->sum('monto'),



        ];
        $modalidadtarifario = RelParametroSucursal::where('sucursal_id', $sucursal->id)
            ->whereHas('parametro', function ($query) {
                $query->where('descripcion', 'Modalidad tarifario');
            })
            ->value('valor'); // esto devuelve directamente un string como 'Si' o 'No'

        DB::commit();
        if ($vista === 'Normal') {
            return view('movhabitacion.index', compact(
                'Habitaciones',
                'operaciones',
                'Pisos',
                'Categorias',
                'habitacionesOcupadasGeneral',
                'habitacionesDisponiblesGeneral',
                'habitacionesMantenimientoGeneral',
                'habitacionesLimpiezaGeneral',
                'horasPorHabitacion',
                'movimientosDetalle',
                'clientesPorHabitacion',
                'modalidadtarifario',
                'movreservahoy',
                'movHabitacionesOcupadas',
                'totales',
                'cajaAbierta'
            ));
        } else {
            return view('movhabitacion.indexv2', compact(
                'Habitaciones',
                'operaciones',
                'Pisos',
                'Categorias',
                'habitacionesOcupadasGeneral',
                'habitacionesDisponiblesGeneral',
                'habitacionesMantenimientoGeneral',
                'habitacionesLimpiezaGeneral',
                'horasPorHabitacion',
                'movimientosDetalle',
                'clientesPorHabitacion',
                'modalidadtarifario',
                'movreservahoy',
                'movHabitacionesOcupadas',
                'totales',
                'cajaAbierta'
            ));
        }
    }
    public function filterBySituacion(Request $request, $situacion)
    {
        try {
            // Obtener la sucursal del usuario autenticado
            $sucursal_id = Auth::user()->persona->sucursal_id;
            $perPage = $request->input('perPage', 10); // 👈 capturamos el valor

            // Obtener las operaciones relacionadas con la tabla 'pasarelapago'
            $operaciones = Operacione::where('tabla_id', 68)
                ->where('estado', true)
                ->get();

            if ($situacion === 'Disponibles') {
                // Usar el modelo Habitacion si la situación es "Disponibles"
                $query = Habitacion::with(['piso', 'tipohabitacion', 'sucursal'])
                    ->where('sucursal_id', $sucursal_id)
                    ->where('situacion', 'Disponible') // Filtrar habitaciones disponibles
                    ->orderBy('numero', 'asc'); // Ordenar por número de habitación de forma ascendente
                //dd($query);
            } elseif ($situacion === 'En mantenimiento') {
                $query = Habitacion::with(['piso', 'tipohabitacion', 'sucursal'])
                    ->where('sucursal_id', $sucursal_id)
                    ->where('situacion', 'Mantenimiento') // Filtrar habitaciones disponibles
                    ->orderBy('numero', 'asc'); // Ordenar por número de habitación de forma ascendente
                //dd($query);
            } elseif ($situacion === 'En Limpieza') {
                $query = Habitacion::with(['piso', 'tipohabitacion', 'sucursal'])
                    ->where('sucursal_id', $sucursal_id)
                    ->where('situacion', 'Limpieza') // Filtrar habitaciones disponibles
                    ->orderBy('numero', 'asc'); // Ordenar por número de habitación de forma ascendente
                //dd($query);
            } else {


                // Usar el modelo Movhabitacion para las otras situaciones
                $query = Movhabitacion::with(['habitacion', 'movreserva.huespeds'])
                    ->where('sucursal_id', $sucursal_id)

                    ->when($situacion === 'En curso', function ($query) {
                        return $query->where('situacion', '!=', 'C'); // Excluye 'C'
                    })
                    ->orderBy('fechaingreso', 'desc'); // Ordenar por fechaingreso
            }


            // Paginación
            $movimientoHabitacion = $query->paginate($perPage);
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $movHabitacionesOcupadas = Movhabitacion::whereHas('habitacion', function ($query) {
                $query->where('situacion', 'Ocupada');
            })
                ->where('sucursal_id', $persona->sucursal_id)
                ->where('situacion', '!=', 'C') // No canceladas
                ->with([
                    'movhabitacion_habitacion.habitacion', // Relación con detalle y habitación
                    'huespeds.persona' // Relación con huéspedes
                ])
                ->get();

            // Lista de habitaciones ocupadas (separadas por detalle de movhabitacion)
            $movimientosDetalle = collect();

            foreach ($movHabitacionesOcupadas as $movimiento) {
                foreach ($movimiento->movhabitacion_habitacion as $detalle) {
                    $movimientosDetalle->push([
                        'movhabitacion_id' => $movimiento->id,
                        'habitacion_id' => $detalle->habitacion_id,
                        'habitacion' => $detalle->habitacion->numero ?? 'N/A',
                        'fechaingreso' => $movimiento->fechaingreso,
                        'fechasalida' => $movimiento->fechasalida,
                        'total' => $movimiento->total,
                        'comentario' => $movimiento->comentario,
                        'situacion' => $detalle->situacion,
                        'tarifa' => $detalle->tarifa,
                        'principal' => $detalle->principal,
                        'cliente' => optional($movimiento->huespeds->first()?->persona)->nombres . ' ' .
                            optional($movimiento->huespeds->first()?->persona)->apellidos
                    ]);
                }
            }

            // Agrupar clientes por habitación
            $clientesPorHabitacion = [];
            foreach ($movimientosDetalle as $movimiento) {
                $clientesPorHabitacion[$movimiento['habitacion_id']] = $movimiento['cliente'] ?: ' ';
            }


            // Agrupar las horas por habitación (misma lógica que index(): por detalle, fechas completas)
            $horasPorHabitacion = $movimientosDetalle->map(function ($mov) {
                return [
                    'habitacion_id' => $mov['habitacion_id'],
                    'hora_inicio' => Carbon::parse($mov['fechaingreso'])->format('Y-m-d H:i:s'),
                    'hora_fin' => Carbon::parse($mov['fechasalida'])->format('Y-m-d H:i:s'),
                    'situacion' => $mov['situacion'],
                ];
            });
            $situaciones = ['En curso', 'Disponibles', 'En mantenimiento', 'En limpieza'];
            $situacionactual = $situacion;
            $movreservahoy = $this->obtenerMovreservasHoyParaControlHabitaciones();

            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal = Sucursal::find($persona->sucursal_id);
            $vista = RelParametroSucursal::where('sucursal_id', $sucursal->id)
                ->whereHas('parametro', function ($query) {
                    $query->where('descripcion', 'Vista control de habitaciones');
                })
                ->value('valor');


            $caja = Caja::where('situacion', 'A')->where('sucursal_id', $sucursal->id)->first();
            $cajaId = $caja->id ?? null;
            // Obtener el último turno activo o cerrado para la caja
            $turnoCaja = RelTurnocaja::where('situacion', 'A') // Turno activo
                ->whereHas('movcajainicio', function ($query) use ($cajaId) {
                    $query->where('caja_id', $cajaId);
                })
                ->latest('fechainicio') // Último turno
                ->first();
            $cajaAbierta = (bool) $turnoCaja;

            if (!$turnoCaja) {
                return response()->json([
                    'success' => false,
                    'errors' => 'No hay ninguna caja aperturada.',
                ], 404);
            }

            // Obtener movimientos con detalles
            $movimientos = Movcaja::with(['detMovcaja.Mediopago', 'conceptopago'])
                ->where('id', '>=', $turnoCaja->movcajainicio_id) // Desde el inicio del turno
                ->where('caja_id', $cajaId)
                ->whereHas('movimiento', function ($query) {
                    $query->where('situacion', 'A'); // Filtrar movimientos con situación 'A'
                })
                ->orderBy('created_at', 'asc')
                ->get();
            $modalidadtarifario = RelParametroSucursal::where('sucursal_id', $sucursal->id)
                ->whereHas('parametro', function ($query) {
                    $query->where('descripcion', 'Modalidad tarifario');
                })
                ->value('valor'); // esto devuelve directamente un string como 'Si' o 'No'


            $totales = [


                'ventas' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->movimiento->tipodocumento->tipomovimiento_id == 2 && $movcaja->conceptopago_id == 4;
                })->flatMap->detMovcaja->where('tipo', 'Pagado')->sum('monto'),



            ];
            DB::commit();
            if ($vista === 'Normal') {
                return view('movhabitacion.index', compact(
                    'Habitaciones',
                    'operaciones',
                    'Pisos',
                    'Categorias',
                    'habitacionesOcupadasGeneral',
                    'habitacionesDisponiblesGeneral',
                    'habitacionesMantenimientoGeneral',
                    'habitacionesLimpiezaGeneral',
                    'horasPorHabitacion',
                    'movimientosDetalle',
                    'clientesPorHabitacion',
                    'modalidadtarifario',
                    'movreservahoy',
                    'movHabitacionesOcupadas',
                    'totales',
                    'cajaAbierta'
                ));
            } else {
                return view('movhabitacion.indexv2', compact(
                    'Habitaciones',
                    'operaciones',
                    'Pisos',
                    'Categorias',
                    'habitacionesOcupadasGeneral',
                    'habitacionesDisponiblesGeneral',
                    'habitacionesMantenimientoGeneral',
                    'habitacionesLimpiezaGeneral',
                    'horasPorHabitacion',
                    'movimientosDetalle',
                    'clientesPorHabitacion',
                    'modalidadtarifario',
                    'movreservahoy',
                    'movHabitacionesOcupadas',
                    'totales',
                    'cajaAbierta'
                ));
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function reservar($id)
    {
        $habitacion = Habitacion::findOrFail($id); // Obtener la habitación
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);

        // Cargar la lista de personas
        $personas = Persona::where('sucursal_id', $sucursal->id)->get();
        return view('movhabitacion.reservar', compact('habitacion', 'sucursal'));
    }

    private function obtenerReservasCalendario(
        int $sucursalId,
        string $finalizadas,
        array $coloresEstado,
        bool $historico = false
    ): array {
        $inicioMesActual = now()->startOfMonth();
        $fechaLimiteFinalizadas = now()->subMonths(3)->startOfDay();

        $reservasBase = Movreserva::query()
            ->select([
                'id', 'observacion', 'nropersonas', 'fechavencimiento', 'fechainicio',
                'fechasalida', 'dias', 'total', 'estado', 'movimiento_id', 'sucursal_id', 'origen_id',
            ])
            ->where('sucursal_id', $sucursalId)
            ->whereHas('movimiento', fn ($query) => $query->where('situacion', 'A'))
            ->when(
                $historico,
                fn ($query) => $query->where(function ($fechas) use ($inicioMesActual) {
                    $fechas->where('fechasalida', '<', $inicioMesActual)
                        ->orWhere(function ($sinSalida) use ($inicioMesActual) {
                            $sinSalida->whereNull('fechasalida')
                                ->where('fechavencimiento', '<', $inicioMesActual);
                        });
                }),
                fn ($query) => $query->where(function ($fechas) use ($inicioMesActual) {
                    $fechas->where('fechasalida', '>=', $inicioMesActual)
                        ->orWhere(function ($sinSalida) use ($inicioMesActual) {
                            $sinSalida->whereNull('fechasalida')
                                ->where('fechavencimiento', '>=', $inicioMesActual);
                        });
                })
            )
            ->where(function ($query) use ($fechaLimiteFinalizadas) {
                $query->where('estado', '!=', 'FINALIZADO')
                    ->orWhere(function ($finalizadasQuery) use ($fechaLimiteFinalizadas) {
                        $finalizadasQuery->where('estado', 'FINALIZADO')
                            ->where('fechasalida', '>=', $fechaLimiteFinalizadas);
                    });
            })
            ->with([
                'origen:id,descripcion,logo',
                'movimiento:id,persona_id',
                'movimiento.Persona:id,nombres,apellidos,personamaestro_id',
                'movimiento.Persona.personamaestro:id,nrodoc',
                'movreserva_habitacion' => fn ($query) => $query
                    ->select([
                        'id', 'movreserva_id', 'habitacion_id', 'situacion',
                        'early_checkin', 'late_checkout', 'fechacheckout', 'fechacheckin',
                    ])
                    ->with('habitacion:id,numero,situacion'),
            ])
            ->get();

        $origenes = $reservasBase
            ->pluck('origen')
            ->filter()
            ->unique('id')
            ->mapWithKeys(fn ($origen) => [(string) $origen->id => $origen->logo])
            ->all();

        $ultimaReservaPorHabitacion = collect();
        if ($finalizadas !== 'Si') {
            $habitacionIds = $reservasBase
                ->flatMap->movreserva_habitacion
                ->where('situacion', 'FINALIZADO')
                ->pluck('habitacion_id')
                ->filter()
                ->unique();

            if ($habitacionIds->isNotEmpty()) {
                $ultimaReservaPorHabitacion = MovreservaHabitacion::select(
                    'habitacion_id',
                    DB::raw('MAX(fechacheckin) as max_fecha'),
                    DB::raw('MAX(movreserva_id) as movreserva_id')
                )
                    ->whereIn('habitacion_id', $habitacionIds)
                    ->whereHas('movreserva', function ($query) use ($sucursalId) {
                        $query->where('sucursal_id', $sucursalId)
                            ->whereHas('movimiento', fn ($movimiento) => $movimiento->where('situacion', 'A'));
                    })
                    ->groupBy('habitacion_id')
                    ->get()
                    ->keyBy('habitacion_id');
            }
        }

        $reservas = $reservasBase->flatMap(function ($reserva) use ($finalizadas, $coloresEstado, $ultimaReservaPorHabitacion) {
            return $reserva->movreserva_habitacion
                ->filter(function ($detalle) use ($finalizadas, $ultimaReservaPorHabitacion) {
                    if ($finalizadas === 'Si' || $detalle->situacion !== 'FINALIZADO') {
                        return true;
                    }

                    $habitacion = $detalle->habitacion;
                    if (!$habitacion) {
                        return false;
                    }

                    // Una finalización rápida no cambia la situación de la habitación.
                    // Debe permanecer visible con el color configurado de FINALIZADO.
                    if ($habitacion->situacion !== 'Limpieza') {
                        return true;
                    }

                    $ultima = $ultimaReservaPorHabitacion->get($habitacion->id);

                    return $ultima && (int) $ultima->movreserva_id === (int) $detalle->movreserva_id;
                })
                ->map(function ($detalle) use ($reserva, $coloresEstado, $finalizadas) {
                    $habitacion = $detalle->habitacion;
                    $fechaSalidaReal = $detalle->fechacheckout
                        ?: $reserva->fechasalida
                        ?: null;

                    return [
                        'id' => $reserva->id,
                        'tipo' => 'reserva',
                        'observacion' => $reserva->observacion,
                        'origen_id' => $reserva->origen_id,
                        'origennombre' => $reserva->origen->descripcion ?? '',
                        'nropersonas' => $reserva->nropersonas,
                        'fechavencimiento' => $reserva->fechavencimiento,
                        'fechainicio' => $reserva->fechainicio,
                        'fechasalida' => $reserva->fechasalida,
                        'fechacheckout' => $detalle->fechacheckout,
                        'fecha_salida_real' => $fechaSalidaReal,
                        'dias' => $reserva->dias,
                        'total' => $reserva->total,
                        'early_checkin' => $detalle->early_checkin,
                        'late_checkout' => $detalle->late_checkout,
                        'habitacionprincipal_id' => $detalle->habitacion_id,
                        'habitacion_principal' => $habitacion ? trim($habitacion->numero) : '',
                        'estado' => $detalle->situacion,
                        'cliente' => $reserva->movimiento?->Persona
                            ? trim($reserva->movimiento->Persona->nombres . ' ' . $reserva->movimiento->Persona->apellidos)
                            : '',
                        'nrodoc' => $reserva->movimiento?->Persona?->personamaestro?->nrodoc ?? '',
                        'color' => ($finalizadas !== 'Si' && $habitacion?->situacion === 'Limpieza')
                            ? ($coloresEstado['LIMPIEZA'] ?? '#ffc107')
                            : ($coloresEstado[$detalle->situacion] ?? '#A3A1FB'),
                    ];
                });
        })->values();

        return compact('reservas', 'origenes');
    }

    public function calendario(Request $request)
{
    // =========================
    // DATOS BASE (UNA SOLA VEZ)
    // =========================
    $user = Auth::user();
    $persona = Persona::select('id', 'sucursal_id')->findOrFail($user->persona_id);
    $sucursal = Sucursal::findOrFail($persona->sucursal_id);

    $finalizadas = RelParametroSucursal::where('sucursal_id', $sucursal->id)
        ->whereHas('parametro', fn ($query) => $query->where('descripcion', 'Visualizar Finalizado'))
        ->value('valor');

    $coloresEstado = DB::table('situacion')
        ->where('sucursal_id', $sucursal->id)
        ->whereIn('descripcion', ['PENDIENTE', 'EN ATENCIÓN', 'FINALIZADO', 'LIMPIEZA'])
        ->pluck('color', 'descripcion')
        ->toArray();

    if ($request->query('scope') === 'history') {
        return response()->json(
            $this->obtenerReservasCalendario($sucursal->id, (string) $finalizadas, $coloresEstado, true)
        );
    }

    // =========================
    // HABITACIONES
    // =========================
    $habitaciones = Habitacion::select('id', 'numero', 'situacion', 'tipohabitacion_id')
        ->with('tipohabitacion:id,nombre')
        ->where('sucursal_id', $sucursal->id)
        ->orderBy('numero')
        ->get();

    // =========================
    // OPERACIONES
    // =========================
    $operaciones = Operacione::where('tabla_id', 77)
        ->where('estado', true)
        ->get();

    $contrasenaAnulacion = RelParametroSucursal::where('sucursal_id', $sucursal->id)
        ->whereHas('parametro', fn ($query) => $query
            ->where('descripcion', 'Contraseña para anulación de alojamiento'))
        ->value('valor');

    $habitacionesData = $habitaciones->map(fn ($habitacion) => [
        'id' => $habitacion->id,
        'numero' => $habitacion->numero,
        'situacion' => $habitacion->situacion,
    ])->values();

    // =========================
    // OCUPACIONES DIRECTAS
    // =========================
    $movhabiocupadas = Movhabitacion::with([
            'habitacion:id,numero',
            'movimiento.Persona:id,nombres,apellidos,personamaestro_id',
            'movimiento.Persona.personamaestro:id,nrodoc'
        ])
        ->whereNotIn('situacion', ['C', 'D'])
        ->where('sucursal_id', $sucursal->id)
        ->whereNull('movreserva_id')
        ->get();

    // =========================
    // PARÁMETRO FINALIZADAS
    // =========================

    // =========================
    // COLORES DE ESTADO
    // =========================

    // =========================
    // LÍMITE DE HISTORIAL (3 MESES)
    // =========================
    ['reservas' => $reservas, 'origenes' => $origenes] = $this->obtenerReservasCalendario(
        $sucursal->id,
        (string) $finalizadas,
        $coloresEstado
    );

    // =========================
    // RESERVAS (QUERY OPTIMIZADA)
    // =========================

    // =========================
    // ÚLTIMA RESERVA POR HABITACIÓN (1 QUERY)
    // =========================

    // =========================
    // FORMATEO FINAL DE RESERVAS
    // =========================
    // =========================
    // OCUPACIONES AL MISMO FORMATO
    // =========================
    $ocupaciones = $movhabiocupadas->map(function ($ocupacion) {
        return [
            'id' => $ocupacion->id,
            'tipo' => 'movhabitacion',
            'observacion' => $ocupacion->observacion ?? '',
            'nropersonas' => $ocupacion->nropersonas ?? 1,
            'fechavencimiento' => $ocupacion->fechaingreso,
            'fechainicio' => $ocupacion->fechaingreso,
            'fechasalida' => $ocupacion->fechasalida,
            'dias' => (int) $ocupacion->dias,
            'total' => $ocupacion->total ?? 0,
            'habitacionprincipal_id' => $ocupacion->habitacion_id,
            'habitacion_principal' => $ocupacion->habitacion
                ? trim($ocupacion->habitacion->numero)
                : '',
            'estado' => 'EN ATENCIÓN',
            'cliente' => $ocupacion->movimiento && $ocupacion->movimiento->Persona
                ? trim($ocupacion->movimiento->Persona->nombres . ' ' . $ocupacion->movimiento->Persona->apellidos)
                : '',
            'nrodoc' => $ocupacion->movimiento?->Persona?->personamaestro?->nrodoc ?? '',
            'color' => '#A3A1FB'
        ];
    });

    // =========================
    // UNIR TODO
    // =========================
    $reservas = $reservas->merge($ocupaciones);

    return view(
        'movreserva.calendario',
        compact(
            'sucursal',
            'habitaciones',
            'reservas',
            'operaciones',
            'finalizadas',
            'coloresEstado',
            'origenes',
            'habitacionesData',
            'contrasenaAnulacion'
        )
    );
}
    public function anular($id)
    {
        DB::beginTransaction();

        try {
            $movhabitacion = Movhabitacion::whereHas('movhabitacion_habitacion', function ($query) use ($id) {
                $query->where('habitacion_id', $id);
            })
                ->whereIn('situacion', ['A', 'P'])
                ->firstOrFail();

            $detalleHabitacion = MovhabitacionHabitacion::where('habitacion_id', $id)
                ->where('movhabitacion_id', $movhabitacion->id)
                ->where('situacion', '!=', 'D')
                ->orderByDesc('id')
                ->firstOrFail();

            // Marcar la habitacion anulada y retirarla del grupo activo.
            $detalleHabitacion->situacion = 'D';
            $detalleHabitacion->save();
            $detalleHabitacion->delete();

            $detallesActivos = MovhabitacionHabitacion::where('movhabitacion_id', $movhabitacion->id)
                ->where('situacion', '!=', 'D')
                ->orderByDesc('principal')
                ->orderBy('id')
                ->get();

            if ($detallesActivos->isEmpty()) {
                $movhabitacion->situacion = 'D';
                $movhabitacion->modo = 'U';
                $movhabitacion->save();

                // Anular tambien el movimiento economico cuando no quedan habitaciones activas.
                Movimiento::where('id', $movhabitacion->movimiento_id)
                    ->update(['situacion' => 'D']);
            } else {
                $nuevoPrincipal = $detallesActivos->firstWhere('principal', true) ?? $detallesActivos->first();

                MovhabitacionHabitacion::where('movhabitacion_id', $movhabitacion->id)
                    ->where('situacion', '!=', 'D')
                    ->update(['principal' => false]);

                if ($nuevoPrincipal) {
                    MovhabitacionHabitacion::where('id', $nuevoPrincipal->id)
                        ->update(['principal' => true]);

                    $movhabitacion->habitacion_id = $nuevoPrincipal->habitacion_id;
                }

                $movhabitacion->modo = $detallesActivos->count() > 1 ? 'G' : 'U';
                $movhabitacion->save();

                // Reasignar huespedes que estaban en la habitacion anulada a la nueva principal.
                if (!empty($movhabitacion->habitacion_id)) {
                    Huesped::where('movhabitacion_id', $movhabitacion->id)
                        ->where('habitacion_id', $id)
                        ->update(['habitacion_id' => $movhabitacion->habitacion_id]);
                }
            }

            HabitacionAvailability::markAvailableIfNoActiveStay((int) $id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Alojamiento anulado correctamente.',
                'redirect_url' => route('movhabitacion.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->__toString(),
            ], 500);
        }
    }

    public function detalleConsumoPdf($id)
    {
        $sucursalId = Auth::user()->persona->sucursal_id;

        $movhabitacion = Movhabitacion::with([
            'sucursal.ubigeo.ubigeo.ubigeo',
            'movimiento.persona.personamaestro',
            'movreserva.movimiento',
            'huespeds.persona.personamaestro',
            'movhabitacion_habitacion.habitacion.tipohabitacion',
            'movventas.movimiento',
            'movventas.detMovventas.igv',
        ])
            ->where('sucursal_id', $sucursalId)
            ->findOrFail($id);

        $sucursal = Auth::user()->persona->sucursal;
        $logoPath = public_path($sucursal->logo ?? '');
        if (empty($sucursal->logo) || !file_exists($logoPath)) {
            $logoPath = public_path('img/logo_h.png');
        }

        $principal = $movhabitacion->huespeds->first()?->persona
            ?? $movhabitacion->movimiento?->persona;

        $detalles = collect();

        foreach ($movhabitacion->movhabitacion_habitacion as $detalleHabitacion) {
            $total = (float) ($detalleHabitacion->total ?? 0);
            $early = (float) ($detalleHabitacion->early_checkin ?? 0);
            $late = (float) ($detalleHabitacion->late_checkout ?? 0);
            $totalBase = max($total - $early - $late, 0);
            $cantidad = max((int) ($detalleHabitacion->dias ?: $detalleHabitacion->horas ?: 1), 1);
            $precio = (float) ($detalleHabitacion->tarifa ?: ($cantidad > 0 ? $totalBase / $cantidad : $totalBase));
            $fechaBase = $detalleHabitacion->fechacheckin ?: $movhabitacion->fechaingreso;
            $descripcion = 'HABITACION ' . strtoupper($detalleHabitacion->habitacion?->tipohabitacion?->nombre ?? $detalleHabitacion->habitacion?->numero ?? '');

            for ($i = 0; $i < $cantidad; $i++) {
                $monto = $precio > 0 ? $precio : ($cantidad > 0 ? $totalBase / $cantidad : $totalBase);
                $detalles->push($this->crearFilaDetalleConsumo(
                    $descripcion,
                    Carbon::parse($fechaBase)->addDays($detalleHabitacion->modalidad === 'H' ? 0 : $i),
                    $monto,
                    1,
                    null
                ));
            }

            foreach ([
                'EARLY CHECK-IN' => $early,
                'LATE CHECKOUT' => $late,
            ] as $descripcionExtra => $montoExtra) {
                if ($montoExtra > 0) {
                    $detalles->push($this->crearFilaDetalleConsumo(
                        $descripcionExtra,
                        $detalleHabitacion->fechacheckout ?: $movhabitacion->fechasalida ?: $movhabitacion->fecha_salida_real,
                        $montoExtra,
                        1,
                        null
                    ));
                }
            }
        }

        $movhabitacion->movventas
            ->flatMap->detMovventas
            ->filter(fn($detalle) => $detalle->producto_id !== null)
            ->each(function ($detalle) use ($detalles) {
                $detalles->push($this->crearFilaDetalleConsumo(
                    strtoupper($detalle->descripcion ?? 'CONSUMO'),
                    $detalle->created_at,
                    (float) ($detalle->monto ?? 0),
                    (float) ($detalle->cantidad ?: 1),
                    $detalle->igv?->porcentaje_igv
                ));
            });

        $totales = [
            'subtotal' => $detalles->sum('subtotal'),
            'igv' => $detalles->sum('igv'),
            'total' => $detalles->sum('total'),
        ];

        $pdf = Pdf::loadView('movhabitacion.detalle-consumo-pdf', [
            'movhabitacion' => $movhabitacion,
            'sucursal' => $sucursal,
            'logoPath' => $logoPath,
            'principal' => $principal,
            'detalles' => $detalles,
            'totales' => $totales,
        ])
            ->setPaper('A4', 'portrait')
            ->setOption('margin-top', '4mm')
            ->setOption('margin-right', '8mm')
            ->setOption('margin-bottom', '6mm')
            ->setOption('margin-left', '8mm');

        return $pdf->stream('detalle_consumo_' . $movhabitacion->movimiento?->numero . '.pdf');
    }

    private function crearFilaDetalleConsumo($descripcion, $fecha, float $monto, float $cantidad, ?float $porcentajeIgv): array
    {
        $total = $monto;
        $porcentajeIgv = $porcentajeIgv ?? 10.5;
        $subtotal = $porcentajeIgv > 0 ? $total / (1 + ($porcentajeIgv / 100)) : $total;

        return [
            'descripcion' => $descripcion,
            'fecha' => $fecha ? Carbon::parse($fecha) : null,
            'precio' => $cantidad > 0 ? $total / $cantidad : $total,
            'cantidad' => $cantidad,
            'subtotal' => $subtotal,
            'igv' => $total - $subtotal,
            'total' => $total,
        ];
    }
    public function exportarPDF(Request $request)
    {
        set_time_limit(600);
        Config::set('timeout', 600);
        ini_set('memory_limit', '2048M');

        $sucursal_id = Auth::user()->persona->sucursal_id;

        // Variables iguales que filter()
        $search = $request->input('search');
        $turnoRequest = $request->input('turno');
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');
        $modalidad = $request->input('modalidad');
        $situacion = $request->input('situacion');

        // Fechas por defecto
        if (!$fechaInicio || !$fechaFin) {
            $fechaInicio = Carbon::now()->startOfMonth();
            $fechaFin = Carbon::now()->endOfDay();
        } else {
            $fechaInicio = Carbon::parse($fechaInicio)->startOfDay();
            $fechaFin = Carbon::parse($fechaFin)->endOfDay();
        }

        // Query base igual que filter()
        $query = Movhabitacion::with(['habitacion', 'movimiento.persona'])
            ->select('movhabitacion.*')
            ->where('movhabitacion.sucursal_id', $sucursal_id)
            ->orderBy('movhabitacion.fechaingreso', 'desc');
        if ($turnoRequest === "0" || $turnoRequest === 0) {
            // sin filtro adicional
        } elseif (!empty($turnoRequest)) {
            $turnoValido = RelTurnocaja::where('id', $turnoRequest)
                ->where('sucursal_id', $sucursal_id)
                ->exists();
            if (!$turnoValido) {
                $query->whereRaw('1 = 0');
            } else {
                $movhabitacionIdsTurno = HistorialHabitacion::where('sucursal_id', $sucursal_id)
                    ->where('relturnocaja_id', $turnoRequest)
                    ->pluck('movhabitacion_id')
                    ->unique()
                    ->values();
                if ($movhabitacionIdsTurno->isEmpty()) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('id', $movhabitacionIdsTurno);
                }
            }
        }

        // ================================
        // 🔍 Filtro de búsqueda (igual que filter)
        // ================================
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('habitacion', function ($subQuery) use ($search) {
                    $subQuery->where('numero', 'LIKE', "%{$search}%");
                })->orWhereHas('movimiento.persona', function ($subQuery) use ($search) {
                    $subQuery->where('nombres', 'LIKE', "%{$search}%");
                });
            });
        }

        // ================================
        // 🔍 Filtro por modalidad (igual que filter)
        // ================================
        if (!empty($modalidad)) {
            $query->where('modalidad', $modalidad); // 'D' o 'H'
        }

        // ================================
        // 🔍 Filtro por situación (A trae A + P)
        // ================================
        if (!empty($situacion)) {
            if ($situacion === 'A') {
                $query->whereIn('situacion', ['A', 'P']);
            } else {
                $query->where('situacion', $situacion);
            }
        }

        // ================================
        // 🔍 Filtro por fechas (igual que filter)
        // ================================
        $query->whereBetween('fechaingreso', [$fechaInicio, $fechaFin]);

        // Obtener datos
        $movimientoHabitacion = $query->get();
        $sucursal = auth()->user()->persona->sucursal;

        $totalAlojado = $movimientoHabitacion->sum('total');

        // Crear PDF
        $pdf = Pdf::loadView('movhabitacion.reportpdfalojamientos', [
            'movimientoHabitacion' => $movimientoHabitacion,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'sucursal' => $sucursal,
            'totalAlojado' => $totalAlojado,
        ])->setPaper('A4', 'landscape');

        return $pdf->stream('checkouts_habitaciones.pdf');
    }
    public function exportarExcel(Request $request)
    {
        $sucursal_id = Auth::user()->persona->sucursal_id;

        // Variables igual que filter()
        $search = $request->input('search');
        $turnoRequest = $request->input('turno');
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');
        $modalidad = $request->input('modalidad');
        $situacion = $request->input('situacion');

        // Fechas por defecto (igual que filter)
        if (!$fechaInicio || !$fechaFin) {
            $fechaInicio = Carbon::now()->startOfMonth();
            $fechaFin = Carbon::now()->endOfDay();
        } else {
            $fechaInicio = Carbon::parse($fechaInicio)->startOfDay();
            $fechaFin = Carbon::parse($fechaFin)->endOfDay();
        }

        // Query base (misma forma que filter)
        $query = Movhabitacion::with(['habitacion', 'movimiento.persona'])
            ->select('movhabitacion.*')
            ->where('movhabitacion.sucursal_id', $sucursal_id)
            ->orderBy('movhabitacion.fechaingreso', 'desc');
        if ($turnoRequest === "0" || $turnoRequest === 0) {
            // sin filtro adicional
        } elseif (!empty($turnoRequest)) {
            $turnoValido = RelTurnocaja::where('id', $turnoRequest)
                ->where('sucursal_id', $sucursal_id)
                ->exists();
            if (!$turnoValido) {
                $query->whereRaw('1 = 0');
            } else {
                $movhabitacionIdsTurno = HistorialHabitacion::where('sucursal_id', $sucursal_id)
                    ->where('relturnocaja_id', $turnoRequest)
                    ->pluck('movhabitacion_id')
                    ->unique()
                    ->values();
                if ($movhabitacionIdsTurno->isEmpty()) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('id', $movhabitacionIdsTurno);
                }
            }
        }

        // ================================
        // 🔍 Filtro de búsqueda (igual que filter)
        // ================================
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {

                // buscar habitación
                $q->whereHas('habitacion', function ($sub) use ($search) {
                    $sub->where('numero', 'LIKE', "%{$search}%");
                })

                    // buscar cliente
                    ->orWhereHas('movimiento.persona', function ($sub) use ($search) {
                        $sub->where('nombres', 'LIKE', "%{$search}%");
                    });
            });
        }

        // ================================
        // 🔍 Filtro por modalidad (igual que filter)
        // ================================
        if (!empty($modalidad)) {
            $query->where('modalidad', $modalidad); // 'D', 'H'
        }

        // ================================
        // 🔍 Filtro por situación (A = A + P)
        // ================================
        if (!empty($situacion)) {
            if ($situacion === 'A') {
                $query->whereIn('situacion', ['A', 'P']);
            } else {
                $query->where('situacion', $situacion);
            }
        }

        // ================================
        // 🔍 Filtro por fechas (igual que filter)
        // ================================
        $query->whereBetween('fechaingreso', [$fechaInicio, $fechaFin]);

        // Ejecutar
        $movimientos = $query->get();

        // Exportar
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MovhabitacionExport($movimientos, $fechaInicio, $fechaFin),
            'alojamientos.xlsx'
        );
    }
    public function generarResumenPDF(Request $request)
    {
        $sucursal_id = Auth::user()->persona->sucursal_id;

        $fechaInicioInput = $request->input('fecha_inicio');
        $fechaFinInput = $request->input('fecha_fin');
        $horaInicioInput = $request->input('hora_inicio');
        $horaFinInput = $request->input('hora_fin');

        $fechaInicio = $fechaInicioInput
            ? Carbon::parse($fechaInicioInput . ' ' . ($horaInicioInput ?: '00:00'))
            : Carbon::now()->startOfDay();

        $fechaFin = $fechaFinInput
            ? Carbon::parse($fechaFinInput . ' ' . ($horaFinInput ?: '23:59'))
            : Carbon::now()->endOfDay();

        if ($fechaInicio->gt($fechaFin)) {
            [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
        }

        // Obtener todas las habitaciones activas
        $habitaciones = Habitacion::where('sucursal_id', $sucursal_id)
            ->whereNull('deleted_at')
            ->orderBy('numero')
            ->get();

        // Obtener alojamientos en el rango de fechas
        $movimientos = Movhabitacion::with('habitacion')
            ->where('sucursal_id', $sucursal_id)
            ->whereNull('deleted_at')
            ->whereBetween('fechaingreso', [$fechaInicio, $fechaFin])
            ->get();

        // dd($movimientos);
        // Clasificar por habitación según hora de ingreso
        $habitacionesResumen = $habitaciones->map(function ($habitacion) use ($movimientos) {
            $mov = $movimientos->firstWhere('habitacion_id', $habitacion->id);

            $color = 'pink'; // Por defecto: no alquilado

            if ($mov) {
                // Obtener la hora de ingreso como entero (hora del día)
                $horaIngreso = \Carbon\Carbon::parse($mov->fechaingreso)->format('H:i');

                // Convertir a formato Carbon para comparar con rango
                $horaCarbon = \Carbon\Carbon::createFromFormat('H:i', $horaIngreso);

                // Definir límites de turno día y noche
                $inicioDia = \Carbon\Carbon::createFromTime(6, 0);   // 06:00
                $finDia = \Carbon\Carbon::createFromTime(18, 0);     // 18:00

                // Determinar color según la hora de ingreso
                if ($horaCarbon->between($inicioDia, $finDia)) {
                    $color = 'yellow'; // Turno día
                } else {
                    $color = 'orange'; // Turno noche
                }
            }

            return [
                'numero' => $habitacion->numero,
                'precio' => $habitacion->precio,
                'color' => $color,
            ];
        });

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::findOrFail($persona->sucursal_id);
        $pdf = Pdf::loadView('movhabitacion.resumen_pdf', [
            'habitaciones' => $habitacionesResumen,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'sucursal' => $sucursal
        ]);

        return $pdf->stream('resumen_alojamientos.pdf');
    }
    public function finalizarmovhabitacion($id)
    {
        DB::beginTransaction();

        try {
            $relacion = Movhabitacion::with('movhabitacion_habitacion')
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();
            $fechaSalida = now();

            $habitacionIds = $relacion->movhabitacion_habitacion
                ->pluck('habitacion_id')
                ->filter()
                ->unique()
                ->values();

            // Older single-room stays may not have a pivot detail row.
            if ($habitacionIds->isEmpty() && $relacion->habitacion_id) {
                $habitacionIds->push((int) $relacion->habitacion_id);
            }

            $relacion->update([
                'situacion' => 'C',
                'fecha_salida_real' => $relacion->fecha_salida_real ?? $fechaSalida,
            ]);

            MovhabitacionHabitacion::where('movhabitacion_id', $id)
                ->update([
                    'situacion' => 'C',
                    'fechacheckout' => $fechaSalida,
                ]);

            foreach ($habitacionIds as $habitacionId) {
                HabitacionAvailability::markCleaningIfNoActiveStay((int) $habitacionId);
            }

            if (!is_null($relacion->movreserva_id)) {
                $movreserva = Movreserva::find($relacion->movreserva_id);

                if ($movreserva) {
                    $movreserva->update([
                        'estado' => 'FINALIZADO',
                        'fechasalida' => $fechaSalida,
                    ]);

                    MovreservaHabitacion::where('movreserva_id', $movreserva->id)
                        ->update([
                            'situacion' => 'FINALIZADO',
                            'fechacheckout' => $fechaSalida,
                        ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Alojamiento culminado exitosamente.',
                'perfil' => $relacion,
                'redirect_url' => route('movhabitacion.alojamientos.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function reactivarmovhabitacion($id)
    {
        DB::beginTransaction();

        try {
            $relacion = Movhabitacion::with('movhabitacion_habitacion')->findOrFail($id);

            $habitacionIds = $relacion->movhabitacion_habitacion
                ->pluck('habitacion_id')
                ->filter()
                ->unique()
                ->values();

            if ($habitacionIds->isNotEmpty()) {
                Habitacion::whereIn('id', $habitacionIds)->update(['situacion' => 'Ocupada']);
            }

            $relacion->update([
                'situacion' => 'A',
                'fecha_salida_real' => null,
            ]);

            MovhabitacionHabitacion::where('movhabitacion_id', $id)
                ->update([
                    'situacion' => 'A',
                    'fechacheckout' => null,
                ]);

            if (!is_null($relacion->movreserva_id)) {
                $movreserva = Movreserva::find($relacion->movreserva_id);

                if ($movreserva) {
                    $movreserva->update([
                        'estado' => 'EN ATENCIÓN',
                        'fechasalida' => $relacion->fechasalida,
                    ]);

                    MovreservaHabitacion::where('movreserva_id', $movreserva->id)
                        ->update([
                            'situacion' => 'ATENDIDO',
                            'fechacheckout' => null,
                        ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Alojamiento reactivado exitosamente.',
                'perfil' => $relacion,
                'redirect_url' => route('checkouts.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }



    public function finalizarmovreserva($id)
    {
        DB::beginTransaction();

        try {
            // Encontramos el Movhabitacion
            $relacion = Movreserva::findOrFail($id);
            $fechaSalidaProgramada = Carbon::parse($relacion->fechavencimiento)
                ->addDays(max(0, (int) $relacion->dias));

            // Actualizamos la situacion de Movhabitacion
            $relacion->update([
                'estado' => 'FINALIZADO',
                'fechasalida' => $relacion->fechasalida ?? $fechaSalidaProgramada,
            ]);

            // Actualizamos los registros relacionados en MovhabitacionHabitacion
            MovreservaHabitacion::where('movreserva_id', $id)
                ->update(['situacion' => 'FINALIZADO']);
            MovreservaHabitacion::where('movreserva_id', $id)
                ->whereNull('fechacheckout')
                ->update(['fechacheckout' => $fechaSalidaProgramada]);

            // Finalización administrativa: no modificar el estado de la habitación.

            DB::commit();

            // Retornamos una respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Reserva finalizada exitosamente.',
                'perfil' => $relacion,
                'redirect_url' => route('calendarioreservas.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
    public function historial($id)
    {
        try {
            // Obtén el Movhabitacion por su ID
            $movhabitacion = Movhabitacion::find($id);

            // Verifica si el Movhabitacion existe
            if (!$movhabitacion) {
                return response()->json(['message' => 'Movhabitacion no encontrado'], 404);
            }


            $movcajaIds = DB::table('movventa as mv')
                ->join('movimiento as mventa', 'mv.movimiento_id', '=', 'mventa.id')          // movimiento de venta
                ->join('movimiento as mcaja', 'mcaja.movimiento_id', '=', 'mventa.id')        // movimiento de caja que apunta al movimiento de venta
                ->join('movcaja as mc', 'mc.movimiento_id', '=', 'mcaja.id')                  // movcaja enlazado al movimiento de caja
                ->where('mv.movhabitacion_id', $movhabitacion->id)                            // solo las ventas de esa habitación
                ->whereNull('mv.deleted_at')                                                  // buenas prácticas
                ->whereNull('mc.deleted_at')
                ->where('mventa.situacion', '!=', 'D')                                        // no anulados
                ->where('mcaja.situacion', '!=', 'D')
                ->pluck('mc.id');

            // Obtener los movimientos de caja y sus detalles
            $movcajas = Movcaja::with([
                'detMovcaja.Mediopago',
                'movimiento.tipomovimiento',
                'conceptopago',
                'caja',
                'turno',
                'movimiento.responsable.personamaestro',
                'movimiento.persona.personamaestro'
            ])->whereIn('id', $movcajaIds)->get();

            // Agrupar los detalles si es necesario para la vista
            $detallesAgrupados = $movcajas->flatMap->detMovcaja->groupBy('Mediopago.descripcion')->map(function ($items) {
                return [
                    'mediopago' => $items->first()->Mediopago->descripcion,
                    'monto' => $items->sum('monto'),
                ];
            });

            // Pasar también la nota y mediopago a la vista
            return view('movhabitacion.historial-ventas', compact('movcajas', 'detallesAgrupados'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
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

    private function resolverTipoPago(array $detallesMovCaja): string
    {
        foreach ($detallesMovCaja as $detalle) {
            if (strcasecmp((string) ($detalle['tipo'] ?? ''), 'Deuda') === 0) {
                return 'CREDITO';
            }
        }

        return 'CONTADO';
    }
}
