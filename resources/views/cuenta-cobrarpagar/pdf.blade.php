<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h1 { font-size: 20px; margin-bottom: 6px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        th { background: #555; color: #fff; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $tipo === 'COBRAR' ? 'Cuentas por cobrar' : 'Cuentas por pagar' }}</h1>
    <p>Total pendiente: PEN {{ number_format($resumen['total'], 2) }} | Documentos: {{ $resumen['documentos'] }}</p>

    <table>
        <thead>
            <tr>
                <th>Numero</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Vencimiento</th>
                <th>Concepto</th>
                <th class="right">Total</th>
                <th class="right">Pagado</th>
                <th class="right">Restante</th>
                <th>Persona</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cuentas as $cuenta)
                <tr>
                    <td>{{ $cuenta->numero }}</td>
                    <td>{{ $cuenta->estado }}</td>
                    <td>{{ optional($cuenta->fecha)->format('Y-m-d H:i') }}</td>
                    <td>{{ optional($cuenta->fechavencimiento)->format('Y-m-d H:i') }}</td>
                    <td>{{ $cuenta->movcaja?->conceptopago?->descripcion }}</td>
                    <td class="right">{{ number_format($cuenta->total, 2) }}</td>
                    <td class="right">{{ number_format((float) $cuenta->totalpagado, 2) }}</td>
                    <td class="right">{{ number_format($cuenta->restante, 2) }}</td>
                    <td>{{ $cuenta->movcaja?->movimiento?->persona }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
