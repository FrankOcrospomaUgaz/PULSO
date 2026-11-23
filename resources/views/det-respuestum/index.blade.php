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

                            <!-- Filtro de Encuesta -->
                   

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

                        <!-- Tabla de Detalles de Encuestas -->
                        <div id="tb-container" class="table-responsive">
                            <table id="data-table" class="highlight striped centered nowrap" style="width:100%">
                                <thead class="thead-custom">
                                    <tr>
                                        <th>Código</th>
                                        <th>Fecha</th>
                                        <th>Persona</th>
                                        <th>Respuestas</th>
                                        <th>Celular</th>
                                        <th>E-mail</th>
                                        <th>Responsable</th>
                                        <th>Habitación</th>
                                        <th>Piso</th>
                                        <th>Alojamiento</th>
                                        <th>Fecha Alojamiento</th>
                                        <th>Usuario</th>
                                        <th>Cliente de Alojamiento</th>
                                        <th>Comentario</th>
                                        <th># personas</th>
                                        <th>Moneda</th>
                                        <th>T. cambio</th>
                                        <th>Total</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($respuestas as $respuesta)
                                        <tr>
                                            <td>{{ $respuesta->codigo }}</td>
                                            <td>
                                            {{ \Carbon\Carbon::parse($respuesta->fecha)->format('Y-m-d') }}<br>
                                            {{ \Carbon\Carbon::parse($respuesta->fecha)->format('h:i:s A') }}
                                        </td>
                                           <td style="min-width: 200px; word-wrap: break-word; white-space: normal;">{{ $respuesta->Persona->Personamaestro->nrodoc }} - {{ $respuesta->persona }}</td>
                                        
                                            <td style="min-width: 300px; word-wrap: break-word; white-space: normal;">
                                                @foreach ($respuesta->detRespuestas as $detrespuesta)
                                                    <p>
                                                        <i class="fa-solid fa-circle-question"></i>   
                                                        <span >{{ $detrespuesta->detEncuestum->enunciado ?? 'Pregunta desconocida' }}</span>:
                                                        <span style="font-weight: bold;">{{ $detrespuesta->valordetRespuestas->pluck('valor')->join(', ') }}</span>
                                                    </p>
                                                @endforeach
                                            </td>
                                            <td>{{ $respuesta->Persona->telefonomovil }}</td>
                                            <td>{{ $respuesta->Persona->email }}</td>
                                             <td style="min-width: 200px; word-wrap: break-word; white-space: normal;">{{ $respuesta->Movimiento->Responsable->Personamaestro->nrodoc }} - {{ $respuesta->Movimiento->responsable }}</td>
                                            <td>{{ $respuesta->Movimiento->movhabitacion->habitacion->numero }}</td>
                                           <td>{{ $respuesta->Movimiento->movhabitacion->habitacion->piso->nombre }}</td>
                                                                           
                                            <td>{{ $respuesta->Movimiento->numero ?? '' }}</td>             
                                             <td>{{ \Carbon\Carbon::parse($respuesta->Movimiento->movhabitacion->created_at)->format('Y-m-d') }}<br>
                                            {{ \Carbon\Carbon::parse($respuesta->Movimiento->movhabitacion->created_at)->format('h:i:s A') }}</td>   
                                            <td>{{ $respuesta->Movimiento->usuario ?? '' }}</td>
                                             <td style="min-width: 100px; word-wrap: break-word; white-space: normal;">{{ $respuesta->Movimiento->Persona->Personamaestro->nrodoc }} - {{ $respuesta->Movimiento->persona }}</td>
                                            <td>{{ $respuesta->Movimiento->comentario}}</td>
                                            <td>{{ $respuesta->Movimiento->movhabitacion->nro_personas }}</td>
                                            <td>{{ $respuesta->Movimiento->movhabitacion->moneda}}</td>
                                            <td>3.77</td>
                                            <td>{{ $respuesta->Movimiento->movhabitacion->total}}</td>                        
                                            <td>
                                                @foreach ($operaciones as $operacion)
                                                    @if ($operacion->tipo == 'Registro')
                                                        @if ($operacion->accion === 'respuesta.destroy')
                                                            <form action="{{ route($operacion->accion, $respuesta->id) }}"
                                                                class="delete-form" method="POST" style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                    data-tooltip="{{ $operacion->descripcion }}">
                                                                    {!! $operacion->imagen !!}
                                                                </button>
                                                            </form>
                                                        @elseif($operacion->accion == 'respuesta.edit')
                                                            <a href="{{ route($operacion->accion, $respuesta->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-crear "
                                                                data-tooltip="{{ $operacion->descripcion }}">
                                                                {!! $operacion->imagen !!}
                                                            </a>
                                                            @else
                                                            <a href="{{ route($operacion->accion, $respuesta->id) }}"
                                                                class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-cargar-crud" data-titulo="{{ $operacion->descripcion . ' - ' . $respuesta->codigo . ' - ' . $respuesta->Persona->Personamaestro->nrodoc . ' - ' . $respuesta->persona }}" data-icono="{{ $operacion->imagen }}"
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
                                    <p>Mostrando {{ $respuestas->firstItem() }} a {{ $respuestas->lastItem() }} de {{ $respuestas->total() }} registros</p>
                                </div>
                                <div class="col s12 m6 right-align">
                                    {{ $respuestas->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
