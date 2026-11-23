@extends('layouts.app')

@section('template_title')
    Habitacion
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
<div class="row valign-wrapper" id="filtros" style="margin-bottom: 10px;">
    <div class="col s12 m6 l6">
        <div style="display: flex; align-items: flex-start; gap: 5px; width: 100%;">
            <div class="input-field" style="flex: 1; margin-top: 0;">
                <input type="text" id="search-input" class="validate"
                       value="{{ request('search', '') }}"
                       data-url-base="{{ route('habitacion.index') }}">
                <label for="search-input">Buscar</label>
            </div>
            <a class="btn waves-effect waves-light darken-2 tooltipped btn-buscar"
               data-tooltip="Buscar" type="button" onclick="realizarBusqueda();">
                <i class="fa-solid fa-magnifying-glass white-text"></i>
            </a>
        </div>
    </div>

    <div class="col s12 m6 l2">
        <div class="input-field" style="margin-top: 0;">
            <select id="situacion-filter" name="situacion" onchange="filterHabitaciones()">
                <option value="">Todas</option>
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

    <div class="col s12 m6 l2">
        <div class="input-field" style="margin-top: 0;">
            <select id="piso-filter" name="piso" onchange="filterHabitaciones()">
                <option value="">Todos</option>
                @foreach ($pisos as $piso)
<option value="{{ $piso->nombre }}"
{{ request('piso') == $piso->nombre ? 'selected' : '' }}>
{{ $piso->nombre }}
</option>
                @endforeach
            </select>
            <label>Piso</label>
        </div>
    </div>

    <div class="col s12 m6 l3">
        <div class="input-field" style="margin-top: 0;">
            <select id="tipohabitacion-filter" name="tipohabitacion" onchange="filterHabitaciones()">
                <option value="">Todos</option>
                @foreach ($tipohabitaciones as $tipohabitacion)
<option value="{{ $tipohabitacion->nombre }}"
{{ request('tipohabitacion') == $tipohabitacion->nombre ? 'selected' : '' }}>
{{ $tipohabitacion->nombre }}
</option>
                @endforeach
            </select>
            <label>Tipo Habitación</label>
        </div>
    </div>

    <div class="col s12 m12 l3 right-align" style="margin-top: 0;">
        @foreach ($operaciones as $operacion)
            @if ($operacion->tipo == 'Tabla')
                <a href="{{ route($operacion->accion) }}"
                    class="btn waves-effect waves-light btn-crear {{ $operacion->color }}"
                    style="border-radius: 10px; margin-top: 0;">
                    {!! $operacion->imagen !!} {{ $operacion->descripcion }}
                </a>
            @endif
        @endforeach
    </div>
</div>

<div class="row valign-wrapper" style="margin-bottom: 10px;">
    <div class="col s12 m8 l8 left-align valign-wrapper" style="padding-left:13px;">
        <span style="margin-right: 8px;">Registros por página:</span>
        <div class="input-field" style="margin: 0;">
            <select id="records-per-page" class="browser-default" onchange="realizarBusqueda()">
                <option value="10" {{ (int) request('perPage', 10) === 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ (int) request('perPage', 10) === 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ (int) request('perPage', 10) === 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ (int) request('perPage', 10) === 100 ? 'selected' : '' }}>100</option>
            </select>
        </div>
    </div>

    <div class="col s12 m4 l4 right-align"
        style="display: flex; align-items: center; justify-content: flex-end; padding-right:0;">
        <button type="button" id="btn-eliminar-habitaciones-masivo"
            class="btn red darken-2 waves-effect waves-light tooltipped"
            data-tooltip="Eliminar seleccionadas" onclick="eliminarHabitacionesMasivo();" disabled
            style="margin-right: 8px; border-radius: 10px;">
            <i class="material-icons left">delete_sweep</i>Eliminar<span class="cantidad-seleccion"></span>
        </button>

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

                    @if ($message = Session::get('success'))
                        <div class="card-panel green lighten-4 green-text text-darken-4">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    <div id="tb-container" class="table-responsive">
                        <table id="data-table" class="highlight striped centered nowrap" style="width:100%">
                            <thead class="thead-custom">
                                <tr>
                                    <th class="no-hide center-align" style="width: 42px;">
                                        <label>
                                            <input type="checkbox" id="chk-habitacion-all" class="filled-in">
                                            <span></span>
                                        </label>
                                    </th>
                                    <th class="no-hide">Número</th>
                                    <th class="no-hide">Situación</th>
                                    <th>Piso</th>
                                    <th class="no-hide">Tipo Habitación</th>
                                     <th class="ocultar">Estado real</th>
                                    <th>Precio por dia</th>
                                    <th>Precio por horas</th>
                                    <th>Detalles</th>
                                    <th>Operaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($habitacions as $habitacion)
                                    <tr>
                                        <td class="center-align">
                                            @if ($habitacion->situacion !== 'Ocupada')
                                                <label>
                                                    <input type="checkbox" class="filled-in chk-habitacion"
                                                        value="{{ $habitacion->id }}">
                                                    <span></span>
                                                </label>
                                            @endif
                                        </td>
                                        <td>{{ $habitacion->numero }}</td>
                                        <td>
                                            <span 
                                                class="new badge 
                                                    {{ $habitacion->situacion == 'Disponible' ? 'green' : '' }}
                                                    {{ $habitacion->situacion == 'Ocupada' ? 'red' : '' }}
                                                    {{ $habitacion->situacion == 'Limpieza' ? 'orange' : '' }}
                                                    {{ $habitacion->situacion == 'Mantenimiento' ? 'brown' : '' }}"
                                                data-badge-caption="{{ $habitacion->situacion }}">
                                            </span>
                                        </td>
                                        
                                        <td>{{ $habitacion->piso->nombre }}</td>
                                        <td>{{ $habitacion->tipohabitacion->nombre }}</td>
                                        <td class="inpt-cantidad-mc ">{{ $habitacion->precio }}</td>
                                        <td>
                                            @if(!empty($habitacion->tipohabitacion->json_precio_horas))
                                                @php
                                                    $precios = json_decode($habitacion->tipohabitacion->json_precio_horas, true);
                                                @endphp
                                        
                                                @if(is_array($precios))
                                                    @foreach($precios as $precio)
                                                        {{ $precio['hora'] }}: S/{{ number_format($precio['precio'], 2) }} <br>
                                                    @endforeach
                                                @endif
                                            @else
                                                {{-- Campo vacío si no hay precios definidos --}}
                                            @endif
                                        </td>
                                        @if ($habitacion->situacion == 'Disponible')
                                            <td class="ocultar">
                                                {{ $habitacion->situacion }}
                                                <a href="{{ route( 'cambiaraocupada', $habitacion->id) }}"
                                                   class="btn-floating red darken-1 waves-light tooltipped btn-activar">
                                                   <i class=""></i>
                                                </a>
                                            </td>
                                        @else
                                            <td class="ocultar">
                                                {{ $habitacion->situacion }}
                                                <a href="{{ route( 'cambiaradisponible', $habitacion->id) }}"
                                                   class="btn-floating red darken-1 waves-light tooltipped btn-activar">
                                                   <i class=""></i>
                                                </a>
                                            </td>
                                        @endif
                                         
                                               
                                        <td>{{ $habitacion->descripcion }}</td>

                                        <td>
                                            {{-- @if ($habitacion->situacion === 'Ocupada')
                                                @foreach ($operaciones as $operacion)
                                                    @if ($operacion->tipo == 'Registro' && $operacion->accion === 'habitacion.edit')
                                                        <a href="{{ route($operacion->accion, $habitacion->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-crear "
                                                            data-tooltip="{{ $operacion->descripcion }}">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @endif
                                                @endforeach
                                            @else --}}
                                                @foreach ($operaciones as $operacion)
                                                    @if ($operacion->tipo == 'Registro')
                                                        @if ($operacion->accion === 'habitacion.destroy' && $habitacion->situacion !== 'Ocupada')
                                                            <form action="{{ route($operacion->accion, $habitacion->id) }}"
                                                                class="delete-form" method="POST" style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                    data-tooltip="{{ $operacion->descripcion }}">
                                                                    {!! $operacion->imagen !!}
                                                                </button>
                                                            </form>
                                                        @elseif(($operacion->accion === 'habitacion.limpiar' || $operacion->accion === 'habitacion.mantenimiento') 
                                                            && ($habitacion->situacion !== 'Limpieza' && $habitacion->situacion !== 'Mantenimiento' && $habitacion->situacion !== 'Ocupada' ))
                                                            <a href="{{ route($operacion->accion, [$habitacion->id, 'CRUD']) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-cargar-crud tooltipped"
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                        @elseif($operacion->accion === 'habitacion.dejarlimpiar' 
                                                            && ($habitacion->situacion === 'Limpieza' || $habitacion->situacion == 'Mantenimiento'))
                                                            <a href="{{ route($operacion->accion, [$habitacion->id, 'CRUD']) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-cargar-crud tooltipped"
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                        @elseif($operacion->accion == 'habitacion.edit')
                                                            <a href="{{ route($operacion->accion, $habitacion->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-crear "
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            {{-- @endif --}}
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $habitacions->firstItem() }} a {{ $habitacions->lastItem() }} de
                                    {{ $habitacions->total() }} registros</p>
                            </div>

                            <div class="col s12 m6 right-align">
                                {{ $habitacions->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
