<?php

namespace App\Http\Controllers;

use App\Models\Banco;
use App\Models\Billeteradigital;
use App\Models\Caja;
use App\Models\Categorium;
use App\Models\DetMovcaja;
use App\Models\Movventum;
use App\Models\DetMovventum;
use App\Models\Facturador;
use App\Models\Kardex;
use App\Models\Mediopago;
use App\Models\Movcaja;
use App\Models\Movhabitacion;
use App\Models\Movimiento;
use App\Models\Igv;

use App\Models\Operacione;
use App\Models\Sucursal;
use App\Models\Tipodocumento;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\RelTurnocaja;
use App\Models\Solicitud;
use App\Models\Stockproducto;
use App\Models\Tarjetum;
use App\Models\Turno;
use App\Models\Unidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\FacturadorController;
use App\Helpers\NumeroTexto;
use App\Services\CorrelativoService;
use App\Services\InventarioVentaService;
use App\Models\MovhabitacionHabitacion;
use App\Models\Pasarelapago;
use App\Models\RelOperacionsucursal;
use App\Models\RelParametrosucursal;
use App\Models\RelProductosucursal;
use Barryvdh\Snappy\Facades\SnappyPdf as Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VentasExport;
use App\Models\Anulacion;
use App\Models\DetMovhabitacion;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Movreserva;
use App\Models\MovreservaHabitacion;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Client;

class MovventumController extends Controller
{
    private function obtenerDetallesVentaDesdeJson(Request $request): array
    {
        $detalles = json_decode((string) $request->input('detalles_json', '[]'), true);
        return is_array($detalles) ? $detalles : [];
    }

    private function validarDescripcionObligatoriaParaGlosa(Request $request)
    {
        if (strtoupper((string) $request->input('tipo_detalle')) !== 'GLOSA') {
            return null;
        }

        $detalles = $this->obtenerDetallesVentaDesdeJson($request);
        if (empty($detalles)) {
            return response()->json([
                'success' => false,
                'errors' => 'Para tipo de detalle GLOSA debe ingresar al menos una descripcion.',
            ], 422);
        }

        foreach ($detalles as $detalle) {
            if (trim((string) ($detalle['descripcion'] ?? '')) === '') {
                return response()->json([
                    'success' => false,
                    'errors' => 'En GLOSA la descripcion no puede estar vacia. SUNAT rechazara el comprobante.',
                ], 422);
            }
        }

        return null;
    }


    public function index(Request $request)
    {
        // Usuario logueado y sucursal
        $user = Auth::user();
        $persona = Persona::findOrFail($user->persona_id);
        $sucursal = Sucursal::findOrFail($persona->sucursal_id);

        // Cajas disponibles
        $cajas = Caja::where('sucursal_id', $sucursal->id)
            ->orderBy('situacion', 'ASC')
            ->get();

        if ($cajas->isEmpty()) {
            return back()->withErrors(['error' => 'No hay cajas registradas para esta sucursal.']);
        }

        // Caja activa (primera disponible)
        $caja_id = $cajas->first()->id;

        // Operaciones disponibles en esta sucursal
        $operaciones = Operacione::where('operaciones.tabla_id', 64)
            ->where('operaciones.estado', true)
            ->join('rel_operacionsucursal as ros', 'operaciones.id', '=', 'ros.operacion_id')
            ->where('ros.sucursal_id', $sucursal->id)
            ->orderBy('ros.orden', 'asc')
            ->select('operaciones.*')
            ->get();

        // Última apertura de caja
        $ultimoAperturaId = Movcaja::where('sucursal_id', $sucursal->id)
            ->where('caja_id', $caja_id)
            ->whereHas('conceptopago', fn($q) => $q->where('descripcion', 'Apertura de caja'))
            ->orderByDesc('id')
            ->value('id');

        // Movimientos de caja posteriores a la apertura
        // 1️⃣ Obtener los Movcaja
        $movcajas = Movcaja::where('sucursal_id', $sucursal->id)
            ->where('caja_id', $caja_id)
            ->when($ultimoAperturaId, fn($q) => $q->where('id', '>=', $ultimoAperturaId))
            ->with(['movimiento.tipomovimiento', 'detMovcaja.mediopago', 'conceptopago'])
            ->orderByDesc('id')
            ->get();

        // 2️⃣ IDs de los movimientos de caja (los "hijos")
        $movimientoCajaIds = $movcajas->pluck('movimiento_id')->filter()->unique()->toArray();

        // 3️⃣ Obtener los movimientos de venta asociados (los "padres" de los movimientos de caja)
        $movimientoVentaIds = \App\Models\Movimiento::whereIn('id', $movimientoCajaIds)
            ->whereNotNull('movimiento_id')
            ->pluck('movimiento_id')
            ->unique()
            ->toArray();

        // 4️⃣ Combinar todos los movimientos relacionados (por seguridad)
        $movimientosRelacionados = array_unique(array_merge($movimientoCajaIds, $movimientoVentaIds));

        // 5️⃣ Obtener las ventas relacionadas con esos movimientos
        $movventas = \App\Models\Movventum::whereIn('movimiento_id', $movimientosRelacionados)
            ->with(['detMovventas', 'sucursal', 'movimiento'])
            ->orderByDesc('id')
            ->paginate(10);





        // Función para calcular importes según documento/movimiento
        $calc = function ($movventa, $campo) {
            $doc = $movventa->movimiento->tipodocumento_id;
            $mov = $movventa->movimiento->tipomovimiento_id;

            if ($doc === 7)
                return $movventa->$campo;
            if ($doc === 8)
                return -$movventa->$campo;
            if ($mov === 2)
                return $movventa->$campo;
            return 0;
        };

        // Totales de la página actual
        $totalPagina = $movventas->where('movimiento.situacion', 'Activado')->sum(fn($mv) => $calc($mv, 'total'));
        $totalPaginaIgv = $movventas->where('movimiento.situacion', 'Activado')->sum(fn($mv) => $calc($mv, 'igv'));
        $totalPaginaSubtotal = $movventas->where('movimiento.situacion', 'Activado')->sum(fn($mv) => $calc($mv, 'subtotal'));

        return view('movventa.index', compact(
            'movventas',
            'operaciones',
            'totalPagina',
            'totalPaginaIgv',
            'totalPaginaSubtotal'
        ));
    }



    public function report(Request $request)
    {
        try {
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal = Sucursal::findOrFail($persona->sucursal_id);
            $cajas = Caja::where('sucursal_id', $sucursal->id)->orderBy('situacion', 'ASC')->get();

            $operaciones = Operacione::where('operaciones.tabla_id', 64)
                ->where('operaciones.estado', true)
                ->join('rel_operacionsucursal as ros', 'operaciones.id', '=', 'ros.operacion_id')
                ->join('sucursal as s', 'ros.sucursal_id', '=', 's.id')
                ->where('s.situacion', 'A')
                ->where('s.id', $sucursal->id)
                ->select('operaciones.*')
                ->orderBy('ros.orden', 'asc')
                ->get();

            // Obtener el ID de la primera caja
            $caja_id = $cajas->first()->id;

            // Obtener el ID del último movimiento de apertura
            $ultimoAperturaId = Movcaja::where('movcaja.sucursal_id', $sucursal->id)
                ->where('movcaja.caja_id', $caja_id)
                ->whereHas('conceptopago', function ($query) {
                    $query->where('descripcion', 'Apertura de caja');
                })
                ->orderBy('id', 'desc')
                ->value('id');
            // Obtener los movcajas
            // 1️⃣ Obtener los Movcaja
            $movcajas = Movcaja::where('sucursal_id', $sucursal->id)
                ->where('caja_id', $caja_id)
                ->when($ultimoAperturaId, fn($q) => $q->where('id', '>=', $ultimoAperturaId))
                ->with(['movimiento.tipomovimiento', 'detMovcaja.mediopago', 'conceptopago'])
                ->orderByDesc('id')
                ->get();

            // 2️⃣ IDs de los movimientos de caja (los "hijos")
            $movimientoCajaIds = $movcajas->pluck('movimiento_id')->filter()->unique()->toArray();

            // 3️⃣ Obtener los movimientos de venta asociados (los "padres" de los movimientos de caja)
            $movimientoVentaIds = Movimiento::whereIn('id', $movimientoCajaIds)
                ->whereNotNull('movimiento_id')
                ->pluck('movimiento_id')
                ->unique()
                ->toArray();

            // 4️⃣ Combinar todos los movimientos relacionados (por seguridad)
            $movimientosRelacionados = array_unique(array_merge($movimientoCajaIds, $movimientoVentaIds));

            // 5️⃣ Obtener las ventas relacionadas con esos movimientos
            $movventas = Movventum::whereIn('movimiento_id', $movimientosRelacionados)
                ->with(['detMovventas', 'sucursal', 'movimiento'])
                ->orderByDesc('id')
                ->paginate(10);

            $tipodocumentos = Tipodocumento::where('tipomovimiento_id', 2)->get();

            $relTurnocajas = RelTurnocaja::where('sucursal_id', $persona->sucursal_id)
                ->orderBy('fechainicio', 'desc')
                ->paginate(10);

            $totalPagina = $movventas->filter(function ($movventa) {
                return $movventa->movimiento->situacion === 'Activado';
            })->map(function ($movventa) {
                $tipoDocumentoId = $movventa->movimiento->tipodocumento_id;
                $tipoMovimientoId = $movventa->movimiento->tipomovimiento_id;

                if ($tipoDocumentoId === 7) {
                    return $movventa->total;
                } elseif ($tipoDocumentoId === 8) {
                    return -$movventa->total;
                } elseif ($tipoMovimientoId === 2) {
                    return $movventa->total;
                }

                return 0;
            })->sum();
            $totalPaginaIgv = $movventas->filter(function ($movventa) {
                return $movventa->movimiento->situacion === 'Activado';
            })->map(function ($movventa) {
                $tipoDocumentoId = $movventa->movimiento->tipodocumento_id;
                $tipoMovimientoId = $movventa->movimiento->tipomovimiento_id;

                if ($tipoDocumentoId === 7) {
                    return $movventa->igv;
                } elseif ($tipoDocumentoId === 8) {
                    return -$movventa->igv;
                } elseif ($tipoMovimientoId === 2) {
                    return $movventa->igv;
                }

                return 0;
            })->sum();

            $totalPaginaSubtotal = $movventas->filter(function ($movventa) {
                return $movventa->movimiento->situacion === 'Activado';
            })->map(function ($movventa) {
                $tipoDocumentoId = $movventa->movimiento->tipodocumento_id;
                $tipoMovimientoId = $movventa->movimiento->tipomovimiento_id;

                if ($tipoDocumentoId === 7) {
                    return $movventa->subtotal;
                } elseif ($tipoDocumentoId === 8) {
                    return -$movventa->subtotal;
                } elseif ($tipoMovimientoId === 2) {
                    return $movventa->subtotal;
                }

                return 0;
            })->sum();

            return view('movventa.report', compact('movventas', 'operaciones', 'cajas', 'relTurnocajas', 'tipodocumentos', 'totalPagina', 'totalPaginaIgv', 'totalPaginaSubtotal'));
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
            $turnofilter = RelTurnocaja::find($request->input('turno'));
            $tipoFiltro = $request->input('tipo', 0);
            $fecha_inicio = $request->input('fechaInicio');
            $fecha_fin = $request->input('fechaFin');



            $cajas = Caja::where('sucursal_id', $sucursal_id)->orderBy('situacion', 'ASC')->get();

            $operaciones = Operacione::where('operaciones.tabla_id', 64)
                ->where('operaciones.estado', true)
                ->join('rel_operacionsucursal as ros', 'operaciones.id', '=', 'ros.operacion_id')
                ->join('sucursal as s', 'ros.sucursal_id', '=', 's.id')
                ->where('s.situacion', 'A')
                ->where('s.id', $sucursal_id)
                ->select('operaciones.*')
                ->orderBy('ros.orden', 'asc')
                ->get();

            $movimientoQuery = Movcaja::with(['detMovcaja.Mediopago', 'conceptopago'])
                ->where('sucursal_id', $sucursal_id)

                ->whereHas('conceptopago', function ($query) {
                    $query->where('descripcion', '!=', 'Cierre de caja');
                });

            if ($turnofilter) {

                $movimientoQuery->where('id', '>=', $turnofilter->movcajainicio_id);
                //dd($turnofilter->movcajainicio_id);
                if ($turnofilter->movcajafin_id) {
                    $movimientoQuery->where('id', '<=', $turnofilter->movcajafin_id);
                }
            }

            if ($tipoFiltro != 0) {
                $movimientoQuery->whereHas('movventa.movimiento', function ($query) use ($tipoFiltro) {
                    $query->where('tipodocumento_id', $tipoFiltro);
                });
            }

            $movimientos = $movimientoQuery->orderBy('created_at', 'asc')->get();

            $movimientoCajaIds = $movimientos->pluck('movimiento_id')->toArray();


            // Obtener los movimientos de venta asociados (los "padres" de los movimientos de caja)
            $movimientoVentaIds = Movimiento::whereIn('id', $movimientoCajaIds)
                ->whereNotNull('movimiento_id')
                ->pluck('movimiento_id')
                ->unique()
                ->toArray();
            // Combinar todos los movimientos relacionados (por seguridad)
            $movimientosRelacionados = array_unique(array_merge($movimientoCajaIds, $movimientoVentaIds));

            $perPage = $request->input('perPage', 10);

            $movventaQuery = Movventum::whereIn('movimiento_id', $movimientosRelacionados)
                ->with(['detMovventas', 'sucursal', 'movimiento'])
                ->orderBy('id', 'desc');

            $search = trim($request->input('search', ''));
            if ($search !== '') {
                $like = '%' . strtolower($search) . '%';
                $movventaQuery->whereHas('movimiento.persona.personamaestro', function ($q) use ($like) {
                    $q->whereRaw('LOWER(COALESCE(personamaestro.nrodoc, \'\')) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(COALESCE(personamaestro.nombres, \'\')) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(COALESCE(personamaestro.apellidos, \'\')) LIKE ?', [$like]);
                });
            }

            if ($fecha_inicio && $fecha_fin) {
                $movventaQuery->whereHas('movimiento', function ($query) use ($fecha_inicio, $fecha_fin) {
                    $query->whereRaw("DATE(fecha) BETWEEN ? AND ?", [$fecha_inicio, $fecha_fin]);
                });
            }

            $movventas = $movventaQuery->paginate($perPage);


            $relTurnocajas = RelTurnocaja::where('sucursal_id', $sucursal_id)
                ->orderBy('fechainicio', 'desc')
                ->paginate($perPage);

            // **Cálculo de totales**
            $totalPagina = $movventas->where('movimiento.situacion', 'Activado')
                ->sum(function ($movventa) {
                    return ($movventa->movimiento->tipodocumento_id === 7) ? $movventa->total : (($movventa->movimiento->tipodocumento_id === 8) ? -$movventa->total : (($movventa->movimiento->tipomovimiento_id === 2) ? $movventa->total : 0));
                });

            $totalPaginaIgv = $movventas->where('movimiento.situacion', 'Activado')
                ->sum(function ($movventa) {
                    return ($movventa->movimiento->tipodocumento_id === 7) ? $movventa->igv : (($movventa->movimiento->tipodocumento_id === 8) ? -$movventa->igv : (($movventa->movimiento->tipomovimiento_id === 2) ? $movventa->igv : 0));
                });

            $totalPaginaSubtotal = $movventas->where('movimiento.situacion', 'Activado')
                ->sum(function ($movventa) {
                    return ($movventa->movimiento->tipodocumento_id === 7) ? $movventa->subtotal : (($movventa->movimiento->tipodocumento_id === 8) ? -$movventa->subtotal : (($movventa->movimiento->tipomovimiento_id === 2) ? $movventa->subtotal : 0));
                });

            $tipodocumentos = Tipodocumento::where('tipomovimiento_id', 2)->get();

            return view('movventa.report', compact(
                'movventas',
                'relTurnocajas',
                'cajas',
                'tipodocumentos',
                'totalPagina',
                'totalPaginaIgv',
                'totalPaginaSubtotal',
                'operaciones'
            ));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }




    public function create($personaCreadaId = null)
    {
        $personaCreada = $personaCreadaId ? Persona::find($personaCreadaId) : null;
        $tipos_documento = Tipodocumento::all();
        $sucursal_id = Auth::user()->persona->sucursal_id;
        // Obtener todos los productos disponibles para ser agregados al detalle del movimiento
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);
        $personas = Persona::where('sucursal_id', $sucursal->id)->get();

        $categorias = Categorium::whereNull('categoria_id')
            ->whereHas('relCategoriasucursal', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id)->where('tipocarta', '!=', 'NINGUNA');
            })
            ->with('categorias')
            ->get();
        $cajas = Caja::where('situacion', 'A')->where('sucursal_id', $sucursal->id)->get();
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

        // Calcular el número correlativo (basado en registros existentes en `movimiento`)
        $tipoDocumentoId = $tipoDocumentoPredeterminado->id ?? optional($tiposDocumento->first())->id;
        $correlativo = $tipoDocumentoId ? $this->generateNumero($tipoDocumentoId, false) : '00000001';
        // Formatear el número correlativo con ceros a la izquierda
        $correlativo = str_pad($correlativo, 8, "0", STR_PAD_LEFT);
        $medio_pagos = Mediopago::where('situacion', true)->get();


        // Generar el número de comprobante
        $numeroComprobante = "{$abreviatura}{$serie}-{$correlativo}";

        // Obtener los productos relacionados con la sucursal especificada
        $productos = Producto::with(['relProductosucursal', 'relListaUnidadSucursal'])
            ->whereHas('relProductosucursal', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id);
            })
            ->get();


        $vendedores = Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 1);
        })->where('sucursal_id', $sucursal->id)->get();
        $medio_pagos = Mediopago::where('situacion', true)->get();
        $pos = Pasarelapago::where('situacion', true)->get(); // Tabla para P.O.S.
        $tarjetas = Tarjetum::where('situacion', true)->get(); // Tabla para Tarjetas
        $bancos = Banco::where('situacion', true)->get(); // Tabla para Bancos
        $billeteras = Billeteradigital::where('situacion', true)->get(); // Tabla para Billeteras Digitales
        $sucursal_id = $sucursal->id;

        return view('movventa.create', compact('numeroComprobante', 'sucursal_id', 'medio_pagos', 'pos', 'tarjetas', 'bancos', 'billeteras', 'personaCreada', 'tiposDocumento', 'tipoDocumentoPredeterminado', 'tipos_documento', 'personas', 'sucursal_id', 'vendedores', 'categorias', 'cajas', 'medio_pagos'));
    }

    public function store(Request $request)
    {
        abort_unless((int) Auth::user()->persona?->sucursal_id === (int) $request->input('sucursal_id'), 403, 'La venta no corresponde a tu sucursal.');
        DB::beginTransaction();

        try {
            // Validación de datos
            $validator = Validator::make($request->all(), [
                'sucursal_id' => 'required|exists:sucursal,id',
                'tipo_detalle' => 'required|string|in:DETALLADO,GLOSA',
                'estado' => 'nullable|string',
                'moneda' => 'required|string|in:PEN,USD',
                'total' => 'required|numeric|min:0',
                'detalles_json' => 'required|json',
                'detalles_movimientos_json' => 'required|json',
                'persona_id' => 'nullable|exists:persona,id',
                'caja_id' => 'required|exists:caja,id',
                'comentario' => 'nullable|string',
                'numero_movimiento_manual' => ['nullable', 'regex:/^\d{1,8}$/'],

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $validacionGlosa = $this->validarDescripcionObligatoriaParaGlosa($request);
            if ($validacionGlosa) {
                return $validacionGlosa;
            }
            $user = Auth::user();
            $personasucursal = Persona::find($user->persona_id);
            $sucursal = Sucursal::find($personasucursal->sucursal_id);
            // Datos generales
            $usuario = Auth::user();
            $responsable = $usuario->persona;
            $persona = $request->persona_id ? Persona::find($request->persona_id) : null;
            $caja = Caja::find($request->caja_id);
            $turnoCaja = RelTurnocaja::conCajaActiva($request->caja_id)->first();
            $turnoId = $turnoCaja ? $turnoCaja->movcajainicio->turno_id : $request->turno_id;
            $tipodocumento = Tipodocumento::findOrFail($request->tipodocumento_id);

            $numeroMovimiento = $this->generateNumero($request->tipodocumento_id);
            $numeroMovimientoManual = trim((string) $request->input('numero_movimiento_manual', '0'));
            if ($numeroMovimientoManual !== '' && $numeroMovimientoManual !== '0') {
                $numeroMovimiento = str_pad((string) ((int) $numeroMovimientoManual), 8, '0', STR_PAD_LEFT);
            }

            // Crear Movimiento
            $movimiento = Movimiento::create([
                'numero' => $numeroMovimiento,
                'fecha' => now(),
                'usuario_id' => $usuario->id,
                'usuario' => $usuario->name,
                'responsable_id' => $responsable->id,
                'responsable' => "{$responsable->nombres} {$responsable->apellidos}",
                'persona_id' => $request->persona_id,
                'persona' => $persona ? "{$persona->nombres} {$persona->apellidos}" : 'N/A',
                'comentario' => $request->notas ?? '',
                'situacion' => 'A',
                'tipomovimiento_id' => Tipodocumento::find($request->tipodocumento_id)->tipomovimiento_id,
                'tipodocumento_id' => $request->tipodocumento_id,
                'sucursal_id' => $request->sucursal_id,
            ]);

            // Procesar detalles para obtener los igv_id de los productos
            $detalles = json_decode($request->detalles_json, true);
            $detallesMovCaja = json_decode($request->detalles_movimientos_json, true) ?: [];
            $tipoPago = $this->resolverTipoPago($detallesMovCaja);
            $igvPredeterminadoId = RelParametrosucursal::where('sucursal_id', $sucursal->id)
                ->where('parametro_id', 8)
                ->value('valor');
            $igvPredeterminado = $igvPredeterminadoId ? Igv::find($igvPredeterminadoId) : null;
            $igvObjeto = Igv::find($igvPredeterminadoId);
            $totalIgv = 0; // Variable para acumular el IGV total
            $totalSinIgv = 0; // Variable para acumular el subtotal (sin IGV)

            foreach ($detalles as $detalle) {
                // Obtener la relación del producto con la sucursal
                $productoId = $detalle['producto_id'] ?? null;
                $relProductoSucursal = !empty($productoId)
                    ? RelProductosucursal::where('producto_id', $productoId)
                    ->where('sucursal_id', $sucursal->id)
                    ->first()
                    : (object) ['igv' => $igvPredeterminado, 'igv_id' => $igvPredeterminado?->id];

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


            // Ahora se crea el Movventa con los valores calculados
            $movventa = Movventum::create([
                'fecha' => now(),
                'usuario_id' => $usuario->id,
                'usuario' => $usuario->name,
                'responsable_id' => $responsable->id,
                'responsable' => "{$responsable->nombres} {$responsable->apellidos}",
                'persona_id' => $request->persona_id,
                'persona' => $persona ? "{$persona->nombres} {$persona->apellidos}" : 'N/A',
                'comentario' => $request->notas ?? '',
                'serie' => $tipodocumento->abreviatura . '' . $caja->serie,
                'anio' => now()->year,
                'tipo_detalle' => $request->tipo_detalle,
                'estado' => $request->estado ?? 'N',
                'moneda' => $request->moneda,
                'tipocambio' => 3.71, // Suponiendo que esta tasa es fija
                'subtotal' => $request->total/($igvObjeto->porcentaje_igv / 100 + 1),
                'tipo_pago' => $tipoPago,
                'igv' => $request->total - $request->total/($igvObjeto->porcentaje_igv / 100 + 1),
                'total' => $request->total,
                'movimiento_id' => $movimiento->id,
                'sucursal_id' => $request->sucursal_id,
                'json_sucursal' => json_encode(Sucursal::find($request->sucursal_id)),
                'detraccion' => ($request->has('detraccion') && in_array($request->input('detraccion'), ['S', 'on', '1', true], true)) || floatval($request->input('porcentaje_detraccion', 0)) > 0 ? 'S' : 'N',
                'porcentaje_detraccion' => floatval($request->input('porcentaje_detraccion', 0)),
                'cuenta_detraccion' => $request->input('cuenta_detraccion', '') ?? '',
                'codigo_bien_detraccion' => $request->input('codigo_bien_detraccion', '') ?? '',
            ]);

            // Después se procesan los detalles de la venta
            foreach ($detalles as $detalle) {
                // Obtener la relación del producto con la sucursal
                $productoId = $detalle['producto_id'] ?? null;
                $relProductoSucursal = !empty($productoId)
                    ? RelProductosucursal::where('producto_id', $productoId)
                    ->where('sucursal_id', $sucursal->id)
                    ->first()
                    : (object) ['igv' => $igvPredeterminado, 'igv_id' => $igvPredeterminado?->id];

                // Obtener el igv_id directamente desde la relación
                $igv_id = $relProductoSucursal ? $relProductoSucursal->igv_id : null;
                $igvClase = Igv::find($igv_id);
                $unidad = Unidad::where('descripcion', $detalle['unidad'])->first();
                $detalleVenta = DetMovventum::create([
                    'movventa_id' => $movventa->id,
                    'tipo_detalle' => $request->tipo_detalle,
                    'codigo' => $detalle['codigo'],
                    'descripcion' => $detalle['descripcion'],
                    'producto_id' => $productoId,
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
                    'det_movventa_id' => null,
                    'complementos' => json_encode([]),
                    'sucursal_id' => $request->sucursal_id,
                ]);
                app(InventarioVentaService::class)->registrarSalida($detalle, $detalleVenta, $movventa, $movimiento, Auth::user());
            }

            $movimientoCaja = Movimiento::create([
                'numero' => $this->generateNumeroMovcaja($request->sucursal_id, $request->caja_id, 4),
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
                'movimiento_id' => $movimiento->id
            ]);

            // Crear Movcaja
            $movcaja = Movcaja::create([
                'conceptopago_id' => 4,
                'moneda' => $request->moneda,
                'tipocambio' => 3.71,
                'total' => $request->total,
                'caja_id' => $request->caja_id,
                'caja' => $caja->numero,
                'turno_id' => $turnoId,
                'json_turno' => json_encode(Turno::find($turnoId)),
                'movimiento_id' => $movimientoCaja->id,
                'sucursal_id' => $request->sucursal_id,
            ]);

            // Procesar Detalles de Movcaja
            foreach ($detallesMovCaja as $detalle) {
                $detalleMovcaja = DetMovcaja::create([
                    'movcaja_id' => $movcaja->id,
                    'tipo' => $detalle['tipo'] ?? null,
                    'fechavencimiento' => !empty($detalle['fechavencimiento']) ? $detalle['fechavencimiento'] : null,
                    'fechapago' => now(),

                    // Medio de pago
                    'mediopago_id' => $detalle['mediopago_id'] ?? null,
                    'mediopago' => $detalle['mediopago_id']
                        ? Mediopago::find($detalle['mediopago_id'])->descripcion ?? ''
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

                // Recorrer el JSON de detalles si no es efectivo
                foreach ($detalle['detalles'] ?? [] as $item) {
                    // Banco
                    if (!empty($item['banco'])) {
                        $bancoDescripcion = trim($item['banco']);
                        $detalleMovcaja->update([
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

                            $detalleMovcaja->update([
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
                        $detalleMovcaja->update([
                            'billeteradigital_id' => Billeteradigital::where('descripcion', $billeteraDescripcion)->value('id'),
                            'billeteradigital' => $billeteraDescripcion
                        ]);
                    }

                    // POS
                    if (!empty($item['pos'])) {
                        $posDescripcion = trim($item['pos']);
                        $detalleMovcaja->update([
                            'pos_id' => Pasarelapago::where('descripcion', $posDescripcion)->value('id'),
                            'pos' => $posDescripcion
                        ]);
                    }
                }
            }


            $facturadorController = new FacturadorController();

            if ($tipodocumento->abreviatura !== 'T') {
                $solicitudFacturacion = $facturadorController->enviarFacturacionElectronica(
                    $movventa,
                    collect($detalles),
                    collect($detalles),
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
            }
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
                return response()->json([
                    'success' => true,
                    'message' => 'Venta registrada con éxito.',
                    'redirect_url' => route('movventa.index'),
                    'pdf_url' => $pdfUrl,
                    'facturacion_response' => $solicitudFacturacion
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'pdf_url' => $pdfUrl,

                    'message' => 'Venta registrada con éxito.',
                    'redirect_url' => route('movventa.index'),
                ]);
            }
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
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
        $serie = $abreviatura . str_pad($caja->serie, 3, "0", STR_PAD_LEFT);

        if ($reservar) {
            return CorrelativoService::nextNumeroVenta($persona->sucursal_id, $tipoDocumentoId, $serie);
        }

        return CorrelativoService::peekNumeroVenta($persona->sucursal_id, $tipoDocumentoId, $serie);
    }



    public function edit($id)
    {

        // Obtener el registro Movventa
        $movventa = Movventum::with(['detMovventas.igv', 'detMovventas.unidad', 'movimiento', 'movimiento.persona'])->findOrFail($id);
        $medio_pagos = Mediopago::where('situacion', true)->get();

        // Obtener datos relacionados
        $tiposDocumento = Tipodocumento::where('tipomovimiento_id', 2)->get();
        $tipoDocumentoPredeterminado = $tiposDocumento->where('id', $movventa->movimiento->tipodocumento_id)->first();
        $sucursal_id = Auth::user()->persona->sucursal_id;
        $personas = Persona::where('sucursal_id', $sucursal_id)->get();

        // Obtener los productos relacionados con la sucursal especificada
        $productos = Producto::with(['relProductosucursal', 'relListaUnidadSucursal'])
            ->whereHas('relProductosucursal', function ($query) use ($sucursal_id) {
                $query->where('sucursal_id', $sucursal_id);
            })
            ->get();
        $categorias = Categorium::whereNull('categoria_id')
            ->whereHas('relCategoriasucursal', function ($query) use ($sucursal_id) {
                $query->where('sucursal_id', $sucursal_id)->where('tipocarta', '!=', 'NINGUNA');
            })
            ->with('categorias')
            ->get();
        $cajas = Caja::where('situacion', 'A')->where('sucursal_id', $sucursal_id)->get();
        $vendedores = Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 1);
        })->where('sucursal_id', $sucursal_id)->get();

        // Determinar la serie de la caja asociada
        $caja = $cajas->first();
        $serie = $caja ? str_pad($caja->serie, 3, "0", STR_PAD_LEFT) : '001';

        // Calcular el número correlativo basado en el movimiento actual
        $correlativo = str_pad(
            $movventa->movimiento->numero ?? $this->generateNumero($movventa->movimiento->tipodocumento_id, false),
            8,
            "0",
            STR_PAD_LEFT
        );

        // Generar el número de comprobante
        $abreviatura = $tipoDocumentoPredeterminado->abreviatura ?? 'N/A';
        $numeroComprobante = "{$abreviatura}{$serie}-{$correlativo}";

        // Formatear los datos para el autocompletado de personas
        // Ajuste dentro del map de $productosConsumidos
$productosConsumidos = DetMovventum::with(['igv', 'unidad'])
    ->whereHas('movventum', function ($query) use ($movventa) {
        $query->where('movimiento_id', $movventa->movimiento_id);
    })
    ->get()
    ->map(function ($detalle) {

        $jsonUnidad = json_decode($detalle->json_unidad, true);
       // dd($detalle->monto * $detalle->igv->porcentaje_igv / 100 );
        // Precio unitario real
        $precioUnitario = $detalle->cantidad > 0
            ? round($detalle->monto / $detalle->cantidad, 2)
            : 0;
       //  dd($detalle->monto * ($detalle->igv->porcentaje_igv / 100));

        return [
            'producto_id'     => $detalle->producto_id,
            'situacion'       => $detalle->situacion ?? 'N',

            // 👉 DESDE DET_MOVVENTA
            'descripcion'     => $detalle->descripcion,
            'codigo'          => $detalle->codigo,
            'unidad'          => $jsonUnidad['descripcion'] ?? 'Unidad(es)',
            'cantidad'        => $detalle->cantidad,

            // 👉 MONTOS REALES
            'precio_compra'   => $detalle->monto_original ?? $detalle->monto,
            'precio_unitario' => $precioUnitario,
            'igv' => round(
                $detalle->monto * ((optional($detalle->igv)->porcentaje_igv ?? 0) / 100),
                2
            ),

            'igv_id' => $detalle->igv_id,

            'total'           => $detalle->monto,

            // 👉 CATEGORÍAS / OPCIONES
            'categoria'       => $detalle->det_categoria ?? 'Sin Categoría',
            'opc_categoria'   => (object) ($detalle->opc_categoria ?? []),

            // 👉 OBSERVACIONES
            'notas'           => $detalle->comentario ?? '',
        ];
    });


        // Ahora usa json_encode con JSON_UNESCAPED_UNICODE
        // Ajuste en el controlador
        $detallesMovimientosJson = json_encode($productosConsumidos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


        // Obtener datos relacionados
        $detallesMovimientos = DetMovcaja::where('movcaja_id', $movventa->movcaja->id ?? null)
            ->get()
            ->map(function ($detalle) {
                // Inicializar el campo "detalles" como un array vacío
                $detalles = [];

                // Agregar solo los campos que no son nulos
                if (!empty($detalle->tarjeta_id)) {
                    $detalles[] = ['tarjeta' => $detalle->tarjeta];
                }
                if (!empty($detalle->banco_id)) {
                    $detalles[] = ['banco' => $detalle->banco];
                }
                if (!empty($detalle->billeteradigital_id)) {
                    $detalles[] = ['billetera_digital' => $detalle->billeteradigital];
                }
                if (!empty($detalle->pos_id)) {
                    $detalles[] = ['pos' => $detalle->pos];
                }
                // Devolver los datos del detalle junto con el campo "detalles" agregado
                return [
                    'tipo' => $detalle->tipo,
                    'monto' => $detalle->monto,
                    'medio_pago' => $detalle->mediopago,
                    'mediopago_id' => $detalle->mediopago_id,
                    'detalles' => $detalles, // Usamos la misma lógica para el campo "detalles"
                    'notas' => $detalle->comentario ?? 'Sin notas',
                ];
            })
            ->toArray();

        // Obtener los detalles de alojamiento relacionados
        $detallesAlojamiento = Movhabitacion::where('movimiento_id', $movventa->movimiento_id)
            ->with(['habitacion'])
            ->get();
        $medio_pagos = Mediopago::where('situacion', true)->get();
        $pos = Pasarelapago::where('situacion', true)->get(); // Tabla para P.O.S.
        $tarjetas = Tarjetum::where('situacion', true)->get(); // Tabla para Tarjetas
        $bancos = Banco::where('situacion', true)->get(); // Tabla para Bancos
        $billeteras = Billeteradigital::where('situacion', true)->get(); // Tabla para Billeteras Digitales

        return view('movventa.edit', compact(
            'movventa',
            'medio_pagos',
            'pos',
            'tarjetas',
            'bancos',
            'billeteras',
            'tiposDocumento',
            'tipoDocumentoPredeterminado',
            'personas',
            'vendedores',
            'categorias',
            'cajas',
            'sucursal_id',
            'numeroComprobante',
            'detallesMovimientos',
            'medio_pagos',
            'detallesMovimientosJson',
            'detallesAlojamiento',
            'sucursal_id'
        ));
    }


    public function verDetalles($id)
    {
        try {
            // Obtener el registro Movventa
            $movventa = Movventum::with(['detMovventas', 'movimiento', 'movimiento.persona'])->findOrFail($id);
            $medio_pagos = Mediopago::where('situacion', true)->get();

            // Obtener datos relacionados
            $tiposDocumento = Tipodocumento::where('tipomovimiento_id', 2)->get();
            $tipoDocumentoPredeterminado = $tiposDocumento->where('id', $movventa->movimiento->tipodocumento_id)->first();
            $sucursal_id = Auth::user()->persona->sucursal_id;
            $personas = Persona::where('sucursal_id', $sucursal_id)->get();

            // Obtener los productos relacionados con la sucursal especificada
            $productos = Producto::with(['relProductosucursal', 'relListaUnidadSucursal'])
                ->whereHas('relProductosucursal', function ($query) use ($sucursal_id) {
                    $query->where('sucursal_id', $sucursal_id);
                })
                ->get();
            $categorias = Categorium::whereNull('categoria_id')
                ->whereHas('relCategoriasucursal', function ($query) use ($sucursal_id) {
                    $query->where('sucursal_id', $sucursal_id)->where('tipocarta', '!=', 'NINGUNA');
                })
                ->with('categorias')
                ->get();
            $cajas = Caja::where('situacion', 'A')->where('sucursal_id', $sucursal_id)->get();
            $vendedores = Persona::whereHas('rolpersonas', function ($query) {
                $query->where('rol_id', 1);
            })->where('sucursal_id', $sucursal_id)->get();

            // Determinar la serie de la caja asociada
            $caja = $cajas->first();
            $serie = $caja ? str_pad($caja->serie, 3, "0", STR_PAD_LEFT) : '001';

            // Calcular el número correlativo basado en el movimiento actual
            $correlativo = str_pad(
                $movventa->movimiento->numero ?? $this->generateNumero($movventa->movimiento->tipodocumento_id, false),
                8,
                "0",
                STR_PAD_LEFT
            );

            // Generar el número de comprobante
            $abreviatura = $tipoDocumentoPredeterminado->abreviatura ?? 'N/A';
            $numeroComprobante = "{$abreviatura}{$serie}-{$correlativo}";

            // Ajuste dentro del map de $productosConsumidos
            $productosConsumidos = DetMovventum::whereHas('movventum', function ($query) use ($movventa) {
                $query->where('movimiento_id', $movventa->movimiento_id);
            })
                ->get()
                ->map(function ($producto) {
                    $datosProducto = json_decode($producto->json_producto, true);

                    return [
                        'producto_id' => $producto->producto_id,
                        'situacion' => $datosProducto['situacion'] ?? 'N',

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


            // Obtener datos relacionados
            $detallesMovimientos = DetMovcaja::where('movcaja_id', $movventa->movcaja->id ?? null)
                ->get()
                ->map(function ($detalle) {
                    // Inicializar el campo "detalles" como un array vacío
                    $detalles = [];

                    // Agregar solo los campos que no son nulos
                    if (!empty($detalle->tarjeta_id)) {
                        $detalles[] = ['tarjeta' => $detalle->tarjeta];
                    }
                    if (!empty($detalle->banco_id)) {
                        $detalles[] = ['banco' => $detalle->banco];
                    }
                    if (!empty($detalle->billeteradigital_id)) {
                        $detalles[] = ['billetera_digital' => $detalle->billeteradigital];
                    }
                    if (!empty($detalle->pos_id)) {
                        $detalles[] = ['pos' => $detalle->pos];
                    }
                    // Devolver los datos del detalle junto con el campo "detalles" agregado
                    return [
                        'tipo' => $detalle->tipo,
                        'monto' => $detalle->monto,
                        'medio_pago' => $detalle->mediopago,
                        'mediopago_id' => $detalle->mediopago_id,
                        'detalles' => $detalles, // Usamos la misma lógica para el campo "detalles"
                        'notas' => $detalle->comentario ?? 'Sin notas',
                    ];
                })
                ->toArray();

            // Obtener los detalles de alojamiento relacionados
            $detallesAlojamiento = Movhabitacion::where('movimiento_id', $movventa->movimiento_id)
                ->with(['habitacion', 'movreserva'])
                ->get();
            $medio_pagos = Mediopago::where('situacion', true)->get();
            $pos = Pasarelapago::where('situacion', true)->get(); // Tabla para P.O.S.
            $tarjetas = Tarjetum::where('situacion', true)->get(); // Tabla para Tarjetas
            $bancos = Banco::where('situacion', true)->get(); // Tabla para Bancos
            $billeteras = Billeteradigital::where('situacion', true)->get(); // Tabla para Billeteras Digitales

            return view('movventa.verdetalles', compact(
                'movventa',
                'medio_pagos',
                'pos',
                'tarjetas',
                'bancos',
                'billeteras',
                'tiposDocumento',
                'tipoDocumentoPredeterminado',
                'personas',
                'vendedores',
                'categorias',
                'cajas',
                'sucursal_id',
                'numeroComprobante',
                'detallesMovimientos',
                'medio_pagos',
                'detallesMovimientosJson',
                'detallesAlojamiento'
            ));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $userBranch = (int) Auth::user()->persona?->sucursal_id;
        abort_unless($userBranch && (int) Movventum::whereKey($id)->value('sucursal_id') === $userBranch
            && (int) $request->input('sucursal_id') === $userBranch, 403, 'La venta no corresponde a tu sucursal.');
        DB::beginTransaction();

        try {
            // Validar los datos
            $validator = Validator::make($request->all(), [
                'sucursal_id' => 'required|exists:sucursal,id',
                'tipo_detalle' => 'required|string|in:DETALLADO,GLOSA',
                'estado' => 'nullable|string',
                'moneda' => 'required|string|in:PEN,USD',
                'total' => 'required|numeric|min:0',
                'detalles_json' => 'required|json',
                'detalles_movimientos_json' => 'required|json',
                'persona_id' => 'nullable|exists:persona,id',
                'caja_id' => 'required|exists:caja,id',
                'comentario' => 'nullable|string',
                'numero_movimiento_manual' => ['nullable', 'regex:/^\d{1,8}$/'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $validacionGlosa = $this->validarDescripcionObligatoriaParaGlosa($request);
            if ($validacionGlosa) {
                return $validacionGlosa;
            }

            // Obtener los registros existentes
            $movventa = Movventum::with(['movimiento', 'detMovventas'])->findOrFail($id);
            $movimiento = $movventa->movimiento;
            if ($movimiento->getRawOriginal('situacion') !== 'A') {
                throw ValidationException::withMessages(['venta' => 'No se puede editar una venta anulada.']);
            }
            $inventarioGestionado = app(InventarioVentaService::class)->revertirSalida($movventa, $movimiento);

            // Calcular totales de los detalles primero (los precios en frontend incluyen IGV)
            $detalles = json_decode($request->detalles_json, true) ?? [];
            $detallesMovCaja = json_decode($request->detalles_movimientos_json, true) ?: [];
            $tipoPago = $this->resolverTipoPago($detallesMovCaja);
            $totalIgv = 0;
            $totalSinIgv = 0;
            $totalGeneral = 0;

            foreach ($detalles as $detalle) {
                $igvId = $detalle['igv_id'] ?? null;
                $igvLinea = $igvId ? Igv::find($igvId) : null;
                $porcentajeIgv = floatval($igvLinea?->porcentaje_igv ?? 0);
                $montoLinea = floatval($detalle['precio_unitario'] ?? 0) * floatval($detalle['cantidad'] ?? 0);
                
                $subtotalLinea = $montoLinea / ($porcentajeIgv / 100 + 1);
                $igvLineaMonto = $montoLinea - $subtotalLinea;
                
                $totalSinIgv += $subtotalLinea;
                $totalIgv += $igvLineaMonto;
                $totalGeneral += $montoLinea;
            }

            $updateMovimiento = [
                'persona_id' => $request->persona_id,
                'comentario' => $request->notas ?? '',
                'situacion' => $request->estado ?? 'A',
                'persona' => $request->persona_id ? Persona::find($request->persona_id)->nombres . ' ' . Persona::find($request->persona_id)->apellidos : '',
            ];

            $numeroMovimientoManual = trim((string) $request->input('numero_movimiento_manual', '0'));
            $numeroActualNormalizado = str_pad((string) ((int) ($movimiento->numero ?? 0)), 8, '0', STR_PAD_LEFT);
            if ($numeroMovimientoManual !== '' && $numeroMovimientoManual !== '0') {
                $numeroSolicitado = str_pad((string) ((int) $numeroMovimientoManual), 8, '0', STR_PAD_LEFT);
                if ($numeroSolicitado !== $numeroActualNormalizado) {
                    $updateMovimiento['numero'] = $numeroSolicitado;
                }
            }

            // Actualizar Movimiento
            $movimiento->update($updateMovimiento);
            $facturador = Facturador::where('sucursal_id', $request->sucursal_id)->first();
            $solicitud = Solicitud::where('movimiento_id', $movimiento->id)->first();
            if ($solicitud) {
                $payloadSolicitud = [
                    'total' => $totalGeneral,
                ];
                if (isset($updateMovimiento['numero'])) {
                    $nuevoNumeroStr = $updateMovimiento['numero'];
                    $serieSinPrimeraLetra = substr($movventa->serie, 1);
                    $numerocomprobante = $serieSinPrimeraLetra . '-' . $nuevoNumeroStr;
                    $tipoNumeroKey = ($solicitud->tipo_documento === 'B') ? 'numeroboleta' : 'numerofactura';
                    $dataDecoded = json_decode($solicitud->data, true);
                    if (is_array($dataDecoded)) {
                        $dataDecoded[$tipoNumeroKey] = $numerocomprobante;
                        $payloadSolicitud['data'] = json_encode($dataDecoded);
                    }
                    $payloadSolicitud['correlativo'] = (int) $nuevoNumeroStr;
                }
                $solicitud->update($payloadSolicitud);
            }

            // Actualizar Movventa con totales calculados
            $movventa->update([
                'persona_id' => $request->persona_id,
                'comentario' => $request->comentario ?? '',
                'tipo_detalle' => $request->tipo_detalle,
                'moneda' => $request->moneda,
                'subtotal' => $totalSinIgv,
                'tipo_pago' => $tipoPago,
                'igv' => $totalIgv,
                'total' => $totalGeneral,
            ]);


            // Procesar Detalles de Venta
            $movventa->detMovventas()->delete(); // Eliminar detalles anteriores

            foreach ($detalles as $detalle) {
                $unidadDescripcion = $detalle['unidad'] ?? null;  // Verificar si 'unidad' existe en $detalle

                // Buscar la unidad por la descripción proporcionada o, si no existe, buscar la unidad 'Servicio(s)'
                $unidad = Unidad::where('descripcion', $unidadDescripcion ?: 'Servicio(s)')->first();
                
                $producto = Producto::find($detalle['producto_id']);  // Buscar el producto en la base de datos

                $igvId = $detalle['igv_id'] ?? null;
                if (!$igvId && !empty($detalle['producto_id'])) {
                    $relProductoSucursal = RelProductosucursal::where('producto_id', $detalle['producto_id'])
                        ->where('sucursal_id', $request->sucursal_id)
                        ->first();
                    $igvId = $relProductoSucursal?->igv_id;
                }

                $igvClase = $igvId ? Igv::find($igvId) : null;

                $detalleVenta = DetMovventum::create([
                    'movventa_id' => $movventa->id,
                    'tipo_detalle' => $request->tipo_detalle,
                    'unidad_id' => $unidad ? $unidad->id : null,
                    'igv_id' => $igvId,
                    'json_igv' => $igvClase ? json_encode($igvClase) : null,
                    'json_unidad' => $unidad ? json_encode($unidad) : null,
                    'codigo' => $detalle['codigo'],
                    'descripcion' => $detalle['descripcion'],
                    'producto_id' => $producto ? $producto->id : null,  // Asignar null si el producto no existe
                    'json_producto' => json_encode($detalle),
                    'cantidad' => $detalle['cantidad'],
                    'monto' => $detalle['precio_unitario'] * $detalle['cantidad'],
                    'dcto_porcentaje' => $detalle['dcto_porcentaje'] ?? 0,
                    'monto_original' => $detalle['total'],
                    'comentario' => $detalle['notas'] ?? '',
                    'det_categoria' => json_encode($detalle['categoria'] ?? []),
                    'opc_categoria' => json_encode($detalle['opc_categoria'] ?? []),
                    'sucursal_id' => $request->sucursal_id,
                ]);
                if ($inventarioGestionado) {
                    app(InventarioVentaService::class)->registrarSalida($detalle, $detalleVenta, $movventa, $movimiento, Auth::user());
                }
            }

            // Actualizar Movcaja y Detalles
            $movcaja = $movventa->movcaja; // Obtener el Movcaja relacionado
            $movcaja->update([
                'total' => $totalGeneral,
            ]);

            $movcaja->detMovcaja()->delete(); // Eliminar detalles anteriores
            foreach ($detallesMovCaja as $detalle) {
                DetMovcaja::create([
                    'movcaja_id' => $movcaja->id,
                    'tipo' => $detalle['tipo'] ?? null,
                    'fechavencimiento' => !empty($detalle['fechavencimiento']) ? $detalle['fechavencimiento'] : null,

                    'fechapago' => now(),

                    // Medio de pago
                    'mediopago_id' => $detalle['mediopago_id'] ?? null,
                    'mediopago' => $detalle['mediopago_id']
                        ? Mediopago::find($detalle['mediopago_id'])->descripcion ?? ''
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

                // Recorrer el JSON de detalles
                foreach ($detalle['detalles'] ?? [] as $item) {
                    // Banco
                    if (!empty($item['banco'])) {
                        $bancoDescripcion = trim($item['banco']);
                        DetMovcaja::where('movcaja_id', $movcaja->id)->update([
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

                            DetMovcaja::where('movcaja_id', $movcaja->id)->update([
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
                        DetMovcaja::where('movcaja_id', $movcaja->id)->update([
                            'billeteradigital_id' => Billeteradigital::where('descripcion', $billeteraDescripcion)->value('id'),
                            'billeteradigital' => $billeteraDescripcion
                        ]);
                    }

                    // POS
                    if (!empty($item['pos'])) {
                        $posDescripcion = trim($item['pos']);
                        DetMovcaja::where('movcaja_id', $movcaja->id)->update([
                            'pos_id' => Pasarelapago::where('descripcion', $posDescripcion)->value('id'),
                            'pos' => $posDescripcion
                        ]);
                    }
                }
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta actualizada con éxito.',
                'redirect_url' => route('movventa.index')
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $movventa = Movventum::findOrFail($id);
            $movventa->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta eliminada correctamente.',
                'redirect_url' => route('movventa.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'errors' => $e->getMessage()], 500);
        }
    }
    public function anular($id)
    {
        $branch = (int) Auth::user()->persona?->sucursal_id;
        abort_unless($branch, 403);
        DB::beginTransaction();

        try {
            // Obtener la venta y su movimiento
            $movventa = Movventum::with('detMovventas', 'movcaja')->where('sucursal_id', $branch)->findOrFail($id);
            $movimientoVenta = Movimiento::findOrFail($movventa->movimiento_id);
            if ($movimientoVenta->getRawOriginal('situacion') !== 'A') {
                throw ValidationException::withMessages(['venta' => 'La venta ya fue anulada.']);
            }
            $movimientoCaja = Movimiento::findOrFail($movventa->movcaja->movimiento_id);
            $movhabitacion = Movhabitacion::find($movventa->movhabitacion_id);
            $detMovventaIds = $movventa->detMovventas->pluck('id')->filter()->values();
            $detallesHabitacionAsociados = collect();
            $ventaSoloProductos = $movhabitacion
                && $movventa->detMovventas->isNotEmpty()
                && $movventa->detMovventas->every(fn($detalle) => !is_null($detalle->producto_id));

            if ($movhabitacion && $detMovventaIds->isNotEmpty()) {
                $detallesHabitacionAsociados = DetMovhabitacion::where('movhabitacion_id', $movhabitacion->id)
                    ->whereIn('det_movventa_id', $detMovventaIds)
                    ->get();
            }

            if ($ventaSoloProductos && $detallesHabitacionAsociados->isEmpty()) {
                $candidatos = collect();

                foreach ($movventa->detMovventas as $detalleVenta) {
                    $candidato = DetMovhabitacion::where('movhabitacion_id', $movhabitacion->id)
                        ->where('situacion', 'P')
                        ->where('producto_id', $detalleVenta->producto_id)
                        ->where('cantidad', $detalleVenta->cantidad)
                        ->where('monto', $detalleVenta->monto)
                        ->orderByDesc('id')
                        ->first();

                    if ($candidato) {
                        $candidatos->push($candidato);
                    }
                }

                $detallesHabitacionAsociados = $candidatos->unique('id')->values();
            }

            $esPagoProductosAlojamiento = $movhabitacion
                && ($detMovventaIds->isNotEmpty() || $ventaSoloProductos)
                && $detallesHabitacionAsociados->isNotEmpty()
                && (
                    $ventaSoloProductos
                    || $detMovventaIds->diff($detallesHabitacionAsociados->pluck('det_movventa_id')->filter()->unique())->isEmpty()
                );

            $usuario = Auth::user();

            // Marcar el movimiento como anulado
            $movimientoVenta->update(['situacion' => 'D']);
            $movimientoCaja->update(['situacion' => 'D']);

            // 🆕 Registrar en histórico de anulaciones
            Anulacion::create([
                'movimiento_id' => $movimientoVenta->id,
                'usuario_id' => $usuario->id,
                'fecha' => now()->toDateTimeString(),
                'motivo' => request('motivo') ?? 'Anulación de venta',
            ]);

            // Restaurar solo salidas que existan en el kárdex; ventas históricas sin salida quedan intactas.
            if (!$esPagoProductosAlojamiento) app(InventarioVentaService::class)->revertirSalida($movventa, $movimientoVenta);

            // Crear nuevo movimiento duplicando la info del anterior
            $tipodocumento = $movimientoVenta->tipodocumento;
            $sucursal = $movimientoVenta->sucursal;

            

            // 🟢 Verificar y actualizar Movreserva
            $movreserva = Movreserva::find($movventa->movreserva_id);
            if ($movreserva && !$esPagoProductosAlojamiento) {
                $movreserva->update([
                    'estado' => 'EN ATENCIÓN',
                ]);
                MovreservaHabitacion::where('movreserva_id', $movreserva->id)
                    ->update(['situacion' => 'EN ATENCIÓN']);
            }

            // 🔵 Verificar y actualizar Movhabitacion
            if ($movhabitacion) {
                if ($esPagoProductosAlojamiento) {
                    DetMovhabitacion::whereIn('id', $detallesHabitacionAsociados->pluck('id'))
                        ->update([
                            'situacion' => 'N',
                            'det_movventa_id' => null,
                        ]);
                } else {
                    $movimientoHab = Movimiento::findOrFail($movhabitacion->movimiento_id);
                    $movimientoHab->update(['situacion' => 'A']);

                    $movhabitacion->update([
                        'situacion' => 'A',
                        'fecha_salida_real' => null,
                        'movreserva_id' => $movreserva->id ?? $movhabitacion->movreserva_id,
                    ]);

                    $detalles = MovhabitacionHabitacion::where('movhabitacion_id', $movhabitacion->id)->get();

                    foreach ($detalles as $detalle) {
                        $detalle->update([
                            'situacion' => 'A',
                            'fechacheckout' => null,
                        ]);
                    }

                    $habitacionIds = $detalles->pluck('habitacion_id')->filter()->unique();
                    if ($habitacionIds->isNotEmpty()) {
                        Habitacion::whereIn('id', $habitacionIds)->update(['situacion' => 'Ocupada']);
                    }
                }
            }


            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento de venta anulado correctamente.',
                'redirect_url' => route('movventa.index')
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }


    public function convertirstore(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // Obtener la venta y su movimiento
            $movventa = Movventum::with('detMovventas')->findOrFail($id);
            $movimiento = Movimiento::findOrFail($movventa->movimiento_id);
            $usuario = Auth::user();

            // Marcar el movimiento original como anulado
            $movimiento->update(['situacion' => 'D']);

            // // // Revertir stock y actualizar Kardex
            // foreach ($movventa->detMovventas as $detalle) {
            //     $stockProducto = Stockproducto::where('producto_id', $detalle->producto_id)
            //         ->where('sucursal_id', $movventa->sucursal_id)
            //         ->first();

            //     if ($stockProducto) {
            //         $stockProducto->stockbase += $detalle->cantidad;
            //         $stockProducto->save();
            //     }

            //     Kardex::where('producto_id', $detalle->producto_id)
            //         ->where('movimiento_id', $movimiento->id)
            //         ->update(['situacion' => 'A']);
            // }

            // Obtener datos base para nuevo movimiento
            $tipodocumento = Tipodocumento::findOrFail($request->tipodocumento_id);
            $sucursal = $movimiento->sucursal;
            $persona = Persona::findOrFail($request->persona_id);
            $fechaConversion = now();

            // Crear nuevo movimiento
            $nuevoMovimiento = Movimiento::create([
                'numero' => $this->generateNumero($tipodocumento->id),
                'fecha' => $fechaConversion,
                'usuario_id' => $usuario->id,
                'usuario' => $usuario->name,
                'responsable_id' => $movimiento->responsable_id,
                'responsable' => $movimiento->responsable,
                'persona_id' => $persona->id,
                'persona' => $persona->nombres . ' ' . $persona->apellidos,
                'comentario' => '',
                'situacion' => 'A',
                'tipomovimiento_id' => $tipodocumento->tipomovimiento_id,
                'tipodocumento_id' => $tipodocumento->id,
                'sucursal_id' => $sucursal->id,
            ]);

            // Actualizar Movhabitacion (si existe)
            $movhabitacion = Movhabitacion::where('id', $movventa->movhabitacion_id)->first();
            if ($movhabitacion) {
                $nuevoMovimiento->update(['movimiento_id' => $movhabitacion->movimiento_id]);
            }
            $movcaja = Movcaja::where('movimiento_id', $movventa->movcaja->movimiento_id)->first();
            $movimientomovcaja = Movimiento::find( $movcaja->movimiento_id);
            $movimientomovcaja->update(['movimiento_id' => $nuevoMovimiento->id]);
            // Actualizar Movreserva (si existe)
            $movreserva = Movreserva::where('id', $movventa->movreserva_id)->first();
         

            // Crear nuevo movventa copiando todos los campos necesarios
            $nuevoMovventa = Movventum::create([
                'json_sucursal' => $movventa->json_sucursal,
                'serie' => $tipodocumento->abreviatura . '' . $movcaja->Caja->serie,
                'anio' => $fechaConversion->year,
                'tipo_detalle' => $movventa->tipo_detalle,
                'consumo' => $movventa->getRawOriginal('consumo'),
                'tipo_pago' => $movventa->tipo_pago,
                'estado' => $movventa->getRawOriginal('estado'),
                'tipo_venta' => $movventa->tipo_venta,
                'moneda' => $movventa->moneda,
                'tipocambio' => $movventa->tipocambio,
                'subtotal' => $movventa->subtotal,
                'igv' => $movventa->igv,
                'total' => $movventa->total,
                'tipo_cobro' => $movventa->tipo_cobro,
                'movearlylate_id' => $movventa->movearlylate_id,
                'movimiento_id' => $nuevoMovimiento->id,
                'sucursal_id' => $movventa->sucursal_id,
                'movhabitacion_id' => $movhabitacion->id ?? null,
                'movreserva_id' => $movreserva->id ?? null,
                'detraccion' => $movventa->detraccion ?? 'N',
                'porcentaje_detraccion' => $movventa->porcentaje_detraccion ?? 0,
                'cuenta_detraccion' => $movventa->cuenta_detraccion ?? '',
                'codigo_bien_detraccion' => $movventa->codigo_bien_detraccion ?? '',
            ]);
            foreach ($movventa->detMovventas as $detalle) {
                $detalle->update(['movventa_id' => $nuevoMovventa->id]);
            }

            $detalles = collect($nuevoMovventa->detMovventas)->map(function ($detalle) {
                $json = json_decode($detalle->json_producto, true);

                // Asegurar que cantidad y total tengan valores numéricos válidos
                $cantidad = floatval($detalle->cantidad);
                $total = floatval($detalle->monto);

                return [
                    'producto_id' => $json['producto_id'] ?? null,
                    'situacion' => $json['situacion'] ?? 'N',
                    'descripcion' => $json['descripcion'] ?? $detalle->descripcion,
                    'precio_compra' => $json['precio_compra'] ?? 0,
                    'unidad' => $json['unidad'] ?? 'Unidad(es)',
                    'cantidad' => $cantidad,
                    'igv' => $json['igv'] ?? 0,
                    'precio_unitario' => $cantidad > 0 ? round($total / $cantidad, 6) : 0,
                    'total' => $total,
                    'codigo' => $json['codigo'] ?? $detalle->codigo,
                    'categoria' => $json['categoria'] ?? 'Sin Categoría',
                    'opc_categoria' => $json['opc_categoria'] ?? [],
                    'notas' => $json['notas'] ?? '',
                ];
            })->values()->toArray();


            //   dd($detalles);
            $facturadorController = new FacturadorController();

            if ($tipodocumento->abreviatura !== 'T') {
                $solicitudFacturacion = $facturadorController->enviarFacturacionElectronica(
                    $nuevoMovventa,
                    collect($detalles),
                    [],
                    $persona,
                    Sucursal::find($sucursal->id),
                    Facturador::where('sucursal_id', $sucursal->id)->first(),
                    $movcaja,
                    collect($movcaja->detMovcaja),
                    false,
                    null,

                );
                // 🔴 Si hubo un error lógico en la solicitud, cancelar la transacción
                if (isset($solicitudFacturacion['success']) && $solicitudFacturacion['success'] === false) {
                    throw new \Exception($solicitudFacturacion['message'] ?? 'Error en la facturación electrónica');
                }
            }
            $pdfUrl = route('movventa.imprimirComprobante', ['id' => $nuevoMovventa->id]);

            DB::commit();

            if (isset($solicitudFacturacion)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Movimiento de venta convertido correctamente.',
                    'redirect_url' => route('movventa.index'),
                    'pdf_url' => $pdfUrl,
                    'facturacion_response' => $solicitudFacturacion
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'pdf_url' => $pdfUrl,

                    'message' => 'Movimiento de venta convertido correctamente.',
                    'redirect_url' => route('movventa.index'),
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $movventa = Movventum::with('detMovventa')->findOrFail($id);

        return view('movventa.show', compact('movventa'));
    }
    public function generarPDF($id, $returnContent = false)
    {
        // Buscar el registro de la tabla movventa con relaciones necesarias
        $movventa = Movventum::with([
            'movimiento',
            'sucursal',
            'detMovventas.producto',
            'detMovventas.igv',
            'movimiento.Persona',
            'movcaja.detMovcaja.Mediopago',
            'movcaja.detMovcaja.billeteradigital',
        ])->findOrFail($id);

        $formaPagoComprobante = $this->formatearFormaPagoComprobante($movventa);
        $cuotasComprobante = $this->obtenerCuotasComprobante($movventa);

        $porcentajeDetraccionGen = floatval($movventa->porcentaje_detraccion ?? 0);
        $tieneDetraccionGen = ($movventa->detraccion == 'S' || $porcentajeDetraccionGen > 0);
        $montoDetraccionGen = ($tieneDetraccionGen && $porcentajeDetraccionGen > 0)
            ? round(($movventa->total * $porcentajeDetraccionGen) / 100, 2)
            : 0;

        $montoNetoPendienteGen = null;
        if ($this->esVentaCredito($movventa)) {
            $montoNetoPendienteGen = max(0, round($movventa->total - $montoDetraccionGen, 2));
        }

        $movimiento = Movimiento::find($movventa->movimiento_id);

        // Calcular el número correlativo (basado en registros existentes en `movimiento`)
        $correlativo = str_pad($movimiento->numero, 8, "0", STR_PAD_LEFT);

        // Calcular o definir el número del comprobante
        $numero_comprobante = $movventa->serie . '-' . $correlativo;

        // 🔹 Obtener todos los registros de `movhabitacion_habitacion` con el mismo `movventa_id`
        $movHabitacionesHab = MovhabitacionHabitacion::where('movventa_id', $movventa->id)
            ->with(['habitacion.tipohabitacion', 'movhabitacion'])
            ->get();

        // Si no se encuentran registros en MovhabitacionHabitacion
        if ($movHabitacionesHab->isEmpty()) {
            // Obtener el movimiento_id de la tabla Movventa
            $movimiento_id = $movventa->movimiento_id;

            // Buscar el Movhabitacion usando el movimiento_id
            $movHabitacionesHab = MovhabitacionHabitacion::join('movhabitacion', 'movhabitacion.id', '=', 'movhabitacion_habitacion.movhabitacion_id')
                ->where('movhabitacion.movimiento_id', $movimiento_id)
                ->with(['habitacion.tipohabitacion', 'movhabitacion']) // Cargar las relaciones necesarias
                ->get();
        }



        if ($movHabitacionesHab->count() > 1) {
            // 🔹 Tomar el primer registro y modificar solo el total
            $movHabitacionHab = $movHabitacionesHab->first();
            $movHabitacionHab->total = $movHabitacionesHab->sum('total'); // Sumar solo los totales
        } else {
            // Si solo hay un registro, tomarlo como está
            $movHabitacionHab = $movHabitacionesHab->first();
        }


        $alojamiento = $movHabitacionHab
            ? [
                'habitacion' => $movHabitacionHab->habitacion->numero ?? 'N/A',
                'tipo' => $movHabitacionHab->habitacion->tipohabitacion->nombre ?? 'N/A',
                'costo' => $movHabitacionHab->total ?? 0,
                'dias' => $movHabitacionHab->dias ?? 0,
                'horas' => $movHabitacionHab->horas ?? 0,
                'modalidad' => $movHabitacionHab->modalidad ?? 'N/A',
            ]
            : null;

        // Preparar datos para la vista
        $comprobante = (object) [
            'nombre' => 'Comprobante de Venta',
            'tipo_documento' => $movventa->movimiento->tipodocumento->abreviatura ?? 'N/A',
        ];

        $sucursal = $movventa->sucursal;
        $fechaComprobante = $this->fechaEmisionComprobante($movventa);

        $xml_data = collect([
            'fecha' => $fechaComprobante->format('d/m/Y'),
            'nombre_cliente' => $movventa->movimiento->persona ?? 'N/A',
            'nro_doc' => $movventa->movimiento->Persona->personamaestro->nrodoc ?? 'N/A',
            'moneda' => $movventa->moneda,
            'productos' => $movventa->detMovventas->pluck('descripcion')->toArray(),
            'unidades' => $movventa->detMovventas->map(fn($detMovventa) => $detMovventa->unidad->abreviatura ?? 'N/A')->toArray(),
            'cantidades' => $movventa->detMovventas->pluck('cantidad')->toArray(),
            'precios' => $movventa->detMovventas->pluck('monto')->toArray(),
            'importes' => $movventa->detMovventas->pluck('monto_original')->toArray(),
            'tipos_igv' => $movventa->detMovventas->map(fn($detMovventa) => $detMovventa->igv->codigo ?? null)->toArray(),
            'porcentajes_igv' => $movventa->detMovventas->map(fn($detMovventa) => $detMovventa->igv->porcentaje_igv ?? null)->toArray(),
            'total_igv' => $movventa->igv,
            'subtotal' => $movventa->subtotal,
            'formapago' => $formaPagoComprobante,
            'fecha_cuotas' => collect($cuotasComprobante)->pluck('fecha')->all(),
            'monto_cuotas' => collect($cuotasComprobante)->pluck('monto')->all(),
            'monto_neto_pendiente' => $montoNetoPendienteGen,
            'total_monto' => $movventa->total,
            'moneda_simbolo' => $movventa->moneda == 'USD' ? '$' : 'S/',
            'textoQR' => $this->generarTextoQR($movventa, $fechaComprobante),
            'alojamiento' => $alojamiento, // Información sobre el alojamiento
            'observacion' => $movventa->movimiento->comentario,

        ]);
  //  dd($movventa->movcaja->detMovcaja->pluck('mediopago')->implode(', '));
        $persona = (object) [
            'direccion' => $movventa->movimiento->Persona->direccion ?? 'N/A',
        ];

        // Instancia de la clase NumeroTexto
        $numeroTexto = new NumeroTexto();

        // Conversión del total a texto con la moneda
        $numero_a_texto = $numeroTexto->convertirLetras($movventa->total, $xml_data['moneda']);

        // Generar el PDF
        $pdf = Pdf::loadView('movventa.comprobante',
         compact('movventa', 'comprobante', 'sucursal', 'xml_data', 'persona', 'numero_a_texto', 'numero_comprobante'))
         ->setOption('margin-bottom', 5)->setOption(
            'footer-html',
            view('movcaja.pdf_footer')->render()
        );

        if ($returnContent) {
            return $pdf->output();
        }

        // Descargar el archivo PDF
        return $pdf->stream("{$numero_comprobante}.pdf");
    }


    public function imprimirComprobante($id)
    {
        // Buscar el registro de la tabla movventa con relaciones necesarias
        $movventa = Movventum::with([
            'movimiento',
            'sucursal',
            'detMovventas.producto',
            'detMovventas.igv',
            'movimiento.Persona',
            'movcaja.detMovcaja.Mediopago',
            'movcaja.detMovcaja.billeteradigital',
        ])->findOrFail($id);

        $formaPagoComprobante = $this->formatearFormaPagoComprobante($movventa);
        $cuotasComprobante = $this->obtenerCuotasComprobante($movventa);

        $porcentajeDetraccionImp = floatval($movventa->porcentaje_detraccion ?? 0);
        $tieneDetraccionImp = ($movventa->detraccion == 'S' || $porcentajeDetraccionImp > 0);
        $montoDetraccionImp = ($tieneDetraccionImp && $porcentajeDetraccionImp > 0)
            ? round(($movventa->total * $porcentajeDetraccionImp) / 100, 2)
            : 0;

        $montoNetoPendienteImp = null;
        if ($this->esVentaCredito($movventa)) {
            $montoNetoPendienteImp = max(0, round($movventa->total - $montoDetraccionImp, 2));
        }

        $movimiento = Movimiento::find($movventa->movimiento_id);

        // Calcular el número correlativo (basado en registros existentes en `movimiento`)
        $correlativo = str_pad($movimiento->numero, 8, "0", STR_PAD_LEFT);

        // Calcular o definir el número del comprobante
        $numero_comprobante = $movventa->serie . '-' . $correlativo;

        // 🔹 Obtener todos los registros de `movhabitacion_habitacion` con el mismo `movventa_id`
        $movHabitacionesHab = MovhabitacionHabitacion::where('movventa_id', $movventa->id)
            ->with(['habitacion.tipohabitacion', 'movhabitacion'])
            ->get();
        // Si no se encuentran registros en MovhabitacionHabitacion
        if ($movHabitacionesHab->isEmpty()) {
            // Obtener el movimiento_id de la tabla Movventa
            $movimiento_id = $movventa->movimiento_id;

            // Buscar el Movhabitacion usando el movimiento_id
            $movHabitacionesHab = MovhabitacionHabitacion::join('movhabitacion', 'movhabitacion.id', '=', 'movhabitacion_habitacion.movhabitacion_id')
                ->where('movhabitacion.movimiento_id', $movimiento_id)
                ->with(['habitacion.tipohabitacion', 'movhabitacion']) // Cargar las relaciones necesarias
                ->get();
        }
        if ($movHabitacionesHab->count() > 1) {
            // 🔹 Tomar el primer registro y modificar solo el total
            $movHabitacionHab = $movHabitacionesHab->first();
            $movHabitacionHab->total = $movHabitacionesHab->sum('total'); // Sumar solo los totales
        } else {
            // Si solo hay un registro, tomarlo como está
            $movHabitacionHab = $movHabitacionesHab->first();
        }



        $alojamiento = $movHabitacionHab
            ? [
                'habitacion' => $movHabitacionHab->habitacion->numero ?? 'N/A',
                'modalidad' => $movHabitacionHab->modalidad ?? 'N/A',
                'tipo' => $movHabitacionHab->habitacion->tipohabitacion->nombre ?? 'N/A',
                'costo' => $movHabitacionHab->total ?? 0,
                'preciounitario' => $movHabitacionHab->tarifa ?? 0,

                'dias' => $movHabitacionHab->dias ?? 0,
                'horas' => $movHabitacionHab->horas ?? 0,
            ]
            : null;
        //  dd($movHabitacionHab);
        // Preparar datos para la vista
        $comprobante = (object) [
            'nombre' => 'Comprobante de Venta',
            'tipo_documento' => $movventa->movimiento->tipodocumento->abreviatura ?? 'N/A',
            'nombre_documento' => $movventa->movimiento->tipodocumento->nombre ?? 'N/A',
        ];

        $sucursal = $movventa->sucursal;

        // Conversión del total a texto
        $numeroTexto = new NumeroTexto();
        $numero_a_texto = $numeroTexto->convertirLetras($movventa->total, $movventa->moneda);
        $fechaComprobante = $this->fechaEmisionComprobante($movventa);
        $xml_data = collect([
            'fecha' => $fechaComprobante->format('d/m/Y'),
            'nombre_cliente' => $movventa->movimiento->persona ?? 'N/A',
            'nro_doc' => $movventa->movimiento->Persona->personamaestro->nrodoc ?? 'N/A',
            'moneda' => $movventa->moneda,
            'productos' => $movventa->detMovventas->pluck('descripcion')->toArray(),
            'unidades' => $movventa->detMovventas->map(fn($detMovventa) => $detMovventa->unidad->abreviatura ?? 'N/A')->toArray(),
            'cantidades' => $movventa->detMovventas->pluck('cantidad')->toArray(),
            'precios' => $movventa->detMovventas->pluck('monto')->toArray(),
            'importes' => $movventa->detMovventas->pluck('monto_original')->toArray(),
            'tipos_igv' => $movventa->detMovventas->map(fn($detMovventa) => $detMovventa->igv->codigo ?? null)->toArray(),
            'porcentajes_igv' => $movventa->detMovventas->map(fn($detMovventa) => $detMovventa->igv->porcentaje_igv ?? null)->toArray(),
            'total_igv' => $movventa->igv,
            'subtotal' => $movventa->subtotal,
            'formapago' => $formaPagoComprobante,
            'fecha_cuotas' => collect($cuotasComprobante)->pluck('fecha')->all(),
            'monto_cuotas' => collect($cuotasComprobante)->pluck('monto')->all(),
            'monto_neto_pendiente' => $montoNetoPendienteImp,

            'total_monto' => $movventa->total,
            'moneda_simbolo' => $movventa->moneda == 'USD' ? '$' : 'S/',
            'textoQR' => $this->generarTextoQR($movventa, $fechaComprobante),
            'alojamiento' => $alojamiento, // Información sobre el alojamiento
        ]);

        // Datos generales del comprobante
        $dataGeneral = (object) [
            'tipo_documento' => $comprobante->tipo_documento,
            'nombre_documento' => $comprobante->nombre_documento,
            'observacion' => $movventa->movimiento->comentario,
            'numero_comprobante' => $numero_comprobante,
            'fecha' => $fechaComprobante->format('d/m/Y'),
            'cliente_nombre' => $movventa->movimiento->persona ?? 'N/A',
            'cliente_direccion' => $movventa->movimiento->Persona->direccion ?? 'N/A',
            'cliente_nro_doc' => $movventa->movimiento->Persona->personamaestro->nrodoc ?? 'N/A',
            'tipo_pago' => $formaPagoComprobante,

            'monedaSimbolo' => $movventa->moneda == 'USD' ? '$' : 'S/',
            'importe_gravado' => $movventa->subtotal ?? 0,
            'igv' => $movventa->igv ?? 0,
            'importe_exonerado' => $movventa->total_exonerado ?? 0,
            'importe_inafecto' => $movventa->total_inafecto ?? 0,
            'total' => $movventa->total,
            'numero_a_texto' => $numero_a_texto,
            'textoQR' => $this->generarTextoQR($movventa, $fechaComprobante),
            'cuotas' => $cuotasComprobante,
            'monto_neto_pendiente' => $montoNetoPendienteImp,
            'ruc' => $sucursal->ruc,
            'razonsocial' => $sucursal->razonsocial,
            'direccion' => $sucursal->direccion,
            'alojamiento' => $alojamiento, // Información sobre el alojamiento



        ];

        // Detalles del comprobante
        $dataDetalles = $movventa->detMovventas->map(function ($detalle) {
            return [
                'descripcion' => $detalle->producto->nombre ?? $detalle->descripcion,
                'cantidad' => $detalle->cantidad,
                'unidad' => $detalle->unidad->abreviatura ?? 'N/A',
                'monto' => $detalle->monto,
                'subtotal' => $detalle->monto,
            ];
        })->toArray();

        // Calcular la altura en función del contenido
        $contenidoHeight = 600 + (count($dataDetalles) * 20); // Estimar que cada línea de detalle añade 20 puntos

        // Establecer el tamaño de papel de manera dinámica
        $pdf = Pdf::loadView('movventa.comprobante_ticket', 
        compact('dataGeneral', 'dataDetalles', 'numero_comprobante', 'alojamiento', 'sucursal', 'xml_data', 'movventa'))
          ->setOption('margin-top', 2)
                ->setOption('margin-bottom', 8)
                ->setOption('margin-left', 2)
                ->setOption('margin-right', 2)->setOption(
            'footer-html',
            view('movcaja.pdf_footer')->render()
        );

        // Ajustar el tamaño del papel en función del contenido
        $widthMm = round(200 * 25.4 / 72, 2);
        $heightMm = round($contenidoHeight * 25.4 / 72, 2);
        $pdf->setOption('page-width', $widthMm . 'mm');
        $pdf->setOption('page-height', $heightMm . 'mm');

        // Descargar el archivo PDF
        return $pdf->stream("{$numero_comprobante}.pdf");
    }


    // Método para generar el texto QR
    private function generarTextoQR(Movventum $movventa, ?Carbon $fechaComprobante = null): string
    {
        $fechaComprobante ??= $this->fechaEmisionComprobante($movventa);

        return implode('|', [
            $movventa->serie,
            str_pad($movventa->id, 8, '0', STR_PAD_LEFT),
            $movventa->moneda,
            $movventa->total,
            $fechaComprobante->format('Y-m-d'),
            $movventa->movimiento->cliente->documento ?? '',
            $movventa->movimiento->persona ?? '',
        ]);
    }

    private function fechaEmisionComprobante(Movventum $movventa): Carbon
    {
        $solicitud = Solicitud::query()
            ->where('movimiento_id', $movventa->movimiento_id)
            ->whereIn('tipo_documento', ['B', 'F'])
            ->orderBy('fecha')
            ->first();

        return $this->resolverFechaEmisionComprobante($movventa, $solicitud);
    }

    private function resolverFechaEmisionComprobante(Movventum $movventa, ?Solicitud $solicitud): Carbon
    {
        $fecha = $solicitud?->fecha
            ?? $movventa->movimiento?->fecha
            ?? $movventa->created_at;

        return Carbon::parse($fecha);
    }

    private function formatearFormaPagoComprobante(Movventum $movventa): string
    {
        if ($this->esVentaCredito($movventa)) {
            return 'Credito';
        }

        $detalles = $movventa->movcaja?->detMovcaja;

        if (!$detalles || $detalles->isEmpty()) {
            return 'N/A';
        }

        return $detalles
            ->map(fn (DetMovcaja $detalle) => $this->formatearLineaFormaPagoComprobante($detalle))
            ->implode("\n");
    }

    private function formatearLineaFormaPagoComprobante(DetMovcaja $detalle): string
    {
        $medioDescripcion = $detalle->Mediopago->descripcion ?? $detalle->mediopago ?? 'N/A';
        $esPagado = strcasecmp((string) ($detalle->tipo ?? ''), 'Pagado') === 0;
        $condicion = $esPagado ? 'Al contado' : 'Al crédito';

        if (strtolower(trim((string) $medioDescripcion)) !== 'billetera digital') {
            return $medioDescripcion;
        }

        $billetera = $detalle->billeteradigital->descripcion
            ?? (is_string($detalle->billeteradigital) ? $detalle->billeteradigital : null)
            ?? '';

        $monto = number_format((float) $detalle->monto, 2, '.', '');

        if ($billetera !== '') {
            return "Billetera digital ({$billetera}: {$monto}) {$condicion}";
        }

        return "Billetera digital ({$monto}) {$condicion}";
    }

    private function esVentaCredito(Movventum $movventa): bool
    {
        $detalles = $movventa->movcaja?->detMovcaja;

        if ($detalles && $detalles->contains(fn (DetMovcaja $detalle) => strcasecmp((string) $detalle->tipo, 'Deuda') === 0)) {
            return true;
        }

        return strcasecmp((string) $movventa->tipo_pago, 'CREDITO') === 0;
    }

    private function obtenerCuotasComprobante(Movventum $movventa): array
    {
        $detalles = $movventa->movcaja?->detMovcaja;

        $cuotas = [];
        if ($detalles) {
            $cuotas = $detalles
                ->filter(fn (DetMovcaja $detalle) => strcasecmp((string) $detalle->tipo, 'Deuda') === 0)
                ->values()
                ->map(function (DetMovcaja $detalle) {
                    return [
                        'fecha' => $detalle->fechavencimiento
                            ? Carbon::parse($detalle->fechavencimiento)->toDateString()
                            : today()->toDateString(),
                        'monto' => (float) $detalle->monto,
                    ];
                })
                ->all();
        }

        $porcentajeDetraccion = floatval($movventa->porcentaje_detraccion ?? 0);
        $tieneDetraccion = ($movventa->detraccion == 'S' || $porcentajeDetraccion > 0);

        if ($this->esVentaCredito($movventa) && $tieneDetraccion && $porcentajeDetraccion > 0) {
            $montoDetraccion = round(($movventa->total * $porcentajeDetraccion) / 100, 2);
            if (empty($cuotas)) {
                $fechaComp = \Carbon\Carbon::parse($movventa->movimiento->fecha ?? $movventa->created_at)->toDateString();
                $cuotas = [
                    [
                        'fecha' => $fechaComp,
                        'monto' => (float) $movventa->total,
                    ]
                ];
            }
            $cuotas = $this->aplicarDescuentoDetraccionACuotas($cuotas, $montoDetraccion, (float) $movventa->total);
        }

        return $cuotas;
    }

    private function aplicarDescuentoDetraccionACuotas(array $cuotas, float $montoDetraccion, float $totalVenta): array
    {
        if ($montoDetraccion <= 0 || empty($cuotas)) {
            return $cuotas;
        }

        $sumCuotas = array_sum(array_map(fn($c) => floatval($c['monto'] ?? 0), $cuotas));
        if ($sumCuotas <= 0) {
            return $cuotas;
        }

        $montoNetoObjetivo = max(0, round($sumCuotas - $montoDetraccion, 2));
        $cuotasAjustadas = [];
        $acumulado = 0.0;
        $count = count($cuotas);

        foreach ($cuotas as $index => $cuota) {
            $montoOriginal = floatval($cuota['monto'] ?? 0);
            if ($index === $count - 1) {
                $nuevoMonto = max(0, round($montoNetoObjetivo - $acumulado, 2));
            } else {
                $proporcion = $montoOriginal / $sumCuotas;
                $nuevoMonto = max(0, round($montoOriginal - ($montoDetraccion * $proporcion), 2));
                $acumulado += $nuevoMonto;
            }

            $cuotaAjustada = $cuota;
            $cuotaAjustada['monto'] = $nuevoMonto;
            $cuotasAjustadas[] = $cuotaAjustada;
        }

        return $cuotasAjustadas;
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

    public function generarPDFVentas($tipo, $turno, $fecha_inicio = null, $fecha_fin = null)
    {
        set_time_limit(600);
        ini_set('memory_limit', '2048M');

        $sucursal_id = Auth::user()->persona->sucursal_id;
        $turnofilter = RelTurnocaja::find($turno);
        $sucursal = Sucursal::findOrFail(Auth::user()->persona->sucursal_id);
        // 1️⃣ Obtener cajas
        $cajas = Caja::where('sucursal_id', $sucursal_id)
            ->orderBy('situacion', 'ASC')
            ->get();

        // 2️⃣ Construir query Movcaja igual que filter()
        $movimientoQuery = Movcaja::with(['detMovcaja.Mediopago', 'conceptopago'])
            ->where('sucursal_id', $sucursal_id)
            ->whereHas('conceptopago', function ($query) {
                $query->where('descripcion', '!=', 'Cierre de caja');
            });

        // Filtro turno
        if ($turnofilter) {
            $movimientoQuery->where('id', '>=', $turnofilter->movcajainicio_id);

            if ($turnofilter->movcajafin_id) {
                $movimientoQuery->where('id', '<=', $turnofilter->movcajafin_id);
            }
        }

        // Filtro tipo documento
        if ($tipo != 0) {
            $movimientoQuery->whereHas('movventa.movimiento', function ($query) use ($tipo) {
                $query->where('tipodocumento_id', $tipo);
            });
        }

        // 3️⃣ Obtener movcaja
        $movimientos = $movimientoQuery->orderBy('created_at', 'asc')->get();

        // 4️⃣ Obtener movimiento_id de movcaja
        $movimientoCajaIds = $movimientos->pluck('movimiento_id')->toArray();

        // 5️⃣ Obtener movimiento padres (movventa)
        $movimientoVentaIds = Movimiento::whereIn('id', $movimientoCajaIds)
            ->whereNotNull('movimiento_id')
            ->pluck('movimiento_id')
            ->unique()
            ->toArray();

        // 6️⃣ Unificar movimientos relacionados (igual que filter)
        $movimientosRelacionados = array_unique(array_merge($movimientoCajaIds, $movimientoVentaIds));

        // 7️⃣ Obtener ventas (Movventum) con filtros de fecha
        if (!$fecha_inicio && !$fecha_fin) {
            $movventas = Movventum::whereIn('movimiento_id', $movimientosRelacionados)
                ->with(['detMovventas', 'sucursal', 'movimiento'])
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $movventas = Movventum::whereIn('movimiento_id', $movimientosRelacionados)
                ->with(['detMovventas', 'sucursal', 'movimiento'])
                ->whereHas('movimiento', function ($query) use ($fecha_inicio, $fecha_fin) {
                    $query->whereRaw("DATE(fecha) BETWEEN ? AND ?", [$fecha_inicio, $fecha_fin]);
                })
                ->orderBy('id', 'desc')
                ->get();
        }

        // 8️⃣ Calcular total vendido
        $totalVendido = $movventas->sum('total');

        // 9️⃣ Generar PDF
        $pdf = Pdf::loadView('movventa.reportepdf', compact(
            'movventas',
            'sucursal_id',
            'fecha_inicio',
            'sucursal',
            'fecha_fin',
            'totalVendido'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream("REPORTE.pdf");
    }


    public function generarExcelVentas($tipo, $turno, $fecha_inicio = null, $fecha_fin = null)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::findOrFail($persona->sucursal_id);
        $turnofilter = RelTurnocaja::find($turno);

        if (!$fecha_inicio && !$fecha_fin) {
            $fecha_inicio = now()->startOfMonth()->toDateString();
            $fecha_fin = now()->toDateString();
        }

        $movimientoQuery = Movcaja::with(['detMovcaja.Mediopago', 'conceptopago'])
            ->where('sucursal_id', $sucursal->id)
            ->whereHas('movimiento', function ($query) {
                $query->where('situacion', 'A');
            })
            ->whereHas('conceptopago', function ($query) {
                $query->where('descripcion', '!=', 'Cierre de caja');
            });

        if ($turnofilter) {
            $movimientoQuery->where('id', '>=', $turnofilter->movcajainicio_id);
            if ($turnofilter->movcajafin_id) {
                $movimientoQuery->where('id', '<=', $turnofilter->movcajafin_id);
            }
        }

        if ($tipo != 0) {
            $movimientoQuery->whereHas('movimiento', function ($query) use ($tipo) {
                $query->where('tipodocumento_id', $tipo);
            });
        }

        $movimientos = $movimientoQuery->get();
        $movimientoIds = $movimientos->pluck('movimiento_id')->toArray();

        $ventas = Movventum::whereIn('movimiento_id', $movimientoIds)
            ->with(['detMovventas', 'sucursal', 'movimiento'])
            ->whereHas('movimiento', function ($query) use ($fecha_inicio, $fecha_fin) {
                $query->whereRaw("DATE(fecha) BETWEEN ? AND ?", [$fecha_inicio, $fecha_fin]);
            })
            ->orderBy('id', 'desc')

            ->get();

        return Excel::download(new VentasExport($ventas, $fecha_inicio, $fecha_fin), 'REPORTE_VENTAS.xlsx');
    }


    public function convertirAComprobante($id, $personaCreadaId = null)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);

        $movventa = Movventum::with(['detMovventas', 'movimiento', 'movimiento.persona', 'movimiento.Persona.personamaestro'])->findOrFail($id);
        $personaCreada = $personaCreadaId ? Persona::with('personamaestro')->find($personaCreadaId) : null;

        return view('movventa.convertir', compact('id', 'movventa', 'sucursal', 'personaCreada'));
    }



    public function generarCodigoAprobacion(Request $request)
    {
        // Validación
        $correoSanitizado = $this->normalizeCorreoAdmin($request->input('correo'));
        $request->merge(['correo' => $correoSanitizado]);

        $request->validate([
            'telefonos' => 'nullable|string', // Puede ser un array o vacío
            'telefonos.*' => 'nullable|numeric',
            'correo' => 'nullable|email',
            'send_type' => 'required|string',
            'dcto_porcentaje' => 'nullable|numeric',
            'comentario' => 'nullable|string',
            'operation_type' => 'nullable|string',
            'movventa_id' => 'nullable|integer',
        ]);

        $sendType = $request->input('send_type');
        $telefonos = $request->input('telefonos', []);  // Aquí recibimos el teléfono como array
        $correoRaw = $request->input('correo');
        $correo = $this->normalizeCorreoAdmin($correoRaw) ?: Auth::user()->email;
        $codigo = rand(100000, 999999); // Código 6 dígitos
        $dctoPorcentaje = $request->input('dcto_porcentaje', 0);
        $comentario = $request->input('comentario', '');
        $dctoTotalMonto = $request->input('dcto_total_monto', 0);
        $habitacionNumero = $request->input('habitacion_numero', '');
        $operationType = $request->input('operation_type', 'descuento_venta');
        $movventaId = $request->input('movventa_id');
        $movventa = null;
        $comprobante = '';

        if ($operationType === 'anulacion_venta' && !empty($movventaId)) {
            $movventa = Movventum::with(['movimiento.tipodocumento'])->find($movventaId);
            if ($movventa) {
                $comprobante = trim(($movventa->serie ?? '') . '-' . ($movventa->movimiento->numero ?? '') . '-' . ($movventa->anio ?? ''));
            }
        }

        // Si el teléfono está en formato string (cuando solo hay uno), lo convertimos a array
        if (is_string($telefonos)) {
            $decoded = json_decode($telefonos, true);
            $telefonos = is_array($decoded) ? $decoded : [$telefonos];
        } elseif (!is_array($telefonos)) {
            $telefonos = $telefonos === null ? [] : [(string) $telefonos];
        }

        // Validamos que al menos haya un teléfono
        if ($sendType == 'whatsapp' && !empty($telefonos)) {
            $resultados = [];
            foreach ($telefonos as $telefono) {
                $res = $this->enviarWhatsApp(
                    $codigo,
                    $telefono,
                    $dctoPorcentaje,
                    $comentario,
                    $habitacionNumero,
                    $dctoTotalMonto,
                    $operationType,
                    $movventa,
                    $comprobante
                );
                $resultados[] = [
                    'telefono' => $telefono,
                    'respuesta' => $res->original ?? $res, // dependiendo si es JsonResponse o array
                ];
            }
            return response()->json([
                'success' => true,
                'codigo' => $codigo,
                'message' => 'Códigos enviados por WhatsApp.',
                'detalles' => $resultados,
            ]);
        }

        // Si el tipo es email y se pasó el correo
        if ($sendType == 'email' && $correo) {
            return $this->enviarCorreo(
                $codigo,
                $correo,
                $habitacionNumero,
                $dctoPorcentaje,
                $dctoTotalMonto,
                $comentario,
                $operationType,
                $movventa,
                $comprobante
            );
        }

        return response()->json([
            'success' => false,
            'message' => 'Tipo de envío no válido o falta número de teléfono/correo.',
        ], 400);
    }




    // Función para enviar WhatsApp
    private function enviarWhatsApp($codigo, $telefono, $dctoPorcentaje, $comentario, $habitacionNumero = '', $dctoTotalMonto = 0, $operationType = 'descuento_venta', $movventa = null, $comprobante = '')
    {
        $auth_token = env('META_AUTH_TOKEN');
        $from = "202127742990342"; // ID de tu número WhatsApp Business
        $apiVersion = "v19.0";
        $sucursal = Sucursal::find(Auth::user()->persona->sucursal_id);

        try {
            $client = new Client();

            // Formatear teléfono con prefijo internacional si no lo tiene
            if (!preg_match('/^\+?51/', $telefono)) {
                $telefono = '51' . ltrim($telefono, '0');
            }

            if ($operationType === 'anulacion_venta') {
                $header = [
                    ['type' => 'text', 'text' => '🚫 HotelHub | Anulacion de venta'],
                ];

                $comprobanteTexto = $comprobante ?: ('VENTA-ID ' . ($movventa->id ?? 'N/A'));
                $totalVenta = $movventa ? (float) ($movventa->total ?? 0) : 0;
                $detalleTexto = "Detalles de anulacion: Comprobante {$comprobanteTexto} | Total S/ " . number_format($totalVenta, 2, '.', '');

                $body = [
                    ['type' => 'text', 'text' => $sucursal->nombrecomercial ?? 'HotelHub'],
                    ['type' => 'text', 'text' => Auth::user()->name ?? 'Usuario'],
                    ['type' => 'text', 'text' => (string) $codigo],
                    ['type' => 'text', 'text' => '5 minuto(s)'],
                    ['type' => 'text', 'text' => "Acción: Anulacion | Tipo: Venta | Comentario: {$comentario}"],
                    ['type' => 'text', 'text' => $detalleTexto],
                    ['type' => 'text', 'text' => 'HotelHub - Soluciones inteligentes para negocios'],
                ];
            } else {
                $header = [
                    ['type' => 'text', 'text' => '🚫 HotelHub | Descuento de venta'],
                ];

                $detalleTexto = 'Detalles de descuento: ';
                if ($habitacionNumero) {
                    $detalleTexto .= "Habitación {$habitacionNumero} - ";
                }
                $detalleTexto .= "Descuento S/ " . number_format($dctoTotalMonto, 2, '.', '') . " ({$dctoPorcentaje}%)";

                $body = [
                    ['type' => 'text', 'text' => $sucursal->nombrecomercial ?? 'HotelHub'],
                    ['type' => 'text', 'text' => Auth::user()->name ?? 'Usuario'],
                    ['type' => 'text', 'text' => (string) $codigo],
                    ['type' => 'text', 'text' => '5 minuto(s)'],
                    ['type' => 'text', 'text' => "Acción: Descuento | Tipo: Venta | Descuento: {$dctoPorcentaje}% | Comentario: {$comentario}"],
                    ['type' => 'text', 'text' => $detalleTexto],
                    ['type' => 'text', 'text' => 'HotelHub - Soluciones inteligentes para negocios'],
                ];
            }

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
                return response()->json(['success' => false, 'errors' => 'Mensaje no aceptado por WhatsApp'], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Código enviado exitosamente por WhatsApp.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar código por WhatsApp: ' . $e->getMessage(),
            ], 500);
        }
    }




    // Función para enviar correo
    private function enviarCorreo($codigo, $correo, $habitacionNumero = '', $dctoPorcentaje = 0, $dctoTotalMonto = 0, $comentario = '', $operationType = 'descuento_venta', $movventa = null, $comprobante = '')
    {
        $fileName = 'comprobante.pdf';

        if ($operationType === 'anulacion_venta') {
            $comprobanteTexto = $comprobante ?: ('VENTA-ID ' . ($movventa->id ?? 'N/A'));
            $totalVenta = $movventa ? (float) ($movventa->total ?? 0) : 0;
            $detalle = "Anulacion de venta | Comprobante: {$comprobanteTexto} | Total S/ " . number_format($totalVenta, 2, '.', '');
            $texto = "Código de aprobación: $codigo\nDetalle: $detalle\nComentario: $comentario";
        } else {
            $detalle = '';
            if ($habitacionNumero) {
                $detalle .= "Habitación $habitacionNumero — ";
            }
            $detalle .= "Descuento S/ " . number_format($dctoTotalMonto, 2, '.', '') . " ({$dctoPorcentaje}%)";
            $texto = "Código de aprobación: $codigo\nDetalle: $detalle\nComentario: $comentario";
        }

        $fields = [
            'token' => config('services.comprobantes_mail.token'),
            "to" => [$correo],
            "bcc" => [],
            "cc" => [],
            "subject" => "Código de Aprobación",
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
                        'habitacion' => $habitacionNumero,
                        'comprobante' => $comprobante,
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
                    'message' => 'Error al enviar correo: ' . ($datos->message ?? 'Respuesta inválida del servidor de correo'),
                    'detail' => $body,
                    'detalle' => [
                        'tipo_envio' => 'email',
                        'destinatario' => $correo,
                        'habitacion' => $habitacionNumero,
                        'comprobante' => $comprobante,
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
                    'correo' => $correo,
                    'habitacion' => $habitacionNumero,
                    'comprobante' => $comprobante,
                    'descuento' => [
                        'porcentaje' => $dctoPorcentaje,
                        'total' => $dctoTotalMonto,
                    ],
                    'respuesta_proveedor' => $datos,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'codigo' => null,
                'send_type' => 'email',
                'destinatario' => $correo,
                'message' => 'Ocurrió un error al enviar el correo: ' . $e->getMessage(),
                'detail' => $e->getMessage(),
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
        if (strpos($sanitized, ',') !== false) {
            $sanitized = explode(',', $sanitized)[0];
        }
        try {
            $parsed = json_decode($sanitized, true);
            if (is_array($parsed) && count($parsed)) {
                return trim((string) $parsed[0], "\"' ");
            }
            if (is_string($parsed)) {
                return trim($parsed, "\"' ");
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return trim($sanitized, "\"' ");
    }



    public function verAlojamiento($id)
    {
        try {
            // Obtener la habitación ocupada por ID


            $movventa = Movventum::find($id);
            // Obtener el movimiento actual de la habitación
            $movimientoHabitacion = Movhabitacion::find($movventa->movhabitacion->id);
            $habitacion = Habitacion::with('tipohabitacion')->findOrFail($movimientoHabitacion->habitacion_id);


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


            $huesped = Huesped::where('movhabitacion_id', $movimientoHabitacion->id)->first();
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
    
}
