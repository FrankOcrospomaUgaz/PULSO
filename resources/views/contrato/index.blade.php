@extends('layouts.app')

@section('template_title')
    Contratos
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
                                        data-url-base="{{ route('contrato.index') }}">
                                </div>
                            </div>

                            <!-- Botón Nuevo -->
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
                                        <th>N° Contrato</th>
                                        <th>Inicio</th>
                                        <th>Fin</th>
                                        <th>Día Pago</th>
                                        <th>Monto</th>
                                        <th>Ambiente</th>
                                        <th>Arrendatario</th>
                                        <th>Arrendador</th>
                                        <th>Situación</th>
                                        <th>Operaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($contratos as $contrato)
                                        <tr>
                                            <td>{{ $contrato->numero_contrato }}</td>
                                            <td>{{ $contrato->fecha_inicio }}</td>
                                            <td>{{ $contrato->fecha_fin }}</td>
                                            <td>{{ $contrato->dia_pago }}</td>
                                            <td>S/. {{ number_format($contrato->monto, 2) }}</td>
                                            <td>{{ $contrato->ambiente->tipoambiente->nombre . ': ' . $contrato->ambiente->numero ?? '-' }}
                                            </td>
                                            <td>{{ $contrato->arrendatario->apellidos . ' ' . $contrato->arrendatario->nombres ?? '-' }}

                                            <td>{{ $contrato->arrendador->apellidos . ' ' . $contrato->arrendador->nombres ?? '-' }}
                                            </td>
                                          <td>
    <span class="new badge {{ [
        'Activo' => 'green',
        'Vencido' => 'orange',
        'Resuelto' => 'blue',
        'Cancelado' => 'red',
        'Suspendido' => 'purple'
    ][$contrato->situacion] ?? 'grey' }}"
    data-badge-caption="{{ $contrato->situacion }}">
    </span>
</td>


                                            <td>
                                                @foreach ($operaciones as $operacion)
                                                    @if ($operacion->tipo == 'Registro')
                                                        @if ($operacion->accion === 'contrato.edit')
                                                            <a href="{{ route($operacion->accion, $contrato->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-cargar-crud tooltipped"
                                                                data-icono="fa-solid fa-edit left"
                                                                data-titulo="{{ $operacion->descripcion }}"
                                                                data-tooltip="{{ $operacion->comentario }}">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                        @elseif ($operacion->accion === 'contrato.destroy')
                                                            <form action="{{ route($operacion->accion, $contrato->id) }}" method="POST"
                                                                class="delete-form" style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                    data-tooltip="{{ $operacion->descripcion }}">
                                                                    {!! $operacion->imagen !!}
                                                                </button>
                                                            </form>
                                                        @elseif ($operacion->accion === 'contrato.pdf')
                                                            <a href="{{ asset($contrato->pdfcontrato) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                data-icono="{{ strip_tags($operacion->imagen, '<i>') ? trim(str_replace(['<i class="', '"></i>'], '', $operacion->imagen)) : '' }}"
                                                                data-titulo="{{ $operacion->descripcion }}"
                                                                data-tooltip="{{ $operacion->comentario }}" target="_blank">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                        @elseif ($operacion->accion === 'contrato.renovar' && ($contrato->situacion === 'Resuelto' || $contrato->situacion === 'Vencido'))
                                                            <a href="{{ route($operacion->accion, $contrato->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-cargar-crud tooltipped"
                                                                data-icono="{{ strip_tags($operacion->imagen, '<i>') ? trim(str_replace(['<i class="', '"></i>'], '', $operacion->imagen)) : '' }}"
                                                                data-titulo="{{ $operacion->descripcion }}"
                                                                data-tooltip="{{ $operacion->comentario }}">
                                                                {!! $operacion->imagen !!}
                                                            </a>
    @elseif ($operacion->accion === 'contrato.anular')
    <form action="{{ route($operacion->accion, $contrato->id) }}" method="POST"
        class="form-accion-contrato"
        data-accion="anular"
        data-titulo="Atención"
        data-texto="¿Realmente desea anular el contrato?"
        data-confirmar="Anular"
        data-exito="¡Contrato anulado!"
        style="display: inline;">
        @csrf
        @method('DELETE')
        <button type="submit"
            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
            data-tooltip="{{ $operacion->descripcion }}">
            {!! $operacion->imagen !!}
        </button>
    </form>

@elseif ($operacion->accion === 'contrato.finalizar')
    <form action="{{ route($operacion->accion, $contrato->id) }}" method="POST"
        class="form-accion-contrato"
        data-accion="finalizar"
        data-titulo="Atención"
        data-texto="¿Realmente desea finalizar el contrato?"
        data-confirmar="Finalizar"
        data-exito="¡Contrato finalizado!"
        style="display: inline;">
        @csrf
        @method('DELETE')
        <button type="submit"
            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
            data-tooltip="{{ $operacion->comentario }}">
            {!! $operacion->imagen !!}
        </button>
    </form>


                                                        @elseif ($operacion->accion === 'contrato.pagos')
                                                            <a href="{{ route($operacion->accion, $contrato->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-cargar-crud tooltipped"
                                                                data-icono="{{ strip_tags($operacion->imagen, '<i>') ? trim(str_replace(['<i class="', '"></i>'], '', $operacion->imagen)) : '' }}"
                                                                data-titulo="{{ $operacion->descripcion }}"
                                                                data-tooltip="{{ $operacion->comentario }}">
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
                                    <p>Mostrando {{ $contratos->firstItem() }} a {{ $contratos->lastItem() }} de
                                        {{ $contratos->total() }} registros
                                    </p>
                                </div>
                                <div class="col s12 m6 right-align">
                                    {{ $contratos->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection