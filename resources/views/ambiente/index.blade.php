@extends('layouts.app')

@section('template_title')
    Ambientes
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
                                    <input type="text" id="search-input" class="validate" placeholder="Buscar..."
                                        data-url-base="{{ route('ambiente.index') }}">
                                </div>
                            </div>

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

                        @if ($message = Session::get('success'))
                            <div class="card-panel green lighten-4 green-text text-darken-4">
                                <p>{{ $message }}</p>
                            </div>
                        @endif

                        <!-- Tabla -->
                        <div id="tb-container" class="table-responsive">
                            <table id="data-table" class="highlight striped centered nowrap" style="width:100%">
                                <thead class="thead-custom">
                                    <tr>
                                        <th>Número</th>
                                        <th>Tipo</th>
                                        <th>Monto</th>
                                        <th>Garantía</th>
                                        <th>Edificio</th>
                                        <th>Piso</th>
                                        <th>Situación</th>
                                        <th>Operaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ambientes as $ambiente)
                                        <tr>
                                            <td>{{ $ambiente->numero }}</td>
                                            <td>{{ ucfirst($ambiente->tipoambiente->nombre) }}</td>
                                            <td>S/ {{ number_format($ambiente->monto, 2) }}</td>
                                            <td>S/ {{ number_format($ambiente->garantia, 2) }}</td>
                                            <td>{{ $ambiente->edificio->nombre ?? '—' }}</td>
                                            <td>{{ $ambiente->piso->nombre ?? '—' }}</td>
                                            <td>
    <span class="new badge
        {{ $ambiente->situacion === 'Libre' ? 'green' :
           ($ambiente->situacion === 'Alquilado' ? 'yellow' :
           ($ambiente->situacion === 'Mantenimiento' ? 'red' : 'grey')) }}"
        style="{{ $ambiente->situacion === 'Alquilado' ? 'color: black !important;' : '' }}"
        data-badge-caption="{{ $ambiente->situacion }}">
    </span>
</td>

                                            <td>
                                                @foreach ($operaciones as $operacion)
                                                    @if ($operacion->tipo == 'Registro')
                                                        @if ($operacion->accion === 'ambiente.edit')
                                                            <a href="{{ route($operacion->accion, $ambiente->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-crear tooltipped"
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                        @elseif ($operacion->accion === 'ambiente.destroy')
                                                            <form action="{{ route($operacion->accion, $ambiente->id) }}" method="POST"
                                                                class="delete-form" style="display: inline;">
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

                            <!-- Paginación -->
                            <div class="row">
                                <div class="col s12 m6">
                                    <p>Mostrando {{ $ambientes->firstItem() }} a {{ $ambientes->lastItem() }} de
                                        {{ $ambientes->total() }} registros
                                    </p>
                                </div>
                                <div class="col s12 m6 right-align">
                                    {{ $ambientes->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection