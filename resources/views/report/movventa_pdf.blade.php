<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="{{ public_path('css/pdf.css') }}">
        <style>
    
            .section-title {
            font-size: 16.5px;
            font-weight: bold;
            margin-top: 20px;
            text-align: left;
        }
        .table-full-soft-bordered.xx-small thead tr th,
        .table-full-soft-bordered.xx-small tbody tr td {
            font-size: 15px;
        }

        /* Paginación */
        .page-number {
            text-align: right;
            margin-top: 10px;
        }

        /* Nueva página */
        .new-page {
            page-break-before: always;
        }

        .with-header-offset {
    padding-top: 60px; /* 50px header + margen */
}

body {
    margin: 0;
    
}


    </style>
</head>

<body>
    <h5 style="text-align: center; font-size: 18px;">{{ $title }}</h5>
    <p style="text-align: center; font-size: 17px; margin-top: -30px">{{ $subtitle }}</p>
    @if (($report_key ?? '') === 'ventas_documento')
        <div class="section-title" style="text-align: left;">TOTALES POR TIPO DE DOCUMENTO</div>
        <br>
        <table class="table-full-soft-bordered xx-small" width="50%">
            <thead style="background-color: #00897b;" class="white-text">
                <tr style=" height: 30px;">
                    <th colspan="1">Ticket</th>
                    <th colspan="4">Boleta</th>
                    <th colspan="4">Factura</th>
                </tr>
                <tr>
                    <th>Total</th>
                    <th>Subtotal</th>
                    <th>IGV</th>
                  
                    <th>Total</th>
                    <th>Subtotal</th>
                    <th>IGV</th>
                   
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td align="right">{{ number_format($totales_documento['ticket']['total'] ?? 0, 2) }}</td>
                    <td align="right">{{ number_format($totales_documento['boleta']['subtotal'] ?? 0, 2) }}</td>
                    <td align="right">{{ number_format($totales_documento['boleta']['igv'] ?? 0, 2) }}</td>
                    <td align="right">{{ number_format($totales_documento['boleta']['total'] ?? 0, 2) }}</td>
                    <td align="right">{{ number_format($totales_documento['factura']['subtotal'] ?? 0, 2) }}</td>
                    <td align="right">{{ number_format($totales_documento['factura']['igv'] ?? 0, 2) }}</td>
                    <td align="right">{{ number_format($totales_documento['factura']['total'] ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>
        <br>
         <br>
          <br>
           <br>
        <table class="table-full-soft-bordered xx-small" width="100%" style="table-layout: fixed;">
            <thead style="background-color: #00897b;" class="white-text">
                <tr style=" height: 65px;">
                    @foreach ($detalle_columns as $label)
                        <th>{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($detalle_rows as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($detalle_columns) }}">Sin registros</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @elseif (($report_key ?? '') === 'ventas_producto')
        @forelse ($categorias as $categoria)
            <div class="section-title" style="text-align: left;">{{ strtoupper($categoria['nombre']) }}</div>
            <br>
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead style="background-color: #00897b;" class="white-text">
                    <tr style=" height: 50px;">
                        @foreach ($columns as $label)
                            <th>{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categoria['rows'] as $row)
                        <tr>
                            @foreach ($row as $cell)
                                <td>{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) }}">Sin registros</td>
                        </tr>
                    @endforelse
                    @php
                        $selectedKeys = $selected_keys ?? [];
                        $totalIndex = array_search('total', $selectedKeys, true);
                        $labelIndex = array_search('descripcion', $selectedKeys, true);
                        if ($labelIndex === false) {
                            $labelIndex = array_search('categoria', $selectedKeys, true);
                        }
                        if ($labelIndex === false && $totalIndex !== false) {
                            $labelIndex = max(0, $totalIndex - 1);
                        }
                    @endphp
                    @if ($totalIndex !== false && !empty($categoria['rows']))
                        @php
                            $totalRow = array_fill(0, count($selectedKeys), '');
                            $label = 'Totales';
                            if (!empty($categoria['moneda'])) {
                                $label .= ' (' . $categoria['moneda'] . ')';
                            }
                            $totalRow[$labelIndex] = $label;
                            $totalRow[$totalIndex] = number_format($categoria['total'] ?? 0, 2);
                        @endphp
                        <tr>
                            @foreach ($totalRow as $index => $cell)
                                <td @if ($index === $totalIndex || $index === $labelIndex) style="font-weight:bold; text-align:right;" @endif>
                                    {{ $cell }}
                                </td>
                            @endforeach
                        </tr>
                    @endif
                </tbody>
            </table>
            <br><br>
        @empty
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead style="background-color: #00897b;" class="white-text">
                    <tr style=" height: 50px;">
                        @foreach ($columns as $label)
                            <th>{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="{{ count($columns) }}">Sin registros</td>
                    </tr>
                </tbody>
            </table>
        @endforelse
    @elseif (($report_key ?? '') === 'ticket_promedio')
        @php
            $sections = $ticket_sections ?? [];
            $hasMultiple = $ticket_has_multiple ?? false;
        @endphp
        @forelse ($sections as $section)
            @if ($hasMultiple)
                <div class="section-title" style="text-align: left;">Moneda: {{ $section['moneda'] ?? '' }}</div>
            @endif
            <div class="section-title" style="text-align: left;">Resumen de ticket promedio</div>
            <br>
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead style="background-color: #00897b;" class="white-text">
                    <tr style=" height: 50px;">
                        <th>Día</th>
                        <th>Ticket promedio ({{ $section['moneda'] ?? '' }})</th>
                        <th>Total de ventas ({{ $section['moneda'] ?? '' }})</th>
                        <th>Número de personas</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td>{{ $section['resumen']['promedio'] ?? '0.00' }}</td>
                        <td>{{ $section['resumen']['total'] ?? '0.00' }}</td>
                        <td>{{ $section['resumen']['personas'] ?? '0' }}</td>
                    </tr>
                </tbody>
            </table>
            <br><br>
            <div class="section-title" style="text-align: left;">Ticket promedio</div>
            <br>
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead style="background-color: #00897b;" class="white-text">
                    <tr style=" height: 50px;">
                        <th>Día</th>
                        <th>Ticket promedio ({{ $section['moneda'] ?? '' }})</th>
                        <th>Total de ventas ({{ $section['moneda'] ?? '' }})</th>
                        <th>Número de personas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($section['dias'] ?? [] as $row)
                        <tr>
                            <td>{{ $row['dia'] ?? '' }}</td>
                            <td>{{ $row['promedio'] ?? '' }}</td>
                            <td>{{ $row['total'] ?? '' }}</td>
                            <td>{{ $row['personas'] ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Sin registros</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <br><br>
        @empty
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead style="background-color: #00897b;" class="white-text">
                    <tr style=" height: 50px;">
                        <th>Día</th>
                        <th>Ticket promedio</th>
                        <th>Total de ventas</th>
                        <th>Número de personas</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4">Sin registros</td>
                    </tr>
                </tbody>
            </table>
        @endforelse
    @elseif (($report_key ?? '') === 'ticket_promedio_persona')
        @php
            $sections = $ticket_persona_sections ?? [];
            $hasMultiple = $ticket_persona_has_multiple ?? false;
        @endphp
        @forelse ($sections as $section)
            @if ($hasMultiple)
                <div class="section-title" style="text-align: left;">Moneda: {{ $section['moneda'] ?? '' }}</div>
            @endif
            <div class="section-title" style="text-align: left;">Resumen de ticket promedio</div>
            <br>
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead style="background-color: #00897b;" class="white-text">
                    <tr style=" height: 50px;">
                        <th>Ticket promedio ({{ $section['moneda'] ?? '' }})</th>
                        <th>Total de ventas ({{ $section['moneda'] ?? '' }})</th>
                        <th>Numero de transacciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $section['resumen']['promedio'] ?? '0.00' }}</td>
                        <td>{{ $section['resumen']['total'] ?? '0.00' }}</td>
                        <td>{{ $section['resumen']['transacciones'] ?? '0' }}</td>
                    </tr>
                </tbody>
            </table>
            <br><br>
            <div class="section-title" style="text-align: left;">Ticket promedio</div>
            <br>
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead style="background-color: #00897b;" class="white-text">
                    <tr style=" height: 50px;">
                        <th>Tipo</th>
                        <th>Documento</th>
                        <th>Nombres completos</th>
                        <th>F. nacimiento</th>
                        <th>Genero</th>
                        <th>Direccion</th>
                        <th>E-mail</th>
                        <th>T. fijo</th>
                        <th>T. movil</th>
                        <th>Distrito</th>
                        <th>Ticket promedio ({{ $section['moneda'] ?? '' }})</th>
                        <th>Total de ventas ({{ $section['moneda'] ?? '' }})</th>
                        <th>Numero de transacciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($section['rows'] ?? [] as $row)
                        <tr>
                            <td>{{ $row['tipo'] ?? '' }}</td>
                            <td>{{ $row['documento'] ?? '' }}</td>
                            <td>{{ $row['nombre'] ?? '' }}</td>
                            <td>{{ $row['fechanac'] ?? '' }}</td>
                            <td>{{ $row['genero'] ?? '' }}</td>
                            <td>{{ $row['direccion'] ?? '' }}</td>
                            <td>{{ $row['email'] ?? '' }}</td>
                            <td>{{ $row['telefono_fijo'] ?? '' }}</td>
                            <td>{{ $row['telefono_movil'] ?? '' }}</td>
                            <td>{{ $row['distrito'] ?? '' }}</td>
                            <td>{{ $row['promedio'] ?? '' }}</td>
                            <td>{{ $row['total'] ?? '' }}</td>
                            <td>{{ $row['transacciones'] ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13">Sin registros</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <br><br>
        @empty
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead style="background-color: #00897b;" class="white-text">
                    <tr style=" height: 50px;">
                        <th>Ticket promedio</th>
                        <th>Total de ventas</th>
                        <th>Numero de transacciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="3">Sin registros</td>
                    </tr>
                </tbody>
            </table>
        @endforelse
    @elseif (($report_key ?? '') === 'ventas_mediopago_detallado')
        @php
            $sections = $mediopago_detallado_sections ?? [];
            $totales = $mediopago_detallado_totales ?? [];
        @endphp
        @forelse ($sections as $section)
            <div class="section-title" style="text-align: left;">{{ $section['metodo'] ?? 'Metodo' }}</div>
            <br>
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead style="background-color: #00897b;" class="white-text">
                    <tr style=" height: 50px;">
                        <th>Origen</th>
                        <th>Total</th>
                        <th>Total del metodo</th>
                        <th>Detalles</th>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Caja</th>
                        <th>Turno</th>
                        <th>Persona</th>
                        <th>Responsable</th>
                        <th>Comentario</th>
                        <th>Situacion</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($section['rows'] ?? [] as $row)
                        <tr>
                            <td>{{ $row['origen'] ?? '' }}</td>
                            <td>{{ $row['total'] ?? '' }}</td>
                            <td>{{ $row['total_metodo'] ?? '' }}</td>
                            <td>{{ $row['detalles'] ?? '' }}</td>
                            <td>{{ $row['fecha'] ?? '' }}</td>
                            <td>{{ $row['usuario'] ?? '' }}</td>
                            <td>{{ $row['caja'] ?? '' }}</td>
                            <td>{{ $row['turno'] ?? '' }}</td>
                            <td>{{ $row['persona'] ?? '' }}</td>
                            <td>{{ $row['responsable'] ?? '' }}</td>
                            <td>{{ $row['comentario'] ?? '' }}</td>
                            <td>{{ $row['situacion'] ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12">Sin registros</td>
                        </tr>
                    @endforelse
                    @if (!empty($section['rows']))
                        <tr>
                            <td></td>
                            <td>{{ $section['total_ventas'] ?? '' }}</td>
                            <td>{{ $section['total_metodo'] ?? '' }}</td>
                            <td colspan="9"></td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <br><br>
        @empty
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead style="background-color: #00897b;" class="white-text">
                    <tr style=" height: 50px;">
                        <th>Origen</th>
                        <th>Total</th>
                        <th>Total del metodo</th>
                        <th>Detalles</th>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Caja</th>
                        <th>Turno</th>
                        <th>Persona</th>
                        <th>Responsable</th>
                        <th>Comentario</th>
                        <th>Situacion</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="12">Sin registros</td>
                    </tr>
                </tbody>
            </table>
        @endforelse
        @if (!empty($totales))
            <div class="section-title" style="text-align: left;">TOTALES POR METODO DE PAGO</div>
            <br>
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead style="background-color: #00897b;" class="white-text">
                    <tr style=" height: 50px;">
                        <th>Efectivo</th>
                        <th>Tarjeta</th>
                        <th>Transferencia</th>
                        <th>Depósito</th>
                        <th>Billetera digital</th>
                        <th>Aplicación móvil</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $totales['Efectivo'] ?? '0.00' }}</td>
                        <td>{{ $totales['Tarjeta'] ?? '0.00' }}</td>
                        <td>{{ $totales['Transferencia'] ?? '0.00' }}</td>
                        <td>{{ $totales['Depósito'] ?? '0.00' }}</td>
                        <td>{{ $totales['Billetera digital'] ?? '0.00' }}</td>
                        <td>{{ $totales['Aplicación móvil'] ?? '0.00' }}</td>
                    </tr>
                </tbody>
            </table>
            <br><br>
        @endif
    @elseif (($report_key ?? '') === 'ventas_mediopago_consolidado')
        @php
            $pagadas = $mediopago_consolidado_pagadas ?? ['rows' => [], 'totales' => []];
            $adeudadas = $mediopago_consolidado_adeudadas ?? ['rows' => [], 'totales' => []];
            $detallePagadas = $mediopago_consolidado_detalle_pagadas ?? [];
        @endphp
        <div class="section-title" style="text-align: left;">Resumen de ventas pagadas</div>
        <br>
        <table class="table-full-soft-bordered xx-small" width="40%">
            <thead style="background-color: #00897b;" class="white-text">
                <tr style=" height: 50px;">
                    <th>Medio de pago</th>
                    <th>Monto (PEN)</th>
                    <th>Monto (USD)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pagadas['rows'] ?? [] as $row)
                    <tr>
                        <td>{{ $row['medio'] ?? '' }}</td>
                        <td>{{ $row['pen'] ?? '' }}</td>
                        <td>{{ $row['usd'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Sin registros</td>
                    </tr>
                @endforelse
                @if (!empty($pagadas['rows']))
                    <tr>
                        <td>Totales</td>
                        <td>{{ $pagadas['totales']['pen'] ?? '0.00' }}</td>
                        <td>{{ $pagadas['totales']['usd'] ?? '0.00' }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
        <br><br>
        <div class="section-title" style="text-align: left;">Resumen de ventas adeudadas</div>
        <br>
        <table class="table-full-soft-bordered xx-small" width="40%">
            <thead style="background-color: #00897b;" class="white-text">
                <tr style=" height: 50px;">
                    <th>Medio de pago</th>
                    <th>Monto (PEN)</th>
                    <th>Monto (USD)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($adeudadas['rows'] ?? [] as $row)
                    <tr>
                        <td>{{ $row['medio'] ?? '' }}</td>
                        <td>{{ $row['pen'] ?? '' }}</td>
                        <td>{{ $row['usd'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Sin registros</td>
                    </tr>
                @endforelse
                @if (!empty($adeudadas['rows']))
                    <tr>
                        <td>Totales</td>
                        <td>{{ $adeudadas['totales']['pen'] ?? '0.00' }}</td>
                        <td>{{ $adeudadas['totales']['usd'] ?? '0.00' }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
        <br><br>
        @if (!empty($detallePagadas))
            <div class="section-title" style="text-align: left;">Consolidado de ventas pagadas</div>
            <br>
            @foreach ($detallePagadas as $section)
                <table class="table-full-soft-bordered xx-small" width="50%">
                    <thead style="background-color: #00897b;" class="white-text">
                        <tr style=" height: 50px;">
                            <th>Medio de pago</th>
                            <th>Monto (PEN)</th>
                            <th>Monto (USD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($section['rows'] ?? [] as $row)
                            <tr>
                                <td>{{ $row['medio'] ?? '' }}</td>
                                <td>{{ $row['pen'] ?? '' }}</td>
                                <td>{{ $row['usd'] ?? '' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">Sin registros</td>
                            </tr>
                        @endforelse
                        @if (!empty($section['rows']))
                            <tr>
                                <td>Totales</td>
                                <td>{{ $section['totales']['pen'] ?? '0.00' }}</td>
                                <td>{{ $section['totales']['usd'] ?? '0.00' }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <br><br>
            @endforeach
        @endif
    @elseif (($report_key ?? '') === 'ventas_responsable')
        @php
            $sections = $responsables ?? [];
            $totalesGenerales = $totales_generales ?? [];
        @endphp
        @forelse ($sections as $section)
            <div class="section-title" style="text-align: left;">{{ $section['responsable'] ?? 'Responsable' }}</div>
            <br>
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead style="background-color: #00897b;" class="white-text">
                    <tr style=" height: 50px;">
                        <th>Codigo</th>
                        <th>Descripcion</th>
                        <th>Moneda</th>
                        <th>P. original</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>P. vendido (PEN)</th>
                        <th>Total (PEN)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($section['rows'] ?? [] as $row)
                        <tr>
                            <td>{{ $row['codigo'] ?? '' }}</td>
                            <td>{{ $row['descripcion'] ?? '' }}</td>
                            <td>{{ $row['moneda'] ?? '' }}</td>
                            <td>{{ $row['precio_original'] ?? '' }}</td>
                            <td>{{ $row['cantidad'] ?? '' }}</td>
                            <td>{{ $row['unidad'] ?? '' }}</td>
                            <td>{{ $row['precio_vendido'] ?? '' }}</td>
                            <td>{{ $row['total'] ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">Sin registros</td>
                        </tr>
                    @endforelse
                    @if (!empty($section['rows']))
                        @foreach ($section['totales'] ?? [] as $moneda => $total)
                            <tr>
                                <td colspan="6"></td>
                                <td style="font-weight:bold; text-align:right;">Totales ({{ $moneda }})</td>
                                <td style="font-weight:bold; text-align:right;">{{ $total }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
            <br><br>
        @empty
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead style="background-color: #00897b;" class="white-text">
                    <tr style=" height: 50px;">
                        <th>Codigo</th>
                        <th>Descripcion</th>
                        <th>Moneda</th>
                        <th>P. original</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>P. vendido (PEN)</th>
                        <th>Total (PEN)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="8">Sin registros</td>
                    </tr>
                </tbody>
            </table>
        @endforelse
        @if (!empty($totalesGenerales))
            <div class="section-title" style="text-align: left;">TOTALES GENERALES</div>
            <br>
            @if (count($totalesGenerales) === 1)
                @foreach ($totalesGenerales as $moneda => $total)
                    <table class="table-full-soft-bordered xx-small" width="25%">
                        <thead style="background-color: #00897b;" class="white-text">
                            <tr style=" height: 50px;">
                                <th>Total ({{ $moneda }})</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $total }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endforeach
            @else
                <table class="table-full-soft-bordered xx-small" width="60%">
                    <thead style="background-color: #00897b;" class="white-text">
                        <tr style=" height: 50px;">
                            @foreach ($totalesGenerales as $moneda => $total)
                                <th>Total ({{ $moneda }})</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach ($totalesGenerales as $total)
                                <td>{{ $total }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            @endif
            <br><br>
        @endif
    @elseif (($report_key ?? '') === 'descuento_persona')
        @php
            $rows = $descuento_persona_rows ?? [];
            $totales = $descuento_persona_totales ?? ['subtotal' => '0.00', 'descuento' => '0.00'];
        @endphp
        <table class="table-full-soft-bordered xx-small" width="100%">
            <thead style="background-color: #00897b;" class="white-text">
                <tr style=" height: 50px;">
                    <th>Cliente</th>
                    <th>Habitacion</th>
                    <th>Cantidad</th>
                    <th>Precio real (PEN)</th>
                    <th>P. alquilado (PEN)</th>
                    <th>Subtotal (PEN)</th>
                    <th>Descuento total (PEN)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['cliente'] ?? '' }}</td>
                    <td>{{ $row['habitacion'] ?? '' }}</td>
                    <td>{{ $row['estadia'] ?? '' }}</td>
                    <td>{{ $row['precio_real'] ?? '' }}</td>
                    <td>{{ $row['precio_vendido'] ?? '' }}</td>
                        <td>{{ $row['subtotal'] ?? '' }}</td>
                        <td>{{ $row['descuento'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Sin registros</td>
                    </tr>
                @endforelse
                @if (!empty($rows))
                    <tr>
                        <td colspan="4"></td>
                        <td style="font-weight:bold; text-align:right;">{{ $totales['subtotal'] ?? '0.00' }}</td>
                        <td style="font-weight:bold; text-align:right;">{{ $totales['descuento'] ?? '0.00' }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @else
        <table class="table-full-soft-bordered xx-small" width="100%">
            <thead style="background-color: #00897b;" class="white-text">
                  <tr style=" height: 50px;">
                    @foreach ($columns as $label)
                        <th>{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}">Sin registros</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif
</body>

</html>
