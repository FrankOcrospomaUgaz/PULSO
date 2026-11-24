<?php

namespace App\Services;

use App\Models\DetMovventum;
use App\Models\Kardex;
use App\Models\Listaunidad;
use App\Models\Movventum;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\RelListaunidadsucursal;
use App\Models\Stockproducto;
use App\Models\Unidad;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventarioVentaService
{
    public function registrarSalida(array $line, DetMovventum $detail, Movventum $sale, Movimiento $movement, $user): void
    {
        $productId = (int) ($line['producto_id'] ?? 0);
        if (!$productId) return;

        $product = Producto::whereKey($productId)
            ->whereHas('relProductosucursal', fn ($q) => $q->where('sucursal_id', $sale->sucursal_id)->where('situacion', 'A'))
            ->lockForUpdate()->first();
        if (!$product) throw ValidationException::withMessages(['detalles_json' => 'Un producto de la venta no pertenece a la sucursal activa.']);
        if ($product->getRawOriginal('kardex') !== 'S') return;

        $quantity = (float) ($line['cantidad'] ?? 0);
        if ($quantity <= 0) throw ValidationException::withMessages(['detalles_json' => "La cantidad de {$product->descripcion} debe ser mayor que cero."]);

        $unit = Unidad::where('descripcion', trim((string) ($line['unidad'] ?? '')))->first();
        if (!$unit && (int) $product->unidadbase_id) $unit = Unidad::find($product->unidadbase_id);
        if (!$unit) throw ValidationException::withMessages(['detalles_json' => "No se pudo identificar la unidad de {$product->descripcion}."]);

        $listUnit = Listaunidad::where('producto_id', $product->id)->where('unidad_id', $unit->id)
            ->where('unidadbase_id', $product->unidadbase_id)->first();
        $factor = $listUnit ? (float) $listUnit->formula : ((int) $unit->id === (int) $product->unidadbase_id ? 1 : 0);
        if ($factor <= 0) throw ValidationException::withMessages(['detalles_json' => "La unidad de {$product->descripcion} no tiene conversión configurada a su unidad base."]);
        $baseQuantity = $quantity * $factor;

        $stock = Stockproducto::where('producto_id', $product->id)->where('sucursal_id', $sale->sucursal_id)
            ->where('unidadbase_id', $product->unidadbase_id)->lockForUpdate()->first();
        if (!$stock || (float) $stock->stockbase + 0.000001 < $baseQuantity) {
            $available = (float) ($stock->stockbase ?? 0);
            throw ValidationException::withMessages(['detalles_json' => "Stock insuficiente para {$product->descripcion}. Disponible: {$available}; requerido: {$baseQuantity}."]);
        }

        $before = (float) $stock->stockbase;
        $stock->stockbase = $before - $baseQuantity;
        $stock->save();
        $price = (float) ($line['precio_compra'] ?? 0);
        if ($price <= 0 && $listUnit) {
            $price = (float) (RelListaunidadsucursal::where('listaunidad_id', $listUnit->id)
                ->where('sucursal_id', $sale->sucursal_id)->value('preciocompra') ?? 0);
        }

        Kardex::create([
            'detalle_id' => $detail->id, 'producto_id' => $product->id, 'json_producto' => json_encode($product),
            'unidad_id' => $unit->id, 'json_listaunidad' => json_encode([
                'unidad_nombre' => $unit->descripcion, 'unidadbase_id' => $product->unidadbase_id,
                'unidadbase_nombre' => Unidad::whereKey($product->unidadbase_id)->value('descripcion'), 'formula' => $factor,
            ]),
            'cantidad' => $quantity, 'unidadbase_id' => $product->unidadbase_id, 'cantidadbase' => $baseQuantity,
            'preciounitario' => $price, 'moneda' => $sale->moneda ?: 'PEN', 'tipocambio' => $sale->tipocambio ?: 1,
            'total' => $price * $quantity, 'fecha' => $movement->fecha, 'situacion' => 'A',
            'usuario_id' => $user->id, 'usuario' => $user->name, 'movimiento_id' => $movement->id,
            'tipomovimiento_id' => $movement->tipomovimiento_id, 'tipodocumento_id' => $movement->tipodocumento_id,
            'sucursal_id' => $sale->sucursal_id, 'stockanterior' => $before, 'stockactual' => $stock->stockbase,
        ]);
    }

    public function revertirSalida(Movventum $sale, Movimiento $movement): bool
    {
        $entries = Kardex::where('movimiento_id', $movement->id)
            ->where('tipomovimiento_id', $movement->tipomovimiento_id)->where('sucursal_id', $sale->sucursal_id)
            ->where('situacion', 'A')->whereNull('deleted_at')->lockForUpdate()->get();
        if ($entries->isEmpty()) return false;

        foreach ($entries->groupBy(fn ($entry) => $entry->producto_id.'|'.$entry->unidadbase_id) as $group) {
            $entry = $group->first();
            $stock = Stockproducto::where('producto_id', $entry->producto_id)->where('sucursal_id', $sale->sucursal_id)
                ->where('unidadbase_id', $entry->unidadbase_id)->lockForUpdate()->first();
            if (!$stock) {
                throw ValidationException::withMessages(['inventario' => 'No se encontró la existencia para revertir el kárdex de la venta.']);
            }
            $before = (float) $stock->stockbase;
            $quantity = (float) $group->sum('cantidadbase');
            $stock->stockbase = $before + $quantity;
            $stock->save();
            foreach ($group as $item) $item->update(['situacion' => 'D', 'stockanterior' => $before, 'stockactual' => $stock->stockbase]);
        }
        return true;
    }
}
