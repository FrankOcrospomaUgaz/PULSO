@extends('layouts.app')

@section('template_title')
    Persona
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
                    <div class="row" id="filtros" style="margin-bottom: 10px; display: flex; flex-wrap: wrap;">
                        <!-- Buscador + Botón Buscar -->
                        <div class="col s12 m6 left-align order-mobile-2 order-desktop-1">
                            <div style="display: flex; align-items: flex-start; gap: 5px; width: 100%;">
                                <!-- Input con icono y label -->
                                <div class="input-field" style="flex: 1; margin-top: 0;">
                                    <i class="material-icons prefix">search</i>
                                    <input type="text" id="search-input" class="validate"
                                        data-url-base="{{ route('sucursal.verPersonal', $sucursal_id) }}">
                                    <label for="search-input">Buscar</label>
                                </div>
                                <!-- Botón Buscar -->
                                <a class="btn waves-effect waves-light darken-2 tooltipped btn-buscar" data-tooltip="Buscar"
                                    type="button" onclick="realizarBusqueda();">
                                    <i class="fa-solid fa-magnifying-glass white-text"></i>
                                </a>
                            </div>
                        </div>

                        <input type="hidden" name="sucursal_id" id="sucursal_id" value="{{ $sucursal_id }}">

                        <!-- Botón NUEVO -->
                        <div class="col s12 m6 right-align order-mobile-1 order-desktop-2"
                            style="margin-top:0; padding-top:0;">
                            @foreach ($operaciones as $operacion)
                                @if ($operacion->tipo == 'Tabla')
                                    <a href="{{ route($operacion->accion, [$sucursal_id, 'CRUD']) }}"
                                        class="btn waves-effect waves-light btn-crear-persona {{ $operacion->color }}"
                                        data-sucursal-id="{{ $sucursal_id }}" style="border-radius: 10px; margin-top:0;">
                                        {!! $operacion->imagen !!} {{ $operacion->descripcion }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Contenedor del selector de registros por página y botones de expansión/filtros -->
                    <div class="row valign-wrapper" style="margin-bottom: 10px;">
                        <!-- Selector de registros y Filtro No Grato -->
                        <div class="col s12 m8 l8 left-align valign-wrapper" style="padding-left:13px; gap: 15px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center;">
                                <span style="margin-right: 8px;">Registros por página:</span>
                                <div class="input-field" style="margin: 0;">
                                    <select id="records-per-page" class="browser-default" onchange="realizarBusqueda()">
                                        <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
                                        <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100</option>
                                    </select>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center;">
                                <span style="margin-right: 8px;">Condición:</span>
                                <div class="input-field" style="margin: 0;">
                                    <select id="no_grato" name="no_grato" class="browser-default" onchange="realizarBusqueda()">
                                        <option value="Todos" {{ request('no_grato') == 'Todos' || !request('no_grato') ? 'selected' : '' }}>Todos</option>
                                        <option value="0" {{ request('no_grato') === '0' ? 'selected' : '' }}>Persona Grata</option>
                                        <option value="1" {{ request('no_grato') === '1' ? 'selected' : '' }}>Persona No Grata</option>
                                    </select>
                                </div>
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
                                        <th class="no-hide">Tipo</th>
                                        <th class="no-hide">Nro. documento</th>
                                        <th class="no-hide">Nombres</th>
                                        <th>Fecha</th>
                                        <th>Género</th>
                                        <th>Dirección</th>
                                        <th>Distrito</th>
                                        <th>Email</th>
                                        <th>Teléfono Fijo</th>
                                        <th>Teléfono Móvil</th>
                                        <th>Imagen</th>
                                        <th>Compartido</th>
                                        <th class="no-hide">Condición</th>
                                        <th>Operaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($personas as $persona)
                                        <tr>
                                            <td>
                                                {{ $persona->personamaestro->tipopersona === 'Natural'
                                                    ? 'DNI'
                                                    : ($persona->personamaestro->tipopersona === 'Juridico'
                                                        ? 'RUC'
                                                        : $persona->personamaestro->tipopersona) }}
                                            </td>

                                            <td>{{ $persona->personamaestro->nrodoc }}</td>
                                            <td>{{ $persona->nombres }}</td>
                                            <td>{{ $persona->personamaestro->fechanac }}</td>
                                            <td>{{ $persona->personamaestro->sexo }}</td>
                                            <td>{{ $persona->direccion }}</td>
                                            <td>{{ $persona->ubigeo->descripcion ?? '' }}</td>
                                            <td>{{ $persona->email }}</td>
                                            <td>{{ $persona->telefonofijo }}</td>
                                            <td>{{ $persona->telefonomovil }}</td>
                                            <td>{{ $persona->imagen }}</td>
                                            <td>{{ $persona->compartido }}</td>
                                            <td>
                                                @if ($persona->no_grato)
                                                    <span class="badge red white-text" style="display: inline-flex; align-items: center; justify-content: center; float: none; margin: 0; padding: 4px 10px; border-radius: 6px; font-weight: bold; line-height: 1;">No Grata</span>
                                                @else
                                                    <span class="badge green white-text" style="display: inline-flex; align-items: center; justify-content: center; float: none; margin: 0; padding: 4px 10px; border-radius: 6px; font-weight: bold; line-height: 1;">Grata</span>
                                                @endif
                                            </td>

                                            <td>
                                                @foreach ($operaciones as $operacion)
                                                    @if ($operacion->tipo == 'Registro')
                                                        @php
                                                            // Decodificar los parámetros almacenados en el campo 'parametros'
                                                            $params = json_decode($operacion->parametros, true);
                                                            $routeParams = [];

                                                            // Asignar el id de la persona y de la sucursal manualmente
                                                            foreach ($params as $param) {
                                                                if ($param == 'idpersona') {
                                                                    $routeParams[] = $persona->id; // id de persona
                                                                } elseif ($param == 'idsucursal') {
                                                                    $routeParams[] = $persona->sucursal_id; // id de sucursal
                                                                }
                                                            }
                                                        @endphp

                                                        <!-- Si es 'persona.destroy' -->
                                                        @if ($operacion->accion === 'persona.destroy')
                                                            <form action="{{ route($operacion->accion, $routeParams) }}"
                                                                class="delete-form" method="POST" style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                    data-tooltip="{{ $operacion->descripcion }}">
                                                                    {!! $operacion->imagen !!}
                                                                </button>
                                                            </form>

                                                            <!-- Si es 'persona.edit' o 'persona.verUsuario' -->
                                                        @else
                                                            <a href="{{ route($operacion->accion, $routeParams) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-crear"
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
                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $personas->firstItem() }} a {{ $personas->lastItem() }} de
                                    {{ $personas->total() }} registros
                                </p>
                            </div>

                            <div class="col s12 m6 right-align">
                                {{ $personas->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Structure -->
    <div id="modal-opcion" class="modal" style="max-width: 400px;">
        <div class="modal-content center-align">
            <!-- Botón de cierre (X) -->
            <a href="#!" class="modal-close btn-flat" style="position: absolute; right: 10px; top: 10px;">
                <i class="material-icons">close</i>
            </a>

            <!-- Contenido del modal -->
            <div class="swal2-icon swal2-question swal2-icon-show" style="display: flex;">
                <div class="swal2-icon-content">?</div>
            </div>
            <h4>Registrar personal</h4>
            <p>¿De qué manera desea registrar su nuevo empleado?</p>
            <div class="modal-footer center-align">
                <button id="opcion-doc-persona" class="btn btn-large" data-sucursal-id="{{ $sucursal_id }}">Nro. de
                    documento</button>
                <button id="opcion-nombre-persona" class="btn btn-large"
                    data-sucursal-id="{{ $sucursal_id }}">Nombre</button>

            </div>
        </div>
    </div>
@endsection
