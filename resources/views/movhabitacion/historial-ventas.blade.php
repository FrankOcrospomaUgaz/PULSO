<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title"><i class="fa-solid fa-cash-register left"></i>Historial de Ventas </span>
            </div>
            <div class="card-body bg-white">
                <div id="tb-container" class="table-responsive">
                    <table class="highlight striped centered data-table" style="width:100%">
                        <thead class="thead-custom">
                            <tr>
                                <th  class="no-hide">Comprobante</th>

                                <th class="no-hide">Total</th>

                                <th class="no-hide">Fecha</th>
                                <th class="no-hide">Detalle</th>
                                <th class="no-hide">Usuario</th>
                                <th class="no-hide">Caja</th>
                                <th class="no-hide">Turno</th>
                                <th class="no-hide">Persona</th>
                                <th>Responsable</th>
                                <th class="no-hide">Métodos de pago</th>
                                <th>Situación</th>

                                <th>Moneda</th>

                                <th>Origen</th>
                                <th>T. cambio</th>
                                <th>Comentario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($movcajas as $movcaja)
                                <tr>
                                    <td>{{ $movcaja->movventa->serie }}-{{ $movcaja->movventa->movimiento->numero }}
                                    </td>

                                    <td><span
                                            style="font-size: 1.5rem; font-weight: bold;">{{ number_format($movcaja->total, 2) }}</span>
                                    </td>

                                    <td>{{ $movcaja->movimiento->fecha ?? 'N/A' }}</td>
                                    <td>
                                        @foreach ($movcaja->movventa->detMovventas as $detalle)
                                            {{ $detalle->descripcion }}<br>
                                        @endforeach
                                        @if ($movcaja->movventa->detMovventas->isEmpty())
                                            N/A
                                        @endif
                                    </td>

                                    <td>{{ $movcaja->movimiento->usuario ?? 'N/A' }}</td>
                                    <td>{{ $movcaja->caja ?? 'N/A' }}</td>
                                    <td>{{ $movcaja->turno->nombre ?? 'N/A' }}</td>
                                    <td>{{ $movcaja->movimiento->Persona->personamaestro->nrodoc ?? 'N/A' }} -
                                        {{ $movcaja->movimiento->persona ?? 'N/A' }}</td>
                                    <td>{{ $movcaja->movimiento->Responsable->personamaestro->nrodoc ?? 'N/A' }} -
                                        {{ $movcaja->movimiento->responsable ?? 'N/A' }}</td>


                                     <td>
                                         @php
                                             $pagosAgrupados = $movcaja->detMovcaja
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
                                                 });
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
                                     </td>
                                    <td>
                                        <span
                                            class="new badge {{ $movcaja->movimiento->situacion == 'Activado' ? 'green' : 'red' }}"
                                            data-badge-caption="{{ $movcaja->movimiento->situacion }}"></span>
                                    </td>
                                    <td>{{ $movcaja->moneda ?? 'N/A' }}</td>


                                    <td>{{ $movcaja->movimiento->tipodocumento->tipomovimiento->descripcion ?? 'N/A' }}
                                    </td>
                                    <td>{{ $movcaja->tipocambio ?? 'N/A' }}</td>
                                    <td>{{ $movcaja->movimiento->comentario ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>
<div class="col s12 mt20 mt-2">
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
    </div>
</div>
