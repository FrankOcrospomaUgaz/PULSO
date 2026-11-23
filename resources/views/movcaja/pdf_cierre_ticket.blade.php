<!doctype html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="utf-8">
    <title>TICKET DE CIERRE</title>

    <style>
        @font-face {
            font-family: 'Verdana';
            font-style: normal;
            font-weight: normal;
            src: url({{ storage_path('fonts/verdana.ttf') }}) format('truetype');
        }

        @font-face {
            font-family: 'Verdana';
            font-style: normal;
            font-weight: bold;
            src: url({{ storage_path('fonts/verdana-bold.ttf') }}) format('truetype');
        }
    </style>
    <link type="text/css" href="css/ticket.css" rel="stylesheet">
</head>

<body>
    <header class="left super-small">
    </header>
    <header class="right super-small">
    </header>

    <footer class="left super-small">
    </footer>
    <footer class="super-small">
        Copyright&copy; {{ date('Y') }}. Garzasoft. Todos los derechos reservados.
    </footer>
    <footer class="right super-small">
    </footer>
    <!-- Encabezado con logo y datos de empresa -->
    {{-- <div class="header">
        <img src="{{ public_path('logo_empresa.png') }}" alt="Logo Empresa">
        <p><strong>GARZASOFT E.I.R.L.</strong></p>
        <p>Dirección: Calle 123, Ciudad - País | Tel: 123-456-7890</p>
        <p>RUC: 1234567890 | Email: contacto@garzasoft.com</p>
    </div> --}}
    <main>

        <h2 style="text-align: center">ARQUEO CAJA</h2>

        <!-- Información del turno y caja -->
        <div class="box-info">
            <p><strong>Turno:</strong> {{ $ultimoTurno->movcajainicio->turno->nombre }}</p>
            <p><strong>Caja:</strong> {{ $caja->numero }}</p>
            <p><strong>Fecha de Apertura:</strong> {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}</p>
            <p><strong>Usuario de Apertura:</strong> {{ Auth::user()->name }}</p>
            <p><strong>Fecha de Impresión:</strong> {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}</p>
            <p><strong>Usuario de Impresión:</strong> {{ Auth::user()->name }}</p>
        </div>
        <hr>
        <br>

        <!-- Totales de cierre -->
        <div class="section-title " style="text-align: center">TOTALES</div>
        <table class="super-small" width="100%">
            <thead class="bg-primary white-text">
                <tr>
                    <th>Concepto</th>
                    <th>Monto (PEN)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2">
                        <hr />
                    </td>
                </tr>
                @foreach ($totales as $concepto => $monto)
                    <tr>
                        <td>
                            @if ($concepto === 'Totales')
                                <strong>{{ $concepto }}</strong>
                            @else
                                {{ $concepto }}
                            @endif
                        </td>
                        <td align="right">
                            @if ($concepto === 'Totales')
                                <strong>{{ number_format($monto, 2) }}</strong>
                            @else
                                {{ number_format($monto, 2) }}
                            @endif
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2">
                        <hr />
                    </td>
                </tr>
            </tbody>
        </table>
        

        <!-- Detalles de cierre -->
        <div class="new-page">
            <br>

            <div class="section-title " style="text-align: center">DETALLES DE CIERRE</div>
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead class="bg-primary white-text">
                    <tr>
                        <th>Tipo</th>
                        <th>Monto (PEN)</th>
                        <th>Medio de Pago</th>
                        <th>Detalles</th>
                        <th>Concepto de Pago</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5">
                            <hr />
                        </td>
                    </tr>
                    @foreach ($detallesAgrupados as $detalle)
                        <tr>
                            <td>{{ $detalle['tipo'] }}</td>
                            <td>{{ number_format($detalle['monto'], 2) }}</td>
                            <td>{{ $detalle['mediopago'] }}</td>
                            <td>{{ $detalle['detalles'] ?? ' ' }}</td>
                            <td>{{ $detalle['nota'] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="5">
                            <hr />
                        </td>
                    </tr>
                </tbody>
            </table>
            
        </div>
        <br>

        <!-- Resumen de efectivo -->
        <div class="section-title " style="text-align: center">RESUMEN DE EFECTIVO (PEN)</div>
        <table class="table-full-soft-bordered xx-small" width="100%">
            <thead class="bg-primary white-text">
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
                    <td colspan="5">
                        <hr />
                    </td>
                </tr>
                <tr>
                    <td>{{ number_format($totales['Total + apertura'], 2) }}</td> <!-- Total general en caja -->
                    <td>{{ number_format($totales['Ventas | Efectivo'], 2) }}</td> <!-- Ventas en Efectivo -->
                    <td>{{ number_format($totales['Apertura'], 2) }}</td> <!-- Apertura de caja -->
                    <td>{{ number_format($totales['Otros ingresos'], 2) }}</td> <!-- Otros ingresos -->
                    <td>{{ number_format($totales['Egresos (gastos)'], 2) }}</td> <!-- Egresos -->
                </tr>
                <tr>
                    <td colspan="5">
                        <hr />
                    </td>
                </tr>
            </tbody>
        </table>
<br>        

        <!-- Ventas por Métodos de Pago - PAGADAS -->
        <div class="section-title " style="text-align: center">VENTAS POR MÉTODOS DE PAGO - PAGADAS</div>
        @foreach ($ventasPorMetodos as $metodo)
        <table class="table-full-soft-bordered xx-small" width="100%" style="table-layout: fixed;">
            <thead class="bg-primary white-text">
                <tr>
                    <th align="left" width="50%">{{ $metodo['medio'] }}</th>
                    <th align="right" width="50%">Monto</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2">
                        <hr />
                    </td>
                </tr>
                @foreach ($metodo['detalles'] as $detalle)
                    <tr>
                        <td align="left" width="50%">{{ $detalle['detalle'] }}</td>
                        <td align="right" width="50%">{{ number_format($detalle['monto'], 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2">
                        <hr />
                    </td>
                </tr>
                <tr>
                    <td align="left" width="50%"><strong>Totales</strong></td>
                    <td align="right" width="50%"><strong>{{ number_format($metodo['total'], 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
        

            <br>
        @endforeach
        <br>

        <div class="section-title " style="text-align: center">RESÚMENES - VENTAS PAGADAS</div>
        <table class="table-full-soft-bordered xx-small" width="100%">
            <thead class="bg-primary white-text">
                <tr>
                    <th align="left">Método de Pago</th>
                    <th align="right">Monto (PEN)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2">
                        <hr />
                    </td>
                </tr>
                @foreach ($ventasPagadas as $metodo => $monto)
                    <tr>
                        <td width="50%">{{ $metodo }}</td>
                        <td align="right" width="50%">{{ number_format($monto, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2">
                        <hr />
                    </td>
                </tr>
                <tr>
                    <td width="50%"><strong>Totales</strong></td>
                    <td align="right" width="50%">
                        <strong>{{ number_format(array_sum($ventasPagadas), 2) }}</strong>
                    </td> <!-- Total de ventas pagadas -->
                </tr>
            </tbody>
        </table>
        
        <div class="new-page">
            <br>

            <div class="section-title " style="text-align: center">DETALLES DE VENTA - POR PRODUCTO</div>

            @foreach ($detallesPorCategoria as $categoria => $detalles)
                <div class="category-section">
                    <h3>{{ $categoria }}</h3>
                    <table class="super-small" width="100%">
                        <thead>
                            <tr>
                                <th align="center">Producto</th>
                                <th align="center">P. Unitario</th> 
                                <th align="center">Stock Entrada</th>
                                <th align="center">Cant. Vendida</th>
                                <th align="center">Stock Salida</th>
                                <th align="center">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6">
                                    <hr />
                                </td>
                            </tr>
                            @foreach ($detalles as $detalle)
                                <tr>
                                    <td colspan="6" align="left">{{ $detalle['descripcion'] }}</td>
                                </tr>
                                <tr>
                                    <td align="left"></td>
                                    <td align="left">{{ number_format($detalle['precio_unitario'], 2) }}</td>
                                    <td align="left">{{ number_format($detalle['stock_inicial'], 2) }}</td>
                                    <td align="left">{{ number_format($detalle['cantidad_total'], 2) }}</td>
                                    <td align="left">{{ number_format($detalle['stock_final'], 2) }}</td>
                                    <td align="left">{{ number_format($detalle['subtotal_total'], 2) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="6">
                                    <hr />
                                </td>
                            </tr>
                            <tr>
                                <td colspan="5" style="text-align: right;"><strong>Totales</strong></td>
                                <td><strong>{{ number_format($detalles->sum('subtotal_total'), 2) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                    
                </div>
            @endforeach
        </div>

        <div class="new-page">
            <br>

            <div class="section-title " style="text-align: center">DETALLES DE PAGO POR HABITACIÓN
            </div>
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead class="bg-primary white-text">
                    <tr>
                        <th>Hab.</th>
                        <th>F. Ingreso</th>
                        {{-- <th>F. Salida Prog.</th> --}}
                        <th>F. Checkout</th>
                        <th>Días</th>
                        <th>Precio Hab. (PEN)</th>
                        <th>Precio Prod. (PEN)</th>
                        <th>Total (PEN)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="8">
                            <hr />
                        </td>
                    </tr>
                    @foreach ($historialhabitacionPago as $hab)
                        @if ($hab->situacion_pago == 'S')
                            <tr>
                                <td colspan="7" align="left">
                                   <strong>{{ optional(optional($hab->movhabitacion)->habitacion)->numero ?? 'N/A' }}</strong>

                                </td>
                            </tr>
                            <tr>
                                <td align="left"></td>
                              <td align="left">{{ $hab->movhabitacion->fechaingreso ?? '—' }}</td>
<td align="left">{{ $hab->movhabitacion->fecha_salida_real ?? '—' }}</td>
<td align="left">{{ $hab->movhabitacion->dias ?? 0 }}</td>
<td align="left">{{ number_format($hab->movhabitacion->preciohabitacion ?? 0, 2) }}</td>
<td align="left">{{ number_format($hab->movhabitacion->precio_productos ?? 0, 2) }}</td>
<td align="left">{{ number_format($hab->movhabitacion->total ?? 0, 2) }}</td>

                            </tr>
                        @endif
                    @endforeach
                    <tr>
                        <td colspan="7">
                            <hr />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6" style="text-align: right;"><strong>Total de alojamientos</strong></td>
                        <td><strong>{{ number_format($totalHabitaciones, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
            
            <br>
            <div class="section-title " style="text-align: center">DETALLES DE HABITACIONES CON CHECKOUT</div>
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead class="bg-primary white-text">
                    <tr>
                        <th>Hab.</th>
                        <th>F. Ingreso</th>
                        {{-- <th>F. Salida Prog.</th> --}}
                        <th>F. Checkout</th>
                        <th>Días</th>
                        <th>Precio Hab. (PEN)</th>
                        <th>Precio Prod. (PEN)</th>
                        <th>Total (PEN)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7">
                            <hr />
                        </td>
                    </tr>
                    @foreach ($movhabitacionesconcheckout as $hab)
                        <tr>
                            <td colspan="7" align="left">
                                <strong>{{ $hab->movhabitacion->habitacion->numero }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td align="left"></td>
                            <td align="left">{{ $hab->movhabitacion->fechaingreso }}</td>
                            <td align="left">{{ $hab->movhabitacion->fecha_salida_real }}</td>
                            <td align="left">{{ $hab->movhabitacion->dias }}</td>
                            <td align="left">{{ number_format($hab->movhabitacion->preciohabitacion, 2) }}</td>
                            <td align="left">{{ number_format($hab->movhabitacion->precio_productos, 2) }}</td>
                            <td align="left">{{ number_format($hab->movhabitacion->total, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="7">
                            <hr />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6" style="text-align: right;"><strong>Total de alojamientos</strong></td>
                        <td><strong>{{ number_format($totalHabitacionesconcheck, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
            
            <br>
            <div class="section-title " style="text-align: center">DETALLES DE HABITACIONES SIN CHECKOUT PAGADAS</div>
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead class="bg-primary white-text">
                    <tr>
                        <th>Hab.</th>
                        <th>F. Ingreso</th>
                        <th>F. Salida Prog.</th>
                        <th>Días</th>
                        <th>Precio Hab. (PEN)</th>
                        <th>Precio Prod. (PEN)</th>
                        <th>Total (PEN)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7">
                            <hr />
                        </td>
                    </tr>
                    @foreach ($movhabitacionessincheckoutpagadas as $hab)
                        <tr>
                            <td colspan="7" align="left">
                                <strong>{{ $hab->movhabitacion->habitacion->numero }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td align="left"></td>
                            <td align="left">{{ $hab->movhabitacion->fechaingreso }}</td>
                            <td align="left">{{ $hab->movhabitacion->fechasalida }}</td>
                            <td align="left">{{ $hab->movhabitacion->dias }}</td>
                            <td align="left">{{ number_format($hab->movhabitacion->preciohabitacion, 2) }}</td>
                            <td align="left">{{ number_format($hab->movhabitacion->precio_productos, 2) }}</td>
                            <td align="left">{{ number_format($hab->movhabitacion->total, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="7">
                            <hr />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6" style="text-align: right;"><strong>Total de alojamientos</strong></td>
                        <td><strong>{{ number_format($totalHabitacionessincheck, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
            
            <br>
            <div class="section-title " style="text-align: center">DETALLES DE HABITACIONES SIN CHECKOUT EN DEUDA
            </div>
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead class="bg-primary white-text">
                    <tr>
                        <th>Hab.</th>
                        <th>F. Ingreso</th>
                        <th>F. Salida Prog.</th>
                        <th>Días</th>
                        <th>Precio Hab. (PEN)</th>
                        <th>Precio Prod. (PEN)</th>
                        <th>Total (PEN)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7">
                            <hr />
                        </td>
                    </tr>
                    @foreach ($movhabitacionessinpagar as $hab)
                        <tr>
                            <td colspan="7" align="left">
                                <strong>{{ $hab->movhabitacion->habitacion->numero }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td align="left"></td>
                            <td align="left">{{ $hab->movhabitacion->fechaingreso }}</td>
                            <td align="left">{{ $hab->movhabitacion->fechasalida }}</td>
                            <td align="left">{{ $hab->movhabitacion->dias }}</td>
                            <td align="left">{{ number_format($hab->movhabitacion->preciohabitacion, 2) }}</td>
                            <td align="left">{{ number_format($hab->movhabitacion->precio_productos, 2) }}</td>
                            <td align="left">{{ number_format($hab->movhabitacion->total, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="7">
                            <hr />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6" style="text-align: right;"><strong>Total de deuda</strong></td>
                        <td><strong>{{ number_format($totalHabitacionessincheckEnDeuda, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
            
        </div>
        <div class="new-page">
            <br>

            <div class="section-title " style="text-align: center">INGRESOS POR MÉTODOS DE PAGO</div>
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead class="bg-primary white-text">
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
            <br>

            <div class="section-title " style="text-align: center">PAGADOS</div>
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead class="bg-primary white-text">
                    <tr>
                        <th>Método de Pago</th>
                        <th>Monto (PEN)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2">
                            <hr />
                        </td>
                    </tr>
                    @foreach ($ingresosPagados as $metodo => $monto)
                        <tr>
                            <td align="left"><strong>{{ $metodo }}</strong></td>
                            <td align="left">{{ number_format($monto, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="2">
                            <hr />
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><strong>Totales</strong></td>
                        <td><strong>{{ number_format(array_sum($ingresosPagados), 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
            
        </div>

        <div class="new-page">
            <br>

            <div class="section-title " style="text-align: center">EGRESOS (GASTOS) POR MÉTODOS DE PAGO</div>
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead class="bg-primary white-text">
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
            <br>

            <div class="section-title " style="text-align: center">PAGADOS</div>
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead class="bg-primary white-text">
                    <tr>
                        <th>Método de Pago</th>
                        <th>Monto (PEN)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2">
                            <hr />
                        </td>
                    </tr>
                    @foreach ($egresosPagados as $metodo => $monto)
                        <tr>
                            <td align="left"><strong>{{ $metodo }}</strong></td>
                            <td align="left">{{ number_format($monto, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="2">
                            <hr />
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><strong>Totales</strong></td>
                        <td><strong>{{ number_format(array_sum($egresosPagados), 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
            
        </div>



    </main>
<script type="text/php">
    if (isset($pdf)) {
        $pdf->page_text(
            $pdf->get_width() - 60,
            $pdf->get_height() - 19,
            "Pág. {PAGE_NUM} de {PAGE_COUNT}",
            null,
            7,
            array(0,0,0)
        );
    }
</script>

</body>

</html>
