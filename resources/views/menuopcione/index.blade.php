@extends('layouts.app')

@section('template_title')
    Menuopciones
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
                                    <input type="text" id="search-input" class="validate"
                                        data-url-base="{{ route('menuopciones.index') }}">
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
                            <button class="btn-flat btn-grid-options tooltipped" id="btn-expand-compress-MenuOpciones"
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


                    <div id="tb-container" class="table-wrapper">
                        <!-- Contenedor con scroll horizontal solo para la tabla -->
                        <div class="table-scroll" style="overflow-x: auto;">
                            <table id="data-table" class="highlight striped centered"
                                style="width: max-content; min-width: 100%;">
                                <thead class="thead-custom">
                                    <tr>
                                        <th class="no-hide">No</th>
                                        <th class="no-hide">Descripción</th>
                                        <th>Orden</th>
                                        <th class="no-hide">Icono</th>
                                        <th class="no-hide">Acción</th>
                                        <th>Tabla</th>
                                        <th>Módulo</th>
                                        <th>Tipo de Opción</th>
                                        <th>Acceso Rápido</th>
                                        <th>Condiciones AR</th>
                                        <th class="no-hide">Estado</th>
                                        <th class="no-hide">Operaciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($menuopciones as $menuopcione)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            <td>{{ $menuopcione->descripcion }}</td>
                                            <td>{{ $menuopcione->orden }}</td>
                                            <td><i class="{{ $menuopcione->icono }}"></i></td>
                                            <td>{{ $menuopcione->accion }}</td>
                                            <td>{{ $menuopcione->tabla->descripcion ?? 'N/A' }}</td>
                                            <td>{{ $menuopcione->modulo->descripcion ?? 'N/A' }}</td>
                                            <td>{{ $menuopcione->tipoopcione->descripcion ?? 'N/A' }}</td>
                                            <td>
                                                <span
                                                    class="{{ $menuopcione->acceso_rapido == 'S' ? 'green-text' : 'red-text' }} bold">
                                                    {{ $menuopcione->acceso_rapido == 'S' ? 'Sí' : 'No' }}
                                                </span>
                                            </td>
                                            <td>{{ json_encode($menuopcione->condiciones_ar, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}
                                            </td>
                                            <td>
                                                <span
                                                    class="estado-label {{ $menuopcione->estado == 'Normal' ? 'green-text' : 'red-text' }} bold">
                                                    {{ $menuopcione->estado }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($menuopcione->estado !== 'Eliminado')
                                                    @foreach ($operaciones as $operacion)
                                                        @if ($operacion->tipo == 'Registro' && $menuopcione->orden != 0)
                                                            @if ($operacion->accion === 'menuopciones.destroy')
                                                                <form action="{{ route($operacion->accion, $menuopcione->id) }}" method="POST"
                                                                    class="delete-form" style="display: inline;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                        class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                        data-tooltip="{{ $operacion->descripcion }}">
                                                                        {!! $operacion->imagen !!}
                                                                    </button>
                                                                </form>
                                                            @elseif($operacion->accion === 'moveOrder')
                                                                @if ($menuopcione->orden == $minOrden)
                                                                    @if ($operacion->comentario == 'Bajar')
                                                                        <button
                                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped move-order"
                                                                            data-tooltip="{{ $operacion->comentario }}" data-id="{{ $menuopcione->id }}"
                                                                            data-url="{{ route($operacion->accion, ['id' => $menuopcione->id, 'direction' => 'up']) }}">
                                                                            {!! $operacion->imagen !!}
                                                                        </button>
                                                                    @endif
                                                                @elseif ($menuopcione->orden == $maxOrden)
                                                                    @if ($operacion->comentario == 'Subir')
                                                                        <button
                                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped move-order"
                                                                            data-tooltip="{{ $operacion->comentario }}" data-id="{{ $menuopcione->id }}"
                                                                            data-url="{{ route($operacion->accion, ['id' => $menuopcione->id, 'direction' => 'down']) }}">
                                                                            {!! $operacion->imagen !!}
                                                                        </button>

                                                                    @endif
                                                                @else
                                                                    <button
                                                                        class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped move-order"
                                                                        data-tooltip="{{ $operacion->comentario }}" data-id="{{ $menuopcione->id }}"
                                                                        data-url="{{ route($operacion->accion, ['id' => $menuopcione->id, 'direction' => $operacion->comentario]) }}">
                                                                        {!! $operacion->imagen !!}
                                                                    </button>
                                                                @endif
                                                            @else
                                                                <a href="{{ route($operacion->accion, $menuopcione->id) }}"
                                                                    class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-crear"
                                                                    data-tooltip="{{ $operacion->descripcion }}">
                                                                    {!! $operacion->imagen !!}
                                                                </a>
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Mostrar el mensaje personalizado en español -->
                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $menuopciones->firstItem() }} a {{ $menuopciones->lastItem() }} de
                                    {{ $menuopciones->total() }} registros
                                </p>
                            </div>

                            <!-- Mostrar la paginación de Laravel -->
                            <div class="col s12 m6 right-align">
                                {{ $menuopciones->links() }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection