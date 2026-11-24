<?php

namespace App\Http\Controllers;

use App\Models\DetMovalmacen;
use App\Models\Kardex;
use App\Models\Listaunidad;
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
        $userBranch = (int) Auth::user()->persona?->sucursal_id;
        abort_unless($userBranch && (int) $request->input('sucursal_id') === $userBranch, 403, 'La sucursal no corresponde al usuario.');
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
                'detalles_productos_json' => 'required|json',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $detalles = json_decode($request->detalles_productos_json, true);
            if (!is_array($detalles) || $detalles === [] || array_is_list($detalles) === false) {
                DB::rollBack();
                return response()->json(['success' => false, 'errors' => 'Agrega al menos un producto válido al movimiento.'], 422);
            }
            $detallesValidator = Validator::make(['detalles' => $detalles], [
                'detalles' => 'required|array|min:1',
                'detalles.*' => 'required|array',
                'detalles.*.producto_id' => 'required|integer|exists:producto,id',
                'detalles.*.unidad_id' => 'required|integer|exists:unidad,id',
                'detalles.*.cantidad' => 'required|numeric|gt:0',
                'detalles.*.precio_unitario' => 'nullable|numeric|min:0',
            ]);
            if ($detallesValidator->fails()) {
                DB::rollBack();
                return response()->json(['success' => false, 'errors' => $detallesValidator->errors()], 422);
            }
            if (!Persona::whereKey($request->responsable_id)->where('sucursal_id', $userBranch)->exists()
                || ($request->filled('persona_id') && !Persona::whereKey($request->persona_id)->where('sucursal_id', $userBranch)->exists())) {
                DB::rollBack();
                return response()->json(['success' => false, 'errors' => 'El responsable o la persona seleccionada no pertenecen a tu sucursal.'], 422);
            }

            // Datos generales
            $tipodoc = Tipodocumento::findOrFail($request->tipo_documento);
            if (!in_array($tipodoc->abreviatura, ['E', 'S'], true)) {
                DB::rollBack();
                return response()->json(['success' => false, 'errors' => 'El documento debe representar una entrada o una salida de almacén.'], 422);
            }
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
            foreach ($detalles as $detalle) {
                $producto = Producto::whereKey($detalle['producto_id'] ?? 0)
                    ->whereHas('relProductosucursal', fn ($q) => $q->where('sucursal_id', $userBranch))
                    ->lockForUpdate()->firstOrFail();
                $cantidad = (float) ($detalle['cantidad'] ?? 0);
                if ($cantidad <= 0) throw new \InvalidArgumentException('Cada cantidad debe ser mayor que cero.');
                $unidad = Unidad::findOrFail($detalle['unidad_id'] ?? 0);
                $factor = $this->factorUnidadBase($producto, $unidad->id);
                $cantidadBase = $cantidad * $factor;
                $detMovalmacen = DetMovalmacen::create([
                    'movalmacen_id' => $movalmacen->id,
                    'producto_id' => $detalle['producto_id'],
                    'unidad_id' => $detalle['unidad_id'],
                    'cantidad' => $detalle['cantidad'],
                    'comentario' => $detalle['notas'] ?? ' ',
                    'situacion' => 'A',
                    'sucursal_id' => $request->sucursal_id,
                    'json_producto' => json_encode(['producto_nombre' => $producto->descripcion]),
                    'json_listaunidad' => json_encode(['unidad_nombre' => $unidad->descripcion]),
                ]);
                // Actualizar o crear Stockproducto
                $stockProducto = Stockproducto::where('producto_id', $producto->id)
                    ->where('unidadbase_id', $producto->unidadbase_id)->where('sucursal_id', $userBranch)
                    ->lockForUpdate()->first();
                $stockProducto ??= new Stockproducto(['producto_id' => $producto->id, 'unidadbase_id' => $producto->unidadbase_id, 'sucursal_id' => $userBranch, 'stockbase' => 0]);
                $stockAnterior = $stockProducto->stockbase ?? 0;

                // Determinar si es entrada o salida
                if ($tipodoc->abreviatura === 'E') { // Entrada
                    $stockProducto->stockbase = $stockAnterior + $cantidadBase;
                } elseif ($tipodoc->abreviatura === 'S') { // Salida
                    if ($stockAnterior + 0.000001 < $cantidadBase) throw new \InvalidArgumentException("Stock insuficiente para {$producto->descripcion}. Disponible: {$stockAnterior}; requerido: {$cantidadBase}.");
                    $stockProducto->stockbase = $stockAnterior - $cantidadBase;
                } else {
                    throw new \InvalidArgumentException('Tipo de documento no corresponde a una entrada o salida.');
                }
                //dd($tipodoc->abreviatura);

                $stockProducto->save();
                // Crear registro en Kardex
                Kardex::create([
                    'detalle_id' => $detMovalmacen->id,
                    'producto_id' => $detalle['producto_id'],
                    'json_producto' => json_encode($producto),
                    'unidad_id' => $detalle['unidad_id'],
                    'json_listaunidad' => json_encode(['unidad_nombre' => $unidad->descripcion]),
                    'cantidad' => $detalle['cantidad'],
                    'unidadbase_id' => $producto->unidadbase_id,
                    'cantidadbase' => $cantidadBase,
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
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'errors' => $e->getMessage()], 422);
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
        $userBranch = (int) Auth::user()->persona?->sucursal_id;
        abort_unless($userBranch, 403, 'El usuario no tiene sucursal asignada.');
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
            abort_unless($userBranch && (int) $movalmacen->sucursal_id === $userBranch, 403, 'El movimiento no corresponde a tu sucursal.');
            abort_unless((int) $request->sucursal_id === $userBranch, 403, 'La sucursal no corresponde al usuario.');
            if ($movimiento->getRawOriginal('situacion') !== 'A') throw new \RuntimeException('No se puede editar un movimiento anulado.');
            if ($request->filled('detalles_productos_json')) {
                $existingKardex = Kardex::where('movimiento_id', $movimiento->id)->where('situacion', 'A')->exists();
                if ($existingKardex) throw new \RuntimeException('Este movimiento ya afectó inventario; anúlalo y registra uno nuevo para cambiar sus productos.');
            }
            if ((int) $movimiento->tipodocumento_id !== (int) $request->tipo_documento
                && Kardex::where('movimiento_id', $movimiento->id)->where('situacion', 'A')->exists()) {
                throw new \RuntimeException('No se puede cambiar el tipo de un movimiento que ya afectó inventario.');
            }

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
            $userBranch = (int) Auth::user()->persona?->sucursal_id;
            abort_unless($userBranch && (int) $movalmacen->sucursal_id === $userBranch, 403, 'El movimiento no corresponde a tu sucursal.');
            if ($movimiento->getRawOriginal('situacion') !== 'A') throw new \RuntimeException('El movimiento ya está anulado.');
            $entries = Kardex::where('movimiento_id', $movimiento->id)->where('sucursal_id', $userBranch)->where('situacion', 'A')->lockForUpdate()->get();
            foreach ($entries->groupBy(fn ($e) => $e->producto_id.'|'.$e->unidadbase_id) as $group) {
                $entry = $group->first();
                $stock = Stockproducto::where('producto_id', $entry->producto_id)->where('unidadbase_id', $entry->unidadbase_id)->where('sucursal_id', $userBranch)->lockForUpdate()->firstOrFail();
                $qty = (float) $group->sum('cantidadbase');
                $before = (float) $stock->stockbase;
                $isEntry = Tipodocumento::whereKey($movimiento->tipodocumento_id)->value('abreviatura') === 'E';
                if ($isEntry && $before + 0.000001 < $qty) throw new \RuntimeException('No se puede anular la entrada: parte del stock ya fue consumido.');
                $stock->stockbase = $isEntry ? $before - $qty : $before + $qty;
                $stock->save();
                foreach ($group as $entryRow) $entryRow->update(['situacion' => 'D']);
            }
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

    private function factorUnidadBase(Producto $producto, int $unidadId): float
    {
        if ((int) $producto->unidadbase_id === $unidadId) return 1.0;
        $factor = Listaunidad::where('producto_id', $producto->id)->where('unidad_id', $unidadId)
            ->where('unidadbase_id', $producto->unidadbase_id)->value('formula');
        if (!$factor || (float) $factor <= 0) throw new \InvalidArgumentException("No hay conversión configurada a unidad base para {$producto->descripcion}.");
        return (float) $factor;
    }
}
