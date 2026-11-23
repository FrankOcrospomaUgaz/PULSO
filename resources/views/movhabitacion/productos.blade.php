@extends('layouts.app')
@section('content')
    <div class="main-body">
        <div class="row">
            <div class="card main-content">

                <div class="card-body bg-white">
                    <form id="form" class="form-retroceder" enctype="multipart/form-data">
                        @csrf

                        <!-- Campo oculto para la URL de acción dinámica -->
                        <input type="hidden" id="form-action-url" value="{{ route('movhabitacion.storeProductos') }}">

                        <input type="hidden" id="switchState" name="switch_state" value="habitacion">
                        <input type="hidden" id="habitacion_id" name="habitacion_id" value="{{$id}}">

                        <div class="row">
                            <div class="col s12 center-align">
                                <div class="switch">
                                    <label>
                                        Agregar a Habitación
                                        <input type="checkbox" id="switchPago" onchange="togglePagoHabitacion()">
                                        <span class="lever"></span>
                                        Pago Caja
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row padding-1 p-1">
                            <div class="col s12 m12 l7" >
                                <div class="input-field col s12 m2 center pagoCajaSection"   style="display: none">
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
                                    <span class="helper-text" id="tipodocumento_id_spn" data-error="" data-success=""></span>
                                </div>
                        
                                <div class="input-field col s12 m3">
                                    <i class="material-icons prefix">meeting_room</i>
                                    <input id="habitacion" type="text" style="font-size: 32px !important;
    font-weight: bold;" value="{{ $movhabitacion->habitacion->numero }}" readonly>
                                    <label for="habitacion" class="active">Habitación</label>
                                </div>

                                <!-- Cliente -->
                                <div class="input-field col s12 m6">
                                    <input type="hidden" name="persona_id" id="persona_id"
                                        value="{{ old('persona_id', $clientePrincipal->id ?? '') }}">
                                    <input type="text" id="persona_autocomplete"
                                        class="autocomplete validate @error('persona_id') invalid @enderror"
                                        placeholder="Seleccionar Persona"
                                        data-autocomplete-url="{{ route('autocomplete.personas') }}"
                                        value="{{ old('persona_id') ? $personas->firstWhere('id', old('persona_id'))->nombres : ($clientePrincipal ? $clientePrincipal->personamaestro->nrodoc . ' - ' .  $clientePrincipal->apellidos . ' ' . $clientePrincipal->nombres : '') }}" disabled>
                                
                                    <label for="persona_autocomplete" class="active">{{ __('Persona') }}</label>
                                    {!! $errors->first(
                                        'persona_id',
                                        '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                    ) !!}
                                </div>
                                

                                <!-- Botón para quitar cliente -->
                                <div class="input-field col s12 m1">
                                    <button class="btn red left tooltipped" id="btnQuitarCliente"
                                        data-tooltip="Deseleccionar cliente" type="button" onclick="eliminarPersona();"
                                        style="border-radius: 99em"><i class="fa-solid fa-user-slash"></i></button>
                                        <button href="{{ route('persona.create', [$sucursal_id, 'VENTA', $habitacion->id ?? null]) }}" class="btn teal right tooltipped btn-crear" id="btnNuevaPersona" data-tooltip="Nueva persona" 
                                            type="button" style="display: none; border-radius: 99em">
                                            <i class="fa-solid fa-user-plus"></i>
                                        </button>
                                </div>

                                <div class="row without-tb flex-row pagoCajaSection"  style="display: none">
                                    <div class="col s12 m12 l12">

                                    <!-- Campo Vendedor -->
<div class="input-field col s12 m8">
    <i class="fa-solid fa-user-tie prefix"></i>
    <select name="vendedor_id" id="vendedor_id"
        class="validate @error('vendedor_id') invalid @enderror">
        @foreach ($vendedores as $vendedor)
            <option value="{{ $vendedor->id }}"
                {{ old('vendedor_id', $movventa->movimiento->responsable_id ?? '') == $vendedor->id ? 'selected' : '' }}>
                {{ $vendedor->personamaestro->nrodoc }} - {{ $vendedor->apellidos }}{{ $vendedor->nombres }}
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
<div class="input-field col s12 m4" >
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

                                    <!-- Botones de Acción -->
                                    {{-- <div class="input-field col s12 m2">
                                        <button class="btn-floating green darken-2 tooltipped" data-tooltip="Cortesía total"
                                            onclick="cortesiaTotal()">
                                            <i class="material-icons">handshake</i>
                                        </button>
                                        <button class="btn-floating orange darken-2 tooltipped"
                                            data-tooltip="Descuento total" onclick="descuentoTotal()">
                                            <i class="material-icons">percent</i>
                                        </button>
                                    </div> --}}
                                </div>
                                </div>

                                <!-- Campo Tipo de Detalle -->
                                <div class="input-field col s12 m3 pagoCajaSection"  style="display: none">
                                    <select id="tipo_detalle" name="tipo_detalle">
                                        <option value="DETALLADO" selected>Detallado</option>
                                        <option value="GLOSA">Glosa</option>
                                    </select>
                                    <label for="tipo_detalle">Tipo de detalle</label>
                                </div>

                                <!-- Checkboxes de Opciones Adicionales -->
                                <div class="row pagoCajaSection"  style="display: none">
                                    <div class="col s12 m6 l2">
                                        <label>
                                            <input type="checkbox" id="por_consumo" name="por_consumo" />
                                            <span>Por consumo</span>
                                        </label>
                                    </div>
                                    <div class="col s12 m6 l2">

                                        <label style="margin-left: 20px;">
                                            <input type="checkbox" id="retencion" name="retencion" disabled />
                                            <span>Retención</span>
                                        </label>
                                    </div>

                                    <div class="col s12 m6 l2">

                                        <label style="margin-left: 20px;">
                                            <input type="checkbox" id="detraccion" name="detraccion" disabled />
                                            <span>Detracción</span>
                                        </label>
                                    </div>

                                </div>
                                <input type="hidden" name="detalles_json" id="detalles_json" 
                                value='{!! old('detalles_json', $detallesMovimientosJson ?? "[]") !!}' />
                         
                                <div class="row without-top">
                                    <!-- Detalle de Venta -->
                                    <div class="col s12 m12">
                                        <!-- Contenedor para hacer la tabla responsive -->
                                        <div class="table-responsive">
                                            <table id="data-table" class="highlight striped centered detalle-productos">
                                                <thead class="bg-primary">
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
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($productosConsumidos as $producto)
                                                        <tr data-producto-id="{{ $producto['producto_id'] }}">
                                                            <td>{{ $producto['descripcion'] }}</td>
                                                            <td class="inpt-cantidad-mc">{{ number_format($producto['precio_compra'], 2) }}</td>
                                                            <td>{{ $producto['unidad'] }}</td>
                                                            <td class="inpt-cantidad-mc">
                                                                <span id="cantidad_{{ $producto['producto_id'] }}">{{ $producto['cantidad'] }}</span>
                                                            </td>
                                                            <td>{{ $producto['igv'] }}</td>
                                                            <td class="inpt-cantidad-mc">{{ number_format($producto['precio_unitario'], 2) }}</td>
                                                            <td class="inpt-cantidad-mc">
                                                                <span class="precio-total">
                                                                    {{ number_format($producto['total'], 2) }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $producto['codigo'] }}</td>
                                                            <td>
                                                                <span class="badge {{ $producto['situacion'] == 'P' ? 'green white-text' : 'yellow black-text' }}">
                                                                    {{ $producto['situacion'] == 'P' ? 'Pagado' : 'Pendiente' }}
                                                                </span>
                                                            </td>
                                                            @if ($producto['situacion'] !== 'P')
                                                            <td>
                                                                <button type="button" class="btn btn-floating btn-small tooltipped bd-primary white-text" 
                                                                    data-position="bottom" data-tooltip="Sumar" 
                                                                    onclick="actualizarRapidoDet_MovVenta({{ $producto['producto_id'] }}, 1);">
                                                                    <i class="fa-solid fa-plus"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-floating btn-small tooltipped bd-primary white-text" 
                                                                    data-position="bottom" data-tooltip="Restar" 
                                                                    onclick="actualizarRapidoDet_MovVenta({{ $producto['producto_id'] }}, -1);">
                                                                    <i class="material-icons">remove</i>
                                                                </button>
                                                                <button type="button" class="btn btn-floating btn-small red white-text tooltipped" 
                                                                    data-tooltip="Quitar" 
                                                                    onclick="quitarProductoDeTabla({{ $producto['producto_id'] }});">
                                                                    <i class="fa-solid fa-trash-can"></i>
                                                                </button>
                                                            </td>
                                                            @else
                                                            <td></td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div> <!-- Fin del contenedor table-responsive -->
                                    </div>
                                </div>
                                

                                <div class="col s12 m12 l7 pagoCajaSection"  style="display: none">
                                    <!-- Tipos de Medio de Pago -->
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

                                        </div>
                                    </div>
                                    <!-- Campo JSON para Detalles -->


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
                            <th>{{ __('Medio') }}</th>
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
                                    <td><span
                                            class="badge {{ $detalle['tipo'] === 'Pagado' ? 'green' : 'red' }} white-text">{{ $detalle['tipo'] }}</span>
                                    </td>
                                    <td class="inpt-cantidad-mc monto ">{{ number_format($detalle['monto'], 2) }}</td>

                                    <td class="medio-pago" data-mediopago-id="{{ $detalle['mediopago_id'] }}">
                                        {{ $detalle['medio_pago'] }}</td>
                                    <td class="detalles">
                                        {{ collect($detalle['detalles'])->map(function ($item) {
                                                // Obtener el valor del primer elemento del array asociativo
                                                return reset($item);
                                            })->implode(' | ') }}
                                    </td>
                                    <td class="notas">{{ $detalle['notas'] }}</td>
                                    <td class="fecha-vencimiento">{{ $detalle['fecha_vencimiento'] ?? '' }}</td>

                                    <td>
                                       
                                        <button type="button" class="btn btn-small red darken-2" onclick="eliminarFila(this); event.stopPropagation();" style=" border-radius: 50%; color: rgb(255, 255, 255); position: relative;">
                                                <i class="material-icons" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">remove</i>
                                            </button>
                                    </td>

                                </tr>
                            @endforeach
                        @else
                            <!-- Valores por defecto si no hay detalles existentes -->
                            <tr onclick="editarMovimiento(this, event)">
                                <td><span class="new badge green white-text" data-badge-caption="Pagado"></span></td>
                                <td class="inpt-cantidad-mc monto">0.00</td>
                                <td class="medio-pago" data-mediopago-id="1">Efectivo</td>
                                <td class="detalles"></td>
                                <td class="notas"></td>
                                <td class="fecha-vencimiento"></td>

                                <td>
                                    <button type="button" class="btn btn-small red darken-2" onclick="eliminarFila(this); event.stopPropagation();" style=" border-radius: 50%; color: rgb(255, 255, 255); position: relative;">
                                                <i class="material-icons" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">remove</i>
                                            </button>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
                                </div>
                 
                                <!-- Opciones de Pago -->
                                <div class="col s12 m12 l5" >
                                    <!-- Total y Vuelto -->
                                    <div class="input-field col s12 m6 l6 pagoCajaSection"  style="display: none">
                                        <div class="input-field">
                                            <i class="fa-solid fa-money-bills prefix"></i> <input type="number"
                                                id="vuelto" value="0.00" disabled>
                                            <label for="vuelto" class="active">Vuelto</label>
                                        </div>
                                    </div>
                                    <div class="input-field col s12 m6 l6">
                                        <div class="input-field">
                                            <input type="number" id="total" name="total"
                                                value="{{ old('total', isset($totalProductosConsumidos) ? number_format($totalProductosConsumidos, 2) : '0.00') }}" 
                                                readonly>
                                            <label for="total" class="active">Total</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Radio buttons para 'moneda' -->
                                    <div class="input-field col s12 m4 pagoCajaSection"  style="display: none">

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

                            <div class="col s12 m12 l5">
                                <div class="input-field col s10">
                                    <i class="fa-solid fa-bag-shopping prefix"></i>
                                    <input type="text" name="buscar_producto" id="producto_autocomplete"
                                        class="autocomplete validate" placeholder="Productos..."    
                                        data-autocomplete-url="{{ route('autocomplete.productos') }}"
                                        data-clasificacion="Bien"
                                        onfocus="activarAutocomplete()" />
                                    <input type="hidden" name="producto_id" id="producto_id">
                                    <label for="producto_autocomplete" class="active">{{ __('Productos') }}</label>
                                </div>


                                <div class="input-field col s2">
                                    <button type="button" class="btn darken-2 btn-small" onclick="agregarProducto()"
                                        style="border-radius: 99em">
                                        <i class="material-icons">add</i>
                                    </button>
                                </div>

                                <input type="hidden" name="detalles_productos_json" id="detalles_productos_json">
                                <div id="div-categoria-1" class="col s12">
                                    <div id="slider-categoria-1" class="slick-slider">
                                        @foreach ($categorias as $categoria)
                                            <div class="categoria-wrapper">
                                                <div class="div-btn-categoria">
                                                    <a class="btn btn-categoria"
                                                        title="{{ $categoria->descripcion }}"
                                                        id="btn-categoria-{{ $categoria->id }}"
                                                        nivel="1" 
                                                        onclick="cargarProductosPorCategoria('{{ $categoria->id }}', 'Bien')">
                                                        {{ $categoria->descripcion }}
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                
                                        <!-- Agregar espacios vacíos si hay menos de 6 categorías -->
                                        @for ($i = count($categorias); $i < 12; $i++)
                                            <div class="categoria-wrapper empty-slide"></div>
                                        @endfor
                                    </div>
                                </div>
                                

                                <div class="input-field col s12">
                                    <div class="row products-container">
                                        <!-- Los productos se cargarán aquí dinámicamente -->
                                    </div>

                                    <!-- Notas -->
                                    <div class="input-field col s12">
                                        <i class="fa-solid fa-note-sticky prefix"></i>
                                        <textarea name="notas" id="notas_venta" class="materialize-textarea"></textarea>
                                        <label for="notas_venta">Notas a la venta</label>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Campo oculto para 'sucursal_id' -->
                        <input type="hidden" name="sucursal_id" value="{{ $sucursal_id }}">


                        <!-- Botones de Acción Final -->
                        <div class="row padding-1 p-1">
                            <div class="col s12 mt20 mt-2">
                                <div class="modal-footer right-align">
                                    <button type="submit" class="btn darken-2"><i
                                            class="material-icons left">save</i>Guardar</button>
                                    <a href="#" class="modal-close waves-effect waves-green btn-flat">CANCELAR</a>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para Detalle de Caja -->
    <div id="detalleModal" class="modal bottom-sheet" tabindex="0" style="z-index: 1003; display: none;">
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
            <a href="{{ route('movcaja.index') }}" class=" waves-effect waves-green btn-flat btn-cargar-crud" data-icono="fa-solid fa-cash-register" data-titulo="Caja chica">
                Corregir
            </a>       
        </div>
    </div>
@endsection

