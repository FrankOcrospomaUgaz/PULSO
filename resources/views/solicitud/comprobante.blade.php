<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobantes</title>
    
    <link rel="stylesheet" href="{{ public_path('css/pdf.css') }}">

</head>

<body>
    <header class="left xx-small">
        Empresa:  {{$sucursal->empresa->razon_social}} | Sucursal: {{$sucursal->nombrecomercial}}
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
    <h5 style="text-align: center">Comprobantes</h5>
    <p style="text-align: center; font-size: 10px; margin-top: -20px">Desde: {{ $fecha_inicio }}  Hasta: {{ $fecha_fin }}</p>
    <table class="table-full-soft-bordered xx-small" width="100%">
        <thead class="bg-primary white-text">
            <tr>
                <th>Tipo</th>
                <th>Número</th>
                <th>Nro. de Documento</th>
                <th>Persona</th>
                <th>Moneda</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Fecha de Solicitud</th>
                <th>F. de Envío</th>
                <th>F. de Respuesta</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalGeneral = 0; // Variable para sumar el total
            @endphp
            @foreach ($solicitudes as $solicitud)
            <tr>
                <td>{{ $solicitud->tipo_documento == 'B' ? 'Boleta' : 'Factura' }}</td>
                <td>{{ $solicitud->serie . '-' . $solicitud->correlativo }}</td>
                <td>{{ $solicitud->persona->personamaestro->nrodoc }}</td>
                <td>{{ $solicitud->movimiento->persona }}</td>
                <td>{{ $solicitud->moneda }}</td>
                <td>
                    {{ $solicitud->total }}
                    @php
                        $totalGeneral += $solicitud->total; // Sumar el total de cada solicitud
                    @endphp
                </td>
                <td>{{ $solicitud->estado_texto }}</td>
                <td>{{ $solicitud->fecha }}</td>
                <td>{{ $solicitud->fecha_envio }}</td>
                <td>{{ $solicitud->fecha_respuesta }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right; font-weight: bold;">Total:</td>
                <td style="font-weight: bold;">{{ number_format($totalGeneral, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
