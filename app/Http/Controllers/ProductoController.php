<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\Perfile;
use App\Models\Operacione;
use App\Http\Requests\ProductoRequest;
use App\Models\Categorium;
use App\Models\Distribuidor;
use App\Models\Igv;
use App\Models\Listaunidad;
use App\Models\Marca;
use App\Models\Persona;
use App\Models\RelListaunidadsucursal;
use App\Models\RelParametrosucursal;
use App\Models\RelProductosucursal;
use App\Models\Stockproducto;
use App\Models\Subalmacen;
use App\Models\Ubicacion;
use App\Models\Unidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

/**
 * Class ProductoController
 * @package App\Http\Controllers
 */
class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $operaciones = Operacione::where('tabla_id', 55)->where('estado', true)->get();
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);
        $empresa_id = $sucursal->empresa_id;
        $search = $request->input('search');
        $perPage = request()->input('perPage', 10);
        // Filtrar productos por la sucursal del usuario logueado
        $query = Producto::whereHas('relProductosucursal', function ($query) use ($sucursal) {
            $query->where('sucursal_id', $sucursal->id);
        })->orderBy('id', 'asc');

        // Obtener las columnas de la tabla 'pisos'
        // Obtener las columnas de la tabla 'producto'
        $columns = Schema::getColumnListing('producto');

        // Filtrar solo las columnas de tipo texto
        $textColumns = [];
        foreach ($columns as $column) {
            $columnType = DB::select("
        SELECT data_type
        FROM information_schema.columns
        WHERE table_name = 'producto' AND column_name = ?
    ", [$column]);

            if (isset($columnType[0]) && in_array($columnType[0]->data_type, ['character varying', 'text'])) {
                $textColumns[] = $column;
            }
        }

        if ($search) {
            $query->where(function ($q) use ($textColumns, $search) {
                foreach ($textColumns as $column) {
                    $q->orWhereRaw("LOWER($column) LIKE ?", ['%' . strtolower($search) . '%']);
                }
            });
        }

        $productos =  $query->orderBy('id', 'desc')->paginate($perPage);

        return view('producto.index', compact('productos', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $productos->perPage());
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $producto = new Producto();


        $marcas = Marca::all();
        $unidades_base = Unidad::all();
        $unidades = Unidad::where('tipo', 'Masa')->get();
        $distribuidores = Distribuidor::all();
        $ubicaciones = Ubicacion::all();
        $subalmacenes = Subalmacen::all();
        $igvs = Igv::all();

        // Obtener proveedores con rol_id 4
        $proveedores = Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 4);
        })->get();



        // Obtener el usuario autenticado y su empresa
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);
        $empresa_id = $sucursal->empresa_id;
        // Obtener listas de opciones para campos foráneos
        $categorias = Categorium::whereNull('categoria_id')
            ->whereHas('relCategoriasucursal', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id);
            })
            ->with('categorias')
            ->get();
        // Obtener el último código de producto registrado en la sucursal
        $ultimoProducto = Producto::whereHas('relProductoSucursal', function ($query) use ($sucursal) {
            $query->where('sucursal_id', $sucursal->id);
        })->orderBy('codigo', 'desc')->first();
        $sucursal_id = $sucursal->id;
        // Determinar el próximo código
        if ($ultimoProducto) {
            // Extraer el número del último código y aumentar en 1
            $ultimoNumero = (int) substr($ultimoProducto->codigo, 1);
            $nuevoNumero = str_pad($ultimoNumero + 1, 3, '0', STR_PAD_LEFT);
            $ultimo_codigo = 'P' . $nuevoNumero;
        } else {
            // Si no existe ningún producto, comenzar con P001
            $ultimo_codigo = 'P001';
        }

        return view('producto.create', compact(
            'producto',
            'categorias',
            'marcas',
            'unidades',
            'unidades_base',
            'distribuidores',
            'ubicaciones',
            'subalmacenes',
            'igvs',
            'proveedores',
            'empresa_id',
            'sucursal_id',
            'ultimo_codigo' // Pasamos el nuevo código a la vista
        ));
    }



    /**
     * Store a newly created resource in storage.
     */


    public function store(Request $request)
    {
        DB::beginTransaction(); // Iniciar la transacción

        try {
            // Validación manual solo para los campos especificados
            $validator = Validator::make($request->all(), [
                'categoria_id' => 'required|exists:categoria,id',
                'codigo' => 'required|string|max:50',
                'descripcion' => 'required|string|max:255',
                'clasificacion' => 'required|string',
                'unidadbase_id' => 'required|exists:unidad,id',
                'empresa_id' => 'required|exists:empresas,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Crear el producto manualmente con valores por defecto o del formulario
            $producto = new Producto();
            $producto->codigo = $request->input('codigo');
            $producto->descripcion = $request->input('descripcion');
            $producto->abreviatura = $request->input('abreviatura') ?? '';
            $producto->tipo = $request->input('tipo', 'PRODUCTO');
            $producto->categoria_id = $request->input('categoria_id');
            $producto->marca_id = $request->input('marca_id');
            $producto->unidadbase_id = $request->input('unidadbase_id');
            $producto->medidapeso_id = $request->input('medidapeso_id');
            $producto->kardex = $request->has('kardex') ? 'S' : 'N';
            $producto->compuesto = $request->input('compuesto') ? 'S' : 'N';
            $producto->autoproduccion = $request->input('autoproduccion') ? 'S' : 'N';
            $producto->peso = $request->has('peso') ? (float) $request->input('peso') : 0.0;
            $producto->comentario = $request->input('comentario', '') !== null ? $request->input('comentario') : '';
            $producto->nro_parte = $request->input('nro_parte', '') !== null ? $request->input('nro_parte') : '';
            $producto->caracteristicas = $request->input('caracteristicas', '') !== null ? $request->input('caracteristicas') : '';
            $producto->imagen = $request->input('imagen', '');
            $producto->complemento = $request->input('complemento') === 'No permite complementos' ? 'No permite' : $request->input('complemento', 'NO');
            $producto->modocomplemento = $request->input('modocomplemento', '');
            $producto->empresa_id = $request->input('empresa_id');
            $producto->clasificacion = $request->input('clasificacion', 'BIEN');
            $producto->distribuidor_id = $request->input('distribuidor_id');
            $producto->imagen2 = $request->input('imagen2', '');
            $producto->imagen3 = $request->input('imagen3', '');

            $producto->save();
            // Crear registro en `rel_productosucursal`
            $relProductoSucursal = new RelProductosucursal();
            $relProductoSucursal->producto_id = $producto->id;
            $relProductoSucursal->sucursal_id = $request->input('sucursal_id'); // ID de sucursal necesario
            $relProductoSucursal->stockminimo = $request->input('stockminimo', 0);
            $relProductoSucursal->stockmaximo = $request->input('stockmaximo', 0);
            $relProductoSucursal->stockoptimo = $request->input('stockoptimo', 0);
            $relProductoSucursal->minimovender = $request->input('minimovender', 0);
            $relProductoSucursal->minimocomprar = $request->input('minimocomprar', 0);
            $relProductoSucursal->ubicacion_id = $request->input('ubicacion_id');
            $relProductoSucursal->fila = $request->input('fila', 0) !== null ? $request->input('fila') : 0;
            $relProductoSucursal->columna = $request->input('columna', 0) !== null ? $request->input('columna') : 0;
            $relProductoSucursal->subalmacen_id = $request->input('subalmacen_id');

            // GUARDAR el registro antes de buscarlo de nuevo
            $relProductoSucursal->save();

            // Obtener el usuario autenticado y su sucursal
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal = Sucursal::find($persona->sucursal_id);

            // Buscar el valor del IGV predeterminado en la tabla rel_parametrosucursal
            $igvPredeterminado = RelParametrosucursal::where('sucursal_id', $sucursal->id)
                ->where('parametro_id', 8) // ID del parámetro para IGV
                ->value('valor'); // Tomamos directamente el campo 'valor'
            // Si el usuario seleccionó "Por defecto" (0), asignamos el IGV predeterminado

            $igvSeleccionado = $request->input('igv_id') == 0 ? $igvPredeterminado : $request->input('igv_id');
            $igvObjeto = Igv::find($igvSeleccionado);
            // Actualizar `rel_productosucursal`
            $relProductoSucursal = RelProductosucursal::where('producto_id', $producto->id)
                ->where('sucursal_id', $request->input('sucursal_id'))
                ->firstOrFail();

            $relProductoSucursal->igv_id = $igvObjeto->id;;
            $relProductoSucursal->situacion = 'A';

            $relProductoSucursal->venta_unitaria = $request->input('venta_unitaria', 'N');
            $relProductoSucursal->proveedor_id = $request->input('proveedor_id');
            $relProductoSucursal->save();

            // Crear registro en `listaunidad`
            $listaUnidad = new Listaunidad();
            $listaUnidad->formula = $request->input('formula', 1);
            $listaUnidad->producto_id = $producto->id;
            $listaUnidad->unidad_id = $request->input('unidad_id');
            $listaUnidad->unidadbase_id = $request->input('unidadbase_id');
            $listaUnidad->empresa_id = $request->input('empresa_id');
            $listaUnidad->save();

            // Crear registro en `rel_listaunidadsucursal`
            $relListaUnidadSucursal = new RelListaunidadsucursal();
            $relListaUnidadSucursal->preciocompra = $request->input('preciocompra', 0);
            $relListaUnidadSucursal->preciomanoobra = $request->input('preciomanoobra', 0);
            $relListaUnidadSucursal->precioventa = $request->input('precioventa', 0);
            $relListaUnidadSucursal->moneda = $request->input('moneda', 'PEN');
            $relListaUnidadSucursal->listaunidad_id = $listaUnidad->id;
            $relListaUnidadSucursal->sucursal_id = $request->input('sucursal_id');

            // Crear registro en `stockproducto`
            $stockProducto = new Stockproducto();
            $stockProducto->producto_id = $producto->id;
            $stockProducto->unidadbase_id = $request->input('unidadbase_id');
            $stockProducto->sucursal_id = $request->input('sucursal_id');
            $stockProducto->stockbase = $request->input('stockbase', 0); // Asumimos que viene desde el formulario
            $stockProducto->save();
            $relListaUnidadSucursal->save();

            // Confirmar la transacción
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto y relaciones creados exitosamente.',
                'producto' => $producto,
                'redirect_url' => route('producto.index')

            ]);
        } catch (\Exception $e) {
            // Si ocurre un error, revertir la transacción
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Obtener el producto a editar
        $producto = Producto::findOrFail($id);

        // Obtener listas de opciones para campos foráneos
        $marcas = Marca::all();
        $unidades_base = Unidad::all();
        $unidades = Unidad::where('tipo', 'Masa')->get();
        $distribuidores = Distribuidor::all();
        $ubicaciones = Ubicacion::all();
        $subalmacenes = Subalmacen::all();
        $igvs = Igv::all();

        // Obtener proveedores con rol_id 4
        $proveedores = Persona::whereHas('rolpersonas', function ($query) {
            $query->where('rol_id', 4);
        })->get();

        // Obtener el usuario autenticado y su empresa
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);
        $empresa_id = $sucursal->empresa_id;
        $sucursal_id = $sucursal->id;
        $categorias = Categorium::whereNull('categoria_id')
            ->whereHas('relCategoriasucursal', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id);
            })
            ->with('categorias')
            ->get();
        return view('producto.edit', compact(
            'producto',
            'categorias',
            'marcas',
            'unidades',
            'unidades_base',
            'distribuidores',
            'ubicaciones',
            'subalmacenes',
            'igvs',
            'proveedores',
            'empresa_id',
            'sucursal_id'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction(); // Iniciar la transacción

        try {
            // Validación manual solo para los campos especificados
            $validator = Validator::make($request->all(), [
                'categoria_id' => 'required|exists:categoria,id',
                'codigo' => 'required|string|max:50',
                'descripcion' => 'required|string|max:255',
                'clasificacion' => 'required|string',
                'unidadbase_id' => 'required|exists:unidad,id',
                'empresa_id' => 'required|exists:empresas,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Buscar el producto existente
            $producto = Producto::findOrFail($id);

            // Actualizar los campos del producto
            $producto->codigo = $request->input('codigo');
            $producto->descripcion = $request->input('descripcion');
            $producto->abreviatura = $request->input('abreviatura') ?? '';
            $producto->tipo = $request->input('tipo', 'PRODUCTO');
            $producto->categoria_id = $request->input('categoria_id');
            $producto->marca_id = $request->input('marca_id');
            $producto->unidadbase_id = $request->input('unidadbase_id');
            $producto->medidapeso_id = $request->input('unidad_id');
            $producto->kardex = $request->has('kardex') ? 'S' : 'N';
            $producto->compuesto = $request->input('compuesto') ? 'S' : 'N';
            $producto->autoproduccion = $request->input('autoproduccion') ? 'S' : 'N';
            $producto->peso = $request->has('peso') ? (float) $request->input('peso') : 0.0;
            $producto->comentario = $request->input('comentario', '') !== null ? $request->input('comentario') : '';
            $producto->nro_parte = $request->input('nro_parte', '') !== null ? $request->input('nro_parte') : '';
            $producto->caracteristicas = $request->input('caracteristicas', '') !== null ? $request->input('caracteristicas') : '';
            $producto->imagen = $request->input('imagen', '');
            $producto->complemento = $request->input('complemento') === 'No permite complementos' ? 'No permite' : $request->input('complemento', 'NO');
            $producto->modocomplemento = $request->input('modocomplemento', '');
            $producto->empresa_id = $request->input('empresa_id');
            $producto->clasificacion = $request->input('clasificacion', 'BIEN');
            $producto->distribuidor_id = $request->input('distribuidor_id');
            $producto->imagen2 = $request->input('imagen2', '');
            $producto->imagen3 = $request->input('imagen3', '');;

            $producto->save();

            // Actualizar `rel_productosucursal`
            $relProductoSucursal = RelProductosucursal::where('producto_id', $producto->id)
                ->where('sucursal_id', $request->input('sucursal_id'))
                ->firstOrFail();

            $relProductoSucursal->stockminimo = $request->input('stockminimo', 0);
            $relProductoSucursal->stockmaximo = $request->input('stockmaximo', 0);
            $relProductoSucursal->stockoptimo = $request->input('stockoptimo', 0);
            $relProductoSucursal->minimovender = $request->input('minimovender', 0);
            $relProductoSucursal->minimocomprar = $request->input('minimocomprar', 0);
            $relProductoSucursal->ubicacion_id = $request->input('ubicacion_id');
            $relProductoSucursal->fila = $request->input('fila', 0) !== null ? $request->input('fila') : 0;
            $relProductoSucursal->columna = $request->input('columna', 0) !== null ? $request->input('columna') : 0;
            $relProductoSucursal->subalmacen_id = $request->input('subalmacen_id');

            // Obtener el usuario autenticado y su sucursal
            $user = Auth::user();
            $persona = Persona::find($user->persona_id);
            $sucursal = Sucursal::find($persona->sucursal_id);

            // Buscar el valor del IGV predeterminado en la tabla rel_parametrosucursal
            $igvPredeterminado = RelParametrosucursal::where('sucursal_id', $sucursal->id)
                ->where('parametro_id', 8) // ID del parámetro para IGV
                ->value('valor'); // Tomamos directamente el campo 'valor'

            $igvSeleccionado = $request->input('igv_id') == 0 ? $igvPredeterminado : $request->input('igv_id');
            $igvObjeto = Igv::find($igvSeleccionado);

            // Actualizar `rel_productosucursal`
            $relProductoSucursal = RelProductosucursal::where('producto_id', $producto->id)
                ->where('sucursal_id', $request->input('sucursal_id'))
                ->firstOrFail();

            $relProductoSucursal->igv_id = $igvObjeto->id;;
            $relProductoSucursal->situacion = 'A';

            $relProductoSucursal->venta_unitaria = $request->input('venta_unitaria', 'N');
            $relProductoSucursal->proveedor_id = $request->input('proveedor_id');
            $relProductoSucursal->save();

            // Actualizar `listaunidad`
            $listaUnidad = Listaunidad::where('producto_id', $producto->id)
                ->firstOrFail();

            $listaUnidad->formula = $request->input('formula', 1);
            $listaUnidad->unidadbase_id = $request->input('unidadbase_id');
            $listaUnidad->empresa_id = $request->input('empresa_id');
            $listaUnidad->save();

            // Actualizar `rel_listaunidadsucursal`
            $relListaUnidadSucursal = RelListaunidadsucursal::where('listaunidad_id', $listaUnidad->id)
                ->where('sucursal_id', $request->input('sucursal_id'))
                ->firstOrFail();

            $relListaUnidadSucursal->preciocompra = $request->input('preciocompra', 0);
            $relListaUnidadSucursal->preciomanoobra = $request->input('preciomanoobra', 0);
            $relListaUnidadSucursal->precioventa = $request->input('precioventa', 0);
            $relListaUnidadSucursal->moneda = $request->input('moneda', 'PEN');
            $relListaUnidadSucursal->save();


            // Confirmar la transacción
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto y relaciones actualizados exitosamente.',
                'producto' => $producto,
                'redirect_url' => route('producto.index')
            ]);
        } catch (\Exception $e) {
            // Si ocurre un error, revertir la transacción
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction(); // Inicia la transacción para asegurar consistencia en caso de error
        try {
            // Encuentra el producto
            $producto = Producto::findOrFail($id);

            // Elimina registros relacionados en `RelProductosucursal`
            RelProductosucursal::where('producto_id', $producto->id)->delete();

            // Encuentra registros en `Listaunidad` relacionados con el producto
            $listaUnidades = Listaunidad::where('producto_id', $producto->id)->get();

            // Elimina registros en `RelListaunidadsucursal` relacionados con cada `Listaunidad`
            foreach ($listaUnidades as $listaUnidad) {
                RelListaunidadsucursal::where('listaunidad_id', $listaUnidad->id)->delete();
            }

            // Elimina registros en `Listaunidad`
            Listaunidad::where('producto_id', $producto->id)->delete();

            // Finalmente, elimina el producto
            $producto->delete();

            // Confirma la transacción si todo fue bien
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto y registros relacionados eliminados correctamente.',
                'redirect_url' => route('producto.index')
            ]);
        } catch (\Exception $e) {
            // Revierte la transacción en caso de error
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => 'Error al eliminar el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function filtrarPorCategoria($categoriaId, $clasificacion)
    {
        // Obtener la sucursal_id del usuario autenticado
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);

        // Obtener los productos filtrados por categoría, clasificación y sucursal
        $productos = Producto::where('categoria_id', $categoriaId)
            ->where('clasificacion', $clasificacion) // Filtra por clasificación
            ->whereHas('relProductosucursal', function ($query) use ($sucursal) {
                $query->where('sucursal_id', $sucursal->id); // Filtra por sucursal
            })
            ->with([
                'relProductosucursal.igv',
                'relListaUnidadSucursal',
                'unidad',
                'stockproductos' => function ($query) use ($sucursal) {
                    // Obtener el stock de cada producto en la sucursal actual
                    $query->where('sucursal_id', $sucursal->id);
                }
            ]) // Cargar relaciones necesarias
            ->get()
            ->map(function ($producto) {
                // Obtener el precio de venta y el IGV
                $ventaUnit = floatval($producto->relListaUnidadSucursal->precioventa ?? 0);
                $igv = floatval($producto->relProductosucursal->igv->porcentaje ?? 0);
                $user = Auth::user();
                $persona = Persona::find($user->persona_id);
                $sucursal = Sucursal::find($persona->sucursal_id);

                // Obtener el stock del producto, si existe
                $stock = $producto->stockproductos->first(); // Debería haber solo un stock por producto en una sucursal
                $stockBase = $stock ? $stock->stockbase : 0;
                $parametroCarrito = RelParametrosucursal::where('sucursal_id', $sucursal->id)
                    ->where('parametro_id', 11)
                    ->value('valor');
                // Devolver el producto con los campos necesarios
                return [
                    'id' => $producto->id,
                    'descripcion' => $producto->descripcion,
                    'unidad' => $producto->unidad->descripcion ?? 'Unidad',
                    'precio_total' => $ventaUnit * (1 + $igv / 100),
                    'stock' => number_format($stockBase, 2), // Aquí formateamos el stock con 2 decimales
                    'parametroCarrito' => $parametroCarrito,
                ];
            });

        // Retornar los productos como respuesta JSON
        return response()->json($productos);
    }

    public function detalle($id)
    {
        $producto = Producto::with(['relProductosucursal.igv', 'relListaUnidadSucursal'])
            ->findOrFail($id);
        //dd($producto->relProductosucursal->igv->porcentaje_igv );
        $response = [
            'id' => $producto->id,
            'descripcion' => $producto->descripcion,
            'precio_compra' => number_format($producto->relListaUnidadSucursal->preciocompra ?? 0, 2, '.', ''),
            'unidad' => $producto->unidad->descripcion ?? 'Unidad',
            'igv_id' => $producto->relProductosucursal->igv_id ?? null,
            'porcentaje_igv' => $producto->relProductosucursal->igv->porcentaje_igv ?? 0,
            // Calculamos el IGV sobre el precio total
            'igv' => number_format(($producto->relProductosucursal->igv->porcentaje_igv ?? 0) * ($producto->relListaUnidadSucursal->precioventa ?? 0) / 100, 2, '.', ''),
            'precio_unitario' => number_format($producto->relListaUnidadSucursal->precioventa ?? 0, 2, '.', ''),
            'precio_total' => number_format($producto->relListaUnidadSucursal->precioventa ?? 0, 2, '.', ''),
            'codigo' => $producto->codigo,
            'categoria' => $producto->categorium->descripcion,
            'cantidad' => 1,
        ];


        return response()->json($response);
    }
}
