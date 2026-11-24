@extends('layouts.app')

@section('content')
        <style>
            .operaciones-crear a,
            div.operaciones-crear button {
                margin-left: 15px !important;
                margin-bottom: 10px !important;
            }
        </style>
        <div class="main-body">
            <div class="row" style="margin-top: 0px;">
                <div class="col s12">
                    <div class="card main-content">
                        <div class="card-content"  style="margin-top: -20px">
                            <div class="row without-bottom filtro" id="filtros" style="display: none">
                                <div class="col s12 m6 l2">
                                    <div class="input-field">
                                        <select id="caja_id" name="caja_id">
                                            @foreach ($cajas as $caja)
    <option value="{{ $caja->id }}"
    {{ request('caja_id', $caja_id) == $caja->id ? 'selected' : '' }}>{{ $caja->numero }}
    </option>
                                            @endforeach
                                        </select>
                                        <label for="caja_id">Caja</label>
                                    </div>
                                </div>
                                <div class="col s12 m6 l6">
                                    <div class="input-field">
                                        <div class="select-wrapper">
                                            <select id="rel_turnocaja_id" name="rel_turnocaja_id" onchange="filterCaja()"
                                                tabindex="-1">
                                                <option value="0">Todos</option>
                                                @foreach ($relTurnocajas as $relTurno)
    <option value="{{ $relTurno->id }}"
    @if (empty($relTurno->fechafin)) selected @endif>
    {{ $relTurno->movcajainicio->caja }} -
    {{ $relTurno->movcajainicio->movimiento->movcaja->turno->nombre ?? '' }}
    |
    {{ $relTurno->fechainicio }}
    {{ empty($relTurno->fechafin) ? '(En curso)' : '- ' . $relTurno->fechafin }}
    </option>
                                                @endforeach
                                            </select>

                                        </div>
                                        <label for="rel_turnocaja_id">Turno</label>
                                    </div>
                                </div>
                                <div class="col s12 m6 l2">
                                    <div class="input-field">
                                        <div class="select-wrapper">
                                            <select id="tipo-filter" name="tipo" onchange="filterCaja()">
                                                <option value="0">Todos</option>
                                                @foreach ($tipos as $tipo)
    <option value="{{ $tipo }}"
    {{ request('tipo') == $tipo ? 'selected' : '' }}>
    {{ $tipo }}
    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <label for="txtIdTipo">Tipo</label>
                                    </div>
                                </div>
                                <div class="col s12 m2 l2">
                                    <div class="input-field">
                                        <input class="submit-search" id="txtBuscar" name="txtBuscar" type="text">
                                        <label for="txtBuscar">Buscar</label>
                                    </div>
                                </div>
                                <div class="col s12 m3 l4">
                                    <div class="input-field">
                                        <div class="select-wrapper">
                                            <select id="conceptospago-filter" name="conceptospago" onchange="filterCaja()">
                                                <option value="0">Todos</option>
                                                @foreach ($conceptospagos as $conceptospago)
    <option value="{{ $conceptospago->id }}"
    {{ request('conceptospago') == $conceptospago->id ? 'selected' : '' }}>
    {{ $conceptospago->tipo }} - {{ $conceptospago->descripcion }}
    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <label for="txtIdconceptospagoo">Concepto de pago</label>
                                    </div>
                                </div>
                                <div class="col s12 m6 l2">
                                    <div class="input-field">
                                        <input id="txtFechaInicio" name="txtFechaInicio" type="date" value=" ">
                                        <label for="txtFechaInicio">Fecha de inicio</label>
                                    </div>
                                </div>
                                <div class="col s12 m6 l2">
                                    <div class="input-field">
                                        <input id="txtFechaFin" name="txtFechaFin" type="date" value=" ">
                                        <label for="txtFechaFin">Fecha de fin</label>
                                    </div>
                                </div>
                                <div class="col s12 m12 l4 search-with-report rigth-align ">
                                
                                        <button class="btn teal tooltipped btn-buscar" id="btn-MovVenta"
                                            data-tooltip="Buscar" type="button" onclick="filterCaja()"><i
                                                class="fa-solid fa-magnifying-glass white-text"></i></button>
                                        <button class="btn red tooltipped truncated btn-pdf" id="btn-pdf-MovVenta"
                                            data-tooltip="Reporte en PDF" type="button"
                                            onclick="generatePDFReportCaja()"><i
                                                class="fa-solid fa-file-pdf white-text right"></i>PDF</button>
                                        <button class="btn green tooltipped truncated btn-excel" id="btn-excel-MovVenta"
                                            data-tooltip="Reporte en Excel" type="button"
                                            onclick="generateExcelReportCaja()"><i
                                                class="fa-solid fa-file white-text right"></i>Excel</button>
                                               
                                </div>
                            </div>

                            <div class="row" style="margin-top: -20px; margin-bottom: 0;">
                                <div class="row valign-wrapper" style="margin-bottom: -25px;">
                                    <div class="col s12 m12 left-align valign-wrapper">
                                        Registros por página:
                                        <div class="input-field" style="margin: 0;">
                                            <select id="records-per-page" class="browser-default" onchange="filterCaja()">
                                                <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10
                                                </option>
                                                <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25
                                                </option>
                                                <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50
                                                </option>
                                                <option value="100" {{ request('perPage', 10) == 100 ? 'selected' : '' }}>100</option>
                                            </select>
                                        </div>
                                    </div>


                                    <div class="col s2 m2 l4 right right-align"
                                        style="display: flex; align-items: center; justify-content: flex-end;">
                                        <button class="btn-flat btn-grid-options tooltipped" id="btn-expand-compress-MovCaja" type="button"
                                            onclick="expandOrCompressChildRows();" data-position="top" data-tooltip="Expandir filas">
                                            <i class="fa-solid fa-expand" style="color:#343a40;"></i>
                                        </button>

                                        <button class="btn-flat btn-grid-options tooltipped" id="btn-filtro" type="button"
                                            onclick="mostrarFiltro();" data-position="top" data-tooltip="Filtros">
                                            <i class="fa-solid fa-magnifying-glass-plus" style="color:#343a40;"></i>
                                        </button>
                                    </div>

                                </div>
                            </div>




                        </div>


            <div id="tb-container" class="table-wrapper">
                <!-- Contenedor con scroll horizontal solo para la tabla -->
                <div  class="table-scroll" style="overflow-x: auto;">
                    <table id="data-table" class="highlight striped centered" style="width: max-content; min-width: 100%;">
                        <thead class="thead-custom">
                            <tr>
                                        <th class="no-hide">#</th>
                                <th class="no-hide">Número</th>
                                <th class="no-hide">Tipo</th>
                                <th class="no-hide">Concepto</th>
                                <th>Moneda</th>
                                <th class="no-hide">Total</th>
                                <th class="no-hide">Fecha</th>
                                <th>Usuario</th>
                                <th>Caja</th>
                                <th>Turno</th>
                                <th>Persona</th>
                                <th>Responsable</th>
                                <th class="no-hide">Situación</th>
                                <th class="no-hide">Métodos de pago</th>
                                <th>Origen</th>
                                <th>T. cambio</th>
                                <th>Comentario</th>
                                <th class="no-hide">Operaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($movcajas as $index => $movcaja)
                                            @php
            $correlativo =
                $movcajas->total() -
                ($movcajas->currentPage() - 1) * $movcajas->perPage() -
                $index;
                                            @endphp
                                            <tr data-caja-id="{{ $movcaja->caja_id }}">
                                                <td>{{ $correlativo }}</td>
                                                <td>{{ $movcaja->movimiento->numero ?? 'N/A' }}</td>
                                                <td>
                                                    <span
                                                        class="new badge {{ $movcaja->movimiento->tipodocumento->nombre == 'Ingreso' || $movcaja->movimiento->tipodocumento->stock == 'Suma' || $movcaja->conceptopago_id == 4 ? 'teal' : 'red' }}"
                                                        data-badge-caption="{{ $movcaja->movimiento->tipodocumento->nombre == 'Ingreso' || $movcaja->movimiento->tipodocumento->stock == 'Suma' || $movcaja->conceptopago_id == 4 ? 'Ingreso' : $movcaja->movimiento->tipodocumento->nombre ?? 'N/A' }}">
                                                    </span>



                                                </td>
                                                <td>
                                                    <span
                                                        class="new badge {{ in_array($movcaja->conceptopago->descripcion, ['Apertura de caja', 'Cierre de caja']) ? 'yellow black-text' : 'orange' }} "
                                                        data-badge-caption="{{ $movcaja->conceptopago->descripcion ?? 'N/A' }}">
                                                    </span>
                                                </td>

                                                <td>{{ $movcaja->moneda ?? 'N/A' }}</td>
                                                <td><span
                                                        style="font-size: 1.5rem; font-weight: bold;">{{ number_format($movcaja->total, 2) }}</span>
                                                </td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($movcaja->created_at)->format('Y-m-d') }}<br>
                                                    {{ \Carbon\Carbon::parse($movcaja->created_at)->format('h:i:s A') }}
                                                </td>



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

                                                <td>
                                                    @if ($movcaja->conceptopago->descripcion === 'Cierre de caja')
                                                        @php
                // Obtener el último turno activo o cerrado para la caja actual
                $ultimoTurno = \App\Models\RelTurnocaja::where('situacion', 'A') // Turno activo
                    ->orWhere('situacion', 'C') // Turno cerrado
                    ->whereHas('movcajainicio', function ($query) use ($movcaja) {
                        $query->where('caja_id', $movcaja->caja_id);
                    })
                    ->latest('fechainicio') // Último turno
                    ->first();

                // Obtener movimientos solo del último turno
                $movimientosCierre = \App\Models\Movcaja::with([
                    'detMovcaja.Mediopago',
                    'conceptopago',
                ])
                    ->where('caja_id', $movcaja->caja_id)
                    ->when($ultimoTurno, function ($query) use ($ultimoTurno) {
                        $query->where('id', '>=', $ultimoTurno->movcajainicio_id); // Desde el inicio del turno
                    })
                    ->whereHas('conceptopago', function ($query) {
                        $query->where('descripcion', '!=', 'Cierre de caja'); // Excluir Cierre de caja
                    })
                    ->orderBy('created_at', 'asc')
                    ->get();

                // Replicar la lógica de agrupación para el desglose de detalles
                $detallesAgrupadosCierre = $movimientosCierre->flatMap->detMovcaja
                    ->groupBy(function ($detalle) {
                        $tipoConcepto = $detalle->movcaja->conceptopago->tipo;
                        $conceptoDescripcion =
                            $detalle->movcaja->conceptopago->descripcion;

                        if ($conceptoDescripcion === 'Apertura de caja') {
                            return "{$detalle->Mediopago->descripcion}|Apertura de caja|{$detalle->tipo}";
                        }

                        if ($conceptoDescripcion === 'Pago de cliente') {
                            return "{$detalle->Mediopago->descripcion}|Pago de cliente|{$detalle->tipo}";
                        }

                        return "{$detalle->Mediopago->descripcion}|" .
                            ($tipoConcepto === 'Ingreso'
                                ? 'Otros ingresos'
                                : 'Egresos') .
                            "|{$detalle->tipo}";
                    })
                    ->map(function ($items, $key) {
                        return [
                            'clave_agrupacion' => $key,
                            'mediopago' => $items->first()->Mediopago->descripcion,
                            'nota' =>
                                $items->first()->movcaja->conceptopago
                                    ->descripcion === 'Apertura de caja'
                                ? 'Apertura de caja'
                                : ($items->first()->movcaja->conceptopago
                                    ->tipo === 'Ingreso'
                                    ? ($items->first()->movcaja->conceptopago
                                        ->descripcion === 'Pago de cliente'
                                        ? 'Pago de cliente'
                                        : 'Otros ingresos')
                                    : 'Egresos'),
                            'tipo' => $items->first()->tipo,
                            'monto' => $items->sum('monto'),
                        ];
                    });
                                                        @endphp


                                                        <div class="metodos-pago">
                                                            <ul style="list-style-type: none; padding-left: 0;">
                                                                @foreach ($detallesAgrupadosCierre as $detalle)
                                                                    <li style="margin-bottom: 10px;">
                                                                        {{-- Ícono basado en el tipo de nota --}}
                                                                        @if ($detalle['nota'] === 'Apertura de caja')
                                                                            <i class="fa-solid fa-cash-register"
                                                                                style="margin-right: 5px; color: #FFA000;"></i>
                                                                        @elseif ($detalle['nota'] === 'Pago de cliente')
                                                                            <i class="fa-solid fa-shopping-cart"
                                                                                style="margin-right: 5px; color: #4CAF50;"></i>
                                                                        @elseif ($detalle['nota'] === 'Otros ingresos')
                                                                            <i class="fa-solid fa-arrow-up"
                                                                                style="margin-right: 5px; color: #00ACC1;"></i>
                                                                        @elseif ($detalle['nota'] === 'Egresos')
                                                                            <i class="fa-solid fa-arrow-down"
                                                                                style="margin-right: 5px; color: #F44336;"></i>
                                                                        @endif

                                                                        {{-- Nota en negrita y monto en la siguiente línea --}}
                                                                        <span
                                                                            style="font-weight: bold;">{{ $detalle['nota'] }}:</span>
                                                                        <br>
                                                                        {{ $detalle['mediopago'] }}:
                                                                        {{ $detalle['nota'] === 'Egresos' ? '-' : '' }}{{ number_format($detalle['monto'], 2) }}
                                                                    </li>
                                                                @endforeach
                                                            </ul>

                                                        </div>
                                                    @else
                                                        {{-- Mostrar otros conceptos normalmente --}}
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
                                                    @endif

                                                </td>
                                                @if ($movcaja->movimiento->tipodocumento->tipomovimiento->descripcion == 'Venta')
                                                    <td> {{ $movcaja->movimiento->tipodocumento->tipomovimiento->descripcion ?? 'N/A' }}
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
                                                    @foreach ($operaciones as $operacion)
                                                        @if ($operacion->tipo == 'Registro')


                                                            @if (
                        $operacion->accion === 'movcaja.edit' &&
                        $movcaja->movimiento->situacion == 'Activado' &&
                        $movcaja->movimiento->tipodocumento->tipomovimiento->descripcion == 'Caja'
                    )

                                                            @elseif (
                        $operacion->accion === 'movcaja.verVenta' &&
                        $movcaja->movimiento->situacion == 'Activado' &&
                        $movcaja->movventa
                    )
                                                                <a href="{{ route('movventa.edit', $movcaja->movventa->id) }}"
                                                                    class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                                                                    data-tooltip="{{ $operacion->descripcion }}"
                                                                    data-icono="fa-solid fa-EDIT left"
                                                                    data-titulo="Movimientos de Caja | Venta">
                                                                    {!! $operacion->imagen !!}
                                                                </a>
                                                            @endif
                                                            @if (
                        $operacion->accion == 'movventa.alojamiento' &&
                        optional($movcaja->movventa)->movhabitacion &&
                        $movcaja->movimiento->situacion == 'Activado'
                    )
                                                                <a href="{{ route($operacion->accion, $movcaja->movventa->id) }}"
                                                                    class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-cargar-crud "
                                                                    data-icono="{{ $operacion->imagen }}"
                                                                    data-titulo="{{ $operacion->descripcion }}"
                                                                    data-tooltip="{{ $operacion->comentario }}">
                                                                    {!! $operacion->imagen !!}
                                                                </a>
                                                            @endif

                                                            @if ($operacion->accion === 'movcaja.verDetalles')
                                                                <a href="{{ route($operacion->accion, $movcaja->id) }}"
                                                                    class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                                                                    data-icono="fa-solid fa-edit left"
                                                                    data-tooltip="{{ $operacion->descripcion }}"
                                                                    data-titulo="Movimientos de Caja | Detalles">

                                                                    {!! $operacion->imagen !!}
                                                                </a>
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                </td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4"></td> <!-- Dejar las columnas vacías anteriores -->
                                            <td class="center" style="font-weight: bold; ">PEN</td> <!-- Moneda -->
                                            <td class="center-align" style="font-size: 1.5rem; font-weight: bold;">
                                                {{ number_format($totalPagina, 2) }}</td> <!-- Total -->

                                            <td colspan="12"></td> <!-- Colspan para cubrir columnas restantes -->
                                        </tr>
                                    </tfoot>
                                </table>
        </div>


                          <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $movcajas->firstItem() }} a {{ $movcajas->lastItem() }} de
                                    {{ $movcajas->total() }} registros</p>
                            </div>

                            <!-- Paginación -->
                            <div class="col s12 m6 right-align">
                                {{ $movcajas->appends(request()->query())->links() }}
                            </div>

                        </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
@endsection
