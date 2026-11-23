@extends('layouts.app')



@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
                    <div class="card-content" style="margin-top: -20px;">
                        <div class="row valign-wrapper" style="margin-bottom: 10px;">
                            <!-- Buscador -->
                            <div class="col s12 m6">
                                <div class="input-field">
                                    <i class="material-icons prefix">search</i>
                                    <input type="text" id="search-input" class="validate" placeholder="Buscar..." data-url-base="{{ route('pregunta.index') }}">
                                </div>
                            </div>

                   


                            <!-- Botón Nuevo -->
                            <div class="col s12 m6 right-align">
                                @foreach ($operaciones as $operacion)
                                    @if ($operacion->tipo == 'Tabla')
                                        <a href="{{ route($operacion->accion, $id) }}"
                                            class="btn waves-effect waves-light btn-crear {{ $operacion->color }}"
                                            style="border-radius: 10px;">
                                            {!! $operacion->imagen !!} {{ $operacion->descripcion }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- Tabla de Detalles de Encuestas -->
                        <div id="tb-container" class="table-responsive">
                            <table id="data-table" class="highlight striped centered nowrap" style="width:100%">
                                <thead class="thead-custom">
                                    <tr>
                                        <th>Enunciado</th>
                                        {{-- <th>Orden</th> --}}
                                        <th>Obligatorio</th>
                                        <th>Opciones</th>
                                        <th>Categoría</th>
                                        <th>Tipo</th>
                                        <th>Situación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($detEncuestas as $detalle)
                                        <tr>
                                            <td>{{ $detalle->enunciado }}</td>
                                            {{-- <td>{{ $detalle->orden }}</td> --}}
                                            <td>{{ $detalle->obligatorio ? 'Sí' : 'No' }}</td>
                                            @php
                                            $opciones = json_decode($detalle->opciones, true) ?? [];
                                        
                                            // Verificar si son solo números y consecutivos
                                            if (!empty($opciones) && ctype_digit(implode('', $opciones))) {
                                                $numeros = array_map('intval', $opciones);
                                                sort($numeros);
                                        
                                                // Si la lista es una secuencia continua, mostrar como "X al Y"
                                                $esRango = ($numeros == range(min($numeros), max($numeros)));
                                                $texto = $esRango ? min($numeros) . ' al ' . max($numeros) : implode('<br>', $numeros);
                                            } else {
                                                $texto = implode('<br>', $opciones);
                                            }
                                        @endphp
                                        
                                        <td>{!! $texto !!}</td>
                                        
                                            <td>{{ $detalle->categoriapregunta }}</td>
                                            <td>{{ $detalle->tipopregunta->nombre }}</td>
                                            <td>
                                                <span class="new badge {{ $detalle->situacion == 'Activado' ? 'green' : 'red' }}"
                                                    data-badge-caption="{{ $detalle->situacion }}">
                                                </span>
                                            </td>
                                            <td>
                                                @foreach ($operaciones as $operacion)
                                                    @if ($operacion->tipo == 'Registro')
                                                        @if ($operacion->accion === 'pregunta.destroy')
                                                            <form action="{{ route($operacion->accion, $detalle->id) }}"
                                                                class="delete-form" method="POST" style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                    data-tooltip="{{ $operacion->descripcion }}">
                                                                    {!! $operacion->imagen !!}
                                                                </button>
                                                            </form>
                                                        @elseif($operacion->accion == 'pregunta.edit')
                                                            <a href="{{ route($operacion->accion, $detalle->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-crear "
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                            @else
                                                            <a href="{{ route($operacion->accion, $detalle->id) }}"
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

                            <!-- Paginación -->
                            <div class="row">
                                <div class="col s12 m6">
                                    <p>Mostrando {{ $detEncuestas->firstItem() }} a {{ $detEncuestas->lastItem() }} de {{ $detEncuestas->total() }} registros</p>
                                </div>
                                <div class="col s12 m6 right-align">
                                    {{ $detEncuestas->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
