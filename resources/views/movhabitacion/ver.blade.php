@extends('layouts.app')

@section('content')
    <div class="main-body">
        <div class="row">
            <div class="col s12">
                <div class="card main-content">
                    <form id="form" class="form-retroceder" enctype="multipart/form-data" disabled>
                        @csrf
                        <input type="hidden" id="form-action-url"
                            value="{{ route('movhabitacion.checkoutstore', [$habitacion->id, 'accion']) }}">
                        <input type="hidden" id="accion" name="accion" value="">
                        <input type="hidden" id="habitacion_id" name="habitacion_id" value="{{ $habitacion->id }}">

                        <!-- Nuevo contenedor para agrupar los inputs y la tabla de huéspedes -->
                        <div class="row">
                            <!-- Row para los inputs -->
                            <div class="col s12 m7">
                                <div class="row" style="margin-bottom: 0;">
                                    <div class="col s12 right-align">
                                        <a href="{{ route('movhabitacion.historial-ventas', $movimientoHabitacion->id) }}" 
                                            class="btn btn-floating purple white-text tooltipped"
                                            data-tooltip="Historial de Ventas">
                                            <i class="fa-solid fa-history"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="input-field col s12 m3">
                                        <i class="material-icons prefix">meeting_room</i>
                                        <input id="habitacion" type="number" value="{{ $habitacion->numero }}" readonly>
                                        <label for="habitacion" class="active">Habitación</label>
                                    </div>

                                    <div class="input-field col s12 m3">
                                        <i class="material-icons prefix">group</i>
                                        <input id="capacidad" type="number"
                                            value="{{ $habitacion->tipohabitacion->capacidad }}" readonly>
                                        <label for="capacidad" class="active">Capacidad Habitación</label>
                                    </div>

                                    <div class="input-field col s12 m3">
                                        <i class="material-icons prefix">event_available</i>
                                        <input id="fechaingreso" type="text"
                                            value="{{ $movimientoHabitacion->fechaingreso }}" readonly>
                                        <label for="fechaingreso" class="active">Fecha Ingreso</label>
                                    </div>

                                    <div class="input-field col s12 m3">
                                        <i class="material-icons prefix">event_busy</i>
                                        <input id="fechasalida" type="text"
                                            value="{{ $movimientoHabitacion->fechasalida }}" readonly>
                                        <label for="fechasalida" class="active">Fecha Salida</label>
                                    </div>
                                </div>

                                <div class="row">
                                    @if ($movimientoHabitacion->modalidad == 'D')
                                        <div class="input-field col s12 m3">
                                            <i class="material-icons prefix">calendar_today</i>
                                            <input id="dias" type="number" min='0' name="dias_totales"
                                                value="{{ number_format($movimientoHabitacion->dias, 0) }}" readonly>
                                            <label for="dias" class="active">Días</label>
                                        </div>
                                    @else
                                        <div class="input-field col s12 m3">
                                            <i class="material-icons prefix">access_time</i>
                                            <input id="horas" type="number" min='0' name="horas_totales"
                                                readonly value="{{ number_format($movimientoHabitacion->horas, 0) }}">
                                            <label for="horas" class="active">Horas</label>
                                        </div>
                                    @endif

                                    <div class="input-field col s12 m3">
                                        <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                        <input id="preciohabitacion" type="number"
                                            value="{{ $habitacion->precio }}" readonly>
                                        <label for="preciohabitacion" class="active">Precio Habitación por día</label>
                                    </div>

                                    <div class="input-field col s12 m3">
                                        <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                        <input id="totalalojamiento" type="number" name="totalalojamiento"
                                            value="{{ $movimientoHabitacion->total }}" readonly>
                                        <label for="total" class="active">Total Alojamiento</label>
                                    </div>

                                    @if ($movimientoHabitacion->situacion == 'P')
                                        <!-- Total Pagado -->
                                        <div class="input-field col s12 m3 total_pagado">
                                            <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                            <input id="total_pagado" type="number" name="total_pagado"
                                                value="{{ $movimientoHabitacion->total + $totalProductosConsumidos - $totalProductosAPagar }}"
                                                readonly>
                                            <label for="total_pagado" class="active">Total Ya Pagado</label>
                                        </div>
                                    @else
                                        <!-- Total a Pagar -->
                                        <div class="input-field col s12 m3 total">
                                            <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                            <input id="total" type="number" name="total"
                                                value="{{ $movimientoHabitacion->total + $totalProductosAPagar }}"
                                                readonly>
                                            <label for="total" class="active">Total a Pagar</label>
                                        </div>
                                    @endif
                                </div>

                                <div class="row">
                                    <div class="input-field col s12 m3">
                                        <i class="material-icons prefix">money_off</i>
                                        <input id="descuento" type="number" value="{{ $movimientoHabitacion->descuento }}" readonly>
                                        <label for="descuento" class="active">Descuento</label>
                                    </div>
                                    <div class="input-field col s12 m3">
                                        <i class="material-icons prefix">flight_land</i>
                                        <input id="early_checkin" type="number" value="{{ $movimientoHabitacion->early_checkin }}" readonly>
                                        <label for="early_checkin" class="active">Early Check-in</label>
                                    </div>
                                    <div class="input-field col s12 m3">
                                        <i class="material-icons prefix">flight_takeoff</i>
                                        <input id="late_checkout" type="number" value="{{ $movimientoHabitacion->late_checkout }}" readonly>
                                        <label for="late_checkout" class="active">Late Check-out</label>
                                    </div>
                                    <div class="input-field col s12 m3">
                                        <i class="material-icons prefix">wb_sunny</i>
                                        <input id="day_use" type="number" value="{{ $movimientoHabitacion->day_use }}" readonly>
                                        <label for="day_use" class="active">Day Use</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Row para la tabla de huéspedes (a la derecha) -->
                            <div class="col s12 m5">
                                <h5 class="center-align">Huéspedes</h5>
                                <div class="col s12" style="overflow-x: auto;">
                                    {{-- <label for="total" class="active">Huéspedes</label> --}}

                                    <table class="highlight striped centered data-table">
                                        <thead class="bg-primary">
                                            <tr style="color: white">
                                                <th>Nombre</th>
                                                <th>DNI / RUC</th>
                                                <th>Teléfono</th>
                                                <th>Dirección</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($huespedes as $huesped)
                                                <tr>
                                                    <td>{{ $huesped->persona->apellidos }}
                                                        {{ $huesped->persona->nombres }}</td>
                                                    <td>{{ $huesped->persona->personamaestro->nrodoc }}</td>
                                                    <td>{{ $huesped->persona->telefono }}</td>
                                                    <td>{{ $huesped->persona->direccion }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    
                                </div>
                                
                            </div>
                        </div>

                        <!-- Movimientos -->
                        <div class="row">
                            <h5 class="center-align">Movimientos Asociados</h5>
                            <div class="col s12">
                                <table class="highlight striped centered detalle-productos" id="data-table">
                                    <thead class="bg-primary">
                                        <tr style="color: white">
                                            <th>Descripción</th>
                                            <th>Precio compra</th>
                                            <th>Unidad</th>
                                            <th>Cantidad</th>
                                            <th>IGV</th>
                                            <th>Precio Unitario</th>
                                            <th>Monto</th>
                                            <th>Código</th>
                                            <th>Estado pago</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $detallesMovimientos = json_decode($detallesJson, true); @endphp
                                        @foreach ($detallesMovimientos as $detalle)
                                            <tr data-producto-id="{{ $detalle['producto_id'] }}">
                                                <td>{{ $detalle['descripcion'] }}</td>
                                                <td class="inpt-cantidad-mc">
                                                    {{ number_format($detalle['precio_compra'], 2) }}</td>
                                                <td>{{ $detalle['unidad'] }}</td>
                                                <td class="inpt-cantidad-mc cantidad">
                                                    <span
                                                        id="cantidad_{{ $detalle['producto_id'] }}">{{ $detalle['cantidad'] }}</span>
                                                </td>
                                                <td class="inpt-cantidad-mc">{{ number_format($detalle['igv'], 2) }}</td>
                                                <td class="inpt-cantidad-mc precio-unitario">
                                                    {{ number_format($detalle['precio_unitario'], 2) }}
                                                </td>
                                                <td class="inpt-cantidad-mc">
                                                    <span class="precio-total">
                                                        {{ number_format($detalle['total'], 2) }}
                                                    </span>
                                                </td>
                                                <td>{{ $detalle['codigo'] }}</td>
                                                <td>
                                                    <span
                                                        class="badge {{ $detalle['situacion'] == 'P' ? 'green white-text' : 'yellow black-text' }}">
                                                        {{ $detalle['situacion'] == 'P' ? 'Pagado' : 'Pendiente' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    @if ($totalProductosConsumidos > 0)
                                    <tfoot>
                                        <tr>
                                            <td colspan="6" class="right-align"
                                                style="font-weight: bold; text-align: right">Total Productos /
                                                Servicios</td>
                                            <td colspan="3" class="inpt-cantidad-mc" style="font-weight: bold;">
                                                S/. {{ number_format($totalProductosConsumidos, 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                @endif
                                </table>
                            </div>
                        </div>



                        <!-- Comentarios -->
                        <div class="row">
                            <br>
                            <div class="input-field col s6">
                                <i class="material-icons prefix">edit</i>
                                <textarea id="comentario" name="comentario" class="materialize-textarea" readonly>{{ old('comentario', $movimientoHabitacion->comentario) }}</textarea>
                                <label for="comentario">Comentario</label>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
