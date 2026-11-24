<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FarmaciaReportController extends Controller
{
    public function index(Request $request)
    {
        $branch = Persona::whereKey(Auth::user()->persona_id)->value('sucursal_id');
        abort_unless($branch, 403, 'Tu usuario no tiene una sucursal asignada.');
        $filter = $request->query('filtro', 'todos');
        abort_unless(in_array($filter, ['todos', 'por-vencer', 'vencidos', 'stock-bajo'], true), 422);
        $hoy = now()->toDateString();
        $limite = now()->addDays(30)->toDateString();

        $query = DB::table('stockproducto as stock')
            ->join('producto as producto', 'producto.id', '=', 'stock.producto_id')
            ->leftJoin('rel_productosucursal as rel', function ($join) use ($branch) {
                $join->on('rel.producto_id', '=', 'stock.producto_id')
                    ->where('rel.sucursal_id', '=', $branch)->whereNull('rel.deleted_at');
            })
            ->leftJoin('unidad as unidad', 'unidad.id', '=', 'stock.unidadbase_id')
            ->where('stock.sucursal_id', $branch)->whereNull('stock.deleted_at')->whereNull('producto.deleted_at')
            ->select([
                'stock.id', 'stock.producto_id', 'stock.stockbase', 'producto.codigo', 'producto.descripcion',
                'unidad.descripcion as unidad', 'rel.fechavencimiento', 'rel.stockminimo', 'rel.stockmaximo',
            ]);

        if ($filter === 'por-vencer') $query->whereNotNull('rel.fechavencimiento')->whereBetween('rel.fechavencimiento', [$hoy, $limite])->where('stock.stockbase', '>', 0);
        if ($filter === 'vencidos') $query->whereNotNull('rel.fechavencimiento')->where('rel.fechavencimiento', '<', $hoy)->where('stock.stockbase', '>', 0);
        if ($filter === 'stock-bajo') $query->whereRaw('COALESCE(rel.stockminimo, 0) > 0 AND stock.stockbase <= rel.stockminimo');
        if ($request->filled('q')) $query->where(function ($q) use ($request) {
            $term = '%'.mb_strtolower(trim($request->query('q'))).'%';
            $q->whereRaw('LOWER(producto.descripcion) LIKE ?', [$term])->orWhereRaw('LOWER(producto.codigo) LIKE ?', [$term]);
        });

        $rows = $query->orderByRaw('rel.fechavencimiento NULLS LAST')->orderBy('producto.descripcion')->paginate(30)->withQueryString();
        $counts = [
            'vencidos' => DB::table('stockproducto as s')->join('rel_productosucursal as r', function ($join) use ($branch) {
                $join->on('r.producto_id', '=', 's.producto_id')->where('r.sucursal_id', '=', $branch)->whereNull('r.deleted_at');
            })->where('s.sucursal_id', $branch)->whereNull('s.deleted_at')->where('s.stockbase', '>', 0)->whereDate('r.fechavencimiento', '<', $hoy)->count(),
            'por_vencer' => DB::table('stockproducto as s')->join('rel_productosucursal as r', function ($join) use ($branch) {
                $join->on('r.producto_id', '=', 's.producto_id')->where('r.sucursal_id', '=', $branch)->whereNull('r.deleted_at');
            })->where('s.sucursal_id', $branch)->whereNull('s.deleted_at')->where('s.stockbase', '>', 0)->whereBetween('r.fechavencimiento', [$hoy, $limite])->count(),
            'stock_bajo' => DB::table('stockproducto as s')->join('rel_productosucursal as r', function ($join) use ($branch) {
                $join->on('r.producto_id', '=', 's.producto_id')->where('r.sucursal_id', '=', $branch)->whereNull('r.deleted_at');
            })->where('s.sucursal_id', $branch)->whereNull('s.deleted_at')->whereRaw('COALESCE(r.stockminimo, 0) > 0 AND s.stockbase <= r.stockminimo')->count(),
        ];

        return view('farmacia.reportes', compact('rows', 'counts', 'filter'));
    }

    public function utilidades(Request $request)
    {
        $branch = $this->branchId();
        $data = $request->validate(['desde' => 'nullable|date', 'hasta' => 'nullable|date|after_or_equal:desde']);
        $from = $data['desde'] ?? now()->startOfMonth()->toDateString();
        $to = $data['hasta'] ?? now()->toDateString();

        $conversiones = DB::table('listaunidad')
            ->whereNull('deleted_at')
            ->select('producto_id', 'unidad_id', 'unidadbase_id')
            ->selectRaw('MAX(formula) as factor')
            ->groupBy('producto_id', 'unidad_id', 'unidadbase_id');

        $costs = DB::table('det_movcompra as dc')
            ->join('movcompra as mc', 'mc.id', '=', 'dc.movcompra_id')
            ->join('movimiento as purchase_move', 'purchase_move.id', '=', 'mc.movimiento_id')
            ->join('producto as purchase_product', 'purchase_product.id', '=', 'dc.producto_id')
            ->leftJoinSub($conversiones, 'purchase_unit', function ($join) {
                $join->on('purchase_unit.producto_id', '=', 'dc.producto_id')
                    ->on('purchase_unit.unidad_id', '=', 'dc.unidad_id')
                    ->on('purchase_unit.unidadbase_id', '=', 'purchase_product.unidadbase_id');
            })
            ->where('dc.sucursal_id', $branch)->whereNull('dc.deleted_at')->whereNull('mc.deleted_at')
            ->where('mc.afecta_kardex', 'S')->where('purchase_move.situacion', 'A')->whereDate('purchase_move.fecha', '<=', $to)
            ->whereNotNull('dc.producto_id')
            ->where(function ($query) {
                $query->whereColumn('dc.unidad_id', 'purchase_product.unidadbase_id')
                    ->orWhereRaw('purchase_unit.factor > 0');
            })
            ->select('dc.producto_id')
            ->selectRaw("SUM(dc.monto * CASE WHEN mc.moneda = 'USD' THEN COALESCE(mc.tipocambio, 1) ELSE 1 END) / NULLIF(SUM(dc.cantidad * CASE WHEN dc.unidad_id = purchase_product.unidadbase_id THEN 1 ELSE purchase_unit.factor END), 0) as costo_unitario_promedio")
            ->groupBy('dc.producto_id');

        $rows = DB::table('det_movventa as dv')
            ->join('movventa as mv', 'mv.id', '=', 'dv.movventa_id')
            ->join('movimiento as sale_move', 'sale_move.id', '=', 'mv.movimiento_id')
            ->join('producto as p', 'p.id', '=', 'dv.producto_id')
            ->leftJoinSub($conversiones, 'sale_unit', function ($join) {
                $join->on('sale_unit.producto_id', '=', 'dv.producto_id')
                    ->on('sale_unit.unidad_id', '=', 'dv.unidad_id')
                    ->on('sale_unit.unidadbase_id', '=', 'p.unidadbase_id');
            })
            ->leftJoinSub($costs, 'costos', fn ($join) => $join->on('costos.producto_id', '=', 'dv.producto_id'))
            ->where('mv.sucursal_id', $branch)->where('dv.sucursal_id', $branch)
            ->whereNull('mv.deleted_at')->whereNull('dv.deleted_at')->whereNull('p.deleted_at')
            ->where('sale_move.situacion', 'A')->whereBetween('sale_move.fecha', [$from.' 00:00:00', $to.' 23:59:59'])
            ->whereNotNull('dv.producto_id')->select('dv.producto_id', 'p.codigo', 'p.descripcion')
            ->selectRaw('SUM(dv.cantidad * CASE WHEN dv.unidad_id = p.unidadbase_id THEN 1 ELSE COALESCE(sale_unit.factor, 0) END) as unidades_vendidas')
            ->selectRaw('SUM(dv.cantidad * CASE WHEN dv.unidad_id IS DISTINCT FROM p.unidadbase_id AND COALESCE(sale_unit.factor, 0) <= 0 THEN 1 ELSE 0 END) as unidades_sin_conversion')
            ->selectRaw("SUM(dv.monto * (1 - LEAST(GREATEST(COALESCE(dv.dcto_porcentaje, 0), 0), 100) / 100) * CASE WHEN mv.moneda = 'USD' THEN COALESCE(mv.tipocambio, 1) ELSE 1 END) as ventas_pen")
            ->selectRaw('MAX(costos.costo_unitario_promedio) as costo_unitario_promedio')
            ->selectRaw('SUM(dv.cantidad * CASE WHEN dv.unidad_id = p.unidadbase_id THEN 1 ELSE COALESCE(sale_unit.factor, 0) END * COALESCE(costos.costo_unitario_promedio, 0)) as costo_estimado_pen')
            ->groupBy('dv.producto_id', 'p.codigo', 'p.descripcion')->orderBy('p.descripcion')->get()
            ->map(function ($row) {
                $row->sin_costo = $row->costo_unitario_promedio === null || (float) $row->unidades_sin_conversion > 0;
                $row->utilidad_estimada_pen = $row->sin_costo ? null : (float) $row->ventas_pen - (float) $row->costo_estimado_pen;
                return $row;
            });

        $totalVentas = $rows->sum('ventas_pen');
        $totalCosto = $rows->where('sin_costo', false)->sum('costo_estimado_pen');
        $totalUtilidad = $rows->where('sin_costo', false)->sum('utilidad_estimada_pen');
        $sinCosto = $rows->where('sin_costo', true)->count();
        return view('farmacia.utilidades', compact('rows', 'from', 'to', 'totalVentas', 'totalCosto', 'totalUtilidad', 'sinCosto'));
    }
}
