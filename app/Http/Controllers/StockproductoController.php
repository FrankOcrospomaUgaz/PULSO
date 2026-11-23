<?php

namespace App\Http\Controllers;

use App\Models\Stockproducto;
use App\Http\Requests\StockproductoRequest;
use App\Models\Categorium;
use App\Models\Operacione;
use App\Models\Persona;
use App\Models\Sucursal;
use Barryvdh\Snappy\Facades\SnappyPdf as Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class StockproductoController
 * @package App\Http\Controllers
 */
class StockproductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search'); // Obtener el término de búsqueda
        $operaciones = Operacione::where('tabla_id', 65)->where('estado', true)->get();

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);
        $sucursal_id = $sucursal->id;

        // Iniciar la consulta base
        $query = Stockproducto::with('producto') // Cargar la relación para mejorar la eficiencia
            ->where('sucursal_id', $sucursal_id)
            ->whereHas('producto', function ($query) use ($sucursal_id) {
                $query->where('clasificacion', 'Bien')
                    ->whereHas('relProductosucursal', function ($query) use ($sucursal_id) {
                        $query->where('sucursal_id', $sucursal_id);
                    });
            });

        // Aplicar búsqueda si hay un término
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->orWhereHas('producto', function ($subQuery) use ($search) {
                    $subQuery->whereRaw("LOWER(descripcion) LIKE ?", ['%' . strtolower($search) . '%']);
                });
            });
        }

        // Obtener registros paginados con los filtros aplicados
        $stockProductos = $query->orderBy('id', 'asc')->paginate();

        // Obtener las categorías, tipos y situaciones disponibles para los filtros
        $categorias = Categorium::whereNull('categoria_id')
            ->whereHas('relCategoriasucursal', function ($query) use ($sucursal_id) {
                $query->where('sucursal_id', $sucursal_id);
            })
            ->with('categorias')
            ->get();

        $tipos = ['Producto final', 'Componente'];
        $situaciones = ['Activado', 'Desactivado'];

        return view('stockproducto.index', compact('stockProductos', 'operaciones', 'categorias', 'tipos', 'situaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $stockProductos->perPage());
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $stockproducto = new Stockproducto();
        return view('stockproducto.create', compact('stockproducto'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StockproductoRequest $request)
    {
        Stockproducto::create($request->validated());

        return redirect()->route('stockproductos.index')
            ->with('success', 'Stockproducto created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $stockproducto = Stockproducto::find($id);

        return view('stockproducto.show', compact('stockproducto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $stockproducto = Stockproducto::find($id);

        return view('stockproducto.edit', compact('stockproducto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StockproductoRequest $request, Stockproducto $stockproducto)
    {
        $stockproducto->update($request->validated());

        return redirect()->route('stockproductos.index')
            ->with('success', 'Stockproducto updated successfully');
    }

    public function destroy($id)
    {
        Stockproducto::find($id)->delete();

        return redirect()->route('stockproductos.index')
            ->with('success', 'Stockproducto deleted successfully');
    }

    public function filter(Request $request)
    {
        try {
            $sucursal_id = Auth::user()->persona->sucursal_id;
            $operaciones = Operacione::where('tabla_id', 65)->where('estado', true)->get();
            $search = $request->input('search'); // Obtener el término de búsqueda
            $categoria_id = $request->input('categoria');
            $tipo = $request->input('tipo');
            $situacion = $request->input('situacion');
            $perPage = $request->input('perPage', 10); // Valor por defecto: 10

            $query = Stockproducto::with('producto') // Cargar la relación para mejorar la eficiencia
                ->where('sucursal_id', $sucursal_id)
                ->whereHas('producto', function ($query) use ($sucursal_id) {
                    $query->where('clasificacion', 'Bien')
                        ->whereHas('relProductosucursal', function ($query) use ($sucursal_id) {
                            $query->where('sucursal_id', $sucursal_id);
                        });
                });

            if ($categoria_id) {
                $query->whereHas('producto.categorium', function ($q) use ($categoria_id) {
                    $q->where('id', $categoria_id);
                });
            }

            if ($tipo) {
                $query->whereHas('producto', function ($q) use ($tipo) {
                    $q->where('tipo', $tipo);
                });
            }
            if ($situacion) {
                $query->whereHas('producto.relProductosucursal', function ($q) use ($situacion, $sucursal_id) {
                    $q->where('sucursal_id', $sucursal_id);

                    if ($situacion === 'Activado') {
                        $q->where('situacion', 'A');
                    } else {
                        $q->where('situacion', '<>', 'A');
                    }
                });
            }
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->orWhereHas('producto', function ($subQuery) use ($search) {
                        $subQuery->whereRaw("LOWER(descripcion) LIKE ?", ['%' . strtolower($search) . '%']);
                    });
                });
            }

            $stockProductos = $query->orderBy('id', 'asc')->paginate($perPage); // Ahora usa `perPage`

            $categorias = Categorium::whereNull('categoria_id')
                ->whereHas('relCategoriasucursal', function ($q) use ($sucursal_id) {
                    $q->where('sucursal_id', $sucursal_id)->where('tipocarta', '!=', 'NINGUNA');
                })
                ->with('categorias')
                ->get();

            $tipos = ['Producto final', 'Componente'];
            $situaciones = ['Activado', 'Desactivado'];

            return view('stockproducto.index', compact('stockProductos', 'operaciones', 'categorias', 'tipos', 'situaciones'))
                ->with('i', (request()->input('page', 1) - 1) * $stockProductos->perPage());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
    public function generarPDFStock($categoria = null, $tipo = null, $situacion = null)
    {
        // Obtiene la sucursal del usuario autenticado
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::findOrFail($persona->sucursal_id);
        $sucursal_id = $sucursal->id;
        // Consulta base de productos en stock, filtrando por sucursal

            $query = Stockproducto::with('producto') // Cargar la relación para mejorar la eficiencia
                ->where('sucursal_id', $sucursal_id)
                ->whereHas('producto', function ($query) use ($sucursal_id) {
                    $query->where('clasificacion', 'Bien')
                        ->whereHas('relProductosucursal', function ($query) use ($sucursal_id) {
                            $query->where('sucursal_id', $sucursal_id);
                        });
                });

        // Aplicar filtros opcionales si existen y son válidos
        if (!empty($categoria) && is_numeric($categoria)) {
            $query->whereHas('producto.categorium', function ($q) use ($categoria) {
                $q->where('id', $categoria);
            });
        }

        if (!empty($tipo) && is_numeric($tipo)) {
            $query->whereHas('producto', function ($q) use ($tipo) {
                $q->where('tipo', $tipo);
            });
        }

        if (!empty($situacion) && is_numeric($situacion)) {
            $query->whereHas('producto.relProductosucursal', function ($q) use ($situacion) {
                $q->where('situacion', $situacion);
            });
        }

        // Obtener los productos filtrados o todos si no hay filtros aplicados
        $stockProductos = $query->orderBy('producto_id', 'asc')->get();

        // Generar PDF con la vista del reporte
        $pdf = Pdf::loadView('stockproducto.reportepdf', compact('stockProductos', 'sucursal', 'categoria', 'tipo', 'situacion'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream("Stock_Productos.pdf");
    }
}
