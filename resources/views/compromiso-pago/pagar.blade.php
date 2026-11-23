@extends('layouts.app')
@section('content')
    <div class="main-body">
        <div class="row">
            <div class="card main-content">

                <div class="card-body bg-white">
                    <!-- Formulario sin action definido -->
                    <form id="form" class="form-retroceder" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="compromiso_id"
                            value="{{ $compromiso->id }}">
                        <!-- Campo oculto para la URL de acción -->
                        <input type="hidden" id="form-action-url"
                            value="{{ route('contrato.compromiso_pago.pagostore') }}">
                        <div class="row padding-1 p-1">
                            <div class="col s12 m12 l12">
                                <div class="input-field col s12 m3 center">
                                    <input type="hidden" id="tipodocumento_id" name="tipodocumento_id"
                                        value="{{ $tipoDocumentoPredeterminado->id ?? '' }}">
                                    @foreach ($tiposDocumento as $tipoDocumento)
                                        <button id="tipodocumento_id_{{ $tipoDocumento->id }}"
                                            class="btn btn-floating {{ $tipoDocumento->id == $tipoDocumentoPredeterminado->id ? 'bg-primary' : 'bg-secondary' }} white-text tooltipped mr5 tipodocumento"
                                            data-tooltip="{{ $tipoDocumento->nombre }}" type="button"
                                            onclick="cambiarTipoDocVenta({{ $tipoDocumento->id }}, this);"
                                            {{ isset($movventa) ? 'disabled' : '' }}>
                                            {{ strtoupper(substr($tipoDocumento->nombre, 0, 1)) }}
                                        </button>
                                    @endforeach
                                    <span class="helper-text" id="tipodocumento_id_spn" data-error=""
                                        data-success=""></span>
                                </div>

                                <!-- Campo de Inquilino con Autocomplete -->
                                <div class="input-field col s12 m5">
                                    <i class="material-icons prefix">person</i>
                                    <input type="hidden" name="persona_id" id="persona_id"
                                        value="{{ old('persona_id', $contrato->persona_id ?? '') }}">
                                    <input type="text" id="persona_autocomplete"
                                        class="autocomplete validate @error('persona_id') invalid @enderror"
                                        placeholder="Seleccione un arrendatario"
                                        data-autocomplete-url="{{ route('autocomplete.personas') }}"
                                        value="{{ isset($contrato->persona_id) ? "{$contrato->arrendatario->personamaestro->nrodoc} - {$contrato->arrendatario->apellidos} {$contrato->arrendatario->nombres}" : '' }}"
                                        autocomplete="off" required>
                                    <label for="persona_autocomplete" class="active">Arrendatario</label>
                                    {!! $errors->first(
                                        'persona_id',
                                        '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                    ) !!}
                                </div>

                                <div class="input-field col s12 m1">
                                    <!-- Botón para quitar inquilino -->
                                    <button class="btn red left tooltipped" id="btnQuitarPersona"
                                        data-tooltip="Deseleccionar inquilino" type="button" onclick="eliminarPersona();"
                                        style="border-radius: 99em; display: none; ">
                                        <i class="fa-solid fa-user-slash"></i>
                                    </button>

                                    <!-- Botón para agregar nuevo inquilino -->
                                    <button
                                        href="{{ route('cliente.create', [$sucursal->id ?? 1, 'CONTRATO']) }}?es_principal=true"
                                        class="btn teal right tooltipped btn-crear" type="button"
                                        data-tooltip="Agregar Nuevo Inquilino" id="btnNuevaPersona"
                                        data-target="modal-nueva-persona" style="border-radius: 99em;">
                                        <i class="material-icons">person_add</i>
                                    </button>
                                </div>

                              

                                <div class="row without-tb flex-row">
                                    <div class="col s12 m12 l12">

                                        <!-- Campo de Arrendador con Autocomplete -->
                                        <div class="input-field col s12 m5">
                                            <i class="material-icons prefix">person</i>
                                            <input type="hidden" name="arrendador_id" id="arrendador_id"
                                                value="{{ old('arrendador_id', $contrato->arrendador_id ?? '') }}">
                                            <input type="text" id="arrendador_autocomplete"
                                                class="autocomplete validate @error('arrendador_id') invalid @enderror"
                                                placeholder="Seleccione un arrendador"
                                                data-autocomplete-url="{{ route('autocomplete.personas') }}"
                                                value="{{ isset($contrato->arrendador_id) ? "{$contrato->arrendador->personamaestro->nrodoc} - {$contrato->arrendador->apellidos} {$contrato->arrendador->nombres}" : '' }}"
                                                autocomplete="off" required>
                                            <label for="arrendador_autocomplete" class="active">Arrendador</label>
                                            {!! $errors->first(
                                                'arrendador_id',
                                                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                            ) !!}
                                        </div>

                                        <div class="input-field col s12 m1">
                                            <!-- Botón para quitar arrendador -->
                                            <button class="btn red left tooltipped" id="btnQuitarArrendador"
                                                data-tooltip="Deseleccionar arrendador" type="button"
                                                onclick="eliminarArrendador();"
                                                style="border-radius: 99em; {{ isset($arrendadorSeleccionado) ? 'display: block;' : 'display: none;' }}">
                                                <i class="fa-solid fa-user-slash"></i>
                                            </button>

                                            <!-- Botón para agregar nuevo arrendador -->
                                            <button
                                                href="{{ route('cliente.create', [$sucursal->id ?? 1, 'CONTRATO']) }}?es_principal=true&tipo=ARRENDADOR"
                                                class="btn teal right tooltipped btn-crear" type="button"
                                                data-tooltip="Agregar Nuevo Arrendador" id="btnNuevoArrendador"
                                                data-target="modal-nuevo-arrendador" style="border-radius: 99em;">
                                                <i class="material-icons">person_add</i>
                                            </button>
                                        </div>

                                        <!-- Campo Caja -->
                                        <div class="input-field col s12 m4 settingsSection">
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
    <!-- Botón para dividir cuenta -->
    <button class="btn btn-floating blue white-text tooltipped mt20 ml10 truncate " id="btn-dividircuenta"
        data-tooltip="Dividir cuenta" type="button" onclick="openSplitBillArrModal()">
        <i class="fa-solid fa-scissors left"></i>
    </button>

                                    </div>

                                </div>

                                <!-- Campo JSON para Detalles -->
                        <input type="hidden" name="detalles_json" id="detalles_json"
                            value='@json(old("detalles_json", $detallesVenta ?? []))' />

                                <!-- Detalle de Venta -->
                                <div class="row without-top">
                                    <div class="col s12 m12">
                                        <table class="highlight striped centered detalle-productos" id="data-table">

                                            <thead class="thead-custom">
                                                <tr style="color: white">
                                                    <th>Descripción</th>
                                                    <th>P. compra</th>
                                                    <th>Unid.</th>
                                                    <th>Cant.</th>
                                                    <th>IGV</th>
                                                    <th>P. unit.</th>
                                                    <th>Total</th>
                                                    <th>Codigo</th>
                                                    <th>Estado pago</th>
                                                    <th class="center all dt-orderable-none" data-dt-column="10"
                                                        rowspan="1" colspan="1"><span class="dt-column-title">
                                                            <button class="btn btn-floating btn-small tooltipped white"
                                                                id="det_movventa-btn-agregar" style="display: none;"
                                                                data-position="bottom" data-delay="30"
                                                                data-tooltip="Agregar" type="button"
                                                                onclick="agregarGlosa();">
                                                                <i class="material-icons black-text">add</i>
                                                            </button>
                                                    </th>
                                                </tr>
                                            </thead>
                                           <tbody>
    @foreach ($detallesVenta as $detalle)
        @php
            $jsonProducto = is_array($detalle['json_producto']) ? $detalle['json_producto'] : json_decode($detalle['json_producto'], true);
        @endphp
        <tr onclick="editarDetalle(this, event)" data-producto-id="{{ $detalle['producto_id'] ?? ('-' . $loop->iteration) }}">
            <td>{{ $detalle['descripcion'] }}</td>
            <td><span class="inpt-cantidad-mc">{{ number_format($jsonProducto['precio_compra'] ?? 0, 2) }}</span></td>
            <td><span>{{ $detalle['unidad']['descripcion'] ?? 'ZZ' }}</span></td>
            <td><span class="inpt-cantidad-mc cantidad">{{ number_format($detalle['cantidad'], 0) }}</span></td>
            <td><span class="inpt-cantidad-mc igv">{{ $jsonProducto['igv'] ?? 0 }}</span></td>
            <td><span class="inpt-cantidad-mc precio-unitario">{{ number_format($detalle['precio_unitario'], 2) }}</span></td>
            <td><span class="inpt-cantidad-mc precio-total">{{ number_format($detalle['precio_unitario'], 2) }}</span></td>
            <td><span>{{ $detalle['codigo'] }}</span></td>
            <td>
                 <span
                                                                                class="badge {{ $detalle['situacion'] == 'P' ? 'green white-text' : 'yellow black-text' }}">
                                                                                {{ $detalle['situacion'] == 'P' ? 'Pagado' : 'Pendiente' }}
                                                                            </span>
            </td>
            <td>
                @if (($jsonProducto['situacion'] ?? 'N') !== 'N')
                    <button type="button" class="btn btn-floating btn-small tooltipped bd-primary white-text"
                        data-position="bottom" data-tooltip="Sumar"
                        onclick="actualizarRapidoDet_MovVenta({{ $detalle['producto_id'] ?? ('-' . $loop->iteration) }}, 1);">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-floating btn-small tooltipped bd-primary white-text"
                        data-position="bottom" data-tooltip="Restar"
                        onclick="actualizarRapidoDet_MovVenta({{ $detalle['producto_id'] ?? ('-' . $loop->iteration) }}, -1);">
                        <i class="material-icons">remove</i>
                    </button>
                    <button type="button" class="btn btn-floating btn-small red white-text tooltipped"
                        data-tooltip="Quitar"
                        onclick="quitarProductoDeTabla({{ $detalle['producto_id'] ?? ('-' . $loop->iteration) }});">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                @endif
            </td>
        </tr>
    @endforeach
</tbody>

                                        </table>
                                    </div>

                                </div>

                                <div class="col s12 m12 l7">
                                    <!-- Tipos de Medio de Pago -->
                                    <div class="row center-align">
                                        <div class="col s12 center">
                                            @php
                                                // Mapeo de iconos según el medio de pago
                                                $iconosMediosPago = [
                                                    1 => 'fa-money-bill-wave', // Efectivo
                                                    2 => 'fa-credit-card', // Tarjeta
                                                    3 => 'fa-comments-dollar', // Transferencia
                                                    4 => 'fa-piggy-bank', // Depósito
                                                    5 => 'fa-wallet', // Billetera digital
                                                ];
                                                $coloresMediosPago = [
                                                    1 => 'green', // Efectivo
                                                    2 => 'blue', // Tarjeta
                                                    3 => 'purple', // Transferencia
                                                    4 => 'orange', // Depósito
                                                    5 => 'pink', // Billetera digital
                                                ];
                                            @endphp

                                            @foreach ($medio_pagos as $medio_pago)
                                                <button
                                                    class="btn btn-floating {{ $coloresMediosPago[$medio_pago->id] ?? 'grey' }} white-text tooltipped mt10 mb10 mr10 btn-todo-det_movcaja truncate"
                                                    data-tooltip="{{ $medio_pago->descripcion }}" type="button"
                                                    onclick="actualizarTodoDet_MovCaja({{ $medio_pago->id }});">
                                                    <i
                                                        class="fa-solid {{ $iconosMediosPago[$medio_pago->id] ?? 'fa-question-circle' }}"></i>
                                                </button>
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


                                    <!-- Campo JSON para Detalles Movimientos -->
                                    <input type="hidden" name="detalles_movimientos_json" id="detalles_movimientos_json"
                                        value="{{ old('detalles_movimientos_json', json_encode($detallesMovimientos ?? [])) }}">
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
                                                            style=" border-radius: 50%; color: black; position: relative;"
                                                            onclick="agregarMovimiento()">
                                                            <i class="material-icons"
                                                                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">add</i>
                                                        </button>

                                                    </th>
                                                </tr>
                                            </thead>
<tbody id="detalle-movcaja">
    @if (!empty($detallesMovimientos))
        @foreach ($detallesMovimientos as $detalle)
            <tr onclick="editarMovimiento(this, event)">
                <td>
                    <span class="badge {{ $detalle['tipo'] === 'Pagado' ? 'green' : 'red' }} white-text">
                        {{ $detalle['tipo'] }}
                    </span>
                </td>
                <td class="inpt-cantidad-mc monto">
                    {{ $detalle['monto'] }}
                </td>
                <td class="medio-pago" data-mediopago-id="{{ $detalle['mediopago_id'] }}">
                    {{ $detalle['mediopago_nombre'] }}
                </td>
                <td class="detalles">
                    {{-- Si es array de arrays asociativos --}}
                    @if (is_array($detalle['detalles']))
                        {{ collect($detalle['detalles'])->map(fn($item) => reset($item))->implode(' | ') }}
                    @else
                        {{ $detalle['detalles'] }}
                    @endif
                </td>
                <td class="notas">{{ $detalle['notas'] }}</td>
                <td class="fecha-vencimiento">{{ $detalle['fecha_vencimiento'] ?? '' }}</td>
                <td>
                    <button type="button" class="btn btn-small red darken-2"
                            onclick="eliminarFila(this); event.stopPropagation();">
                        <i class="material-icons">remove</i>
                    </button>
                </td>
            </tr>
        @endforeach
    @endif
</tbody>

                                        </table>
                                    </div>

                                </div>

                                <!-- Opciones de Pago -->
                                <div class="col s12 m12 l5">

<div class="input-field col s12 m6 l6">
    <div class="input-field">
        <i class="fa-solid fa-money-bill prefix"></i>
        <input type="number"
               id="monto_pagado"
               name="monto_pagado"
               step="0.1"
               value="{{ $totalMovventas ?? '0.00' }}"
               readonly>
        <label for="monto_pagado" class="active">Monto Pagado</label>
    </div>
</div>

<div class="input-field col s12 m6 l6">
    <div class="input-field">
        <i class="fa-solid fa-money-check-dollar prefix"></i>
        <input type="number"
               id="totalapagar"
               name="monto_apagar"
               step="0.1"
               value="{{ ($compromiso->monto - $totalMovventas) ?? '0.00' }}"
               readonly>
        <label for="monto_apagar" class="active">Monto a Pagar</label>
    </div>
</div>

<div class="input-field col s12 m6 l6">
    <div class="input-field">
        <i class="fa-solid fa-money-bills prefix"></i>
        <input type="number"
               id="total_compromiso"
               name="total_compromiso"
               step="0.1"
               value="{{ $compromiso->monto ?? '0.00' }}"
               readonly>
        <label for="total_compromiso" class="active">Total Compromiso</label>
    </div>
</div>

                                    <!-- Radio buttons para 'moneda' -->
                                    <div class="input-field col s12 m4">

                                        <label>
                                            <input type="radio" name="moneda" id="moneda_pen" value="PEN"
                                                checked />
                                            <span>S/.</span>
                                        </label>

                                        <label style="margin-left: 100px;">
                                            <input type="radio" name="moneda" id="moneda_usd" value="USD" />
                                            <span>$</span>
                                        </label>
                                        {!! $errors->first('moneda', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
                                    </div>
                                </div>

                            </div>
                        </div>


                        <!-- Modal para Detalle de Caja -->
                        <div id="detalleModal" class="modal modal-bottom-sheet"
                            style="width: 100%; border-radius: 0%; bottom: 0;">
                            <div class="modal-content">
                                <div class="row without-top">

                                    <h5 class="grey-text text-darken-3 modal-title without-tb center"
                                        id="det_movcaja-submodal-title">
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
                                        <select id="medio_pago_detalle" name="medio_pago_detalle"
                                            onchange="mostrarSelects(this)">
                                            @foreach ($medio_pagos as $medio_pago)
<option value="{{ $medio_pago->id }}"
    data-descripcion="{{ $medio_pago->descripcion }}">
    {{ $medio_pago->descripcion }}
</option>
                                            @endforeach
                                        </select>
                                        <label for="medio_pago_detalle">Medio de Pago</label>
                                    </div>


                                    <!-- Select para P.O.S. -->
                                    <div class="input-field col s12 m2 custom-dropdown" id="pos_container"
                                        style="display: none;">
                                        <select id="pos_detalle" name="pos_detalle">
                                            @foreach ($pos as $posItem)
<option value="{{ $posItem->id }}">{{ $posItem->descripcion }}</option>
                                            @endforeach
                                        </select>
                                        <label for="pos_detalle">P.O.S.</label>
                                    </div>

                                    <!-- Select para Tarjeta -->
                                    <div class="input-field col s12 m3 custom-dropdown" id="tarjeta_container"
                                        style="display: none;">
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

                                    <div class="input-field col s12 m6 l2 mediopago-data mediopago-3 mediopago-4"
                                        id="det_numero" style="display: none">
                                        <input class="mediopago-3 mediopago-4" id="det_numero" name="det_numero"
                                            type="text">
                                        <label id="lblDet_numero" for="det_numero" class="">Nro. operación</label>
                                        <span class="helper-text" id="det_numero_spn" data-error=""
                                            data-success=""></span>
                                    </div>
                                    <!-- Select para Banco -->
                                    <div class="input-field col s12 m2 custom-dropdown" id="banco_container"
                                        style="display: none;">
                                        <select id="banco_detalle" name="banco_detalle">
                                            @foreach ($bancos as $banco)
<option value="{{ $banco->id }}">{{ $banco->descripcion }}</option>
                                            @endforeach
                                        </select>
                                        <label for="banco_detalle">Banco</label>
                                    </div>

                                    <!-- Select para Billetera Digital -->
                                    <div class="input-field col s12 m2 custom-dropdown" id="billetera_container"
                                        style="display: none;">
                                        <select id="billetera_detalle" name="billetera_detalle">
                                            @foreach ($billeteras as $billetera)
<option value="{{ $billetera->id }}">{{ $billetera->descripcion }}
</option>
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
                                <button type="button" class="modal-close btn darken-2"
                                    onclick="agregarMovimientoAlDetalle()">
                                    <i class="material-icons left">save</i>Guardar
                                </button>
                                <a href="#!" class="modal-close btn-flat">Cancelar</a>
                            </div>
                        </div>
                        <!-- Campo oculto para 'sucursal_id' -->
                        <input type="hidden" name="sucursal_id" value="{{ $sucursal_id }}">


                        <!-- Botones de Acción Final -->
                        <div class="row padding-1 p-1">
                            <div class="col s12 mt20 mt-2">
                                <div class="modal-footer right-align">
                                    <button type="submit" class="btn darken-2"><i
                                            class="material-icons right">print</i>PAGAR</button>
                                    <a href="#" onclick="back();"  class="modal-close waves-effect waves-green btn-flat">CANCELAR</a>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
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
            onclick="acceptSplitBillArrModal()">
            GUARDAR
        </button>
    </div>
</div>

        <!-- Fondo oscuro para el modal -->
        <div id="modal-overlay"
            style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1002;">
        </div>
@endsection

