@extends('layouts.app')

@section('template_title')
    Salon
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
                    <div class="card-content" style="margin-top: -20px;">
                        <div class="row valign-wrapper" style="margin-bottom: 10px;">
                            <!-- Columna del buscador -->
                            <div class="col s12 m6 left-align">
                                <div class="input-field" style="margin: 0;">
                                    <i class="material-icons prefix">search</i>
                                    <input type="text" id="search-input" class="validate" placeholder="Buscar..."
                                        data-url-base="{{ route('salon.index') }}">
                                </div>
                            </div>

                            <!-- Filtro de Situación -->
                            <div class="col s12 m3">
                                <div class="input-field">
                                    <select id="situacion-filter" name="situacion" onchange="filterSalones()">
                                        <option value="">Seleccionar una situacion</option>
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

                            <!-- Columna del botón Nuevo -->
                            <div class="col s12 m6 right-align">
                                @foreach ($operaciones as $operacion)
                                    @if ($operacion->tipo == 'Tabla')
                                        <a href="{{ route($operacion->accion) }}"
                                            class="btn waves-effect waves-light btn-crear {{ $operacion->color }}"
                                            style="border-radius: 10px;">
                                            {!! $operacion->imagen !!} {{ $operacion->descripcion }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- Contenedor del selector de registros por página y botones de exportación -->
                        <div class="row valign-wrapper" style="margin-bottom: 10px;">
                            <div class="col s12 m6 left-align valign-wrapper">
                                <label for="records-per-page" style="margin-right: 10px;">Registros por página:</label>
                                <div class="input-field" style="margin: 0;">
                                    <select id="records-per-page" class="browser-default">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Forzamos la alineación de los botones de exportación a la derecha -->
                            <div class="col s12 m6" style="display: flex; justify-content: flex-end;">
                                <div class="dt-buttons btn-group" style="display: flex; gap: 10px;">
                                    <button class="btn waves-effect waves-light grey" id="export-copy">
                                        <i class="material-icons">content_copy</i>
                                    </button>
                                    <button class="btn waves-effect waves-light green" id="export-excel">
                                        <i class="material-icons">grid_on</i>
                                    </button>
                                    <button class="btn waves-effect waves-light orange" id="export-csv">
                                        <i class="material-icons">table_chart</i>
                                    </button>
                                    <button class="btn waves-effect waves-light red" id="export-pdf">
                                        <i class="material-icons">picture_as_pdf</i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($message = Session::get('success'))
                        <div class="card-panel green lighten-4 green-text text-darken-4">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    <!-- Tabla de datos -->
                    <div id="tb-container" class="table-responsive" style="overflow-x: auto;">
                        <table id="data-table" class="highlight striped centered nowrap data-table" >

                            <thead class="thead-custom">
                                <tr>
                                    <th>Título</th>
                                    <th>Imagen</th>
                                    <th>Descripción corta</th>
                                    <th>Descripción larga</th>
                                    <th>Situación</th>
                                    <th>Operaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($salones as $salon)
                                    <tr>
                                        <td>{{ $salon->titulo }}</td>
                                        <td>
                                            @if ($salon->imagen)
                                                <img src="{{ asset($salon->imagen) }}" alt="Imagen" style="max-width: 100px; max-height: 80px; object-fit: contain;">
                                            @else
                                                <img src="https://via.placeholder.com/100x80?text=Sin+imagen" alt="Sin imagen" style="max-width: 100px; max-height: 80px; object-fit: contain;">
                                            @endif
                                        </td>
                                        <td>{{ $salon->descripcion_corta }}</td>
                                        <td>{{ $salon->descripcion_larga }}</td>
                                        <td>
                                            <span
                                                class="new badge 
                                                    {{ $salon->situacion == 'A' ? 'green' : '' }}
                                                    {{ $salon->situacion == 'D' ? 'red' : '' }}"
                                                data-badge-caption="{{ $salon->situacion == 'A' ? 'Activo' : 'Desactivado' }}">
                                            </span>
                                        </td>

                                        <td>
                                            @foreach ($operaciones as $operacion)
                                                @if ($operacion->tipo == 'Registro')
                                                    @if ($operacion->accion === 'salon.edit')
                                                        <a href="{{ route($operacion->accion, $salon->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-crear tooltipped"
                                                            data-tooltip="{{ $operacion->descripcion }}">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @elseif ($operacion->accion === 'salon.destroy')
                                                        <form action="{{ route($operacion->accion, $salon->id) }}"
                                                            class="delete-form" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </button>
                                                        </form>
                                                    @elseif ($operacion->accion === 'salon.servicios')
                                                        <a href="{{ route($operacion->accion, $salon->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-cargar-crud tooltipped"
                                                            data-tooltip="Ver servicios del salón"
                                                            data-titulo="{{ $operacion->descripcion }} de {{ $salon->titulo }}"
                                                            data-icono="{{ strip_tags($operacion->imagen, '<i>') ? trim(str_replace(['<i class="', '"></i>'], '', $operacion->imagen)) : '' }}">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @elseif ($operacion->accion === 'salon.paquetes')
                                                        <a href="{{ route($operacion->accion, $salon->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-cargar-crud tooltipped"
                                                            data-tooltip="Ver paquetes del salón"
                                                            data-titulo="{{ $operacion->descripcion }} de {{ $salon->titulo }}"
                                                            data-icono="{{ strip_tags($operacion->imagen, '<i>') ? trim(str_replace(['<i class="', '"></i>'], '', $operacion->imagen)) : '' }}">
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
                                <p>Mostrando {{ $salones->firstItem() }} a {{ $salones->lastItem() }} de
                                    {{ $salones->total() }} registros</p>
                            </div>

                            <!-- Mostrar la paginación de Laravel -->
                            <div class="col s12 m6 right-align">
                                {{ $salones->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
