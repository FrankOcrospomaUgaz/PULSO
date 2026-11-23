<!doctype html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="utf-8">
    <title>{{ $dataGeneral->numero_comprobante }}</title>

 
       <link rel="stylesheet" href="{{ public_path('css/pdf.css') }}">

</head>

<body>
    <header class="left super-small">
    </header>
    <header class="right super-small">
    </header>



    <main>
        <table width="100%">
            <tr>
                <td align="center">

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
                    {{-- <img src="logos_sucursales/1738456160_WhatsApp Image 2025-02-01 at 3.49.05 PM.jpeg" alt="logo" class="img-fluid" style="max-width: 50%; max-height: 50%; margin-left: 25%; margin-top: -15%;" /> --}}

                </td>
                
            </tr>
            <tr>
                <td align="center">
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
                        {{-- @if (substr($dataGeneral->ruc, 0, 2) == '10')
                            <font class="xx-small bold">{{ $dataGeneral->nombrecomercial }}</font><br/>
                        @endif --}}
                        <font class="super-small bold">
                            {{ $dataGeneral->razonsocial }}<br/>
                            {{ $dataGeneral->direccion }}<br/>
                            @if ($ubigeoTexto !== '')
                                {{ $ubigeoTexto }}<br/>
                            @endif
                            @if (session()->get('sucursal_id') == 45)
                                {{ '985721935 - 957808052 - 901648436' }}<br/>
                            @endif
                            RUC: {{ $dataGeneral->ruc }}<br/>
                        </font>
                    </p>
                </td>
            </tr>
        </table>
        <table class="super-small" width="100%">
            <tr>
                <td class="bold" width="25%">{{ $dataGeneral->nombre_documento }}: </td>
                <td width="75%">{{ $dataGeneral->numero_comprobante }}</td>
            </tr>
            <tr>
                <td class="bold" width="30%">Fecha:</td>
                <td width="70%">{{ $dataGeneral->fecha }}</td>
            </tr>
            <tr>
                <td class="bold" width="30%">Cliente:</td>
                <td width="70%">{{ $dataGeneral->cliente_nombre }}</td>
            </tr>
            <tr>
                <td class="bold" width="30%">Dir.:</td>
                <td width="70%">{{ $dataGeneral->cliente_direccion }}</td>
            </tr>
            <tr>
                <td class="bold" width="30%">RUC/DNI:</td>
                <td width="70%">{{ $dataGeneral->cliente_nro_doc }}</td>
            </tr>
            <tr>
                <td class="bold" width="30%">Forma de pago:</td>
                <td width="70%">{!! str_replace("\n", '<br/>', $dataGeneral->tipo_pago) !!}</td>
            </tr>
            <tr>
                <td class="bold" width="30%">Notas:</td>
                <td width="70%">{!! str_replace("\n", '<br/>', $dataGeneral->observacion) !!}</td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr />
                </td>
            </tr>
        </table>
        <table class="super-small" width="100%">
            <thead class="">
                <tr>
                    <td align="center">Prod.</td>
                    <td align="center">Cant</td>
                    <td align="center">Unidad</td>
                    <td align="center">P.Unit.</td>
                    <td align="center">Subt.</td>
                </tr>
            </thead>
            <tbody>
            @php
                $totalItems = 0;
            @endphp

                <tr>
                    <td colspan="5">
                        <hr />
                    </td>
                </tr>
                @for ($i = 0; $i < count($dataDetalles); $i++)
                    @php
                        $dataDetalle = (object) $dataDetalles[$i];
                    @endphp
                    <tr>
                        <td colspan="5" align="left">{{ $dataDetalle->descripcion }}</td>
                    </tr>
                    <tr>
                        <td align="left"></td>
                        <td align="left">{{ number_format($dataDetalle->cantidad, 2, '.', '') }}</td>
                        <td align="left">{{ $dataDetalle->unidad }}</td>
                        <td align="left">{{ number_format($dataDetalle->monto / $dataDetalle->cantidad, 2, '.', '') }}</td>
                        <td align="left">{{ number_format($dataDetalle->subtotal, 2, '.', '') }}</td>
                    </tr>
                    @php
                        $totalItems += $dataDetalle->subtotal;
                    @endphp

                @endfor
        
            

                <tr>
                    <td colspan="5">
                        <hr />
                    </td>
                </tr>
            </tbody>
        </table>
        @php
    use App\Models\RelParametrosucursal;
    use App\Models\Igv;

    $igvPredeterminado = RelParametrosucursal::where('sucursal_id', $sucursal->id)
        ->where('parametro_id', 8)
        ->value('valor');

    $igvSeleccionado = request()->input('igv_id') == 0 ? $igvPredeterminado : request()->input('igv_id');
    $igvObjeto = Igv::find($igvSeleccionado);
    $porcentajeIGV = floatval($igvObjeto?->porcentaje_igv ?? 18); // fallback al 18% si no hay

    $totalComprobante = $dataGeneral->total ?? $totalItems;
    $baseImponible = $totalComprobante / (1 + ($porcentajeIGV / 100));
    $igvCalculado = $totalComprobante - $baseImponible;
@endphp

        @if ($dataGeneral->tipo_documento !== 'T')
            <table class="super-small" width="100%">
                <tr>
                    <td class="bold" width="50%">Op. gravada:</td>
                    <td width="50%" align="right">{{ $dataGeneral->monedaSimbolo . ' ' . number_format($baseImponible, 2) }}</td>
                    </tr>
                <tr>
                    <td class="bold" width="50%">I.G.V.:</td>
                    <td width="50%" align="right">{{ $dataGeneral->monedaSimbolo . ' ' . number_format($igvCalculado, 2) }}</td>
                    </tr>
                <tr>
                    <td class="bold" width="50%">Op. exonerada:</td>
                    <td width="50%" align="right">{{ $dataGeneral->monedaSimbolo . ' ' . number_format($dataGeneral->importe_exonerado, 2, '.', '') }}</td>
                </tr>
                <tr>
                    <td class="bold" width="50%">Op. inafecta:</td>
                    <td width="50%" align="right">{{ $dataGeneral->monedaSimbolo . ' ' . number_format($dataGeneral->importe_inafecto, 2, '.', '') }}</td>
                </tr>
            </table>
        @endif
        <table class="super-small" width="100%">
            <tr>
                <td class="bold" width="50%">Importe total:</td>
                <td class="bold" width="50%" align="right">{{ $dataGeneral->monedaSimbolo . ' ' . number_format($totalComprobante, 2, '.', '') }}</td>
                </tr>
                @php
use App\Helpers\NumeroTexto;
$numeroTexto = new NumeroTexto();
    $numero_a_texto = $numeroTexto->convertirLetras($totalComprobante, $xml_data['moneda']);
@endphp
<tr>
    <td colspan="2" width="100%">{{ $numero_a_texto }}</td>
</tr>

            <tr>
                <td colspan="2" width="100%">
                    <hr />
                </td>
            </tr>
        </table>
        @if ($dataGeneral->tipo_documento !== 'T')
            <table class="super-small" width="100%">
                <tr>
                    <td align="center">
                        <img src="data:image/svg+xml;base64, {!! base64_encode(QrCode::format('svg')->size(90)->generate($xml_data->get('textoQR'))) !!} ">
                    </td>
                </tr>
                <tr>
                    <td>
                        <hr />
                    </td>
                </tr>
            </table>
        @endif
        @if (isset($movventa) && ($movventa->detraccion == 'S' || floatval($movventa->porcentaje_detraccion) > 0))
            @php
                $porcentajeDetraccion = floatval($movventa->porcentaje_detraccion);
                $montoDetraccion = (($totalComprobante ?? 0) * $porcentajeDetraccion) / 100;
                $montoTexto = ($montoDetraccion == intval($montoDetraccion)) ? intval($montoDetraccion) : number_format($montoDetraccion, 2);
            @endphp
            <table class="super-small" width="100%">
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
        <table class="super-small" width="100%">
            <tr>
                <td class="bold" width="30%">Forma de pago:</td>
                <td width="70%" align="right">{!! str_replace("\n", '<br/>', $dataGeneral->tipo_pago) !!}</td>
            </tr>
            <tr>
                <td class="bold" width="30%">Hora:</td>
                <td width="70%" align="right">{{ date('H:i:s') }}</td>
            </tr>
        </table>
        @php
            $cuotas = $dataGeneral->cuotas ?? [];
        @endphp
        @if (count($cuotas) > 0)
            <table class="super-small" width="100%">
                <tr>
                    <td colspan="3">
                        <hr />
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="bold" width="100%" align="center">CUOTAS</td>
                </tr>
                @if (isset($dataGeneral->monto_neto_pendiente) && $dataGeneral->monto_neto_pendiente !== null)
                    <tr>
                        <td colspan="3" class="super-small" width="100%" align="center">
                            Monto neto pendiente: {{ $dataGeneral->monedaSimbolo . ' ' . number_format($dataGeneral->monto_neto_pendiente, 2, '.', '') }}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td colspan="3">
                        <hr />
                    </td>
                </tr>
            </table>
            <table class="super-small" width="100%">
                <thead class="">
                    <tr>
                        <td align="center">Nº</td>
                        <td align="center">Monto</td>
                        <td align="center">Fecha</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="3">
                            <hr />
                        </td>
                    </tr>
                    @for ($numero = 0; $numero < count($cuotas); $numero++)
                        @php
                            $cuota = (object) $cuotas[$numero];
                        @endphp
                        <tr>
                            <td align="left">{{ (intval($numero) + 1) }}</td>
                            <td align="left">{{ $dataGeneral->monedaSimbolo . ' '  . number_format($cuota->monto, 2, '.', '') }}</td>
                            <td align="left">{{ $cuota->fecha }}</td>
                        </tr>
                    @endfor
                    <tr>
                        <td colspan="3"><br/></td>
                    </tr>
                </tbody>
            </table>
        @endif
        @if ($dataGeneral->tipo_documento !== 'T')
            <table class="super-small" width="100%">
                <tr>
                    <td align="center">Representación impresa del comprobante electrónico. Consulte en <font class="bold">https://www.hotelhub.com.pe</font></td>
                </tr>
                <tr>
                    <td align="center">
                        <img src="{{ public_path('img/Logo_HotelHub_tr.png') }}" alt="Logo HotelHub" height="55">
                    </td>
                </tr>
            </table>
        @endif
        <table class="super-small" width="100%">
            <tr>
                <td align="center">
                    {{-- <img src="{{ public_path('assets/img/logo-completo.png') }}" alt="Logo" height="50px"> --}}
                </td>
            </tr>
           
            {{-- <tr>
                <td align="center">GRACIAS POR SU PREFERENCIA</td>
            </tr> --}}
            <tr>
                <td><br/></td>
            </tr>
        </table>
    </main>
</body>

</html>
