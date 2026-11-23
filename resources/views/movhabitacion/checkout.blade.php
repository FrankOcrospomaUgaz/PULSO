@extends('layouts.app')

@section('content')
                            <div class="main-body">
                                <div class="row">
                                    <div class="col s12">
                                        <div class="card main-content">
                                            @php
    use App\Models\MovhabitacionHabitacion;

    $habitaciones = MovhabitacionHabitacion::with('habitacion')
        ->where('movhabitacion_id', $movimientoHabitacion->id)
        ->whereIn('situacion', ['A', 'P'])
        ->get()

        ->map(function ($mh) {
            $habitacion = $mh->habitacion;

            if ($habitacion) {
                return [
                    'id' => $habitacion->id,
                    'numero' => $habitacion->numero,
                    'situacion' => $habitacion->situacion,
                    'situacion_detalle' => $mh->situacion,
                    'piso_id' => $habitacion->piso_id,
                    'tipohabitacion_id' => $habitacion->tipohabitacion_id,
                    'created_at' => $habitacion->created_at,
                    'updated_at' => $habitacion->updated_at,
                    'sucursal_id' => $habitacion->sucursal_id,
                    'deleted_at' => $habitacion->deleted_at,
                    'descripcion' => $habitacion->descripcion,
                    'precio' => $mh->tarifa,
                    'pivot' => [
                        'movhabitacion_id' => $mh->movhabitacion_id,
                        'habitacion_id' => $mh->habitacion_id,
                    ]
                ];
            }

            return null;
        })
        ->filter()
        ->values(); // Elimina los nulos y reindexa

    $telefonoadmin = App\Models\RelParametrosucursal::where('sucursal_id', $sucursal_id)
        ->whereHas('parametro', function ($query) {
            $query->where('descripcion', 'Celular de administración');
        })
        ->value('valor');
    $correoadmin = App\Models\RelParametrosucursal::where('sucursal_id', $sucursal_id)
        ->whereHas('parametro', function ($query) {
            $query->where('descripcion', 'E-mail de administración');
        })
        ->value('valor');

    $contraseñadescuento = App\Models\RelParametrosucursal::where('sucursal_id', $sucursal_id)
        ->whereHas('parametro', function ($query) {
            $query->where('descripcion', 'Contraseña para descuento de alojamiento');
        })
        ->value('valor');
            $modosuite = App\Models\RelParametrosucursal::where('sucursal_id', $sucursal_id)
        ->whereHas('parametro', function ($query) {
            $query->where('descripcion', 'Suite');
        })
        ->value('valor');
     

                                        @endphp

                                        <input type="hidden" id="habitacionesgrupales" name="habitacionesgrupales"
                                               value='@json($habitaciones)'>
                                        <input type="hidden" id="contraseñadescuento" name="contraseñadescuento" value={{ $contraseñadescuento }}>
   


                                            <form id="form" class="form-retroceder" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" id="form-action-url"
                                                    value="{{ route('movhabitacion.checkoutstore', [$habitacion->id, 'accion']) }}">
                                                <input type="hidden" id="accion" name="accion" value="">
                                                <input type="hidden" id="habitacion_id" name="habitacion_id" value="{{ $habitacion->id }}">
                                                <input type="hidden" id="switchState" name="switch_state" value="unico">
                                                <input type="hidden" id="vista" name="vista" value={{ $vista }}>
                                                @php
                                                    $checkoutProgramado = $movimientoHabitacion->fechasalida
                                                        ?? optional($movimientoHabitacion->movhabitacion_habitacion->first())->fechacheckout;
                                                @endphp
                                                <div id="late-checkout-meta"
                                                    data-fecha-salida="{{ $checkoutProgramado ? \Carbon\Carbon::parse($checkoutProgramado)->toIso8601String() : '' }}"
                                                    data-habitacion="{{ $habitacion->numero ?? $habitacion->id }}"
                                                    data-tarifa-hora="{{ $movimientoHabitacion->tarifa ?? $movimientoHabitacion->preciohabitacion ?? 0 }}"
                                                    data-modo-suite="{{ $modosuite ?? '' }}">
                                                </div>

                                                @if ($movimientoHabitacion->modo === 'G')
                                                    <div class="row">
                                                        <div class="col s12 center-align">
                                                            <div class="switch">
                                                                <label>
                                                                    Único
                                                                    <input type="checkbox" id="switchHoras" onchange="toggleModoCheckout()">
                                                                    <span class="lever"></span>
                                                                    Grupal
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                <!-- Contenedor de Habitaciones Seleccionadas -->
                                                <div class="row" id="habitacionesRow" style="display: none;">
                                                    <div class="col s12" id="habitacionContainer"></div>
                                                </div>
                                                <!-- Información de Habitación -->
                                                <div class="row">
                                                   <div class="input-field col s12 m2" id="habitacionContainer">
                                                    <i class="material-icons prefix">meeting_room</i>
                                                    <input id="habitacion" style="font-size: 32px !important; font-weight: bold;"
                                                        type="text"
                                                        value="{{ $habitacion->numero ?? '' }}"
                                                        readonly
                                                        class="right-align habitacion-input">
                                                    <label for="habitacion" class="active">Habitación</label>
                                                </div>


 @if ($movimientoHabitacion->modalidad == 'D')
                                                        <div class="input-field col s12 m2">
                                                            <i class="material-icons prefix">calendar_today</i>
                                                            <input id="dias" type="number" min='0' name="dias_totales"
                                                                value="{{ number_format($movimientoHabitacion->dias, 0) }}">
                                                            <label for="dias" class="active">Noches</label>
                                                        </div>
                                                        {{-- <div class="input-field col s12 m1 left-align">
                                                            <button type="button" class="btn waves-effect waves-light darken-2"
                                                                onclick="calcularTotal()">
                                                                Calcular Total
                                                            </button>
                                                        </div> --}}
                                                    @else
                                                        <div class="input-field col s12 m2">
                                                            <i class="material-icons prefix">access_time</i>
                                                            <input id="horas" type="number" min='0' name="horas_totales" readonly
                                                                value="{{ number_format($movimientoHabitacion->horas, 0) }}">
                                                            <label for="horas" class="active">Horas</label>
                                                        </div>
                                                    @endif
                                                    {{-- <div class="input-field col s12 m2" id="div-capacidad">
                                                        <i class="material-icons prefix">group</i>
                                                        <input id="capacidad" type="number" value="{{ $habitacion->tipohabitacion->capacidad }}"
                                                            readonly>
                                                        <label for="capacidad" class="active">Capacidad Habitación</label>
                                                    </div> --}}

                                                    <div class="input-field col s12 m2">
                                                        <i class="material-icons prefix">event_available</i>
                                                        <input id="fechaingreso" type="text" value="{{ $movimientoHabitacion->fechaingreso }}"
                                                            readonly>
                                                        <label for="fechaingreso" class="active">Fecha Ingreso</label>
                                                    </div>

                                                    <div class="input-field col s12 m2">
                                                        <i class="material-icons prefix">event_busy</i>
                                                        <input id="fechasalida" type="text" value="{{ $movimientoHabitacion->fechasalida }}"
                                                            readonly>
                                                        <label for="fechasalida" class="active">Fecha Salida</label>
                                                    </div>
                                                    <div class="input-field col s10 m4">
                                                        <i class="fa-solid fa-bag-shopping prefix"></i>
                                                        <input type="text" name="buscar_producto" id="producto_autocomplete"
                                                            class="autocomplete validate" 
                                                            data-autocomplete-url="{{ route('autocomplete.productos') }}"
                                                            data-clasificacion="Bien" onfocus="activarAutocomplete()"
                                                            autocomplete="off" />
                                                        <input type="hidden" name="producto_id" id="producto_id">
                                                        <label for="producto_autocomplete" >{{ __('Productos') }}</label>
                                                    </div>
                                                </div>

                                                <!-- Precio y Días -->
                                                <div class="row">
                                                   


                                                    <div class="input-field col s12 m2">
                                                        <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                                        <input id="preciohabitacion" type="number" value="{{ $movimientoHabitacion->tarifa }}"
                                                            readonly>
                                                        <label for="preciohabitacion" class="active">Tarifa</label>
                                                    </div>
                                                    @if ($movimientoHabitacion->modalidad == 'D')
                                                        <div class="input-field col s12 m2">
                                                            <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                                            <input id="totalalojamiento" type="number" name="totalalojamiento"
                                                                value="{{ $movimientoHabitacion->total }}"
                                                                readonly>
                                                            <label for="total" class="active">Alojamiento</label>
                                                        </div>
                                                    @else
                                                        <div class="input-field col s12 m2">
                                                            <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                                            <input id="totalalojamiento" type="number" name="totalalojamiento"
                                                                value="{{ $movimientoHabitacion->total }}" readonly>
                                                            <label for="total" class="active">Alojamiento</label>
                                                        </div>
                                                    @endif
                                                   
                                                      
                                                            <div class="input-field col s12 m2 total_pagado">
                                                                <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                                                @php
                                                                    // Sumar el total de todas las movventas asociadas a este movimientoHabitacion
                                                                    $totalMovventas = $movimientoHabitacion->movventas->sum('total');

                                                                    // Filtrar las det_movhabitacion con situación 'P'
                                                                    $totalDetMovhabitacion = $movimientoHabitacion->detmovhabitacion->filter(function($detalle) {
                                                                        return $detalle->situacion == 'P';
                                                                    })->sum('monto');
                                                                    $totalDetMovhabitacionN = $movimientoHabitacion->detmovhabitacion->filter(function($detalle) {
                                                                        return $detalle->situacion == 'N';
                                                                    })->sum('monto');


                                                                    // Restar los montos de los detalles de movhabitacion con situación 'P'
                                                                    $totalAlojamientoYaPagado = $totalMovventas - $totalDetMovhabitacion;
                                                                    $saldoTotalPendienteCheckout = max(
                                                                        0,
                                                                        $movimientoHabitacion->total - ($totalMovventas ?? 0) + $totalProductosConsumidos
                                                                    );
                                                             

                                                                @endphp
                                                                <input id="total_pagado" type="number" name="total_pagado"
                                                                    value="{{ $totalAlojamientoYaPagado}}"
                                                                    readonly>
                                                                <label for="total_pagado" class="active">Ya Pagado</label>
                                                            </div>
                                                         <div class="input-field col s12 m2 total">
    <i class="material-icons prefix" style="font-weight: bold;">S/.</i>

    <input id="totalapagaralojamiento" 
           type="number" 
           name="total_a_pagar_alojamiento"
           value="{{ $saldoTotalPendienteCheckout }}"
           readonly>

    <label for="total" class="active">A Pagar</label>
</div>


                                                   



                                                    <div class="input-field col s4">
                                                        <!-- Icono de servicio usando Material Icons -->
                                                        <i class="material-icons prefix">room_service</i>
                                                        <input type="text" name="buscar_servicio" id="servicio_autocomplete"
                                                            class="autocomplete validate" 
                                                            data-autocomplete-url="{{ route('autocomplete.productos') }}"
                                                            data-clasificacion="Servicio"
                                                            onfocus="activarAutocompleteServicio()" autocomplete="off" />
                                                        <input type="hidden" name="servicio_id" id="servicio_id">
                                                        <label for="producto_autocomplete">{{ __('Servicios') }}</label>
                                                    </div>

                                                </div>
                                                @if ($movimientoHabitacion->modalidad == 'D')
                                                    <div class="input-field col s12 m2">
                                                        <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                                        <input id="early" type="number" name="early"
                                                            value="{{ $movimientoHabitacion->early_checkin ?? '0' }}"  step="0.01">
                                                        <label for="early" class="active">Early checkin</label>
                                                    </div>

                                                    <div class="input-field col s12 m2">
                                                        <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                                        <input id="late" type="number" name="late"
                                                            value="{{ $movimientoHabitacion->late_checkout ?? '0' }}"  step="0.01">
                                                        <label for="late" class="active">Late checkout</label>
                                                        <small class="late-overstay-info grey-text"></small>
                                                    </div>

                                                    <div class="input-field col s12 m2">
                                                        <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                                        <input id="descuento" type="number" name="descuento" readonly
                                                            value="{{ $movimientoHabitacion->descuento ?? '0' }}" step="0.01">
                                                        <label for="descuento" class="active">Descuento</label>
                                                    </div>


    @php 
        $disabled = $movimientoHabitacion->situacion !== 'A' ? 'disabled style=opacity:0.5;cursor:not-allowed;' : '';
    @endphp

                                                    <div class="input-field col s12 m1 left-align">
                                                              <button class="btn green left tooltipped" type="button" style="margin-left:-5px;"
                                            data-tooltip="Calcular" onclick="calcularEarlylate()">
                                            <i class="material-icons">calculate</i>
                                        </button>
                                                    </div>
<div class="input-field col s12 m2">



    <!-- Botón de descuento total -->
    <button class="btn btn-floating orange white-text tooltipped mt20 ml10 truncate"
        data-tooltip="Descuento total"
        type="button"
        onclick="openMaintenanceModal();"
        {{ $disabled }}>
        <i class="fa-solid fa-percentage left"></i>
    </button>

    <!-- Botón para dividir cuenta -->
    <button class="btn btn-floating blue white-text tooltipped mt20 ml10 truncate"
        data-tooltip="Dividir cuenta"
        type="button"
        onclick="openSplitBillModal()"
        {{ $disabled }}>
        <i class="fa-solid fa-scissors left"></i>
    </button>

    <!-- Botón para ver el historial de ventas (siempre activo) -->
    <a href="{{ route('movhabitacion.historial-ventas', $movimientoHabitacion->id) }}" 
        class="btn btn-floating purple white-text tooltipped mt20 ml10 truncate btn-crear"
        data-tooltip="Historial de Ventas">
        <i class="fa-solid fa-history left"></i>
    </a>

</div>


                                                    <div class="input-field col s12 m3" id="descripcionAlojamientoContainer"  style="display: {{ $movimientoHabitacion->situacion !== 'P' ? 'block' : 'none' }};">
                                                        <i class="material-icons prefix">hotel</i>
                                                        <input id="descripcion_alojamiento" type="text" style="font-size: 20px; font-weight: 500;"
                                                            value="Alojamiento en la habitación {{ $habitacion->numero }}" name="descripcion_alojamiento">
                                                        <label for="descripcion_alojamiento" class="active">Descripción del Alojamiento</label>
                                                    </div>

                                                    {{-- @if ($movimientoHabitacion->movreserva)
                                                        <div class="input-field col s12 m2">
                                                            <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                                            <input id="adelanto" type="number" name="adelanto"
                                                                value="{{ $movimientoHabitacion->movreserva->adelanto ?? '0' }}" readonly  step="0.01">
                                                            <label for="descuento" class="active">A Cuenta</label>
                                                        </div>
                                                    @endif --}}
                                                @else
                                                      

                                                    <div id="precios-data" data-precios='{{ json_encode($jsonPrecioHoras) }}'></div>
                                                    <div class="input-field col s12 m2">
                                                        <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                                        <input id="early" type="number" name="early"
                                                            value="{{ $movimientoHabitacion->early_checkin ?? '0' }}"  step="0.01">
                                                        <label for="early" class="active">Early checkin</label>
                                                    </div>

                                                    <div class="input-field col s12 m2">
                                                        <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                                        <input id="late" type="number" name="late"
                                                            value="{{ $movimientoHabitacion->late_checkout ?? '0' }}"  step="0.01">
                                                        <label for="late" class="active">Late checkout</label>
                                                        <small class="late-overstay-info grey-text"></small>
                                                    </div>

                                                    <div class="input-field col s12 m2">
                                                        <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                                        <input id="descuento" type="number" name="descuento"
                                                            value="{{ $movimientoHabitacion->descuento ?? '0' }}"  step="0.01">
                                                        <label for="descuento" class="active">Descuento</label>
                                                    </div>


                                                    <div class="input-field col s12 m2 left-align">
                                                         <button class="btn green left tooltipped" type="button" style="margin-left:-5px;"
                                            data-tooltip="Calcular" onclick="calcularEarlylate()">
                                            <i class="material-icons">calculate</i>
                                        </button>
                                                    </div>
                                                    <div class="input-field col s12 m4" id="descripcionAlojamientoContainer">
                                                        <i class="material-icons prefix">hotel</i>
                                                        <input id="descripcion_alojamiento" type="text" style="font-size: 20px; font-weight: 500;"
                                                            value="Alojamiento en la habitación {{ $habitacion->numero }}" name="descripcion_alojamiento">
                                                        <label for="descripcion_alojamiento" class="active">Descripción del Alojamiento</label>
                                                    </div>

                                                @endif
                                                <input type="hidden" id="situacion" value="{{ $movimientoHabitacion->situacion }}">


                                                <input type="hidden" name="detalles_json" id="detalles_json" value='{!! old('detalles_json', $detallesJson ?? '[]') !!}' />

                                                <!-- Movimientos -->
                                                <div class="row">
                                                    <br>
                                                    <br>
                                                    <br>
                                                    <h5 class="center-align">Movimientos Asociados</h5>
                                                    <div class="col s12">
                                                        <table class="highlight striped centered detalle-productos" id="data-table">
                                                          <thead class="thead-custom">
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

                                                                    <th>Acciones</th>

                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <!-- Decodificar el JSON y recorrerlo -->
                                                                @php
    $detallesMovimientos = json_decode($detallesJson, true);
                                                                @endphp

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
                                                                            {{ number_format($detalle['precio_unitario'], 2) }}</td>
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
                                                                        @if ($detalle['situacion'] !== 'P')
                                                                            <td>
                                                                                <button type="button"
                                                                                    class="btn btn-floating btn-small tooltipped bd-primary white-text"
                                                                                    data-position="bottom" data-tooltip="Sumar"
                                                                                    onclick="actualizarRapidoDet_MovVenta({{ $detalle['producto_id'] }}, 1);">
                                                                                    <i class="fa-solid fa-plus"></i>
                                                                                </button>
                                                                                <button type="button"
                                                                                    class="btn btn-floating btn-small tooltipped bd-primary white-text"
                                                                                    data-position="bottom" data-tooltip="Restar"
                                                                                    onclick="actualizarRapidoDet_MovVenta({{ $detalle['producto_id'] }}, -1);">
                                                                                    <i class="material-icons">remove</i>
                                                                                </button>
                                                                                <button type="button"
                                                                                    class="btn btn-floating btn-small red white-text tooltipped"
                                                                                    data-tooltip="Quitar"
                                                                                    onclick="quitarProductoDeTabla({{ $detalle['producto_id'] }});">
                                                                                    <i class="fa-solid fa-trash-can"></i>
                                                                                </button>
                                                                            </td>
                                                                        @else
                                                                            <td></td>
                                                                        @endif

                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                            @if ($totalProductosConsumidos > 0)
                                                                <tfoot>
                                                                    <tr>
                                                                        <td colspan="6" class="right-align"
                                                                            style="font-weight: bold; text-align: right">Total Productos /
                                                                            Servicios</td>
                                                                        <td colspan="4" class="inpt-cantidad-mc" style="font-weight: bold;">
                                                                            S/. {{ number_format($totalProductosConsumidos, 2) }}
                                                                        </td>
                                                                    </tr>
                                                                </tfoot>
                                                            @endif

                                                        </table>
                                                    </div>
                                                </div>

                                                @php
    // Verificar si hay pendientes y calcular el total de pendientes
    $pendientes = collect($detallesMovimientos)->filter(function ($detalle) {
        return $detalle['situacion'] !== 'P'; // Si no está pagado, está pendiente
    });

    // Determinar si hay pendientes
    $hayPendientes = $pendientes->isNotEmpty();

    // Calcular el total de los montos pendientes
    $totalPendientes = $pendientes->sum(function ($detalle) {
        return ($detalle['cantidad'] ?? 0) * ($detalle['precio_unitario'] ?? 0);
    });
                                                @endphp
                                                <!-- Campo oculto para 'sucursal_id' -->
                                                <input type="hidden" name="sucursal_id" value="{{ $sucursal_id }}">


                                                <div class="row" id="documento"
                                                    style="display: {{ $hayPendientes || $movimientoHabitacion->situacion == 'A' ? 'block' : 'none' }};">

                                                    <h5 class="center-align">Datos del Documento</h5>
                                                    <!-- Tipos de Documento -->
                                                    <div class="input-field col s12 m2 center">
                                                        <input type="hidden" id="tipodocumento_id" name="tipodocumento_id"
                                                            value="{{ $tipoDocumentoPredeterminado->id ?? '' }}">
                                                        @foreach ($tiposDocumento as $tipoDocumento)
                                                            <button id="tipodocumento_id_{{ $tipoDocumento->id }}"
                                                                class="btn btn-floating {{ $tipoDocumento->id == $tipoDocumentoPredeterminado->id ? 'bg-primary' : 'bg-secondary' }} white-text tooltipped mr5 tipodocumento"
                                                                data-tooltip="{{ $tipoDocumento->nombre }}" type="button"
                                                                onclick="cambiarTipoDocVenta({{ $tipoDocumento->id }}, this);">
                                                                {{ strtoupper(substr($tipoDocumento->nombre, 0, 1)) }}
                                                            </button>
                                                        @endforeach
                                                        <span class="helper-text" id="tipodocumento_id_spn" data-error=""
                                                            data-success=""></span>
                                                    </div>

                                                    <!-- Radio buttons para 'moneda' -->
                                                    <div class="input-field col s12 m2">

                                                        <label>
                                                            <input type="radio" name="moneda" id="moneda_pen" value="PEN" checked />
                                                            <span>S/.</span>
                                                        </label>

                                                        <label style="margin-left: 100px;">
                                                            <input type="radio" name="moneda" id="moneda_usd" value="USD" />
                                                            <span>$</span>
                                                        </label>
                                                        {!! $errors->first('moneda', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
                                                    </div>
                                                    <!-- Campo Caja -->
                                                    <div class="input-field col s12 m2">
                                                        <select id="caja_id" name="caja_id">
                                                            @foreach ($cajas as $caja)
                                                                <option value="{{ $caja->id }}"
                                                                    {{ old('caja_id') == $caja->id ? 'selected' : '' }}>
{{ $caja->numero }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <label for="caja_id">Caja</label>
                                                    </div>


                                                    <!-- Total a Pagar -->
                                                    {{-- @if ($hayPendientes && $movimientoHabitacion->situacion !== 'A') --}}
                                                        <div class="input-field col s12 m2 to   tal">
                                                            <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                                            <input id="total" type="number" name="total"
                                                                value="{{ $saldoTotalPendienteCheckout }}" readonly>
                                                            <label for="total" class="active">Total a Pagar</label>
                                                        </div>
                                                    {{-- @else
                                                        <div class="input-field col s12 m2 total" style="display: none">
                                                            <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                                            <input id="total" type="number" name="total_a_pagar_productos"
                                                                value="{{ $movimientoHabitacion->total + $totalProductosAPagar }}" readonly>
                                                            <label for="total" class="active">Total a Pagar</label>
                                                        </div>
                                                    @endif --}}

                                                    <!-- Campo de Número de Comprobante -->
                                                    <div class="input-field col s12 m2" style="display: none">
                                                        <i class="material-icons prefix">description</i>
                                                        <input id="numero_comprobante" name="numero_comprobante" type="text"
                                                            value="{{ $numeroComprobante }}" readonly>
                                                        <label for="numero_comprobante" class="active">Nro Comprobante</label>
                                                    </div>
                                                    <!-- Cliente -->
                                                    <div class="input-field col s12 m3">
                                                        <i class="material-icons prefix">person</i>

                                                        <input type="hidden" name="persona_id" id="persona_id"
                                                            value="{{ old('persona_id',  request('personaCreadaId')  ?? ($clientePrincipal->id ?? '')) }}">

                                                        <input type="text" id="persona_autocomplete"
                                                            class="autocomplete validate @error('persona_id') invalid @enderror"
                                                            placeholder="Seleccionar Persona" data-autocomplete-url="{{ route('autocomplete.personas') }}"
                                                            value="{{ old('persona_id')
        ? $personas->firstWhere('id', old('persona_id'))->nombres
        : ($personaCreada
            ? "{$personaCreada->personamaestro->nrodoc} - {$personaCreada->apellidos} {$personaCreada->nombres}"
            : ($clientePrincipal
                ? $clientePrincipal->personamaestro->nrodoc .
                ' - ' .
                $clientePrincipal->apellidos .
                ' ' .
                $clientePrincipal->nombres
                : '')) }}"
                                                            disabled>

                                                        <label for="persona_autocomplete" class="active">{{ __('Persona') }}</label>
                                                        {!! $errors->first(
        'persona_id',
        '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
    ) !!}
                                                    </div>


                                                    <!-- Botón para quitar cliente -->
                                                    <div class="input-field col s12 m1">

                                                        <button
                                                            href="{{ route('persona.create', [$sucursal_id, 'CHECKOUT', $habitacion->id ?? null]) }}"
                                                            class="btn teal right tooltipped btn-crear" id="btnNuevaPersona"
                                                            data-tooltip="Nueva persona" type="button"
                                                            style="display: none; border-radius: 99em">
                                                            <i class="fa-solid fa-user-plus"></i>
                                                        </button>
                                                        <button class="btn red left tooltipped" id="btnQuitarCliente"
                                                            data-tooltip="Deseleccionar cliente" type="button" onclick="eliminarPersona();"
                                                            style="border-radius: 99em"><i class="fa-solid fa-user-slash"></i></button>
                                                    </div>

                                                </div>
                                                <div class="row">
                                                    {{-- @if ($hayPendientes || $movimientoHabitacion->situacion == 'A') --}}

                                                    <div class="col s12 m6">
                                                        <h5 class="center-align">Huéspedes</h5>
                                                        <div class="col s12" style="overflow-x: auto;">
                                                            <table class="highlight striped centered data-table">
                                                                <thead class="thead-custom">
                                                                    <tr style="color: white">
                                                                        <th>Nombre</th>
                                                                        <th>DNI / RUC</th>
                                                                        <th>Teléfono</th>
                                                                        <th>Dirección</th>
                                                                        {{-- <th> 
                                                                            <button
                                                                                href="{{ route('persona.create', [$sucursal_id, 'CHECKOUT', $habitacion->id ?? null]) }}"
                                                                                class="btn teal right tooltipped btn-crear" id="btnNuevaPersona"
                                                                                data-tooltip="Nueva persona" type="button"
                                                                                style="border-radius: 99em;  position: relative; margin-left: -86px;  ">
                                                                                <i class="fa-solid fa-user-plus"></i>
                                                                            </button></th>

                                                                    </tr> --}}
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($huespedes as $huesped)
                                                                        <tr>

                                                                            <td>
                                                                                {{ $huesped->persona->nombres ?? ' ' }} {{ $huesped->persona->apellidos ?? ' ' }}</td>
                                                                            <td>{{ $huesped->persona->personamaestro->nrodoc ?? ' ' }}</td>
                                                                            <td>{{ $huesped->persona->telefono ?? ' ' }} </td>
                                                                            <td>{{ $huesped->persona->direccion ?? ' ' }}</td>
                                                                            {{-- <td>
                                                                                <button type="button" class="btn btn-small red darken-2"
                                                                                    onclick="eliminarFila(this); event.stopPropagation();"
                                                                                    style="border-radius: 50%; color: rgb(255, 255, 255); position: relative;">
                                                                                    <i class="material-icons"
                                                                                        style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">remove</i>
                                                                                </button>
                                                                            </td> --}}
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <!-- Columna Izquierda: Tipo de Pago -->
                                                    <div class="col s12 m6" id="tipopago"
                                                        style="display: {{ $hayPendientes || $movimientoHabitacion->situacion == 'A' ? 'block' : 'none' }};">
                                                        <div class="row">
                                                            <h5 class="center-align">Tipo de Pago</h5>
                                                            <div class="row center-align">
                                                                <div class="col s12 center">
                                                                    @php
            $iconosMediosPago = [
                1 => 'fa-money-bill-wave',
                2 => 'fa-credit-card',
                3 => 'fa-comments-dollar',
                4 => 'fa-piggy-bank',
                5 => null, // Yape
                6 => null, // Plin
            ];

            $coloresMediosPago = [
                1 => 'green',
                2 => 'blue',
                3 => 'pink',
                4 => 'orange',
                5 => 'purple darken-2', // Yape
                6 => 'blue lighten-1',  // Plin
            ];
            $billeterasDigitales = App\Models\Billeteradigital::where('situacion', 1)
                ->orderBy('orden')
                ->get();

            @endphp


            @foreach ($medio_pagos as $medio_pago)

                {{-- 👉 CASO: BILLETERA DIGITAL --}}
                @if ($medio_pago->id == 5)

                @foreach ($billeterasDigitales as $billetera)

                @php
                    $nombre = strtolower($billetera->descripcion);
                @endphp

                @if (Str::contains($nombre, 'yape') || Str::contains($nombre, 'plin'))

                    <button
                        class="btn btn-floating
                            {{ Str::contains($nombre, 'yape') ? 'purple darken-2' : 'green' }}
                            white-text tooltipped mt10 mb10 mr10 btn-todo-det_movcaja"
                        data-tooltip="{{ ucfirst($billetera->descripcion) }}"
                        type="button"
                        onclick="actualizarTodoDet_MovCaja({{ $medio_pago->id }}, '{{ $billetera->descripcion }}')">

                        @if (Str::contains($nombre, 'yape'))
                            <img src="{{ asset('img/white-yape.png') }}" class="yape-icon" alt="Yape">
                        @else
                            <img src="{{ asset('img/white-plin.png') }}" class="yape-icon" alt="Plin">
                        @endif

                    </button>

                @endif
            @endforeach


                {{-- 👉 OTROS MEDIOS DE PAGO --}}
                @else
                    <button
                        class="btn btn-floating {{ $coloresMediosPago[$medio_pago->id] ?? 'grey' }} white-text tooltipped mt10 mb10 mr10 btn-todo-det_movcaja"
                        data-tooltip="{{ $medio_pago->descripcion }}"
                        type="button"
                        onclick="actualizarTodoDet_MovCaja({{ $medio_pago->id }});">

                        <i class="fa-solid {{ $iconosMediosPago[$medio_pago->id] ?? 'fa-question-circle' }}"></i>
                    </button>
                @endif

            @endforeach

                                                                        <button class="btn btn-floating bg-primary white-text tooltipped mt10 mb10 btn-todo-det_movcaja truncate" data-position="top" id="btn-reemplazar" data-tooltip="Reemplazar todo" type="button" onclick="cambiarreemplazar();"><i class="fa-solid fa-right-left"></i></button>
                                                                        <input type="hidden" id="modalidad-det_movcaja" value="reemplazar">
                                                                        <!-- Botón adicional para agregar -->
                                                                        <button style="display: none;"
                                                                            class="btn btn-floating bg-primary white-text tooltipped mt10 mb10 btn-todo-det_movcaja truncate"
                                                                            id="boton-agregar" data-tooltip="Agregar" type="button"
                                                                            onclick="cambiaragregar();"><i class="fa-solid fa-plus"></i>
                                                                        </button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        @php
                                                            $adelanto = $movimientoHabitacion->movreserva->adelanto ?? 0;
                                                            $medioPagoAdelantoId = $movimientoHabitacion->movreserva->mediopagoadelanto_id ?? 1; // Por defecto efectivo
                                                            $detallesMovimientos = json_decode($detallesMovimientosJson, true) ?? [];

                                                            // Mapeo de ID de medios de pago a nombres
                                                            $mediosPago = [
                                                                1 => 'Efectivo',
                                                                2 => 'Tarjeta',
                                                                3 => 'Transferencia',
                                                                4 => 'Depósito',
                                                                5 => 'Billetera Digital',
                                                            ];

                                                            // Mapeo de ID de medios de pago a detalles
                                                            $detallesMediosPago = [
                                                                1 => [], // Efectivo no tiene detalles adicionales
                                                                2 => ['Izipay', 'Visa - Débito'],
                                                                3 => ['BCP'],
                                                                4 => ['BCP'],
                                                                5 => ['Yape'],
                                                            ];

                                                            $medioPagoAdelanto = $mediosPago[$medioPagoAdelantoId] ?? 'Efectivo';
                                                            $detallesAdelanto = $detallesMediosPago[$medioPagoAdelantoId] ?? [];

                                                            $totalReserva = $movimientoHabitacion->movreserva?->total ?? 0;
                                                            $total = $totalReserva;

                                                            $montoNegativo = false;
                                                           
                                                         //   dd($totalMovventas);
                                                            $detallesMovimientos[0]['monto'] = $movimientoHabitacion->total - ($totalMovventas ?? 0) + $totalProductosConsumidos;
                                                            // 📤 Convertimos el array nuevamente a JSON
                                                            $detallesMovimientosJson = json_encode($detallesMovimientos);

                                                           
                                                           
                                                        @endphp


                                                        <!-- Campo JSON para Detalles Movimientos -->
                                                        <input type="hidden" name="detalles_movimientos_json" id="detalles_movimientos_json"
        value="{{ ($detallesMovimientosJson) }}">



                                                        <!-- Tabla de Medios de Pago -->
                                                        <div class="col s12 m12 l12 mb10" style="overflow-x: auto;">
                                                            <table class="highlight striped centered data-table-2" id="tabla-medios-pago">
                                                               <thead class="thead-custom">
                                                                    <tr style="color: white">
                                                                        <th>{{ __('Tipo') }}</th>
                                                                        <th>{{ __('Monto') }}</th>
                                                                        <th>{{ __('Medio de Pago') }}</th>
                                                                        <th>{{ __('Detalles') }}</th>
                                                                        <th>{{ __('Notas') }}</th>
                                                                        <th>{{ __('F. Venc') }}</th>
                                                                        <th>
                                                                            <button type="button" class="btn btn-small green white"
                                                                                id="addRowBtn" onclick="agregarMovimiento()"
                                                                                style="border-radius: 50%; color: black; position: relative;">
                                                                                <i class="material-icons"
                                                                                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                                                                                    add
                                                                                </i>
                                                                            </button>
                                                                        </th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="detalle-movcaja">
                                                                    @foreach ($detallesMovimientos as $detalle)
                                                                        <tr onclick="editarMovimiento(this, event)">
                                                                            <td><span class="new badge green white-text" data-badge-caption="{{ $detalle['tipo'] }}"></span>
                                                                            </td>
                                                                            <td class="inpt-cantidad-mc monto" id="total-display">
                                                                                {{ $detalle['monto']  }}
                                                                            </td>
                                                                            <td class="medio-pago"
                                                                                data-mediopago-id="{{ $detalle['mediopago_id'] ?? $medioPagoAdelantoId }}">
                                                                                {{ $detalle['medio_pago'] ?? $medioPagoAdelanto }}
                                                                            </td>
                                                                            <td class="detalles">
                                                                                @if (!empty($detalle['detalles']) && is_array($detalle['detalles']))
                                                                                    {{ implode('; ', $detalle['detalles']) }}
                                                                                @endif
                                                                            </td>
                                                                            <td class="notas">{{ $detalle['notas'] }}</td>
                                                                            <td class="fecha-vencimiento">
                                                                                {{ $detalle['fecha_vencimiento'] ?? '-' }}</td>
                                                                            <td>
                                                                                <button type="button" class="btn btn-small red darken-2"
                                                                                    onclick="eliminarFila(this); event.stopPropagation();"  style=" border-radius: 50%; color: rgb(255, 255, 255); position: relative;">
                                                                                    <i class="material-icons" style="position: absolute;top: 50%;left: 50%;transform: translate(-50%, -50%);">remove</i>
                                                                                </button>
                                                                                
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        {{-- Campo Total (actualizado) --}}
                                                        @if ($movimientoHabitacion->movreserva )
                                                        <div class="input-field col s12 m6 l3">
                                                            <i class="material-icons prefix" style="font-weight: bold;">S/.</i>
                                                            <input id="deuda" name="deuda" type="number" value="{{ $total - $totalAlojamientoYaPagado }}" readonly>
                                                            <label for="deuda" class="active">Saldo Global</label>
                                                        </div>
                                                    
                                                        @if (($movimientoHabitacion->total - ($totalMovventas ?? 0) + $totalProductosConsumidos) == 0.00)
                                                            @php
                                                                $montoNegativo = true;
                                                           
                                                            @endphp
                                                            
                                                        @endif
                                                        @endif





                                                    </div>
                                                  

                                                </div>

                                                <!-- Comentarios -->
                                                <div class="row">
                                                    <div class="input-field col s12">
                                                        <i class="material-icons prefix">edit</i>
                                                        <textarea id="comentario" name="comentario" class="materialize-textarea"></textarea>
                                                        <label for="comentario">Comentario</label>
                                                    </div>
                                                </div>

                                                <div class="row center-align">
                                                    @php
                                                        $saldoPendienteCheckoutCobrable = ($saldoTotalPendienteCheckout ?? 0) > 0.01;
                                                    @endphp
                                                    <!-- Botón para Cobrar y Generar Comprobante -->
                                                    <button type="submit" id="btn-cobrar" class="btn-large waves-effect waves-light blue"
                                                        onclick="handleAction('cobrar')"
                                                        {{ $saldoPendienteCheckoutCobrable && ($hayPendientes || $movimientoHabitacion->situacion == 'A') ? '' : 'disabled' }}>
                                                        <i class="material-icons left">receipt</i> Cobrar
                                                    </button>

                                                    <!-- Botón para Check-Out -->
                                                    <button type="submit" id="checkout-button"  class="btn-large waves-effect waves-light darken-2"
                                                        onclick="handleAction('checkout')"
                                                        {{ $saldoPendienteCheckoutCobrable && ($hayPendientes || $movimientoHabitacion->situacion == 'A') ? 'disabled' : '' }}>
                                                        <i class="material-icons left">check_circle</i> Check-Out
                                                    </button>

                                                    <!-- Botón para Cobrar y Generar Comprobante -->
                                                    @if ($saldoPendienteCheckoutCobrable)
                                                        <button type="submit" class="btn-large waves-effect waves-light green cobrar_checkout"
                                                            onclick="handleAction('cobrar_checkout')"
                                                            {{ $movimientoHabitacion->situacion == 'A' ? '' : 'disabled' }}>
                                                            <i class="material-icons left">exit_to_app</i> Cobrar y Check-Out
                                                        </button>
                                                    @endif


                                                </div>

                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Modal para Detalle de Caja -->
                            <div id="detalleModal" class="modal modal-bottom-sheet" style="width: 100%; border-radius: 0%; bottom: 0;">
                                <div class="modal-content">
                                    <div class="row without-top">

                                        <h5 class="grey-text text-darken-3 modal-title without-tb center" id="det_movcaja-submodal-title">
                                            <i class="fa-solid fa-money-bills"></i>
                                            Detalle de caja
                                        </h5>

                                    </div>
                                    <div class="row">
                                        <!-- Select para Tipo -->
                                        <div class="input-field col s12 m2">
                                            <select id="tipo_detalle">
                                                <option value="Pagado" selected>Pagado</option>
                                                <option value="Deuda">Deuda</option>
                                            </select>
                                            <label for="tipo_detalle">Tipo</label>
                                        </div>

                                        <!-- Campo para Monto -->
                                        <div class="input-field col s12 m2">
                                            <input type="number" id="monto_detalle" step="0.01" value="0.00">
                                            <label for="monto_detalle" class="active">Monto</label>
                                        </div>
                                        <!-- Select para Medio de Pago -->
                                        <div class="input-field col s12 m2 custom-dropdown">
                                            <select id="medio_pago_detalle" name="medio_pago_detalle" onchange="mostrarSelects(this)">
                                                @foreach ($medio_pagos as $medio_pago)
                                                    <option value="{{ $medio_pago->id }}" data-descripcion="{{ $medio_pago->descripcion }}">
                        {{ $medio_pago->descripcion }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <label for="medio_pago_detalle">Medio de Pago</label>
                                        </div>


                                        <!-- Select para P.O.S. -->
                                        <div class="input-field col s12 m2 custom-dropdown" id="pos_container" style="display: none;">
                                            <select id="pos_detalle" name="pos_detalle">
                                                @foreach ($pos as $posItem)
                        <option value="{{ $posItem->id }}">{{ $posItem->descripcion }}</option>
                                                @endforeach
                                            </select>
                                            <label for="pos_detalle">P.O.S.</label>
                                        </div>

                                        <!-- Select para Tarjeta -->
                                        <div class="input-field col s12 m3 custom-dropdown" id="tarjeta_container" style="display: none;">
                                            <select id="tarjeta_detalle" name="tarjeta_detalle">
                                                @foreach ($tarjetas as $tarjeta)
                                                    <option value="{{ $tarjeta->id }}">
                                                        {{ $tarjeta->descripcion }} - @if ($tarjeta->tipo === 'C')
                                                            Crédito
                                                        @else
                                                            Débito
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <label for="tarjeta_detalle">Tarjeta</label>
                                        </div>

                                        <div class="input-field col s12 m6 l2 mediopago-data mediopago-3 mediopago-4" id="det_numero"
                                            style="display: none">
                                            <input class="mediopago-3 mediopago-4" id="det_numero" name="det_numero" type="text">
                                            <label id="lblDet_numero" for="det_numero" class="">Nro. operación</label>
                                            <span class="helper-text" id="det_numero_spn" data-error="" data-success=""></span>
                                        </div>
                                        <!-- Select para Banco -->
                                        <div class="input-field col s12 m2 custom-dropdown" id="banco_container" style="display: none;">
                                            <select id="banco_detalle" name="banco_detalle">
                                                @foreach ($bancos as $banco)
                                                    <option value="{{ $banco->id }}">{{ $banco->descripcion }}</option>
                                                @endforeach
                                            </select>
                                            <label for="banco_detalle">Banco</label>
                                        </div>

                                        <!-- Select para Billetera Digital -->
                                        <div class="input-field col s12 m2 custom-dropdown" id="billetera_container" style="display: none;">
                                            <select id="billetera_detalle" name="billetera_detalle">
                                                @foreach ($billeteras as $billetera)
                                                    <option value="{{ $billetera->id }}">{{ $billetera->descripcion }}</option>
                                                @endforeach
                                            </select>
                                            <label for="billetera_detalle">Billetera Digital</label>
                                        </div>


                                        <!-- Campo para Notas -->
                                        <div class="input-field col s12 m4">
                                            <textarea id="notas_detalle" class="materialize-textarea"></textarea>
                                            <label for="notas_detalle">Notas</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer right-align">
                                    <button type="button" class="modal-close btn darken-2" onclick="agregarMovimientoAlDetalle()">
                                        <i class="material-icons left">save</i>Guardar
                                    </button>
                                    <a href="#!" class="modal-close btn-flat">Cancelar</a>
                                </div>
                            </div>
                            <div id="modal-error" class="modal s12 m6 l4">
                                <div class="modal-content center-align">
                                    <div class="swal2-icon swal2-warning swal2-icon-show"
                                        style="display: flex; justify-content: center; margin-bottom: 20px;">
                                        <div class="swal2-icon-content">!</div>
                                    </div>
                                    <h4 style="font-weight: bold;">Atención</h4>
                                    <p id="error-message"></p> <!-- Contenedor del mensaje de error -->
                                </div>
                                <div class="modal-footer" style="display: flex; justify-content: center; gap: 20px;">
                                    <a href="#" class="modal-close waves-effect waves-green btn-flat">
                                        Corregir
                                    </a>

                                </div>
                            </div>
                         <!-- Contenedor envolvente con condicional de visibilidad -->
                         <div id="contenedorAdvertenciaMonto" 
                         style="display: {{ $montoNegativo && $movimientoHabitacion->situacion !== 'P' ? 'block' : 'none' }};">

                            <!-- Fondo oscuro (Overlay) -->
                            <div id="overlay" style="
                                position: fixed;
                                top: 0;
                                left: 0;
                                width: 100%;
                                height: 100%;
                                background: rgba(0, 0, 0, 0.6);
                                z-index: 9998;
                            "></div>

                            <!-- Advertencia Permanente -->
                            <div id="modalAdvertenciaMonto" class="card-panel red lighten-4"
                                style="
                                    position: fixed;
                                    top: 50%;
                                    left: 50%;
                                    transform: translate(-50%, -50%);
                                    width: 50%;
                                    max-width: 500px;
                                    z-index: 9999;
                                    border-left: 6px solid #d32f2f;
                                    padding: 15px;
                                    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
                                ">
                                <div class="row" style="margin-bottom: 0;">
                                    <div class="col s2 center-align">
                                        <i class="material-icons red-text" style="font-size: 40px;">warning</i>
                                    </div>
                                    <div class="col s10">
                                        <h5 style="font-weight: bold; color: #d32f2f;">Atención</h5>
                                        <p style="color: #b71c1c;">El monto de esta habitacion es menor que el saldo a cuenta.
                                            Solo podrás cobrar de manera grupal el saldo restante
                                        </p>
                                        <div class="center-align">
                                            <a href="#!" onclick="activarModoGrupal()" class="waves-effect waves-light btn red darken-2">
                                                Ok
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div id="contenedorExcesoCheckout" style="display:none;">
                            <div id="overlayExcesoCheckout" style="
                                position: fixed;
                                top: 0;
                                left: 0;
                                width: 100%;
                                height: 100%;
                                background: rgba(0, 0, 0, 0.6);
                                z-index: 10000;
                            "></div>

                            <div id="modalExcesoCheckout" class="card-panel orange lighten-5" style="
                                position: fixed;
                                top: 50%;
                                left: 50%;
                                transform: translate(-50%, -50%);
                                width: 52%;
                                max-width: 560px;
                                z-index: 10001;
                                border-left: 6px solid #ef6c00;
                                padding: 18px;
                                box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
                            ">
                                <div class="row" style="margin-bottom: 0;">
                                    <div class="col s2 center-align">
                                        <i class="material-icons orange-text text-darken-3" style="font-size: 40px;">schedule</i>
                                    </div>
                                    <div class="col s10">
                                        <h5 style="font-weight: bold; color: #e65100;">Tiempo excedido</h5>
                                        <p style="color: #bf360c; margin-bottom: 8px;">
                                            Habitación <b id="exceso_habitacion_numero"></b> excedió
                                            <b id="exceso_tiempo_transcurrido"></b>.
                                            Se cobrará <b id="exceso_horas_cobrables"></b> adicional.
                                        </p>

                                        <div class="row without-bottom" style="margin-top: 8px;">
                                            <div class="input-field col s12 m6">
                                                <i class="material-icons prefix">attach_money</i>
                                                <input id="exceso_precio_hora" type="number" min="0" step="0.01">
                                                <label for="exceso_precio_hora" class="active">Monto por hora</label>
                                            </div>
                                            <div class="input-field col s12 m6">
                                                <i class="material-icons prefix">calculate</i>
                                                <input id="exceso_total" type="number" readonly>
                                                <label for="exceso_total" class="active">Total excedido</label>
                                            </div>
                                        </div>

                                        <div class="center-align">
                                            <a href="#!" id="btnAceptarExcesoCheckout" class="waves-effect waves-light btn orange darken-2">
                                                Aceptar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
        <!-- Modal Structure -->
        <div id="maintenance-modal" class="modal modal-large" style="z-index: 99999999">
            <div class="modal-content">
                <div class="row without-top">
                    <p class="grey-text text-darken-3 without-tb center" id="modal-title">
                        <i class="fa-solid fa-percentage left"></i> Aplicar descuento total
                    </p>
                </div>
                <div class="row without-tb" id="modal-form">
                    <form id="frmMantDescuento">
                        <div class="col s12">
                            <div class="row">
                                <div class="input-field col s12 m6 l2 offset-l3">
                                    <input autocomplete="off" class="inpt-cantidad-lc" id="total_actual" name="total_actual"
                                        type="text" value="10" oninput="validateDecimalNumbers(this);" inputmode="numeric"
                                        onchange="validateEmptyNumber(this);" disabled />
                                    <label class="active" for="total_actual">Total actual</label>
                                    <span class="helper-text" id="total_actual_spn" data-error="" data-success="">PEN</span>
                                </div>

                                <div class="input-field col s12 m6 l2">
                                    <input autocomplete="off" class="inpt-cantidad-lc submit-form" id="dcto_total_porcentaje"
                                        name="dcto_total_porcentaje" type="text" oninput="actualizarDescuento('PORCENTAJE');"
                                        inputmode="numeric" value="0" />
                                    <label class="active" for="dcto_total_porcentaje">Dcto. (%)</label>
                                    <span class="helper-text" id="dcto_total_porcentaje_spn" data-error=""
                                        data-success=""></span>
                                </div>

                                <div class="input-field col s12 m6 l2">
                                    <input autocomplete="off" class="inpt-cantidad-lc submit-form" id="dcto_total_monto"
                                        name="dcto_total_monto" type="text" oninput=" actualizarDescuento('MONTO');"
                                        inputmode="numeric" value="0" />
                                    <label class="active" for="dcto_total_monto">Dcto. (por monto)</label>
                                    <span class="helper-text" id="dcto_total_monto_spn" data-error="" data-success=""></span>
                                </div>
                                <input type="hidden" name="telefonoadmin" id="telefonoadmin" value='@json($telefonoadmin)'>
                                <input type="hidden" id="habitacion_numero" value="{{ $habitacion->numero }}">
                                <input type="hidden" name="correoadmin" id="correoadmin" value='@json($correoadmin)'>

                                <div class="col s12 m12 l4 offset-l2">
                                    <div class="input-group-field" style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div class="right-wrapper input-field" style="flex-grow: 1; margin: 0;">
                                            <input class="submit-form" id="codigo_aprobacion_movimiento"
                                                name="codigo_aprobacion_movimiento" type="password" autocomplete="off"
                                                style="margin: 0;" />
                                            <label id="codigo_aprobacion_movimiento_lbl" for="codigo_aprobacion_movimiento"
                                                style="left: 0;">
                                                Código de aprobación
                                            </label>
                                            <span class="helper-text" id="codigo_aprobacion_movimiento_spn" data-error=""
                                                data-success="">
                                                Ingrese el código o envíe uno temporal
                                            </span>
                                        </div>

                                        <button class="btn tooltipped green" id="btn-codigo-aprobacion-movimiento-whatsapp"
                                            data-tooltip="Solicitar código por WhatsApp" type="button"
                                            onclick="sendWhatsAppCodeForProcess();" style="height: 36px;"
                                            data-tooltip="Solicitar código por WhatsApp">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </button>

                                        <button class="btn tooltipped" id="btn-codigo-aprobacion-movimiento-email"
                                            data-tooltip="Solicitar código por e-mail" type="button"
                                            onclick="sendEmailCodeForProcess();" style="height: 36px;"
                                            data-tooltip="Solicitar código por e-email">
                                            <i class="fa-solid fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </div>


                                <div class="input-field col s12 m12 l4" style="margin-top: -1px">
                                    <textarea class="materialize-textarea" id="motivo" name="motivo" rows="1"
                                       ></textarea>
                                    <label for="motivo">Motivo</label>
                                    <span class="helper-text" id="motivo_spn" data-error="" data-success=""></span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal-footer">
                <button id="btn-cancel" type="button"
                    class="btn-flat waves-effect waves-dark btn-cancelar btn-mantenimiento grey-text text-darken-3"
                    onclick="cancelMaintenanceModal();">
                    CANCELAR
                </button>
                <button id="btn-save" type="button"
                    class="btn-flat waves-effect waves-light teal-text semibold btn-aceptar btn-mantenimiento"
                    onclick="aceptarMaintenanceModal()">
                    GUARDAR
                </button>
            </div>
        </div>

        <!-- Modal Structure for Dividir Cuenta -->
<div id="split-bill-modal" class="modal modal-large" style="z-index: 99999999">
    <div class="modal-content">
        <div class="row without-top">
            <p class="grey-text text-darken-3 without-tb center" id="modal-title">
                <i class="fa-solid fa-calculator left"></i> Dividir cuenta
            </p>
        </div>
        <div class="row without-tb" id="modal-form">
            <form id="frmSplitBill">
                <div class="col s12">
                    <div class="row">
                        <!-- Monto a Pagar -->
                        <div class="input-field col s12 m6 l4 offset-l4">
                            <input autocomplete="off" class="inpt-cantidad-lc" id="monto_a_pagar" name="monto_a_pagar"
                                type="text" value="0" oninput="validateDecimalNumbers(this);" inputmode="numeric"
                                onchange="validateEmptyNumber(this);" />
                            <label class="active" for="monto_a_pagar">Monto a pagar</label>
                            <span class="helper-text" id="monto_a_pagar_spn" data-error="" data-success="">
                                Se obtendrá una cuenta con un total igual o aproximado al ingresado
                            </span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-footer">
        <button id="btn-cancel" type="button"
            class="btn-flat waves-effect waves-dark btn-cancelar btn-mantenimiento grey-text text-darken-3"
            onclick="cancelSplitBillModal();">
            CANCELAR
        </button>
        <button id="btn-save" type="button"
            class="btn-flat waves-effect waves-light teal-text semibold btn-aceptar btn-mantenimiento"
            onclick="acceptSplitBillModal()">
            GUARDAR
        </button>
    </div>
</div>


        <!-- Fondo oscuro para el modal -->
        <div id="modal-overlay"
            style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1002;">
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
