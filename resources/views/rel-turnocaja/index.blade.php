@extends('layouts.app')

@section('template_title')
    Rel Turnocaja
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">

                    <div class="row" id="filtros"
                        style="margin-bottom: 10px; display: flex; flex-wrap: wrap;">
                        <!-- Buscador + Botón Buscar -->
                        <div class="col s12 m6 left-align order-mobile-2 order-desktop-1">
                            <div style="display: flex; align-items: flex-start; gap: 5px; width: 100%;">
                                <!-- Input con label -->
                                <div class="input-field" style="flex: 1; margin-top: 0;">
                                    <input type="text" id="search-input" class="validate"
                                        data-url-base="{{ route('rel_turnocaja.index') }}">
                                    <label for="search-input">Buscar</label>
                                </div>

                                <!-- Botón Buscar -->
                                <a class="btn waves-effect waves-light darken-2 tooltipped btn-buscar" data-tooltip="Buscar"
                                    type="button" onclick="realizarBusqueda();">
                                    <i class="fa-solid fa-magnifying-glass white-text"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Botón NUEVO -->
                        <div class="col s12 m6 right-align order-mobile-1 order-desktop-2"
                            style="margin-top:0; padding-top:0;">
                            @foreach ($operaciones as $operacion)
                                @if ($operacion->tipo == 'Tabla')
                                    <a href="{{ route($operacion->accion) }}"
                                        class="btn waves-effect waves-light btn-crear {{ $operacion->color }}"
                                        style="border-radius: 10px; margin-top:0;">
                                        {!! $operacion->imagen !!} {{ $operacion->descripcion }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Contenedor del selector de registros por página y botones de expansión/filtros -->
                    <div class="row valign-wrapper" style="margin-bottom: 10px;">
                        <!-- Selector de registros -->
                        <div class="col s12 m8 l8 left-align valign-wrapper" style="padding-left:13px;">
                            <span style="margin-right: 8px;">Registros por página:</span>
                            <div class="input-field" style="margin: 0;">
                                <select id="records-per-page" class="browser-default"
                                    onchange="realizarBusqueda()">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>

                        <!-- Botones de expansión y filtros -->
                        <div class="col s12 m4 l4 right-align"
                            style="display: flex; align-items: center; justify-content: flex-end; padding-right:0;">
                            <button class="btn-flat btn-grid-options tooltipped" id="btn-expand-compress" type="button"
                                onclick="expandOrCompressChildRows();" data-position="top" data-tooltip="Expandir filas">
                                <i class="fa-solid fa-expand" style="color:#343a40;"></i>
                            </button>

                            <button class="btn-flat btn-grid-options tooltipped" id="btn-filtro" type="button"
                                onclick="mostrarFiltro();" data-position="top" data-tooltip="Filtros">
                                <i class="fa-solid fa-magnifying-glass-minus" style="color:#343a40;"></i>
                            </button>
                        </div>
                    </div>

                     <div id="tb-container" class="table-wrapper">
                        <!-- Contenedor con scroll horizontal solo para la tabla -->
                        <div class="table-scroll" style="overflow-x: auto;">
                            <table id="data-table" class="highlight striped centered"
                                style="width: max-content; min-width: 100%;">
                                <thead class="thead-custom">
                                    <tr>
                                        <th class="no-hide">F. Inicio</th>
                                        <th class="no-hide">F. Fin</th>
                                        <th>Nro. de apertura</th>
                                        <th>Caja</th>
                                        <th>Turno</th>
                                        <th>Detalle de turno</th>

                                        <th>Situación</th>
                                        <th>Nro. de cierre</th>

                                        <th>Operaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($relTurnocajas as $relTurnocaja)
                                        <tr>
                                            <td>
                                                {{ \Carbon\Carbon::parse($relTurnocaja->fechainicio)->format('Y-m-d') }}<br>
                                                {{ \Carbon\Carbon::parse($relTurnocaja->fechainicio)->format('h:i:s A') }}
                                            </td>
                                          
                                            @if ($relTurnocaja->fechafin == '')
                                                <td><span class="new badge" data-badge-caption="En curso"></span></td>
                                            @else
                                                 <td>
                                                {{ \Carbon\Carbon::parse($relTurnocaja->fechafin)->format('Y-m-d') }}<br>
                                                {{ \Carbon\Carbon::parse($relTurnocaja->fechafin)->format('h:i:s A') }}
                                                 </td>
                                            @endif



                                            <td>{{ $relTurnocaja->movcajainicio->movimiento->numero }}</td>
                                            <td>{{ $relTurnocaja->movcajainicio->caja }}</td>
                                            <td>{{ $relTurnocaja->movcajainicio->movimiento->movcaja->turno->nombre }}</td>
                                            <td>
                                                @php
                                                    // Obtener el turno actual
                                                    $ultimoTurno = $relTurnocaja;

                                                    // Obtener los movimientos relacionados con este turno
                                                    $movimientos = \App\Models\Movcaja::with([
                                                        'detMovcaja.Mediopago',
                                                        'conceptopago',
                                                    ])
                                                        ->where('caja_id', $ultimoTurno->movcajainicio->caja_id)
                                                        ->where('id', '>=', $ultimoTurno->movcajainicio_id)
                                                        ->when($ultimoTurno->movcajafin_id, function ($query) use (
                                                            $ultimoTurno,
                                                        ) {
                                                            $query->where('id', '<=', $ultimoTurno->movcajafin_id); // Limitar al rango del turno
                                                        })
                                                        ->whereHas('conceptopago', function ($query) {
                                                            $query->where('descripcion', '!=', 'Cierre de caja'); // Excluir cierre de caja
                                                        })
                                                        ->orderBy('created_at', 'asc')
                                                        ->get();

                                                    // Agrupar detalles por nota y medio de pago
                                                    $detallesAgrupados = $movimientos->flatMap->detMovcaja
                                                        ->groupBy(function ($detalle) {
                                                            $tipoConcepto = $detalle->movcaja->conceptopago->tipo;
                                                            $conceptoDescripcion =
                                                                $detalle->movcaja->conceptopago->descripcion;

                                                            if ($conceptoDescripcion === 'Apertura de caja') {
                                                                return "Apertura de caja|{$detalle->Mediopago->descripcion}";
                                                            }

                                                            if ($conceptoDescripcion === 'Pago de cliente') {
                                                                return "Pago de cliente|{$detalle->Mediopago->descripcion}";
                                                            }

                                                            return ($tipoConcepto === 'Ingreso'
                                                                ? 'Otros ingresos'
                                                                : 'Egresos') . "|{$detalle->Mediopago->descripcion}";
                                                        })
                                                        ->map(function ($items, $key) {
                                                            [$nota, $mediopago] = explode('|', $key);
                                                            return [
                                                                'nota' => $nota,
                                                                'mediopago' => $mediopago,
                                                                'monto' => $items->sum('monto'),
                                                            ];
                                                        })
                                                        ->groupBy('nota'); // Agrupar por nota final
                                                @endphp

                                                <div class="metodos-pago">
                                                    <ul style="list-style-type: none; padding-left: 0;">
                                                        @foreach ($detallesAgrupados as $nota => $detalles)
                                                            <li style="margin-bottom: 10px;">
                                                                {{-- Ícono basado en el tipo de nota --}}
                                                                @if ($nota === 'Apertura de caja')
                                                                    <i class="fa-solid fa-cash-register"
                                                                        style="margin-right: 5px; color: #FFA000;"></i>
                                                                @elseif ($nota === 'Pago de cliente')
                                                                    <i class="fa-solid fa-shopping-cart"
                                                                        style="margin-right: 5px; color: #4CAF50;"></i>
                                                                @elseif ($nota === 'Otros ingresos')
                                                                    <i class="fa-solid fa-arrow-up"
                                                                        style="margin-right: 5px; color: #00ACC1;"></i>
                                                                @elseif ($nota === 'Egresos')
                                                                    <i class="fa-solid fa-arrow-down"
                                                                        style="margin-right: 5px; color: #F44336;"></i>
                                                                @endif

                                                                {{-- Nota en negrita --}}
                                                                <span style="font-weight: bold;">{{ $nota }}:</span>
                                                                <ul style="list-style-type: none; padding-left: 15px;">
                                                                    @foreach ($detalles as $detalle)
                                                                        <li>
                                                                            {{-- Mostrar medio de pago y monto --}}
                                                                            {{ $detalle['mediopago'] }}:
                                                                            {{ $nota === 'Egresos' ? '-' : '' }}{{ number_format($detalle['monto'], 2) }}
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </td>
                                            <td>

                                                <span
                                                    class="new badge {{ $relTurnocaja->situacion == 'Activado' ? 'green' : 'red' }}"
                                                    data-badge-caption="{{ $relTurnocaja->situacion }}"></span>
                                            </td>
                                            <td>{{ $relTurnocaja->movcajafin->movimiento->numero ?? '' }}</td>

                                            <td>
                                                @foreach ($operaciones as $operacion)
                                                    @if ($operacion->tipo == 'Registro')
                                                        @if ($operacion->accion === 'rel_turnocaja.destroy')
                                                            <form
                                                                action="{{ route($operacion->accion, $relTurnocaja->id) }}"
                                                                class="delete-form" method="POST" style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                    data-tooltip="{{ $operacion->descripcion }}">
                                                                    {!! $operacion->imagen !!}
                                                                </button>
                                                            </form>
                                                        @else
                                                            <a href="{{ route($operacion->accion, $relTurnocaja->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light"
                                                                data-tooltip="{{ $operacion->descripcion }}"
                                                                target="_blank">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                             </div>
                            <div class="row">
                                <div class="col s12 m6">
                                    <p>Mostrando {{ $relTurnocajas->firstItem() }} a {{ $relTurnocajas->lastItem() }} de
                                        {{ $relTurnocajas->total() }} registros</p>
                                </div>

                                <div class="col s12 m6 right-align">
                                    {{ $relTurnocajas->links() }}
                                </div>
                            </div>
                       
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
