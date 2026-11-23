@extends('layouts.app')

@section('template_title')
    Alojamientos
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">

                    <!-- Filtro de búsqueda -->
                    <div class="row valign-wrapper" id="filtros" style="display: none;  margin: 0">
                        <div class="col s12 m6">
                            <div class="input-field">
                                <i class="material-icons prefix">search</i>
                                <input type="text" id="search-input" class="validate" placeholder="Buscar...">
                            </div>
                        </div>
                        <!-- Filtro de Situación -->
                        <div class="col s12 m3">
                            <div class="input-field">
                                <select id="situacion-filter" name="situacion" onchange="filterBySituacion()">
                                    @foreach ($situaciones as $situacion)
<option value="{{ $situacion }}"
{{ request('situacion') == $situacion ? 'selected' : '' }}>
{{ $situacion }}
</option>
                                    @endforeach
                                </select>
                                <label>Situación</label>
                            </div>
                        </div>


                    </div>
                    <div class="row valign-wrapper" style=" margin: 0">
                    <div class="col s12 m12 left-align valign-wrapper">
    <label for="records-per-page">Registros por página:</label>
    <div class="input-field">
        <select id="records-per-page" class="browser-default" onchange="filterBySituacion()">
            <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
            <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100</option>
        </select>
    </div>
</div>



                        <div class="search-with-report right-align"
                            style="display: flex; align-items: center; justify-content: flex-end;">
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

                    <!-- Mensajes de éxito -->
                    @if ($message = Session::get('success'))
                        <div class="card-panel green lighten-4 green-text text-darken-4">
                            <p>{{ $message }}</p>
                        </div>
                    @endif
                    <div id="tb-container" class="table-wrapper">
                        <!-- Contenedor con scroll horizontal solo para la tabla -->
                        <div class="table-scroll" style="overflow-x: auto;">
                            <table id="data-table" class="highlight striped centered"
                                style="width: max-content; min-width: 100%;">
                                <thead class="thead-custom">
                                    <tr>
                                        <th class="no-hide">Habitación</th>
                                        <th class="no-hide">Tipo Habitación</th>
                                        <th>Piso</th>


                                        @if ($situacionactual === 'En curso')
                                            <th class="no-hide">Huespeds</th>
                                            <th class="no-hide">Fecha Ingreso</th>
                                            <th>Fecha Salida Programada</th>
                                            <th class="no-hide">Situación</th>
                                            <th class="ocultar">Estado real</th>
                                            <th class="no-hide">Total</th>
                                            <th>Contador</th>
                                            <th class="no-hide">Estadía</th>
                                            <th>Comentario</th>
                                        @else
                                            <th>P. por día</th>
                                            <th>P. por horas</th>
                                            <th>Situación</th>
                                            <th style="display: none">Estado real</th>
                                        @endif


                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($movimientoHabitacion as $mov)
                                        <tr>
                                            <td class="inpt-cantidad-mc ">{{ collect($mov->habitaciones ?? [])->pluck('numero')->map(fn($num) => trim($num))->join(', ') }}</td>

                                            <td>{{ $mov->habitacion->tipohabitacion->nombre ?? '' }}
                                            </td>
                                            <td>{{ $mov->habitacion->piso->nombre ?? '' }}
                                            </td>


                                            @if ($situacionactual === 'En curso')
                                                                  <td>
                    @foreach($mov->huespeds as $huesped)
                    {{ optional(optional($huesped->persona)->personamaestro)->nrodoc ?? 'S/D' }} - {{ optional($huesped->persona)->nombres ?? '' }} {{ optional($huesped->persona)->apellidos ?? '' }}<br>
                    @endforeach
                    @if($mov->huespeds->isEmpty())
                        0 -CLIENTES VARIOS
                    @endif
                </td>
                                                <td>
                                                    {!! $mov->fechaingreso
                                                        ? \Carbon\Carbon::parse($mov->fechaingreso)->format('Y-m-d') .
                                                            '<br>' .
                                                            \Carbon\Carbon::parse($mov->fechaingreso)->format('h:i:s A')
                                                        : '' !!}
                                                </td>
                                                <td>
                                                    {!! $mov->fechasalida
                                                        ? \Carbon\Carbon::parse($mov->fechasalida)->format('Y-m-d') .
                                                            '<br>' .
                                                            \Carbon\Carbon::parse($mov->fechasalida)->format('h:i:s A')
                                                        : '' !!}
                                                </td>

                                                <td>
                                                    <span
                                                        class="new badge 
                                                                                                                                {{ $situacionactual === 'Disponibles'
                                                                                                                                    ? 'green'
                                                                                                                                    : ($situacionactual === 'En mantenimiento'
                                                                                                                                        ? 'yellow black-text'
                                                                                                                                        : ($situacionactual === 'En curso'
                                                                                                                                            ? 'red'
                                                                                                                                            : '')) }}"
                                                        data-badge-caption="{{ $situacionactual === 'Disponibles' ? 'Disponible' : $situacionactual }}">
                                                    </span>

                                                </td>
                                                <td class="ocultar">
                                                    {{ $mov->situacion }}
                                                    <a href="{{ route('finalizarmovhabitacion', $mov->id) }}"
                                                        class="btn-floating red darken-1 waves-light tooltipped btn-activar">
                                                        <i class=""></i>
                                                    </a>
                                                </td>


                                                <td class="inpt-cantidad-mc ">S/. {{ number_format($mov->total, 2) }}</td>
                                                <td class="inpt-cantidad-mc">
                                                    <div class="tiempo-restante-container">
                                                        @if (!is_null($mov->habitacion) && !empty($mov->fechasalida))
                                                            <div id="tiempo-restante-{{ $mov->id }}"
                                                                class="tiempo-restante"
                                                                data-habitacion-id="{{ $mov->id }}"
                                                                data-hora-fin="{{ \Carbon\Carbon::parse($mov->fechasalida)->format('Y-m-d H:i:s') }}">
                                                                <span id="timer-{{ $mov->id }}"></span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>{{ $mov->horas ? $mov->horas . ' hrs' : ($mov->dias ?? 0) . ' noches' }}
                                                </td>

                                                <td>{{ $mov->comentario ?? '' }}</td>
                                            @else
                                                <td class="inpt-cantidad-mc ">{{ $mov->tipohabitacion->precio }}</td>
                                                <td>
                                                    @if ($mov->tipohabitacion->json_precio_horas)
                                                        @php
                                                            $precios = json_decode(
                                                                $mov->tipohabitacion->json_precio_horas,
                                                                true,
                                                            );
                                                        @endphp
                                                        @if ($precios)
                                                            @foreach ($precios as $precio)
                                                                {{ $precio['hora'] }}:
                                                                S/{{ number_format($precio['precio'], 2) }} <br>
                                                            @endforeach
                                                        @endif
                                                    @else
                                                        No hay precios definidos.
                                                    @endif
                                                </td>
                                                <td>
                                                    <span
                                                        class="new badge 
                                                            {{ $situacionactual === 'Disponibles'
                                                                ? 'green'
                                                                : ($situacionactual === 'En mantenimiento'
                                                                    ? 'yellow black-text'
                                                                    : ($situacionactual === 'En curso'
                                                                        ? 'red'
                                                                        : '')) }}"
                                                        data-badge-caption="{{ $situacionactual === 'Disponibles' ? 'Disponible' : $situacionactual }}">
                                                    </span>

                                                </td>
                                            @endif

                                            <td>
                                                @foreach ($operaciones as $operacion)
                                                    @if ($operacion->tipo === 'Registro')
                                                        @if (
                                                            !is_null($mov->habitacion) &&
                                                                $operacion->accion !== 'movhabitacion.checkin' &&
                                                                $operacion->accion !== 'habitacion.limpiar' &&
                                                                $operacion->accion !== 'habitacion.dejarlimpiar')
                                                            <!-- Verificar si habitacion no es null -->
                                                            <a href="{{ route($operacion->accion, $mov->habitacion_id) }}"
                                                                data-icono="fas fa-gifts"
                                                                data-titulo="{{ $operacion->descripcion }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-cargar-crud"
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                        @elseif(
                                                            ($operacion->accion === 'movhabitacion.checkin' ||
                                                                $operacion->accion === 'habitacion.limpiar' ||
                                                                $operacion->accion === 'habitacion.reservar') &&
                                                                $situacionactual === 'Disponibles')
                                                            <a href="{{ route($operacion->accion, $mov->id) }}"
                                                                data-icono="fas fa-gifts"
                                                                data-titulo="{{ $operacion->descripcion }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-cargar-crud"
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                        @elseif(
                                                            ($operacion->accion === 'habitacion.reservar' || $operacion->accion === 'habitacion.dejarlimpiar') &&
                                                                $situacionactual === 'En mantenimiento')
                                                            <a href="{{ route($operacion->accion, $mov->id) }}"
                                                                data-icono="fas fa-gifts"
                                                                data-titulo="{{ $operacion->descripcion }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-cargar-crud"
                                                                data-tooltip="{{ $operacion->descripcion }}">
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
                        <!-- Paginación -->
                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $movimientoHabitacion->firstItem() }} a
                                    {{ $movimientoHabitacion->lastItem() }} de {{ $movimientoHabitacion->total() }}
                                    registros
                                </p>
                            </div>
                            <div class="col s12 m6 right-align">
                                {{ $movimientoHabitacion->links() }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
