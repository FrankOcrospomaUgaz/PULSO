@extends('layouts.app')

@section('content')
    <style>
        #resumen-dia .card.minicard {
            border-radius: 16px;
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08);
        }

        #resumen-dia .card-content {
            padding-top: 12px !important;
            padding-bottom: 12px !important;
        }

        #resumen-dia .card-supertitle {
            font-size: 3rem !important;
            font-weight: 700 !important;
        }

        #resumen-dia .card-medium-title {
            font-size: 2rem !important;
            font-weight: 800 !important;
        }

        #resumen-dia .mini-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .rounded-card {
            border-radius: 16px;
            overflow: hidden;
        }

        .rounded-card .card-action {
            border-top: none;
        }

        .btn-revisar-primary strong,
        .btn-revisar-primary i {
            color: #343a40 !important;
        }

        .btn-revisar-secondary strong,
        .btn-revisar-secondary i {
            color: #1e3d8a !important;
        }

        /* OPERACIONES */
        .opciones {
            display: flex;
            flex-direction: column;
        }

        .operacion-item {
            margin-bottom: 10px;
            /* separación REAL entre botones */
        }

        .operacion-item:last-child {
            margin-bottom: 0;
            /* opcional: evita espacio al final */
        }
        .badge-cantidad {
    min-width: unset !important;   /* quita el ancho mínimo */
    padding: 0 8px !important;     /* ancho justo al número */
    font-size: 1.1rem !important;  /* número más grande */
    font-weight: 700;
    line-height: 24px;
    height: 24px;
    border-radius: 12px;
    text-align: center;
}

        #movcaja-resumen-row.resumen-oculto #resumen-dia {
            display: none;
        }

        #movcaja-resumen-row.resumen-oculto #movcaja-operaciones {
            width: 100% !important;
            margin-bottom: 10px !important;
        }

        #movcaja-resumen-row.resumen-oculto #movcaja-operaciones .opciones {
            flex-direction: row;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: flex-start;
            padding-left: 0;
        }

        #movcaja-resumen-row.resumen-oculto #movcaja-operaciones .operacion-item {
            margin-bottom: 0;
            flex: 0 0 auto;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        #movcaja-resumen-row.resumen-oculto #movcaja-operaciones .operacion-item.col {
            padding-left: 0 !important;
            padding-right: 0 !important;
            margin-left: 0 !important;
        }

        #movcaja-resumen-row.resumen-oculto #movcaja-operaciones .opciones.row {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        #movcaja-resumen-row.resumen-oculto #movcaja-operaciones .operacion-item .btn {
            width: auto;
            min-width: unset;
            padding: 0 18px;
            height: 36px;
            line-height: 36px;
            border-radius: 18px;
            font-size: 0.85rem;
        }

        #movcaja-resumen-row.resumen-oculto #movcaja-operaciones .operacion-item {
            width: auto !important;
        }

        @media (max-width: 600px) {
            #movcaja-operaciones {
                margin-top: 12px;
            }

            #movcaja-operaciones .opciones {
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                align-items: center;
                gap: 0;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            #movcaja-resumen-row.resumen-oculto #movcaja-operaciones .opciones {
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                align-items: center;
                gap: 0;
                margin-left: 0 !important;
                margin-right: 0 !important;
                padding-left: 0 !important;
            }

            #movcaja-operaciones .operacion-item,
            #movcaja-operaciones .operacion-item.col {
                width: auto !important;
                flex: 0 0 33.333333%;
                max-width: 33.333333%;
                margin: 0 0 10px 0 !important;
                padding: 0 !important;
                display: flex;
            }

            #movcaja-resumen-row.resumen-oculto #movcaja-operaciones .operacion-item,
            #movcaja-resumen-row.resumen-oculto #movcaja-operaciones .operacion-item.col {
                width: auto !important;
                flex: 0 0 33.333333%;
                max-width: 33.333333%;
                margin: 0 0 10px 0 !important;
                padding: 0 !important;
                display: flex;
            }

            #movcaja-operaciones .operacion-item .btn {
                width: calc(100% - 8px) !important;
                min-width: 0;
                max-width: 100%;
                height: 34px;
                line-height: 34px;
                padding: 0 14px !important;
                border-radius: 17px !important;
                font-size: 0.78rem;
                letter-spacing: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            #movcaja-resumen-row.resumen-oculto #movcaja-operaciones .operacion-item .btn {
                width: calc(100% - 8px) !important;
                min-width: 0 !important;
                max-width: 100%;
                height: 34px;
                line-height: 34px;
                padding: 0 14px !important;
                border-radius: 17px !important;
                font-size: 0.78rem;
                letter-spacing: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            #movcaja-operaciones .operacion-item .btn i {
                line-height: 34px;
                margin-left: 8px;
                font-size: 1rem;
            }

            #movcaja-operaciones .operacion-nuevo {
                order: 1;
                justify-content: flex-start;
            }

            #movcaja-resumen-row.resumen-oculto #movcaja-operaciones .operacion-nuevo {
                order: 1;
                justify-content: flex-start;
            }

            #movcaja-operaciones .operacion-ingreso {
                order: 2;
                justify-content: center;
            }

            #movcaja-resumen-row.resumen-oculto #movcaja-operaciones .operacion-ingreso {
                order: 2;
                justify-content: center;
            }

            #movcaja-operaciones .operacion-egreso {
                order: 3;
                justify-content: flex-end;
            }

            #movcaja-resumen-row.resumen-oculto #movcaja-operaciones .operacion-egreso {
                order: 3;
                justify-content: flex-end;
            }

            #movcaja-operaciones .operacion-arqueo {
                order: 4;
                justify-content: flex-start;
            }

            #movcaja-resumen-row.resumen-oculto #movcaja-operaciones .operacion-arqueo {
                order: 4;
                justify-content: flex-start;
            }

            #movcaja-operaciones .operacion-cerrar,
            #movcaja-operaciones .operacion-cierre {
                order: 5;
                justify-content: flex-start;
            }

            #movcaja-resumen-row.resumen-oculto #movcaja-operaciones .operacion-cerrar,
            #movcaja-resumen-row.resumen-oculto #movcaja-operaciones .operacion-cierre {
                order: 5;
                justify-content: flex-start;
            }

            #movcaja-operaciones .operacion-resumen {
                order: 6;
                justify-content: center;
            }

            #movcaja-resumen-row.resumen-oculto #movcaja-operaciones .operacion-resumen {
                order: 6;
                justify-content: center;
            }
        }

        /* Texto blanco en stock bajo */
        #resumen-dia .stock-card .card-title,
        #resumen-dia .stock-card ul,
        #resumen-dia .stock-card ul li,
        #resumen-dia .stock-card ul li span {
            color: #fff !important;
        }
.row {

    margin-bottom: 2px !important;
}
    </style>

    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">


                    <div>
                        @isset($resumenCaja)
                            <div class="row without-tb {{ session('movcaja_resumen_oculto') ? 'resumen-oculto' : '' }}" id="movcaja-resumen-row">

                                {{-- ================== RESUMEN (l10) ================== --}}
                                <div class="col s12 m12 l10" id="resumen-dia">
                                    <div class="row without-tb flex">

                                        {{-- CARD IZQUIERDA --}}
                                        <div class="col s12 m12 l3">
                                            <div class="card minicard bg-secondary rounded-card">
                                                <div class="card-content white-text" style="height:348px; overflow-y:auto;">
                                                    <span class="card-supertitle">
                                                        <font>{{ $pendientesAlojamientoCount ?? 0 }}</font>
                                                        <i class="fa-solid fa-bed right"></i>
                                                    </span>

                                                    <p class="bold">Alojamientos pendientes de pago</p>

                                                    <ul class="without-bottom white-text">
                                                        @foreach ($pendientesPorPiso as $pisoPendiente)
                                                                <li class="mb5">
                                                                    {{ $pisoPendiente['piso'] }}
                                                                   <span class="new badge right orange lighten-1 badge-cantidad"
      data-badge-caption="{{ $pisoPendiente['cantidad'] }}">
</span>

                                                                </li>
                                                            @endforeach
                                                    </ul>
                                                 
                                                </div>

                                                <div class="card-action right-align">
                                                    <button href="{{ route('movhabitacion.index') }}" class="btn btn-cargar-crud white btn-revisar-secondary"
                                                            data-titulo="Habitaciones" data-icono="fa-solid fa-plus left">
                                                        <strong>Revisar</strong>
                                                        <i class="fa-solid fa-caret-right right"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- CARDS CENTRALES --}} <div class="col s12 m12 l5">
                                            <div class="row without-tb"> @php $cardsCentro = [ [ "titulo" => "Checkouts", "valor" => $checkoutsHoyCount ?? 0, "icon" => "circle-arrow-up", "sub" => "para hoy", "url" => route('movhabitacion.index'), "dataTitulo" => "Habitaciones", "dataIcono" => "fa-solid fa-bed left" ], [ "titulo" => "Reservas", "valor" => $reservasHoyCount ?? 0, "icon" => "calendar-day", "sub" => "para hoy", "url" => route('movreserva.index'), "dataTitulo" => "Reservas", "dataIcono" => "fa-solid fa-calendar-day left" ], [ "titulo" => "Cuentas por cobrar", "valor" => $cobrarCount ?? 0, "icon" => "hand-holding-dollar", "sub" => "PEN " . number_format($cobrarTotal ?? 0, 2), "url" => route('cuentas.cobrar.index'), "dataTitulo" => "Cuentas por cobrar", "dataIcono" => "fa-solid fa-hand-holding-dollar left" ], [ "titulo" => "Cuentas por pagar", "valor" => $deudasCount ?? 0, "icon" => "coins", "sub" => "PEN " . number_format($deudasTotal ?? 0, 2), "url" => route('cuentas.pagar.index'), "dataTitulo" => "Cuentas por pagar", "dataIcono" => "fa-solid fa-coins left" ], ]; @endphp @foreach ($cardsCentro as $card)
                                                    <div class="col s12 m6 l6">
                                                        <div class="card minicard bg-primary rounded-card">
                                                            <div class="card-content white-text" style="padding:16px!important">
                                                                <span class="card-medium-title">
                                                                    <font>{{ in_array($card['icon'], ['calendar-day','circle-arrow-up','coins','hand-holding-dollar']) ? number_format($card['valor'], 0) : number_format($card['valor'], 2) }}</font> <i
                                                                        class="fa-solid fa-{{ $card['icon'] }} right"></i>
                                                                </span>
                                                                <p class="bold truncate">{{ $card['titulo'] }}</p>
                                                                <p>{{ $card['sub'] ?? 'para hoy' }}</p>
                                                            </div>
                                                            <div class="card-action right-align"> <button
                                                                     href="{{ $card['url'] }}" class="btn btn-cargar-crud white waves-effect waves-dark btn-revisar-primary"
                                                                     data-titulo="{{ $card['dataTitulo'] }}" data-icono="{{ $card['dataIcono'] }}"
                                                                   >
                                                                    <strong>Revisar</strong> <i
                                                                        class="fa-solid fa-caret-right right"></i> </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        {{-- CARD DERECHA --}}
                                        <div class="col s12 m12 l4">
                                            <div class="card minicard bg-secondary rounded-card stock-card">
                                                <div class="card-content white-text" style="height:348px; overflow-y:auto;">
                                                    <span class="card-title white-text">
                                                        Stock bajo
                                                        <i class="fa-solid fa-triangle-exclamation right white-text"></i>
                                                    </span>

                                                    <ul class="without-bottom white-text">
                                                        @forelse ($stockBajo as $item)
                                                            <li class="mb5">
                                                                {{ $item->descripcion }}
                                                                <span class="new badge right bg-primary"
                                                                    data-badge-caption="{{ number_format($item->stockbase ?? 0, 0) }}"></span>
                                                            </li>
                                                        @empty
                                                            <li>Sin stock bajo</li>
                                                        @endforelse
                                                    </ul>
                                                </div>

                                                <div class="card-action right-align">
                                                     <button href="{{ route('stockproducto.index') }}" class="btn btn-cargar-crud white btn-revisar-secondary"
                                                        data-titulo="Stock de productos" data-icono="fa-solid fa-box left">
                                                        <strong>Revisar</strong>
                                                        <i class="fa-solid fa-caret-right right"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                {{-- ================== OPERACIONES (l2) ================== --}}
                                <div class="col s12 l2" id="movcaja-operaciones">
                                    <div class="opciones row without-tb">

                                        

                                        @isset($caja_id)
                                            @foreach ($operaciones as $operacion)
                                                @if ($operacion->tipo == 'Tabla')
                                                    @php
                                                        $operacionSlug = \Illuminate\Support\Str::slug($operacion->descripcion);
                                                    @endphp
                                                    <div class="col s12 operacion-item operacion-{{ $operacionSlug }}">
                                                        <a href="{{ route($operacion->accion, [$caja_id, $operacion->descripcion]) }}" class="btn {{ $operacion->color }} btn-cargar-crud tooltipped truncate"
                                                            data-titulo="Caja chica | {{ $operacion->comentario }}" data-icono="fa-solid fa-plus left"  data-tooltip="{{ $operacion->comentario }}"
                                                           style="width:100%; border-radius:14px">
                                                            {!! $operacion->imagen !!}
                                                            {{ strtoupper($operacion->descripcion) }}
                                                        </a>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endisset

                                        {{-- BOTÓN RESUMEN --}}
                                        <div class="col s12 operacion-item operacion-resumen">
                                            <a id="btn-toggle-resumen" onclick="toggleResumenMovcaja()" class="btn tooltipped white-text truncate"
                                              data-tooltip="Resumen del día"  data-resumen-url="{{ route('movcaja.resumen.estado') }}"
                                                style="width:100%;  background-color: #343a40;">
                                                <i class="fa-solid fa-bars-progress right"></i>
                                                RESUMEN
                                        </a>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        @endisset


                        <div class="row without-bottom filtro" id="filtros" style="display: none">
                            <div class="col s12 m6 l2">
                                <div class="input-field">
                                    <select id="caja_id" name="caja_id">
                                        @foreach ($cajas as $caja)
                                            <option value="{{ $caja->id }}"
                                                {{ old('caja_id') == $caja->id ? 'selected' : '' }}>{{ $caja->numero }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="caja_id">Caja</label>
                                </div>
                            </div>

                            <div class="col s12 m6 l2">
                                <div class="input-field">
                                    <div class="select-wrapper">
                                        <select id="tipo-filter" name="tipo" onchange="filterCajaAperturada()">
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
                                        <select id="conceptospago-filter" name="conceptospago"
                                            onchange="filterCajaAperturada()">
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
                            <div class="col s12 m6 l2">
                                <div class="input-field">
                                    <button class="btn teal tooltipped btn-buscar" id="btn-MovVenta"
                                        data-tooltip="Buscar" type="button" onclick="filterCajaAperturada()"><i
                                            class="fa-solid fa-magnifying-glass white-text"></i></button>

                                </div>
                            </div>
                        </div>
                        <div class="row without-tb">
                            <div class="col s10 m10 l8 center">
                                <div class="valign-wrapper">
                                    Registros por página:
                                    <select id="records-per-page" class="browser-default"
                                        onchange="filterCajaAperturada()">
                                        <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10
                                        </option>
                                        <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25
                                        </option>
                                        <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50
                                        </option>
                                        <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col s2 m2 l4 right right-align">
                                <input id="expand-compress-rows-MovCaja" type="hidden" value="N">
                                <input id="mostrar-filtro-MovCaja" type="hidden" value="N">
                                <button class="btn-flat btn-grid-options tooltipped" id="btn-expand-compress-MovCaja"
                                    type="button" onclick="expandOrCompressChildRows();" data-position="top"
                                    data-tooltip="Expandir filas">
                                    <i class="fa-solid fa-expand" style="color:#343a40;"></i>
                                </button>

                                <button class="btn-flat btn-grid-options tooltipped" id="btn-filtro" type="button"
                                    onclick="mostrarFiltro();" data-position="top" data-tooltip="Filtros">
                                    <i class="fa-solid fa-magnifying-glass-plus" style="color:#343a40;"></i>
                                </button>
                            </div>
                        </div>


                    </div>

                    <div id="tb-container" class="table-wrapper">
                        <!-- Contenedor con scroll horizontal solo para la tabla -->
                        <div class="table-scroll" style="overflow-x: auto;">
                            <table id="data-table" class="highlight striped centered"
                                style="width: max-content; min-width: 100%;">
                                <thead class="thead-custom">
                                    <tr>
                                        <th class="no-hide">#</th>
                                        <th class="no-hide">Número</th>
                                        <th class="no-hide">Tipo</th>
                                        <th class="no-hide">Concepto</th>

                                        <th class="no-hide">Total</th>
                                        <th class="no-hide">Fecha</th>
                                        <th>Usuario</th>
                                        <th>Caja</th>
                                        <th>Turno</th>
                                        <th>Persona</th>
                                        <th>Responsable</th>
                                        <th class="no-hide">Situación</th>
                                        <th class="no-hide">Métodos de pago</th>
                                        <th>Moneda</th>
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
                                                    class="new badge {{ $movcaja->movimiento->tipodocumento->nombre == 'Ingreso' ? 'teal' : 'red' }}"
                                                    data-badge-caption="{{ $movcaja->movimiento->tipodocumento->nombre ?? 'N/A' }}">
                                                </span>



                                            </td>
                                            <td>
                                                <span
                                                    class="new badge {{ in_array($movcaja->conceptopago->descripcion, ['Apertura de caja', 'Cierre de caja']) ? 'yellow black-text' : 'orange' }} "
                                                    data-badge-caption="{{ $movcaja->conceptopago->descripcion ?? 'N/A' }}">
                                                </span>
                                            </td>


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
                                                         
                                                            ->whereHas('movcajainicio', function ($query) use (
                                                                $movcaja,
                                                            ) {
                                                                $query->where('caja_id', $movcaja->caja_id);
                                                            })->where('sucursal_id', $movcaja->sucursal_id)
                                                            ->latest('fechainicio') // último turno
                                                            ->first();
                                                            
                                                        // Obtener movimientos solo del último turno
                                                        $movimientosCierre = \App\Models\Movcaja::with([
                                                            'detMovcaja.Mediopago',
                                                            'conceptopago',
                                                        ])
                                                            ->where('caja_id', $movcaja->caja_id)
                                                            ->when($ultimoTurno, function ($query) use ($ultimoTurno) {
                                                                $query->where(
                                                                    'id',
                                                                    '>=',
                                                                    $ultimoTurno->movcajainicio_id,
                                                                ); // Desde el inicio del turno
                                                            })
                                                            ->whereHas('conceptopago', function ($query) {
                                                                $query->where('descripcion', '!=', 'Cierre de caja'); // Excluir Cierre de caja
                                                            })
                                                            ->orderBy('created_at', 'asc')
                                                            ->get();
                                                          //  dd($ultimoTurno);
                                                        // Replicar la lógica de agrupación para el desglose de detalles
                                                                $detallesAgrupadosCierre = $movimientosCierre
                                                                    ->flatMap->detMovcaja
                                                                    ->groupBy(function ($detalle) {
                                                                        $concepto = $detalle->movcaja->conceptopago;

                                                                        if ($concepto->descripcion === 'Apertura de caja') {
                                                                            return 'Apertura de caja';
                                                                        }

                                                                        if ($concepto->descripcion === 'Pago de cliente') {
                                                                            return 'Pago de cliente';
                                                                        }

                                                                        return $concepto->tipo === 'Ingreso'
                                                                            ? 'Otros ingresos'
                                                                            : 'Egresos';
                                                                    })
                                                                    ->map(function ($items, $nota) {

                                                                        $itemsAgrupados = $items->groupBy(function ($item) {
                                                                            // 🔥 SI ES BILLETERA DIGITAL → agrupar por billetera
                                                                        
                                                                            $medioDescripcion = data_get($item, 'Mediopago.descripcion') ?? $item->mediopago ?? '';
                                                                            if (
                                                                                strtolower((string) $medioDescripcion) === 'billetera digital'
                                                                                && $item->billeteradigital_id
                                                                            ) {
                                                                                return 'billetera_' . $item->billeteradigital_id;
                                                                            }

                                                                            // 🔹 Caso normal
                                                                            return 'medio_' . $item->mediopago_id;
                                                                        });

                                                                        return [
                                                                            'nota' => $nota,
                                                                            'items' => $itemsAgrupados->map(function ($group) {

                                                                                $first = $group->first();

                                                                                // Nombre del medio
                                                                                $medioDescripcion = data_get($first, 'Mediopago.descripcion') ?? $first->mediopago ?? '';
                                                                                $billeteraDescripcion = data_get($first, 'billeteradigital.descripcion');
                                                                                if ($billeteraDescripcion === null && is_string($first->billeteradigital)) {
                                                                                    $billeteraDescripcion = $first->billeteradigital;
                                                                                }

                                                                                if (strtolower((string) $medioDescripcion) === 'billetera digital') {
                                                                                    $label = $billeteraDescripcion
                                                                                        ? 'Billetera digital (' . $billeteraDescripcion . ')'
                                                                                        : 'Billetera digital';
                                                                                } else {
                                                                                    $label = $medioDescripcion !== '' ? $medioDescripcion : 'N/A';
                                                                                }


                                                                                return [
                                                                                    'mediopago' => $label,
                                                                                    'monto' => $group->sum('monto'),
                                                                                ];
                                                                            })->values(),
                                                                        ];
                                                                    });
                                                                            @endphp


                                                    <div class="metodos-pago">
    <ul style="list-style-type:none; padding-left:0;">
        @foreach ($detallesAgrupadosCierre as $grupo)
            <li style="margin-bottom:12px;">
                
                {{-- ÍCONO + TÍTULO --}}
                @if ($grupo['nota'] === 'Apertura de caja')
                    <i class="fa-solid fa-cash-register" style="color:#FFA000;"></i>
                @elseif ($grupo['nota'] === 'Pago de cliente')
                    <i class="fa-solid fa-shopping-cart" style="color:#4CAF50;"></i>
                @elseif ($grupo['nota'] === 'Otros ingresos')
                    <i class="fa-solid fa-arrow-up" style="color:#00ACC1;"></i>
                @elseif ($grupo['nota'] === 'Egresos')
                    <i class="fa-solid fa-arrow-down" style="color:#F44336;"></i>
                @endif

                <strong style="font-weight: bold;">{{ $grupo['nota'] }}:</strong>

                {{-- MEDIOS --}}
                <ul style="margin-top:4px; padding-left:18px;">
                    @foreach ($grupo['items'] as $item)
                        <li>
                            {{ $item['mediopago'] }}:
                            {{ $grupo['nota'] === 'Egresos' ? '-' : '' }}
                            {{ number_format($item['monto'], 2) }}
                        </li>
                    @endforeach
                </ul>
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
                                                @foreach ($operaciones as $operacion)
                                                    @if ($operacion->tipo == 'Registro')
                                                        @if (
                                                            ($operacion->accion === 'movcaja.anular' || $operacion->accion === 'movcaja.destroy') &&
                                                                $movcaja->conceptopago->descripcion !== 'Apertura de caja' &&
                                                                $movcaja->conceptopago->descripcion !== 'Cierre de caja' &&
                                                                $movcaja->movimiento->situacion == 'Activado' &&
                                                                $movcaja->movventa == '')
                                                            @if ($cajaSeleccionada->situacion !== 'C')
                                                                <form
                                                                    action="{{ route($operacion->accion, $movcaja->id) }}"
                                                                    method="POST" class="anular-form"
                                                                    style="display: inline;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                        class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                        data-tooltip="{{ $operacion->descripcion }}">
                                                                        {!! $operacion->imagen !!}
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        @endif

                                                        @if ($operacion->accion === 'movcaja.edit' && $movcaja->movimiento->situacion == 'Activado' && $movcaja->movventa == '')
                                                            @if ($cajaSeleccionada->situacion !== 'C' || $movcaja->conceptopago->descripcion === 'Cierre de caja')
                                                                @if ($movcaja->conceptopago->descripcion == 'Apertura de caja')
                                                                    <a href="{{ route($operacion->accion, [$movcaja->id, 'APERTURAR']) }}"
                                                                        class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                                                                        data-tooltip="{{ $operacion->descripcion }}"
                                                                        data-icono="fa-solid fa-EDIT left"
                                                                        data-titulo="Movimientos de Caja | Editar">
                                                                        {!! $operacion->imagen !!}
                                                                    </a>
                                                                @else
                                                                    <a href="{{ route($operacion->accion, [$movcaja->id, strtoupper($movcaja->movimiento->tipodocumento->nombre)]) }}"
                                                                        class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                                                                        data-tooltip="{{ $operacion->descripcion }}"
                                                                        data-icono="fa-solid fa-EDIT left"
                                                                        data-titulo="Movimientos de Caja | Editar">
                                                                        {!! $operacion->imagen !!}
                                                                    </a>
                                                                @endif
                                                            @endif
                                                        @elseif ($operacion->accion === 'movcaja.verVenta' && $movcaja->movimiento->situacion == 'Activado' && $movcaja->movventa)
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
                                                                $movcaja->movimiento->situacion == 'Activado')
                                                            <a href="{{ route($operacion->accion, $movcaja->movventa->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-cargar-crud tooltipped"
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
                                        <td colspan="3"></td> <!-- Dejar las columnas vacías anteriores -->
                                        <td class="center" style="font-weight: bold; ">PEN</td> <!-- Moneda -->
                                        <td class="center-align" style="font-size: 1.5rem; font-weight: bold;">
                                            {{ number_format($totalPagina, 2) }}</td> <!-- Total -->

                                        <td colspan="13"></td> <!-- Colspan para cubrir columnas restantes -->
                                    </tr>
                                </tfoot>
                            </table>
                        </div>


                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $movcajas->firstItem() }} a {{ $movcajas->lastItem() }} de
                                    {{ $movcajas->total() }} registros</p>
                            </div>

                            <div class="col s12 m6 right-align">
                                {{ $movcajas->links('vendor.adminlte-templates.common.paginator_always') }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
