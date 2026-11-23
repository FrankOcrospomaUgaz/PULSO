<?php

namespace App\Http\Controllers;

use App\Models\DetMovalmacen;
use App\Models\Kardex;
use App\Models\Movalmacen;
use App\Models\Movimiento;
use App\Models\Operacione;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\Stockproducto;
use App\Models\Subalmacen;
use App\Models\Sucursal;
use App\Models\Tipodocumento;
use App\Models\Tipomovimiento;
use App\Models\Unidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MovalmacenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id); // Obtener la sucursal de la persona
        $movalmacenes = Movalmacen::where('sucursal_id', $sucursal->id)
            ->orderBy('id', 'desc') // Ordenar por ID de forma descendente
            ->paginate(10);
        $operaciones = Operacione::where('tabla_id', 59)->where('estado', true)->get();

        return view('movalmacen.index', compact('movalmacenes', 'operaciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Instancia de Movalmacen vacío para creación
        $movalmacen = new Movalmacen();
        // Obtener todas las opciones de subalmacenes
        $subalmacenes = Subalmacen::all();

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id); // Obtener la sucursal de la persona

        // Obtener las personas
        $personas = Persona::where('sucursal_id', $sucursal->id)->get();

        // Formatear los datos para el autocompletado de personas
        // Obtener responsables (usuarios con rol específico, por ejemplo, si hay un rol "Responsable")
        $responsables = Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 1);
        })->where('sucursal_id', $sucursal->id)->get();
        $sucursal_id = $sucursal->id;
        // Obtener todos los productos disponibles para ser agregados al detalle del movimiento

        $productos = Producto::with(['relProductosucursal', 'relListaUnidadSucursal'])
            ->whereHas('relProductosucursal', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id);
            })
            ->get();
        // Formatear los datos de productos para el autocompletado
        // Obtener todas las unidades para los productos
        $unidades = Unidad::all();
        $tipos_documento = Tipodocumento::whereIn('id', [5, 6])->get();
        return view('movalmacen.create', compact(
            'movalmacen',
            'subalmacenes',
            'personas',
            'responsables',
            'productos',
            'unidades',
            'sucursal_id',
            'tipos_documento'
        ));
    }

    public function entrada()
    {
        return $this->createMovalmacenView('entrada');
    }

    public function salida()
    {
        return $this->createMovalmacenView('salida');
    }

    /**
     * Método privado para generar la vista del formulario en base al tipo
     */
    private function createMovalmacenView($tipo)
    {
        $movalmacen = new Movalmacen();
        $subalmacenes = Subalmacen::all();

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id); // Obtener la sucursal de la persona

        $personas = Persona::where('sucursal_id', $sucursal->id)->get();

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);

        $responsables = Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 1);
        })->where('sucursal_id', $sucursal->id)->get();

        $sucursal_id = $sucursal->id;
        $productos = Producto::with(['relProductosucursal', 'relListaUnidadSucursal'])
            ->whereHas('relProductosucursal', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id);
            })
            ->get();
        // Formatear los datos de productos para el autocompletado
        $unidades = Unidad::whereIn('id', [1, 4, 5])->get();
        $tipos_documento = Tipodocumento::whereIn('id', [5, 6])->get();

        if ($tipo == 'salida') {
            $tipodocumento_id = 6;
        } else {
            $tipodocumento_id = 5;
        }
        // dd($tipo);
        return view('movalmacen.create', compact(
            'movalmacen',
            'subalmacenes',
            'personas',
            'responsables',
            'productos',
            'unidades',
            'sucursal_id',
            'tipos_documento',
            'tipo',
            'tipodocumento_id'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validación
            $validator = Validator::make($request->all(), [
                'sucursal_id' => 'required|exists:sucursal,id',
                'tipo_documento' => 'required|exists:tipodocumento,id',
                'fecha' => 'required|date',
                'responsable_id' => 'required|exists:persona,id',
                'persona_id' => 'nullable|exists:persona,id',
                'comentario' => 'nullable|string',
                'detalles_productos_json' => 'required|json', // Validación del JSON
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Datos generales
            $tipodoc = Tipodocumento::find($request->tipo_documento);
            $usuario = Auth::user();
            $persona = $request->persona_id ? Persona::find($request->persona_id) : null;
            $responsable = Persona::find($request->responsable_id);

            // Crear Movimiento
            $movimiento = Movimiento::create([
                'numero' => $this->generateNumero(),
                'fecha' => now(), // Obtiene la fecha y hora actual
                'usuario_id' => $usuario->id,
                'usuario' => $usuario->name,
                'persona_id' => $request->persona_id,
                'persona' => $persona ? $persona->nombres . ' ' . $persona->apellidos : '',
                'responsable_id' => $request->responsable_id,
                'responsable' => $responsable->nombres . ' ' . $responsable->apellidos,
                'comentario' => $request->comentario ?? ' ',
                'situacion' => 'A',
                'tipomovimiento_id' => $tipodoc->tipomovimiento_id,
                'tipodocumento_id' => $request->tipo_documento,
                'sucursal_id' => $request->sucursal_id,
                'movimiento_id' => $request->movimiento_id ?? null,
            ]);

            // Crear Movalmacen
            $movalmacen = Movalmacen::create([
                'movimiento_id' => $movimiento->id,
                'sucursal_id' => $request->sucursal_id,
                'comentario' => $request->comentario ?? ' ',
            ]);

            // Crear detalles de productos en DetMovalmacen
            $detalles = json_decode($request->detalles_productos_json, true);
            foreach ($detalles as $detalle) {
                $detMovalmacen = DetMovalmacen::create([
                    'movalmacen_id' => $movalmacen->id,
                    'producto_id' => $detalle['producto_id'],
                    'unidad_id' => $detalle['unidad_id'],
                    'cantidad' => $detalle['cantidad'],
                    'comentario' => $detalle['notas'] ?? ' ',
                    'situacion' => 'A',
                    'sucursal_id' => $request->sucursal_id,
                    'json_producto' => json_encode([
                        'producto_nombre' => $detalle['producto_nombre']
                    ]),
                    'json_listaunidad' => json_encode([
                        'unidad_nombre' => $detalle['unidad_nombre']
                    ]),
                ]);
                // Actualizar o crear Stockproducto
                $stockProducto = Stockproducto::firstOrNew([
                    'producto_id' => $detalle['producto_id'],
                    'unidadbase_id' => $detalle['unidad_id'],
                    'sucursal_id' => $request->sucursal_id,
                ]);
                $stockAnterior = $stockProducto->stockbase ?? 0;

                // Determinar si es entrada o salida
                if ($tipodoc->abreviatura === 'E') { // Entrada
                    $stockProducto->stockbase = $stockAnterior + $detalle['cantidad'];
                } elseif ($tipodoc->abreviatura === 'S') { // Salida
                    $stockProducto->stockbase = $stockAnterior - $detalle['cantidad'];
                }
                //dd($tipodoc->abreviatura);

                $stockProducto->save();
                $producto = Producto::find($detalle['producto_id']);
                // Crear registro en Kardex
                Kardex::create([
                    // 'detalle_id' => $detMovalmacen->id,
                    'producto_id' => $detalle['producto_id'],
                    'json_producto' => json_encode([
                        $producto
                    ]),
                    'unidad_id' => $detalle['unidad_id'],
                    'json_listaunidad' => json_encode([
                        'unidad_nombre' => $detalle['unidad_nombre']
                    ]),
                    'cantidad' => $detalle['cantidad'],
                    'unidadbase_id' => $detalle['unidad_id'],
                    'cantidadbase' => $detalle['cantidad'], // Si la unidad base es igual a la unidad
                    'preciounitario' => $detalle['precio_unitario'] ?? 0,
                    'moneda' => $detalle['moneda'] ?? 'PEN',
                    'tipocambio' => $detalle['tipocambio'] ?? 1,
                    'total' => ($detalle['precio_unitario'] ?? 0) * $detalle['cantidad'],
                    'fecha' => now(),
                    'situacion' => 'A',
                    'usuario_id' => $usuario->id,
                    'usuario' => $usuario->name,
                    'movimiento_id' => $movimiento->id,
                    'tipomovimiento_id' => $tipodoc->tipomovimiento_id,
                    'tipodocumento_id' => $request->tipo_documento,
                    'sucursal_id' => $request->sucursal_id,
                    'stockanterior' => $stockAnterior,
                    'stockactual' => $stockProducto->stockbase,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento de almacén creado exitosamente.',
                'movalmacen' => $movalmacen,
                'redirect_url' => route('movalmacen.index')
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
     * Método para generar un número único para el movimiento (ejemplo).
     */
    private function generateNumero()
    {
        // Implementa tu lógica para generar un número único
        return 'MOV-' . strtoupper(uniqid());
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Obtener el movimiento de almacén específico
        $movalmacen = Movalmacen::with('movimiento', 'sucursal')->findOrFail($id);

        // Obtener el usuario autenticado y su sucursal
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);
        // Obtener listas relacionadas para mostrar en los selects
        $subalmacenes = Subalmacen::all();
        $personas = Persona::where('sucursal_id', $sucursal->id)->get();

        $sucursal_id = $sucursal->id;
        // Obtener responsables (usuarios con rol específico en la misma sucursal)
        $responsables = Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 1); // Ajusta el rol_id si es necesario
        })->where('sucursal_id', $movalmacen->sucursal_id)->get();

        // Obtener todos los productos para el detalle del movimiento
        $productos = Producto::with(['relProductosucursal', 'relListaUnidadSucursal'])
            ->whereHas('relProductosucursal', function ($query) use ($sucursal_id) {
                $query->where('sucursal_id', $sucursal_id);
            })
            ->get();
        // Obtener las unidades y tipos de documento permitidos
        $unidades = Unidad::whereIn('id', [1, 4, 5])->get();
        $tipos_documento = Tipodocumento::whereIn('id', [5, 6])->get();

        // Obtener los detalles del movimiento
        $detalles = DetMovalmacen::with('producto', 'unidad')
            ->where('movalmacen_id', $id)
            ->get();

        return view('movalmacen.edit', compact(
            'movalmacen',
            'subalmacenes',
            'personas',
            'responsables',
            'productos',
            'unidades',
            'tipos_documento',
            'detalles',
            'sucursal_id'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // Validación de los datos recibidos
            $validator = Validator::make($request->all(), [
                'sucursal_id' => 'required|exists:sucursal,id',
                'tipo_documento' => 'required|exists:tipodocumento,id',
                'fecha' => 'required|date',
                'responsable_id' => 'required|exists:persona,id',
                'persona_id' => 'nullable|exists:persona,id',
                'comentario' => 'nullable|string',
                'detalles_productos_json' => 'nullable|json', // Ahora es opcional
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Obtener el Movalmacen y Movimiento existentes
            $movalmacen = Movalmacen::findOrFail($id);
            $movimiento = $movalmacen->movimiento;

            // Actualizar el Movimiento
            $movimiento->update([
                'fecha' => $request->fecha,
                'responsable_id' => $request->responsable_id,
                'comentario' => $request->comentario ?? ' ',
                'persona_id' => $request->persona_id,
                'sucursal_id' => $request->sucursal_id,
                'tipodocumento_id' => $request->tipo_documento,
            ]);

            // Actualizar el Movalmacen
            $movalmacen->update([
                'sucursal_id' => $request->sucursal_id,
                'comentario' => $request->comentario ?? ' ',
            ]);

            // Procesar los detalles de productos
            if ($request->filled('detalles_productos_json')) {
                $detalles = json_decode($request->detalles_productos_json, true);
                $nuevosProductosIds = collect($detalles)->pluck('producto_id')->toArray();

                // Eliminar detalles que ya no están en la lista enviada
                DetMovalmacen::where('movalmacen_id', $movalmacen->id)
                    ->whereNotIn('producto_id', $nuevosProductosIds)
                    ->delete();

                // Recorrer y actualizar o crear los detalles de productos
                foreach ($detalles as $detalle) {
                    DetMovalmacen::updateOrCreate(
                        [
                            'movalmacen_id' => $movalmacen->id,
                            'producto_id' => $detalle['producto_id']
                        ],
                        [
                            'unidad_id' => $detalle['unidad_id'],
                            'cantidad' => $detalle['cantidad'],
                            'comentario' => $detalle['notas'] ?? ' ',
                            'situacion' => 'A',
                            'sucursal_id' => $request->sucursal_id,
                            'json_producto' => json_encode([
                                'producto_nombre' => $detalle['producto_nombre']
                            ]),
                            'json_listaunidad' => json_encode([
                                'unidad_nombre' => $detalle['unidad_nombre']
                            ]),
                        ]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento de almacén actualizado exitosamente.',
                'movalmacen' => $movalmacen,
                'redirect_url' => route('movalmacen.index')
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
            $movalmacen = Movalmacen::findOrFail($id);
            $movalmacen->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento de almacén eliminado correctamente.',
                'redirect_url' => route('movalmacen.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function anular($id)
    {
        DB::beginTransaction();

        try {
            $movalmacen = Movalmacen::findOrFail($id);
            $movimiento = Movimiento::find($movalmacen->movimiento_id);
            $movimiento->update(['situacion' => 'D']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento de almacén anulado correctamente.',
                'redirect_url' => route('movalmacen.index')
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
        // Buscar el movimiento de almacén específico
        $movalmacen = Movalmacen::with(['movimiento', 'sucursal'])->findOrFail($id);

        // Obtener los detalles del movimiento a través de la relación DetMovalmacen
        $detalles = DetMovalmacen::with('producto', 'unidad')
            ->where('movalmacen_id', $id)
            ->get();

        // Obtener listas relacionadas para mostrar en los selects
        $subalmacenes = Subalmacen::all();
        $responsables = Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 1); // Ajusta el rol_id si es necesario
        })->where('sucursal_id', $movalmacen->sucursal_id)->get();
        $unidades = Unidad::whereIn('id', [1, 4, 5])->get();
        $tipos_documento = Tipodocumento::whereIn('id', [5, 6])->get();

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id); // Obtener la sucursal de la persona

        // Retornar la vista de detalles con los datos obtenidos
        return view('movalmacen.detalles', compact('movalmacen', 'detalles', 'subalmacenes', 'responsables', 'unidades', 'tipos_documento'));
    }
}
