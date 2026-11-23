@extends('layouts.app')

@section('template_title')
    Movimientos de Reserva
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
                     <div class="row" id="filtros" style="margin-bottom: 10px; display: flex; flex-wrap: wrap;">
                        <!-- Buscador + Botón Buscar -->
                        <div class="col s12 m6 left-align order-mobile-2 order-desktop-1">
                            <div style="display: flex; align-items: flex-start; gap: 5px; width: 100%;">
                                <!-- Input con label -->
                                <div class="input-field" style="flex: 1; margin-top: 0;">
                                    <input type="text" id="search-input" class="validate"
                                        data-url-base="{{ route('movreserva.index') }}">
                                    <label for="search-input">Buscar</label>
                                </div>
                            <div class="col s12 m4">
                              <div class="input-field" style="flex: 1; margin-top: 0;">
                                    <select id="situacion-filter" name="estado" onchange="filterReservas()">
                                        @foreach ($estados as $estado)
                                            <option value="{{ $estado }}"
{{ $estadoSeleccionado == $estado ? 'selected' : '' }}>
{{ $estado }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label>Situación</label>
                                </div>
                            </div>
                                <!-- Botón Buscar -->
                                <a class="btn waves-effect waves-light darken-2 tooltipped btn-buscar" data-tooltip="Buscar"
                                    type="button" onclick="realizarBusqueda();">
                                    <i class="fa-solid fa-magnifying-glass white-text"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Botón NUEVO -->
                        <div class="col s12 m6 right-align order-mobile-1 order-desktop-2"
                            style="margin-top:0; padding-top:0;">
                            @foreach ($operaciones as $operacion)
                                @if ($operacion->tipo == 'Tabla')
                                      <a href="{{ route($operacion->accion) }}"
                                            class="btn waves-effect waves-light btn-cargar-crud {{ $operacion->color }}"
                                            data-icono="fa-solid fa-plus left" data-titulo="{{ $operacion->comentario }}"
                                            style="border-radius: 10px;">
                                            {!! $operacion->imagen !!} {{ $operacion->descripcion }}
                                        </a>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Contenedor del selector de registros por página y botones -->
                    <div class="row valign-wrapper" style="margin-bottom: 10px;">
                        <!-- Selector de registros -->
                        <div class="col s12 m8 l8 left-align valign-wrapper" style="padding-left:13px;">
                            <span style="margin-right: 8px;">Registros por página:</span>
                            <div class="input-field" style="margin: 0;">
                                <select id="records-per-page" class="browser-default" onchange="realizarBusqueda()">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>

                        <!-- Botones de expansión y filtros -->
                        <div class="col s12 m4 l4 right-align"
                            style="display: flex; align-items: center; justify-content: flex-end; padding-right:0;">
                            <button class="btn-flat btn-grid-options tooltipped" id="btn-expand-compress" type="button"
                                onclick="expandOrCompressChildRows();" data-position="top" data-tooltip="Expandir filas">
                                <i class="fa-solid fa-expand" style="color:#343a40;"></i>
                            </button>

                            <button class="btn-flat btn-grid-options tooltipped" id="btn-filtro" type="button"
                                onclick="mostrarFiltro();" data-position="top" data-tooltip="Filtros">
                                <i class="fa-solid fa-magnifying-glass-minus" style="color:#343a40;"></i>
                            </button>
                        </div>
                    </div>
                    <div id="tb-container" class="table-responsive">
                        <table id="data-table" class="highlight striped centered nowrap" style="width:100%">
                            <thead class="thead-custom">
                                <tr>
                                    <th class="center">Código</th>
                                    <th class="center">Id</th>
                                    <th class="ocultar">Estado real</th>
                                    <th class="center">F. Registro </th>
                                    <th class="center">Habitacion(es) </th>

                                    <th class="center">F. reserva</th>
                                    <th class="center">F. Inicio</th>

                                    <th class="center">F. Salida</th>
                                    <th class="center">Usuario</th>
                                    <th class="center">Cliente</th>
                                    <th class="center">Celular</th>
                                    <th class="center"># Personas</th>

                                    <th class="center">Dias</th>
                                    <th class="center">Total</th>
                                    <th class="center dtr-hidden" style="display: none;">Moneda</th>
                                    <th class="center">Estado</th>
                                    <th class="center">Situación</th>
                                    <th class="center dtr-hidden" style="display: none;">Tipo Cambio</th>
                                    <th class="center dtr-hidden" style="display: none;">Comentario</th>
                                    <th class="center">F. Cancelacion</th>

                                    <th class="center">E-mail</th>

                                    <th class="center dtr-hidden" style="display: none;">Origen</th>
                                    <th class="center">Operaciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($reservas as $reserva)
                                    <tr class="dt-hasChild dtr-expanded">
                                        <td class="center dtr-control" tabindex="0">
                                            {{ str_pad($reserva->movimiento->numero, 8, '0', STR_PAD_LEFT) }}</td>
                                        <td class="center">{{ $reserva->id }}</td>
                                         <td class="ocultar">
                                                {{ $reserva->estado }}
                                                <a href="{{ route( 'finalizarmovreserva', $reserva->id) }}"
                                                   class="btn-floating red darken-1 waves-light tooltipped btn-activar">
                                                   <i class=""></i>
                                                </a>
                                            </td>
                                        <td class="center">{{ $reserva->created_at->format('Y-m-d') }} <br>
                                            {{ $reserva->created_at->format('h:i:s A') }}</td>
                                        <td class="inpt-cantidad-mc ">
                                            {{ collect(json_decode($reserva->json_habitaciones))->pluck('numero')->map(fn($num) => trim($num))->join(', ') }}
                                        </td>

                                        <td class="center">
                                            {{ $reserva->fechavencimiento ? \Carbon\Carbon::parse($reserva->fechavencimiento)->format('Y-m-d') : '' }}
                                            <br>
                                            {{ $reserva->fechavencimiento ? \Carbon\Carbon::parse($reserva->fechavencimiento)->format('h:i:s A') : '' }}
                                        </td>
                                        <td class="center">
                                            {{ $reserva->fechainicio ? \Carbon\Carbon::parse($reserva->fechainicio)->format('Y-m-d') : '' }}
                                            <br>
                                            {{ $reserva->fechainicio ? \Carbon\Carbon::parse($reserva->fechainicio)->format('h:i:s A') : '' }}
                                        </td>
                                        <td class="center">
                                            {{ $reserva->fechasalida ? \Carbon\Carbon::parse($reserva->fechasalida)->format('Y-m-d') : '' }}
                                            <br>
                                            {{ $reserva->fechasalida ? \Carbon\Carbon::parse($reserva->fechasalida)->format('h:i:s A') : '' }}
                                        </td>

                                        <td>
                                            {{ strtoupper($reserva->movimiento->Responsable->usuario->name ?? 'N/A') }}
                                        </td>

                                        <td>{{ $reserva->movimiento->Persona->personamaestro->nrodoc ?? 'N/A' }} - <br>
                                            {{ $reserva->movimiento->persona ?? 'N/A' }}</td>
                                        <td class="center">{{ $reserva->celular }}</td>

                                        <td class="inpt-cantidad-mc">{{ $reserva->nropersonas }}</td>

                                        <td class="inpt-cantidad-mc">{{ $reserva->dias }}</td>
                                        <td class="inpt-cantidad-mc">{{ number_format($reserva->total, 2) }}</td>
                                        <td class="center dtr-hidden" style="display: none;">{{ $reserva->moneda }}</td>
                                        <td class="center">
                                            <span
                                                class="new badge 
                                                {{ $reserva->estado == 'PENDIENTE' ? 'yellow grey-text text-darken-3' : '' }}
                                                {{ $reserva->estado == 'EN ATENCIÓN' ? 'blue white-text' : '' }}
                                                {{ $reserva->estado == 'FINALIZADO' ? 'teal white-text' : '' }}
                                                {{ $reserva->estado == 'AUSENTE' ? 'red white-text' : '' }}"
                                                data-badge-caption="{{ $reserva->estado }}">
                                            </span>
                                        </td>
                                        <td class="center">
                                            <span
                                                class="new badge 
                                                {{ $reserva->movimiento->situacion == 'Activado' ? 'green white-text' : '' }}
                                                {{ $reserva->movimiento->situacion == 'Desactivado' ? 'red white-text' : '' }}"
                                                data-badge-caption="{{ $reserva->movimiento->situacion }}">
                                            </span>
                                        </td>
                                        <td class="inpt-cantidad-mc dtr-hidden" style="display: none;">
                                            {{ number_format($reserva->tipocambio, 3) }}</td>
                                        <td class="center dtr-hidden" style="display: none;">{{ $reserva->observacion }}
                                        </td>
                                        <td class="center">{{ $reserva->fechacancelacion ?? '-' }}</td>

                                        <td class="center">{{ $reserva->email ?? '-' }}</td>

                                        <td class="center dtr-hidden" style="display: none;">
                                            {{ $reserva->origen->descripcion }}</td>
                                        <td class="center">

                                            @foreach ($operaciones as $operacion)
                                                @if ($operacion->tipo == 'Registro')
                                                    @if (
                                                        $operacion->accion === 'movreserva.atender' &&
                                                            $reserva->movimiento->situacion == 'Activado' &&
                                                            $reserva->estado == 'PENDIENTE')
                                                        <a href="{{ route($operacion->accion, $reserva->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                                                            data-icono="fa-solid fa-calendar-check"
                                                            data-titulo="{{ $operacion->comentario }}"
                                                            data-tooltip="{{ $operacion->comentario }}">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @endif
                                                    @if (
                                                        $operacion->accion === 'movreserva.anular' &&
                                                            $reserva->movimiento->situacion == 'Activado' &&
                                                            $reserva->estado == 'PENDIENTE')
                                                            <form action="{{ route($operacion->accion, $reserva->id) }}"
                                                                method="POST" class="anular-form" style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                                    data-tooltip="{{ $operacion->descripcion }}">
                                                                    {!! $operacion->imagen !!}
                                                                </button>
                                                            </form>
                                                    @endif
                                                    {{-- Botón de ver detalles --}}
                                                    @if ($operacion->accion === 'movreserva.detalles')
                                                        <a href="{{ route($operacion->accion, $reserva->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                                                            data-icono="fa-solid fa-eye left"
                                                            data-tooltip="{{ $operacion->descripcion }}"
                                                            data-titulo="Movimientos de Reserva | Detalles">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @endif
                                                    @if (
                                                        $operacion->accion === 'movhabitacion.checkout' &&
                                                            $reserva->estado == 'EN ATENCIÓN' &&
                                                            $reserva->movimiento->situacion == 'Activado')
                                                        <a href="{{ route($operacion->accion, $reserva->habitacionprincipal_id) }}"
                                                            data-icono="fas fa-gifts"
                                                            data-titulo="{{ $operacion->descripcion }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                                                            data-tooltip="{{ $operacion->descripcion }}">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @endif
                                                  
                                                    @if (
                                                        $operacion->accion === 'movreserva.edit' &&
                                                            $reserva->movimiento->situacion == 'Activado' &&
                                                            $reserva->estado !== 'FINALIZADO')
                                                        <a href="{{ route($operacion->accion, $reserva->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                                                            data-tooltip="{{ $operacion->descripcion }}"
                                                            data-icono="fa-solid fa-EDIT left"
                                                            data-titulo="Movimientos de reserva | Editar">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @endif

                                                    {{-- @if (
                                                        $operacion->accion === 'movventa.edit' &&
                                                            $reserva->movimiento->situacion == 'Activado' &&
                                                            $reserva->estado == 'FINALIZADO')
                                                        <a href="{{ route($operacion->accion, $reserva->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped btn-cargar-crud"
                                                            data-tooltip="{{ $operacion->descripcion }}"
                                                            data-icono="fa-solid fa-EDIT left"
                                                            data-titulo="Movimientos de reserva | Editar">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @endif --}}
                                                    @if ($operacion->accion === 'movreserva.generarPDF' && $reserva->movimiento->situacion == 'Activado')
                                                        <a href="{{ route($operacion->accion, $reserva->id) }}"
                                                            class="btn-floating {{ $operacion->color }} darken-1 waves-light tooltipped"
                                                            data-icono="fa-solid fa-edit left"
                                                            data-titulo="{{ $operacion->descripcion }}"
                                                            data-tooltip="{{ $operacion->comentario }}" target="_blank">
                                                            {!! $operacion->imagen !!}
                                                        </a>
                                                    @endif
                                                @endif
                                            @endforeach
                                            @if ( $reserva->estado == 'FINALIZADO')
                                                 <!-- Botón para ver el historial de ventas -->
                                                    <a href="{{ route('movhabitacion.historial-ventas', $reserva->movhabitacion->id) }}" 
                                                        class="btn btn-floating purple white-text tooltipped btn-crear" id="btn-historial-ventas"
                                                        data-tooltip="Historial de Ventas" type="button">
                                                        <i class="fa-solid fa-history left"></i>
                                                    </a>
                                            @endif

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>


                        </table>

                        <div class="row">
                            <div class="col s12 m6">
                                <p>Mostrando {{ $reservas->firstItem() }} a {{ $reservas->lastItem() }} de
                                    {{ $reservas->total() }} registros</p>
                            </div>

                            <!-- Paginación -->
                            <div class="col s12 m6 right-align">
                                {{ $reservas->appends(request()->query())->links() }}
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
