<?php

namespace App\Http\Controllers;

use App\Models\RelTurnocaja;
use App\Http\Requests\RelTurnocajaRequest;
use App\Models\Anulacion;
use App\Models\Caja;
use App\Models\DetMovventum;
use App\Models\Kardex;
use App\Models\HistorialHabitacion;

use App\Models\Mediopago;
use App\Models\Movcaja;
use App\Models\Movhabitacion;
use App\Models\Movventum;
use App\Models\Operacione;
use App\Models\Persona;
use App\Models\RelMovcajadinero;
use App\Models\Sucursal;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class RelTurnocajaController
 * @package App\Http\Controllers
 */
class RelTurnocajaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);

        $query = RelTurnocaja::where('sucursal_id', $persona->sucursal_id)
            ->orderBy('fechainicio', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('fechainicio', 'ILIKE', "%{$search}%")
                    ->orWhere('fechafin', 'ILIKE', "%{$search}%")
                    ->orWhere('situacion', 'ILIKE', "%{$search}%");
            });
        }

        $relTurnocajas = $query->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage,
        ]);

        $sucursal = Sucursal::findOrFail($persona->sucursal_id);

        $operaciones = Operacione::where('tabla_id', 69)
            ->where('estado', true)
            ->get();

        return view('rel-turnocaja.index', compact('relTurnocajas', 'sucursal', 'operaciones', 'search', 'perPage'))
            ->with('i', (request()->input('page', 1) - 1) * $relTurnocajas->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create($sucursal_id)
    {
        $movcajas = Movcaja::where('sucursal_id', $sucursal_id)
            ->where('estado', true)
            ->pluck('descripcion', 'id');

        $relTurnocaja = new RelTurnocaja();
        return view('rel-turnocaja.create', compact('relTurnocaja', 'movcajas', 'sucursal_id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RelTurnocajaRequest $request)
    {
        DB::beginTransaction();

        try {
            $relTurnocaja = RelTurnocaja::create($request->validated());
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Turno de caja creado exitosamente.',
                'relTurnocaja' => $relTurnocaja,
                'redirect_url' => route('rel-turnocaja.index', $relTurnocaja->sucursal_id),
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
     * Display the specified resource.
     */
    public function show($id)
    {
        $relTurnocaja = RelTurnocaja::findOrFail($id);
        return view('rel-turnocaja.show', compact('relTurnocaja'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $relTurnocaja = RelTurnocaja::findOrFail($id);
        $movcajas = Movcaja::where('sucursal_id', $relTurnocaja->sucursal_id)
            ->where('estado', true)
            ->pluck('descripcion', 'id');

        return view('rel-turnocaja.edit', compact('relTurnocaja', 'movcajas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RelTurnocajaRequest $request, RelTurnocaja $relTurnocaja)
    {
        try {
            $relTurnocaja->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Turno de caja actualizado exitosamente.',
                'relTurnocaja' => $relTurnocaja,
                'redirect_url' => route('rel-turnocaja.index', $relTurnocaja->sucursal_id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, $sucursal_id)
    {
        try {
            RelTurnocaja::findOrFail($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Turno de caja eliminado correctamente.',
                'redirect_url' => route('rel-turnocaja.index', $sucursal_id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activar un turno de caja.
     */
    public function activar($id)
    {
        try {
            $relTurnocaja = RelTurnocaja::findOrFail($id);
            $relTurnocaja->update(['situacion' => 'A']);

            return redirect()->route('rel-turnocaja.index', $relTurnocaja->sucursal_id)
                ->with('success', 'Turno de caja activado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    /**
     * Desactivar un turno de caja.
     */
    public function desactivar($id)
    {
        try {
            $relTurnocaja = RelTurnocaja::findOrFail($id);
            $relTurnocaja->update(['situacion' => 'I']);

            return redirect()->route('rel-turnocaja.index', $relTurnocaja->sucursal_id)
                ->with('success', 'Turno de caja desactivado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function arqueoPDF($turnocajaId)
    {
        try {
            // Obtener información de la caja y la sucursal
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal = Sucursal::findOrFail($persona->sucursal_id);
            // Obtener el    último turno activo o cerrado para la caja
            $ultimoTurno = RelTurnocaja::where('situacion', 'A') // Turno activo
                ->where('id', $turnocajaId) // Turno cerrado
                ->first();
            //dd($ultimoTurno);
            // Obtener movimientos solo del último turno
            $movimientos = Movcaja::with(['detMovcaja.Mediopago', 'conceptopago'])
                ->when($ultimoTurno && $ultimoTurno->movcajafin_id, function ($query) use ($ultimoTurno) {
                    return $query->where('id', '<=', $ultimoTurno->movcajafin_id);
                })
                ->when($ultimoTurno, function ($query) use ($ultimoTurno) {
                    $query->where('id', '>=', $ultimoTurno->movcajainicio_id);
                })
                ->whereHas('movimiento', function ($query) {
                    $query->where('situacion', 'A'); // Filtrar movimientos con situación 'A'
                })
                ->whereHas('conceptopago', function ($query) {
                    $query->where('descripcion', '!=', 'Cierre de caja'); // Excluir los de "Cierre de caja"
                })
                ->where('sucursal_id', $sucursal->id)
                ->orderBy('created_at', 'asc')
                ->get();



            // Agrupar detalles por Medio de Pago y Tipo de Concepto de Pago
            $detallesAgrupados = $movimientos->flatMap->detMovcaja->groupBy(function ($detalle) {
                $tipoConcepto = $detalle->movcaja->conceptopago->tipo;
                $conceptoDescripcion = $detalle->movcaja->conceptopago->descripcion;

                // Construir el detalle completo para agrupación
                $detalles = [];
                if (!empty($detalle->tarjeta_id)) {
                    $detalles[] = $detalle->tarjeta;
                }
                if (!empty($detalle->banco_id)) {
                    $detalles[] =  $detalle->banco;
                }
                if (!empty($detalle->billeteradigital_id)) {
                    $detalles[] =  $detalle->billeteradigital;
                }
                if (!empty($detalle->pos_id)) {
                    $detalles[] =  $detalle->pos;
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
                        $detalles[] =  $detalle->banco;
                    }
                    if (!empty($detalle->billeteradigital_id)) {
                        $detalles[] =  $detalle->billeteradigital;
                    }
                    if (!empty($detalle->pos_id)) {
                        $detalles[] =  $detalle->pos;
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

                    // Excluir "Cierre de caja" de la suma del monto
                    'monto' => $items->filter(fn($item) => $item->movcaja->conceptopago->descripcion !== 'Cierre de caja')->sum('monto'),

                    'movcaja_ids' => $items->pluck('movcaja_id')->toArray(),
                    'detalles' => $detalles, // Agregar el campo "detalles" unificado

                ];
            });

        
            $totales = [
                'Ventas | Efectivo' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->movventa;
                })->flatMap->detMovcaja->where('mediopago_id', 1)->where('tipo', 'Pagado')->sum('monto'),

                'Ventas | Tarjeta' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->movventa;
                })->flatMap->detMovcaja->where('mediopago_id', 2)->where('tipo', 'Pagado')->sum('monto'),

                'Ventas | Transferencia' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->movventa;
                })->flatMap->detMovcaja->where('mediopago_id', 3)->where('tipo', 'Pagado')->sum('monto'),

                'Ventas | Depósito' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->movventa;
                })->flatMap->detMovcaja->where('mediopago_id', 4)->where('tipo', 'Pagado')->sum('monto'),

                'Ventas | Billetera digital' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->movventa;
                })->flatMap->detMovcaja->where('mediopago_id', 5)->where('tipo', 'Pagado')->sum('monto'),

                'Apertura' => $movimientos->flatMap->detMovcaja->where('tipo', 'Pagado')->where(function ($detalle) {
                    return $detalle->movcaja->conceptopago->descripcion === 'Apertura de caja';
                })->sum('monto'),

                'Otros ingresos' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->movimiento->movimiento_id == null && $movcaja->conceptopago_id != 2 && $movcaja->movimiento->tipodocumento_id === 7;
                })->flatMap->detMovcaja->where('tipo', 'Pagado')->sum('monto'),

                // Convertir Egresos a negativo:
                'Egresos (gastos)' => $movimientos->flatMap->detMovcaja->where('tipo', 'Pagado')->where(function ($detalle) {
                    return $detalle->movcaja->conceptopago->tipo === 'Egreso' && $detalle->movcaja->conceptopago_id != 3;
                })->sum('monto') * (-1),

                // Nuevo: Total sin apertura
                'Total sin apertura' => $movimientos->flatMap->detMovcaja->where('tipo', 'Pagado')->filter(function ($detalle) {
                    return $detalle->movcaja->conceptopago_id != 3 && $detalle->movcaja->conceptopago->descripcion !== 'Apertura de caja';
                })->sum(function ($detalle) {
                    return $detalle->movcaja->conceptopago->tipo === 'Egreso' ? -$detalle->monto : $detalle->monto;
                }),
                'efectivo_caja' =>
                $movimientos->filter(function ($movcaja) {
                    return $movcaja->movimiento->tipodocumento_id == 7;
                })->flatMap->detMovcaja->where('mediopago_id', 1)->where('tipo', 'Pagado')->sum('monto')

                    + $movimientos->filter(function ($movcaja) {
                        return $movcaja->movimiento->tipodocumento->tipomovimiento_id == 2 && $movcaja->conceptopago_id == 4;
                    })->flatMap->detMovcaja->where('mediopago_id', 1)->where('tipo', 'Pagado')->sum('monto')

                    - $movimientos->filter(function ($movcaja) {
                        return $movcaja->movimiento->tipodocumento_id == 8;
                    })->flatMap->detMovcaja->where('mediopago_id', 1)->where('tipo', 'Pagado')->sum('monto'),
                // Total con apertura (igual que antes)
                'Total + apertura' => $movimientos->flatMap->detMovcaja->where('tipo', 'Pagado')->filter(function ($detalle) {
                    return $detalle->movcaja->conceptopago_id != 3;
                })->sum(function ($detalle) {
                    return $detalle->movcaja->conceptopago->tipo === 'Egreso' ? -$detalle->monto : $detalle->monto;
                }),
            ];



            // Obtener todos los medios de pago
            $mediosPago = Mediopago::where('situacion', true)->get();

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


            // Agrupar movimientos por medio de pago
                 $ventasPorMetodos = $mediosPago->map(function ($medio) use ($movimientos) {
                $movimientosPorMetodo = $movimientos->filter(function ($movcaja) use ($medio) {
                    return $movcaja->movventa && // Ventas
                        $movcaja->detMovcaja->where('mediopago_id', $medio->id)->isNotEmpty();
                })->flatMap->detMovcaja->where('mediopago_id', $medio->id);

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


            $movimientosanulados = Movcaja::with(['detMovcaja.Mediopago', 'conceptopago'])
                ->when($ultimoTurno && $ultimoTurno->movcajafin_id, function ($query) use ($ultimoTurno) {
                    return $query->where('id', '<=', $ultimoTurno->movcajafin_id);
                })
                ->when($ultimoTurno, function ($query) use ($ultimoTurno) {
                    $query->where('id', '>=', $ultimoTurno->movcajainicio_id);
                })
                ->whereHas('movimiento', function ($query) {
                    $query->where('situacion', 'D'); // Filtrar movimientos con situación 'A'
                })
                ->where('sucursal_id', $sucursal->id)
                ->orderBy('created_at', 'asc')
                ->get();

            //dd($historialhabitacion); 

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
            // dd( $detallesVentaPorProducto   );
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


            $historialhabitacionPago = HistorialHabitacion::where('relturnocaja_id', $turnocajaId)
                ->get()
                ->groupBy('movhabitacion_id')
                ->map(function ($items) use ($turnocajaId) {
                    $ultimoRegistro = $items->sortByDesc('created_at')->first(); // Obtiene el último registrado

                    // Verificar si el pago ya fue realizado en un turno ANTERIOR
                    $tienePagoEnTurnoAnterior = HistorialHabitacion::where('movhabitacion_id', $ultimoRegistro->movhabitacion_id)
                        ->where('relturnocaja_id', '<', $turnocajaId) // Solo en turnos anteriores
                        ->where('situacion_pago', 'S')
                        ->exists();

                    return $tienePagoEnTurnoAnterior ? null : $ultimoRegistro;
                })
                ->filter() // Remueve los nulos (los que ya fueron pagados en otro turno)
                ->values();




            //  dd( $movhabitacioneshistorial);

            // Filtrar movimientos de habitaciones con situación 'C' relacionados al turno



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
                return  $hab->situacion_checkout == 'S';
            })->sum('movhabitacion.total');
            $totalHabitacionessincheck = $movhabitacionessincheckoutpagadas->filter(function ($hab) {
                return empty($hab->fecha_salida_real);
            })->sum('movhabitacion.total');
            $totalHabitacionessincheckEnDeuda = $movhabitacionessinpagar->filter(function ($hab) {
                return empty($hab->fecha_salida_real);
            })->sum('movhabitacion.total');
            $totalHabitaciones = $historialhabitacionPago->filter(function ($hab) {
                return $hab->situacion_pago !== 'N';
            })->sum('movhabitacion.total');

            // Egresos por métodos de pago
            $egresosPagados = [];
            foreach ($mediosPago as $medio) {
                $totalEgresosPorMetodo = $movimientos->flatMap->detMovcaja->where(function ($detalle) use ($medio) {
                    return $detalle->movcaja->conceptopago->tipo === 'Egreso' && $detalle->movcaja->conceptopago_id !== 3 &&
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
                    return $detalle->movcaja->conceptopago->tipo === 'Ingreso' &&  $detalle->movcaja->conceptopago_id !== 4 && $detalle->movcaja->conceptopago_id !== 2 &&
                        $detalle->mediopago_id == $medio->id;
                })->sum('monto');

                if ($totalIngresosPorMetodo > 0) {
                    $ingresosPagados[$medio->descripcion] = $totalIngresosPorMetodo;
                }
            }

            $ventasPorTipoDocumento = Movventum::with('movimiento.tipodocumento')
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
                                'persona'   => $persona,
                                'subtotal'  => $totalOriginal,
                                'descuento' => $descuento,
                            ];
                        });
                })
                ->groupBy('persona')
                ->map(function ($items) {
                    return [
                        'subtotal'  => $items->sum('subtotal'),
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
            $anulaciones = Anulacion::with(['usuario', 'movimiento'])
                ->whereHas('movimiento', function ($q) use ($sucursal) {
                    $q->where('sucursal_id', $sucursal->id);
                })
                ->orderBy('fecha', 'desc')
                ->get();


            $movcajadinero = RelMovcajadinero::where('movcaja_id', $ultimoTurno->movcajafin_id)
                ->with('dinero') // importante para acceder a nombre/valor
                ->get();

            $caja = Caja::findOrFail($ultimoTurno->movcajainicio->Caja->id);

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
                'anulacionesVentasPorCategoria' => $anulacionesVentas,
                'anulacionesProductosPorCategoria' => $anulacionesProductos,
                'consolidadoPorCategoria' => $consolidadoPorCategoria,
                'movhabitacionessinpagar' => $movhabitacionessinpagar,
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
                'descuentosPorPersona' => $descuentosPorPersona,
                'anulacionesAlojamientos' => $anulacionesAlojamientos,

            ];
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


            //  $pdf = PDF::loadView('movcaja.pdf_cierre', $data)
            //            ->setOption('enable-local-file-access', true);

            //             return $pdf->stream('cierre_caja_' . $caja->numero . '.pdf');


        } catch (\Exception $e) {

            return response()->json(['error' => 'Error al generar el PDF: ' . $e->getMessage()]);
        }
    }

    public function arqueoTicket($turnocajaId)
    {
        try {
            // Obtener el último turno activo o cerrado para la caja
            $ultimoTurno = RelTurnocaja::find($turnocajaId);
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal = Sucursal::findOrFail($persona->sucursal_id);
            // Obtener movimientos solo del último turno
            $movimientos = Movcaja::with(['detMovcaja.Mediopago', 'conceptopago'])
                ->when($ultimoTurno && $ultimoTurno->movcajafin_id, function ($query) use ($ultimoTurno) {
                    return $query->where('id', '<=', $ultimoTurno->movcajafin_id);
                })
                ->when($ultimoTurno, function ($query) use ($ultimoTurno) {
                    $query->where('id', '>=', $ultimoTurno->movcajainicio_id);
                })
                ->whereHas('movimiento', function ($query) {
                    $query->where('situacion', 'A'); // Filtrar movimientos con situación 'A'
                })
                ->where('sucursal_id', $sucursal->id)
                ->orderBy('created_at', 'asc')
                ->get();

            // Agrupar detalles por Medio de Pago y Tipo de Concepto de Pago
            $detallesAgrupados = $movimientos->flatMap->detMovcaja->groupBy(function ($detalle) {
                $tipoConcepto = $detalle->movcaja->conceptopago->tipo;
                $conceptoDescripcion = $detalle->movcaja->conceptopago->descripcion;

                // Construir el detalle completo para agrupación
                $detalles = [];
                if (!empty($detalle->tarjeta_id)) {
                    $detalles[] =  $detalle->tarjeta;
                }
                if (!empty($detalle->banco_id)) {
                    $detalles[] = $detalle->banco;
                }
                if (!empty($detalle->billeteradigital_id)) {
                    $detalles[] =  $detalle->billeteradigital;
                }
                if (!empty($detalle->pos_id)) {
                    $detalles[] =  $detalle->pos;
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
                        $detalles[] =  $detalle->tarjeta;
                    }
                    if (!empty($detalle->banco_id)) {
                        $detalles[] =  $detalle->banco;
                    }
                    if (!empty($detalle->billeteradigital_id)) {
                        $detalles[] =  $detalle->billeteradigital;
                    }
                    if (!empty($detalle->pos_id)) {
                        $detalles[] =  $detalle->pos;
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

            $totales = [
                'Ventas | Efectivo' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->movimiento->tipodocumento->tipomovimiento_id == 2;
                })->flatMap->detMovcaja->where('mediopago_id', 1)->where('tipo', 'Pagado')->sum('monto'),

                'Ventas | Tarjeta' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->movimiento->tipodocumento->tipomovimiento_id == 2;
                })->flatMap->detMovcaja->where('mediopago_id', 2)->where('tipo', 'Pagado')->sum('monto'),

                'Ventas | Transferencia' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->movimiento->tipodocumento->tipomovimiento_id == 2;
                })->flatMap->detMovcaja->where('mediopago_id', 3)->where('tipo', 'Pagado')->sum('monto'),

                'Ventas | Depósito' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->movimiento->tipodocumento->tipomovimiento_id == 2;
                })->flatMap->detMovcaja->where('mediopago_id', 4)->where('tipo', 'Pagado')->sum('monto'),

                'Ventas | Billetera digital' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->movimiento->tipodocumento->tipomovimiento_id == 2;
                })->flatMap->detMovcaja->where('mediopago_id', 5)->where('tipo', 'Pagado')->sum('monto'),

                'Apertura' => $movimientos->flatMap->detMovcaja->where('tipo', 'Pagado')->where(function ($detalle) {
                    return $detalle->movcaja->conceptopago->descripcion === 'Apertura de caja';
                })->sum('monto'),

                'Otros ingresos' => $movimientos->filter(function ($movcaja) {
                    return $movcaja->movimiento->tipodocumento_id == 7 && $movcaja->conceptopago_id != 2;
                })->flatMap->detMovcaja->where('tipo', 'Pagado')->sum('monto'),

                // Convertir Egresos a negativo:
                'Egresos (gastos)' => $movimientos->flatMap->detMovcaja->where('tipo', 'Pagado')->where(function ($detalle) {
                    return $detalle->movcaja->conceptopago->tipo === 'Egreso' && $detalle->movcaja->conceptopago_id != 3;
                })->sum('monto') * (-1),

                // Nuevo: Total sin apertura
                'Total sin apertura' => $movimientos->flatMap->detMovcaja->where('tipo', 'Pagado')->filter(function ($detalle) {
                    return $detalle->movcaja->conceptopago_id != 3 && $detalle->movcaja->conceptopago->descripcion !== 'Apertura de caja';
                })->sum(function ($detalle) {
                    return $detalle->movcaja->conceptopago->tipo === 'Egreso' ? -$detalle->monto : $detalle->monto;
                }),

                // Total con apertura (igual que antes)
                'Total + apertura' => $movimientos->flatMap->detMovcaja->where('tipo', 'Pagado')->filter(function ($detalle) {
                    return $detalle->movcaja->conceptopago_id != 3;
                })->sum(function ($detalle) {
                    return $detalle->movcaja->conceptopago->tipo === 'Egreso' ? -$detalle->monto : $detalle->monto;
                }),
            ];


            // Obtener todos los medios de pago
            $mediosPago = Mediopago::where('situacion', true)->get();
            // Agrupar movimientos por medio de pago
            $ventasPorMetodos = $mediosPago->map(function ($medio) use ($movimientos) {
                $movimientosPorMetodo = $movimientos->filter(function ($movcaja) use ($medio) {
                    return $movcaja->movimiento->tipodocumento->tipomovimiento_id == 2 && // Ventas
                        $movcaja->detMovcaja->where('mediopago_id', $medio->id)->isNotEmpty();
                })->flatMap->detMovcaja->where('mediopago_id', $medio->id);

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
                    return $movcaja->movimiento->tipodocumento->tipomovimiento_id == 2 &&
                        $movcaja->detMovcaja->where('mediopago_id', $medio->id)->isNotEmpty();
                })->flatMap->detMovcaja->where('mediopago_id', $medio->id)->sum('monto');

                if ($totalPorMetodo > 0) {
                    $ventasPagadas[$medio->descripcion] = $totalPorMetodo;
                }
            }

            // Obtener información de la caja y la sucursal
            $caja = Caja::findOrFail($ultimoTurno->movcajainicio->caja_id);
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
                        return $movventa->detMovventas->map(function ($detalle) {
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
            $historialhabitacionPago = HistorialHabitacion::where('relturnocaja_id',  $ultimoTurno->id)
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
                return  $hab->situacion_checkout == 'S';
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
                    return $detalle->movcaja->conceptopago->tipo === 'Ingreso' &&  $detalle->movcaja->conceptopago_id !== 4 && $detalle->movcaja->conceptopago_id !== 2 &&
                        $detalle->mediopago_id == $medio->id;
                })->sum('monto');

                if ($totalIngresosPorMetodo > 0) {
                    $ingresosPagados[$medio->descripcion] = $totalIngresosPorMetodo;
                }
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
}
