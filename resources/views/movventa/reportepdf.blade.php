<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas</title>
    
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
    <h5 style="text-align: center">Ventas</h5>
    <p style="text-align: center; font-size: 10px; margin-top: -20px">Desde: {{ $fecha_inicio }}  Hasta: {{ $fecha_fin }}</p>
    <table class="table-full-soft-bordered xx-small" width="100%">
        <thead class="bg-primary white-text">
            <tr>
                <th>Comprobante</th>
                <th>Tipo</th>
                <th>Moneda</th>
                {{-- <th>T. cambio</th> --}}
                <th>Subtotal</th>
                <th>IGV</th>
                <th>Total</th>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Persona</th>
                <th>Responsable</th>
                <th>Tipo de detalle</th>                
                <th>Por consumo</th>
                <th>Medio de pago</th>
                <th>Tipo de pago</th>
                <th style="width: 15%;">Detalles</th>
                <th>Habitaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($movventas as $movventa)
                <tr>
                    <td>{{ $movventa->serie }}-{{ $movventa->movimiento->numero }}-{{ $movventa->anio }}
                    </td>
                    <td>{{ $movventa->movimiento->tipodocumento->nombre }}</td>
                    <td>{{ $movventa->moneda }}</td>
                    {{-- <td>{{ $movventa->tipocambio }}</td> --}}

                    <td class="inpt-cantidad-mc ">{{ number_format($movventa->subtotal, 2) }}</td>
                    <td class="inpt-cantidad-mc ">{{ number_format($movventa->igv, 2) }}</td>
                    <td class="inpt-cantidad-mc ">{{ number_format($movventa->total, 2) }}</td>
                    <td>{{ $movventa->movimiento->fecha }}</td>
                    <td>{{ $movventa->movimiento->usuario ?? 'N/A' }}</td>
                    <td>{{ $movventa->movimiento->Persona->personamaestro->nrodoc ?? 'N/A' }} -
                        {{ $movventa->movimiento->persona ?? 'N/A' }}</td>
                    <td>{{ $movventa->movimiento->Responsable->personamaestro->nrodoc ?? 'N/A' }} -
                        {{ $movventa->movimiento->responsable ?? 'N/A' }}</td>
                    <td>{{ $movventa->tipo_detalle }}</td>

                    <td>{{ $movventa->consumo }}</td>
                        @php
                        $pagosAgrupados = optional(optional($movventa)->movcaja)->detMovcaja ? $movventa->movcaja->detMovcaja
                            ->groupBy(function ($item) {
                                $medioDescripcion = data_get($item, 'Mediopago.descripcion') ?? $item->mediopago ?? '';
                                return $medioDescripcion !== '' ? $medioDescripcion : 'Desconocido';
                            })
                            ->map(function ($items, $medio) {
                                if (strtolower(trim($medio)) === 'billetera digital') {
                                    $breakdown = $items->groupBy(function ($subItem) {
                                        $billetera = data_get($subItem, 'billeteradigital.descripcion');
                                        if ($billetera === null && is_string($subItem->billeteradigital)) {
                                            $billetera = $subItem->billeteradigital;
                                        }
                                        return $billetera !== null && trim($billetera) !== '' ? trim($billetera) : 'Otros';
                                    })->map(fn($group) => $group->sum('monto'));

                                    return [
                                        'monto' => $items->sum('monto'),
                                        'breakdown' => $breakdown
                                    ];
                                }
                                return [
                                    'monto' => $items->sum('monto'),
                                    'breakdown' => null
                                ];
                            }) : collect();
                        @endphp

                        @foreach ($pagosAgrupados as $medio => $info)
                            @if ($info['breakdown'] && $info['breakdown']->isNotEmpty())
                                @php
                                    $breakdownStrings = [];
                                    foreach ($info['breakdown'] as $billeteraName => $billeteraMonto) {
                                        $breakdownStrings[] = "{$billeteraName}: " . number_format($billeteraMonto, 2);
                                    }
                                    $breakdownText = implode(', ', $breakdownStrings);
                                @endphp
                                {{ $medio }} ({{ $breakdownText }})<br>
                            @else
                                {{ $medio }}: {{ number_format($info['monto'], 2) }}<br>
                            @endif
                        @endforeach
                    <td>{{ $movventa->tipo_pago }}</td>
                    <td>
                        @foreach($movventa->detMovventas as $detalle)
                            {{ intval($detalle->cantidad) }} {{ $detalle->descripcion }} : {{ number_format($detalle->monto, 2, '.', '') }} <br>
                        @endforeach
                    </td>
                    
          <td>
    @php
        $movhab = $movventa->movhabitacion;
    @endphp

    @if ($movhab && $movhab->habitaciones->count() > 0)
        @foreach ($movhab->habitaciones as $hab)
            <div style="margin-bottom: 4px;">
               {{ $hab->tipohabitacion->nombre ?? '' }}
                 <strong>{{ $hab->numero }}</strong><br>
            </div>

            
        @endforeach
    @else
        —
    @endif
</td>

                    
                </tr>
            @endforeach
        </tbody>
    <!-- **Fila de Total Vendido** -->
 <tr class="total-row">
    <td colspan="5" style="text-align:right;font-weight:bold;">
        Total Vendido:
    </td>
    <td style="font-weight:bold;">
        {{ number_format($totalVendido, 2) }}
    </td>
    <td colspan="10"></td>
</tr>

    </table>

</body>

</html>
