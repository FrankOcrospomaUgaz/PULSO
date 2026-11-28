    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $comprobante->nombre }}</title>

    <link rel="stylesheet" href="{{ public_path('css/pdf.css') }}">
    </head>

    <body>
        <header class="left xx-small">
        </header>
        <header class="right xx-small">
        </header>

        <footer class="right xx-small">
        </footer>

        <main>
            <table align="left" width="100%">
                <tr>
                    <td class="left" width="65%">
                        <p>
                        @php
                            $fileUri = function ($path) {
                                return 'file:///' . str_replace('\\', '/', $path);
                            };

                            $logoPath = public_path($sucursal->logo ?? '');

                            if (!file_exists($logoPath) || empty($sucursal->logo)) {
                                $logoPath = public_path('img/logo_h.png');
                            }
                        @endphp

                        <img src="{{ $fileUri($logoPath) }}" alt="Logo" height="100">

                        </p>
                    </td>
                    @php
                        $tipo_documento = '';
                        switch ($comprobante->tipo_documento) {
                            case 'F':
                                $tipo_documento = 'FACTURA DE VENTA ELECTRÓNICA';
                                break;
                            case 'B':
                                $tipo_documento = 'BOLETA DE VENTA ELECTRÓNICA';
                                break;
                            case 'C':
                                $tipo_documento = 'NOTA DE CRÉDITO ELECTRÓNICA';
                                break;
                            case 'C':
                                $tipo_documento = 'NOTA DE DÉBITO ELECTRÓNICA';
                                break;
                            case 'T':
                                $tipo_documento = 'TICKET DE VENTA';
                                break;
                            default:
                                break;
                        }
                    @endphp
                    <td class="table-soft-bordered" align="center" width="35%">
                        <h2 class="without-tb primary-text">{{ $tipo_documento }}</h2>
                        <p class="medium without-tb">
                            RUC: {{ $sucursal->ruc }} <br />
                            {{ $numero_comprobante }} <br />
                        </p>
                    </td>
                </tr>
            </table>
            <table width="100%">
                <tr>
                    <td align="left">
                        <p>
                            @php
                                $distrito = $sucursal->ubigeo;
                                $provincia = $distrito?->ubigeo;
                                $departamento = $provincia?->ubigeo;
                                $ubigeoTexto = collect([
                                    $departamento?->descripcion,
                                    $provincia?->descripcion,
                                    $distrito?->descripcion,
                                ])->filter()->implode(' - ');
                            @endphp
                            <font class="medium bold">{{ $sucursal->nombrecomercial }}</font> <br />
                            <font class="xx-small bold">{{ $sucursal->razonsocial }}</font> <br />
                            <font class="xx-small">
                                {{ $sucursal->direccion }} <br />
                                @if ($ubigeoTexto !== '')
                                    {{ $ubigeoTexto }} <br />
                                @endif
                                @if ($sucursal->telefonomovil != '')
                                    Celular: {{ $sucursal->telefonomovil }} <br />
                                @endif
                            </font>
                        </p>
                    </td>
                </tr>
            </table>
            <table class="xx-small" width="100%">
                <tr>
                    <td class="bold" width="30%">Fecha de emisión:</td>
                    <td width="70%">{{ $xml_data->get('fecha') }}</td>
                </tr>
                @if ($comprobante->tipo_documento == 'C')
                    <tr>
                        <td class="bold" width="30%">Número de referencia:</td>
                        <td width="70%">{{ $xml_data->get('nro_ref') }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="bold" width="30%">Señor(es):</td>
                    <td width="70%">{{ $xml_data->get('nombre_cliente') }}</td>
                </tr>
                <tr>
                    <td class="bold" width="30%">RUC/DNI:</td>
                    <td width="70%">{{ $xml_data->get('nro_doc') }}</td>
                </tr>
                <tr>
                    <td class="bold" width="30%">Dirección:</td>
                    <td width="70%">{{ $persona->direccion }}</td>
                </tr>
                <tr>
                    <td class="bold" width="30%">Moneda:</td>
                    <td width="70%">{{ $xml_data->get('moneda') }}</td>
                </tr>
                {{-- @php
                    $showOrderNotes = $movpedido && (session()->get('empresa_id') == 19);
                @endphp
                @if ($showOrderNotes)
                    <tr>
                        <td class="bold" width="30%">Datos de pedido:</td>
                        <td width="70%">{{ $movpedido->comentario }}</td>
                    </tr>
                @endif --}}
                @if ($comprobante->tipo_documento == 'F' || $comprobante->tipo_documento == 'C')
                    <tr>
                        <td class="bold" width="30%">Forma de pago:</td>
                        <td width="70%">{{ $xml_data->get('formapago') }}</td>
                    </tr>
                    @if (mb_strtoupper($xml_data->get('formapago')) == 'CREDITO')
                        @if ($xml_data->has('monto_neto_pendiente') && $xml_data->get('monto_neto_pendiente') !== null)
                            <tr>
                                <td class="bold" width="30%">Monto neto pendiente:</td>
                                <td width="70%">{{ $xml_data->get('moneda_simbolo') . ' ' . number_format(floatval($xml_data->get('monto_neto_pendiente')), 2, '.', '') }}</td>
                            </tr>
                        @endif
                        @if (count($xml_data->get('fecha_cuotas')) > 0)
                            <tr>
                                <td class="bold" width="30%">Cantidad de cuotas:</td>
                                <td width="70%">{{ count($xml_data->get('fecha_cuotas')) }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" width="100%">
                                    <table class="xx-small" width="40%">
                                        @foreach ($xml_data->get('fecha_cuotas') as $llave => $valor)
                                            <tr>
                                                <td class="left bold">Cuota {{ floatval($llave) + 1 }}</td>
                                                <td class="left">Fecha: {{ date_format(date_create($valor), 'd/m/Y') }}
                                                </td>
                                                <td class="right">Monto:
                                                    {{ $xml_data->get('moneda_simbolo') . ' ' . number_format(floatval($xml_data->get('monto_cuotas')[$llave]), 2, '.', '') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </td>
                            </tr>
                        @endif
                    @endif
                @endif
            </table>
            <br />
            <table class="table-full-soft-bordered xx-small" width="100%">
                <thead class="bg-primary white-text">
                    <tr>
                        <td align="center">Item</td>
                        <td align="center">Descripción</td>
                        <td align="center">U.M.</td>
                        <td align="center">Cantidad</td>
                        <td align="center">V.U.</td>
                        <td align="center">P.U.</td>
                        <td align="center">Dcto.</td>
                        <td align="center">Valor de venta</td>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total = 0;
                        $total_gravado = 0;
                        $total_exonerado = 0;
                        $total_inafecto = 0;
                        $igvPredeterminadoLinea = \App\Models\RelParametrosucursal::where('sucursal_id', $sucursal->id)
                            ->where('parametro_id', 8)
                            ->value('valor');
                        $igvSeleccionadoLinea =
                            request()->input('igv_id') == 0 ? $igvPredeterminadoLinea : request()->input('igv_id');
                        $igvObjetoLinea = \App\Models\Igv::find($igvSeleccionadoLinea);
                        $porcentajeIGVLinea = floatval($igvObjetoLinea?->porcentaje_igv ?? 18);
                    @endphp
                    @php
                        $contador = 1; // Inicializa un contador manual
                    @endphp
          
                    


                    <!-- Datos de Productos -->
                    @foreach ($xml_data->get('productos') as $llave => $valor)
                        @php
                            $cantidadItem = floatval($xml_data->get('cantidades')[$llave]) ?: 1;
                            $precioBrutoItem = floatval($xml_data->get('precios')[$llave]);
                            $tipoIgvItem = strval($xml_data->get('tipos_igv')[$llave] ?? '');
                            $porcentajeIGVLineaItem = floatval($xml_data->get('porcentajes_igv')[$llave] ?? $porcentajeIGVLinea);
                            $esItemGravado = $tipoIgvItem === '10';
                            $valorUnitarioItem = $esItemGravado
                                ? $precioBrutoItem / $cantidadItem / (1 + $porcentajeIGVLineaItem / 100)
                                : $precioBrutoItem / $cantidadItem;
                            $valorVentaItem = $esItemGravado
                                ? $precioBrutoItem / (1 + $porcentajeIGVLineaItem / 100)
                                : $precioBrutoItem;
                        @endphp
                        <tr>
                            <td align="center">{{ $contador }}</td> <!-- Usa el contador manual -->
                            <td align="left">{{ $valor }}</td>
                            <td align="center">
                                {{ $xml_data->get('unidades')[$llave] ?? '-' }}
                            </td>
                            <td align="right">{{ number_format($cantidadItem, 2) }}
                            </td>
                            <td align="right">
                                {{ $xml_data->get('moneda_simbolo') . ' ' . number_format($valorUnitarioItem, 3) }}
                            </td>
                            <td align="right">
                                {{ $xml_data->get('moneda_simbolo') . ' ' . number_format($precioBrutoItem / $cantidadItem, 3) }}
                            </td>
                            <td align="right">
                                {{ $xml_data->get('moneda_simbolo') . ' ' . number_format(floatval(0), 2) }}
                            </td>
                            <td align="right">
                                {{ $xml_data->get('moneda_simbolo') . ' ' . number_format($valorVentaItem, 2) }}
                            </td>
                        </tr>
                        @php
                            $total += $precioBrutoItem;
                            if ($xml_data->get('tipos_igv')[$llave] == '10') {
                                $total_gravado += $valorVentaItem;
                            }
                            if ($xml_data->get('tipos_igv')[$llave] == '20') {
                                $total_exonerado += $precioBrutoItem;
                            }
                            if ($xml_data->get('tipos_igv')[$llave] == '30') {
                                $total_inafecto += $precioBrutoItem;
                            }
                            $contador++; // Incrementa el contador
                        @endphp
                    @endforeach

                </tbody>
            </table>
            <br />
            <table class="xx-small" width="35%" align="right">
                @php
                    use App\Models\RelParametrosucursal;
                    use App\Models\Igv;

                    $igvPredeterminado = RelParametrosucursal::where('sucursal_id', $sucursal->id)
                        ->where('parametro_id', 8)
                        ->value('valor');

                    $igvSeleccionado =
                        request()->input('igv_id') == 0 ? $igvPredeterminado : request()->input('igv_id');
                    $igvObjeto = Igv::find($igvSeleccionado);
                    $porcentajeIGV = floatval($igvObjeto?->porcentaje_igv ?? 18); // fallback al 18% si no hay

                    $baseImponible = $total / (1 + $porcentajeIGV / 100);
                    $igvCalculado = $total - $baseImponible;
                @endphp

                <tr>
                    <td class="bold" width="70%">Op. gravada:</td>
                    <td width="30%" align="right">
                        {{ $xml_data->get('moneda_simbolo') . ' ' . number_format($baseImponible, 2) }}
                    </td>
                </tr>
                <tr>
                    <td class="bold" width="70%">I.G.V.:</td>
                    <td width="30%" align="right">
                        {{ $xml_data->get('moneda_simbolo') . ' ' . number_format($igvCalculado, 2) }}
                    </td>
                </tr>


                <tr>
                    <td class="bold" width="70%">Op. exonerada:</td>
                    <td width="30%" align="right">
                        {{ $xml_data->get('moneda_simbolo') . ' ' . number_format($total_exonerado, 2) }}</td>
                </tr>
                <tr>
                    <td class="bold" width="70%">Op. inafecta:</td>
                    <td width="30%" align="right">
                        {{ $xml_data->get('moneda_simbolo') . ' ' . number_format($total_inafecto, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <hr />
                    </td>
                </tr>
                <tr>
                    <td class="bold" width="70%">Importe total:</td>
                    <td width="30%" align="right">
                        {{ $xml_data->get('moneda_simbolo') . ' ' . number_format($total, 2) }}
                    </td>
                </tr>

            </table>
            <table class="xx-small" width="100%">
                <tr>
                    <td class="bold" width="30%">Observacion:</td>
                    <td width="70%">{{ $xml_data->get('observacion') }}</td>
                </tr>

            </table>

            @if (isset($movventa) && ($movventa->detraccion == 'S' || floatval($movventa->porcentaje_detraccion) > 0))
                @php
                    $porcentajeDetraccion = floatval($movventa->porcentaje_detraccion);
                    $montoDetraccion = ($total * $porcentajeDetraccion) / 100;
                    $montoTexto = ($montoDetraccion == intval($montoDetraccion)) ? intval($montoDetraccion) : number_format($montoDetraccion, 2);
                @endphp
                <br />
                <table class="xx-small" width="100%">
                    <tr>
                        <td width="100%">
                            Operación sujeta a detracción:<br />
                            Porcentaje: {{ $porcentajeDetraccion }}%<br />
                            Monto: {{ $montoTexto }}<br />
                            Cód. de bien: {{ $movventa->codigo_bien_detraccion }}<br />
                            Cuenta: {{ $movventa->cuenta_detraccion }}
                        </td>
                    </tr>
                </table>
            @endif
            @php
                use App\Helpers\NumeroTexto;
                $numeroTexto = new NumeroTexto();
                $numero_a_texto = $numeroTexto->convertirLetras($total, $xml_data->get('moneda'));
            @endphp

            <br />
            @if ($comprobante->tipo_documento != 'T')
                <table class="xx-small" width="100%">
                    <tr>
                        <td>
                            <p>{{ $numero_a_texto }}</p>
                            <p class="x-small">Representación impresa del comprobante electrónico, consulte en
                                https://www.pulsoplus.net</p>
                        </td>
                    </tr>
                </table>
            @endif
            @if ($xml_data->get('textoQR') != '')
                <table width="10%">
                    <tr>
                        <td align="center">
                            <img src="data:image/svg+xml;base64, {!! base64_encode(QrCode::format('svg')->size(90)->generate($xml_data->get('textoQR'))) !!} ">
                            <p class="xx-small">{{ $numero_comprobante }}</p>
                        </td>
                    </tr>
                </table>
            @endif
            <br/>
            
       <img src="{{ public_path(path: 'img/pulso-logo.jpg') }}" alt="Logo" height="80">
        </main>
    </body>

    </html>
