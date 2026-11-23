@extends('layouts.app')

@section('template_title')
    Paquetes del salón: {{ $salon->titulo }}
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
                                    <input type="text" id="search-input" class="validate" placeholder="Buscar...">
                                </div>
                            </div>

                            <!-- Botón "Nuevo" según operaciones -->
                            <div class="col s12 m6 right-align">
                                @foreach ($operaciones as $operacion)
                                    @if ($operacion->tipo === 'Tabla')
                                        <a href="{{ route($operacion->accion, $salon->id) }}"
                                            class="btn waves-effect waves-light btn-crear {{ $operacion->color }}"
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
                            <table class="highlight striped centered nowrap" id="data-table"  style="width:100%">
    <thead class="thead-custom">
        <tr>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Contenido</th>
            <th>Imagen</th> <!-- Nueva columna -->
            <th>Operaciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($paquetes as $paquete)
            <tr>
                <td>{{ $paquete->nombre }}</td>
                <td>{{ $paquete->precio }}</td>
                <td>{{ $paquete->content }}</td>
                <td>
                    @if ($paquete->imagen)
                        <img src="{{ asset($paquete->imagen) }}" alt="Imagen" style="max-width: 100px; max-height: 80px; object-fit: contain;">
                    @else
                        <img src="https://via.placeholder.com/100x80?text=Sin+imagen" alt="Sin imagen" style="max-width: 100px; max-height: 80px; object-fit: contain;">
                    @endif
                </td>
                <td>
                    @foreach ($operaciones as $operacion)
                        @if ($operacion->tipo === 'Registro')
                            @if ($operacion->accion === 'salon.paquetes.edit')
                                <a href="{{ route($operacion->accion, [$salon->id, $paquete->id]) }}"
                                    class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-crear"
                                    data-tooltip="{{ $operacion->descripcion }}">
                                    {!! $operacion->imagen !!}
                                </a>
                            @elseif ($operacion->accion === 'salon.paquetes.destroy')
                                <form action="{{ route($operacion->accion, [$salon->id, $paquete->id]) }}"
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

                        {{-- <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $paquetes->firstItem() }} a {{ $paquetes->lastItem() }} de 
                                    {{ $paquetes->total() }} registros</p>
                            </div>
                            <div class="col s12 m6 right-align">
                                {{ $paquetes->links() }}
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
