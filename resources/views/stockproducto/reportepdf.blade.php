<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock de Productos</title>
    
    <link rel="stylesheet" href="{{ public_path('css/pdf.css') }}">

</head>

<body>
    <header class="left xx-small">
        Empresa: {{$sucursal->empresa->razon_social}} | Sucursal: {{$sucursal->nombrecomercial}}
    </header>
    <header class="right xx-small">
        {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}
    </header>
    <footer class="left xx-small">
    </footer>
    <footer class="xx-small">
        Copyright&copy; {{ date('Y') }}. Garzasoft. Todos los derechos reservados.
    </footer>
    <footer class="right xx-small">
    </footer>

    <h5 style="text-align: center">Stock de Productos</h5>
    <p style="text-align: center; font-size: 10px; margin-top: -20px">Generado el: {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}</p>

    <table class="table-full-soft-bordered xx-small" width="100%">
        <thead class="bg-primary white-text">
            <tr>
                <th>Cód.</th>
                <th>Descripción</th>
                <th>Abreviatura</th>

                <th>Tipo</th>
                <th>Categoría</th>
                <th>Unidad Base</th>
                <th>Stock</th>
                <th>St. Mín.</th>
                <th>St. Máx.</th>
                <th>St. Ópt.</th>
                <th>Situación</th>
                <th>P. Compra</th>
                <th>P. Venta</th>
                <th>Moneda</th>
                <th>Peso</th>
             
              
            </tr>
        </thead>
        <tbody>
            @foreach ($stockProductos as $stockProducto)
                <tr>
                    <td>{{ $stockProducto->producto->codigo }}</td>
                    <td>{{ $stockProducto->producto->descripcion }}</td>
                    <td>{{ $stockProducto->producto->abreviatura ?? 'N/A' }}</td>

                    <td>{{ $stockProducto->producto->tipo }}</td>
                    <td>{{ $stockProducto->producto->categorium->descripcion }}</td>
                    <td>{{ $stockProducto->unidad->descripcion }}</td>
                    <td class="inpt-cantidad-mc ">{{ $stockProducto->stockbase }}</td>
                    <td class="inpt-cantidad-mc ">{{ $stockProducto->producto->relProductosucursal->stockminimo ?? 0 }}</td>
                    <td class="inpt-cantidad-mc ">{{ $stockProducto->producto->relProductosucursal->stockmaximo ?? 0 }}</td>
                    <td class="inpt-cantidad-mc ">{{ $stockProducto->producto->relProductosucursal->stockoptimo ?? 0 }}</td>
                    <td>
                        {{ $stockProducto->producto->relProductosucursal->situacion }}
                    </td>
                    <td class="inpt-cantidad-mc ">{{ $stockProducto->producto->relListaUnidadSucursal->preciocompra ?? 'N/A' }}</td>
                    <td class="inpt-cantidad-mc ">{{ $stockProducto->producto->relListaUnidadSucursal->precioventa ?? 'N/A' }}</td>
                    <td>{{ $stockProducto->producto->relListaUnidadSucursal->moneda ?? 'N/A' }}</td>
                    <td class="inpt-cantidad-mc ">{{ $stockProducto->producto->peso ?? '0.00' }}</td>
             
                    
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
