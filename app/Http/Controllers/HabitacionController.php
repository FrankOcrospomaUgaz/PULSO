<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Http\Requests\HabitacionRequest;
use App\Models\MovhabitacionHabitacion;
use App\Models\MovreservaHabitacion;
use App\Models\Operacione;
use App\Models\Persona;
use App\Models\Piso;
use App\Models\Tipohabitacion;
use App\Services\HabitacionAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


/**
 * Class HabitacionController
 * @package App\Http\Controllers
 */
class HabitacionController extends Controller
{


    public function index(Request $request)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $perPage = $request->input('perPage', 10);

        $query = Habitacion::with(['piso', 'tipohabitacion'])
            ->where('sucursal_id', $persona->sucursal_id);

        $this->aplicarFiltrosHabitacionIndex($query, $request);

        $habitacions = $query->orderBy('numero', 'asc')
            ->paginate($perPage)
            ->appends($request->query());

        $operaciones = Operacione::where('tabla_id', 21)->where('estado', true)->get();
        $situaciones = ['Ocupada', 'Disponible', 'En limpieza', 'En mantenimiento'];
        $tipohabitaciones = Tipohabitacion::where('sucursal_id', $persona->sucursal_id)->get();
        $pisos = Piso::where('sucursal_id', $persona->sucursal_id)->get();

        return view('habitacion.index', compact('habitacions', 'operaciones', 'situaciones', 'tipohabitaciones', 'pisos'))
            ->with('i', (request()->input('page', 1) - 1) * $habitacions->perPage());
    }

    private function aplicarFiltrosHabitacionIndex($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'ILIKE', "%{$search}%")
                    ->orWhere('situacion', 'ILIKE', "%{$search}%")
                    ->orWhere('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhereHas('piso', function ($sub) use ($search) {
                        $sub->where('nombre', 'ILIKE', "%{$search}%");
                    })
                    ->orWhereHas('tipohabitacion', function ($sub) use ($search) {
                        $sub->where('nombre', 'ILIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('situacion')) {
            $mapaSituacion = [
                'Ocupada' => 'Ocupada',
                'Disponible' => 'Disponible',
                'Disponibles' => 'Disponible',
                'En limpieza' => 'Limpieza',
                'En mantenimiento' => 'Mantenimiento',
            ];
            $situacion = $mapaSituacion[$request->situacion] ?? $request->situacion;
            $query->where('situacion', $situacion);
        }

        if ($request->filled('piso')) {
            $query->whereHas('piso', function ($sub) use ($request) {
                $sub->where('nombre', $request->piso);
            });
        }

        if ($request->filled('tipohabitacion')) {
            $query->whereHas('tipohabitacion', function ($sub) use ($request) {
                $sub->where('nombre', $request->tipohabitacion);
            });
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $habitacion = new Habitacion();
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal_id = $persona->sucursal_id;
        // Obtener todos los tipos de habitación
        $tipohabitaciones = Tipohabitacion::where('sucursal_id', $sucursal_id)->get();
        $pisos = Piso::where('sucursal_id', $sucursal_id)->get();
        return view('habitacion.create', compact('habitacion', 'tipohabitaciones', 'pisos', 'sucursal_id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HabitacionRequest $request)
    {
        try {
            // Obtener el estado del switch
            $switchState = $request->input('switch_state', 'unitario');
            $tipohabitacion = Tipohabitacion::find($request->input('tipohabitacion_id'));
            if ($switchState === 'unitario') {
                // Modo Unitario: Crear una sola habitación
                $habitacion = Habitacion::create([
                    'numero' => $request->input('numero'),
                    'situacion' => $request->input('situacion', 'Disponible'), // Valor por defecto
                    'piso_id' => $request->input('piso_id'),
                    'tipohabitacion_id' => $request->input('tipohabitacion_id'),
                    'descripcion' => Tipohabitacion::find($request->input('tipohabitacion_id'))->descripcion,
                    'sucursal_id' => $request->input('sucursal_id'),
                    'precio' => $tipohabitacion->precio,

                ]);
            } else {
                // Modo Por Bloques: Crear múltiples habitaciones
                $inicio = $request->input('inicio_habitacion');
                $fin = $request->input('fin_habitacion');

                if ($inicio > $fin) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El número de habitación inicial no puede ser mayor que el número de habitación final.'
                    ], 400);
                }

                for ($i = $inicio; $i <= $fin; $i++) {
                    Habitacion::create([
                        'numero' => $request->input('formato') . $i,
                        'situacion' => 'Disponible', // Asumimos que 'Disponible' es el estado por defecto
                        'piso_id' => $request->input('piso_id'),
                        'tipohabitacion_id' => $request->input('tipohabitacion_id'),
                        'descripcion' => Tipohabitacion::find($request->input('tipohabitacion_id'))->descripcion,
                        'precio' => Tipohabitacion::find($request->input('tipohabitacion_id'))->precio,

                        'sucursal_id' => $request->input('sucursal_id')
                    ]);
                }
            }

            $pagina_actual = $request->input('pagina_actual', 1);

            return response()->json([
                'success' => true,
                'message' => 'Habitación(es) creada(s) exitosamente.',
                'redirect_url' => route('habitacion.index', ['page' => $pagina_actual])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $habitacion = Habitacion::find($id);

        return view('habitacion.show', compact('habitacion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $habitacion = Habitacion::find($id);
        // Obtener todos los tipos de habitación
        $tipohabitaciones = Tipohabitacion::where('sucursal_id', $persona->sucursal_id)->orderBy('id')->get();

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal_id = $persona->sucursal_id;
        $pisos = Piso::where('sucursal_id', $persona->sucursal_id)->orderBy('id')->get();

        return view('habitacion.edit', compact('habitacion', 'tipohabitaciones', 'pisos', 'sucursal_id'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(HabitacionRequest $request, Habitacion $habitacion)
    {
        DB::beginTransaction(); // Iniciar transacción

        try {
            // Actualizar la habitación con los datos validados
            $habitacion->update([
                'numero' => $request->input('numero'),
                'piso_id' => $request->input('piso_id'),
                'tipohabitacion_id' => $request->input('tipohabitacion_id'),
                'descripcion' => $request->input('descripcion'),
                'precio' => $request->input('precio'),

                'sucursal_id' => $request->input('sucursal_id')
            ]);

            DB::commit(); // Confirmar la transacción

            return response()->json([
                'success' => true,
                'message' => 'Habitación actualizada exitosamente.',
                'habitacion' => $habitacion,
                'redirect_url' => route('habitacion.index', ['page' => $request->input('pagina_actual', 1)])
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir la transacción en caso de error

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
        try {
            Habitacion::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Habitación eliminada correctamente.',
                'redirect_url' => route('habitacion.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function destroyMany(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        try {
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursalId = $persona->sucursal_id;

            $habitaciones = Habitacion::where('sucursal_id', $sucursalId)
                ->whereIn('id', $request->input('ids'))
                ->get();

            $eliminadas = 0;
            $omitidas = [];

            foreach ($habitaciones as $habitacion) {
                if ($habitacion->situacion === 'Ocupada') {
                    $omitidas[] = $habitacion->numero;
                    continue;
                }

                $habitacion->delete();
                $eliminadas++;
            }

            if ($eliminadas === 0) {
                return response()->json([
                    'success' => false,
                    'message' => count($omitidas) > 0
                        ? 'No se eliminaron habitaciones ocupadas: ' . implode(', ', $omitidas)
                        : 'No se encontraron habitaciones para eliminar.',
                ], 422);
            }

            $mensaje = "Se eliminaron {$eliminadas} habitación(es) correctamente.";
            if (count($omitidas) > 0) {
                $mensaje .= ' No se eliminaron ocupadas: ' . implode(', ', $omitidas) . '.';
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'redirect_url' => route('habitacion.index', ['page' => $request->input('pagina_actual', 1)]),
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
        return $this->index($request);
    }
    public function habitaciones($sucursal_id, $tipohabitacion_id = null)
    {
        try {
            $habitacionesQuery = Habitacion::with('tipohabitacion', 'piso')
                ->where('sucursal_id', $sucursal_id);

            // Si se proporciona un tipo de habitación, filtrar por ese tipo
            if ($tipohabitacion_id) {
                $habitacionesQuery->where('tipohabitacion_id', $tipohabitacion_id);
            }

            $habitaciones = $habitacionesQuery->orderBy('numero')->get()->groupBy('piso_id');

            // Mapear las habitaciones para incluir los campos de tipohabitacion con la descripción separada
            $habitacionesData = $habitaciones->map(function ($habitacionesPorPiso) {
                return $habitacionesPorPiso->map(function ($habitacion) {
                    // Separar la descripción de tipohabitacion en un array
                    $descripcionArray = explode('+', $habitacion->tipohabitacion->descripcion);

                    return [
                        'id' => $habitacion->id,
                        'numero' => $habitacion->numero,
                        'situacion' => $habitacion->situacion,
                        'tipohabitacion' => [
                            'id' => $habitacion->tipohabitacion->id,
                            'nombre' => $habitacion->tipohabitacion->nombre,
                            'precio' => $habitacion->tipohabitacion->precio,
                            'capacidad' => $habitacion->tipohabitacion->capacidad,
                            'descripcion' => $descripcionArray, // Descripción como array
                            'imagenes' => $habitacion->tipohabitacion->imagenes, // Imágenes asociadas al tipo de habitación
                        ],
                        'piso' => $habitacion->piso,
                    ];
                });
            });

            return response()->json([
                'status' => true,
                'message' => 'Habitaciones obtenidas correctamente.',
                'data' => $habitacionesData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al obtener habitaciones.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function habitacionesDisponibles($fecha, $fechafin, $sucursal_id, $tipohabitacion_id = null)
    {
        try {
            $habitacionesOcupadas = MovreservaHabitacion::whereHas('movreserva', function ($q) use ($sucursal_id) {
                $q->whereHas('movimiento', function ($query) {
                    $query->where('situacion', '!=', 'D');
                })
                    ->where('sucursal_id', $sucursal_id);
            })
                ->whereRaw("
            ?::date < (fechacheckin + INTERVAL '1 day' * dias)::date
            AND
            ?::date > fechacheckin::date
        ", [$fecha, $fechafin])
                ->pluck('habitacion_id');

            $habitacionesDisponiblesQuery = Habitacion::with('tipohabitacion')
                ->whereNotIn('id', $habitacionesOcupadas)
                ->where('sucursal_id', $sucursal_id);

            if ($tipohabitacion_id) {
                $habitacionesDisponiblesQuery->where('tipohabitacion_id', $tipohabitacion_id);
            }

            $habitacionesDisponibles = $habitacionesDisponiblesQuery->orderBy('numero')->get();

            $tiposHabitacionDisponibles = $habitacionesDisponibles->groupBy('tipohabitacion_id');

            $data = $tiposHabitacionDisponibles->map(function ($habitaciones) {
                return [
                    'tipohabitacion' => [
                        'id' => $habitaciones->first()->tipohabitacion->id,
                        'nombre' => $habitaciones->first()->tipohabitacion->nombre,
                        'precio' => $habitaciones->first()->tipohabitacion->precio,
                        'capacidad' => $habitaciones->first()->tipohabitacion->capacidad,
                        'descripcion' => explode('+', $habitaciones->first()->tipohabitacion->descripcion),
                        'imagenes' => $habitaciones->first()->tipohabitacion->imagenes,
                    ],
                    'habitaciones' => $habitaciones->map(function ($habitacion) {
                        return [
                            'id' => $habitacion->id,
                            'numero' => $habitacion->numero,
                            'situacion' => $habitacion->situacion,
                            'piso' => $habitacion->piso->nombre,
                        ];
                    })
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Habitaciones disponibles agrupadas por tipo obtenidas correctamente.',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al obtener habitaciones disponibles.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function verificarEstadoHabitacion($habitacionId)
    {
        try {
            // Buscar el movhabitacion_habitacion más reciente relacionado a esa habitación
            $detalle = MovhabitacionHabitacion::where('habitacion_id', $habitacionId)
                ->orderByDesc('id')
                ->first();

            if (!$detalle) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró información de la habitación.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'situacion_pago' => $detalle->situacion,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar estado de pago.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function cambiaraocupada($id)
    {
        try {
            // Encontramos el Movhabitacion
            $relacion = Habitacion::findOrFail($id);

            // Actualizamos la situacion de Movhabitacion
            $relacion->update(['situacion' => 'Ocupada']);

            // Retornamos una respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Habitacion actualizada exitosamente.',
                'perfil' => $relacion,
                'redirect_url' => route('habitacion.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
        public function cambiaradisponible($id)
    {
        try {
            // Encontramos el Movhabitacion
            $relacion = Habitacion::findOrFail($id);

            if (!HabitacionAvailability::markAvailableIfNoActiveStay((int) $relacion->id)) {
                return response()->json(['success' => false, 'errors' => 'No se puede liberar la habitación porque tiene un alojamiento activo.'], 409);
            }

            // Retornamos una respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Habitacion actualizada exitosamente.',
                'perfil' => $relacion,
                'redirect_url' => route('habitacion.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
