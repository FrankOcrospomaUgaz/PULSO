@extends('layouts.app')

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
               

                    <div class="row without-bottom flex">
                        <div class="col s12 m6">
                            <div class="card minicard">
                                <form id="movventa-ventas-producto-form" action="{{ route('report.movventa.generar') }}"
                                    method="post" target="_blank">
                                    @csrf
                                    <input type="hidden" name="reporte" value="ventas_producto">
                                    <input type="hidden" name="tipo_documento" value="pdf">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-mug-saucer left"></i>Ventas por
                                            producto/servicio</span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12 m6 l4">
                                                <select name="categoria_id" id="ventas-producto-categoria">
                                                    <option value="">Todas</option>
                                                    @foreach ($categorias as $categoria)
                                                        <option value="{{ $categoria->id }}">{{ $categoria->descripcion }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label for="ventas-producto-categoria">Categoria</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ventas-producto-fecha_inicio" name="fecha_inicio" type="date"
                                                    value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                                <label for="ventas-producto-fecha_inicio" class="active">Fecha de
                                                    inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ventas-producto-hora_inicio" name="hora_inicio" type="time"
                                                    value="00:00:00">
                                                <label for="ventas-producto-hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ventas-producto-fecha_fin" name="fecha_fin" type="date"
                                                    value="{{ now()->format('Y-m-d') }}">
                                                <label for="ventas-producto-fecha_fin" class="active">Fecha de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ventas-producto-hora_fin" name="hora_fin" type="time"
                                                    value="23:59:59">
                                                <label for="ventas-producto-hora_fin">Hora de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <select name="origen_venta" id="ventas-producto-origen">
                                                    <option value="">Todos</option>
                                                    <option value="ALOJAMIENTO">Alojamiento</option>
                                                    <option value="RESERVA">Reserva</option>
                                                    <option value="DIRECTA">Venta directa</option>
                                                </select>
                                                <label for="ventas-producto-origen">Origen de venta</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <select name="habitacion_id" id="ventas-producto-habitacion">
                                                    <option value="">Todas</option>
                                                    @foreach ($habitaciones as $habitacion)
                                                        <option value="{{ $habitacion->id }}">{{ $habitacion->numero }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label for="ventas-producto-habitacion">Habitacion</label>
                                            </div>
                                            <div class="input-field col s12 m12 l8">
                                                <select id="ventas-producto-columnas" name="columnas[]" multiple>
                                                    <option value="categoria" selected>Categoria</option>
                                                    <option value="codigo">Codigo</option>
                                                    <option value="descripcion" selected>Producto</option>
                                                    <option value="moneda">Moneda</option>
                                                    <option value="precio_original">Precio original</option>
                                                    <option value="cantidad" selected>Cantidad</option>
                                                    <option value="unidad">Unidad</option>
                                                    <option value="precio_vendido" selected>P. vendido (PEN)</option>
                                                    <option value="total" selected>Total (PEN)</option>
                                                </select>
                                                <label for="ventas-producto-columnas">Columnas</label>
                                            </div>
                                            <div class="col s12 m6 l6">
                                                <p class="mb20">
                                                    <label for="ventas-producto-moneda-pen" style="margin-right:20px;">
                                                        <input id="ventas-producto-moneda-pen" name="moneda"
                                                            type="radio" value="PEN" checked>
                                                        <span>PEN</span>
                                                    </label>
                                                    <label for="ventas-producto-moneda-usd">
                                                        <input id="ventas-producto-moneda-usd" name="moneda"
                                                            type="radio" value="USD">
                                                        <span>USD</span>
                                                    </label>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action right-align">
                                        <button class="btn waves-effect waves-light red"
                                            onclick="seleccionarTipoReporte('movventa-ventas-producto-form', 'pdf');"
                                            type="button">PDF<i class="fa-solid fa-file-pdf white-text right"></i></button>
                                        <button class="btn waves-effect waves-light green"
                                            onclick="seleccionarTipoReporte('movventa-ventas-producto-form', 'excel');"
                                            type="button">Excel<i class="fa-solid fa-file white-text right"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col s12 m6">
                            <div class="card minicard">
                                <form id="movventa-ingresos-parcial-form" action="{{ route('report.movventa.generar') }}"
                                    method="post" target="_blank">
                                    @csrf
                                    <input type="hidden" name="reporte" value="ingresos_parcial">
                                    <input type="hidden" name="tipo_documento" value="pdf">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-calendar-day left"></i>Ingresos
                                            parciales</span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ingresos-parcial-fecha_inicio" name="fecha_inicio" type="date"
                                                    value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                                <label for="ingresos-parcial-fecha_inicio" class="active">Fecha de
                                                    inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ingresos-parcial-hora_inicio" name="hora_inicio" type="time"
                                                    value="00:00:00">
                                                <label for="ingresos-parcial-hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ingresos-parcial-fecha_fin" name="fecha_fin" type="date"
                                                    value="{{ now()->format('Y-m-d') }}">
                                                <label for="ingresos-parcial-fecha_fin" class="active">Fecha de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ingresos-parcial-hora_fin" name="hora_fin" type="time"
                                                    value="23:59:59">
                                                <label for="ingresos-parcial-hora_fin">Hora de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <select name="origen_venta" id="ingresos-parcial-origen">
                                                    <option value="">Todos</option>
                                                    <option value="ALOJAMIENTO">Alojamiento</option>
                                                    <option value="RESERVA">Reserva</option>
                                                    <option value="DIRECTA">Venta directa</option>
                                                </select>
                                                <label for="ingresos-parcial-origen">Origen de venta</label>
                                            </div>
                                            <div class="input-field col s12 m12 l8">
                                                <select id="ingresos-parcial-columnas" name="columnas[]" multiple>
                                                    <option value="fecha" selected>Fecha</option>
                                                    <option value="moneda" selected>Moneda</option>
                                                    <option value="subtotal">Subtotal</option>
                                                    <option value="igv">IGV</option>
                                                    <option value="total" selected>Total</option>
                                                    <option value="diassemana">Dias de semana</option>
                                                    <option value="finsemana">Fin de semana</option>
                                                </select>
                                                <label for="ingresos-parcial-columnas">Columnas</label>
                                            </div>
                                            <div class="col s12 m6 l6">
                                                <p class="mb20">
                                                    <label for="ingresos-parcial-moneda-pen" style="margin-right:20px;">
                                                        <input id="ingresos-parcial-moneda-pen" name="moneda"
                                                            type="radio" value="PEN" checked>
                                                        <span>PEN</span>
                                                    </label>
                                                    <label for="ingresos-parcial-moneda-usd">
                                                        <input id="ingresos-parcial-moneda-usd" name="moneda"
                                                            type="radio" value="USD">
                                                        <span>USD</span>
                                                    </label>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action right-align">
                                        <button class="btn waves-effect waves-light red"
                                            onclick="seleccionarTipoReporte('movventa-ingresos-parcial-form', 'pdf');"
                                            type="button">PDF<i class="fa-solid fa-file-pdf white-text right"></i></button>
                                        <button class="btn waves-effect waves-light green"
                                            onclick="seleccionarTipoReporte('movventa-ingresos-parcial-form', 'excel');"
                                            type="button">Excel<i class="fa-solid fa-file white-text right"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col s12 m6">
                            <div class="card minicard">
                                <form id="movventa-ticket-promedio-form" action="{{ route('report.movventa.generar') }}"
                                    method="post" target="_blank">
                                    @csrf
                                    <input type="hidden" name="reporte" value="ticket_promedio">
                                    <input type="hidden" name="tipo_documento" value="pdf">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-users-rectangle left"></i>Ticket
                                            promedio</span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ticket-promedio-fecha_inicio" name="fecha_inicio" type="date"
                                                    value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                                <label for="ticket-promedio-fecha_inicio" class="active">Fecha de
                                                    inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ticket-promedio-hora_inicio" name="hora_inicio" type="time"
                                                    value="00:00:00">
                                                <label for="ticket-promedio-hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ticket-promedio-fecha_fin" name="fecha_fin" type="date"
                                                    value="{{ now()->format('Y-m-d') }}">
                                                <label for="ticket-promedio-fecha_fin" class="active">Fecha de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ticket-promedio-hora_fin" name="hora_fin" type="time"
                                                    value="23:59:59">
                                                <label for="ticket-promedio-hora_fin">Hora de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <select name="origen_venta" id="ticket-promedio-origen">
                                                    <option value="">Todos</option>
                                                    <option value="ALOJAMIENTO">Alojamiento</option>
                                                    <option value="RESERVA">Reserva</option>
                                                    <option value="DIRECTA">Venta directa</option>
                                                </select>
                                                <label for="ticket-promedio-origen">Origen de venta</label>
                                            </div>
                                            <div class="col s12 m6 l6">
                                                <p class="mb20">
                                                    <label for="ticket-promedio-moneda-pen" style="margin-right:20px;">
                                                        <input id="ticket-promedio-moneda-pen" name="moneda" type="radio"
                                                            value="PEN" checked>
                                                        <span>PEN</span>
                                                    </label>
                                                    <label for="ticket-promedio-moneda-usd">
                                                        <input id="ticket-promedio-moneda-usd" name="moneda" type="radio"
                                                            value="USD">
                                                        <span>USD</span>
                                                    </label>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action right-align">
                                        <button class="btn waves-effect waves-light red"
                                            onclick="seleccionarTipoReporte('movventa-ticket-promedio-form', 'pdf');"
                                            type="button">PDF<i class="fa-solid fa-file-pdf white-text right"></i></button>
                                        <button class="btn waves-effect waves-light green"
                                            onclick="seleccionarTipoReporte('movventa-ticket-promedio-form', 'excel');"
                                            type="button">Excel<i class="fa-solid fa-file white-text right"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col s12 m6">
                            <div class="card minicard">
                                <form id="movventa-ticket-promedio-persona-form"
                                    action="{{ route('report.movventa.generar') }}" method="post" target="_blank">
                                    @csrf
                                    <input type="hidden" name="reporte" value="ticket_promedio_persona">
                                    <input type="hidden" name="tipo_documento" value="pdf">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-users-rectangle left"></i>Ticket
                                            promedio por persona</span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ticket-promedio-persona-fecha_inicio" name="fecha_inicio"
                                                    type="date" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                                <label for="ticket-promedio-persona-fecha_inicio"
                                                    class="active">Fecha de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ticket-promedio-persona-hora_inicio" name="hora_inicio"
                                                    type="time" value="00:00:00">
                                                <label for="ticket-promedio-persona-hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ticket-promedio-persona-fecha_fin" name="fecha_fin" type="date"
                                                    value="{{ now()->format('Y-m-d') }}">
                                                <label for="ticket-promedio-persona-fecha_fin" class="active">Fecha de
                                                    fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ticket-promedio-persona-hora_fin" name="hora_fin" type="time"
                                                    value="23:59:59">
                                                <label for="ticket-promedio-persona-hora_fin">Hora de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <select name="origen_venta" id="ticket-promedio-persona-origen">
                                                    <option value="">Todos</option>
                                                    <option value="ALOJAMIENTO">Alojamiento</option>
                                                    <option value="RESERVA">Reserva</option>
                                                    <option value="DIRECTA">Venta directa</option>
                                                </select>
                                                <label for="ticket-promedio-persona-origen">Origen de venta</label>
                                            </div>
                                            <div class="col s12 m6 l6">
                                                <p class="mb20">
                                                    <label for="ticket-promedio-persona-moneda-pen"
                                                        style="margin-right:20px;">
                                                        <input id="ticket-promedio-persona-moneda-pen" name="moneda"
                                                            type="radio" value="PEN" checked>
                                                        <span>PEN</span>
                                                    </label>
                                                    <label for="ticket-promedio-persona-moneda-usd">
                                                        <input id="ticket-promedio-persona-moneda-usd" name="moneda"
                                                            type="radio" value="USD">
                                                        <span>USD</span>
                                                    </label>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action right-align">
                                        <button class="btn waves-effect waves-light red"
                                            onclick="seleccionarTipoReporte('movventa-ticket-promedio-persona-form', 'pdf');"
                                            type="button">PDF<i class="fa-solid fa-file-pdf white-text right"></i></button>
                                        <button class="btn waves-effect waves-light green"
                                            onclick="seleccionarTipoReporte('movventa-ticket-promedio-persona-form', 'excel');"
                                            type="button">Excel<i class="fa-solid fa-file white-text right"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col s12 m6">
                            <div class="card minicard">
                                <form id="movventa-documento-form" action="{{ route('report.movventa.generar') }}"
                                    method="post" target="_blank">
                                    @csrf
                                    <input type="hidden" name="reporte" value="ventas_documento">
                                    <input type="hidden" name="tipo_documento" value="pdf">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-receipt left"></i>Ventas por tipo
                                            de documento</span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ventas-documento-fecha_inicio" name="fecha_inicio"
                                                    type="date" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                                <label for="ventas-documento-fecha_inicio" class="active">Fecha de
                                                    inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ventas-documento-hora_inicio" name="hora_inicio" type="time"
                                                    value="00:00:00">
                                                <label for="ventas-documento-hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ventas-documento-fecha_fin" name="fecha_fin" type="date"
                                                    value="{{ now()->format('Y-m-d') }}">
                                                <label for="ventas-documento-fecha_fin" class="active">Fecha de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ventas-documento-hora_fin" name="hora_fin" type="time"
                                                    value="23:59:59">
                                                <label for="ventas-documento-hora_fin">Hora de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <select name="situacion" id="ventas-documento-situacion">
                                                    <option value="">Todos</option>
                                                    <option value="A">Activado</option>
                                                    <option value="D">Desactivado</option>
                                                </select>
                                                <label for="ventas-documento-situacion">Situacion</label>
                                            </div>
                                            <div class="col s12 m6 l4">
                                                <p>
                                                    <label for="ventas-documento-moneda-pen" style="margin-right:20px;">
                                                        <input id="ventas-documento-moneda-pen" name="moneda"
                                                            type="radio" value="PEN" checked>
                                                        <span>PEN</span>
                                                    </label>
                                                    <label for="ventas-documento-moneda-usd">
                                                        <input id="ventas-documento-moneda-usd" name="moneda"
                                                            type="radio" value="USD">
                                                        <span>USD</span>
                                                    </label>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action right-align">
                                        <button class="btn waves-effect waves-light red"
                                            onclick="seleccionarTipoReporte('movventa-documento-form', 'pdf');"
                                            type="button">PDF<i class="fa-solid fa-file-pdf white-text right"></i></button>
                                        <button class="btn waves-effect waves-light green"
                                            onclick="seleccionarTipoReporte('movventa-documento-form', 'excel');"
                                            type="button">Excel<i class="fa-solid fa-file white-text right"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col s12 m6">
                            <div class="card minicard">
                                <form id="movventa-medio-pago-form" action="{{ route('report.movventa.generar') }}"
                                    method="post" target="_blank">
                                    @csrf
                                    <input type="hidden" name="reporte" value="ventas_mediopago">
                                    <input type="hidden" name="tipo_documento" value="pdf">
                                    <div class="card-content">
                                        <span class="card-title"><i
                                                class="fa-solid fa-magnifying-glass-dollar left"></i>Ventas por metodo de
                                            pago</span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12 m6 l4">
                                                <input id="medio-pago-fecha_inicio" name="fecha_inicio" type="date"
                                                    value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                                <label for="medio-pago-fecha_inicio" class="active">Fecha de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="medio-pago-hora_inicio" name="hora_inicio" type="time"
                                                    value="00:00:00">
                                                <label for="medio-pago-hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="medio-pago-fecha_fin" name="fecha_fin" type="date"
                                                    value="{{ now()->format('Y-m-d') }}">
                                                <label for="medio-pago-fecha_fin" class="active">Fecha de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="medio-pago-hora_fin" name="hora_fin" type="time"
                                                    value="23:59:59">
                                                <label for="medio-pago-hora_fin">Hora de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <select name="mediopago_id" id="medio-pago-mediopago">
                                                    <option value="">Todos</option>
                                                    @foreach ($mediopagos as $mediopago)
                                                        <option value="{{ $mediopago->id }}">
                                                            {{ $mediopago->descripcion }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label for="medio-pago-mediopago">Medio</label>
                                            </div>
                                            <div class="col s12 m6 l4">
                                                <p>
                                                    <label for="medio-pago-moneda-pen" style="margin-right:20px;">
                                                        <input id="medio-pago-moneda-pen" name="moneda" type="radio"
                                                            value="PEN" checked>
                                                        <span>PEN</span>
                                                    </label>
                                                    <label for="medio-pago-moneda-usd">
                                                        <input id="medio-pago-moneda-usd" name="moneda" type="radio"
                                                            value="USD">
                                                        <span>USD</span>
                                                    </label>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action right-align">
                                        <button class="btn waves-effect waves-light red"
                                            onclick="seleccionarTipoReporte('movventa-medio-pago-form', 'pdf');"
                                            type="button">PDF<i class="fa-solid fa-file-pdf white-text right"></i></button>
                                        <button class="btn waves-effect waves-light green"
                                            onclick="seleccionarTipoReporte('movventa-medio-pago-form', 'excel');"
                                            type="button">Excel<i class="fa-solid fa-file white-text right"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col s12 m6">
                            <div class="card minicard">
                                <form id="movventa-medio-pago-detallado-form"
                                    action="{{ route('report.movventa.generar') }}" method="post" target="_blank">
                                    @csrf
                                    <input type="hidden" name="reporte" value="ventas_mediopago_detallado">
                                    <input type="hidden" name="tipo_documento" value="pdf">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-rectangle-list left"></i>Detalle de
                                            ventas por metodo de pago</span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12 m6 l4">
                                                <input id="medio-pago-det-fecha_inicio" name="fecha_inicio" type="date"
                                                    value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                                <label for="medio-pago-det-fecha_inicio" class="active">Fecha de
                                                    inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="medio-pago-det-hora_inicio" name="hora_inicio" type="time"
                                                    value="00:00:00">
                                                <label for="medio-pago-det-hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="medio-pago-det-fecha_fin" name="fecha_fin" type="date"
                                                    value="{{ now()->format('Y-m-d') }}">
                                                <label for="medio-pago-det-fecha_fin" class="active">Fecha de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="medio-pago-det-hora_fin" name="hora_fin" type="time"
                                                    value="23:59:59">
                                                <label for="medio-pago-det-hora_fin">Hora de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <select name="mediopago_id" id="medio-pago-det-mediopago">
                                                    <option value="">Todos</option>
                                                    @foreach ($mediopagos as $mediopago)
                                                        <option value="{{ $mediopago->id }}">
                                                            {{ $mediopago->descripcion }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label for="medio-pago-det-mediopago">Medio</label>
                                            </div>
                                            <div class="col s12 m6 l4">
                                                <p>
                                                    <label for="medio-pago-det-moneda-pen" style="margin-right:20px;">
                                                        <input id="medio-pago-det-moneda-pen" name="moneda" type="radio"
                                                            value="PEN" checked>
                                                        <span>PEN</span>
                                                    </label>
                                                    <label for="medio-pago-det-moneda-usd">
                                                        <input id="medio-pago-det-moneda-usd" name="moneda" type="radio"
                                                            value="USD">
                                                        <span>USD</span>
                                                    </label>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action right-align">
                                        <button class="btn waves-effect waves-light red"
                                            onclick="seleccionarTipoReporte('movventa-medio-pago-detallado-form', 'pdf');"
                                            type="button">PDF<i class="fa-solid fa-file-pdf white-text right"></i></button>
                                        <button class="btn waves-effect waves-light green"
                                            onclick="seleccionarTipoReporte('movventa-medio-pago-detallado-form', 'excel');"
                                            type="button">Excel<i class="fa-solid fa-file white-text right"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col s12 m6">
                            <div class="card minicard">
                                <form id="movventa-medio-pago-consolidado-form"
                                    action="{{ route('report.movventa.generar') }}" method="post" target="_blank">
                                    @csrf
                                    <input type="hidden" name="reporte" value="ventas_mediopago_consolidado">
                                    <input type="hidden" name="tipo_documento" value="pdf">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-piggy-bank left"></i>Consolidado
                                            por metodo de pago</span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12 m6 l4">
                                                <input id="medio-pago-con-fecha_inicio" name="fecha_inicio" type="date"
                                                    value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                                <label for="medio-pago-con-fecha_inicio" class="active">Fecha de
                                                    inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="medio-pago-con-hora_inicio" name="hora_inicio" type="time"
                                                    value="00:00:00">
                                                <label for="medio-pago-con-hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="medio-pago-con-fecha_fin" name="fecha_fin" type="date"
                                                    value="{{ now()->format('Y-m-d') }}">
                                                <label for="medio-pago-con-fecha_fin" class="active">Fecha de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="medio-pago-con-hora_fin" name="hora_fin" type="time"
                                                    value="23:59:59">
                                                <label for="medio-pago-con-hora_fin">Hora de fin</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action right-align">
                                        <button class="btn waves-effect waves-light red"
                                            onclick="seleccionarTipoReporte('movventa-medio-pago-consolidado-form', 'pdf');"
                                            type="button">PDF<i class="fa-solid fa-file-pdf white-text right"></i></button>
                                        <button class="btn waves-effect waves-light green"
                                            onclick="seleccionarTipoReporte('movventa-medio-pago-consolidado-form', 'excel');"
                                            type="button">Excel<i class="fa-solid fa-file white-text right"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col s12 m6">
                            <div class="card minicard">
                                <form id="movventa-responsable-form" action="{{ route('report.movventa.generar') }}"
                                    method="post" target="_blank">
                                    @csrf
                                    <input type="hidden" name="reporte" value="ventas_responsable">
                                    <input type="hidden" name="tipo_documento" value="pdf">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-id-card-clip left"></i>Ventas por
                                            responsable</span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12 m12 l12">
                                                <i class="fa-solid fa-user-tie prefix"></i>
                                                <select name="responsable_id" id="ventas-responsable-responsable">
                                                    <option value="">Todos</option>
                                                    @foreach ($responsables as $responsable)
                                                        <option value="{{ $responsable->id }}">
                                                            {{ ($responsable->personamaestro->nrodoc ?? '0') . ' - ' . trim($responsable->nombres . ' ' . $responsable->apellidos) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label for="ventas-responsable-responsable">Responsable</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <select name="categoria_id" id="ventas-responsable-categoria">
                                                    <option value="">Todas</option>
                                                    @foreach ($categorias as $categoria)
                                                        <option value="{{ $categoria->id }}">{{ $categoria->descripcion }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label for="ventas-responsable-categoria">Categoria</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ventas-responsable-fecha_inicio" name="fecha_inicio"
                                                    type="date" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                                <label for="ventas-responsable-fecha_inicio" class="active">Fecha de
                                                    inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ventas-responsable-hora_inicio" name="hora_inicio" type="time"
                                                    value="00:00:00">
                                                <label for="ventas-responsable-hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ventas-responsable-fecha_fin" name="fecha_fin" type="date"
                                                    value="{{ now()->format('Y-m-d') }}">
                                                <label for="ventas-responsable-fecha_fin" class="active">Fecha de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ventas-responsable-hora_fin" name="hora_fin" type="time"
                                                    value="23:59:59">
                                                <label for="ventas-responsable-hora_fin">Hora de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <select name="origen_venta" id="ventas-responsable-origen">
                                                    <option value="">Todos</option>
                                                    <option value="ALOJAMIENTO">Alojamiento</option>
                                                    <option value="RESERVA">Reserva</option>
                                                    <option value="DIRECTA">Venta directa</option>
                                                </select>
                                                <label for="ventas-responsable-origen">Origen de venta</label>
                                            </div>
                                            <div class="col s12 m6 l4">
                                                <p>
                                                    <label for="ventas-responsable-moneda-pen" style="margin-right:20px;">
                                                        <input id="ventas-responsable-moneda-pen" name="moneda"
                                                            type="radio" value="PEN" checked>
                                                        <span>PEN</span>
                                                    </label>
                                                    <label for="ventas-responsable-moneda-usd">
                                                        <input id="ventas-responsable-moneda-usd" name="moneda"
                                                            type="radio" value="USD">
                                                        <span>USD</span>
                                                    </label>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action right-align">
                                        <button class="btn waves-effect waves-light red"
                                            onclick="seleccionarTipoReporte('movventa-responsable-form', 'pdf');"
                                            type="button">PDF<i class="fa-solid fa-file-pdf white-text right"></i></button>
                                        <button class="btn waves-effect waves-light green"
                                            onclick="seleccionarTipoReporte('movventa-responsable-form', 'excel');"
                                            type="button">Excel<i class="fa-solid fa-file white-text right"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col s12 m6">
                            <div class="card minicard">
                                <form id="movventa-descuento-persona-form" action="{{ route('report.movventa.generar') }}"
                                    method="post" target="_blank">
                                    @csrf
                                    <input type="hidden" name="reporte" value="descuento_persona">
                                    <input type="hidden" name="tipo_documento" value="pdf">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-percentage left"></i>Descuentos por
                                            persona</span>
                                        <div class="row without-tb">
                                            <div class="col s12 m12 l12">
                                                <div class="input-group-field">
                                                    <button class="btn red right tooltipped"
                                                        id="btn-movventa-descuento-persona-quitar-persona"
                                                        data-tooltip="Deseleccionar persona" type="button"
                                                        onclick="eliminarPersona('movventa-descuento-persona')"
                                                        style="display:none"><i class="fa-solid fa-user-slash"></i></button>
                                                    <div class="right-wrapper input-field">
                                                        <input id="movventa-descuento-persona-persona_id"
                                                            name="persona_id" type="hidden">
                                                        <input class="autocomplete" autocomplete="off"
                                                            id="movventa-descuento-persona-persona" name="persona"
                                                            type="text"
                                                            data-autocomplete-url="{{ route('autocomplete.personas') }}">
                                                        <label for="movventa-descuento-persona-persona">Persona</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <select name="categoria_id" id="descuento-persona-categoria">
                                                    <option value="">Todas</option>
                                                    @foreach ($categorias as $categoria)
                                                        <option value="{{ $categoria->id }}">{{ $categoria->descripcion }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label for="descuento-persona-categoria">Categoria</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="descuento-persona-fecha_inicio" name="fecha_inicio"
                                                    type="date" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                                <label for="descuento-persona-fecha_inicio" class="active">Fecha de
                                                    inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="descuento-persona-hora_inicio" name="hora_inicio" type="time"
                                                    value="00:00:00">
                                                <label for="descuento-persona-hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="descuento-persona-fecha_fin" name="fecha_fin" type="date"
                                                    value="{{ now()->format('Y-m-d') }}">
                                                <label for="descuento-persona-fecha_fin" class="active">Fecha de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="descuento-persona-hora_fin" name="hora_fin" type="time"
                                                    value="23:59:59">
                                                <label for="descuento-persona-hora_fin">Hora de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <select name="origen_venta" id="descuento-persona-origen">
                                                    <option value="">Todos</option>
                                                    <option value="ALOJAMIENTO">Alojamiento</option>
                                                    <option value="RESERVA">Reserva</option>
                                                    <option value="DIRECTA">Venta directa</option>
                                                </select>
                                                <label for="descuento-persona-origen">Origen de venta</label>
                                            </div>
                                            <div class="input-field col s12 m12 l8">
                                                <select id="descuento-persona-columnas" name="columnas[]" multiple>
                                                    <option value="persona" selected>Persona</option>
                                                    <option value="moneda">Moneda</option>
                                                    <option value="subtotal" selected>Subtotal</option>
                                                    <option value="descuento" selected>Descuento</option>
                                                    <option value="total" selected>Total</option>
                                                </select>
                                                <label for="descuento-persona-columnas">Columnas</label>
                                            </div>
                                            <div class="col s12 m6 l4">
                                                <p>
                                                    <label for="descuento-persona-moneda-pen" style="margin-right:20px;">
                                                        <input id="descuento-persona-moneda-pen" name="moneda"
                                                            type="radio" value="PEN" checked>
                                                        <span>PEN</span>
                                                    </label>
                                                    <label for="descuento-persona-moneda-usd">
                                                        <input id="descuento-persona-moneda-usd" name="moneda"
                                                            type="radio" value="USD">
                                                        <span>USD</span>
                                                    </label>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action right-align">
                                        <button class="btn waves-effect waves-light red"
                                            onclick="seleccionarTipoReporte('movventa-descuento-persona-form', 'pdf');"
                                            type="button">PDF<i class="fa-solid fa-file-pdf white-text right"></i></button>
                                        <button class="btn waves-effect waves-light green"
                                            onclick="seleccionarTipoReporte('movventa-descuento-persona-form', 'excel');"
                                            type="button">Excel<i class="fa-solid fa-file white-text right"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col s12 m6">
                            <div class="card minicard">
                                <form id="movventa-cortesia-persona-form" action="{{ route('report.movventa.generar') }}"
                                    method="post" target="_blank">
                                    @csrf
                                    <input type="hidden" name="reporte" value="cortesia_persona">
                                    <input type="hidden" name="tipo_documento" value="pdf">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-handshake left"></i>Cortesias por
                                            persona</span>
                                        <div class="row without-tb">
                                            <div class="col s12 m12 l12">
                                                <div class="input-group-field">
                                                    <button class="btn red right tooltipped"
                                                        id="btn-movventa-cortesia-persona-quitar-persona"
                                                        data-tooltip="Deseleccionar persona" type="button"
                                                        onclick="eliminarPersona('movventa-cortesia-persona')"
                                                        style="display:none"><i class="fa-solid fa-user-slash"></i></button>
                                                    <div class="right-wrapper input-field">
                                                        <input id="movventa-cortesia-persona-persona_id"
                                                            name="persona_id" type="hidden">
                                                        <input class="autocomplete" autocomplete="off"
                                                            id="movventa-cortesia-persona-persona" name="persona"
                                                            type="text"
                                                            data-autocomplete-url="{{ route('autocomplete.personas') }}">
                                                        <label for="movventa-cortesia-persona-persona">Persona</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <select name="categoria_id" id="cortesia-persona-categoria">
                                                    <option value="">Todas</option>
                                                    @foreach ($categorias as $categoria)
                                                        <option value="{{ $categoria->id }}">{{ $categoria->descripcion }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label for="cortesia-persona-categoria">Categoria</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="cortesia-persona-fecha_inicio" name="fecha_inicio" type="date"
                                                    value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                                <label for="cortesia-persona-fecha_inicio" class="active">Fecha de
                                                    inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="cortesia-persona-hora_inicio" name="hora_inicio" type="time"
                                                    value="00:00:00">
                                                <label for="cortesia-persona-hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="cortesia-persona-fecha_fin" name="fecha_fin" type="date"
                                                    value="{{ now()->format('Y-m-d') }}">
                                                <label for="cortesia-persona-fecha_fin" class="active">Fecha de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="cortesia-persona-hora_fin" name="hora_fin" type="time"
                                                    value="23:59:59">
                                                <label for="cortesia-persona-hora_fin">Hora de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <select name="origen_venta" id="cortesia-persona-origen">
                                                    <option value="">Todos</option>
                                                    <option value="ALOJAMIENTO">Alojamiento</option>
                                                    <option value="RESERVA">Reserva</option>
                                                    <option value="DIRECTA">Venta directa</option>
                                                </select>
                                                <label for="cortesia-persona-origen">Origen de venta</label>
                                            </div>
                                            <div class="input-field col s12 m12 l8">
                                                <select id="cortesia-persona-columnas" name="columnas[]" multiple>
                                                    <option value="persona" selected>Persona</option>
                                                    <option value="moneda">Moneda</option>
                                                    <option value="cantidad" selected>Cantidad</option>
                                                    <option value="total" selected>Total</option>
                                                </select>
                                                <label for="cortesia-persona-columnas">Columnas</label>
                                            </div>
                                            <div class="col s12 m6 l4">
                                                <p>
                                                    <label for="cortesia-persona-moneda-pen" style="margin-right:20px;">
                                                        <input id="cortesia-persona-moneda-pen" name="moneda"
                                                            type="radio" value="PEN" checked>
                                                        <span>PEN</span>
                                                    </label>
                                                    <label for="cortesia-persona-moneda-usd">
                                                        <input id="cortesia-persona-moneda-usd" name="moneda"
                                                            type="radio" value="USD">
                                                        <span>USD</span>
                                                    </label>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action right-align">
                                        <button class="btn waves-effect waves-light red"
                                            onclick="seleccionarTipoReporte('movventa-cortesia-persona-form', 'pdf');"
                                            type="button">PDF<i class="fa-solid fa-file-pdf white-text right"></i></button>
                                        <button class="btn waves-effect waves-light green"
                                            onclick="seleccionarTipoReporte('movventa-cortesia-persona-form', 'excel');"
                                            type="button">Excel<i class="fa-solid fa-file white-text right"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col s12 m6">
                            <div class="card minicard">
                                <form id="movventa-descuentos-form" action="{{ route('report.movventa.generar') }}"
                                    method="post" target="_blank">
                                    @csrf
                                    <input type="hidden" name="reporte" value="descuentos">
                                    <input type="hidden" name="tipo_documento" value="pdf">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-percentage left"></i>Descuentos</span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12 m6 l4">
                                                <input id="descuentos-fecha_inicio" name="fecha_inicio" type="date"
                                                    value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                                <label for="descuentos-fecha_inicio" class="active">Fecha de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="descuentos-hora_inicio" name="hora_inicio" type="time"
                                                    value="00:00:00">
                                                <label for="descuentos-hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="descuentos-fecha_fin" name="fecha_fin" type="date"
                                                    value="{{ now()->format('Y-m-d') }}">
                                                <label for="descuentos-fecha_fin" class="active">Fecha de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="descuentos-hora_fin" name="hora_fin" type="time"
                                                    value="23:59:59">
                                                <label for="descuentos-hora_fin">Hora de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="descuentos-producto" name="producto" type="text" value="">
                                                <label for="descuentos-producto">Producto</label>
                                            </div>
                                            <div class="input-field col s12 m12 l12">
                                                <i class="fa-solid fa-user-tie prefix"></i>
                                                <select name="responsable_id" id="descuentos-responsable">
                                                    <option value="">Todos</option>
                                                    @foreach ($responsables as $responsable)
                                                        <option value="{{ $responsable->id }}">
                                                            {{ ($responsable->personamaestro->nrodoc ?? '0') . ' - ' . trim($responsable->nombres . ' ' . $responsable->apellidos) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label for="descuentos-responsable">Responsable</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <select name="origen_venta" id="descuentos-origen">
                                                    <option value="">Todos</option>
                                                    <option value="ALOJAMIENTO">Alojamiento</option>
                                                    <option value="RESERVA">Reserva</option>
                                                    <option value="DIRECTA">Venta directa</option>
                                                </select>
                                                <label for="descuentos-origen">Origen de venta</label>
                                            </div>
                                            <div class="col s12 m6 l4">
                                                <p>
                                                    <label for="descuentos-moneda-pen" style="margin-right:20px;">
                                                        <input id="descuentos-moneda-pen" name="moneda" type="radio"
                                                            value="PEN" checked>
                                                        <span>PEN</span>
                                                    </label>
                                                    <label for="descuentos-moneda-usd">
                                                        <input id="descuentos-moneda-usd" name="moneda" type="radio"
                                                            value="USD">
                                                        <span>USD</span>
                                                    </label>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action right-align">
                                        <button class="btn waves-effect waves-light red"
                                            onclick="seleccionarTipoReporte('movventa-descuentos-form', 'pdf');"
                                            type="button">PDF<i class="fa-solid fa-file-pdf white-text right"></i></button>
                                        <button class="btn waves-effect waves-light green"
                                            onclick="seleccionarTipoReporte('movventa-descuentos-form', 'excel');"
                                            type="button">Excel<i class="fa-solid fa-file white-text right"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col s12 m6">
                            <div class="card minicard">
                                <form id="movventa-cortesias-form" action="{{ route('report.movventa.generar') }}"
                                    method="post" target="_blank">
                                    @csrf
                                    <input type="hidden" name="reporte" value="cortesias">
                                    <input type="hidden" name="tipo_documento" value="pdf">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-handshake left"></i>Cortesias</span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12 m6 l4">
                                                <input id="cortesias-fecha_inicio" name="fecha_inicio" type="date"
                                                    value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                                <label for="cortesias-fecha_inicio" class="active">Fecha de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="cortesias-hora_inicio" name="hora_inicio" type="time"
                                                    value="00:00:00">
                                                <label for="cortesias-hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="cortesias-fecha_fin" name="fecha_fin" type="date"
                                                    value="{{ now()->format('Y-m-d') }}">
                                                <label for="cortesias-fecha_fin" class="active">Fecha de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="cortesias-hora_fin" name="hora_fin" type="time"
                                                    value="23:59:59">
                                                <label for="cortesias-hora_fin">Hora de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="cortesias-producto" name="producto" type="text" value="">
                                                <label for="cortesias-producto">Producto</label>
                                            </div>
                                            <div class="input-field col s12 m12 l12">
                                                <i class="fa-solid fa-user-tie prefix"></i>
                                                <select name="responsable_id" id="cortesias-responsable">
                                                    <option value="">Todos</option>
                                                    @foreach ($responsables as $responsable)
                                                        <option value="{{ $responsable->id }}">
                                                            {{ ($responsable->personamaestro->nrodoc ?? '0') . ' - ' . trim($responsable->nombres . ' ' . $responsable->apellidos) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label for="cortesias-responsable">Responsable</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <select name="origen_venta" id="cortesias-origen">
                                                    <option value="">Todos</option>
                                                    <option value="ALOJAMIENTO">Alojamiento</option>
                                                    <option value="RESERVA">Reserva</option>
                                                    <option value="DIRECTA">Venta directa</option>
                                                </select>
                                                <label for="cortesias-origen">Origen de venta</label>
                                            </div>
                                            <div class="col s12 m6 l4">
                                                <p>
                                                    <label for="cortesias-moneda-pen" style="margin-right:20px;">
                                                        <input id="cortesias-moneda-pen" name="moneda" type="radio"
                                                            value="PEN" checked>
                                                        <span>PEN</span>
                                                    </label>
                                                    <label for="cortesias-moneda-usd">
                                                        <input id="cortesias-moneda-usd" name="moneda" type="radio"
                                                            value="USD">
                                                        <span>USD</span>
                                                    </label>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action right-align">
                                        <button class="btn waves-effect waves-light red"
                                            onclick="seleccionarTipoReporte('movventa-cortesias-form', 'pdf');"
                                            type="button">PDF<i class="fa-solid fa-file-pdf white-text right"></i></button>
                                        <button class="btn waves-effect waves-light green"
                                            onclick="seleccionarTipoReporte('movventa-cortesias-form', 'excel');"
                                            type="button">Excel<i class="fa-solid fa-file white-text right"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col s12 m6">
                            <div class="card minicard">
                                <form id="movventa-compromisos-responsable-form"
                                    action="{{ route('report.movventa.generar') }}" method="post" target="_blank">
                                    @csrf
                                    <input type="hidden" name="reporte" value="compromisos_responsable">
                                    <input type="hidden" name="tipo_documento" value="pdf">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-coins left"></i>Compromisos por
                                            responsable</span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12 m12 l12">
                                                <i class="fa-solid fa-user-tie prefix"></i>
                                                <select name="responsable_id" id="compromisos-responsable">
                                                    <option value="">Todos</option>
                                                    @foreach ($responsables as $responsable)
                                                        <option value="{{ $responsable->id }}">
                                                            {{ ($responsable->personamaestro->nrodoc ?? '0') . ' - ' . trim($responsable->nombres . ' ' . $responsable->apellidos) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label for="compromisos-responsable">Responsable</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="compromisos-resp-fecha_inicio" name="fecha_inicio" type="date"
                                                    value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                                <label for="compromisos-resp-fecha_inicio" class="active">Fecha de
                                                    inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="compromisos-resp-hora_inicio" name="hora_inicio" type="time"
                                                    value="00:00:00">
                                                <label for="compromisos-resp-hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="compromisos-resp-fecha_fin" name="fecha_fin" type="date"
                                                    value="{{ now()->format('Y-m-d') }}">
                                                <label for="compromisos-resp-fecha_fin" class="active">Fecha de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="compromisos-resp-hora_fin" name="hora_fin" type="time"
                                                    value="23:59:59">
                                                <label for="compromisos-resp-hora_fin">Hora de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <select name="estado" id="compromisos-resp-estado">
                                                    <option value="">Todos</option>
                                                    <option value="PENDIENTE">Pendiente</option>
                                                    <option value="PAGADO">Pagado</option>
                                                </select>
                                                <label for="compromisos-resp-estado">Estado</label>
                                            </div>
                                            <div class="col s12 m6 l4">
                                                <p>
                                                    <label for="compromisos-resp-moneda-pen" style="margin-right:20px;">
                                                        <input id="compromisos-resp-moneda-pen" name="moneda"
                                                            type="radio" value="PEN" checked>
                                                        <span>PEN</span>
                                                    </label>
                                                    <label for="compromisos-resp-moneda-usd">
                                                        <input id="compromisos-resp-moneda-usd" name="moneda"
                                                            type="radio" value="USD">
                                                        <span>USD</span>
                                                    </label>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action right-align">
                                        <button class="btn waves-effect waves-light red"
                                            onclick="seleccionarTipoReporte('movventa-compromisos-responsable-form', 'pdf');"
                                            type="button">PDF<i class="fa-solid fa-file-pdf white-text right"></i></button>
                                        <button class="btn waves-effect waves-light green"
                                            onclick="seleccionarTipoReporte('movventa-compromisos-responsable-form', 'excel');"
                                            type="button">Excel<i class="fa-solid fa-file white-text right"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col s12 m6">
                            <div class="card minicard">
                                <form id="movventa-cliente-form" action="{{ route('report.movventa.generar') }}"
                                    method="post" target="_blank">
                                    @csrf
                                    <input type="hidden" name="reporte" value="ventas_cliente">
                                    <input type="hidden" name="tipo_documento" value="pdf">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-user left"></i>Ventas por persona</span>
                                        <div class="row without-tb">
                                            <div class="col s12 m12 l12">
                                                <div class="input-group-field">
                                                    <button class="btn red right tooltipped"
                                                        id="btn-movventa-cliente-quitar-persona"
                                                        data-tooltip="Deseleccionar persona" type="button"
                                                        onclick="eliminarPersona('movventa-cliente')" style="display:none"><i
                                                            class="fa-solid fa-user-slash"></i></button>
                                                    <div class="right-wrapper input-field">
                                                        <input id="movventa-cliente-persona_id" name="persona_id" type="hidden">
                                                        <input class="autocomplete" autocomplete="off"
                                                            id="movventa-cliente-persona" name="persona" type="text"
                                                            data-autocomplete-url="{{ route('autocomplete.personas') }}">
                                                        <label for="movventa-cliente-persona">Persona</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="movventa-cliente-fecha_inicio" name="fecha_inicio" type="date"
                                                    value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                                <label for="movventa-cliente-fecha_inicio" class="active">Fecha de
                                                    inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="movventa-cliente-hora_inicio" name="hora_inicio" type="time"
                                                    value="00:00:00">
                                                <label for="movventa-cliente-hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="movventa-cliente-fecha_fin" name="fecha_fin" type="date"
                                                    value="{{ now()->format('Y-m-d') }}">
                                                <label for="movventa-cliente-fecha_fin" class="active">Fecha de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="movventa-cliente-hora_fin" name="hora_fin" type="time"
                                                    value="23:59:59">
                                                <label for="movventa-cliente-hora_fin">Hora de fin</label>
                                            </div>
                                            <div class="col s12 m6 l4">
                                                <p>
                                                    <label for="movventa-cliente-moneda-pen" style="margin-right:20px;">
                                                        <input id="movventa-cliente-moneda-pen" name="moneda"
                                                            type="radio" value="PEN" checked>
                                                        <span>PEN</span>
                                                    </label>
                                                    <label for="movventa-cliente-moneda-usd">
                                                        <input id="movventa-cliente-moneda-usd" name="moneda"
                                                            type="radio" value="USD">
                                                        <span>USD</span>
                                                    </label>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action right-align">
                                        <button class="btn waves-effect waves-light red"
                                            onclick="seleccionarTipoReporte('movventa-cliente-form', 'pdf');"
                                            type="button">PDF<i class="fa-solid fa-file-pdf white-text right"></i></button>
                                        <button class="btn waves-effect waves-light green"
                                            onclick="seleccionarTipoReporte('movventa-cliente-form', 'excel');"
                                            type="button">Excel<i class="fa-solid fa-file white-text right"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col s12 m6">
                            <div class="card minicard">
                                <form id="movventa-productos-cliente-form"
                                    action="{{ route('report.movventa.generar') }}" method="post" target="_blank">
                                    @csrf
                                    <input type="hidden" name="reporte" value="productos_cliente">
                                    <input type="hidden" name="tipo_documento" value="pdf">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-mug-saucer left"></i>Productos por
                                            persona</span>
                                        <div class="row without-tb">
                                            <div class="col s12 m12 l12">
                                                <div class="input-group-field">
                                                    <button class="btn red right tooltipped"
                                                        id="btn-movventa-productos-cliente-quitar-persona"
                                                        data-tooltip="Deseleccionar persona" type="button"
                                                        onclick="eliminarPersona('movventa-productos-cliente')"
                                                        style="display:none"><i class="fa-solid fa-user-slash"></i></button>
                                                    <div class="right-wrapper input-field">
                                                        <input id="movventa-productos-cliente-persona_id" name="persona_id"
                                                            type="hidden">
                                                        <input class="autocomplete" autocomplete="off"
                                                            id="movventa-productos-cliente-persona" name="persona"
                                                            type="text"
                                                            data-autocomplete-url="{{ route('autocomplete.personas') }}">
                                                        <label for="movventa-productos-cliente-persona">Persona</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="movventa-productos-cliente-fecha_inicio" name="fecha_inicio"
                                                    type="date" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                                <label for="movventa-productos-cliente-fecha_inicio"
                                                    class="active">Fecha de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="movventa-productos-cliente-hora_inicio" name="hora_inicio"
                                                    type="time" value="00:00:00">
                                                <label for="movventa-productos-cliente-hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="movventa-productos-cliente-fecha_fin" name="fecha_fin"
                                                    type="date" value="{{ now()->format('Y-m-d') }}">
                                                <label for="movventa-productos-cliente-fecha_fin" class="active">Fecha de
                                                    fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="movventa-productos-cliente-hora_fin" name="hora_fin" type="time"
                                                    value="23:59:59">
                                                <label for="movventa-productos-cliente-hora_fin">Hora de fin</label>
                                            </div>
                                            <div class="col s12 m6 l4">
                                                <p>
                                                    <label for="movventa-productos-cliente-moneda-pen"
                                                        style="margin-right:20px;">
                                                        <input id="movventa-productos-cliente-moneda-pen" name="moneda"
                                                            type="radio" value="PEN" checked>
                                                        <span>PEN</span>
                                                    </label>
                                                    <label for="movventa-productos-cliente-moneda-usd">
                                                        <input id="movventa-productos-cliente-moneda-usd" name="moneda"
                                                            type="radio" value="USD">
                                                        <span>USD</span>
                                                    </label>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action right-align">
                                        <button class="btn waves-effect waves-light red"
                                            onclick="seleccionarTipoReporte('movventa-productos-cliente-form', 'pdf');"
                                            type="button">PDF<i class="fa-solid fa-file-pdf white-text right"></i></button>
                                        <button class="btn waves-effect waves-light green"
                                            onclick="seleccionarTipoReporte('movventa-productos-cliente-form', 'excel');"
                                            type="button">Excel<i class="fa-solid fa-file white-text right"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col s12 m6">
                            <div class="card minicard">
                                <form id="movventa-ingresos-producto-form" action="{{ route('report.movventa.generar') }}"
                                    method="post" target="_blank">
                                    @csrf
                                    <input type="hidden" name="reporte" value="ingresos_producto">
                                    <input type="hidden" name="tipo_documento" value="pdf">
                                    <div class="card-content">
                                        <span class="card-title"><i class="fa-solid fa-money-bill-wheat left"></i>Ingresos
                                            por categoria</span>
                                        <div class="row without-tb">
                                            <div class="input-field col s12 m6 l4">
                                                <select name="categoria_id" id="ingresos-producto-categoria">
                                                    <option value="">Todas</option>
                                                    @foreach ($categorias as $categoria)
                                                        <option value="{{ $categoria->id }}">{{ $categoria->descripcion }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label for="ingresos-producto-categoria">Categoria</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ingresos-producto-fecha_inicio" name="fecha_inicio"
                                                    type="date" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                                <label for="ingresos-producto-fecha_inicio" class="active">Fecha de
                                                    inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ingresos-producto-hora_inicio" name="hora_inicio" type="time"
                                                    value="00:00:00">
                                                <label for="ingresos-producto-hora_inicio">Hora de inicio</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ingresos-producto-fecha_fin" name="fecha_fin" type="date"
                                                    value="{{ now()->format('Y-m-d') }}">
                                                <label for="ingresos-producto-fecha_fin" class="active">Fecha de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <input id="ingresos-producto-hora_fin" name="hora_fin" type="time"
                                                    value="23:59:59">
                                                <label for="ingresos-producto-hora_fin">Hora de fin</label>
                                            </div>
                                            <div class="input-field col s12 m6 l4">
                                                <select name="origen_venta" id="ingresos-producto-origen">
                                                    <option value="">Todos</option>
                                                    <option value="ALOJAMIENTO">Alojamiento</option>
                                                    <option value="RESERVA">Reserva</option>
                                                    <option value="DIRECTA">Venta directa</option>
                                                </select>
                                                <label for="ingresos-producto-origen">Origen de venta</label>
                                            </div>
                                            <div class="col s12 m6 l4">
                                                <p>
                                                    <label for="ingresos-producto-moneda-pen" style="margin-right:20px;">
                                                        <input id="ingresos-producto-moneda-pen" name="moneda"
                                                            type="radio" value="PEN" checked>
                                                        <span>PEN</span>
                                                    </label>
                                                    <label for="ingresos-producto-moneda-usd">
                                                        <input id="ingresos-producto-moneda-usd" name="moneda"
                                                            type="radio" value="USD">
                                                        <span>USD</span>
                                                    </label>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action right-align">
                                        <button class="btn waves-effect waves-light red"
                                            onclick="seleccionarTipoReporte('movventa-ingresos-producto-form', 'pdf');"
                                            type="button">PDF<i class="fa-solid fa-file-pdf white-text right"></i></button>
                                        <button class="btn waves-effect waves-light green"
                                            onclick="seleccionarTipoReporte('movventa-ingresos-producto-form', 'excel');"
                                            type="button">Excel<i class="fa-solid fa-file white-text right"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
