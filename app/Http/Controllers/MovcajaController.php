<?php

namespace App\Http\Controllers;

use App\Models\Movcaja;
use App\Models\DetMovcaja;
use App\Models\Movimiento;
use App\Models\Caja;
use App\Models\Movreserva;
use App\Models\Turno;
use App\Models\Sucursal;
use App\Models\Conceptopago;
use App\Models\CuentaCobrarpagar;
use App\Http\Requests\MovcajaRequest;
use App\Models\Anulacion;
use App\Models\Banco;
use App\Models\Billeteradigital;
use App\Models\DetMovventum;
use App\Models\DetCuentaCobrarpagar;
use App\Models\HistorialHabitacion;
use App\Models\Kardex;
use App\Models\Mediopago;
use App\Models\Movhabitacion;
use App\Models\Movventum;
use App\Models\Operacione;
use App\Models\Pasarelapago;
use App\Models\Persona;
use App\Models\RelMovcajadinero;
use App\Models\RelParametrosucursal;
use App\Models\RelTurnocaja;
use App\Models\Stockproducto;
use App\Models\Tarjetum;
use App\Models\Tipodocumento;
use App\Models\MovhabitacionHabitacion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Support\Collection;
use App\Services\CuentaCobrarpagarService;

class MovcajaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 🔹 Cargar persona + sucursal en una sola pasada
        $persona = Persona::with('sucursal')->findOrFail($user->persona_id);
        $sucursal = $persona->sucursal;

        // 🔹 Cajas
        $cajas = Caja::where('sucursal_id', $sucursal->id)
            ->orderBy('situacion', 'ASC')
            ->get();

        $conceptospagos = Conceptopago::all();
        $tipos = ['Ingreso', 'Egreso'];

        // 🔹 Caja seleccionada
        $caja_id = $request->caja_id ?? optional($cajas->first())->id;
        $cajaSeleccionada = $caja_id
            ? $cajas->firstWhere('id', $caja_id)
            : null;

        // 🔹 Operaciones
        $operaciones = Operacione::where('tabla_id', 61)
            ->where('estado', true)
            ->when(
                $cajaSeleccionada && $cajaSeleccionada->situacion === 'C',
                fn($q) => $q->whereNotIn('accion', [
                    'movcaja.create',
                    'movcaja.ingreso',
                    'movcaja.egreso',
                    'movcaja.cierre'
                ]),
                fn($q) => $q->where('accion', '!=', 'movcaja.apertura')
            )
            ->get();

        // 🔹 Última apertura
        $ultimoAperturaId = Movcaja::where('sucursal_id', $sucursal->id)
            ->where('caja_id', $caja_id)
            ->whereHas(
                'conceptopago',
                fn($q) =>
                $q->where('descripcion', 'Apertura de caja')
            )
            ->latest('id')
            ->value('id');

        // 🔹 Query base reutilizable
        $movcajaBaseQuery = Movcaja::where('sucursal_id', $sucursal->id)
            ->when($caja_id, fn($q) => $q->where('caja_id', $caja_id))
            ->when($ultimoAperturaId, fn($q) => $q->where('id', '>=', $ultimoAperturaId))
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhere('caja', 'like', "%{$search}%")
                        ->orWhere('moneda', 'like', "%{$search}%")
                        ->orWhere('total', 'like', "%{$search}%")
                        ->orWhere('tipocambio', 'like', "%{$search}%")
                        ->orWhereHas('movimiento', function ($m) use ($search) {
                            $m->where('numero', 'like', "%{$search}%")
                                ->orWhere('usuario', 'like', "%{$search}%")
                                ->orWhere('comentario', 'like', "%{$search}%")
                                ->orWhere('situacion', 'like', "%{$search}%")
                                ->orWhereHas(
                                    'tipodocumento.tipomovimiento',
                                    fn($tm) =>
                                    $tm->where('descripcion', 'like', "%{$search}%")
                                )
                                ->orWhereHas(
                                    'Persona.personamaestro',
                                    fn($p) =>
                                    $p->where('nrodoc', 'like', "%{$search}%")
                                        ->orWhere('nombres', 'like', "%{$search}%")
                                        ->orWhere('apellidos', 'like', "%{$search}%")
                                )
                                ->orWhereHas(
                                    'Responsable.personamaestro',
                                    fn($r) =>
                                    $r->where('nrodoc', 'like', "%{$search}%")
                                        ->orWhere('nombres', 'like', "%{$search}%")
                                        ->orWhere('apellidos', 'like', "%{$search}%")
                                );
                        })
                        ->orWhereHas(
                            'conceptopago',
                            fn($c) =>
                            $c->where('descripcion', 'like', "%{$search}%")
                        )
                        ->orWhereHas(
                            'turno',
                            fn($t) =>
                            $t->where('nombre', 'like', "%{$search}%")
                        );
                });
            })
            ->when($request->tipo, function ($q, $tipo) {
                $q->whereHas('movimiento.tipodocumento', function ($td) use ($tipo) {
                    if ($tipo === 'Ingreso') {
                        $td->whereIn('nombre', [
                            'Boleta de venta',
                            'Ticket de venta',
                            'Factura de venta',
                            'Ingreso'
                        ]);
                    } else {
                        $td->where('nombre', $tipo);
                    }
                });
            })
            ->when($request->conceptospago, fn($q, $c) => $q->where('conceptopago_id', $c))
            ->when($request->fechaInicio, fn($q, $f) => $q->whereDate('created_at', '>=', $f))
            ->when($request->fechaFin, fn($q, $f) => $q->whereDate('created_at', '<=', $f));

        // 🔹 Movimientos paginados (con eager loading completo)
        $movcajas = (clone $movcajaBaseQuery)
            ->with([
                'movimiento.tipomovimiento',
                'movimiento.tipodocumento',
                'movimiento.Persona.personamaestro',
                'detMovcaja.Mediopago',
                'conceptopago'
            ])
            ->latest('id')
            ->paginate($request->perPage ?? 10)
            ->appends($request->all());

        // 🔹 Total página (sin N+1)
        $totalPagina = $movcajas->sum(function ($mov) {
            if ($mov->movimiento->situacion !== 'Activado' || $mov->conceptopago_id == 3) {
                return 0;
            }

            return match (true) {
                $mov->movimiento->tipodocumento_id === 7 => $mov->total,
                $mov->movimiento->tipodocumento_id === 8 => -$mov->total,
                $mov->movimiento->tipomovimiento_id === 2 => $mov->total,
                default => 0,
            };
        });

        // 🔹 Movimientos completos (una sola vez)
        $movimientosFiltrados = (clone $movcajaBaseQuery)
            ->with('conceptopago')
            ->get();

        $ingresos = $movimientosFiltrados->where('conceptopago.tipo', 'Ingreso');
        $egresos = $movimientosFiltrados->where('conceptopago.tipo', 'Egreso');

        $hoy = Carbon::today();
        $movimientosHoy = $movimientosFiltrados->whereBetween(
            'created_at',
            [$hoy->startOfDay(), $hoy->endOfDay()]
        );

        $resumenCaja = [
            'totalIngresos' => $ingresos->sum('total'),
            'totalEgresos' => $egresos->sum('total'),
            'saldo' => $ingresos->sum('total') - $egresos->sum('total'),
            'conteoIngresos' => $ingresos->count(),
            'conteoEgresos' => $egresos->count(),
            'movimientosHoy' => $movimientosHoy->count(),
            'montoHoy' => $movimientosHoy->sum(
                fn($m) => ($m->conceptopago?->tipo ?? 'Ingreso') === 'Egreso'
                    ? -$m->total
                    : $m->total
            ),
        ];

        // 🔹 Resumen métodos de pago (1 query)
        $movcajaIds = $movimientosFiltrados->pluck('id');

        $resumenMetodosPago = $movcajaIds->isEmpty()
            ? collect()
            : DetMovcaja::whereIn('movcaja_id', $movcajaIds)
            ->select('mediopago', DB::raw('SUM(monto) total'))
            ->groupBy('mediopago')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // 🔹 Resumen conceptos
        $resumenConceptos = $movimientosFiltrados
            ->groupBy(fn($m) => $m->conceptopago->descripcion ?? 'Sin concepto')
            ->map(fn($items, $concepto) => [
                'concepto' => $concepto,
                'tipo' => $items->first()->conceptopago?->tipo ?? 'N/A',
                'total' => $items->sum('total'),
            ])
            ->sortByDesc('total')
            ->take(5)
            ->values();

        // 🔹 Pendientes alojamiento
        $pendientesAlojamientos = MovhabitacionHabitacion::with('habitacion.piso')
            ->where('situacion', 'A')
            ->whereHas(
                'habitacion',
                fn($q) =>
                $q->where('sucursal_id', $sucursal->id)
            )
            ->get();

        $pendientesAlojamientoCount = $pendientesAlojamientos->count();
        $pendientesAlojamientoTotal = $pendientesAlojamientos->sum('total');

        $pendientesPorPiso = $pendientesAlojamientos
            ->groupBy(fn($i) => $i->habitacion->piso->nombre ?? 'Sin piso')
            ->map(fn($items, $piso) => [
                'piso' => $piso,
                'cantidad' => $items->count(),
                'total' => $items->sum('total'),
            ])
            ->values();

        // 🔹 Reservas hoy
        $reservasHoy = Movreserva::where('sucursal_id', $sucursal->id)
            ->whereDate('fechainicio', $hoy)
            ->whereNull('deleted_at')
            ->get();

        $reservasHoyCount = $reservasHoy->count();
        $reservasHoyTotal = $reservasHoy->sum('total');

        // 🔹 Deudas
        $resumenCuentas = CuentaCobrarpagar::query()
            ->join('movcaja as cuenta_movcaja', 'cuenta_cobrarpagar.movcaja_id', '=', 'cuenta_movcaja.id')
            ->where('cuenta_cobrarpagar.sucursal_id', $sucursal->id)
            ->where('cuenta_cobrarpagar.estado', '!=', 'PAGADO')
            ->whereNull('cuenta_cobrarpagar.deleted_at')
            ->selectRaw("
                cuenta_cobrarpagar.tipo,
                COUNT(*) AS cantidad,
                COALESCE(SUM(GREATEST(cuenta_movcaja.total - cuenta_cobrarpagar.totalpagado, 0)), 0) AS saldo_pendiente
            ")
            ->groupBy('cuenta_cobrarpagar.tipo')
            ->get()
            ->keyBy('tipo');

        $cobrarCount = (int) ($resumenCuentas->get('COBRAR')->cantidad ?? 0);
        $cobrarTotal = (float) ($resumenCuentas->get('COBRAR')->saldo_pendiente ?? 0);
        $deudasCount = (int) ($resumenCuentas->get('PAGAR')->cantidad ?? 0);
        $deudasTotal = (float) ($resumenCuentas->get('PAGAR')->saldo_pendiente ?? 0);

        // 🔹 Stock bajo
        $stockBajo = Stockproducto::select(
            'stockproducto.stockbase',
            'producto.descripcion',
            'rel_productosucursal.stockminimo'
        )
            ->join('producto', 'producto.id', '=', 'stockproducto.producto_id')
            ->join('rel_productosucursal', function ($join) use ($sucursal) {
                $join->on('rel_productosucursal.producto_id', '=', 'stockproducto.producto_id')
                    ->where('rel_productosucursal.sucursal_id', $sucursal->id)
                    ->whereNull('rel_productosucursal.deleted_at');
            })
            ->where('stockproducto.sucursal_id', $sucursal->id)
            ->whereNull('stockproducto.deleted_at')
            ->whereRaw('COALESCE(rel_productosucursal.stockminimo, 0) > 0 AND stockproducto.stockbase <= rel_productosucursal.stockminimo')
            ->orderBy('stockproducto.stockbase')
            ->limit(10)
            ->get();

        return view('movcaja.index', compact(
            'movcajas',
            'conceptospagos',
            'tipos',
            'operaciones',
            'cajas',
            'caja_id',
            'totalPagina',
            'cajaSeleccionada',
            'resumenCaja',
            'resumenMetodosPago',
            'resumenConceptos',
            'pendientesAlojamientoCount',
            'pendientesAlojamientoTotal',
            'pendientesPorPiso',
            'reservasHoyCount',
            'reservasHoyTotal',
            'cobrarCount',
            'cobrarTotal',
            'deudasCount',
            'deudasTotal',
            'stockBajo'
        ));
    }

    public function filterByCaja($cajaId)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::findOrFail($persona->sucursal_id);

        // Obtener el ID del último movimiento de apertura
        $ultimoAperturaId = Movcaja::where('movcaja.sucursal_id', $sucursal->id)
            ->where('movcaja.caja_id', $cajaId)
            ->whereHas('conceptopago', function ($query) {
                $query->where('descripcion', 'Apertura de caja');
            })
            ->orderBy('id', 'desc')
            ->value('id');

        // Filtrar los movimientos desde el último movimiento de apertura
        $movcajas = Movcaja::where('movcaja.sucursal_id', $sucursal->id)
            ->where('movcaja.caja_id', $cajaId)
            ->when($ultimoAperturaId, function ($query) use ($ultimoAperturaId) {
                return $query->where('movcaja.id', '>=', $ultimoAperturaId);
            })
            ->with([
                'movimiento.tipomovimiento',
                'detMovcaja.Mediopago',
                'conceptopago'
            ])
            ->orderBy('movcaja.id', 'desc')
            ->paginate(10);


        // Calcular el total general considerando solo movimientos activados
        $totalGeneral = Movcaja::join('movimiento', 'movimiento.id', '=', 'movcaja.movimiento_id')
            ->where('movcaja.sucursal_id', $sucursal->id)
            ->when($cajaId, function ($query, $cajaId) {
                return $query->where('movcaja.caja_id', $cajaId);
            })
            ->where('movimiento.situacion', 'A')
            ->where('movcaja.conceptopago_id', '!=', 3) // Excluir conceptopago_id = 3
            ->selectRaw("
            SUM(CASE 
                WHEN movimiento.tipodocumento_id = 7 THEN movcaja.total -- Ingresos
                WHEN movimiento.tipodocumento_id = 8 THEN -movcaja.total -- Egresos
                ELSE 0 
            END) as total_balance
        ")
            ->value('total_balance');

        // Total de la página actual
        $totalPagina = $movcajas->filter(function ($movcaja) {
            return $movcaja->conceptopago_id != 3; // Excluir conceptopago_id = 3
        })->map(function ($movcaja) {
            $tipoDocumentoId = $movcaja->movimiento->tipodocumento_id;
            return $tipoDocumentoId === 7 ? $movcaja->total : ($tipoDocumentoId === 8 ? -$movcaja->total : 0);
        })->sum();


        $cajas = Caja::where('sucursal_id', $sucursal->id)->get();
        $caja_id = $cajaId;
        // Obtener la caja seleccionada
        $cajaSeleccionada = Caja::find($cajaId);

        // Filtrar operaciones según la situación de la caja
        if ($cajaSeleccionada->situacion === 'C') {
            $operaciones = Operacione::where('tabla_id', 61)
                ->where('estado', true)
                ->where('accion', '!=', 'movcaja.create')
                ->where('accion', '!=', 'movcaja.ingreso')
                ->where('accion', '!=', 'movcaja.egreso')
                ->where('accion', '!=', 'movcaja.cierre')
                ->get();
        } else {
            $operaciones = Operacione::where('tabla_id', 61)
                ->where('estado', true)
                ->where('accion', '!=', 'movcaja.apertura')
                ->get();
        }


        return view('movcaja.index', compact('movcajas', 'operaciones', 'cajas', 'cajaSeleccionada', 'caja_id', 'totalGeneral', 'totalPagina'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create($cajaId, $operacion)
    {
        try {
            $movcaja = new Movcaja();
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);

            $sucursal = Sucursal::find($persona->sucursal_id);

            $personas = Persona::where('sucursal_id', $sucursal->id)->get();
            $recepcionistasmodel = DB::table('persona as p')
                ->select('p.id')
                ->distinct()
                ->leftJoin('users as u', 'u.persona_id', '=', 'p.id')
                ->leftJoin('rolpersona as r', 'r.persona_id', '=', 'p.id')
                ->where('p.sucursal_id', $sucursal->id)
                ->whereNull('p.deleted_at')
                ->where(function ($query) {
                    $query->whereIn('u.perfil_id', [12, 10, 11, 19])
                        ->whereIn('r.rol_id', [1, 2]);
                })
                ->pluck('p.id'); // 🔹 obtenemos solo los IDs

            // Ahora obtenemos los modelos Persona completos
            $responsables = Persona::whereIn('id', $recepcionistasmodel)->get();

            $cajas = Caja::where('id', $cajaId)->get();
            $turnos = Turno::where('sucursal_id', $sucursal->id)->get();
            $medio_pagos = Mediopago::where('situacion', true)->get();
            $pos = Pasarelapago::where('situacion', true)->get(); // Tabla para P.O.S.
            $tarjetas = Tarjetum::where('situacion', true)->get(); // Tabla para Tarjetas
            $bancos = Banco::where('situacion', true)->get(); // Tabla para Bancos
            $billeteras = Billeteradigital::where('situacion', true)->get(); // Tabla para Billeteras Digitales


            $conceptos_pago = Conceptopago::whereNotIn('id', [2, 3, 4])
                ->when($operacion === 'INGRESO', function ($query) {
                    return $query->where('tipo', 'I');
                })
                ->when($operacion === 'EGRESO', function ($query) {
                    return $query->where('tipo', 'E');
                })
                ->when(!in_array($operacion, ['INGRESO', 'EGRESO']), function ($query) {
                    // Por defecto mostrar ingresos
                    return $query->where('tipo', 'I');
                })
                ->get();

            if ($conceptos_pago->isEmpty()) {
                return redirect()->route('movcaja.index')->withErrors('No hay conceptos de pago disponibles.');
            }

            $sucursal_id = Auth::user()->persona->sucursal_id;
            $tipos_documento = Tipodocumento::whereIn('id', [7, 8])->get();

            // Verificar si hay un turno anterior y obtener detalles de movimientos
            $detallesMovimientos = [];
            if ($operacion === 'APERTURAR') {
                $ultimoTurno = RelTurnocaja::where('situacion', 'A')
                    ->whereHas('movcajafin', function ($query) use ($cajaId) {
                        $query->where('caja_id', $cajaId);
                    })
                    ->latest('fechafin')
                    ->first();

                if ($ultimoTurno && $ultimoTurno->movcajafin) {
                    $detallesMovimientos[] = [
                        'tipo' => 'Pagado',
                        'monto' => $ultimoTurno->movcajafin->total,
                        'fechavencimiento' => null,
                        'mediopago_id' => 1,
                        'mediopago' => 'Efectivo',
                        'numero' => '',
                        'comentario' => 'Monto inicial basado en el cierre anterior',

                    ];
                }
            }
            $sucursal_id = Auth::user()->persona->sucursal_id;
            $tipos_documento = Tipodocumento::whereIn('id', [7, 8])->get();

            // Verificar si hay un turno anterior
            $montoCierreAnterior = null;
            if ($operacion === 'APERTURAR') {
                $ultimoTurno = RelTurnocaja::where('situacion', 'A')
                    ->whereHas('movcajafin', function ($query) use ($cajaId) {
                        $query->where('caja_id', $cajaId);
                    })
                    ->latest('fechafin')
                    ->first();

                if ($ultimoTurno && $ultimoTurno->movcajafin) {
                    $montoCierreAnterior = $ultimoTurno->movcajafin->total;
                }
            }
            //dd($montoCierreAnterior);

            return view('movcaja.create', compact(
                'cajas',
                'conceptos_pago',
                'sucursal_id',
                'movcaja',
                'tipos_documento',
                'responsables',
                'medio_pagos',
                'operacion',
                'turnos',
                'montoCierreAnterior',
                'pos',
                'persona',
                'tarjetas',
                'billeteras',
                'bancos',
                'detallesMovimientos' // Pasar los detalles del movimiento con el monto de apertura si existe
            ));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function cerrarCaja($cajaId)
    {
        try {
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal = Sucursal::findOrFail($persona->sucursal_id);
            $caja = Caja::whereKey($cajaId)->where('sucursal_id', $sucursal->id)->firstOrFail();
            $cashMethodId = Mediopago::whereRaw('LOWER(descripcion) = ?', ['efectivo'])->value('id') ?? 1;

            // Obtener el último turno activo o cerrado para la caja
            $turnoCaja = RelTurnocaja::where('situacion', 'A') // Turno activo
                ->where('sucursal_id', $sucursal->id)
                ->whereHas('movcajainicio', function ($query) use ($cajaId) {
                    $query->where('caja_id', $cajaId);
                })
                ->latest('fechainicio') // Último turno
                ->first();

            if (!$turnoCaja) {
                return response()->json([
                    'success' => false,
                    'errors' => 'No se encontró un turno activo o cerrado para esta caja.',
                ], 404);
            }

            // Obtener movimientos con detalles
            $movimientos = Movcaja::with(['detMovcaja.Mediopago', 'conceptopago', 'movventa', 'movimiento'])
                ->where('id', '>=', $turnoCaja->movcajainicio_id) // Desde el inicio del turno
                ->where('caja_id', $cajaId)
                ->where('sucursal_id', $sucursal->id)
                ->whereHas('movimiento', function ($query) {
                    $query->where('situacion', 'A'); // Filtrar movimientos con situación 'A'
                })
                ->orderBy('created_at', 'asc')
                ->get();





            $detallesAgrupados = $movimientos->flatMap->detMovcaja->where('tipo', 'Pagado')->groupBy(function ($detalle) {
                $tipoConcepto = $detalle->movcaja->conceptopago->tipo;
                $conceptoDescripcion = $detalle->movcaja->conceptopago->descripcion;

                // Construir el detalle completo para agrupación
                $detalles = [];
                if (!empty($detalle->tarjeta_id)) {
                    $detalles[] = $detalle->tarjeta;
                }
                if (!empty($detalle->banco_id)) {
                    $detalles[] = $detalle->banco;
                }
                if (!empty($detalle->billeteradigital_id)) {
                    $detalles[] = $detalle->billeteradigital;
                }
                if (!empty($detalle->pos_id)) {
                    $detalles[] = $detalle->pos;
                }
                $detalleUnificado = implode(' | ', $detalles);

                // Incluir el detalle unificado en la clave de agrupación
                if ($conceptoDescripcion === 'Apertura de caja') {
                    return "{$detalle->Mediopago->descripcion}|Apertura de caja|{$detalle->tipo}|{$detalleUnificado}";
                }

                if ($conceptoDescripcion === 'Pago de cliente') {
                    return "{$detalle->Mediopago->descripcion}|Pago de cliente|{$detalle->tipo}|{$detalleUnificado}";
                }

                return "{$detalle->Mediopago->descripcion}|" . ($tipoConcepto === 'Ingreso' ? 'Otros ingresos' : 'Egreso') . "|{$detalle->tipo}|{$detalleUnificado}";
            })->map(function ($items, $key) {
                // Construir los detalles unificados para la agrupación
                $detalles = $items->map(function ($detalle) {
                    $detalles = [];
                    if (!empty($detalle->tarjeta_id)) {
                        $detalles[] = $detalle->tarjeta;
                    }
                    if (!empty($detalle->banco_id)) {
                        $detalles[] = $detalle->banco;
                    }
                    if (!empty($detalle->billeteradigital_id)) {
                        $detalles[] = $detalle->billeteradigital;
                    }
                    if (!empty($detalle->pos_id)) {
                        $detalles[] = $detalle->pos;
                    }

                    return $detalles;
                })->flatten()->unique()->implode(' | ');

                return [
                    'clave_agrupacion' => $key,
                    'mediopago' => $items->first()->Mediopago->descripcion,
                    'nota' => $items->first()->movcaja->conceptopago->descripcion === 'Apertura de caja'
                        ? 'Apertura de caja'
                        : ($items->first()->movcaja->conceptopago->tipo === 'Ingreso'
                            ? ($items->first()->movcaja->conceptopago->descripcion === 'Pago de cliente'
                                ? 'Pago de cliente'
                                : 'Otros ingresos')
                            : 'Egresos'),
                    'tipo' => $items->first()->tipo,
                    'monto' => $items->sum('monto'),
                    'movcaja_ids' => $items->pluck('movcaja_id')->toArray(),
                    'detalles' => $detalles, // Agregar el campo "detalles" unificado
                ];
            });


            // Calcular totales usando DetMovcaja
            // Calcular totales usando DetMovcaja con tipo 'Pagado'
            $totales = [
                'apertura' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->conceptopago && $movcaja->conceptopago->descripcion === 'Apertura de caja';
                })->flatMap->detMovcaja->where('tipo', 'Pagado')->sum('monto'),

                'efectivo' => $movimientos->flatMap->detMovcaja->where('mediopago_id', $cashMethodId)->where('tipo', 'Pagado')->sum('monto'),
                'egresos' => $movimientos->where('movimiento.tipodocumento_id', 8)->flatMap->detMovcaja->where('tipo', 'Pagado')->sum('monto'),
                'egresos_efectivo' => $movimientos->where('movimiento.tipodocumento_id', 8)
                    ->flatMap->detMovcaja->where('mediopago_id', $cashMethodId)->where('tipo', 'Pagado')->sum('monto'),

                'ingresos' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->movimiento->movimiento_id == null && $movcaja->movimiento->tipodocumento_id == 7 && $movcaja->conceptopago->descripcion !== 'Apertura de caja';
                })->flatMap->detMovcaja->where('tipo', 'Pagado')->sum('monto'),

                'ventas' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->movventa && $movcaja->conceptopago_id == 4;
                })->flatMap->detMovcaja->where('tipo', 'Pagado')->sum('monto'),

                'ventas_efectivo' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->movventa && $movcaja->conceptopago_id == 4;
                })->flatMap->detMovcaja->where('mediopago_id', $cashMethodId)->where('tipo', 'Pagado')->sum('monto'),

                'tarjeta' => $movimientos->flatMap->detMovcaja->where('mediopago_id', 2)->where('tipo', 'Pagado')->sum('monto'),

                'ingresos_efectivo' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->movimiento->movimiento_id == null && $movcaja->movimiento->tipodocumento_id == 7;
                })->flatMap->detMovcaja->where('mediopago_id', $cashMethodId)->where('tipo', 'Pagado')->sum('monto'),

                // Calcular efectivo en caja
                'efectivo_caja' =>
                $movimientos->filter(function ($movcaja) {
                    return $movcaja->movimiento->tipodocumento_id == 7;
                })->flatMap->detMovcaja->where('mediopago_id', $cashMethodId)->where('tipo', 'Pagado')->sum('monto')
                    - $movimientos->filter(function ($movcaja) {
                        return $movcaja->movimiento->tipodocumento_id == 8;
                    })->flatMap->detMovcaja->where('mediopago_id', $cashMethodId)->where('tipo', 'Pagado')->sum('monto'),

                'total' =>
                $movimientos->filter(fn($movcaja) => $movcaja->movimiento->tipodocumento_id == 7)
                    ->flatMap->detMovcaja->where('mediopago_id', $cashMethodId)->where('tipo', 'Pagado')->sum('monto')
                    - $movimientos->filter(fn($movcaja) => $movcaja->movimiento->tipodocumento_id == 8)
                    ->flatMap->detMovcaja->where('mediopago_id', $cashMethodId)->where('tipo', 'Pagado')->sum('monto'),
            ];


            // Calcular total como la diferencia entre ingresos y egresos en efectivo
            //  dd($totales);
            // Preparar otros datos necesarios para la vista
            $responsables = Persona::whereHas('rolpersonas', function ($query) {
                $query->where('rol_id', 1); // Ajustar ID del rol según corresponda
            })->where('sucursal_id', $sucursal->id)->get();

            $medio_pagos = Mediopago::where('situacion', true)->get();
            $conceptos_pago = Conceptopago::whereNotIn('id', [2])->get(); // Excluir el concepto 'Apertura'
            $cajas = collect([$caja]);
            // Obtener lista de monedas y billetes desde la BD
            $monedas = \App\Models\Dinero::where('tipo', 'MONEDA')
                ->where('situacion', 'E')
                ->orderBy('orden')
                ->get();

            $billetes = \App\Models\Dinero::where('tipo', 'BILLETE')
                ->where('situacion', 'E')
                ->orderBy('orden')
                ->get();

            return view('movcaja.cerrarCaja', compact(
                'movimientos',
                'totales',
                'responsables',
                'medio_pagos',
                'conceptos_pago',
                'cajas',
                'cajaId',
                'detallesAgrupados',
                'sucursal',
                'monedas',
                'billetes'
            ));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeCierre(Request $request, $cajaId)
    {
        $userBranch = (int) Auth::user()->persona?->sucursal_id;
        abort_unless($userBranch && (int) $cajaId > 0 && (int) $request->input('caja_id') === (int) $cajaId, 403, 'La caja solicitada no corresponde al usuario.');
        DB::beginTransaction();

        try {
            // Validación de los datos
            $validator = Validator::make($request->all(), [
                'responsable_id' => 'required|exists:persona,id',
                'moneda' => 'required|string|in:PEN,USD',
                'total' => 'required|numeric|min:0',
                'efectivo_real' => 'nullable|numeric|min:0',
                'dinero_id' => 'nullable|array',
                'dinero_id.*' => 'integer|exists:dinero,id',
                'dinero_cantidad' => 'nullable|array',
                'dinero_cantidad.*' => 'nullable|numeric|min:0',
                'caja_id' => 'required|exists:caja,id',
                'detalles_movimientos_json' => 'required|json',
                'notas' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Obtener datos generales
            $usuario = Auth::user();
            $persona_sucursal = Persona::find($usuario->persona_id);
            $sucursal = Sucursal::find($persona_sucursal->sucursal_id);
            $responsable = Persona::find($request->responsable_id);
            $caja = Caja::whereKey($cajaId)->where('sucursal_id', $userBranch)->lockForUpdate()->firstOrFail();
            $turnoCaja = RelTurnocaja::conCajaActiva($cajaId)->where('sucursal_id', $userBranch)->lockForUpdate()->firstOrFail();
            if (!$responsable || (int) $responsable->sucursal_id !== $userBranch) {
                DB::rollBack();
                return response()->json(['success' => false, 'errors' => 'El responsable no pertenece a tu sucursal.'], 422);
            }

            // El total esperado se recalcula desde los movimientos pagados del turno.
            // Nunca confiar en el campo readonly enviado por el navegador.
            $cashMethodId = Mediopago::whereRaw('LOWER(descripcion) = ?', ['efectivo'])->value('id') ?? 1;
            $movimientosTurno = Movcaja::with(['movimiento', 'detMovcaja'])
                ->where('sucursal_id', $userBranch)
                ->where('caja_id', $cajaId)
                ->where('id', '>=', $turnoCaja->movcajainicio_id)
                ->whereHas('movimiento', fn($q) => $q->where('situacion', 'A'))
                ->get();
            $ingresosEfectivo = $movimientosTurno
                ->filter(fn($mov) => $mov->movimiento && (int) $mov->movimiento->tipodocumento_id === 7)
                ->flatMap->detMovcaja->where('mediopago_id', $cashMethodId)->where('tipo', 'Pagado')->sum('monto');
            $egresosEfectivo = $movimientosTurno
                ->filter(fn($mov) => $mov->movimiento && (int) $mov->movimiento->tipodocumento_id === 8)
                ->flatMap->detMovcaja->where('mediopago_id', $cashMethodId)->where('tipo', 'Pagado')->sum('monto');
            $efectivoEsperado = round((float) $ingresosEfectivo - (float) $egresosEfectivo, 2);

            $dineroIds = $request->input('dinero_id', []);
            $cantidades = $request->input('dinero_cantidad', []);
            $dineroConfig = \App\Models\Dinero::whereIn('id', $dineroIds)
                ->where('situacion', 'E')
                ->get(['id', 'valor'])
                ->keyBy('id');
            $efectivoContado = 0.0;
            foreach ($dineroIds as $i => $dineroId) {
                $cantidad = (float) ($cantidades[$i] ?? 0);
                if ($cantidad > 0 && $dineroConfig->has((int) $dineroId)) {
                    $efectivoContado += (float) $dineroConfig->get((int) $dineroId)->valor * $cantidad;
                }
            }
            $efectivoContado = round($efectivoContado, 2);
            $diferenciaCaja = round($efectivoContado - $efectivoEsperado, 2);

            $persona = $request->persona_id ? Persona::find($request->persona_id) : null;

            // Crear Movimiento de cierre
            $movimiento = Movimiento::create([
                'numero' => $this->generateNumeroMovcaja($sucursal->id, $request->caja_id, 3),
                'fecha' => now(),
                'usuario_id' => $usuario->id,
                'usuario' => $usuario->name,
                'responsable_id' => $responsable->id,
                'responsable' => $responsable->nombres . ' ' . $responsable->apellidos,
                'persona_id' => $request->persona_id,
                'persona' => $persona ? $persona->nombres . ' ' . $persona->apellidos : 'N/A',
                'comentario' => trim(($request->notas ?? '') . ' | Efectivo esperado: ' . number_format($efectivoEsperado, 2, '.', '')
                    . ' | Contado: ' . number_format($efectivoContado, 2, '.', '')
                    . ' | Diferencia: ' . number_format($diferenciaCaja, 2, '.', '')),
                'situacion' => 'A', // Cierre
                'tipomovimiento_id' => 4, // Supongamos 4 es Cierre
                'tipodocumento_id' => 8, // Documento de Cierre
                'sucursal_id' => $sucursal->id,
            ]);

            // Crear Movcaja de cierre
            $movcaja = Movcaja::create([
                'conceptopago_id' => 3, // Supongamos 3 es Cierre
                'moneda' => $request->moneda,
                'tipocambio' => 3.71,
                'total' => $efectivoEsperado,
                'caja_id' => $request->caja_id,
                'caja' => $caja->numero,
                'turno_id' => $turnoCaja->movcajainicio->turno_id,
                'json_turno' => json_encode(Turno::find($turnoCaja->movcajainicio->turno_id)),
                'movimiento_id' => $movimiento->id,
                'sucursal_id' => $sucursal->id,
            ]);

            // Procesar Detalles de Movcaja (DetMovcaja)
            $detalles = json_decode($request->detalles_movimientos_json, true);

            foreach ($detalles as $detalle) {
                // Buscar el mediopago_id correspondiente a la descripción
                $mediopago = Mediopago::where('descripcion', $detalle['mediopago'])->first();

                DetMovcaja::create([
                    'movcaja_id' => $movcaja->id,
                    'tipo' => $detalle['tipo'],
                    'fechavencimiento' => $detalle['fechavencimiento'] ?? null,
                    'fechapago' => now(),
                    'mediopago_id' => $mediopago ? $mediopago->id : null,
                    'mediopago' => $detalle['mediopago'],
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
                    'situacion' => 'A',
                    'sucursal_id' => $sucursal->id,
                ]);
            }

            // Actualizar la situación de la caja a 'Cerrada'
            $caja->update(['situacion' => 'C']);

            // Actualizar el turno con el cierre
            $turnoCaja->update([
                'fechafin' => now(),
                'movcajafin_id' => $movcaja->id,
            ]);

            // Procesar monedas y billetes (rel_movcajadinero)
            $comentarios = $request->input('dinero_comentario', []);

            $dineroValido = \App\Models\Dinero::whereIn('id', $dineroIds)->pluck('id')->all();
            foreach ($dineroIds as $i => $dineroId) {
                // Evitamos guardar filas vacías
                if (!in_array((int) $dineroId, $dineroValido, true) || !isset($cantidades[$i]) || (float) $cantidades[$i] <= 0) {
                    continue;
                }

                RelMovcajadinero::create([
                    'dinero_id' => $dineroId,
                    'movcaja_id' => $movcaja->id,
                    'cantidad' => $cantidades[$i],
                    'comentario' => $comentarios[$i] ?? '',
                    'sucursal_id' => $sucursal->id,
                ]);
            }


            DB::commit();
            $this->enviarCorreoCierreCaja($cajaId, $turnoCaja);

            return response()->json([
                'success' => true,
                'message' => 'Cierre de caja registrado exitosamente.',
                'redirect_url' => route('movcaja.index'),
                'caja' => 'caja',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    private function resumenTotalesCierre(Collection $movimientos, bool $incluirEfectivoCaja = true): array
    {
        // El movimiento de cierre conserva detalles para el comprobante, pero no es
        // un nuevo ingreso ni egreso y nunca debe volver a afectar el cuadre.
        $transacciones = $movimientos->reject(fn($mov) => (int) $mov->conceptopago_id === 3);
        $cashMethodId = Mediopago::whereRaw('LOWER(descripcion) = ?', ['efectivo'])->value('id') ?? 1;
        $ventas = $transacciones->filter(fn($mov) => $mov->movventa);
        $mediosPago = Mediopago::where('situacion', true)->orderBy('orden')->get();
        $totales = [];

        foreach ($mediosPago as $medio) {
            $totales['Ventas | '.$medio->descripcion] = $ventas->flatMap->detMovcaja
                ->where('mediopago_id', $medio->id)->where('tipo', 'Pagado')->sum('monto');
        }

        $apertura = $transacciones->filter(fn($mov) => $mov->conceptopago?->descripcion === 'Apertura de caja')
            ->flatMap->detMovcaja->where('mediopago_id', $cashMethodId)->where('tipo', 'Pagado')->sum('monto');
        $ingresosEfectivo = $transacciones->filter(fn($mov) => (int) ($mov->movimiento?->tipodocumento_id ?? 0) === 7)
            ->flatMap->detMovcaja->where('mediopago_id', $cashMethodId)->where('tipo', 'Pagado')->sum('monto');
        $egresosEfectivo = $transacciones->filter(fn($mov) => (int) ($mov->movimiento?->tipodocumento_id ?? 0) === 8)
            ->flatMap->detMovcaja->where('mediopago_id', $cashMethodId)->where('tipo', 'Pagado')->sum('monto');
        $otrosIngresos = $transacciones->filter(function ($mov) {
            return (int) ($mov->movimiento?->tipodocumento_id ?? 0) === 7
                && !$mov->movventa
                && $mov->conceptopago?->descripcion !== 'Apertura de caja';
        })->flatMap->detMovcaja->where('mediopago_id', $cashMethodId)->where('tipo', 'Pagado')->sum('monto');
        $efectivoCaja = round((float) $ingresosEfectivo - (float) $egresosEfectivo, 2);

        $totales['Apertura'] = $apertura;
        $totales['Otros ingresos'] = $otrosIngresos;
        $totales['Egresos (gastos)'] = -1 * $egresosEfectivo;
        if ($incluirEfectivoCaja) $totales['efectivo_caja'] = $efectivoCaja;
        $totales['Total sin apertura'] = round($efectivoCaja - (float) $apertura, 2);
        $totales['Total + apertura'] = $efectivoCaja;

        return $totales;
    }

    private function enviarCorreoCierreCaja($cajaId, $turnoCaja)
    {
        // Generar PDF de cierre
        $pdf = app(self::class)->generarPdfCierre($cajaId, true);
        $pdf = mb_convert_encoding($pdf, 'UTF-8', 'ISO-8859-1');

        // Obtener correos configurados
        $sucursalId = Caja::find($cajaId)->sucursal_id;
        $correos = RelParametrosucursal::where('sucursal_id', $sucursalId)
            ->whereHas('parametro', function ($query) {
                $query->where('descripcion', 'E-mail de administración');
            })
            ->value('valor');

        $correos = json_decode($correos, true) ?? [];

        if (empty($correos))
            return;

        // 📩 Info adicional

        $usuario = Auth::user();
        $nombreUsuario = $usuario->name;
        $turno = Turno::find($turnoCaja->movcajainicio->turno_id)->descripcion ?? 'Turno';
        $caja = Caja::find($cajaId)->numero ?? 'Caja';
        $sucursalNombreComercial = Sucursal::find($sucursalId)->nombrecomercial ?? 'Sucursal';

        // Mensaje
        $body = '<img class="responsive-img" width="300" src="https://sistema.hotelhub.com.pe/assets/img/Logo_HotelHub_tr.png" alt="hotelhub"><br/>
        <p>Sr(a). administrador(a) de ' . $sucursalNombreComercial . '. Se le informa que el usuario <strong>' . $nombreUsuario . '</strong> ha realizado su cierre de caja:<br/>
        <strong>Turno:</strong> ' . $turno . '<br/>
        <strong>Caja:</strong> ' . $caja . '</p>';

        $subject = '💰 HotelHub | Cierre de caja';

        $fields = [
            'token' => config('services.comprobantes_mail.token'),
            "to" => $correos,
            "bcc" => [],
            "cc" => [],
            "subject" => $subject,
            "body" => $body,
            "attachments" => [
                [
                    "name_file" => "ARQUEO DE CIERRE.pdf",
                    "content_file" => $pdf,
                    "content_type" => "application/pdf",
                ]
            ],
        ];

        // Enviar correo
        $client = new \GuzzleHttp\Client();
        $response = $client->post("https://facturae-garzasoft.com/facturacion/enviaEmail/envia_correo_json.php", [
            'json' => $fields,
        ]);

        if ($response->getStatusCode() != 200) {
            throw new \Exception('Error al enviar el correo de cierre.');
        }

        $data = json_decode($response->getBody());
        if ($data->code != 0) {
            throw new \Exception('Error al enviar correo: código ' . $data->code);
        }
    }

    public function store(Request $request)
    {
        $userBranch = (int) Auth::user()->persona?->sucursal_id;
        abort_unless($userBranch && (int) $request->input('sucursal_id') === $userBranch, 403, 'La sucursal no corresponde al usuario.');
        DB::beginTransaction();

        try {
            // Validación
            $validator = Validator::make($request->all(), [
                'sucursal_id' => 'required|exists:sucursal,id',
                'conceptopago_id' => 'required|exists:conceptopago,id',
                'moneda' => 'required|string|in:PEN,USD',
                'total' => 'required|numeric|min:0',
                'caja_id' => 'required|exists:caja,id',
                'detalles_movimientos_json' => 'required|json',
                'tipo_documento_visible' => 'required|exists:tipodocumento,id',
                'operacion' => 'required|in:APERTURAR,INGRESO,EGRESO',
                'comentario' => 'nullable|string',
                'persona_id' => 'nullable|exists:persona,id', // Validación de persona_id
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $caja = Caja::whereKey($request->caja_id)->where('sucursal_id', $userBranch)->lockForUpdate()->first();
            if (!$caja) {
                DB::rollBack();
                return response()->json(['success' => false, 'errors' => 'La caja no pertenece a tu sucursal.'], 422);
            }
            if ($request->operacion === 'APERTURAR') {
                $turnoAbierto = RelTurnocaja::conCajaActiva($caja->id)->where('sucursal_id', $userBranch)->exists();
                if ($turnoAbierto) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'La caja ya se encuentra aperturada. Debe cerrarla antes de abrir una nueva.'], 422);
                }
            } else {
                $turnoCajaActivo = RelTurnocaja::conCajaActiva($caja->id)->where('sucursal_id', $userBranch)->exists();
                if (!$turnoCajaActivo) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'La caja debe estar aperturada para registrar ingresos o egresos.'], 422);
                }
            }

            // Datos generales
            $usuario = Auth::user();
            $responsable = $usuario->persona;
            if (!$responsable || (int) $responsable->sucursal_id !== $userBranch) {
                DB::rollBack();
                return response()->json(['success' => false, 'errors' => 'El responsable no pertenece a tu sucursal.'], 422);
            }
            $persona = $request->persona_id ? Persona::find($request->persona_id) : null;
            $tipodoc = Tipodocumento::find($request->tipo_documento_visible);
            $turnoCaja = RelTurnocaja::conCajaActiva($request->caja_id)->first();
            $turnoId = $turnoCaja ? $turnoCaja->movcajainicio->turno_id : $request->turno_id;

            // Crear Movimiento
            $movimiento = Movimiento::create([
                'numero' => $this->generateNumeroMovcaja($request->sucursal_id, $request->caja_id, $request->conceptopago_id),
                'fecha' => now(),
                'usuario_id' => $usuario->id,
                'usuario' => $usuario->name,
                'responsable_id' => $responsable->id,
                'responsable' => $responsable->nombres . ' ' . $responsable->apellidos,
                'persona_id' => $request->persona_id,
                'persona' => $persona ? $persona->nombres . ' ' . $persona->apellidos : 'N/A', // Asegura valor no nulo
                'comentario' => $request->comentario ?? '',
                'situacion' => 'A',
                'tipomovimiento_id' => 4,
                'tipodocumento_id' => $request->tipo_documento_visible,
                'sucursal_id' => $request->sucursal_id,
            ]);

            // Crear Movcaja
            $movcaja = Movcaja::create([
                'conceptopago_id' => $request->conceptopago_id,
                'moneda' => $request->moneda,
                'tipocambio' => 3.71,
                'total' => $request->total,
                'caja_id' => $request->caja_id,
                'caja' => $caja->numero,
                'turno_id' => $turnoId,
                'json_turno' => json_encode(Turno::find($turnoId)->makeHidden(['created_at', 'updated_at', 'deleted_at'])->toArray(), JSON_UNESCAPED_UNICODE),
                'movimiento_id' => $movimiento->id,
                'sucursal_id' => $request->sucursal_id,
            ]);


            // Procesar Detalles de Movcaja
            $detalles = json_decode($request->detalles_movimientos_json, true);
            foreach ($detalles as $detalle) {
                $tipoDetalle = $detalle['tipo'] ?? null;
                if ($request->operacion === 'APERTURAR' && (empty($tipoDetalle) || trim((string) $tipoDetalle) === '')) {
                    $tipoDetalle = 'Pagado';
                }

                $detalleMovcaja = DetMovcaja::create([
                    'movcaja_id' => $movcaja->id,
                    'tipo' => $tipoDetalle,
                    'fechavencimiento' => $detalle['fechavencimiento'] ?? null,
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

                // Obtener el medio de pago
                $medioPago = strtolower($detalleMovcaja->mediopago);

                // Si el medio de pago es "efectivo", no registrar detalles adicionales
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

            // Cambiar situación de la caja y crear un registro en rel_turnocaja si la operación es 'APERTURAR'
            if ($request->operacion === 'APERTURAR') {

                // Si no existe turno abierto -> se permite aperturar
                $caja->update(['situacion' => 'A']);

                RelTurnocaja::create([
                    'fechainicio' => now(),
                    'situacion' => 'A',
                    'movcajainicio_id' => $movcaja->id,
                    'sucursal_id' => $request->sucursal_id,
                ]);

                // La sincronizacion de apertura se ejecuta en segundo plano en movcaja.apertura.sync.
            }


            DB::commit();
            if ($request->operacion === 'APERTURAR') {
                return response()->json([
                    'success' => true,
                    'redirect_url' => route('movcaja.index'),
                    'message' => 'Caja aperturada exitosamente.',
                    'sync_url' => route('movcaja.apertura.sync'),
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'redirect_url' => route('movcaja.index'),
                    'message' => 'Movimiento de caja creado exitosamente.',
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

    public function syncApertura(Request $request)
    {
        try {
            $usuario = Auth::user();
            $responsable = $usuario->persona;
            $sucursal_id = $responsable->sucursal_id;

            $this->procesarSyncApertura($sucursal_id);

            return response()->json([
                'success' => true,
                'message' => 'Sincronizacion ejecutada en segundo plano.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function setResumenEstado(Request $request)
    {
        $hidden = $request->boolean('hidden');
        session(['movcaja_resumen_oculto' => $hidden]);

        return response()->json([
            'success' => true,
            'hidden' => $hidden,
        ]);
    }

    private function procesarSyncApertura($sucursal_id)
    {
        app(SolicitudController::class)->sync();

        $ventas_anuladas = DB::table('movventa')
            ->join('movimiento', 'movventa.movimiento_id', '=', 'movimiento.id')
            ->join('solicitud', 'movventa.movimiento_id', '=', 'solicitud.movimiento_id')
            ->where('movventa.sucursal_id', $sucursal_id)
            ->where('movimiento.situacion', 'D') // movimiento anulado
            ->whereRaw("DATE_PART('day', now() - movventa.created_at) <= 7")
            ->whereIn('solicitud.estado', ['C', 'M', 'T'])
            ->select(
                'movventa.*',
                'solicitud.id as solicitud_id',
                'solicitud.serie',
                'solicitud.correlativo',
                'solicitud.moneda',
                'solicitud.fecha',
                'solicitud.json_facturador',
                'solicitud.json_sucursal'
            )
            ->get();

        $ventas_anuladas = $ventas_anuladas->groupBy(function ($venta) {
            return (new Carbon($venta->fecha))->toDateString() . '-' . $venta->moneda;
        });

        $tipos_documento = ['B', 'F'];

        $ventas_anuladas->each(function ($grupo_ventas, $clave) use ($tipos_documento, $sucursal_id) {
            [$fecha_str, $moneda] = explode('-', $clave);

            foreach ($tipos_documento as $tipo_documento) {
                $movimiento_ids = $grupo_ventas->pluck('movimiento_id')->unique();

                $solicitudes_paraanular = DB::table('solicitud')
                    ->join('movventa', 'solicitud.movimiento_id', '=', 'movventa.movimiento_id')
                    ->join('movimiento', 'movventa.movimiento_id', '=', 'movimiento.id')
                    ->where('solicitud.sucursal_id', $sucursal_id)
                    ->select('solicitud.id', DB::raw("'Error en comprobante' AS motivo"))
                    ->where('solicitud.tipo_documento', $tipo_documento)
                    ->where('movimiento.situacion', 'D')
                    ->whereRaw("DATE_PART('day', now() - movventa.created_at) <= 7")
                    ->whereIn('solicitud.estado', ['C', 'M', 'T'])
                    ->whereIn('solicitud.movimiento_id', $movimiento_ids)
                    ->whereNotExists(function ($subquery) use ($tipo_documento, $sucursal_id) {
                        $subquery->select(DB::raw(1))
                            ->from('solicitud as resumen')
                            ->where('resumen.sucursal_id', $sucursal_id)
                            ->whereNull('resumen.deleted_at')
                            ->whereIn('resumen.estado', ['R', 'E', 'P', 'S', 'C', 'M', 'T', 'U'])
                            ->where(function ($inner) use ($tipo_documento) {
                                if ($tipo_documento === 'B') {
                                    // Validacion para BOLETAS
                                    $inner->where('resumen.tipo_documento', 'E')
                                        ->whereRaw("EXISTS (
                                            SELECT 1 FROM jsonb_array_elements(resumen.data::jsonb->'detalles') AS detalle
                                            WHERE (detalle->>'id')::int = solicitud.id
                                        )");
                                } elseif ($tipo_documento === 'F') {
                                    // Validacion para FACTURAS (notas de credito)
                                    $inner->where('resumen.tipo_documento', 'N')
                                        ->whereRaw("
                                            (resumen.data->'documento_afectado'->>'serie') = solicitud.serie
                                            AND (resumen.data->'documento_afectado'->>'correlativo')::int = solicitud.correlativo::int
                                        ");
                                }
                            });
                    })
                    ->get();

                if ($solicitudes_paraanular->count()) {
                    $ids = $solicitudes_paraanular->pluck('id')->toArray();
                    $motivos = [];
                    foreach ($solicitudes_paraanular as $sol) {
                        $motivos[$sol->id] = $sol->motivo ?? 'Error en comprobante';
                    }

                    $fakeRequest = new \Illuminate\Http\Request([
                        'solicitud_id' => $ids,
                        'motivos' => $motivos,
                    ]);
                    // dd($tipo_documento );
                    if ($tipo_documento === 'B') {
                        app(SolicitudController::class)->cancell_boletas($fakeRequest);
                    } elseif ($tipo_documento === 'F') {
                        app(SolicitudController::class)->cancell_facturas($fakeRequest);
                    }
                }
            }
        });
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, $operacion)
    {
        try {
            $movcaja = Movcaja::with('detMovcaja', 'movimiento', 'caja', 'conceptopago', 'turno')->findOrFail($id);
            // Obtener detalles del movimiento para llenar la tabla en la vista
            $detallesMovimientos = $movcaja->detMovcaja->map(function ($detalle) {
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
                return array_merge($detalle->toArray(), [
                    'detalles' => $detalles // Añade el JSON como campo adicional
                ]);
            });
            $cajaId = $movcaja->caja_id;

            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal = Sucursal::findOrFail($persona->sucursal_id);
            $cajas = Caja::where('id', $cajaId)->get();
            $turnos = Turno::where('sucursal_id', $sucursal->id)->get();
            // Filtrar conceptos de pago según la operación
            $conceptos_pago = Conceptopago::where('id', '!=', 2)
                ->where('id', '!=', 3)
                ->where('id', '!=', 4)
                ->when($operacion === 'INGRESO', function ($query) {
                    return $query->where('tipo', 'I');
                })
                ->when($operacion === 'EGRESO', function ($query) {
                    return $query->where('tipo', 'E');
                })
                ->get();
            $medio_pagos = Mediopago::where('situacion', true)->get();
            $pos = Pasarelapago::where('situacion', true)->get(); // Tabla para P.O.S.
            $tarjetas = Tarjetum::where('situacion', true)->get(); // Tabla para Tarjetas
            $bancos = Banco::where('situacion', true)->get(); // Tabla para Bancos
            $billeteras = Billeteradigital::where('situacion', true)->get(); // Tabla para Billeteras Digitales


            $personas = Persona::where('sucursal_id', $sucursal->id)->get();
            $responsables = Persona::whereHas('rolpersonas', function ($query) {
                $query->where('rol_id', 1); // Ajusta el ID del rol según corresponda
            })->where('sucursal_id', $sucursal->id)->get();

            $tipos_documento = Tipodocumento::whereIn('id', [7, 8])->get();
            $sucursal_id = Auth::user()->persona->sucursal_id;

            return view('movcaja.edit', compact(
                'movcaja',
                'cajas',
                'conceptos_pago',
                'sucursal',
                'medio_pagos',
                'tipos_documento',
                'turnos',
                'operacion',
                'responsables',
                'sucursal_id',
                'pos',
                'tarjetas',
                'billeteras',
                'bancos',
                'detallesMovimientos'
            ));
        } catch (\Exception $e) {
            return redirect()->route('movcaja.index')->withErrors('Error al cargar la edición: ' . $e->getMessage());
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(MovcajaRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            // Validación
            $validator = Validator::make($request->all(), [
                'sucursal_id' => 'required|exists:sucursal,id',
                'conceptopago_id' => 'required|exists:conceptopago,id',
                'moneda' => 'required|string|in:PEN,USD',
                'total' => 'required|numeric|min:0',
                'caja_id' => 'required|exists:caja,id',
                'detalles_movimientos_json' => 'required|json',
                'comentario' => 'nullable|string',
                'persona_id' => 'nullable|exists:persona,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $movcaja = Movcaja::findOrFail($id);
            $usuario = Auth::user();
            $persona = $request->persona_id ? Persona::find($request->persona_id) : null;
            $responsable = $usuario->persona;
            $tipodoc = Tipodocumento::find($request->tipo_documento_visible);
            $caja = Caja::find($request->caja_id);

            // Actualizar Movimiento
            $movcaja->movimiento()->update([
                'usuario_id' => $usuario->id,
                'usuario' => $usuario->name,
                'responsable_id' => $responsable->id,
                'responsable' => $responsable->nombres . ' ' . $responsable->apellidos,
                'persona_id' => $request->persona_id,
                'persona' => $persona ? $persona->nombres . ' ' . $persona->apellidos : 'N/A',
                'comentario' => $request->comentario ?? '',

                'situacion' => 'A',
                'tipomovimiento_id' => $tipodoc->tipomovimiento_id,
                'tipodocumento_id' => $request->tipo_documento_visible,
            ]);
            $movcaja->update([
                'conceptopago_id' => $request->conceptopago_id,
                'moneda' => $request->moneda,
                'total' => $request->total,
                'tipocambio' => 3.71, // Proporciona un valor por defecto
                'caja_id' => $request->caja_id,
                'caja' => Caja::find($request->caja_id)->numero, // Asegúrate de tener el valor de caja
                'turno_id' => $request->turno_id ?? $movcaja->turno_id, // Mantener turno_id si no se envía
                'json_turno' => json_encode(Turno::find($request->turno_id ?? $movcaja->turno_id)), // Actualizar solo si turno_id cambia
                'movimiento_id' => $movcaja->movimiento_id, // Mantener el ID del movimiento asociado
            ]);



            // Actualizar Detalles
            DetMovcaja::where('movcaja_id', $movcaja->id)->forceDelete();
            $detalles = json_decode($request->detalles_movimientos_json, true);
            foreach ($detalles as $detalle) {
                DetMovcaja::create([
                    'movcaja_id' => $movcaja->id,
                    'tipo' => $detalle['tipo'] ?? null,
                    'fechavencimiento' => $detalle['fechavencimiento'] ?? null,
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
                'message' => 'Movimiento de caja actualizado exitosamente.',
                'redirect_url' => route('movcaja.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $movcaja = Movcaja::findOrFail($id);
            $movcaja->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento de caja eliminado correctamente.',
                'redirect_url' => route('movcaja.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a unique number for the movement.
     */
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

    public function anular($id)
    {
        $branch = (int) Auth::user()->persona?->sucursal_id;
        abort_unless($branch, 403, 'El usuario no tiene sucursal asignada.');
        DB::beginTransaction();

        try {
            $movcaja = Movcaja::where('sucursal_id', $branch)->lockForUpdate()->findOrFail($id);
            $movimiento = Movimiento::where('sucursal_id', $branch)->lockForUpdate()->findOrFail($movcaja->movimiento_id);
            if ($movimiento->getRawOriginal('situacion') !== 'A') {
                DB::rollBack();
                return response()->json(['success' => false, 'errors' => 'El movimiento ya está anulado.'], 422);
            }

            $cuentasOrigen = CuentaCobrarpagar::where('movcaja_id', $movcaja->id)->lockForUpdate()->get();
            foreach ($cuentasOrigen as $cuenta) {
                $tienePagosActivos = DetCuentaCobrarpagar::where('cuenta_cobrarpagar_id', $cuenta->id)
                    ->whereIn('situacion', ['A', 'E'])
                    ->whereHas('movcaja.movimiento', fn($query) => $query->where('situacion', 'A'))
                    ->exists();
                if ($tienePagosActivos) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'errors' => 'Esta cuenta tiene pagos parciales activos. Anula primero esos pagos y luego la deuda original.',
                    ], 409);
                }
            }

            $pagosRelacionados = DetCuentaCobrarpagar::where('movcaja_id', $movcaja->id)
                ->whereIn('situacion', ['A', 'E'])->get();
            $cuentasPagadas = $pagosRelacionados->pluck('cuenta_cobrarpagar_id')->unique();
            $movimiento->update(['situacion' => 'D']);
            $pagosRelacionados->each(fn($pago) => $pago->update(['situacion' => 'D']));
            if ($cuentasOrigen->isNotEmpty()) {
                CuentaCobrarpagarService::sincronizarDesdeMovcajaId((int) $movcaja->id);
            }
            foreach ($cuentasPagadas as $cuentaId) {
                $cuenta = CuentaCobrarpagar::find($cuentaId);
                if ($cuenta) CuentaCobrarpagarService::sincronizarDesdeMovcajaId((int) $cuenta->movcaja_id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento de caja anulado correctamente.',
                'redirect_url' => route('movcaja.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function verDetalles($id)
    {
        try {
            // Obtener el registro de Movcaja con los detalles relacionados
            $movcaja = Movcaja::with(['detMovcaja.Mediopago', 'movimiento.tipodocumento', 'conceptopago'])
                ->findOrFail($id);

            // Pasar los datos a la vista
            return view('movcaja.detalles', compact('movcaja'));
        } catch (\Exception $e) {
            // Redirigir con mensaje de error en caso de excepción

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function verDetallesMovimiento(Request $request)
    {
        try {
            // Obtener los IDs de movcaja, nota y mediopago de la petición
            $movcajaIds = explode(',', $request->input('movcaja_ids'));
            $nota = $request->input('nota');
            $mediopago = $request->input('mediopago');

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
            return view('movcaja.detallesMovimiento', compact('movcajas', 'detallesAgrupados', 'nota', 'mediopago'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
    public function generarPdfCierre($cajaId)
    {
        try {
            $user = Auth::user();
            $persona = Persona::findOrFail($user->persona_id);
            $sucursal = Sucursal::findOrFail($persona->sucursal_id);
            Caja::whereKey($cajaId)->where('sucursal_id', $sucursal->id)->firstOrFail();
            // Obtener el último turno activo o cerrado para la caja
            $ultimoTurno = RelTurnocaja::where('situacion', 'A') // Turno activo
                ->where('sucursal_id', $sucursal->id)
                ->whereHas('movcajainicio', function ($query) use ($cajaId) {
                    $query->where('caja_id', $cajaId);
                })
                ->latest('fechainicio') // Último turno
                ->first();
            abort_if(!$ultimoTurno, 404, 'No se encontró un turno para esta caja.');
            // Obtener movimientos solo del último turno
            $movimientos = Movcaja::with(['detMovcaja.Mediopago', 'conceptopago', 'movventa', 'movimiento'])
                ->when($ultimoTurno && $ultimoTurno->movcajafin_id, function ($query) use ($ultimoTurno) {
                    return $query->where('id', '<=', $ultimoTurno->movcajafin_id);
                })
                ->when($ultimoTurno, function ($query) use ($ultimoTurno) {
                    $query->where('id', '>=', $ultimoTurno->movcajainicio_id);
                })
                ->whereHas('movimiento', function ($query) {
                    $query->where('situacion', 'A'); // Filtrar movimientos con situación 'A'
                })
                ->where('caja_id', $cajaId)
                ->where('sucursal_id', $sucursal->id)
                ->orderBy('created_at', 'asc')
                ->get();
            $movimientosanulados = Movcaja::with(['detMovcaja.Mediopago', 'conceptopago', 'movventa', 'movimiento'])
                ->when($ultimoTurno && $ultimoTurno->movcajafin_id, function ($query) use ($ultimoTurno) {
                    return $query->where('id', '<=', $ultimoTurno->movcajafin_id);
                })
                ->when($ultimoTurno, function ($query) use ($ultimoTurno) {
                    $query->where('id', '>=', $ultimoTurno->movcajainicio_id);
                })
                ->whereHas('movimiento', function ($query) {
                    $query->where('situacion', 'D'); // Filtrar movimientos con situación 'A'
                })
                ->where('caja_id', $cajaId)
                ->where('sucursal_id', $sucursal->id)
                ->orderBy('created_at', 'asc')
                ->get();
            // dd($movimientosanulados);
            // Agrupar detalles por Medio de Pago y Tipo de Concepto de Pago
            $detallesAgrupados = $movimientos->flatMap->detMovcaja->where('tipo', 'Pagado')->groupBy(function ($detalle) {
                $tipoConcepto = $detalle->movcaja->conceptopago->tipo;
                $conceptoDescripcion = $detalle->movcaja->conceptopago->descripcion;

                // Construir el detalle completo para agrupación
                $detalles = [];
                if (!empty($detalle->tarjeta_id)) {
                    $detalles[] = $detalle->tarjeta;
                }
                if (!empty($detalle->banco_id)) {
                    $detalles[] = $detalle->banco;
                }
                if (!empty($detalle->billeteradigital_id)) {
                    $detalles[] = $detalle->billeteradigital;
                }
                if (!empty($detalle->pos_id)) {
                    $detalles[] = $detalle->pos;
                }
                $detalleUnificado = implode(' | ', $detalles);

                // Incluir el detalle unificado en la clave de agrupación
                if ($conceptoDescripcion === 'Apertura de caja') {
                    return "{$detalle->Mediopago->descripcion}|Apertura de caja|{$detalle->tipo}|{$detalleUnificado}";
                }

                if ($conceptoDescripcion === 'Pago de cliente') {
                    return "{$detalle->Mediopago->descripcion}|Pago de cliente|{$detalle->tipo}|{$detalleUnificado}";
                }

                return "{$detalle->Mediopago->descripcion}|" . ($tipoConcepto === 'Ingreso' ? 'Otros ingresos' : 'Egreso') . "|{$detalle->tipo}|{$detalleUnificado}";
            })->map(function ($items, $key) {
                // Construir los detalles unificados para la agrupación
                $detalles = $items->map(function ($detalle) {
                    $detalles = [];
                    if (!empty($detalle->tarjeta_id)) {
                        $detalles[] = $detalle->tarjeta;
                    }
                    if (!empty($detalle->banco_id)) {
                        $detalles[] = $detalle->banco;
                    }
                    if (!empty($detalle->billeteradigital_id)) {
                        $detalles[] = $detalle->billeteradigital;
                    }
                    if (!empty($detalle->pos_id)) {
                        $detalles[] = $detalle->pos;
                    }

                    return $detalles;
                })->flatten()->unique()->implode(' | ');

                return [
                    'clave_agrupacion' => $key,
                    'mediopago' => $items->first()->Mediopago->descripcion,
                    'nota' => $items->first()->movcaja->conceptopago->descripcion === 'Apertura de caja'
                        ? 'Apertura de caja'
                        : ($items->first()->movcaja->conceptopago->tipo === 'Ingreso'
                            ? ($items->first()->movcaja->conceptopago->descripcion === 'Pago de cliente'
                                ? 'Pago de cliente'
                                : 'Otros ingresos')
                            : 'Egresos'),
                    'tipo' => $items->first()->tipo,
                    'monto' => $items->sum('monto'),
                    'movcaja_ids' => $items->pluck('movcaja_id')->toArray(),
                    'detalles' => $detalles, // Agregar el campo "detalles" unificado
                ];
            });

            $totales = $this->resumenTotalesCierre($movimientos);


            // Obtener todos los medios de pago
            $mediosPago = Mediopago::where('situacion', true)->get();
            // Agrupar movimientos por medio de pago
            $ventasPorMetodos = $mediosPago->map(function ($medio) use ($movimientos) {
                $movimientosPorMetodo = $movimientos->filter(function ($movcaja) use ($medio) {
                    return $movcaja->movventa && // Ventas
                        $movcaja->detMovcaja->where('mediopago_id', $medio->id)->isNotEmpty();
                })->flatMap->detMovcaja->where('mediopago_id', $medio->id)->where('tipo', 'Pagado');

                // Detallar y agrupar los movimientos por detalle
                $detalles = $movimientosPorMetodo->map(function ($detalle) use ($medio) {
                    $extra = [];

                    // Si el medio de pago es "Efectivo", se asigna directamente
                    if (strtolower($medio->descripcion) === 'efectivo') {
                        $extra[] = 'Efectivo';
                    } else {
                        if (!empty($detalle->tarjeta_id)) {
                            $extra[] = $detalle->tarjeta;
                        }
                        if (!empty($detalle->banco_id)) {
                            $extra[] = $detalle->banco;
                        }
                        if (!empty($detalle->billeteradigital_id)) {
                            $extra[] = $detalle->billeteradigital;
                        }
                        if (!empty($detalle->pos_id)) {
                            $extra[] = $detalle->pos;
                        }
                    }

                    return [
                        'monto' => $detalle->monto,
                        'detalle' => implode(' | ', $extra),
                    ];
                })
                    ->groupBy('detalle') // Agrupa los detalles repetidos
                    ->map(function ($items, $detalle) {
                        // Calcula el total del monto por cada grupo de detalles
                        return [
                            'detalle' => $detalle,
                            'monto' => $items->sum('monto'),
                        ];
                    })
                    ->values(); // Reindexa los elementos agrupados

                return [
                    'medio' => $medio->descripcion,
                    'detalles' => $detalles,
                    'total' => $detalles->sum('monto'), // Calcula el total agrupado del medio de pago
                ];
            })->filter(function ($ventaPorMetodo) {
                // Filtrar solo los métodos de pago con ventas (total > 0)
                return $ventaPorMetodo['total'] > 0;
            });



            // Inicializar el array de ventas pagadas por cada medio de pago
            $ventasPagadas = [];

            foreach ($mediosPago as $medio) {
                $totalPorMetodo = $movimientos->filter(function ($movcaja) use ($medio) {
                    return $movcaja->movventa &&
                        $movcaja->detMovcaja->where('mediopago_id', $medio->id)->isNotEmpty();
                })->flatMap->detMovcaja->where('mediopago_id', $medio->id)->sum('monto');

                if ($totalPorMetodo > 0) {
                    $ventasPagadas[$medio->descripcion] = $totalPorMetodo;
                }
            }

            // Obtener información de la caja y la sucursal
            $caja = Caja::findOrFail($cajaId);
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal = Sucursal::findOrFail($persona->sucursal_id);


            // Obtener los movimiento_id de los movimientos filtrados
            $movimientoIds = $movimientos->pluck('movimiento.movimiento_id');
            $movimientoanuladosIds = $movimientosanulados->pluck('movimiento.movimiento_id');
            // Filtrar los Movventum usando los movimiento_id
            $detallesVentaPorProducto = Movventum::with(['detMovventas'])
                ->whereIn('movimiento_id', $movimientoIds) // Filtrar por movimiento_id del turno
                ->where('estado', 'N') // Solo estados válidos
                ->get()
                ->flatMap(function ($movventa) {
                    if ($movventa->detMovventas->isNotEmpty()) {
                        return $movventa->detMovventas
                            ->filter(function ($detalle) {
                                // Filtrar detalles que tengan producto_id no nulo
                                return !is_null($detalle->producto_id);
                            })
                            ->map(function ($detalle) {
                                $jsonProducto = json_decode($detalle->json_producto, true);
                                $descripcion = $jsonProducto['descripcion'] ?? 'Sin Descripción';
                                $precioUnitario = $jsonProducto['precio_unitario'] ?? 0;
                                $unidad = $jsonProducto['unidad'] ?? 'Sin Unidad';
                                $cantidad = $detalle->cantidad;
                                $subtotal = $detalle->monto;
                                $categoria = $detalle->producto->categorium->descripcion ?? 'Sin Categoria';

                                // Obtener los kardex asociados a este detalle
                                $kardexEntries = Kardex::where('detalle_id', $detalle->id)
                                    ->orderBy('fecha', 'asc')
                                    ->get();

                                $stockInicial = $kardexEntries->first()->stockanterior ?? 0;
                                $stockFinal = $kardexEntries->last()->stockactual ?? 0;

                                return [
                                    'descripcion' => $descripcion,
                                    'precio_unitario' => $precioUnitario,
                                    'unidad' => $unidad,
                                    'cantidad' => $cantidad,
                                    'subtotal' => $subtotal,
                                    'categoria' => $categoria,
                                    'stock_inicial' => $stockInicial,
                                    'stock_final' => $stockFinal,
                                ];
                            });
                    }
                    return collect(); // Retorna una colección vacía si no tiene detalles
                })
                ->groupBy(function ($item) {
                    return $item['descripcion'] . '|' . $item['precio_unitario'];
                })
                ->map(function (Collection $group) {
                    $primerItem = $group->first();
                    return [
                        'descripcion' => $primerItem['descripcion'],
                        'precio_unitario' => $primerItem['precio_unitario'],
                        'unidad' => $primerItem['unidad'],
                        'cantidad_total' => $group->sum('cantidad'),
                        'subtotal_total' => $group->sum('subtotal'),
                        'categoria' => $primerItem['categoria'],
                        'stock_inicial' => $group->first()['stock_inicial'], // Tomar el primero
                        'stock_final' => $group->last()['stock_final'], // Tomar el último
                    ];
                })
                ->values();


            // Agrupar detalles por categoría
            $detallesPorCategoria = $detallesVentaPorProducto->groupBy('categoria');
            // A partir de $detallesVentaPorProducto (ya procesado)
            $consolidadoPorCategoria = $detallesVentaPorProducto
                ->groupBy('categoria')
                ->map(function ($itemsPorCategoria) {
                    return $itemsPorCategoria->map(function ($item) {
                        return [
                            'descripcion' => $item['descripcion'],
                            'unidad' => $item['unidad'],
                            'cantidad' => $item['cantidad_total'],
                            'total' => $item['subtotal_total'],
                        ];
                    });
                });

            // dd( $detallesVentaPorProducto   );
            $historialhabitacion = HistorialHabitacion::where('relturnocaja_id', $ultimoTurno->id)
                ->whereHas('movhabitacion', function ($query) {
                    $query->whereNull('deleted_at'); // solo los que no están eliminados
                })
                ->with('movhabitacion')
                ->get()
                ->groupBy('movhabitacion_id')
                ->map(function ($items) {
                    return $items->sortByDesc('created_at')->first();
                })
                ->values();


            $historialhabitacionPago = HistorialHabitacion::where('relturnocaja_id', $ultimoTurno->id)
                ->with('movhabitacion') // <-- Para verificar soft delete
                ->get()
                ->filter(function ($item) {
                    return optional($item->movhabitacion)->deleted_at === null;
                })
                ->groupBy('movhabitacion_id')
                ->map(function ($items) {
                    $ultimoRegistro = $items->sortByDesc('created_at')->first();

                    // Verificar si en otro turno fue pagado
                    $tienePagoEnOtroTurno = HistorialHabitacion::where('movhabitacion_id', $ultimoRegistro->movhabitacion_id)
                        ->where('relturnocaja_id', '!=', $ultimoRegistro->relturnocaja_id)
                        ->where('situacion_pago', 'S')
                        ->exists();

                    return $tienePagoEnOtroTurno ? null : $ultimoRegistro;
                })
                ->filter()
                ->values();

            $movhabitacioneshistorial = Movhabitacion::whereIn('id', $historialhabitacion->pluck('movhabitacion_id'))
                ->whereNull('deleted_at')
                ->get();

            // Agregar situacion_checkout y situacion_pago a cada movhabitacion
            $movhabitacioneshistorial->each(function ($movhabitacion) use ($historialhabitacion) {
                $historial = $historialhabitacion->firstWhere('movhabitacion_id', $movhabitacion->id);
                if ($historial) {
                    $movhabitacion->situacion_checkout = $historial->situacion_checkout;
                    $movhabitacion->situacion_pago = $historial->situacion_pago;
                }
            });
            //  dd( $movhabitacioneshistorial);

            // Filtrar movimientos de habitaciones con situación 'C' relacionados al turno
            $movhabitaciones = Movhabitacion::where('sucursal_id', $sucursal->id)
                ->whereIn('id', $historialhabitacion->where('situacion_pago', 'S')->pluck('movhabitacion_id')) // Filtrar solo los pagados
                ->get()
                ->map(function ($hab) {
                    // Obtener productos consumidos en base al movimiento de la habitación
                    $productosConsumidos = DetMovventum::whereHas('movventum', function ($query) use ($hab) {
                        $query->where('movimiento_id', $hab->movimiento_id);
                    })->get();

                    // Calcular el total de productos consumidos
                    $totalProductosConsumidos = $productosConsumidos->sum(function ($producto) {
                        $datosProducto = json_decode($producto->json_producto, true);
                        $cantidad = $datosProducto['cantidad'] ?? 0;
                        $precioUnitario = $datosProducto['precio_unitario'] ?? 0;
                        return $cantidad * $precioUnitario;
                    });

                    // Agregar el total como un atributo adicional del modelo
                    $hab->precio_productos = $totalProductosConsumidos;
                    return $hab;
                });



            $movhabitacionessincheckoutpagadas = $historialhabitacion->filter(function ($historialhabitacion) {
                return $historialhabitacion->situacion_checkout === 'N' && $historialhabitacion->situacion_pago === 'S';
            });
            $movhabitacionessinpagar = $historialhabitacion->filter(function ($historialhabitacion) {
                return $historialhabitacion->situacion_checkout === 'N' && $historialhabitacion->situacion_pago === 'N';
            });
            $movhabitacionesconcheckout = $historialhabitacion->filter(function ($historialhabitacion) {
                return $historialhabitacion->situacion_checkout === 'S' && $historialhabitacion->situacion_pago === 'S';
            });

            $totalHabitacionesconcheck = $historialhabitacion->filter(function ($hab) {
                return $hab->situacion_checkout == 'S';
            })->sum('movhabitacion.total');
            $totalHabitacionessincheck = $movhabitacionessincheckoutpagadas->filter(function ($hab) {
                return empty($hab->fecha_salida_real);
            })->sum('movhabitacion.total');
            $totalHabitacionessincheckEnDeuda = $movhabitacionessinpagar->filter(function ($hab) {
                return empty($hab->fecha_salida_real);
            })->sum('movhabitacion.total');
            $totalHabitaciones = $historialhabitacion->filter(function ($hab) {
                return $hab->situacion_pago !== 'N';
            })->sum('movhabitacion.total');

            // Egresos por métodos de pago
            $egresosPagados = [];
            foreach ($mediosPago as $medio) {
                $totalEgresosPorMetodo = $movimientos->flatMap->detMovcaja->where(function ($detalle) use ($medio) {
                    return $detalle->movcaja->conceptopago->tipo === 'Egreso' &&
                        $detalle->mediopago_id == $medio->id;
                })->sum('monto');

                if ($totalEgresosPorMetodo > 0) {
                    $egresosPagados[$medio->descripcion] = $totalEgresosPorMetodo;
                }
            }
            // Ingresos por métodos de pago
            $ingresosPagados = [];
            foreach ($mediosPago as $medio) {
                $totalIngresosPorMetodo = $movimientos->flatMap->detMovcaja->where(function ($detalle) use ($medio) {
                    return $detalle->movcaja->conceptopago->tipo === 'Ingreso' && $detalle->movcaja->conceptopago_id !== 4 && $detalle->movcaja->conceptopago_id !== 2 &&
                        $detalle->mediopago_id == $medio->id;
                })->sum('monto');

                if ($totalIngresosPorMetodo > 0) {
                    $ingresosPagados[$medio->descripcion] = $totalIngresosPorMetodo;
                }
            }


            // Consultar las ventas (movventa) asociadas a esos movimientos de venta
            $ventasPorTipoDocumento = \App\Models\Movventum::with('movimiento.tipodocumento')
                ->whereIn('movimiento_id', $movimientoIds)
                ->where('estado', 'N') // solo ventas válidas
                ->get()
                ->groupBy(function ($venta) {
                    return $venta->movimiento->tipodocumento->nombre ?? 'Desconocido';
                })
                ->map(function ($items, $tipoNombre) {
                    return [
                        'tipo' => $tipoNombre,
                        'cantidad' => $items->count(),
                        'total' => $items->sum('total'),
                    ];
                })
                ->values();


            $anulacionesVentas = Movventum::with(['detMovventas'])
                ->whereIn('movimiento_id', $movimientoanuladosIds)
                ->whereHas('movimiento', function ($q) {
                    $q->where('situacion', 'D'); // Aquí filtras en la tabla movimiento
                })
                ->get()
                ->flatMap(function ($movventa) {
                    return $movventa->detMovventas
                        ->filter(fn($detalle) => !is_null($detalle->producto_id))
                        ->map(function ($detalle) {
                            $jsonProducto = json_decode($detalle->json_producto, true);
                            return [
                                'descripcion' => $jsonProducto['descripcion'] ?? 'Sin Descripción',
                                'unidad' => $jsonProducto['unidad'] ?? 'Sin Unidad',
                                'cantidad' => $detalle->cantidad,
                                'subtotal' => $detalle->monto,
                                'categoria' => $detalle->producto->categorium->descripcion ?? 'Sin Categoria',
                            ];
                        });
                })
                ->groupBy('categoria');
            $descuentosPorPersona = Movventum::with(['detMovventas', 'movimiento.persona'])
                ->whereIn('movimiento_id', $movimientoIds)
                ->whereHas('detMovventas', function ($q) {
                    $q->where('dcto_porcentaje', '>', 0);
                })
                ->get()
                ->flatMap(function ($movventa) {
                    $persona = $movventa->movimiento->Persona->personamaestro->nrodoc . ' - ' . $movventa->movimiento->Persona->nombres . ' ' . $movventa->movimiento->Persona->apellidos ?? 'Sin Nombre';

                    return $movventa->detMovventas
                        ->filter(fn($detalle) => $detalle->dcto_porcentaje > 0)
                        ->map(function ($detalle) use ($persona) {
                            $totalOriginal = $detalle->monto ?? 0;
                            $descuento = $totalOriginal * ($detalle->dcto_porcentaje / 100);

                            return [
                                'persona' => $persona,
                                'subtotal' => $totalOriginal,
                                'descuento' => $descuento,
                            ];
                        });
                })
                ->groupBy('persona')
                ->map(function ($items) {
                    return [
                        'subtotal' => $items->sum('subtotal'),
                        'descuento' => $items->sum('descuento'),
                    ];
                });


            // Obtener anulaciones de productos

            $anulacionesProductos = DetMovventum::with('producto.categorium')
                ->whereHas('movventum', fn($q) => $q->where('estado', 'A')) // o usa otro filtro si aplica
                ->get()
                ->map(function ($detalle) {
                    $jsonProducto = json_decode($detalle->json_producto, true);
                    return [
                        'descripcion' => $jsonProducto['descripcion'] ?? 'Sin Descripción',
                        'unidad' => $jsonProducto['unidad'] ?? 'Sin Unidad',
                        'cantidad' => $detalle->cantidad,
                        'subtotal' => $detalle->monto,
                        'categoria' => $detalle->producto->categorium->descripcion ?? 'Sin Categoria',
                    ];
                })
                ->groupBy('categoria');

            $anulacionesAlojamientos = collect(); // por seguridad

            if ($ultimoTurno) {
                $anulacionesAlojamientos = Movhabitacion::whereHas('movimiento', function ($q) {
                    $q->where('situacion', 'D'); // Anulado
                })
                    ->where('sucursal_id', $sucursal->id)
                    ->whereBetween('fechaingreso', [
                        $ultimoTurno->fechainicio,
                        $ultimoTurno->fechafin ?? now(), // por si el turno sigue abierto
                    ])
                    ->with('movimiento')
                    ->get();
            }

            $anulaciones = Anulacion::with(['usuario', 'movimiento'])
                ->whereHas('movimiento', function ($q) use ($sucursal) {
                    $q->where('sucursal_id', $sucursal->id);
                })
                ->orderBy('fecha', 'desc')
                ->get();


            $movcajadinero = RelMovcajadinero::where('movcaja_id', $ultimoTurno->movcajafin_id)
                ->with('dinero') // importante para acceder a nombre/valor
                ->get();



            // Preparar los datos para el PDF
            $data = [
                'caja' => $caja,
                'totalHabitaciones' => $totalHabitaciones,
                'sucursal' => $sucursal,
                'totales' => $totales,
                'detallesAgrupados' => $detallesAgrupados,
                'detallesVentaPorProducto' => $detallesVentaPorProducto,
                'detallesPorCategoria' => $detallesPorCategoria,
                'anulacionesVentasPorCategoria' => $anulacionesVentas,
                'anulacionesProductosPorCategoria' => $anulacionesProductos,
                'anulacionesAlojamientos' => $anulacionesAlojamientos,

                'consolidadoPorCategoria' => $consolidadoPorCategoria,
                'movhabitacionessinpagar' => $movhabitacionessinpagar,
                'movhabitaciones' => $movhabitaciones, // Pasamos los datos de habitaciones
                'egresosPagados' => $egresosPagados,
                'ingresosPagados' => $ingresosPagados,
                'ventasPagadas' => $ventasPagadas,
                'ultimoTurno' => $ultimoTurno,
                'totalHabitaciones' => $totalHabitaciones,
                'movhabitacionessinpagar' => $movhabitacionessinpagar,
                'movhabitacionessincheckoutpagadas' => $movhabitacionessincheckoutpagadas,
                'movhabitacionesconcheckout' => $movhabitacionesconcheckout,
                'ventasPorMetodos' => $ventasPorMetodos,
                'totalHabitacionesconcheck' => $totalHabitacionesconcheck,
                'historialhabitacion' => $historialhabitacion,
                'totalHabitacionessincheckEnDeuda' => $totalHabitacionessincheckEnDeuda,
                'totalHabitacionessincheck' => $totalHabitacionessincheck,
                'ventasPorMetodos' => $ventasPorMetodos,
                'historialhabitacionPago' => $historialhabitacionPago,
                'ventasPorTipoDocumento' => $ventasPorTipoDocumento,
                'anulaciones' => $anulaciones,
                'movcajadinero' => $movcajadinero,
                'descuentosPorPersona' => $descuentosPorPersona

            ];

            // Cargar la vista con los datos para el PDF
            $pdf = PDF::loadView('movcaja.pdf_cierre', $data)
                ->setOption('margin-top', 2.55)
                ->setOption('margin-bottom', 5)
                ->setOption('margin-left', 10)
                ->setOption('margin-right', 10)
                ->setOption(
                    'header-html',
                    view('movcaja.pdf_header', $data)->render()
                )
                ->setOption(
                    'footer-html',
                    view('movcaja.pdf_footer')->render()
                )

                ->setOption('footer-right', 'Pg. [page] de [topage]')
                ->setOption('footer-font-size', 6)
                ->setOption('footer-spacing', 0);


            return $pdf->stream('cierre_caja_' . $caja->numero . '.pdf');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al generar el PDF: ' . $e->getMessage()]);
        }
    }

    public function generarPdfCierreTicket($cajaId)
    {
        try {
            $user = Auth::user();
            $persona = Persona::findOrFail($user->persona_id);
            $sucursal = Sucursal::findOrFail($persona->sucursal_id);
            Caja::whereKey($cajaId)->where('sucursal_id', $sucursal->id)->firstOrFail();
            // Obtener el último turno activo o cerrado para la caja
            $ultimoTurno = RelTurnocaja::where('situacion', 'A') // Turno activo
                ->where('sucursal_id', $sucursal->id)
                ->whereHas('movcajainicio', function ($query) use ($cajaId) {
                    $query->where('caja_id', $cajaId);
                })
                ->latest('fechainicio') // Último turno
                ->first();
            abort_if(!$ultimoTurno, 404, 'No se encontró un turno para esta caja.');
            // Obtener movimientos solo del último turno
            $movimientos = Movcaja::with(['detMovcaja.Mediopago', 'conceptopago', 'movventa', 'movimiento'])
                ->when($ultimoTurno && $ultimoTurno->movcajafin_id, function ($query) use ($ultimoTurno) {
                    return $query->where('id', '<=', $ultimoTurno->movcajafin_id);
                })
                ->when($ultimoTurno, function ($query) use ($ultimoTurno) {
                    $query->where('id', '>=', $ultimoTurno->movcajainicio_id);
                })
                ->whereHas('movimiento', function ($query) {
                    $query->where('situacion', 'A'); // Filtrar movimientos con situación 'A'
                })
                ->where('caja_id', $cajaId)
                ->where('sucursal_id', $sucursal->id)
                ->orderBy('created_at', 'asc')
                ->get();

            // Agrupar detalles por Medio de Pago y Tipo de Concepto de Pago
            $detallesAgrupados = $movimientos->flatMap->detMovcaja->where('tipo', 'Pagado')->groupBy(function ($detalle) {
                $tipoConcepto = $detalle->movcaja->conceptopago->tipo;
                $conceptoDescripcion = $detalle->movcaja->conceptopago->descripcion;

                // Construir el detalle completo para agrupación
                $detalles = [];
                if (!empty($detalle->tarjeta_id)) {
                    $detalles[] = $detalle->tarjeta;
                }
                if (!empty($detalle->banco_id)) {
                    $detalles[] = $detalle->banco;
                }
                if (!empty($detalle->billeteradigital_id)) {
                    $detalles[] = $detalle->billeteradigital;
                }
                if (!empty($detalle->pos_id)) {
                    $detalles[] = $detalle->pos;
                }
                $detalleUnificado = implode(' | ', $detalles);

                // Incluir el detalle unificado en la clave de agrupación
                if ($conceptoDescripcion === 'Apertura de caja') {
                    return "{$detalle->Mediopago->descripcion}|Apertura de caja|{$detalle->tipo}|{$detalleUnificado}";
                }

                if ($conceptoDescripcion === 'Pago de cliente') {
                    return "{$detalle->Mediopago->descripcion}|Pago de cliente|{$detalle->tipo}|{$detalleUnificado}";
                }

                return "{$detalle->Mediopago->descripcion}|" . ($tipoConcepto === 'Ingreso' ? 'Otros ingresos' : 'Egreso') . "|{$detalle->tipo}|{$detalleUnificado}";
            })->map(function ($items, $key) {
                // Construir los detalles unificados para la agrupación
                $detalles = $items->map(function ($detalle) {
                    $detalles = [];
                    if (!empty($detalle->tarjeta_id)) {
                        $detalles[] = $detalle->tarjeta;
                    }
                    if (!empty($detalle->banco_id)) {
                        $detalles[] = $detalle->banco;
                    }
                    if (!empty($detalle->billeteradigital_id)) {
                        $detalles[] = $detalle->billeteradigital;
                    }
                    if (!empty($detalle->pos_id)) {
                        $detalles[] = $detalle->pos;
                    }

                    return $detalles;
                })->flatten()->unique()->implode(' | ');

                return [
                    'clave_agrupacion' => $key,
                    'mediopago' => $items->first()->Mediopago->descripcion,
                    'nota' => $items->first()->movcaja->conceptopago->descripcion === 'Apertura de caja'
                        ? 'Apertura de caja'
                        : ($items->first()->movcaja->conceptopago->tipo === 'Ingreso'
                            ? ($items->first()->movcaja->conceptopago->descripcion === 'Pago de cliente'
                                ? 'Pago de cliente'
                                : 'Otros ingresos')
                            : 'Egresos'),
                    'tipo' => $items->first()->tipo,
                    'monto' => $items->sum('monto'),
                    'movcaja_ids' => $items->pluck('movcaja_id')->toArray(),
                    'detalles' => $detalles, // Agregar el campo "detalles" unificado
                ];
            });

            $totales = $this->resumenTotalesCierre($movimientos, false);

            // Obtener todos los medios de pago
            $mediosPago = Mediopago::where('situacion', true)->get();
            // Agrupar movimientos por medio de pago
            $ventasPorMetodos = $mediosPago->map(function ($medio) use ($movimientos) {
                $movimientosPorMetodo = $movimientos->filter(function ($movcaja) use ($medio) {
                    return $movcaja->movventa && // Ventas
                        $movcaja->detMovcaja->where('mediopago_id', $medio->id)->isNotEmpty();
                })->flatMap->detMovcaja->where('mediopago_id', $medio->id)->where('tipo', 'Pagado');

                // Detallar y agrupar los movimientos por detalle
                $detalles = $movimientosPorMetodo->map(function ($detalle) use ($medio) {
                    $extra = [];

                    // Si el medio de pago es "Efectivo", se asigna directamente
                    if (strtolower($medio->descripcion) === 'efectivo') {
                        $extra[] = 'Efectivo';
                    } else {
                        if (!empty($detalle->tarjeta_id)) {
                            $extra[] = $detalle->tarjeta;
                        }
                        if (!empty($detalle->banco_id)) {
                            $extra[] = $detalle->banco;
                        }
                        if (!empty($detalle->billeteradigital_id)) {
                            $extra[] = $detalle->billeteradigital;
                        }
                        if (!empty($detalle->pos_id)) {
                            $extra[] = $detalle->pos;
                        }
                    }

                    return [
                        'monto' => $detalle->monto,
                        'detalle' => implode(' | ', $extra),
                    ];
                })
                    ->groupBy('detalle') // Agrupa los detalles repetidos
                    ->map(function ($items, $detalle) {
                        // Calcula el total del monto por cada grupo de detalles
                        return [
                            'detalle' => $detalle,
                            'monto' => $items->sum('monto'),
                        ];
                    })
                    ->values(); // Reindexa los elementos agrupados

                return [
                    'medio' => $medio->descripcion,
                    'detalles' => $detalles,
                    'total' => $detalles->sum('monto'), // Calcula el total agrupado del medio de pago
                ];
            })->filter(function ($ventaPorMetodo) {
                // Filtrar solo los métodos de pago con ventas (total > 0)
                return $ventaPorMetodo['total'] > 0;
            });




            // Inicializar el array de ventas pagadas por cada medio de pago
            $ventasPagadas = [];

            foreach ($mediosPago as $medio) {
                $totalPorMetodo = $movimientos->filter(function ($movcaja) use ($medio) {
                    return $movcaja->movventa &&
                        $movcaja->detMovcaja->where('mediopago_id', $medio->id)->isNotEmpty();
                })->flatMap->detMovcaja->where('mediopago_id', $medio->id)->sum('monto');

                if ($totalPorMetodo > 0) {
                    $ventasPagadas[$medio->descripcion] = $totalPorMetodo;
                }
            }

            // Obtener información de la caja y la sucursal
            $caja = Caja::findOrFail($cajaId);
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal = Sucursal::findOrFail($persona->sucursal_id);


            // Obtener los movimiento_id de los movimientos filtrados
            $movimientoIds = $movimientos->pluck('movimiento_id');
            // Filtrar los Movventum usando los movimiento_id
            $detallesVentaPorProducto = Movventum::with(['detMovventas'])
                ->whereIn('movimiento_id', $movimientoIds) // Filtrar por movimiento_id del turno
                ->where('estado', 'N') // Solo estados válidos
                ->get()
                ->flatMap(function ($movventa) {
                    if ($movventa->detMovventas->isNotEmpty()) {
                        return $movventa->detMovventas
                            ->filter(function ($detalle) {
                                // Filtrar detalles que tengan producto_id no nulo
                                return !is_null($detalle->producto_id);
                            })
                            ->map(function ($detalle) {
                                $jsonProducto = json_decode($detalle->json_producto, true);
                                $descripcion = $jsonProducto['descripcion'] ?? 'Sin Descripción';
                                $precioUnitario = $jsonProducto['precio_unitario'] ?? 0;
                                $unidad = $jsonProducto['unidad'] ?? 'Sin Unidad';
                                $cantidad = $detalle->cantidad;
                                $subtotal = $detalle->monto;
                                $categoria = $detalle->producto->categorium->descripcion ?? 'Sin Categoria';

                                // Obtener los kardex asociados a este detalle
                                $kardexEntries = Kardex::where('detalle_id', $detalle->id)
                                    ->orderBy('fecha', 'asc')
                                    ->get();

                                $stockInicial = $kardexEntries->first()->stockanterior ?? 0;
                                $stockFinal = $kardexEntries->last()->stockactual ?? 0;

                                return [
                                    'descripcion' => $descripcion,
                                    'precio_unitario' => $precioUnitario,
                                    'unidad' => $unidad,
                                    'cantidad' => $cantidad,
                                    'subtotal' => $subtotal,
                                    'categoria' => $categoria,
                                    'stock_inicial' => $stockInicial,
                                    'stock_final' => $stockFinal,
                                ];
                            });
                    }
                    return collect(); // Retorna una colección vacía si no tiene detalles
                })
                ->groupBy(function ($item) {
                    return $item['descripcion'] . '|' . $item['precio_unitario'];
                })
                ->map(function (Collection $group) {
                    $primerItem = $group->first();
                    return [
                        'descripcion' => $primerItem['descripcion'],
                        'precio_unitario' => $primerItem['precio_unitario'],
                        'unidad' => $primerItem['unidad'],
                        'cantidad_total' => $group->sum('cantidad'),
                        'subtotal_total' => $group->sum('subtotal'),
                        'categoria' => $primerItem['categoria'],
                        'stock_inicial' => $group->first()['stock_inicial'], // Tomar el primero
                        'stock_final' => $group->last()['stock_final'], // Tomar el último
                    ];
                })
                ->values();


            // Agrupar detalles por categoría
            $detallesPorCategoria = $detallesVentaPorProducto->groupBy('categoria');
            // dd( $detallesVentaPorProducto   );
            $historialhabitacion = HistorialHabitacion::where('relturnocaja_id', $ultimoTurno->id)
                ->whereHas('movhabitacion', function ($query) {
                    $query->whereNull('deleted_at'); // solo los que no están eliminados
                })
                ->with('movhabitacion')
                ->get()
                ->groupBy('movhabitacion_id')
                ->map(function ($items) {
                    return $items->sortByDesc('created_at')->first();
                })
                ->values();
            $historialhabitacionPago = HistorialHabitacion::where('relturnocaja_id', $ultimoTurno->id)
                ->get()
                ->groupBy('movhabitacion_id')
                ->map(function ($items) {
                    $ultimoRegistro = $items->sortByDesc('created_at')->first(); // Obtiene el último registrado

                    // Verificar si en TODAS las ocurrencias de este movhabitacion_id existe un pago 'S' en otro turno
                    $tienePagoEnOtroTurno = HistorialHabitacion::where('movhabitacion_id', $ultimoRegistro->movhabitacion_id)
                        ->where('relturnocaja_id', '!=', $ultimoRegistro->relturnocaja_id)
                        ->where('situacion_pago', 'S')
                        ->exists();

                    return $tienePagoEnOtroTurno ? null : $ultimoRegistro;
                })
                ->filter() // Remueve los nulos (los que ya fueron pagados en otro turno)
                ->values();
            $movhabitacioneshistorial = Movhabitacion::whereIn('id', $historialhabitacion->pluck('movhabitacion_id'))->get();

            // Agregar situacion_checkout y situacion_pago a cada movhabitacion
            $movhabitacioneshistorial->each(function ($movhabitacion) use ($historialhabitacion) {
                $historial = $historialhabitacion->firstWhere('movhabitacion_id', $movhabitacion->id);
                if ($historial) {
                    $movhabitacion->situacion_checkout = $historial->situacion_checkout;
                    $movhabitacion->situacion_pago = $historial->situacion_pago;
                }
            });
            //  dd( $movhabitacioneshistorial);

            // Filtrar movimientos de habitaciones con situación 'C' relacionados al turno
            $movhabitaciones = Movhabitacion::where('sucursal_id', $sucursal->id)
                ->whereIn('id', $historialhabitacion->where('situacion_pago', 'S')->pluck('movhabitacion_id')) // Filtrar solo los pagados
                ->get()
                ->map(function ($hab) {
                    // Obtener productos consumidos en base al movimiento de la habitación
                    $productosConsumidos = DetMovventum::whereHas('movventum', function ($query) use ($hab) {
                        $query->where('movimiento_id', $hab->movimiento_id);
                    })->get();

                    // Calcular el total de productos consumidos
                    $totalProductosConsumidos = $productosConsumidos->sum(function ($producto) {
                        $datosProducto = json_decode($producto->json_producto, true);
                        $cantidad = $datosProducto['cantidad'] ?? 0;
                        $precioUnitario = $datosProducto['precio_unitario'] ?? 0;
                        return $cantidad * $precioUnitario;
                    });

                    // Agregar el total como un atributo adicional del modelo
                    $hab->precio_productos = $totalProductosConsumidos;
                    return $hab;
                });



            $movhabitacionessincheckoutpagadas = $historialhabitacion->filter(function ($historialhabitacion) {
                return $historialhabitacion->situacion_checkout === 'N' && $historialhabitacion->situacion_pago === 'S';
            });
            $movhabitacionessinpagar = $historialhabitacion->filter(function ($historialhabitacion) {
                return $historialhabitacion->situacion_checkout === 'N' && $historialhabitacion->situacion_pago === 'N';
            });
            $movhabitacionesconcheckout = $historialhabitacion->filter(function ($historialhabitacion) {
                return $historialhabitacion->situacion_checkout === 'S' && $historialhabitacion->situacion_pago === 'S';
            });

            $totalHabitacionesconcheck = $historialhabitacion->filter(function ($hab) {
                return $hab->situacion_checkout == 'S';
            })->sum('movhabitacion.total');
            $totalHabitacionessincheck = $movhabitacionessincheckoutpagadas->filter(function ($hab) {
                return empty($hab->fecha_salida_real);
            })->sum('movhabitacion.total');
            $totalHabitacionessincheckEnDeuda = $movhabitacionessinpagar->filter(function ($hab) {
                return empty($hab->fecha_salida_real);
            })->sum('movhabitacion.total');
            $totalHabitaciones = $historialhabitacion->filter(function ($hab) {
                return $hab->situacion_pago !== 'N';
            })->sum('movhabitacion.total');

            // Egresos por métodos de pago
            $egresosPagados = [];
            foreach ($mediosPago as $medio) {
                $totalEgresosPorMetodo = $movimientos->flatMap->detMovcaja->where(function ($detalle) use ($medio) {
                    return $detalle->movcaja->conceptopago->tipo === 'Egreso' &&
                        $detalle->mediopago_id == $medio->id;
                })->sum('monto');

                if ($totalEgresosPorMetodo > 0) {
                    $egresosPagados[$medio->descripcion] = $totalEgresosPorMetodo;
                }
            }
            // Ingresos por métodos de pago
            $ingresosPagados = [];
            foreach ($mediosPago as $medio) {
                $totalIngresosPorMetodo = $movimientos->flatMap->detMovcaja->where(function ($detalle) use ($medio) {
                    return $detalle->movcaja->conceptopago->tipo === 'Ingreso' && $detalle->movcaja->conceptopago_id !== 4 && $detalle->movcaja->conceptopago_id !== 2 &&
                        $detalle->mediopago_id == $medio->id;
                })->sum('monto');

                if ($totalIngresosPorMetodo > 0) {
                    $ingresosPagados[$medio->descripcion] = $totalIngresosPorMetodo;
                }
            }

            $anulacionesAlojamientos = collect(); // por seguridad

            if ($ultimoTurno) {
                $anulacionesAlojamientos = Movhabitacion::whereHas('movimiento', function ($q) {
                    $q->where('situacion', 'D'); // Anulado
                })
                    ->where('sucursal_id', $sucursal->id)
                    ->whereBetween('fechaingreso', [
                        $ultimoTurno->fechainicio,
                        $ultimoTurno->fechafin ?? now(), // por si el turno sigue abierto
                    ])
                    ->with('movimiento')
                    ->get();
            }
            // Preparar los datos para el PDF
            $data = [
                'caja' => $caja,
                'totalHabitaciones' => $totalHabitaciones,
                'sucursal' => $sucursal,
                'totales' => $totales,
                'detallesAgrupados' => $detallesAgrupados,
                'detallesVentaPorProducto' => $detallesVentaPorProducto,
                'detallesPorCategoria' => $detallesPorCategoria,
                'movhabitacionessinpagar' => $movhabitacionessinpagar,
                'movhabitaciones' => $movhabitaciones, // Pasamos los datos de habitaciones
                'egresosPagados' => $egresosPagados,
                'ingresosPagados' => $ingresosPagados,
                'ventasPagadas' => $ventasPagadas,
                'ultimoTurno' => $ultimoTurno,
                'totalHabitaciones' => $totalHabitaciones,
                'movhabitacionessinpagar' => $movhabitacionessinpagar,
                'movhabitacionessincheckoutpagadas' => $movhabitacionessincheckoutpagadas,
                'movhabitacionesconcheckout' => $movhabitacionesconcheckout,
                'ventasPorMetodos' => $ventasPorMetodos,
                'totalHabitacionesconcheck' => $totalHabitacionesconcheck,
                'historialhabitacion' => $historialhabitacion,
                'totalHabitacionessincheckEnDeuda' => $totalHabitacionessincheckEnDeuda,
                'totalHabitacionessincheck' => $totalHabitacionessincheck,
                'ventasPorMetodos' => $ventasPorMetodos,
                'historialhabitacionPago' => $historialhabitacionPago,
                'anulacionesAlojamientos' => $anulacionesAlojamientos
            ];

            // Cargar la vista con los datos para el PDF
            $pdf = PDF::loadView('movcaja.pdf_cierre_ticket', $data);
            $widthMm = round(226.77 * 25.4 / 72, 2);
            $heightMm = round(5500 * 25.4 / 72, 2);
            $pdf->setOption('page-width', $widthMm . 'mm');
            $pdf->setOption('page-height', $heightMm . 'mm');

            // Descargar el PDF o abrirlo en una nueva pestaña
            return $pdf->stream('cierre_caja_' . $caja->numero . '.pdf');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al generar el PDF: ' . $e->getMessage()]);
        }
    }

    public function report(Request $request)
    {
        $user = Auth::user();
        $persona = Persona::findOrFail($user->persona_id);
        $sucursal = Sucursal::findOrFail($persona->sucursal_id);
        $request->validate([
            'caja_id' => ['nullable', 'integer', Rule::exists('caja', 'id')->where('sucursal_id', $sucursal->id)],
            'turno' => 'nullable|string|max:20',
            'tipo' => 'nullable|in:0,Ingreso,Egreso',
            'conceptospago' => 'nullable|integer|min:0',
            'search' => 'nullable|string|max:150',
            'fechaInicio' => 'nullable|date_format:Y-m-d',
            'fechaFin' => 'nullable|date_format:Y-m-d|after_or_equal:fechaInicio',
            'perPage' => 'nullable|integer|in:10,25,50,100',
        ]);

        $cajaFiltro = $request->filled('caja_id') ? (int) $request->input('caja_id') : null;
        $turnoRequest = (string) $request->input('turno', '');
        $turnoFiltro = null;
        if ($turnoRequest !== '' && $turnoRequest !== '0') {
            $turnoFiltro = RelTurnocaja::where('sucursal_id', $sucursal->id)
                ->whereKey((int) $turnoRequest)
                ->whereHas('movcajainicio', fn($q) => $q->where('sucursal_id', $sucursal->id)
                    ->when($cajaFiltro, fn($box) => $box->where('caja_id', $cajaFiltro)))
                ->firstOrFail();
        }

        try {
            // Cajas de la sucursal
            $cajas = Caja::where('sucursal_id', $sucursal->id)
                ->orderBy('situacion', 'ASC')
                ->get();

            // Tomar caja_id del request si viene, si no, la primera disponible
            $caja_id = $cajaFiltro ?? ($cajas->first()->id ?? null);
            $cajaSeleccionada = $caja_id
                ? Caja::whereKey($caja_id)->where('sucursal_id', $sucursal->id)->first()
                : null;
            $caja_id = $cajaSeleccionada?->id;

            // Operaciones (depende del estado de la caja seleccionada)
            $operaciones = Operacione::where('tabla_id', 61)
                ->where('estado', true)
                ->when(
                    $cajaSeleccionada && $cajaSeleccionada->situacion === 'C',
                    fn($q) => $q->whereNotIn('accion', ['movcaja.create', 'movcaja.ingreso', 'movcaja.egreso', 'movcaja.cierre']),
                    fn($q) => $q->where('accion', '!=', 'movcaja.apertura')
                )
                ->get();

            // Filtros desde el request
            $tipoRequest = $request->input('tipo');               // "Ingreso" | "Egreso" | null
            $conceptoId = $request->filled('conceptospago') ? (int) $request->input('conceptospago') : null; // 0 = todos
            $search = trim((string) $request->input('search', ''));
            $fechaInicio = $request->input('fechaInicio');        // YYYY-MM-DD
            $fechaFin = $request->input('fechaFin');           // YYYY-MM-DD
            $perPage = (int) $request->input('perPage', 10);

            // Base query de Movcaja
            $movimientoQuery = Movcaja::with([
                'movimiento.tipomovimiento',
                'detMovcaja.Mediopago',
                'conceptopago'
            ])
                ->where('sucursal_id', $sucursal->id)
                ->when($caja_id, fn($q) => $q->where('caja_id', $caja_id));

            /**
             * FILTRO: TURNO
             * - "0" => todos los movimientos (no se agrega filtro)
             * - id   => rango movcajainicio_id .. movcajafin_id
             * - null => desde la última "Apertura de caja"
             */
            if ($turnoRequest === "0" || $turnoRequest === 0) {
                // sin filtro adicional
            } elseif ($turnoFiltro) {
                $movimientoQuery->where('id', '>=', $turnoFiltro->movcajainicio_id);
                if ($turnoFiltro->movcajafin_id) {
                    $movimientoQuery->where('id', '<=', $turnoFiltro->movcajafin_id);
                }
            } else {
                // sin turno -> desde última apertura
                $ultimoAperturaId = Movcaja::where('sucursal_id', $sucursal->id)
                    ->when($caja_id, fn($q) => $q->where('caja_id', $caja_id))
                    ->whereHas('conceptopago', fn($q) => $q->where('descripcion', 'Apertura de caja'))
                    ->orderBy('id', 'desc')
                    ->value('id');

                if ($ultimoAperturaId) {
                    $movimientoQuery->where('id', '>=', $ultimoAperturaId);
                } else {
                    // Si no existe apertura y no se pasó turno → no mostrar nada
                    $movimientoQuery->whereRaw('1 = 0');
                }
            }

            /**
             * FILTRO: FECHAS (rango)
             * Aplica sobre created_at de movcajas y, si existe, también sobre movimiento.fecha
             */
            if ($fechaInicio && $fechaFin) {
                $movimientoQuery->where(function ($query) use ($fechaInicio, $fechaFin) {
                    $query->whereBetween(DB::raw('DATE(created_at)'), [$fechaInicio, $fechaFin])
                        ->orWhereHas('movimiento', function ($mq) use ($fechaInicio, $fechaFin) {
                        $mq->whereDate('fecha', '>=', $fechaInicio)->whereDate('fecha', '<=', $fechaFin);
                        });
                    });
            } elseif ($fechaInicio) {
                $movimientoQuery->where(function ($query) use ($fechaInicio) {
                    $query->whereDate('created_at', '>=', $fechaInicio)
                        ->orWhereHas('movimiento', function ($mq) use ($fechaInicio) {
                            $mq->whereDate('fecha', '>=', $fechaInicio);
                        });
                    });
            } elseif ($fechaFin) {
                $movimientoQuery->where(function ($query) use ($fechaFin) {
                    $query->whereDate('created_at', '<=', $fechaFin)
                        ->orWhereHas('movimiento', function ($mq) use ($fechaFin) {
                            $mq->whereDate('fecha', '<=', $fechaFin);
                        });
                    });
            }

            /**
             * FILTRO: TIPO (Ingreso/Egreso)
             * Buscamos por la descripción del tipo movimiento en la relación.
             */
            if ($tipoRequest && in_array($tipoRequest, ['Ingreso', 'Egreso'], true)) {
                $movimientoQuery->whereHas('movimiento.tipodocumento', function ($q) use ($tipoRequest) {
                    $q->where('nombre', $tipoRequest);
                });
            }

            /**
             * FILTRO: CONCEPTO DE PAGO
             * conceptospago=0 => no filtra
             * conceptospago>0 => filtra por conceptopago_id
             */
            if (!is_null($conceptoId) && $conceptoId > 0) {
                $movimientoQuery->where('conceptopago_id', $conceptoId);
            }

            /**
             * FILTRO: BÚSQUEDA (search)
             * Busca en campos comunes del propio movcaja y de la relación movimiento
             */
            if ($search !== '') {
                $movimientoQuery->where(function ($q) use ($search) {
                    $term = '%'.$search.'%';
                    $q->where('caja', 'ILIKE', $term)
                        ->orWhere('moneda', 'ILIKE', $term)
                        ->orWhereRaw('CAST(movcaja.total AS TEXT) ILIKE ?', [$term])
                        ->orWhereHas('movimiento', function ($mq) use ($search) {
                            $term = '%'.$search.'%';
                            $mq->where('numero', 'ILIKE', $term)
                                ->orWhere('usuario', 'ILIKE', $term)
                                ->orWhere('comentario', 'ILIKE', $term)
                                ->orWhere('persona', 'ILIKE', $term)
                                ->orWhereHas('Persona.personamaestro', fn($p) => $p->where('nombres', 'ILIKE', $term)
                                    ->orWhere('apellidos', 'ILIKE', $term)->orWhere('nrodoc', 'ILIKE', $term));
                        })
                        ->orWhereHas('conceptopago', fn($concepto) => $concepto->where('descripcion', 'ILIKE', $term));
                });
            }


            // Paginación y orden
            $movcajas = $movimientoQuery->where('sucursal_id', $sucursal->id)->orderBy('id', 'desc')->paginate($perPage);

            // Total de la página (misma lógica que ya usabas)
            $totalPagina = $movcajas->filter(function ($movcaja) {
                return $movcaja->movimiento->situacion === 'Activado' && $movcaja->conceptopago_id != 3;
            })->map(function ($movcaja) {
                $tipoDocumentoId = $movcaja->movimiento->tipodocumento_id;
                $tipoMovimientoId = $movcaja->movimiento->tipomovimiento_id;

                if ((int) $tipoDocumentoId === 7) {
                    return $movcaja->total;
                } elseif ((int) $tipoDocumentoId === 8) {
                    return -$movcaja->total;
                } elseif ($tipoMovimientoId === 2) {
                    return $movcaja->total;
                }

                return 0;
            })->sum();

            $tipos = ['Ingreso', 'Egreso'];
            $conceptospagos = Conceptopago::all();

            $relTurnocajas = RelTurnocaja::where('sucursal_id', $persona->sucursal_id)
                ->orderBy('fechainicio', 'desc')
                ->paginate($perPage);

            return view('movcaja.report', compact(
                'movcajas',
                'relTurnocajas',
                'conceptospagos',
                'tipos',
                'operaciones',
                'cajas',
                'caja_id',
                'totalPagina',
                'cajaSeleccionada'
            ));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
