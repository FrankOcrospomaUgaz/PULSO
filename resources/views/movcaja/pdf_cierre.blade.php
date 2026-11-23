<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cierre de Caja</title>
    <link rel="stylesheet" href="{{ public_path('css/pdf.css') }}">

    <style>
        tr {
            page-break-inside: avoid;
            height: 25px;
        }
            .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
            text-align: left;
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

    </style>
    {{-- <link type="text/css" href="css/pdf.css" rel="stylesheet"> --}}

</head>

<body>
    <br><br>
<table width="100%">
    <tr>
        <td align="center">
            <h1>ARQUEO DE CAJA</h1>
        </td>
    </tr>
    <tr>
        <td align="left">
            <p class="without-bottom">
                <font class="bold">Turno:</font> {{ $ultimoTurno->movcajainicio->turno->nombre }}<br/>
                <font class="bold">Caja:</font> {{ $caja->numero }}<br/>
                <font class="bold">Fecha de apertura:</font> {{ $ultimoTurno->fechainicio }}<br/>
                <font class="bold">Usuario de apertura:</font> {{ $ultimoTurno->movcajainicio->movimiento->responsable }}<br/>
                <font class="bold">Fecha de impresión:</font> {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}<br/>
                <font class="bold">Usuario de impresión:</font> {{ Auth::user()->name }}
            </p>
        </td>
    </tr>
</table>
<br/>

    <hr style="margin-bottom: 30px">
    
    <!-- Totales de cierre -->
    <div class="section-title bold" style="text-align: center; color: #00897b ;">TOTALES</div>
    <br>
    <table class="table-full-soft-bordered xx-small" width="100%">
        <thead style="background-color: #00897b;" class="white-text">
            <tr>
                <th align="left" width="50%">Concepto</th>
                <th align="right" width="50%">Monto (PEN)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($totales as $concepto => $monto)
                <tr>
                    <td>
                        @if ($concepto === 'Total + apertura' || $concepto === 'Total sin apertura')
                            <strong>{{ $concepto }}</strong>
                        @else
                            {{ $concepto }}
                        @endif
                    </td>
                    <td align="right">
                        @if ($concepto === 'Total + apertura' || $concepto === 'Total sin apertura')
                            <strong>{{ number_format($monto, 2) }}</strong>
                        @else
                            {{ number_format($monto, 2) }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>


    <div class="section-title " style="text-align: center">DETALLES DE CIERRE</div>
    <br>
    <table class="table-full-soft-bordered xx-small" width="100%">
        <thead style="background-color: #00897b;" class="white-text">
            <tr>
                <th>Tipo</th>
                <th>Monto (PEN)</th>
                <th>Medio de Pago</th>
                <th>Detalles</th>
                <th>Concepto de Pago</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detallesAgrupados as $detalle)
                <tr>
                   <td>{{ strtoupper($detalle['tipo']) }}</td>

                    <td>{{ $detalle['monto'] }}</td>
                    <td>{{ $detalle['mediopago'] }}</td>
                    <td>{{ $detalle['detalles'] ?? ' ' }}</td>
                    <td>{{ $detalle['nota'] ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <!-- Tipos de venta -->
    <div class="section-title" style="text-align: center; font-weight: bold;">TIPOS DE VENTA</div>
    <br>
    <table class="table-full-soft-bordered xx-small" width="100%" style="table-layout: fixed;">
        <thead style="background-color: #00897b;" class="white-text">
            <tr>
                <th align="left" width="60%">Tipo de venta</th>
                <th align="right" width="20%">Cantidad</th>
                <th align="right" width="20%">Tot. (PEN)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalCantidad = 0;
                $totalMonto = 0;
            @endphp

            @foreach ($ventasPorTipoDocumento as $venta)
                <tr>
                    <td align="left" width="60%">{{ $venta['tipo'] }}</td>
                    <td align="right" width="20%">{{ $venta['cantidad'] }}</td>
                    <td align="right" width="20%">{{ number_format($venta['total'], 2) }}</td>
                </tr>
                @php
                    $totalCantidad += $venta['cantidad'];
                    $totalMonto += $venta['total'];
                @endphp
            @endforeach

            <tr>
                <td align="left" width="60%"><strong>Totales</strong></td>
                <td align="right" width="20%"><strong>{{ $totalCantidad }}</strong></td>
                <td align="right" width="20%"><strong>{{ number_format($totalMonto, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Resumen de efectivo -->
    <div class="section-title " style="text-align: center">RESUMEN DE EFECTIVO (PEN)</div>
    <br>
    <table class="table-full-soft-bordered xx-small" width="100%">
        <thead style="background-color: #00897b;" class="white-text">
            <tr>
                <th>Efectivo en Caja</th>
                <th>Ventas en Efectivo</th>
                <th>Apertura en Efectivo</th>
                <th>Otros Ingresos en Efectivo</th>
                <th>Egresos en Efectivo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>{{ number_format($totales['efectivo_caja']) }}</strong></td>

                <td>{{ number_format($totales['Ventas | Efectivo'], 2) }}</td> <!-- Ventas en Efectivo -->
                <td>{{ number_format($totales['Apertura'], 2) }}</td> <!-- Apertura de caja -->
                <td>{{ number_format($totales['Otros ingresos'], 2) }}</td> <!-- Otros ingresos -->
                <td>{{ number_format($totales['Egresos (gastos)'], 2) }}</td> <!-- Egresos -->
            </tr>
        </tbody>
    </table>
    @php
        // Mapeo rápido de dinero_id => registro
        $mapaDinero = $movcajadinero->keyBy('dinero_id');
    @endphp

    <div class="new-page with-header-offset">

        <h4 style="text-align:center; font-weight:bold; margin-bottom:10px;">
            DINERO (PEN)
        </h4>

        <table width="100%" style="table-layout: fixed; margin-bottom: 20px;">
            <tr>
                {{-- Monedas --}}
                <td style="vertical-align: top; width: 50%;">
                    <table class="table-full-soft-bordered xx-small" width="100%">
                        <thead style="background-color: #00897b;" class="white-text">
                            <tr>
                                <th style="text-align:center; padding:6px;">Dinero</th>
                                <th style="text-align:center; padding:6px;">Cant.</th>
                                <th style="text-align:right;"></th>
                                <th style="text-align:center; padding:6px;">Total</th>
                                <th style="text-align:center; padding:6px;">Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $subtotalMonedas = 0; @endphp
                            @foreach([1 => '10 céntimos', 2 => '20 céntimos', 3 => '50 céntimos', 4 => '1 sol', 5 => '2 soles', 6 => '5 soles'] as $id => $nombre)
                                @php
                                    $item = $mapaDinero->get($id);
                                    $cantidad = $item->cantidad ?? 0;
                                    $valor = $item && $item->dinero ? $item->dinero->valor : 0;
                                    $comentario = $item->comentario ?? '';
                                    $total = $cantidad * $valor;
                                    $subtotalMonedas += $total;
                                @endphp
                                <tr>
                                    <td>{{ $nombre }}</td>
                                    <td style="text-align:center;">{{ $cantidad }}</td>
                                    <td width="5%">PEN</td>
                                    <td width="15%" style="text-align:center;">{{ number_format($total, 2) }}</td>
                                    <td>{{ $comentario }}</td>
                                </tr>
                            @endforeach
                            {{-- Fila subtotal monedas --}}
                            <tr style="font-weight: bold; background-color: #e0f2f1;">
                                <td colspan="3" style="text-align:right;">Subtotal Monedas:</td>
                                <td style="text-align:center;">{{ number_format($subtotalMonedas, 2) }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </td>

                {{-- Billetes --}}
                <td style="vertical-align: top; width: 50%;">
                    <table class="table-full-soft-bordered xx-small" width="100%">
                        <thead style="background-color: #00897b;" class="white-text">
                            <tr>
                                <th style="text-align:center; padding:6px;">Dinero</th>
                                <th style="text-align:center; padding:6px;">Cant.</th>
                                <th style="text-align:right;"></th>
                                <th style="text-align:center; padding:6px;">Total</th>
                                <th style="text-align:center; padding:6px;">Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $subtotalBilletes = 0; @endphp
                            @foreach([7 => '10 soles', 8 => '20 soles', 9 => '50 soles', 10 => '100 soles', 11 => '200 soles'] as $id => $nombre)
                                @php
                                    $item = $mapaDinero->get($id);
                                    $cantidad = $item->cantidad ?? 0;
                                    $valor = $item && $item->dinero ? $item->dinero->valor : 0;
                                    $comentario = $item->comentario ?? '';
                                    $total = $cantidad * $valor;
                                    $subtotalBilletes += $total;
                                @endphp
                                <tr>
                                    <td>{{ $nombre }}</td>
                                    <td style="text-align:center;">{{ $cantidad }}</td>
                                    <td width="5%">PEN</td>
                                    <td width="15%" style="text-align:center;">{{ number_format($total, 2) }}</td>
                                    <td>{{ $comentario }}</td>
                                </tr>
                            @endforeach
                            {{-- Fila subtotal billetes --}}
                            <tr style="font-weight: bold; background-color: #e0f2f1;">
                                <td colspan="3" style="text-align:right;">Subtotal Billetes:</td>
                                <td style="text-align:center;">{{ number_format($subtotalBilletes, 2) }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Total final --}}
        @php
            $totalFinal = $subtotalMonedas + $subtotalBilletes;
        @endphp
        <table class="table-full-soft-bordered xx-small" width="50%" style="margin:auto;">
            <thead style="background-color: #00897b;" class="white-text">
                <tr>
                    <th style="text-align:center; padding:6px;">Moneda</th>
                    <th style="text-align:right; padding:6px;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align:center;">PEN</td>
                    <td style="text-align:right;">{{ number_format($totalFinal, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>



    <!-- Detalles de cierre -->
    <div class="new-page with-header-offset">
        <!-- Ventas por Métodos de Pago - PAGADAS -->
        <div class="section-title " style="text-align: center; color: #00897b">VENTAS POR MÉTODOS DE PAGO - PAGADAS
        </div>
        <br>
        @foreach ($ventasPorMetodos as $metodo)
            <table class="table-full-soft-bordered xx-small" width="100%" style="table-layout: fixed;">
                <thead style="background-color: #00897b;" class="white-text">
                    <tr>
                        <th align="left" width="50%">{{ $metodo['medio'] }}</th>
                        <th align="right" width="50%">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($metodo['detalles'] as $detalle)
                        <tr>
                            <td align="left" width="50%">{{ $detalle['detalle'] }}</td>
                            <td align="right" width="50%">{{ number_format($detalle['monto'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td align="left" width="50%"><strong>Totales</strong></td>
                        <td align="right" width="50%"><strong>{{ number_format($metodo['total'], 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>

            <br>
        @endforeach
    </div>



    <div class="section-title " style="text-align: center">RESÚMENES - VENTAS PAGADAS</div>
    <br>
    <table class="table-full-soft-bordered xx-small" width="100%">
        <thead style="background-color: #00897b;" class="white-text">
            <tr>
                <th align="left">Metodo de Pago</th>
                <th align="right">Monto (PEN)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ventasPagadas as $metodo => $monto)
                <tr>
                    <td width="50%">{{ $metodo }}</td>
                    <td align="right" width="50%">{{ number_format($monto, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td width="50%"><strong>Totales</strong></td>
                <td align="right" width="50%"><strong>{{ number_format(array_sum($ventasPagadas), 2) }}</strong>
                </td> <!-- Total de ventas pagadas -->
            </tr>
        </tbody>
    </table>



    <div class="new-page with-header-offset">
        <div class="section-title " style="text-align: center; color: #00897b">DETALLES DE VENTA - POR PRODUCTO</div>
        <br>

        @foreach ($detallesPorCategoria as $categoria => $detalles)
            <div class="category-section">
                <h3 style="text-align: center;">{{ $categoria }}</h3>

                <table class="table-full-soft-bordered xx-small" width="100%">
                    <thead style="background-color: #00897b;" class="white-text">
                        <tr>
                            <th width="20%">Producto</th>
                            <th width="10%">Unidad</th>
                            <th width="15%">P. Unitario</th> <!-- Opcional -->
                            <th width="15%">Stock de entrada</th>
                            <th width="15%">Cantidad vendida</th>
                            <th width="15%">Stock de salida</th>
                            <th width="10%">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($detalles as $detalle)
                            <tr>
                                <td width="20%">{{ $detalle['descripcion'] }}</td>
                                <td width="10%">{{ $detalle['unidad'] }}</td>
                                <td width="15%">{{ number_format($detalle['precio_unitario'], 2) }}</td>
                                <!-- Opcional -->
                                <td width="15%">{{ number_format($detalle['stock_inicial'], 2) }}</td>
                                <td width="15%">{{ number_format($detalle['cantidad_total'], 2) }}</td>
                                <td width="15%">{{ number_format($detalle['stock_final'], 2) }}</td>
                                <td width="10%">{{ number_format($detalle['subtotal_total'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="6" style="text-align: right;"><strong>Totales</strong></td>
                            <td><strong>{{ number_format($detalles->sum('subtotal_total'), 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>

    <div class="new-page with-header-offset">
        <div class="section-title" style="text-align: center; color: #00897b">
            CONSOLIDADO DE PRODUCTOS VENDIDOS
        </div>
        @foreach ($consolidadoPorCategoria as $categoria => $productos)
            <h3 style="text-align: center; font-weight: bold;">{{ $categoria }}</h3>

            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead style="background-color: #00897b; color: white;">
                    <tr>
                        <th width="40%">Producto</th>
                        <th width="20%">Unidad</th>
                        <th width="20%">Cantidad</th>
                        <th width="20%">Tot. (PEN)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $producto)
                        <tr>
                            <td>{{ $producto['descripcion'] }}</td>
                            <td>{{ $producto['unidad'] }}</td>
                            <td>{{ number_format($producto['cantidad'], 2) }}</td>
                            <td>{{ number_format($producto['total'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Totales</strong></td>
                        <td><strong>{{ number_format(collect($productos)->sum('total'), 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
            <br>
        @endforeach
    </div>

    <div class="new-page with-header-offset">
        {{-- ANULACIONES DE VENTAS --}}
        <div class="section-title" style="text-align: center; color: #00897b">
            ANULACIONES DE VENTAS
        </div>

        @foreach ($anulacionesVentasPorCategoria as $categoria => $productos)
            <h3 style="text-align: center; font-weight: bold;">{{ $categoria }}</h3>

            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead style="background-color: #00897b; color: white;">
                    <tr>
                        <th width="40%">Producto</th>
                        <th width="20%">Unidad</th>
                        <th width="20%">Cantidad</th>
                        <th width="20%">Subt. (PEN)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $producto)
                        <tr>
                            <td>{{ $producto['descripcion'] }}</td>
                            <td>{{ $producto['unidad'] }}</td>
                            <td>{{ number_format($producto['cantidad'], 3) }}</td>
                            <td>{{ number_format($producto['subtotal'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Totales</strong></td>
                        <td><strong>{{ number_format(collect($productos)->sum('subtotal'), 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
            <br>
        @endforeach

      {{-- ANULACIONES DE ALOJAMIENTOS --}}
<div class="section-title" style="text-align: center; color: #00897b">
    ANULACIONES DE ALOJAMIENTOS
</div>
  <br>
<table class="table-full-soft-bordered xx-small" width="100%">
    <thead style="background-color: #00897b; color: white;">
        <tr>
            <th>Habitación</th>
            <th>Ingreso</th>
            <th>Salida</th>
            <th>Días</th>
            <th>Total (PEN)</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($anulacionesAlojamientos as $alojamiento)
            <tr>
                <td>
                    {{ optional($alojamiento->habitacion)->numero ?? '—' }}
                </td>
                <td>{{ $alojamiento->fechaingreso }}</td>
                <td>{{ $alojamiento->fechasalida }}</td>
                <td>{{ $alojamiento->dias }}</td>
                <td>
                    {{ number_format($alojamiento->total, 2) }}
                </td>
            </tr>
        @empty
         
        @endforelse
          <tr>
                        <td colspan="4" style="text-align: right;"><strong>Totales</strong></td>
                        <td><strong>{{ number_format(collect($anulacionesAlojamientos)->sum('total'), 2) }}</strong></td>
                    </tr>
    </tbody>
</table>

<br>



    </div>

    <div class="new-page with-header-offset">
        <div class="section-title" style="text-align: center; color: #00897b">
            HISTÓRICO DE ANULACIONES
        </div>
        <br>
        <table class="table-full-soft-bordered xx-small" width="100%">
            <thead style="background-color: #00897b; color: white;">
                <tr>
                    <th width="15%">Fecha</th>
                    <th width="15%">Numero</th>
                    <th width="25%">Responsable</th>
                    <th width="35%">Motivo</th>
                    <th width="35%">Detalles</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($anulaciones as $anulacion)
                    <tr>
                        <td>
                            {{ \Carbon\Carbon::parse($anulacion->fecha)->format('Y-m-d') }}<br>
                            {{ \Carbon\Carbon::parse($anulacion->fecha)->format('h:i:s A') }}
                        </td>
                        <td>{{ $anulacion->movimiento->movventa?->serie }}-{{ $anulacion->movimiento->numero }}-{{ $anulacion->movimiento->movventa?->anio }}
                        </td>

                        <td>{{ $anulacion->usuario->persona->personamaestro->nrodoc}} -
                            {{ $anulacion->usuario->name ?? 'Sin Usuario' }}
                        </td>
                        <td>{{ $anulacion->motivo }}</td>
                        <td>
                            @foreach ($anulacion->movimiento->movventa?->detMovventas ?? [] as $detalle)
                                &bull; ({{ (int) $detalle->cantidad }}) {{ $detalle->descripcion }}<br>
                            @endforeach
                        </td>


                    </tr>

                @endforeach
            </tbody>
        </table>
    </div>

    <div class="new-page with-header-offset">
        <div class="section-title " style="text-align: center">DETALLES DE PAGO POR HABITACIÓN
        </div>
        <br>
        <table class="table-full-soft-bordered xx-small" width="100%">
            <thead style="background-color: #00897b;" class="white-text">
                <tr>
                    <th>Habitación</th>
                    <th>Fecha Ingreso</th>
                    <th>Fecha Salida Programada</th>
                    <th>Fecha Checkout</th>

                    <th>Días</th>
                    <th>Precio Habitación (PEN)</th>
                    <th>Precio Productos (PEN)</th>
                    <th>Total (PEN)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($historialhabitacionPago as $hab)
                    @if ($hab->situacion_pago == 'S')
                        <tr>
                            <td>{{ $hab->movhabitacion?->habitacion->numero }}</td>
                            <td>{{ $hab->movhabitacion?->fechaingreso }}</td>
                            <td>{{ $hab->movhabitacion?->fechasalida }}</td>
                            <td>{{ $hab->movhabitacion?->fecha_salida_real }}</td>

                            <td>{{ $hab->movhabitacion?->dias }}</td>
                            <td>{{ number_format($hab->movhabitacion?->preciohabitacion, 2) }}</td>
                            <td>{{ number_format($hab->movhabitacion?->precio_productos, 2) }}</td>
                            <td>{{ number_format($hab->movhabitacion?->total, 2) }}</td>
                        </tr>
                    @endif

                @endforeach
                <tr>
                    <td colspan="7" style="text-align: right;"><strong>Total de alojamientos</strong></td>
                    <td><strong>{{ number_format($totalHabitaciones, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
        <br>
        <div class="section-title " style="text-align: center">DETALLES DE HABITACIONES CON CHECKOUT</div>
        <br>
        <table class="table-full-soft-bordered xx-small" width="100%">
            <thead style="background-color: #00897b;" class="white-text">
                <tr>
                    <th>Habitación</th>
                    <th>Fecha Ingreso</th>
                    <th>Fecha Salida Programada</th>
                    <th>Fecha Checkout</th>

                    <th>Días</th>
                    <th>Precio Habitación (PEN)</th>
                    <th>Precio Productos (PEN)</th>
                    <th>Total (PEN)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($movhabitacionesconcheckout as $hab)
                    <tr>
                        <td>{{ $hab->movhabitacion?->habitacion->numero }}</td>
                        <td>{{ $hab->movhabitacion?->fechaingreso }}</td>
                        <td>{{ $hab->movhabitacion?->fechasalida }}</td>
                        <td>{{ $hab->movhabitacion?->fecha_salida_real }}</td>

                        <td>{{ $hab->movhabitacion?->dias }}</td>
                        <td>{{ number_format($hab->movhabitacion->preciohabitacion, 2) }}</td>
                        <td>{{ number_format($hab->movhabitacion->precio_productos, 2) }}</td>
                        <td>{{ number_format($hab->movhabitacion->total, 2) }}</td>
                    </tr>

                @endforeach

                <tr>
                    <td colspan="7" style="text-align: right;"><strong>Total de alojamientos</strong></td>
                    <td><strong>{{ number_format($totalHabitacionesconcheck, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
        <br>
        <div class="new-page with-header-offset">
        <div class="section-title " style="text-align: center">DETALLES DE HABITACIONES SIN CHECKOUT PAGADAS</div>
        <br>
        <table class="table-full-soft-bordered xx-small" width="100%">
            <thead style="background-color: #00897b;" class="white-text">
                <tr>
                    <th>Habitación</th>
                    <th>Fecha Ingreso</th>
                    <th>Fecha Salida Programada</th>
                    <th>Fecha Checkout</th>
                    <th>Días</th>
                    <th>Precio Habitación (PEN)</th>
                    <th>Precio Productos (PEN)</th>
                    <th>Total (PEN)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($movhabitacionessincheckoutpagadas as $hab)
                    <tr>
                        <td>{{ $hab->movhabitacion?->habitacion->numero }}</td>
                        <td>{{ $hab->movhabitacion?->fechaingreso }}</td>
                        <td>{{ $hab->movhabitacion?->fechasalida }}</td>
                        <td></td>

                        <td>{{ $hab->movhabitacion?->dias }}</td>
                        <td>{{ number_format($hab->movhabitacion?->preciohabitacion, 2) }}</td>
                        <td>{{ number_format($hab->movhabitacion?->precio_productos, 2) }}</td>
                        <td>{{ number_format($hab->movhabitacion?->total, 2) }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="7" style="text-align: right;"><strong>Total de alojamientos</strong></td>
                    <td><strong>{{ number_format($totalHabitacionessincheck, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
        <br>
        <div class="section-title " style="text-align: center">DETALLES DE HABITACIONES SIN CHECKOUT EN DEUDA</div>
        <br>
        <table class="table-full-soft-bordered xx-small" width="100%">
            <thead style="background-color: #00897b;" class="white-text">
                <tr>
                    <th>Habitación</th>
                    <th>Fecha Ingreso</th>
                    <th>Fecha Salida Programada</th>
                    <th>Fecha Checkout</th>

                    <th>Días</th>
                    <th>Precio Habitación (PEN)</th>
                    <th>Precio Productos (PEN)</th>
                    <th>Total (PEN)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($movhabitacionessinpagar as $hab)
                    <tr>
                        <td>{{ $hab->movhabitacion?->habitacion->numero }}</td>
                        <td>{{ $hab->movhabitacion?->fechaingreso }}</td>
                        <td>{{ $hab->movhabitacion?->fechasalida }}</td>
                        <td></td>

                        <td>{{ $hab->movhabitacion?->dias }}</td>
                        <td>{{ number_format($hab->movhabitacion?->preciohabitacion, 2) }}</td>
                        <td>{{ number_format($hab->movhabitacion?->precio_productos, 2) }}</td>
                        <td>{{ number_format($hab->movhabitacion?->total, 2) }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="7" style="text-align: right;"><strong>Total de deuda</strong></td>
                    <td><strong>{{ number_format($totalHabitacionessincheckEnDeuda, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
         </div>
    </div>
        

    <div class="new-page with-header-offset">
        <div class="section-title " style="text-align: center">INGRESOS POR MÉTODOS DE PAGO</div>
        <br>
        <table class="table-full-soft-bordered xx-small" width="100%">
            <thead style="background-color: #00897b;" class="white-text">
                <tr>
                    <th>Concepto</th>
                    <th>Persona</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Otros ingresos</td>
                    <td>0 - CLIENTES VARIOS</td>
                </tr>
            </tbody>
        </table>

        <div class="section-title " style="text-align: center">PAGADOS</div>
        <br>
        <table class="table-full-soft-bordered xx-small" width="100%">
            <thead style="background-color: #00897b;" class="white-text">
                <tr>
                    <th>Método de Pago</th>
                    <th>Monto (PEN)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ingresosPagados as $metodo => $monto)
                    <tr>
                        <td>{{ $metodo }}</td>
                        <td>{{ number_format($monto, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><strong>Totales</strong></td>
                    <td><strong>{{ number_format(array_sum($ingresosPagados), 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="new-page with-header-offset">
        <div class="section-title " style="text-align: center">EGRESOS (GASTOS) POR MÉTODOS DE PAGO</div>
        <br>
        <table class="table-full-soft-bordered xx-small" width="100%">
            <thead style="background-color: #00897b;" class="white-text">
                <tr>
                    <th>Concepto</th>
                    <th>Persona</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Otros egresos</td>
                    <td>0 - CLIENTES VARIOS</td>
                </tr>
            </tbody>
        </table>

        <div class="section-title " style="text-align: center">PAGADOS</div>
        <br>
        <table class="table-full-soft-bordered xx-small" width="100%">
            <thead style="background-color: #00897b;" class="white-text">
                <tr>
                    <th>Método de Pago</th>
                    <th>Monto (PEN)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($egresosPagados as $metodo => $monto)
                    <tr>
                        <td>{{ $metodo }}</td>
                        <td>{{ number_format($monto, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><strong>Totales</strong></td>
                    <td><strong>{{ number_format(array_sum($egresosPagados), 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="new-page with-header-offset">
        <h3 style="text-align:center; color:#00897b; font-weight:bold; margin-bottom:10px;">
            DESCUENTOS - POR PERSONA
        </h3>

        <table class="table-full-soft-bordered xx-small" width="100%">
            <thead style="background-color: #00897b;" class="white-text">
                <tr>
                    <!-- Celda Persona que abarca 2 filas -->
                    <th rowspan="2" style="text-align:left; padding:8px; width:60%;">Persona</th>
                    <th colspan="2" style="text-align:center; padding:8px;">Montos</th>
                </tr>
                <tr>
                    <th style="text-align:center; padding:6px;">Subtotal (PEN)</th>
                    <th style="text-align:center; padding:6px;">Dcto. (PEN)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($descuentosPorPersona as $persona => $datos)
                    <tr>
                        <td>{{ $persona }}</td>
                        <td style="text-align:right;">{{ number_format($datos['subtotal'], 2) }}</td>
                        <td style="text-align:right;">{{ number_format($datos['descuento'], 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><strong>Totales</strong></td>
                    <td style="text-align:right;">
                        <strong>{{ number_format($descuentosPorPersona->sum('subtotal'), 2) }}</strong>
                    </td>
                    <td style="text-align:right;">
                        <strong>{{ number_format($descuentosPorPersona->sum('descuento'), 2) }}</strong>
                    </td>
                </tr>
            </tbody>



        </table>
    </div>

<br>
    <p style="text-align:center;">
       <img src="{{ public_path('img/Logo_HotelHub_tr.png') }}" alt="Logo" height="120">


    </p>



</body>

</html>