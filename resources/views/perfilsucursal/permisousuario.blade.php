@extends('layouts.app')


@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">

                    <div class="row" id="filtros" style="margin-bottom: 10px; display: flex; flex-wrap: wrap;">
                        <!-- Buscador + Botón Buscar -->
                        <div class="col s12 m6 left-align order-mobile-2 order-desktop-1">
                            <div style="display: flex; align-items: flex-start; gap: 5px; width: 100%;">
                                <!-- Input sin icono ni placeholder -->
                                <div class="input-field" style="flex: 1; margin-top: 0;">
                                    <input type="text" id="search-input" class="validate"
                                        data-url-base="{{ route('verPermisoUsuario', $id) }}">
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
                                    <a href="{{ route($operacion->accion, $id) }}"
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
                                <select id="records-per-page" class="browser-default" onchange="realizarBusqueda()">
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
                                        <th class="no-hide">Módulo</th>
                                        <th class="no-hide">Tipo</th>
                                        <th class="no-hide">Opción</th>
                                        <th class="no-hide">Estado</th>
                                        <th>Operaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($permisos as $permiso)
                                        <tr>
                                            <td>{{ $permiso->menuopcione->modulo->descripcion }}</td>
                                            <td>{{ $permiso->menuopcione->tipoopcione->descripcion }}</td>
                                            <td>{{ $permiso->menuopcione->descripcion }}</td>
                                            <td>
                                                <span class="new badge {{ $permiso->situacion ? 'green' : 'red' }}"
                                                    data-badge-caption="{{ $permiso->situacion ? 'Activado' : 'Desactivado' }}"></span>
                                            </td>
                                            <td>
                                                @foreach ($operaciones as $operacion)
                                                    @if ($operacion->tipo == 'Registro')
                                                        @if ($permiso->situacion == false && $operacion->accion === 'permisousuario.activar')
                                                            <a href="{{ route($operacion->accion, $permiso->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-activar"
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                        @elseif ($permiso->situacion == true && $operacion->accion === 'permisousuario.desactivar')
                                                            <a href="{{ route($operacion->accion, $permiso->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-activar"
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
                                <p>Mostrando {{ $permisos->firstItem() }} a {{ $permisos->lastItem() }} de
                                    {{ $permisos->total() }} registros
                                </p>
                            </div>
                            <div class="col s12 m6 right-align">
                                {{ $permisos->links() }}
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
@endsection