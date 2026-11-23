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
                                        <a href="{{ route($operacion->accion) }}"
                                            class="btn waves-effect waves-light btn-crear {{ $operacion->color }}"
                                            style="border-radius: 10px;">
                                            {!! $operacion->imagen !!} {{ $operacion->comentario }}
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
                                        <th>Nombre</th>
                                        <th>Situación</th>
                                        <th>Operaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($categoriapreguntas as $categoriapregunta)
                                        <tr>
                                            <td>{{ $categoriapregunta->nombre }}</td>
                                            <td>
                                                <span class="new badge {{ $categoriapregunta->situacion == 'Activado' ? 'green' : 'red' }}"
                                                    data-badge-caption="{{ $categoriapregunta->situacion }}">
                                                </span>
                                            </td>
                                            <td>
                                                @foreach ($operaciones as $operacion)
                                                @if ($operacion->tipo == 'Registro')
                                                    @if ($operacion->accion === 'categoriapregunta.destroy')
                                                        <form action="{{ route($operacion->accion, $categoriapregunta->id) }}"
                                                            class="delete-form" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </button>
                                                        </form>
                                                    @elseif($operacion->accion == 'categoriapregunta.edit')
                                                        <a href="{{ route($operacion->accion, $categoriapregunta->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-crear tooltipped "
                                                            data-tooltip="{{ $operacion->descripcion }}">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                   
                                                    @elseif ($categoriapregunta->situacion == 'Desactivado' && $operacion->accion === 'categoriapregunta.activar')
                                                        <a href="{{ route($operacion->accion, $categoriapregunta->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-activar"
                                                            data-tooltip="{{ $operacion->descripcion }}">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @elseif ($categoriapregunta->situacion == 'Activado' && $operacion->accion === 'categoriapregunta.desactivar')
                                                        <a href="{{ route($operacion->accion, $categoriapregunta->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-activar"
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
                                    <p>Mostrando {{ $categoriapreguntas->firstItem() }} a {{ $categoriapreguntas->lastItem() }} de {{ $categoriapreguntas->total() }} registros</p>
                                </div>
                                <div class="col s12 m6 right-align">
                                    {{ $categoriapreguntas->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
