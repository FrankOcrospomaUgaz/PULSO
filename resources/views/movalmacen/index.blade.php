@extends('layouts.app')

@section('template_title')
    Movimientos de Almacén
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
                    <!-- Filtros -->
                    <div class="row" id="filtros" style="margin-bottom: 10px; display: flex; flex-wrap: wrap;">
                        <!-- Buscador + Botón Buscar -->
                        <div class="col s12 m6 left-align order-mobile-2 order-desktop-1">
                            <div style="display: flex; align-items: flex-start; gap: 5px; width: 100%;">
                                <!-- Input con label (con data-url-base) -->
                                <div class="input-field" style="flex: 1; margin-top: 0;">
                                    <input type="text" id="search-input" class="validate" 
                                        data-url-base="{{ route('movalmacen.index') }}">
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
                                        class="btn waves-effect waves-light btn-cargar-crud {{ $operacion->color }}"
                                        data-icono="fa-solid fa-plus left" data-titulo="Movimientos de almacén | Nuevo"
                                        style="border-radius: 10px; margin-right:10px; margin-bottom:10px;">
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
                                        <th class="no-hide">Número</th>
                                        <th>Tipo</th>
                                        <th>Fecha</th>
                                        <th>Usuario</th>

                                        <th>Persona</th>
                                        <th>Responsable</th>
                                        <th class="no-hide">Estado</th>
                                        <th class="no-hide">Situación</th>

                                        <th class="no-hide">Origen</th>
                                        <th>Comentario</th>
                                        <th>Operaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($movalmacenes as $movalmacen)
                                        <tr>
                                            <td>{{ $movalmacen->movimiento->numero ?? 'N/A' }}</td>
                                            <td>
                                                <span
                                                    class="new badge {{ $movalmacen->movimiento->tipodocumento->nombre == 'Entrada' ? 'blue ' : 'orange' }}"
                                                    data-badge-caption="{{ $movalmacen->movimiento->tipodocumento->nombre }}"></span>
                                            </td>
                                            <td>{{ $movalmacen->movimiento->fecha ?? 'N/A' }}</td>
                                            <td>{{ $movalmacen->movimiento->usuario ?? 'N/A' }}</td>

                                            <td>{{ $movalmacen->movimiento->Persona->personamaestro->nrodoc ?? 'N/A' }} -
                                                {{ $movalmacen->movimiento->persona ?? 'N/A' }}
                                            </td>

                                            <td>{{ $movalmacen->movimiento->Responsable->personamaestro->nrodoc ?? 'N/A' }} -
                                                {{ $movalmacen->movimiento->responsable ?? 'N/A' }}
                                            </td>

                                            <td>
                                                <span
                                                    class="new badge {{ $movalmacen->estado == 'FINALIZADO' ? 'teal ' : 'red' }}"
                                                    data-badge-caption="{{ $movalmacen->estado }}"></span>
                                            </td>
                                            <td>
                                                <span
                                                    class="new badge {{ $movalmacen->movimiento->situacion == 'Activado' ? 'green' : 'red' }}"
                                                    data-badge-caption="{{ $movalmacen->movimiento->situacion }}"></span>
                                            </td>
                                            <td>{{ $movalmacen->movimiento->tipodocumento->tipomovimiento->descripcion ?? 'N/A' }}
                                            </td>
                                            <td>{{ $movalmacen->comentario ?? '' }}</td>
                                            <td>
                                                @foreach ($operaciones as $operacion)
                                                    @if ($operacion->tipo == 'Registro')
                                                        @if ($operacion->accion === 'movalmacen.destroy')
                                                            {{-- <form action="{{ route($operacion->accion, $movalmacen->id) }}"
                                                                method="POST" class="delete-form" style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                    data-tooltip="{{ $operacion->descripcion }}">
                                                                    {!! $operacion->imagen !!}
                                                                </button>
                                                            </form> --}}
                                                        @elseif ($operacion->accion === 'movalmacen.anular')
                                                            <form action="{{ route($operacion->accion, $movalmacen->id) }}" method="POST"
                                                                class="anular-form" style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                    data-tooltip="{{ $operacion->descripcion }}">
                                                                    {!! $operacion->imagen !!}
                                                                </button>
                                                            </form>
                                                        @elseif ($operacion->accion === 'movalmacen.edit')
                                                            <a href="{{ route($operacion->accion, $movalmacen->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                                                                data-tooltip="{{ $operacion->descripcion }}"
                                                                data-titulo="Movimientos de almacén | Editar">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                        @elseif ($operacion->accion === 'movalmacen.verDetalles')
                                                            <a href="{{ route($operacion->accion, $movalmacen->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
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
                                <p>Mostrando {{ $movalmacenes->firstItem() }} a {{ $movalmacenes->lastItem() }} de
                                    {{ $movalmacenes->total() }} registros
                                </p>
                            </div>

                            <div class="col s12 m6 right-align">
                                {{ $movalmacenes->links() }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection