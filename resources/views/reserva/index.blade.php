@extends('layouts.app')

@section('template_title')
    Reserva
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
                                        data-url-base="{{ route('reserva.index') }}">
                                </div>
                            </div>

                            {{-- <!-- Filtro de Situación -->
                            <div class="col s12 m3">
                                <div class="input-field">
                                    <select id="situacion-filter" name="situacion" onchange="filterReservas()">
                                        <option value="">Seleccionar una situación</option>
                                        <option value="Pendiente" {{ request('situacion') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="Confirmada" {{ request('situacion') == 'Confirmada' ? 'selected' : '' }}>Confirmada</option>
                                        <option value="Cancelada" {{ request('situacion') == 'Cancelada' ? 'selected' : '' }}>Cancelada</option>
                                    </select>
                                    <label>Situación</label>
                                </div>
                            </div>

                            <!-- Filtro de Habitación -->
                            <div class="col s12 m3">
                                <div class="input-field">
                                    <select id="habitacion-filter" name="habitacion" onchange="filterReservas()">
                                        <option value="">Seleccionar una habitación</option>
                                        @foreach ($habitaciones as $habitacion)
                                            <option value="{{ $habitacion->id }}"
                                                {{ request('habitacion') == $habitacion->id ? 'selected' : '' }}>
                                                {{ $habitacion->numero }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label>Habitación</label>
                                </div>
                            </div> --}}

                            <!-- Botón Nuevo -->
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

                        <!-- Controles de exportación y paginación -->
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

                    <div id="tb-container" class="table-responsive">
                        <table id="data-table" class="highlight striped centered nowrap" style="width:100%">
                            <thead class="thead-custom">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Fecha Salida</th>
                                    <th>Situación</th>
                                    <th>Habitación</th>
                                    <th>Cliente</th>
                                    <th>Operaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reservas as $reserva)
                                    <tr>
                                        <td>{{ $reserva->fecha }}</td>
                                        <td>{{ $reserva->fechasalida }}</td>
                                        <td>
                                            <span 
                                                class="new badge 
                                                    {{ $reserva->situacion == 'Pendiente' ? 'orange' : '' }}
                                                    {{ $reserva->situacion == 'Confirmada' ? 'green' : '' }}
                                                    {{ $reserva->situacion == 'Cancelada' ? 'red' : '' }}"
                                                data-badge-caption="{{ $reserva->situacion }}">
                                            </span>
                                        </td>
                                        <td>{{ $reserva->habitacion->numero }}</td>
                                        <td>{{ $reserva->persona->nombre }}</td>

                                        <td>
                                            @foreach ($operaciones as $operacion)
                                                @if ($operacion->tipo == 'Registro')
                                                    @if ($operacion->accion === 'reserva.destroy')
                                                        <form action="{{ route($operacion->accion, $reserva->id) }}"
                                                            class="delete-form" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </button>
                                                        </form>
                                                    @elseif($operacion->accion == 'reserva.edit')
                                                        <a href="{{ route($operacion->accion, $reserva->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-crear "
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
                                <p>Mostrando {{ $reservas->firstItem() }} a {{ $reservas->lastItem() }} de
                                    {{ $reservas->total() }} registros</p>
                            </div>

                            <!-- Paginación -->
                            <div class="col s12 m6 right-align">
                                {{ $reservas->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
