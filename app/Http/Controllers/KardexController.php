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
        $filters = $this->validatedReportFilters($request);
        [$query, $branch] = $this->kardexQuery($request, $filters);
        $perPage = (int) ($filters['perPage'] ?? 10);
        $operaciones = Operacione::where('tabla_id', 66)
            ->where('estado', true)
            ->get();
        $kardex = $query->orderByDesc('fecha')->orderByDesc('id')->paginate($perPage)->withQueryString();
        $categorias = Categorium::whereHas('relCategoriasucursal', fn ($q) => $q->where('sucursal_id', $branch))
            ->orderBy('descripcion')->get();
        $tipos       = ['Producto final', 'Materia prima'];
        $situaciones = ['Activado', 'Desactivado'];
        $marcas = Marca::whereHas('productos', fn ($q) => $q->whereHas('relProductosucursal', fn ($s) => $s->where('sucursal_id', $branch)))
            ->orderBy('descripcion')->get();
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

    private function validatedReportFilters(Request $request): array
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:150',
            'categoria_id' => 'nullable|integer',
            'marca_id' => 'nullable|integer',
            'tipo' => 'nullable|string|max:80',
            'compuesto' => 'nullable|in:S,N',
            'situacion' => 'nullable|in:Activado,Desactivado',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'hora_inicio' => 'nullable|date_format:H:i',
            'hora_fin' => 'nullable|date_format:H:i',
            'perPage' => 'nullable|integer|in:10,25,50,100',
        ]);
        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin']) && !empty($filters['hora_inicio']) && !empty($filters['hora_fin'])) {
            $start = $filters['fecha_inicio'].' '.$filters['hora_inicio'];
            $end = $filters['fecha_fin'].' '.$filters['hora_fin'];
            if (strtotime($end) < strtotime($start)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['fecha_fin' => 'La fecha y hora final deben ser posteriores al inicio.']);
            }
        }
        return $filters;
    }

    private function kardexQuery(Request $request, array $filters)
    {
        $branch = (int) $request->user()->persona?->sucursal_id;
        abort_unless($branch, 403, 'Tu usuario no tiene una sucursal asignada.');
        $query = Kardex::with(['producto.categorium', 'producto.marca', 'unidad', 'movimiento.tipodocumento.tipomovimiento', 'movimiento.movventa'])
            ->where('sucursal_id', $branch)->whereNull('deleted_at');

        if (!empty($filters['search'])) {
            $term = mb_strtolower(trim($filters['search']));
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(usuario) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(COALESCE(json_producto::text, \'\')) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(moneda) LIKE ?', ["%{$term}%"])
                    ->orWhereHas('producto', fn ($p) => $p->whereRaw('LOWER(descripcion) LIKE ?', ["%{$term}%"])->orWhereRaw('LOWER(codigo) LIKE ?', ["%{$term}%"]));
            });
        }
        if (!empty($filters['categoria_id'])) $query->whereHas('producto', fn ($q) => $q->where('categoria_id', $filters['categoria_id']));
        if (!empty($filters['marca_id'])) $query->whereHas('producto', fn ($q) => $q->where('marca_id', $filters['marca_id']));
        if (!empty($filters['tipo'])) $query->whereHas('producto', fn ($q) => $q->where('tipo', $filters['tipo']));
        if (!empty($filters['compuesto'])) $query->whereHas('producto', fn ($q) => $q->where('compuesto', $filters['compuesto']));
        if (($filters['situacion'] ?? null) === 'Activado') $query->whereIn('situacion', ['A', 'E']);
        if (($filters['situacion'] ?? null) === 'Desactivado') $query->where('situacion', 'D');

        if (!empty($filters['fecha_inicio'])) {
            $query->where('fecha', '>=', $filters['fecha_inicio'].' '.($filters['hora_inicio'] ?? '00:00').':00');
        }
        if (!empty($filters['fecha_fin'])) {
            $query->where('fecha', '<=', $filters['fecha_fin'].' '.($filters['hora_fin'] ?? '23:59').':59');
        }
        return [$query, $branch];
    }

    public function exportarCsv(Request $request)
    {
        $filters = $this->validatedReportFilters($request);
        [$query] = $this->kardexQuery($request, $filters);
        $filename = 'kardex-'.now()->format('Ymd-His').'.csv';
        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Fecha', 'Código', 'Producto', 'Movimiento', 'Unidad', 'Stock anterior', 'Cantidad', 'Stock actual', 'Precio unitario', 'Moneda', 'Tipo cambio', 'Total', 'Origen', 'Situación'], ';');
            $query->orderBy('fecha')->orderBy('id')->chunk(500, function ($rows) use ($output) {
                foreach ($rows as $row) {
                    fputcsv($output, [
                        $row->fecha, $row->producto?->codigo, $row->producto?->descripcion,
                        $row->movimiento?->tipodocumento?->nombre, $row->unidad?->descripcion,
                        $row->stockanterior, $row->cantidad, $row->stockactual, $row->preciounitario,
                        $row->moneda, $row->tipocambio, $row->total,
                        $row->movimiento?->numero, $row->situacion,
                    ], ';');
                }
            });
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportarPdf(Request $request)
    {
        $filters = $this->validatedReportFilters($request);
        [$query, $branch] = $this->kardexQuery($request, $filters);
        $sucursal = Sucursal::findOrFail($branch);
        $totalRegistros = (clone $query)->count();
        $kardex = $query->orderByDesc('fecha')->orderByDesc('id')->limit(3000)->get();
        $incluidos = $kardex->count();
        $pdf = Pdf::loadView('kardex.reporte-pdf', compact('kardex', 'sucursal', 'totalRegistros', 'incluidos'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('kardex-'.now()->format('Ymd-His').'.pdf');
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
        $branch = (int) Auth::user()->persona?->sucursal_id;
        abort_unless($branch, 403, 'Tu usuario no tiene una sucursal asignada.');
        $kardex = Kardex::with(['producto', 'unidad', 'movimiento.tipodocumento'])
            ->where('sucursal_id', $branch)
            ->findOrFail($id);

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
