
<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <p class="grey-text text-darken-3 without-tb center card-title" id="modal-title">
                    <i class="fa-solid fa-cash-register left"></i>Caja chica | {{$nota}} | {{$mediopago}}
                </p>
            </div>
           
            <div class="card-body bg-white">
                <div id="tb-container" class="table-responsive">
                    <table id="data-table" class="highlight striped centered" style="width:100%">
                        <thead class="thead-custom">
                            <tr>
                                <th>Número</th>
                                <th>Tipo</th>
                                <th>Concepto</th>
                                <th>Total</th>
                                <th>Total para el medio de pago</th>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Caja</th>
                                <th>Turno</th>
                                <th>Persona</th>
                                <th>Responsable</th>
                               
                                <th>Situación</th>

                                <th>Moneda</th>

                                <th>Origen</th>
                                <th>T. cambio</th>
                                <th>Comentario</th>
                                 <th>Métodos de pago</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($movcajas as $movcaja)
                                <tr>
                                    <td>{{ $movcaja->movimiento->numero ?? 'N/A' }}</td>
                                    <td>
                                        <span
                                            class="new badge {{ $movcaja->movimiento->tipodocumento->nombre == 'Ingreso' ? 'teal' : 'red' }}"
                                            data-badge-caption="{{ $movcaja->movimiento->tipodocumento->nombre }}"></span>
                                    </td>
                                    <td>
                                        <span
                                            class="new badge {{ $movcaja->conceptopago->descripcion == 'Apertura de caja' ? 'yellow black-text' : 'orange' }}"
                                            data-badge-caption="{{ $movcaja->conceptopago->descripcion }}"></span>
                                    </td>
                                    <td><span
                                            style="font-size: 1.5rem; font-weight: bold;">{{ number_format($movcaja->total, 2) }}</span>
                                    </td>
                                    <td><span
                                        style="font-size: 1.5rem; font-weight: bold;">   @php
                                        $pagosAgrupados = $movcaja->detMovcaja
                                            ->groupBy(function ($item) {
                                                return $item->Mediopago->descripcion; // Agrupar por la descripción del medio de pago
                                            })
                                            ->map(function ($items) {
                                                return $items->sum('monto'); // Sumar los montos para cada medio de pago
                                            });
                                    @endphp

                                    @foreach ($pagosAgrupados as $medio => $monto)
                                        @if ($medio == $mediopago)
                                        {{ number_format($monto, 2) }}<br>

                                        @endif
                                    @endforeach</span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($movcaja->created_at)->format('Y-m-d') }}<br>
                                                    {{ \Carbon\Carbon::parse($movcaja->created_at)->format('h:i:s A') }}</td>
                                    <td>{{ $movcaja->movimiento->usuario ?? 'N/A' }}</td>
                                    <td>{{ $movcaja->caja ?? 'N/A' }}</td>
                                    <td>{{ $movcaja->turno->nombre ?? 'N/A' }}</td>
                                    <td>{{ $movcaja->movimiento->Persona->personamaestro->nrodoc ?? 'N/A' }} -
                                        {{ $movcaja->movimiento->persona ?? 'N/A' }}</td>
                                    <td>{{ $movcaja->movimiento->Responsable->personamaestro->nrodoc ?? 'N/A' }} -
                                        {{ $movcaja->movimiento->responsable ?? 'N/A' }}</td>
                                  
                              
                                   
                                    <td>
                                        <span
                                            class="new badge {{ $movcaja->movimiento->situacion == 'Activado' ? 'green' : 'red' }}"
                                            data-badge-caption="{{ $movcaja->movimiento->situacion }}"></span>
                                    </td>
                                    <td>{{ $movcaja->moneda ?? 'N/A' }}</td>

  @if ($movcaja->movventa)
                                                    <td> {{ $movcaja->movventa->movimiento->tipodocumento->tipomovimiento->descripcion ?? 'N/A' }}
                                                        -
                                                        {{ $movcaja->movventa->serie }}-{{ $movcaja->movventa->movimiento->numero }}-{{ $movcaja->movventa->anio }}
                                                    </td>
                                                @else
                                                    <td>{{ $movcaja->movimiento->tipodocumento->tipomovimiento->descripcion ?? 'N/A' }}
                                                    </td>
                                                @endif
                                                                              <td>{{ $movcaja->tipocambio ?? 'N/A' }}</td>
                                    <td>{{ $movcaja->movimiento->comentario ?? 'N/A' }}</td>
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