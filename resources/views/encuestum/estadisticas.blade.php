@extends('layouts.app')

@section('template_title')
    Estadísticas de Encuesta
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
                    <div class="card-content">
                        <div class="row without-bottom">
                            <!-- Filtros de búsqueda -->
                            <div class="col s12">
                                <div class="card minicard">
                                    <form id="frmMantRespuesta">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}" autocomplete="off">
                                        <div class="card-content">
                                            <span class="card-title"><i class="fa-solid fa-magnifying-glass left"></i>
                                                Filtros de búsqueda</span>
                                            <div class="row without-tb">
                                                <div class="input-field col s12 m6 l2">
                                                    <input id="fecha_inicio" name="fecha_inicio" type="date"
                                                        value="{{ request('fecha_inicio') }}">
                                                    <label for="fecha_inicio">Fecha de inicio</label>
                                                </div>
                                                <div class="input-field col s12 m6 l2">
                                                    <input id="hora_inicio" name="hora_inicio" type="time"
                                                        value="{{ request('hora_inicio') }}">
                                                    <label for="hora_inicio">Hora de inicio</label>
                                                </div>
                                                <div class="input-field col s12 m6 l2">
                                                    <input id="fecha_fin" name="fecha_fin" type="date"
                                                        value="{{ request('fecha_fin') }}">
                                                    <label for="fecha_fin">Fecha de fin</label>
                                                </div>
                                                <div class="input-field col s12 m6 l2">
                                                    <input id="hora_fin" name="hora_fin" type="time"
                                                        value="{{ request('hora_fin') }}">
                                                    <label for="hora_fin">Hora de fin</label>
                                                </div>
                                                <div class="input-field col s12 m6 l3">
                                                    <button class="btn tooltipped btn-buscar" id="btn-Respuesta"
                                                        data-tooltip="Actualizar todo" type="button"
                                                        onclick="actualizarGraficosEncuesta();">
                                                        <i class="fa-solid fa-magnifying-glass white-text"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div id="charts-Respuesta" class="row flex without-tb">
                            <div class="col s12 m6">
                                <div class="card minicard">
                                    <div class="card-content">
                                        <span class="card-title">Respuestas por responsable</span>

                                        <div class="row without-tb">
                                            <div class="input-field col s12">
                                                <div class="chart-container" id="chartdiv-respuestas-responsable"
                                                    style="width: 100%;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @foreach ($detEncuestas as $pregunta)
                                @if ($pregunta->tipopregunta->nombre !== 'Texto')
                                    <div class="col s12 m6">
                                        <div class="card minicard">
                                            <div class="card-content">
                                                <span class="card-title">
                                                    <i class="fa-solid fa-chart-pie left"></i>
                                                    {{ $pregunta->enunciado }} |
                                                    {{ $pregunta->tipopregunta->nombre }}

                                                </span>
                                                @php
                                                    $respuestasPorTipoPregunta = [];
                                                    $respuestasContadas = 0;
                                                @endphp

                                                @foreach ($pregunta->encuesta->respuestas as $respuesta)
                                                    @foreach ($respuesta->detRespuestas as $detrespuesta)
                                                        @if ($detrespuesta->detEncuestum->tipopregunta->id == $pregunta->tipopregunta->id)
                                                            @php
                                                                $respuestasPorTipoPregunta[] = $detrespuesta->valordetRespuestas
                                                                    ->pluck('valor')
                                                                    ->join(', ');
                                                                $respuestasContadas++;
                                                            @endphp
                                                        @endif
                                                    @endforeach
                                                @endforeach

                                                <input type="hidden" id="{{ $pregunta->tipopregunta->id }}_data"
                                                    value="{{ json_encode(['respuestas' => $respuestasPorTipoPregunta, 'total_respuestas' => $respuestasContadas], JSON_UNESCAPED_UNICODE) }}">

                                                <div class="row without-tb">
                                                    <div class="input-field col s12">
                                                        <div id="det_encuesta_{{ $pregunta->tipopregunta->id }}"
                                                            class="chart-container"
                                                            style="position: relative; width: 100%; min-height: 350px;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>





                        <!-- Aquí mostramos las preguntas de tipo 'Texto' al final, ocupando todo el ancho -->
                        <div id="texto-encuestas" class="row flex without-tb">
                            @foreach ($detEncuestas as $pregunta)
                                @if ($pregunta->tipopregunta->nombre === 'Texto')
                                    <div class="col s12">
                                        <div class="card minicard">
                                            <div class="card-content">
                                                <span class="card-title">{{ $pregunta->enunciado }}
                                                </span>
                                                <div class="row without-tb">
                                                    <div class="input-field col s12">
                                                        <table class="highlight striped centered data-table"
                                                            style="width:100%">
                                                            <thead class="thead-custom">
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Código</th>
                                                                    <th>Fecha</th>
                                                                    <th>Cliente</th>
                                                                    <th>Respuesta</th>
                                                                    <th></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($pregunta->encuesta->respuestas as $respuesta)
                                                                    <tr>
                                                                        <td>{{ $loop->iteration }}
                                                                        <td>{{ $respuesta->codigo ?? 'N/A' }}</td>
                                                                        <td>{{ \Carbon\Carbon::parse($respuesta->fecha)->format('Y-m-d') ?? 'N/A' }}
                                                                            <br>
                                                                            {{ \Carbon\Carbon::parse($respuesta->fecha)->format(' h:i:s A') ?? 'N/A' }}
                                                                        </td>

                                                                        <td>{{ $respuesta->persona ?? 'N/A' }}</td>
                                                                        <td>
                                                                            @foreach ($respuesta->detRespuestas as $detrespuesta)
                                                                                @if ($detrespuesta->detEncuestum->tipopregunta->nombre == 'Texto')
                                                                                    <p>
                                                                                        <span>{{ $detrespuesta->valordetRespuestas->pluck('valor')->join(', ') }}</span>

                                                                                    </p>
                                                                                @endif
                                                                            @endforeach
                                                                        </td>

                                                                        <td>
                                                                            <a href="{{ route('respuesta.detalle', $respuesta->id) }}"
                                                                                class="btn-floating blue darken-1 waves-light btn-cargar-crud"
                                                                                data-titulo="{{  ' Respuestas - ' . $respuesta->codigo . ' - ' . $respuesta->Persona->Personamaestro->nrodoc . ' - ' . $respuesta->persona }}" data-icono="{{ '<i class="fa-solid fa-list white-text"></i>' }}"
                                                                                data-tooltip="Respuestas">       
                                                                                <i class="fa-solid fa-list white-text"></i>
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>

                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="respuestas_responsable_data" value='@json($respuestasPorResponsable)'>
@endsection
