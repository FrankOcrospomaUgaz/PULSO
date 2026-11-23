<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reservas de habitaciones</title>
    <link rel="stylesheet" href="{{ public_path('css/pdf.css') }}">
</head>

<body>
    <header class="left xx-small">
        Empresa: {{ $sucursal->empresa->razon_social }} | Sucursal: {{ $sucursal->nombrecomercial }}
    </header>
    <header class="right xx-small">
        {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}
    </header>

    <h5 style="text-align: center">Reservas de habitaciones</h5>
    <p style="text-align: center; font-size: 10px; margin-top: -20px">
        Desde: {{ $fecha_inicio ?: '-' }} Hasta: {{ $fecha_fin ?: '-' }}
    </p>

    <table class="table-full-soft-bordered xx-small" width="100%">
        <thead class="bg-primary white-text">
            <tr>
                <th>Codigo</th>
                <th>Habitacion(es)</th>
                <th>Cliente</th>
                <th>Origen</th>
                <th>F. reserva</th>
                <th>F. inicio</th>
                <th>F. salida</th>
                <th>Dias</th>
                <th>Personas</th>
                <th>Adelanto</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Situacion</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reservas as $reserva)
                <tr>
                    <td>{{ str_pad($reserva->movimiento->numero ?? $reserva->id, 8, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ collect(json_decode($reserva->json_habitaciones))->pluck('numero')->map(fn($numero) => trim($numero))->join(', ') }}</td>
                    <td>
                        {{ $reserva->movimiento->Persona->personamaestro->nrodoc ?? 'N/A' }} -
                        {{ $reserva->movimiento->persona ?? 'N/A' }}
                    </td>
                    <td>{{ $reserva->origen->descripcion ?? '-' }}</td>
                    <td>
                        @if ($reserva->fechavencimiento)
                            {{ \Carbon\Carbon::parse($reserva->fechavencimiento)->format('Y-m-d') }}<br>
                            {{ \Carbon\Carbon::parse($reserva->fechavencimiento)->format('h:i:s A') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if ($reserva->fechainicio)
                            {{ \Carbon\Carbon::parse($reserva->fechainicio)->format('Y-m-d') }}<br>
                            {{ \Carbon\Carbon::parse($reserva->fechainicio)->format('h:i:s A') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if ($reserva->fechasalida)
                            {{ \Carbon\Carbon::parse($reserva->fechasalida)->format('Y-m-d') }}<br>
                            {{ \Carbon\Carbon::parse($reserva->fechasalida)->format('h:i:s A') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="inpt-cantidad-mc">{{ $reserva->dias ?? 0 }}</td>
                    <td class="inpt-cantidad-mc">{{ $reserva->nropersonas ?? 0 }}</td>
                    <td class="inpt-cantidad-mc">{{ number_format($reserva->adelanto ?? 0, 2) }}</td>
                    <td class="inpt-cantidad-mc">{{ number_format($reserva->total, 2) }}</td>
                    <td>{{ $reserva->estado }}</td>
                    <td>{{ $reserva->movimiento->situacion ?? 'Desactivado' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="9" style="text-align: right; font-weight: bold;">
                    Total reservado:
                </td>
                <td class="inpt-cantidad-mc" style="font-weight: bold;">
                    {{ number_format($reservas->sum('adelanto'), 2) }}
                </td>
                <td class="inpt-cantidad-mc" style="font-weight: bold;">
                    {{ number_format($totalReservado, 2) }}
                </td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <footer class="center xx-small" style="margin-top: 30px;">
        Copyright &copy; {{ date('Y') }}. Garzasoft. Todos los derechos reservados.
    </footer>
</body>

</html>
