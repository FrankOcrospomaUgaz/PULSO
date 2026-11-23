@extends('layouts.app')

@section('template_title')
    Stock de Productos
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">

                    <!-- 🔹 Filtro de búsqueda y filtros adicionales -->
                    <div class="row" id="filtros" style="margin-bottom: 10px;">
                        <!-- Filtros -->
                        <div class="col s12">
                            <!-- Buscador -->
                            <div class="col s12 m4">
                                <div class="input-field">
                                    <input type="text" id="search-input" class="validate"
                                        data-url-base="{{ route('stockproducto.index') }}">
                                    <label for="search-input">Buscar</label>
                                </div>
                            </div>

                            <!-- Filtro de Categoría -->
                            <div class="col s12 m2">
                                <div class="input-field">
                                    <select id="categoria-filter" name="categoria" onchange="filterStock()">
                                        <option value="" disabled selected>Seleccionar Categoría</option>
                                        @foreach ($categorias as $categoria)
                                            <option value="{{ $categoria->id }}"
                                                {{ request('categoria') == $categoria->id ? 'selected' : '' }}>
                                                {{ $categoria->descripcion }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label>Categoría</label>
                                </div>
                            </div>

                            <!-- Filtro de Tipo -->
                            <div class="col s12 m2">
                                <div class="input-field">
                                    <select id="tipo-filter" name="tipo" onchange="filterStock()">
                                        <option value="" disabled selected>Seleccionar Tipo</option>
                                        @foreach ($tipos as $tipo)
                                            <option value="{{ $tipo }}"
                                                {{ request('tipo') == $tipo ? 'selected' : '' }}>
                                                {{ $tipo }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label>Tipo</label>
                                </div>
                            </div>

                            <!-- Filtro de Situación -->
                            <div class="col s12 m2">
                                <div class="input-field">
                                    <select id="situacion-filter" name="situacion" onchange="filterStock()">
                                        <option value="" disabled selected>Seleccionar Situación</option>
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

                        <!-- Botones de exportación debajo alineados a la derecha -->
                        <div class="col s12 right-align">
                            <div class="input-field" style="display: inline-flex; gap: 10px; flex-wrap: wrap;">
                                <button class="btn teal tooltipped btn-buscar" id="btn-MovVenta" data-tooltip="Buscar"
                                    type="button" onclick="filterStock()">
                                    <i class="fa-solid fa-magnifying-glass white-text"></i>
                                </button>
                                <button class="btn red tooltipped btn-pdf" id="btn-pdf-MovVenta"
                                    data-tooltip="Reporte en PDF" type="button" onclick="generatePDFReportStock()">
                                    <i class="fa-solid fa-file-pdf white-text right"></i> PDF
                                </button>
                                <button class="btn green tooltipped btn-excel" id="btn-excel-MovVenta"
                                    data-tooltip="Reporte en Excel" type="button"
                                    onclick="document.getElementById('page').value=1;excel();">
                                    <i class="fa-solid fa-file-excel white-text right"></i> Excel
                                </button>
                            </div>
                        </div>
                    </div>


                    <!-- 🔹 Selector de registros por página y botones de exportación -->
                    <div class="row valign-wrapper" style="margin-bottom: 10px;">
                        <!-- Selector de registros -->
                        <div class="col s12 m8 left-align valign-wrapper">
                            <label for="records-per-page" style="margin-right: 10px;">Registros por página:</label>
                            <div class="input-field" style="margin: 0;">
                                <select id="records-per-page" class="browser-default" onchange="filterStock()">
                                    <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </div>
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
                                        <th class="no-hide">Cód.</th>
                                        <th class="no-hide">Descr.</th>
                                        <th>Tipo</th>
                                        <th>Categoría</th>
                                        <th>Unidad base</th>
                                        <th class="no-hide">Stock</th>
                                        <th>Situación</th>
                                        <th>P. compra</th>
                                        <th>P. venta</th>
                                        <th>Moneda</th>
                                        <th>Abreviatura</th>
                                        <th>Marca</th>
                                        <th>Peso</th>
                                        <th>Medida peso</th>
                                        <th>F. venc.</th>
                                        <th>Ubicación</th>
                                        <th>Col.</th>
                                        <th>Fil.</th>
                                        <th>Compuesto</th>
                                        <th>St. Mín.</th>
                                        <th>St. Máx.</th>
                                        <th>St. Ópt.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stockProductos as $stockProducto)
                                        <tr>
                                            <td>{{ $stockProducto->producto->codigo }}</td>
                                            <td>{{ $stockProducto->producto->descripcion }}</td>
                                            <td> <span style="color: black" class="new badge yellow"
                                                    data-badge-caption="{{ $stockProducto->producto->tipo }}">
                                                </span></td>
                                            <td>{{ $stockProducto->producto->categorium->descripcion }}</td>
                                            <td>{{ $stockProducto->unidad->descripcion }}</td>
                                            <td class="inpt-cantidad-mc">{{ intval($stockProducto->stockbase) }}</td>

                                            <td>
                                                <span
                                                    class="new badge {{ $stockProducto->producto->relProductosucursal->situacion == 'Activado' ? 'green' : 'red' }}"
                                                    data-badge-caption="{{ $stockProducto->producto->relProductosucursal->situacion }}"></span>
                                            </td>
                                            <td class="inpt-cantidad-mc">
                                                {{ isset($stockProducto->producto->relListaUnidadSucursal->preciocompra)
                                                    ? number_format($stockProducto->producto->relListaUnidadSucursal->preciocompra, 2)
                                                    : '' }}
                                            </td>
                                            <td class="inpt-cantidad-mc">
                                                {{ isset($stockProducto->producto->relListaUnidadSucursal->precioventa)
                                                    ? number_format($stockProducto->producto->relListaUnidadSucursal->precioventa, 2)
                                                    : '' }}
                                            </td>

                                            <td>{{ $stockProducto->producto->relListaUnidadSucursal->moneda ?? '' }}</td>
                                            <td>{{ $stockProducto->producto->abreviatura ?? '' }}</td>
                                            <td>{{ $stockProducto->producto->marca->descripcion ?? '' }}</td>
                                            <td class="inpt-cantidad-mc">
                                                {{ isset($stockProducto->producto->peso) ? number_format($stockProducto->producto->peso, 2) : '0.00' }}
                                            </td>

                                            <td>{{ $stockProducto->producto->unidad->descripcion ?? 'Gramo(s)' }}</td>
                                            <td>{{ $stockProducto->producto->relProductosucursal->fechavencimiento ?? '' }}
                                            </td>
                                            <td>{{ $stockProducto->producto->relProductosucursal->ubicacion->descripcion ?? '' }}
                                            </td>
                                            <td class="inpt-cantidad-mc ">
                                                {{ $stockProducto->producto->relProductosucursal->columna ?? 0 }}</td>
                                            <td class="inpt-cantidad-mc ">
                                                {{ $stockProducto->producto->relProductosucursal->fila ?? 0 }}</td>
                                            <td>{{ $stockProducto->producto->compuesto === 'S' ? 'Sí' : 'No' }}</td>
                                            <td class="inpt-cantidad-mc">
                                                {{ intval($stockProducto->producto->relProductosucursal->stockminimo ?? 0) }}
                                            </td>
                                            <td class="inpt-cantidad-mc">
                                                {{ intval($stockProducto->producto->relProductosucursal->stockmaximo ?? 0) }}
                                            </td>
                                            <td class="inpt-cantidad-mc">
                                                {{ intval($stockProducto->producto->relProductosucursal->stockoptimo ?? 0) }}
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $stockProductos->firstItem() }} a {{ $stockProductos->lastItem() }} de
                                    {{ $stockProductos->total() }} registros</p>
                            </div>

                            <div class="col s12 m6 right-align">
                                {{ $stockProductos->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
