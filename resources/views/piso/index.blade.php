@extends('layouts.app')

@section('template_title')
    Pisos
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
                 <!-- 🔹 FILTROS SUPERIORES -->
<div class="row" id="filtros" style="margin-bottom: 10px; display: flex; flex-wrap: wrap;">
    <!-- Buscador + Botón Buscar -->
    <div class="col s12 m6 left-align order-mobile-2 order-desktop-1">
        <div style="display: flex; align-items: flex-start; gap: 5px; width: 100%;">
            <!-- Input con label -->
            <div class="input-field" style="flex: 1; margin-top: 0;">
                <input type="text" id="search-input" class="validate"
                       value="{{ request('search', '') }}"
                       data-url-base="{{ route('pisos.index') }}">
                <label for="search-input">Buscar</label>
            </div>

            <!-- Botón Buscar -->
            <a class="btn waves-effect waves-light darken-2 tooltipped btn-buscar"
               data-tooltip="Buscar" type="button" onclick="realizarBusqueda();">
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

<!-- 🔹 SELECTOR DE REGISTROS Y BOTONES -->
<div class="row valign-wrapper" style="margin-bottom: 10px;">
    <!-- Selector de registros -->
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

                    @if ($message = Session::get('success'))
                        <div class="card-panel green lighten-4 green-text text-darken-4">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    <div id="tb-container" class="table-responsive">
                        <table id="data-table" class="highlight striped centered nowrap" style="width:100%">
                            <thead class="thead-custom">
                                <tr>
                                    <th class="no-hide">Nombre</th>
                                    <th class="no-hide">Estado</th>
                                    <th>Operaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pisos as $piso)
                                    <tr>
                                        <td>{{ $piso->nombre }}</td>
                                        <td>
                                            <span
                                                class="new badge {{ $piso->estado == 'Activo' ? 'green' : 'red' }} " data-badge-caption="{{ $piso->estado }}">
                                                
                                            </span>
                                        </td>
                                        <td>
                                            @foreach ($operaciones as $operacion)
                                                @if ($operacion->tipo == 'Registro')
                                                    @if ($operacion->accion === 'pisos.destroy')
                                                        <form action="{{ route($operacion->accion, $piso->id) }}"
                                                            method="POST" class="delete-form-piso" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </button>
                                                        </form>
                                                    @else
                                                        <a href="{{ route($operacion->accion, $piso->id) }}"
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

                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $pisos->firstItem() }} a {{ $pisos->lastItem() }} de
                                    {{ $pisos->total() }} registros</p>
                            </div>

                            <div class="col s12 m6 right-align">
                                {{ $pisos->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
