<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen Kardex</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        .header-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .header-cell {
            display: table-cell;
            text-align: left;
            font-weight: bold;
            padding: 2px 5px;
        }

        .header-cell.center {
            text-align: center;
        }

        .header-cell.right {
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th, td {
            border: 1px solid #333;
            padding: 5px;
            text-align: center;
        }

        th {
            background-color: #eee;
        }

        .categoria {
            font-weight: bold;
            font-size: 12px;
            text-align: left;
            background-color: #f8f8f8;
            padding-left: 5px;
        }
    </style>
</head>
<body>

{{-- ✅ Encabezado alineado como plantilla física --}}
<div class="header-grid">
    <div class="header-cell">FECHA: {{ now()->format('d/m/Y') }}</div>
    <div class="header-cell center">TURNO: {{ $sucursal->descripcion }}</div>
    <div class="header-cell right">FIRMA: ........................................</div>
</div>

{{-- ✅ Tabla unificada por categorías --}}
<table>
    <thead>
        <tr>
            <th>PRODUCTO</th>
            <th>PRECIO</th>
            <th>CANTIDAD DE ENTRADA</th>
            <th>VENTAS DEL DÍA</th>
            <th>CANTIDAD DE SALIDA</th>
            <th>EFECTIVO</th>
        </tr>
    </thead>
    <tbody>
        @foreach($detallesVentaPorProducto as $categoria => $productos)
            <tr>
                <td colspan="6" class="categoria">
                    <span style="display: inline-block; width: 100%; text-align: center;">
                        ---------------------------- {{ strtoupper($categoria) }} ----------------------------
                    </span>
                </td>
                            </tr>
            @foreach($productos as $item)
                <tr>
                    <td style="text-align: left;">{{ $item['descripcion'] }}</td>
                    <td>{{ number_format($item['precio_unitario'], 2) }}</td>
                    <td>{{ number_format($item['stock_inicial'], 2) }}</td>
                    <td>{{ number_format($item['cantidad_total'], 0) }}</td>
                    <td>{{ number_format( $item['stock_final'], 0) }}</td>
                    <td>{{ number_format($item['subtotal_total'], 2) }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

</body>
</html>
