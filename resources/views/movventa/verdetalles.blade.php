@extends('layouts.app')
@section('content')
    <div class="main-body">
        <div class="row">
            <div class="card main-content">

                <div class="card-body bg-white">
                    <div class="row padding-1 p-1">
                        <div class="col s12 m12 l7">
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
                                <span class="helper-text" id="tipodocumento_id_spn" data-error="" data-success=""></span>
                            </div>

                            <!-- Cliente -->
                            <div class="input-field col s12 m7">
                                <input type="hidden" name="persona_id" id="persona_id"
                                    value="{{ old('persona_id', isset($personaCreada) ? $personaCreada->id : $movventa->movimiento->persona_id ?? '26') }}">

                                <input type="text" id="persona_autocomplete"
                                    class="autocomplete validate @error('persona_id') invalid @enderror"
                                    placeholder="Seleccionar Persona" data-autocomplete-url="{{ route('autocomplete.personas') }}"
                                    value="{{ isset($personaCreada)
                                        ? "{$personaCreada->personamaestro->nrodoc} - {$personaCreada->apellidos} {$personaCreada->nombres}"
                                        : (old('persona_id') !== null
                                            ? $personaSeleccionada ?? ''
                                            : (isset($movventa) && isset($movventa->movimiento->persona)
                                                ? "{$movventa->movimiento->Persona->personamaestro->nrodoc} - {$movventa->movimiento->persona}"
                                                : '0 - VARIOS CLIENTES')) }}"
                                    {{ isset($personaCreada) ? '' : 'disabled' }}>

                                <label for="persona_autocomplete" class="active">{{ __('Persona') }}</label>

                                {!! $errors->first(
                                    'persona_id',
                                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                ) !!}
                            </div>


                           

                            <!-- Botones de Acción (Configurar, Pagar, etc.) -->
                            <div class="input-field col s12 m1">
                                <a onclick="Settings()" class="btn-floating waves-effect waves-light blue darken-2"
                                    data-tooltip="Más opciones">
                                    <i class="material-icons">settings</i>
                                </a>

                             
                            </div>

                            <div class="row without-tb flex-row">
                                <div class="col s12 m12 l12">

                                    <!-- Campo Vendedor -->
                                    <div class="input-field col s12 m8 settingsSection" style="display: none">
                                        <i class="fa-solid fa-user-tie prefix"></i>
                                        <select name="vendedor_id" id="vendedor_id" disabled
                                            class="validate @error('vendedor_id') invalid @enderror">
                                            @foreach ($vendedores as $vendedor)
                                                <option value="{{ $vendedor->id }}" 
{{ old('vendedor_id', $movventa->movimiento->responsable_id ?? '') == $vendedor->id ? 'selected' : '' }}>
{{ $vendedor->personamaestro->nrodoc }} - {{ $vendedor->apellidos }}
{{ $vendedor->nombres }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="vendedor_id">{{ __('Vendedor') }}</label>
                                        {!! $errors->first(
                                            'vendedor_id',
                                            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                        ) !!}
                                    </div>
                                    <!-- Campo Caja -->
                                    <div class="input-field col s12 m3 settingsSection" style="display: none">
                                        <select id="caja_id" name="caja_id" disabled> 
                                            @foreach ($cajas as $caja)
                                                <option value="{{ $caja->id }}"
{{ old('caja_id') == $caja->id ? 'selected' : '' }}>
{{ $caja->numero }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="caja_id">Caja</label>
                                    </div>

                                    <!-- Campo de Número de Comprobante -->

                                </div>

                            </div>

                            <!-- Campo Tipo de Detalle -->
                            <div class="input-field col s12 m2 settingsSection" style="display: none">
                                <select id="tipo_detalle_consumo" name="tipo_detalle" disabled>
                                    <option value="DETALLADO" selected>Detallado</option>
                                    <option value="GLOSA">Glosa</option>
                                </select>
                                <label for="tipo_detalle">Tipo de detalle</label>
                            </div>

                            <!-- Checkboxes de Opciones Adicionales -->
                            <div class="row settingsSection" style="display: none">
                                <div class="col s12 m3 l3">
                                    <label style="margin-left: 20px;">
                                        <input type="checkbox" id="por_consumo" name="por_consumo" disabled />
                                        <span>Por consumo</span>
                                    </label>
                                </div>
                                <div class="col s12 m6 l3">

                                    <label style="margin-left: 20px;">
                                        <input type="checkbox" id="retencion" name="retencion" disabled />
                                        <span>Retención</span>
                                    </label>
                                </div>

                                <div class="col s12 m6 l3">

                                    <label style="margin-left: 20px;">
                                        <input type="checkbox" id="detraccion" name="detraccion" disabled {{ old('detraccion', $movventa->detraccion ?? 'N') == 'S' || (isset($movventa) && floatval($movventa->porcentaje_detraccion) > 0) ? 'checked' : '' }} />
                                        <span>Detracción</span>
                                    </label>
                                </div>

                            </div>

                            @if((isset($movventa) && ($movventa->detraccion == 'S' || floatval($movventa->porcentaje_detraccion) > 0)))
                            <div class="row settingsSection" style="display: none; margin-top: 10px;">
                                <div class="col s12 m4">
                                    <div class="input-field" style="margin-top: 0;">
                                        <input type="text" value="{{ floatval($movventa->porcentaje_detraccion) }}%" disabled style="font-weight: 500;">
                                        <label class="active" style="font-weight: bold; color: #555;">Detracción (%)</label>
                                        <span class="helper-text grey-text text-darken-2" style="font-weight: 500;">Monto: {{ number_format(($movventa->total * floatval($movventa->porcentaje_detraccion)) / 100, 2) }}</span>
                                    </div>
                                </div>
                                <div class="col s12 m4">
                                    <div class="input-field" style="margin-top: 0;">
                                        <input type="text" value="{{ $movventa->cuenta_detraccion }}" disabled style="font-weight: 500;">
                                        <label class="active" style="font-weight: bold; color: #555;">Cuenta de detracción</label>
                                    </div>
                                </div>
                                <div class="col s12 m4">
                                    <div class="input-field" style="margin-top: 0;">
                                        <input type="text" value="{{ $movventa->codigo_bien_detraccion }}" disabled style="font-weight: 500;">
                                        <label class="active" style="font-weight: bold; color: #555;">Código de bien</label>
                                    </div>
                                </div>
                            </div>
                            @endif
                     
                            <!-- Campo JSON para Detalles -->
                            <input type="hidden" name="detalles_json" id="detalles_json"
                                value='{!! old('detalles_json', $detallesMovimientosJson ?? '[]') !!}' />
                            <!-- Detalle de Venta -->
                            <div class="row without-top">
                                <div class="col s12 m12">
                                    <table id="data-table" class="highlight striped centered detalle-productos">

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
                                                {{-- <th>Estado pago</th> --}}
                                                {{-- <th>Acciones</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($movventa->detMovventas ?? [] as $detalle)
                                                @php
                                                    $jsonProducto = json_decode($detalle->json_producto, true); // Decodificar el JSON del producto
                                                @endphp
                                                <tr data-producto-id="{{ $detalle['producto_id'] }}">
                                                    <td>{{ $detalle->descripcion }}</td>
                                                    <td class="inpt-cantidad-mc ">
                                                        {{ number_format($jsonProducto['precio_compra'] ?? 0, 2) }}</td>
                                                    <td>{{ $detalle->unidad->descripcion ?? 'Unidad' }}</td>
                                                    <td class="inpt-cantidad-mc ">
                                                        {{ number_format($detalle->cantidad, 0) }}</td>
                                                    <td class="inpt-cantidad-mc ">{{ $jsonProducto['igv'] ?? 0 }}</td>
                                                    <td class="inpt-cantidad-mc ">
                                                        {{ number_format($jsonProducto['precio_unitario'] ?? 0, 2) }}</td>
                                                    <td class="inpt-cantidad-mc ">{{ number_format($detalle->monto, 2) }}
                                                    </td>
                                                    <td>{{ $detalle->codigo }}</td>
                                               
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div>



                            <div class="col s12 m12 l7">
                       
                                <!-- Campo JSON para Detalles Movimientos -->
                                <input type="hidden" name="detalles_movimientos_json" id="detalles_movimientos_json"
                                    value="{{ old('detalles_movimientos_json', json_encode($detallesMovimientos ?? [])) }}">
                                <!-- Tabla de Medios de Pago -->
                                <div class="col s12 m12 l12 mb10 table-responsive">
                                    <table id="tabla-medios-pago" class="highlight striped centered data-table">
                                                                                <thead class="thead-custom">

                                            <tr style="color: white">
                                                <th>{{ __('Tipo') }}</th>
                                                <th>{{ __('Monto') }}</th>
                                                <th>{{ __('Medio de Pago') }}</th>
                                                <th>{{ __('Detalles') }}</th>
                                                <th>{{ __('Notas') }}</th>
                                                <th>{{ __('F. Venc') }}</th>

                                                <th>
                                               

                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody id="detalle-movcaja">
                                            @if (!empty($detallesMovimientos))
                                                @foreach ($detallesMovimientos as $detalle)
                                                    <tr onclick="editarMovimiento(this, event)">
                                                        <td><span
                                                                class="badge {{ $detalle['tipo'] === 'Pagado' ? 'green' : 'red' }} white-text">{{ $detalle['tipo'] }}</span>
                                                        </td>
                                                        <td class="inpt-cantidad-mc monto ">
                                                            {{ number_format($detalle['monto'], 2) }}</td>

                                                        <td class="medio-pago"
                                                            data-mediopago-id="{{ $detalle['mediopago_id'] }}">
                                                            {{ $detalle['medio_pago'] }}</td>
                                                        <td class="detalles">
                                                            {{ collect($detalle['detalles'])->map(function ($item) {
                                                                    // Obtener el valor del primer elemento del array asociativo
                                                                    return reset($item);
                                                                })->implode(' | ') }}
                                                        </td>
                                                        <td class="notas">{{ $detalle['notas'] }}</td>
                                                        <td class="fecha-vencimiento">
                                                            {{ $detalle['fechavencimiento'] ?? '' }}</td>

                                                        <td>
                                                           
                                                        </td>

                                                    </tr>
                                                @endforeach
                                            @else
                                              
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                            </div>

                            <!-- Opciones de Pago -->
                            <div class="col s12 m12 l5">
                                <!-- Total y Vuelto -->
                                <div class="input-field col s12 m6 l6">
                                    <div class="input-field">
                                        <i class="fa-solid fa-money-bills prefix"></i> <input type="number"
                                            id="vuelto" value="0.00" disabled>
                                        <label for="vuelto" class="active">Vuelto</label>
                                    </div>
                                </div>
                                <div class="input-field col s12 m6 l6">
                                    <div class="input-field">
                                        <input type="number" id="total" name="total"
                                            value="{{ $movventa->total ?? '0.00' }}" readonly>
                                        <label for="total" class="active">Total</label>
                                    </div>
                                </div>
                                <!-- Radio buttons para 'moneda' -->
                                <div class="input-field col s12 m4">

                                    <label>
                                        <input disabled type="radio" name="moneda" id="moneda_pen" value="PEN" checked />
                                        <span>S/.</span>
                                    </label>

                                    <label style="margin-left: 100px;">
                                        <input disabled type="radio" name="moneda" id="moneda_usd" value="USD" />
                                        <span>$</span>
                                    </label>
                                    {!! $errors->first('moneda', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
                                </div>
                            </div>

                        </div>

                        <div class="col s12 m12 l5">
                          
                         



                            <div class="row">
                             
                                <!-- Notas -->
                                <div class="input-field col s12">
                                    <i class="fa-solid fa-note-sticky prefix"></i>
                                    <textarea name="notas" id="notas_venta" class="materialize-textarea"></textarea>
                                    <label for="notas_venta">Notas a la venta</label>
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
                                    <span class="helper-text" id="det_numero_spn" data-error="" data-success=""></span>
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
                            <button type="button" class="modal-close btn darken-2"
                                onclick="agregarMovimientoAlDetalle()">
                                <i class="material-icons left">save</i>Guardar
                            </button>
                            <a href="#!" class="modal-close btn-flat">Cancelar</a>
                        </div>
                    </div>
                    <!-- Campo oculto para 'sucursal_id' -->
                    <input type="hidden" name="sucursal_id" value="{{ $sucursal_id }}">



                </div>
            </div>
        </div>
    </div>


@endsection

