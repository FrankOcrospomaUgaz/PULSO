@extends('layouts.app')

@section('template_title')
    Plantillas de Contrato
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
                                        data-url-base="{{ route('plantilla_contrato.index') }}">
                                </div>
                            </div>

                            <!-- Botón Crear -->
                            <div class="col s12 m6 right-align">
                                @foreach ($operaciones as $operacion)
                                    @if ($operacion->tipo == 'Tabla')
                                        <a href="{{ route($operacion->accion) }}"
                                            class="btn waves-effect waves-light btn-cargar-crud {{ $operacion->color }}"
                                             data-icono="fa-solid fa-plus left" data-titulo="{{ $operacion->descripcion }}"
                                            data-tooltip="{{ $operacion->comentario }}" style="border-radius: 10px;">
                                            {!! $operacion->imagen !!} {{ $operacion->descripcion }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- Mensaje de éxito -->
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
                                        <th>Título</th>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Operaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($plantillaContratos as $plantilla)
                                        <tr>
                                            <td>{{ $plantilla->titulo }}</td>
                                            <td>{{ $plantilla->tipoambiente->nombre }}</td>
                                            <td>{{ Str::limit($plantilla->descripcion, 40) }}</td>
                                            <td>
                                                <span class="new badge {{ $plantilla->deleted_at ? 'red' : 'green' }}"
                                                    data-badge-caption="{{ $plantilla->deleted_at ? 'Inactivo' : 'Activo' }}">
                                                </span>
                                            </td>
                                            <td>
                                                @foreach ($operaciones as $op)
                                                    @if ($op->tipo == 'Registro')
                                                        @if ($op->accion === 'plantilla_contrato.edit')
                                                            <a href="{{ route($op->accion, $plantilla->id) }}"
                                                                class="btn-floating {{ $op->color }} tooltipped btn-cargar-crud"
                                                                 data-icono="fa-solid fa-edit left"
                                                                data-titulo="{{ $op->descripcion }}"
                                                                data-tooltip="{{ $op->comentario }}">
                                                                {!! $op->imagen !!}
                                                            </a>
                                                        @elseif ($op->accion === 'plantilla_contrato.destroy')
                                                            <form action="{{ route($op->accion, $plantilla->id) }}"
                                                                method="POST" class="delete-form" style="display:inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn-floating {{ $op->color }} tooltipped"
                                                                    data-tooltip="{{ $op->descripcion }}">
                                                                    {!! $op->imagen !!}
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
                                    <p>
                                        Mostrando {{ $plantillaContratos->firstItem() }} a {{ $plantillaContratos->lastItem() }}
                                        de {{ $plantillaContratos->total() }} registros
                                    </p>
                                </div>
                                <div class="col s12 m6 right-align">
                                    {{ $plantillaContratos->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection
