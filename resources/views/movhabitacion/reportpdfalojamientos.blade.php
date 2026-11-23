<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alojamientos</title>
    <link rel="stylesheet" href="{{ public_path('css/pdf.css') }}">
</head>
<body>

    <header class="left xx-small">
        Empresa: {{ $sucursal->empresa->razon_social }} | Sucursal: {{ $sucursal->nombrecomercial }}
    </header>
    <header class="right xx-small">
        {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}
    </header>

    <h5 style="text-align: center">Alojamientos</h5>
    <p style="text-align: center; font-size: 10px; margin-top: -20px">
        Desde: {{ $fecha_inicio }}  Hasta: {{ $fecha_fin }}
    </p>

    <table class="table-full-soft-bordered xx-small" width="100%">
        <thead class="bg-primary white-text">
            <tr>
                <th>Habitacion(es)</th>
                <th>Huespedes</th>
                <th>Ingreso</th>
                <th>Salida Programada</th>
                <th>Salida Real</th>
                <th>Estadía</th>
                <th>Early CheckIn</th>
                <th>Late Checkout</th>
                <th>Descuento</th>
                <th>Total</th>
                <th>Medio pago</th>
                <th>Moneda</th>
                <th>Comentario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimientoHabitacion as $mov)
            <tr>
                <td>{{ collect(json_decode($mov->habitaciones))->pluck('numero')->map(fn($num) => trim($num))->join(', ') }}</td>
                <td>
                    @foreach($mov->huespeds as $huesped)
                    {{ $huesped->persona->personamaestro->nrodoc }} - {{ $huesped->persona->nombres }} {{ $huesped->persona->apellidos }}<br>
                    @endforeach
                    @if($mov->huespeds->isEmpty())
                        0 -CLIENTES VARIOS
                    @endif
                </td>
                

                {{-- Fecha Ingreso --}}
                <td>
                    {{ \Carbon\Carbon::parse($mov->fechaingreso)->format('Y-m-d') }}<br>
                    {{ \Carbon\Carbon::parse($mov->fechaingreso)->format('h:i:s A') }}
                </td>

                {{-- Fecha Salida Programada --}}
                <td>
                    @if($mov->fechasalida)
                        {{ \Carbon\Carbon::parse($mov->fechasalida)->format('Y-m-d') }}<br>
                        {{ \Carbon\Carbon::parse($mov->fechasalida)->format('h:i:s A') }}
                    @else
                        -
                    @endif
                </td>

                {{-- Fecha Salida Real --}}
                <td>
                    @if($mov->fecha_salida_real)
                        {{ \Carbon\Carbon::parse($mov->fecha_salida_real)->format('Y-m-d') }}<br>
                        {{ \Carbon\Carbon::parse($mov->fecha_salida_real)->format('h:i:s A') }}
                    @else
                        -
                    @endif
                </td>

                {{-- Estadía --}}
                <td>
                    @if($mov->horas)
                        {{ $mov->horas }} hrs
                    @else
                        @if($mov->dias == 1)
                          1 noche
                        @else
                          {{ number_format($mov->dias, 0) }} noches
                        @endif
                    @endif
                </td>

                {{-- Early Check‑In --}}
                <td class="inpt-cantidad-mc">
                    S/. {{ number_format($mov->early_checkin ?? 0, 2) }}
                </td>

                {{-- Late Checkout --}}
                <td class="inpt-cantidad-mc">
                    S/. {{ number_format($mov->late_checkout ?? 0, 2) }}
                </td>

                {{-- Descuento --}}
                <td class="inpt-cantidad-mc">
                    S/. {{ number_format($mov->descuento ?? 0, 2) }}
                </td>

                {{-- Total --}}
                <td class="inpt-cantidad-mc">
                    S/. {{ number_format($mov->total, 2) }}
                </td>
                <td>
                    @php
                    $pagosAgrupados = $mov->movventas
    ->flatMap(function ($venta) {
        // Accede al movcaja de la venta a través del movimiento
        return optional($venta->movcaja?->detMovcaja)->all() ?? [];
    })
    ->groupBy(fn($item) => optional($item->Mediopago)->descripcion ?? 'Desconocido')
    ->map(fn($items) => $items->sum('monto'));

                @endphp

                @foreach ($pagosAgrupados as $medio => $monto)
                    {{ $medio }}: {{ number_format($monto, 2) }}<br>
                @endforeach
                </td>
                {{-- Moneda --}}
                <td>{{ $mov->moneda }}</td>

                {{-- Comentario --}}
                <td>{{ $mov->comentario ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                {{-- Ajusta colspan para abarcar hasta Descuento --}}
                <td colspan="9" style="text-align: right; font-weight: bold;">
                    Total Alojado:
                </td>
                <td class="inpt-cantidad-mc" style="font-weight: bold;">
                    S/. {{ number_format($totalAlojado, 2) }}
                </td>
                {{-- Celdas vacías para Moneda y Comentario --}}
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <footer class="center xx-small" style="margin-top: 30px;">
        Copyright &copy; {{ date('Y') }}. Garzasoft. Todos los derechos reservados.
    </footer>
</body>
</html>
