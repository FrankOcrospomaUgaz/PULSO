@extends('layouts.app')

@section('template_title')
    Productos
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
                                        data-url-base="{{ route('producto.index') }}">
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
                                        class="btn btn-crear waves-effect waves-light {{ $operacion->color }}"
                                        style="border-radius: 10px; margin-right:10px; margin-bottom:10px;">
                                        {!! $operacion->imagen !!} {{ $operacion->descripcion }}
                                    </a>

                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Contenedor del selector de registros por página y botones -->
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
                                        <th class="no-hide">Código</th>
                                        <th class="no-hide">Descripción</th>
                                        <th class="no-hide">Clasificación</th>
                                        <th>Tipo</th>
                                        <th class="no-hide">Categoría</th>
                                        <th class="no-hide">Situación</th>
                                        <th class="no-hide">P. venta</th>
                                        <th>Moneda</th>
                                        <th>Complemento</th>
                                        <th>Unidad Base</th>
                                        <th>Peso</th>
                                        <th class="no-hide">Kárdex</th>
                                        <th>Marca</th>
                                        <th>Abreviatura</th>
                                        <th>Compuesto</th>
                                        <th>Medida peso</th>
                                        <th>P. compra</th>
                                        <th>Operaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($productos as $producto)
                                        <tr>
                                            <td>{{ $producto->codigo }}</td>
                                            <td>{{ $producto->descripcion }}</td>
                                            <td>
                                                <span
                                                    class="new badge {{ $producto->clasificacion == 'Bien' ? 'purple' : 'red' }}"
                                                    data-badge-caption="{{ $producto->clasificacion }}"></span>
                                            </td>
                                            <td>
                                                <span
                                                    class="new badge {{ $producto->tipo == 'Producto final' ? 'yellow' : 'red' }}"
                                                    data-badge-caption="{{ $producto->tipo }}" style="color: black"></span>
                                            </td>
                                            <td>{{ $producto->categorium->descripcion }}</td>
                                            <td>
                                                <span
                                                    class="new badge {{ $producto->relProductosucursal->situacion == 'Activado' ? 'green' : 'red' }}"
                                                    data-badge-caption="{{ $producto->relProductosucursal->situacion  }}"></span>
                                            </td>

                                            <td class="inpt-cantidad-mc ">
                                                {{ $producto->relListaUnidadSucursal?->precioventa ?? 'N/A' }}
                                            </td>
                                            <td>{{ $producto->relListaUnidadSucursal?->moneda ?? 'N/A' }}</td>
                                            <td>
                                                <span
                                                    class="new badge {{ $producto->complemento == 'No permite' ? 'blue' : 'red' }}"
                                                    data-badge-caption="{{ $producto->complemento  }}"></span>
                                            </td>

                                            <td>{{ $producto->unidad->descripcion }}</td>
                                            <td>{{ $producto->peso }}</td>
                                            <td>{{ $producto->kardex == 'N' ? 'No' : 'Si' }}</td>

                                            <td>{{ $producto->marca_id }}</td>

                                            <td>{{ $producto->abreviatura }}</td>
                                            <td>{{ $producto->compuesto == 'N' ? 'No' : 'Si'  }}</td>
                                            <td>{{ $producto->medidapeso->descripcion ?? '' }}</td>

                                            <td>{{ $producto->relListaUnidadSucursal?->preciocompra }}</td>

                                            <td>
                                                @foreach ($operaciones as $operacion)
                                                    @if ($operacion->tipo == 'Registro')
                                                        @if ($operacion->accion === 'producto.destroy')
                                                            <form action="{{ route($operacion->accion, $producto->id) }}" method="POST"
                                                                class="delete-form" style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                    data-tooltip="{{ $operacion->descripcion }}">
                                                                    {!! $operacion->imagen !!}
                                                                </button>
                                                            </form>
                                                        @elseif ($operacion->accion === 'producto.edit')
                                                            <a href="{{ route($operacion->accion, $producto->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-crear"
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
                                <p>Mostrando {{ $productos->firstItem() }} a {{ $productos->lastItem() }} de
                                    {{ $productos->total() }} registros
                                </p>
                            </div>

                            <div class="col s12 m6 right-align">
                                {{ $productos->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection