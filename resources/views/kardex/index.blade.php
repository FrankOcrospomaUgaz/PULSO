@extends('layouts.app')

@section('template_title')
    Kardex de Productos
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">

                    <form method="GET" action="{{ route('kardex.index') }}" id="kardex-filters">
                    <!-- 🔹 Filtro de búsqueda y filtros adicionales -->
                    <div class="row valign-wrapper" id="filtros" style="margin-bottom: 10px; flex-wrap: wrap;">
                        <div class="row">
                            <div class="col s12 m12 l12">
                                <!-- Buscador -->
                                <div class="col s12 m4 left-align">
                                    <div class="input-field">
                                        <input type="text" id="search-input" name="search" value="{{ request('search') }}" class="validate"
                                            data-url-base="{{ route('kardex.index') }}">
                                        <label for="search-input">Buscar</label>
                                    </div>
                                </div>
                                <!-- Categoría -->
                                <div class="col s12 m2">
                                    <div class="input-field">
                                        <select id="categoria-filter" name="categoria_id">
                                            <option value="" disabled @selected(!request()->filled('categoria_id'))>Todas</option>
                                            @foreach ($categorias as $categoria)
                                                <option value="{{ $categoria->id }}"
                                                    {{ request('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                                    {{ $categoria->descripcion }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label>Categoría</label>
                                    </div>
                                </div>
                                <!-- Marca -->
                                <div class="col s12 m2">
                                    <div class="input-field">
                                        <select id="marca-filter" name="marca_id">
                                            <option value="" disabled @selected(!request()->filled('marca_id'))>Todas</option>
                                            @foreach ($marcas as $marca)
                                                <option value="{{ $marca->id }}"
                                                    {{ request('marca_id') == $marca->id ? 'selected' : '' }}>
                                                    {{ $marca->descripcion }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label>Marca</label>
                                    </div>
                                </div>
                                <!-- Fecha inicio -->
                                <div class="col s12 m2">
                                    <div class="input-field">
                                        <input type="date" id="fecha_inicio" name="fecha_inicio"
                                            value="{{ request('fecha_inicio') }}">
                                        <label for="fecha_inicio" class="active">Fecha inicio</label>
                                    </div>
                                </div>
                                <!-- Hora inicio -->
                                <div class="col s12 m2">
                                    <div class="input-field">
                                        <input type="time" id="hora_inicio" name="hora_inicio"
                                            value="{{ request('hora_inicio', '00:00') }}">
                                        <label for="hora_inicio">Hora inicio</label>
                                    </div>
                                </div>
                                <!-- Fecha fin -->
                                <div class="col s12 m2">
                                    <div class="input-field">
                                        <input type="date" id="fecha_fin" name="fecha_fin"
                                            value="{{ request('fecha_fin') }}">
                                        <label for="fecha_fin" class="active">Fecha fin</label>
                                    </div>
                                </div>
                                <!-- Hora fin -->
                                <div class="col s12 m2">
                                    <div class="input-field">
                                        <input type="time" id="hora_fin" name="hora_fin"
                                            value="{{ request('hora_fin', '23:59') }}">
                                        <label for="hora_fin">Hora fin</label>
                                    </div>
                                </div>
                                <!-- Tipo -->
                                <div class="col s12 m2">
                                    <div class="input-field">
                                        <select id="tipo-filter" name="tipo">
                                            <option value="" disabled @selected(!request()->filled('tipo'))>Todos</option>
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
                                <!-- Compuesto -->
                                <div class="col s12 m2">
                                    <div class="input-field">
                                        <select id="compuesto-filter" name="compuesto">
                                            <option value="" disabled @selected(!request()->filled('compuesto'))>Todos</option>
                                            <option value="S" {{ request('compuesto') === 'S' ? 'selected' : '' }}>Sí
                                            </option>
                                            <option value="N" {{ request('compuesto') === 'N' ? 'selected' : '' }}>No
                                            </option>
                                        </select>
                                        <label>Compuesto</label>
                                    </div>
                                </div>
                                <!-- Situación -->
                                <div class="col s12 m2">
                                    <div class="input-field">
                                        <select id="situacion-filter" name="situacion">
                                            <option value="" disabled @selected(!request()->filled('situacion'))>Todos</option>
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
                        </div>
                        <!-- Botones de exportación -->
                        <div class="col s12 m3 right-align">
                            <div class="input-field"
                                style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end;">
                                <button class="btn teal tooltipped btn-buscar" id="btn-Kardex" data-tooltip="Buscar"
                                    type="submit">
                                    <i class="fa-solid fa-magnifying-glass white-text"></i>
                                </button>
                                <a class="btn red tooltipped btn-pdf" id="btn-pdf-Kardex" data-tooltip="Reporte en PDF"
                                    href="{{ route('kardex.exportarPdf', request()->query()) }}">
                                    <i class="fa-solid fa-file-pdf white-text right"></i> PDF
                                </a>
                                <a class="btn green tooltipped btn-excel" id="btn-excel-Kardex"
                                    data-tooltip="Reporte en Excel" href="{{ route('kardex.exportarCsv', request()->query()) }}">
                                    <i class="fa-solid fa-file-excel white-text right"></i> Excel
                                </a>
                                <a class="btn purple tooltipped btn-ticket" id="btn-ticket-Kardex"
                                    data-tooltip="Resumen en ticket" href="{{ route('kardex.resumen_pdf') }}" target="_blank" rel="noopener">
                                    <i class="fa-solid fa-print white-text right"></i> Resumen
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 🔹 Selector de registros por página y botones extra -->
                    <div class="row valign-wrapper" style="margin-bottom: 10px;">
                        <!-- Selector de registros -->
                        <div class="col s12 m8 left-align valign-wrapper">
                            <label for="records-per-page" style="margin-right: 10px;">Registros por página:</label>
                            <div class="input-field" style="margin: 0;">
                                <select id="records-per-page" name="perPage" class="browser-default" onchange="this.form.submit()">
                                    <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </div>

                        <!-- Botones de expandir/filtros -->
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
                    </form>
 

                    <!-- Tabla -->
                    <div id="tb-container" class="table-wrapper">
                        <!-- Contenedor con scroll horizontal solo para la tabla -->
                        <div class="table-scroll" style="overflow-x: auto;">
                            <table id="data-table" class="highlight striped centered"
                                style="width: max-content; min-width: 100%;">
                                <thead class="thead-custom">
                                    <tr>
                                        <th class="no-hide">Producto</th>
                                        <th class="no-hide">Tipo</th>
                                        <th>Unidad</th>
                                        <th class="no-hide">Stock Anterior</th>
                                        <th class="no-hide">Cantidad</th>
                                        <th class="no-hide">Stock Actual</th>
                                        <th>P. Unitario</th>
                                        <th>Moneda</th>
                                        <th>Total</th>
                                        <th class="no-hide">Fecha</th>
                                        <th class="no-hide">Origen</th>
                                        <th>T. cambio</th>

                                        <th class="no-hide">Situación</th>



                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($kardex as $item)
                                        <tr>
                                            <td>{{ $item->producto->codigo ?? 'N/A' }} -
                                                {{ $item->producto->descripcion ?? 'N/A' }}</td>
                                            <td>
                                                <span
                                                    class="new badge {{ $item->movimiento?->tipodocumento?->tipomovimiento?->descripcion === 'Almacén' ? 'light-blue white-text' : 'yellow' }}"
                                                    data-badge-caption="{{ $item->movimiento?->tipodocumento?->nombre ?? 'Movimiento' }}"
                                                    style="color: black">
                                                </span>
                                            </td>

                                            <td>{{ $item->unidad?->descripcion ?? '—' }}</td>
                                            <td class="inpt-cantidad-mc "> {{ number_format($item->stockanterior, 0) }}
                                            </td>
                                            <td class="inpt-cantidad-mc ">{{ number_format($item->cantidad, 0) }}</td>
                                            <td class="inpt-cantidad-mc ">{{ number_format($item->stockactual, 0) }}</td>
                                            <td class="inpt-cantidad-mc ">{{ number_format($item->preciounitario, 2) }}
                                            </td>
                                            <td>{{ $item->moneda }}</td>
                                            <td class="inpt-cantidad-mc ">{{ number_format($item->total, 2) }}</td>

                                            <td>{{ $item->fecha }}</td>
                                            <td>
                                                {{ $item->movimiento?->tipodocumento?->tipomovimiento?->descripcion ?? '—' }} -
                                                @if ($item->movimiento?->movventa)
                                                    {{ $item->movimiento->movventa->serie }}-{{ $item->movimiento->numero }}-{{ $item->movimiento->movventa->anio }}
                                                @else
                                                    {{ $item->movimiento?->numero ?? '—' }}
                                                @endif
                                            </td>

                                            <td>{{ $item->tipocambio }}</td>

                                            <td>
                                                <span
                                                    class="new badge {{ $item->situacion === 'Activado' ? 'green' : 'red' }}"
                                                    data-badge-caption="{{ $item->situacion }}"></span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Paginación -->
                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $kardex->firstItem() }} a {{ $kardex->lastItem() }} de
                                    {{ $kardex->total() }} registros</p>
                            </div>
                            <div class="col s12 m6 right-align">
                                {{ $kardex->links() }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
