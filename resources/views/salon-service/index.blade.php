@extends('layouts.app')

@section('template_title')
    Servicios del salón: {{ $salon->titulo }}
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
                    <div class="card-content" style="margin-top: -20px;">
                        <div class="row valign-wrapper" style="margin-bottom: 10px;">
                            <!-- Buscador -->
                            <div class="col s12 m6 left-align">
                                <div class="input-field" style="margin: 0;">
                                    <i class="material-icons prefix">search</i>
                                    <input type="text" id="search-input" class="validate" placeholder="Buscar...">
                                </div>
                            </div>

                            <!-- Botón "Nuevo" según operaciones -->
                            <div class="col s12 m6 right-align">
                                @foreach ($operaciones as $operacion)
                                    @if ($operacion->tipo === 'Tabla')
                                        <a href="{{ route($operacion->accion, $salon->id) }}"
                                            class="btn waves-effect waves-light {{ $operacion->color }} btn-crear"
                                            style="border-radius: 10px;">
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
                            <table class="highlight striped centered nowrap data-table" style="width:100%">
                                <thead class="thead-custom">
                                    <tr>
                                        <th>Título</th>
                                        <th>Descripción</th>
                                        <th>Ícono</th>
                                        <th>Operaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($servicios as $servicio)
                                        <tr>
                                            <td>{{ $servicio->titulo }}</td>
                                            <td>{{ $servicio->descripcion }}</td>
                                            <td>{{ $servicio->icono }}</td>
                                            <td>
                                                @foreach ($operaciones as $operacion)
                                                    @if ($operacion->tipo === 'Registro')
                                                        @if ($operacion->accion === 'salon.servicios.edit')
                                                            <a href="{{ route($operacion->accion, [$salon->id, $servicio->id]) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-crear"
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                        @elseif ($operacion->accion === 'salon.servicios.destroy')
                                                            <form action="{{ route($operacion->accion, [$salon->id, $servicio->id]) }}"
                                                                class="delete-form" method="POST" style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                    data-tooltip="{{ $operacion->descripcion }}">
                                                                    {!! $operacion->imagen !!}
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Puedes reactivar la paginación si la necesitas --}}
                        {{-- 
                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $servicios->firstItem() }} a {{ $servicios->lastItem() }} de {{ $servicios->total() }} registros</p>
                            </div>
                            <div class="col s12 m6 right-align">
                                {{ $servicios->links() }}
                            </div>
                        </div> 
                        --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
