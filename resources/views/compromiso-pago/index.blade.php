@extends('layouts.app')

@section('template_title')
    Compromisos de Pago
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
                                        data-url-base="{{ route('contrato.compromiso_pago.index', $contrato->id) }}">
                                </div>
                            </div>

                            <!-- Botón Nuevo -->
                            <div class="col s12 m6 right-align">
                                @foreach ($operaciones as $operacion)
                                    @if ($operacion->tipo == 'Tabla')
                                        <a href="{{ route($operacion->accion, $contrato->id) }}"
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
                                        <th>N°</th>
                                        <th>Contrato</th>
                                        <th>Persona</th>
                                        <th>Ambiente</th>
                                        <th>Vencimiento</th>
                                        <th>Pago</th>
                                        <th>Cuota</th>
                                        <th>Monto Pagado</th>
                                        <th>Monto Pendiente</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Operaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($compromisoPagos as $index => $compromiso)
                                        <tr>
                                            <td>{{ $index + $compromisoPagos->firstItem() }}</td>
                                            <td>{{ $compromiso->contrato->numero_contrato ?? '-' }}</td>
                                            <td>{{ $compromiso->persona->apellidos }} {{ $compromiso->persona->nombres }}</td>
                                            <td>{{ $compromiso->ambiente->tipoambiente->nombre ?? '-' }}: {{ $compromiso->ambiente->numero ?? '-' }}</td>
                                            <td>{{ $compromiso->fecha_vencimiento }}</td>
                                            <td>{{ $compromiso->fecha_pago ?? '-' }}</td>
                                            <td>{{ $compromiso->numero_cuota }}</td>
                                            <td>S/. {{ number_format($compromiso->movventas->sum('total'), 2) }}</td>
                                            <td>S/. {{ number_format($compromiso->monto - $compromiso->movventas->sum('total'), 2) }}</td>
                                            <td>S/. {{ number_format($compromiso->monto, 2) }}</td>

<td>
    <span class="new badge
        {{ $compromiso->estado === 'pagado' ? 'green' :
           ($compromiso->estado === 'pendiente' ? 'yellow' :
           ($compromiso->estado === 'anulado' ? 'red' : 'grey')) }}"
        style="{{ $compromiso->estado === 'pendiente' ? 'color: black !important;' : '' }}"
        data-badge-caption="{{ Illuminate\Support\Str::ucfirst($compromiso->estado) }}">
    </span>
</td>

                                           <td>
    @foreach ($operaciones as $operacion)
        @if ($operacion->tipo == 'Registro')
            @if ($operacion->accion === 'contrato.compromiso_pago.edit')
                {{-- Botón editar (comentado) --}}
            @elseif ($operacion->accion === 'contrato.compromiso_pago.destroy' && $compromiso->estado === 'pendiente')
                <form action="{{ route($operacion->accion, [$contrato->id, $compromiso->id]) }}"
                    method="POST" class="anular-form" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="btn-floating red darken-1 waves-light tooltipped"
                        data-tooltip="Anular Compromiso de Pago">
                        <i class="material-icons">block</i>
                    </button>
                </form>
            @elseif ($operacion->accion === 'contrato.comprimiso_pago.pagar' && $compromiso->estado === 'pendiente')
                <a href="{{ route($operacion->accion, $compromiso->id) }}"
                    class="btn-floating {{ $operacion->color }} darken-1 waves-light btn-cargar-crud tooltipped"
                    data-icono="fa-solid fa-edit left"
                    data-titulo="{{ $operacion->descripcion }}"
                    data-tooltip="{{ $operacion->comentario }}">
                    {!! $operacion->imagen !!}
                </a>
            @endif

            
        @endif
    @endforeach

    {{-- Botón para ver pagos parciales --}}
            <a href="{{ route('contrato.compromiso_pago.parciales', $compromiso->id) }}"
                class="btn-floating purple darken-1 waves-light tooltipped btn-crear"
                data-tooltip="Ver pagos">
                <i class="material-icons">visibility</i>
            </a>
        


</td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Paginación -->
                            <div class="row">
                                <div class="col s12 m6">
                                    <p>Mostrando {{ $compromisoPagos->firstItem() }} a {{ $compromisoPagos->lastItem() }} de
                                        {{ $compromisoPagos->total() }} registros</p>
                                </div>
                                <div class="col s12 m6 right-align">
                                    {{ $compromisoPagos->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para mostrar el historial de ventas -->
<div id="modal-ventas" class="modal">
    <div class="modal-content">
        <h4>Historial de Ventas</h4>
        <div id="ventas-list">
            <!-- Aquí irán las ventas cargadas dinámicamente -->
            <p>Cargando ventas...</p>
        </div>
    </div>
    <div class="modal-footer">
        <button class="modal-close btn-flat">Cerrar</button>
    </div>
</div>
@endsection
