<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de kárdex</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; color: #263238; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .meta { color: #546e7a; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #b0bec5; padding: 4px; text-align: right; }
        th { background: #e8eef1; }
        th.left, td.left { text-align: left; }
        tr { page-break-inside: avoid; }
        .notice { margin: 8px 0; padding: 6px; background: #fff3cd; }
    </style>
</head>
<body>
    <h1>Reporte de kárdex</h1>
    <div class="meta">{{ $sucursal->nombrecomercial }} · Generado {{ now()->format('d/m/Y H:i') }} · {{ $incluidos }} de {{ $totalRegistros }} movimientos</div>
    @if($totalRegistros > $incluidos)<div class="notice">El PDF muestra los 3,000 movimientos más recientes que coinciden. Usa la exportación CSV para descargar el resultado completo.</div>@endif
    <table>
        <thead><tr><th>Fecha</th><th class="left">Código / producto</th><th>Documento</th><th>Stock ant.</th><th>Cant.</th><th>Stock act.</th><th>P. unitario</th><th>Moneda</th><th>Total</th><th>Situación</th></tr></thead>
        <tbody>
        @forelse($kardex as $item)
            <tr>
                <td>{{ $item->fecha?->format('d/m/Y H:i') ?? '—' }}</td>
                <td class="left">{{ $item->producto?->codigo }} · {{ $item->producto?->descripcion ?? 'Producto eliminado' }}</td>
                <td>{{ $item->movimiento?->tipodocumento?->nombre ?? '—' }} {{ $item->movimiento?->numero }}</td>
                <td>{{ number_format((float)$item->stockanterior, 2) }}</td><td>{{ number_format((float)$item->cantidadbase, 2) }}</td>
                <td>{{ number_format((float)$item->stockactual, 2) }}</td><td>{{ number_format((float)$item->preciounitario, 2) }}</td>
                <td>{{ $item->moneda }}</td><td>{{ number_format((float)$item->total, 2) }}</td><td>{{ $item->situacion }}</td>
            </tr>
        @empty
            <tr><td colspan="10" style="text-align:center">No hay movimientos con estos filtros.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
