@extends('layouts.app')

@section('content')
    <div class="main-body">
        <div class="row">
            <div class="card main-content">
                <div class="card-body bg-white">
                    <div class="row without-tb">
                        <div class="col s12 m6">
                            <p><strong>Comprobante:</strong> {{ $movcompra->serie }}-{{ $movcompra->movimiento->numero }}-{{ $movcompra->anio }}</p>
                            <p><strong>Tipo:</strong> {{ $movcompra->movimiento->tipodocumento->nombre ?? 'N/A' }}</p>
                            <p><strong>Proveedor:</strong> {{ $movcompra->movimiento->persona ?? 'N/A' }}</p>
                            <p><strong>Responsable:</strong> {{ $movcompra->movimiento->responsable ?? 'N/A' }}</p>
                        </div>
                        <div class="col s12 m6">
                            <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($movcompra->movimiento->fecha)->format('d/m/Y') }}</p>
                            <p><strong>Moneda:</strong> {{ $movcompra->moneda }}</p>
                            <p><strong>Tipo de pago:</strong> {{ $movcompra->tipo_pago_texto }}</p>
                            <p><strong>Situación:</strong> {{ $movcompra->movimiento->situacion }}</p>
                        </div>
                    </div>

                    <div class="table-wrapper" style="margin-top: 20px;">
                        <div class="table-scroll" style="overflow-x: auto;">
                            <table class="highlight striped centered" style="width: max-content; min-width: 100%;">
                                <thead class="thead-custom">
                                    <tr>
                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th>Unidad</th>
                                        <th>Cantidad</th>
                                        <th>Importe</th>
                                        <th>Notas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($movcompra->detMovcompras as $detalle)
                                        <tr>
                                            <td>{{ $detalle->codigo }}</td>
                                            <td>{{ $detalle->descripcion }}</td>
                                            <td>{{ $detalle->unidad->descripcion ?? '' }}</td>
                                            <td class="inpt-cantidad-mc">{{ number_format($detalle->cantidad, 2) }}</td>
                                            <td class="inpt-cantidad-mc">{{ number_format($detalle->monto, 2) }}</td>
                                            <td>{{ $detalle->comentario }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="right-align"><strong>Subtotal</strong></td>
                                        <td class="inpt-cantidad-mc"><strong>{{ number_format($movcompra->subtotal, 2) }}</strong></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="right-align"><strong>IGV</strong></td>
                                        <td class="inpt-cantidad-mc"><strong>{{ number_format($movcompra->igv, 2) }}</strong></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="right-align"><strong>Total</strong></td>
                                        <td class="inpt-cantidad-mc"><strong>{{ number_format($movcompra->total, 2) }}</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    @if ($movcompra->movcaja)
                        <h5 class="mt20">Movimiento de caja</h5>
                        <p><strong>Concepto:</strong> {{ $movcompra->movcaja->conceptopago->descripcion ?? '' }}</p>
                        <p><strong>Caja:</strong> {{ $movcompra->movcaja->caja }}</p>
                    @endif

                    <div class="row mt20">
                        <div class="col s12 right-align">
                            <a href="#" onclick="back();" class="btn-flat">VOLVER</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
