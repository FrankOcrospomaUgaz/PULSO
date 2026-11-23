@extends('layouts.app')

@section('template_title')
    Encuestas
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
                                        data-url-base="{{ route('encuesta.index') }}">
                                </div>
                            </div>

                       

                            <!-- Columna del botón Nuevo -->
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

                            <!-- Botones de exportación -->
                            <div class="col s12 m6" style="display: flex; justify-content: flex-end;">
                               
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
                                    <th>Código QR</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Fecha</th>
                                    <th>Fecha Fin</th>
                                    <th>Mensaje final</th>
                                    <th>Situación</th>
                                    <th>Estado</th>
                                    <th>Operaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($encuestas as $encuesta)
                                    <tr>
                                        <td>
                                            {!! QrCode::size(100)->generate('https://hotel-hub-encuestas.vercel.app/' . $encuesta->uuid) !!}
                                        </td>
                                        <td style="min-width: 50px; word-wrap: break-word; white-space: normal;"">{{ $encuesta->nombre }}</td>
                                        <td style="min-width: 220px; word-wrap: break-word; white-space: normal;">
                                            {{ $encuesta->descripcion }}</td>
                                       
                                        <td>
                                            {{ \Carbon\Carbon::parse($encuesta->fecha)->format('Y-m-d') }}<br>
                                            {{ \Carbon\Carbon::parse($encuesta->fecha)->format('h:i:s A') }}
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($encuesta->fechavencimiento)->format('Y-m-d') }}<br>
                                            {{ \Carbon\Carbon::parse($encuesta->fechavencimiento)->format('h:i:s A') }}
                                        </td>
                                        <td style="min-width: 180px; word-wrap: break-word; white-space: normal;">
                                            {{ $encuesta->mensaje_finalizacion }}</td>
                                        <td>
                                            <span class="new badge {{ $encuesta->estado == 'VIGENTE' ? 'green' : 'blue' }}"
                                                data-badge-caption="{{ ucfirst(strtolower($encuesta->estado)) }}">
                                            </span>
                                        </td>


                                        <td>
                                            <span
                                                class="new badge {{ $encuesta->situacion == 'Activado' ? 'green' : 'red' }}"
                                                data-badge-caption="{{ $encuesta->situacion }}">
                                            </span>
                                        </td>

                                        <td>
                                            @foreach ($operaciones as $operacion)
                                                @if ($operacion->tipo == 'Registro')
                                                    @if ($operacion->accion === 'encuesta.destroy')
                                                        <form action="{{ route($operacion->accion, $encuesta->id) }}"
                                                            class="delete-form" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </button>
                                                        </form>
                                                    @elseif($operacion->accion == 'encuesta.edit')
                                                        <a href="{{ route($operacion->accion, $encuesta->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-crear tooltipped "
                                                            data-tooltip="{{ $operacion->descripcion }}">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @elseif($operacion->accion == 'encuesta.preguntas')
                                                        <a href="{{ route($operacion->accion, $encuesta->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-cargar-crud tooltipped "
                                                            data-tooltip="{{ $operacion->descripcion }}"
                                                            data-icono="{{ $operacion->imagen }}"
                                                            data-titulo="{{ $operacion->comentario }} - {{ $encuesta->nombre }}">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @elseif($operacion->accion == 'encuesta.estadisticas')
                                                        <a href="{{ route($operacion->accion, $encuesta->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-cargar-crud tooltipped "
                                                            data-tooltip="{{ $operacion->descripcion }}"
                                                            data-icono="{{ $operacion->imagen }}"
                                                            data-titulo="{{ $operacion->comentario }} - {{ $encuesta->nombre }}">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @elseif($operacion->accion == 'encuesta.respuestas')
                                                        <a href="{{ route($operacion->accion, $encuesta->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-cargar-crud tooltipped "
                                                            data-tooltip="{{ $operacion->descripcion }}"
                                                            data-icono="{{ $operacion->imagen }}"
                                                            data-titulo="{{ $operacion->comentario }} - {{ $encuesta->nombre }}">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @elseif ($encuesta->situacion == 'Desactivado' && $operacion->accion === 'encuesta.activar')
                                                        <a href="{{ route($operacion->accion, $encuesta->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-activar"
                                                            data-tooltip="{{ $operacion->descripcion }}">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @elseif ($encuesta->situacion == 'Activado' && $operacion->accion === 'encuesta.desactivar')
                                                        <a href="{{ route($operacion->accion, $encuesta->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-activar"
                                                            data-tooltip="{{ $operacion->descripcion }}">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @elseif ($operacion->accion === 'encuesta.copiar')
                                                        <a href="javascript:void(0);"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-copiar"
                                                            data-tooltip="{{ $operacion->descripcion }}"
                                                            onclick="copiarUrlQr('{{ 'https://hotel-hub-encuestas.vercel.app/' . $encuesta->uuid }}')">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @elseif ($operacion->accion === 'encuesta.finalizar' && $encuesta->estado == 'VIGENTE')
                                                        <form action="{{ route($operacion->accion, $encuesta->id) }}"
                                                            class="finalizar-form" method="POST"
                                                            style="display: inline;">
                                                            @csrf
                                                            @method('GET')
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

                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $encuestas->firstItem() }} a {{ $encuestas->lastItem() }} de
                                    {{ $encuestas->total() }} registros</p>
                            </div>

                            <!-- Mostrar la paginación de Laravel -->
                            <div class="col s12 m6 right-align">
                                {{ $encuestas->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
