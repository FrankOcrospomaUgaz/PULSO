@extends('layouts.app')

@section('template_title')
    Housekeeping
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
                                        data-url-base="{{ route('housekeeping.index') }}">
                                </div>
                            </div>

                            <!-- Filtro de Estado -->
                            <div class="col s12 m3">
                                <div class="input-field">
                                    <select id="estado-filter" name="estado" onchange="filterHousekeeping()">
                                        <option value="">Seleccionar estado</option>
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="En progreso">En progreso</option>
                                        <option value="Completado">Completado</option>
                                    </select>
                                    <label>Estado</label>
                                </div>
                            </div>

                            <!-- Botón de Nuevo Registro -->
                            <div class="col s12 m3 right-align">
                                @foreach ($operaciones as $operacion)
                                    @if ($operacion->tipo == 'Tabla')
                                        <a href="{{ route($operacion->accion) }}"
                                            class="btn waves-effect waves-light btn-cargar-crud {{ $operacion->color }}"
                                            data-tooltip="{{ $operacion->descripcion }}"
                                            data-icono="fa-solid fa-landmark left"
                                            data-titulo="{{$operacion->comentario }}">
                                            {!! $operacion->imagen !!} {{ $operacion->descripcion }}
                                        </a>
                                    @endif
                                @endforeach
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
                                        <th>N°</th>
                                        <th>Habitaciones</th>
                                        <th>Fecha Programada</th>
                                        <th>Tiempo Total</th>
                                        <th>Estado</th>
                                        <th>Operaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($housekeeping as $registro)
                                        <tr>
                                            <td>{{ $registro->id }}</td>
                                            <!-- Habitaciones -->
                                            <td>
                                                @foreach ($registro->housekeepingDetalles as $detalle)
                                                    <span class="badge blue white-text" style="margin: 2px;">
                                                        {{ $detalle->habitacion->numero ?? 'N/A' }}
                                                    </span>
                                                @endforeach
                                            </td>
                            
                                            <td>{{ $registro->fecha_programada }}</td>
                                            <td>{{ $registro->tiempo_total }}</td>
                            
                                            <td>
                                                <span class="new badge 
                                                    {{ $registro->estado == 'Pendiente' ? 'orange' : '' }}
                                                    {{ $registro->estado == 'En progreso' ? 'blue' : '' }}
                                                    {{ $registro->estado == 'Completado' ? 'green' : '' }}"
                                                    data-badge-caption="{{ $registro->estado }}">
                                                </span>
                                            </td>
                                                        
                                            <td>
    @foreach ($operaciones as $operacion)
        @if ($operacion->tipo == 'Registro')
            @if ($operacion->accion === 'housekeeping.destroy')
                <form action="{{ route($operacion->accion, $registro->id) }}"
                    class="delete-form" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                        data-tooltip="{{ $operacion->descripcion }}">
                        {!! $operacion->imagen !!}
                    </button>
                </form>

            @elseif($operacion->accion == 'housekeeping.edit' &&  $registro->estado == 'Pendiente')
                <a href="{{ route($operacion->accion, $registro->id) }}"
                    class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                    data-tooltip="{{ $operacion->descripcion }}"
                    data-icono="fa-solid fa-landmark left"
                    data-titulo="{{ $operacion->comentario }}">
                    {!! $operacion->imagen !!}
                </a>

            @elseif($operacion->accion == 'housekeeping.progreso' &&  $registro->estado !== 'Pendiente')
                <a href="{{ route($operacion->accion, $registro->id) }}"
                    class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-crear"
                    data-tooltip="{{ $operacion->descripcion }}">
                    {!! $operacion->imagen !!}
                </a>

            @elseif($operacion->accion == 'housekeeping.asignar')
                @php
                    $detalle = \App\Models\HousekeepingDetalle::where('jornada_id', $registro->id)->first();
                @endphp

                @if($detalle && is_null($detalle->empleado_id))
                    <a href="{{ route($operacion->accion, $registro->id) }}"
                        class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-crear"
                        data-tooltip="{{ $operacion->descripcion }}">
                        {!! $operacion->imagen !!}
                    </a>
                @endif
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
                                    <p>Mostrando {{ $housekeeping->firstItem() }} a {{ $housekeeping->lastItem() }} de
                                        {{ $housekeeping->total() }} registros</p>
                                </div>

                                <div class="col s12 m6 right-align">
                                    {{ $housekeeping->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
