<?php

namespace App\Http\Controllers;

use App\Models\Kardex;
use App\Http\Requests\KardexRequest;
use App\Models\Categorium;
use App\Models\Marca;
use App\Models\Movcaja;
use App\Models\Movventum;
use App\Models\Operacione;
use App\Models\Persona;
use App\Models\RelTurnocaja;
use App\Models\Sucursal;
use Faker\Provider\ar_EG\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Snappy\Facades\SnappyPdf as Pdf;
use Illuminate\Support\Facades\DB;

/**
 * Class KardexController
 * @package App\Http\Controllers
 */
class KardexController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage   = $request->input('perPage', 10);
        $search    = $request->input('search');
        $categoria = $request->input('categoria_id');
        $marca     = $request->input('marca_id');
        $tipo      = $request->input('tipo');
        $compuesto = $request->input('compuesto');
        $situacion = $request->input('situacion');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        // Operaciones
        $operaciones = Operacione::where('tabla_id', 66)
            ->where('estado', true)
            ->get();

        // Usuario y sucursal
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);

        // Query principal con relaciones
        $query = Kardex::with(['producto.categorium', 'producto.marca', 'tipomovimiento', 'tipodocumento'])
            ->where('sucursal_id', $sucursal->id);

        // 🔹 Búsqueda libre
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(usuario) LIKE ?", ['%' . strtolower($search) . '%'])
                    ->orWhereRaw("LOWER(json_producto) LIKE ?", ['%' . strtolower($search) . '%'])
                    ->orWhereRaw("LOWER(moneda) LIKE ?", ['%' . strtolower($search) . '%'])
                    ->orWhereHas('producto', function ($q2) use ($search) {
                        $q2->whereRaw("LOWER(descripcion) LIKE ?", ['%' . strtolower($search) . '%'])
                            ->orWhereRaw("LOWER(codigo) LIKE ?", ['%' . strtolower($search) . '%']);
                    })
                    ->orWhereHas('producto.categorium', function ($q3) use ($search) {
                        $q3->whereRaw("LOWER(descripcion) LIKE ?", ['%' . strtolower($search) . '%']);
                    })
                    ->orWhereHas('producto.marca', function ($q4) use ($search) {
                        $q4->whereRaw("LOWER(descripcion) LIKE ?", ['%' . strtolower($search) . '%']);
                    });
            });
        }

        // 🔹 Filtros
        if ($categoria) {
            $query->whereHas('producto', function ($q) use ($categoria) {
                $q->where('categoria_id', $categoria);
            });
        }

        if ($marca) {
            $query->whereHas('producto', function ($q) use ($marca) {
                $q->where('marca_id', $marca);
            });
        }

        if ($tipo) {
            $query->whereHas('producto', function ($q) use ($tipo) {
                $q->where('tipo', $tipo);
            });
        }

        if ($compuesto !== null && $compuesto !== '') {
            $query->whereHas('producto', function ($q) use ($compuesto) {
                $q->where('compuesto', $compuesto === 'Sí' ? 'S' : 'N');
            });
        }

        if ($situacion !== null && $situacion !== '') {
            $query->where('situacion', $situacion === 'Activado' ? 'E' : 'D');
        }

        if ($fechaDesde) {
            $query->whereDate('fecha', '>=', $fechaDesde);
        }

        if ($fechaHasta) {
            $query->whereDate('fecha', '<=', $fechaHasta);
        }

        // 🔹 Ejecutar query
        $kardex = $query->orderBy('fecha', 'desc')->paginate($perPage);

        // Datos auxiliares para filtros
        $categorias  = Categorium::all();
        $tipos       = ['Producto final', 'Materia prima'];
        $situaciones = ['Activado', 'Desactivado'];
        $marcas      = Marca::all();
        $compuestos  = ['Sí', 'No'];

        return view('kardex.index', compact(
            'kardex',
            'operaciones',
            'categorias',
            'tipos',
            'situaciones',
            'marcas',
            'compuestos'
        ))->with('i', (request()->input('page', 1) - 1) * $kardex->perPage());
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kardex = new Kardex();
        return view('kardex.create', compact('kardex'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KardexRequest $request)
    {
        Kardex::create($request->validated());

        return redirect()->route('kardexes.index')
            ->with('success', 'Kardex created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $kardex = Kardex::find($id);

        return view('kardex.show', compact('kardex'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $kardex = Kardex::find($id);

        return view('kardex.edit', compact('kardex'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(KardexRequest $request, Kardex $kardex)
    {
        $kardex->update($request->validated());

        return redirect()->route('kardexes.index')
            ->with('success', 'Kardex updated successfully');
    }

    public function destroy($id)
    {
        Kardex::find($id)->delete();

        return redirect()->route('kardexes.index')
            ->with('success', 'Kardex deleted successfully');
    }

    public function generarResumenPDF()
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::findOrFail($persona->sucursal_id);

        // $turnocajaId = session('turnocaja_id'); 
        $turnocajaId = 228;

        $ultimoTurno = RelTurnocaja::where('situacion', 'A')
            ->where('id', $turnocajaId)
            ->first();

        $movimientos = Movcaja::with(['detMovcaja.Mediopago', 'conceptopago'])
            ->when($ultimoTurno && $ultimoTurno->movcajafin_id, function ($query) use ($ultimoTurno) {
                return $query->where('id', '<=', $ultimoTurno->movcajafin_id);
            })
            ->when($ultimoTurno, function ($query) use ($ultimoTurno) {
                return $query->where('id', '>=', $ultimoTurno->movcajainicio_id);
            })
            ->whereHas('movimiento', fn($q) => $q->where('situacion', 'A'))
            ->whereHas('conceptopago', fn($q) => $q->where('descripcion', '!=', 'Cierre de caja'))
            ->where('sucursal_id', $sucursal->id)
            ->orderBy('created_at', 'asc')
            ->get();

        $movimientoIds = $movimientos->pluck('movimiento_id');
        $detallesVentaPorProducto = Movventum::with(['detMovventas'])
            ->whereIn('movimiento_id', $movimientoIds)
            ->where('estado', 'N')
            ->get()
            ->flatMap(function ($movventa) {
                return $movventa->detMovventas->map(function ($detalle) {
                    $jsonProducto = json_decode($detalle->json_producto, true);
                    $descripcion = $jsonProducto['descripcion'] ?? 'Sin Descripción';
                    $precioUnitario = $jsonProducto['precio_unitario'] ?? 0;
                    $unidad = $jsonProducto['unidad'] ?? '';
                    $cantidad = $detalle->cantidad;
                    $subtotal = $detalle->monto;
                    $categoria = $detalle->producto->categorium->descripcion ?? 'Sin Categoría';

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
            })
            ->groupBy(fn($item) => $item['descripcion'] . '|' . $item['precio_unitario'])
            ->map(function ($group) {
                $item = $group->first();
                return [
                    'descripcion' => $item['descripcion'],
                    'precio_unitario' => $item['precio_unitario'],
                    'unidad' => $item['unidad'],
                    'cantidad_total' => $group->sum('cantidad'),
                    'subtotal_total' => $group->sum('subtotal'),
                    'categoria' => $item['categoria'],
                    'stock_inicial' => $group->first()['stock_inicial'],
                    'stock_final' => $group->last()['stock_final'],
                ];
            })
            ->values()
            ->groupBy('categoria'); // ✅ Agrupación final por categoría


        $pdf = Pdf::loadView('kardex.kardex_resumen', compact('detallesVentaPorProducto', 'sucursal'));
        return $pdf->stream('resumen_kardex_' . now()->format('Ymd_His') . '.pdf');
    }
}
