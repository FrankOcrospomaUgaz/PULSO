@extends('layouts.app')

@section('template_title')
    Operaciones
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
                                <!-- Input con label -->
                                <div class="input-field" style="flex: 1; margin-top: 0;">
                                    <input type="text" id="search-input" class="validate" data-url-base="{{ route('operaciones.index') }}">
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
                            @foreach ($ope_ope as $operacion)
                                @if ($operacion->tipo == 'Tabla')
                                    @isset($id)
                                        @if ($operacion->parametros != '{}')
                                            <a href="{{ route($operacion->accion, $id) }}"
                                                class="btn waves-effect waves-light {{ $operacion->color }} btn-crear"
                                                style="border-radius: 10px; margin-top:0;">
                                                {!! $operacion->imagen !!} {{ $operacion->descripcion }}
                                            </a>
                                        @endif
                                    @else
                                        @if ($operacion->parametros == '{}')
                                            <a href="{{ route($operacion->accion) }}"
                                                class="btn waves-effect waves-light {{ $operacion->color }} btn-crear"
                                                style="border-radius: 10px; margin-top:0;">
                                                {!! $operacion->imagen !!} {{ $operacion->descripcion }}
                                            </a>
                                        @endif
                                    @endisset
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Contenedor del selector de registros por página y botones de exportación -->
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

                        <!-- Botones de exportación (expandir/filtro) -->
                        <div class="col s12 m4 l4 right-align"
                            style="display: flex; align-items: center; justify-content: flex-end; padding-right:0;">
                            <button class="btn-flat btn-grid-options tooltipped" id="btn-expand-compress-MovCaja"
                                type="button" onclick="expandOrCompressChildRows();" data-position="top"
                                data-tooltip="Expandir filas">
                                <i class="fa-solid fa-expand" style="color:#343a40;"></i>
                            </button>

                            <button class="btn-flat btn-grid-options tooltipped" id="btn-filtro" type="button"
                                onclick="mostrarFiltro();" data-position="top" data-tooltip="Filtros">
                                <i class="fa-solid fa-magnifying-glass-minus" style="color:#343a40;"></i>
                            </button>
                        </div>
                    </div>


                    <div id="tb-container" class="table-responsive">
                        <table id="data-table" class="highlight striped centered nowrap" style="width:100%">
                            <thead class="thead-custom">
                                <tr>
                                    <th>No</th>
                                    <th>Tabla</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Comentario</th>
                                    <th>Acción</th>
                                    <th>Imagen</th>
                                    <th>Color</th>
                                    <th>Parámetros</th>
                                    <th>Estado</th>
                                    <th>Operaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($operaciones as $operacione)
                                    <tr>
                                        <td>{{ ++$i }}</td>
                                        <td>{{ $operacione->Tabla->comentario }}</td>
                                        <td>{{ $operacione->tipo }}</td>
                                        <td>{{ $operacione->descripcion }}</td>
                                        <td>{{ $operacione->comentario }}</td>
                                        <td>{{ $operacione->accion }}</td>
                                        <td>{!! $operacione->imagen !!}</td>
                                        <td>{{ $operacione->color }}</td>
                                        <td>{{ $operacione->parametros }}</td>
                                        <td>
                                            <span
                                                class="estado-label {{ $operacione->estado == 'Normal' ? 'green-text' : 'red-text' }} bold">
                                                {{ $operacione->estado }}
                                            </span>
                                        </td>
                                        <td>
                                            @foreach ($ope_ope as $operacion)
                                                @if ($operacion->tipo == 'Registro' && $operacione->estado == 'Normal')
                                                    @if ($operacion->accion === 'operaciones.destroy')
                                                        <form action="{{ route($operacion->accion, $operacione->id) }}" class="delete-form"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </button>
                                                        </form>
                                                    @else
                                                        @isset($id)
                                                            <a href="{{ route('tablas.' . $operacion->accion, ['tabla' => $id, 'operacione' => $operacione->id]) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-crear"
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                        @else
                                                            <a href="{{ route($operacion->accion, $operacione->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-crear"
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                        @endisset
                                                    @endif
                                                @endif
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Mostrar el mensaje personalizado en español -->
                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $operaciones->firstItem() }} a {{ $operaciones->lastItem() }} de
                                    {{ $operaciones->total() }} registros
                                </p>
                            </div>

                            <!-- Mostrar la paginación de Laravel -->
                            <div class="col s12 m6 right-align">
                                {{ $operaciones->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection