

<div class="row padding-1 p-1">
    <div class="col s12 m12 l7">
        <div class="input-field col s10 m3 center">
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

        <!-- Botones de Acción (Configurar, Pagar, etc.) -->
        <div class="input-field col s2 m1 right-align" id="settings-btn-container">
            <a onclick="Settings()" class="btn-floating waves-effect waves-light blue darken-2"
                data-tooltip="Más opciones" {{ isset($movventa) ? 'disabled' : '' }}>
                <i class="material-icons">settings</i>
            </a>
        </div>

        <!-- Cliente -->
        <div class="input-field col s12 m8 persona-wrapper">

            <div class="persona-flex">
                <input type="text" id="persona_autocomplete"
                    class="autocomplete validate @error('persona_id') invalid @enderror" placeholder="Seleccionar Persona"
                    autocomplete="new-password" autocapitalize="off" autocorrect="off" spellcheck="false"
                    data-lpignore="true" data-form-type="other"
                    data-autocomplete-url="{{ route('autocomplete.personas') }}"
                    value="{{ isset($personaCreada)
                        ? "{$personaCreada->personamaestro->nrodoc} - {$personaCreada->apellidos} {$personaCreada->nombres}"
                        : (old('persona_id') !== null
                            ? $personaSeleccionada ?? ''
                            : (isset($movventa) && isset($movventa->movimiento->persona)
                                ? "{$movventa->movimiento->Persona->personamaestro->nrodoc} - {$movventa->movimiento->persona}"
                                : '0 - VARIOS CLIENTES')) }}"
                    {{ isset($personaCreada) ? '' : 'disabled' }}>
                <button class="btn red left tooltipped" id="btnQuitarCliente" data-tooltip="Deseleccionar cliente"
                    type="button" onclick="eliminarPersona();" style="border-radius: 99em"><i
                        class="fa-solid fa-user-slash"></i></button>
                <button href="{{ route('cliente.create', [$sucursal_id, 'VENTA']) }}?es_principal=true"
                    class="btn teal right tooltipped btn-crear-cliente" data-tooltip="Agregar Nuevo Cliente"
                    id="btnNuevaPersona" data-target="modal-nuevo-cliente" data-sucursal-id="{{ $sucursal_id }}"
                    onclick="abrirModalRegistro('principal');" style="display: none; border-radius: 99em">
                    <i class="material-icons">person_add</i>
                </button>

            </div>
            
            <input type="hidden" name="persona_id" id="persona_id"
                value="{{ old('persona_id', isset($personaCreada) ? $personaCreada->id : $movventa->movimiento->persona_id ?? '26') }}">
            <label for="persona_autocomplete" class="active">{{ __('Cliente') }}</label>

            {!! $errors->first(
                'persona_id',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
     
            
        </div>
        <div class="row without-tb flex-row">
            <div class="col s12 m12 l12">

                <!-- Campo Vendedor -->
                <div class="input-field col s12 m8 settingsSection" style="display: none">
                    <i class="fa-solid fa-user-tie prefix"></i>
                    <select name="vendedor_id" id="vendedor_id"
                        class="validate @error('vendedor_id') invalid @enderror">
                        @foreach ($vendedores as $vendedor)
<option value="{{ $vendedor->id }}"
{{ old('vendedor_id', $movventa->movimiento->responsable_id ?? '') == $vendedor->id ? 'selected' : '' }}>
{{ $vendedor->personamaestro->nrodoc }} - {{ $vendedor->apellidos }} {{ $vendedor->nombres }}
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
                <div class="input-field col s6 m4 settingsSection" style="display: none">
                    <select id="caja_id" name="caja_id">
                        @foreach ($cajas as $caja)
<option value="{{ $caja->id }}" {{ old('caja_id') == $caja->id ? 'selected' : '' }}>
{{ $caja->numero }}
</option>
                        @endforeach
                    </select>
                    <label for="caja_id">Caja</label>
                </div>
                <!-- Campo Tipo de Detalle -->
                <div class="input-field col s6 m3 settingsSection" style="display: none">
                    <select id="tipo_detalle_consumo" name="tipo_detalle" onchange="toggleBotonAgregar()">
                        <option value="DETALLADO" selected>Detallado</option>
                        <option value="GLOSA">Glosa</option>
                    </select>
                    <label for="tipo_detalle">Tipo de detalle</label>
                </div>

           
            </div>

        </div>


        <!-- Checkboxes y Opciones Adicionales -->
        <div class="row settingsSection" style="display: none; margin-top: 10px;">
            <div class="col s12 m12">
                <div class="card-panel grey lighten-4" style="padding: 12px 15px; border-radius: 8px; border: 1px solid #e0e0e0; margin: 0 0 15px 0;">
                    <div class="row" style="margin-bottom: 0;">
                        <div class="col s12 m4">
                            <label style="margin-top: 5px; display: inline-block;">
                                <input type="checkbox" id="por_consumo" name="por_consumo" />
                                <span>Por consumo</span>
                            </label>
                        </div>
                        <div class="col s12 m4">
                            <label style="margin-top: 5px; display: inline-block;">
                                <input type="checkbox" class="filled-in" id="detraccion_checkbox" name="detraccion" value="S"
                                    {{ old('detraccion', $movventa->detraccion ?? 'N') == 'S' || (isset($movventa) && floatval($movventa->porcentaje_detraccion) > 0) ? 'checked' : '' }}
                                    onchange="toggleDetraccionFields();" />
                                <span style="font-weight: 600; color: #333;">Detracción</span>
                            </label>
                        </div>
                    </div>

                    <!-- Campos de Detracción (Coincide exacto con la imagen de referencia) -->
                    <div id="detraccion_fields_container" class="row" style="margin-top: 15px; margin-bottom: 0; {{ old('detraccion', $movventa->detraccion ?? 'N') == 'S' || (isset($movventa) && floatval($movventa->porcentaje_detraccion) > 0) ? 'display: flex; flex-wrap: wrap;' : 'display: none;' }}">
                        <div class="input-field col s12 m4" style="margin-top: 10px;">
                            <input type="number" step="0.01" min="0" max="100" id="porcentaje_detraccion" name="porcentaje_detraccion"
                                value="{{ old('porcentaje_detraccion', isset($movventa) && floatval($movventa->porcentaje_detraccion) > 0 ? floatval($movventa->porcentaje_detraccion) : '0') }}"
                                oninput="calcularMontoDetraccion();" placeholder="0" style="font-size: 2.2rem; font-weight: bold; color: #222; margin-bottom: 5px;">
                            <label for="porcentaje_detraccion" class="active" style="font-weight: bold; color: #666;">Detracción (%)</label>
                            <span class="helper-text grey-text text-darken-2" id="monto_detraccion_helper" style="font-weight: 500; font-size: 0.95rem; display: block; margin-top: 4px;">Monto: 0</span>
                        </div>
                        <div class="input-field col s12 m4" style="margin-top: 10px;">
                            <input type="text" id="cuenta_detraccion" name="cuenta_detraccion"
                                value="{{ old('cuenta_detraccion', $movventa->cuenta_detraccion ?? '') }}" placeholder="" style="font-weight: 500; font-size: 1.1rem;">
                            <label for="cuenta_detraccion" class="active" style="font-weight: bold; color: #666;">Cuenta de detracción</label>
                        </div>
                        <div class="input-field col s12 m4" style="margin-top: 10px;">
                            <input type="text" id="codigo_bien_detraccion" name="codigo_bien_detraccion"
                                value="{{ old('codigo_bien_detraccion', $movventa->codigo_bien_detraccion ?? '') }}" placeholder="" style="font-weight: 500; font-size: 1.1rem;">
                            <label for="codigo_bien_detraccion" class="active" style="font-weight: bold; color: #666;">Código de bien</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
  
        <!-- Campo JSON para Detalles -->
        <input type="hidden" name="detalles_json" id="detalles_json" value='{!! old('detalles_json', $detallesMovimientosJson ?? '[]') !!}' />
        <input type="text" name="numero_movimiento_manual" id="numero_movimiento_manual"
            value="{{ old('numero_movimiento_manual', (isset($movventa) && isset($movventa->movimiento)) ? str_pad((string) (int) $movventa->movimiento->numero, 8, '0', STR_PAD_LEFT) : '0') }}"
            style="display: none;">
        <!-- Detalle de Venta -->
        <div class="row without-top">
            <div class="col s12 m12">
                <div class="table-wrapper">
                    <div class="table-scroll" style="overflow-x: auto;">
                        <table class="highlight striped centered detalle-productos" id="data-table"
                            style="width: max-content; min-width: 100%;">

                    <thead class="thead-custom">
                        <tr style="color: white">
                            <th class="no-hide">Descripción</th>
                            <th>P. compra</th>
                            <th class="no-hide">Unid.</th>
                            <th class="no-hide">Cant.</th>
                            <th class="no-hide">IGV</th>
                            <th class="no-hide">P. unit.</th>
                            <th class="no-hide">Total</th>
                            <th>Codigo</th>
                            <th>Estado pago</th>
                            <th class="center all dt-orderable-none" data-dt-column="10" rowspan="1" colspan="1">
                                <span class="dt-column-title">
                                    <button class="btn btn-floating btn-small tooltipped white"
                                        id="det_movventa-btn-agregar" style="display: none;" data-position="bottom"
                                        data-delay="30" data-tooltip="Agregar" type="button"
                                        onclick="agregarGlosa();">
                                        <i class="material-icons black-text">add</i>
                                    </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movventa->detMovventas ?? [] as $detalle)
                            @php
                                $jsonProducto = json_decode($detalle->json_producto, true); // Decodificar el JSON del producto
                            @endphp
                            <tr onclick="editarDetalle(this, event)"
                                data-producto-id="{{ $detalle->producto_id ?? ('-' . $loop->iteration) }}"
                                data-igv-id="{{ $detalle->igv_id ?? '' }}"
                                data-igv-porcentaje="{{ optional($detalle->igv)->porcentaje_igv ?? 0 }}">
                                <td>{{ $detalle->descripcion }}</td>
                                <td><span
                                        class="inpt-cantidad-mc">{{ number_format($jsonProducto['precio_compra'] ?? 0, 2) }}</span>
                                </td>
                                <td><span>{{ $detalle->unidad->descripcion ?? 'Unidad' }}</span></td>
                                <td><span
                                        class="inpt-cantidad-mc cantidad">{{ number_format($detalle->cantidad, 0) }}</span>
                                </td>
                                <td><span class="inpt-cantidad-mc igv">{{ number_format($detalle->monto * (optional($detalle->igv)->porcentaje_igv / 100) ?? 0 , 2)}}</span></td>
                                <td><span
                                        class="inpt-cantidad-mc precio-unitario">{{ number_format($detalle->monto, 2) }}</span>
                                </td>
                                <td><span
                                        class="inpt-cantidad-mc precio-total">{{ number_format($detalle->monto, 2) }}</span>
                                </td>
                                <td><span>{{ $detalle->codigo }}</span></td>

                                <td>
                                    <span class="new badge green white-text" data-badge-caption="Pagado">
                                        
                                    </span>
                                </td>
                                <td>
                                    @isset($jsonProducto['situacion'])
                                        @if ($jsonProducto['situacion'] !== 'N')
                                            <button type="button"
                                                class="btn btn-floating btn-small tooltipped bd-primary white-text"
                                                data-position="bottom" data-tooltip="Sumar"
                                                onclick="actualizarRapidoDet_MovVenta({{ $detalle->producto_id ?? ('-' . $loop->iteration) }}, 1);">
                                                <i class="fa-solid fa-plus"></i>
                                            </button>
                                            <button type="button"
                                                class="btn btn-floating btn-small tooltipped bd-primary white-text"
                                                data-position="bottom" data-tooltip="Restar"
                                                onclick="actualizarRapidoDet_MovVenta({{ $detalle->producto_id ?? ('-' . $loop->iteration) }}, -1);">
                                                <i class="material-icons">remove</i>
                                            </button>
                                            <button type="button"
                                                class="btn btn-floating btn-small red white-text tooltipped"
                                                data-tooltip="Quitar"
                                                onclick="quitarProductoDeTabla({{ $detalle->producto_id ?? ('-' . $loop->iteration) }});">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        @endif
                                    @endisset


                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <div class="col s12 m12 l8">
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



                    <button
                        class="btn btn-floating bg-primary white-text tooltipped mt10 mb10 btn-todo-det_movcaja truncate"
                        data-position="top" id="btn-reemplazar" data-tooltip="Reemplazar todo" type="button"
                        onclick="cambiarreemplazar();"><i class="fa-solid fa-right-left"></i></button>
                    <input type="hidden" id="modalidad-det_movcaja" value="reemplazar">
                    <!-- Botón adicional para agregar -->
                    <button style="display: none;"
                        class="btn btn-floating bg-primary white-text tooltipped mt10 mb10 btn-todo-det_movcaja truncate"
                        id="boton-agregar" data-tooltip="Agregar" type="button" onclick="cambiaragregar();"><i
                            class="fa-solid fa-plus"></i>
                    </button>
                </div>
            </div>


            <!-- Campo JSON para Detalles Movimientos -->
            <input type="hidden" name="detalles_movimientos_json" id="detalles_movimientos_json"
                value="{{ old('detalles_movimientos_json', json_encode($detallesMovimientos ?? [])) }}">
            <!-- Tabla de Medios de Pago -->
            <div class="row without-top">
                <div class="table-wrapper">
                    <div class="table-scroll" style="overflow-x: auto;">
                        <table class="highlight striped centered data-table-2" id="tabla-medios-pago"
                            style="width: max-content; min-width: 100%;">
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
                                            class="new badge {{ $detalle['tipo'] === 'Pagado' ? 'green' : 'red' }} white-text" data-badge-caption="{{ $detalle['tipo'] }}"></span>
                                    </td>
<td class="monto"><span class="inpt-cantidad-mc monto">{{ number_format($detalle['monto'], 2) }}</span></td>
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
                                <td class="monto"><span class="inpt-cantidad-mc monto">0.00</span></td>

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
            </div>

        </div>

        <!-- Opciones de Pago -->
        <div class="col s12 m12 l4">
            <!-- Total y Vuelto -->
            {{-- <div class="input-field col s12 m6 l6">
                <div class="input-field">
                    <i class="fa-solid fa-money-bills prefix"></i> <input type="number" id="vuelto"
                        value="0.00" disabled>
                    <label for="vuelto" class="active">Vuelto</label>
                </div>
            </div> --}}
            <div class="input-field col s12 m6 l12">
                <div class="input-field">
                      <i class="fa-solid fa-money-bills prefix"></i>
                    <input type="number" id="total" name="total" step="0.1"
                        value="{{ $movventa->total ?? '0.00' }}" readonly>
                    <label for="total" class="active">Total</label>
                </div>
            </div>
            <!-- Radio buttons para 'moneda' -->
            <div class="input-field col s12 m4">

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
        </div>

    </div>

    <div class="col s12 m12 l5">
        <div class="input-field col s10">
            <i class="fa-solid fa-bag-shopping prefix"></i>
            <input type="text" name="buscar_producto" id="producto_autocomplete" class="autocomplete validate"
               autocomplete="off"   {{ isset($movventa) ? 'disabled' : '' }}
                data-autocomplete-url="{{ route('autocomplete.productos') }}" onfocus="activarAutocomplete()" />
            <input type="hidden" name="producto_id" id="producto_id">
            <label for="producto_autocomplete" >{{ __('Producto o Servicio ') }}</label>
        </div>


        <div class="input-field col s2">
            <a href="{{ route('producto.create') }}" type="button" class="btn darken-2 tooltipped btn-crear"
                data-tooltip="Crear nuevo producto" style="border-radius: 99em"   {{ isset($movventa) ? 'disabled' : '' }}>
                <i class="material-icons">add</i>
            </a>
        </div>

        <input type="hidden" name="detalles_productos_json" id="detalles_productos_json">
        <div id="div-categoria-1" class="col s12">
            <div id="slider-categoria-1" class="slick-slider">
                @foreach ($categorias as $categoria)
                    <div class="categoria-wrapper">
                        <div class="div-btn-categoria">
                            <a class="btn btn-categoria tooltipped" data-tooltip="{{ $categoria->descripcion }}"
                                id="btn-categoria-{{ $categoria->id }}" nivel="1"   {{ isset($movventa) ? 'disabled' : '' }}
                                onclick="cargarProductosPorCategoria('{{ $categoria->id }}', 'Bien')">
                                {{ $categoria->descripcion }}
                            </a>
                        </div>
                    </div>
                @endforeach

                <!-- Si hay pocos elementos, añadir espacios vacíos -->
                @for ($i = count($categorias); $i < 12; $i++)
                    <div class="categoria-wrapper empty-slide"></div>
                @endfor
            </div>
        </div>

        <div class="row">
            <div class="row products-container">
                <!-- Los productos se cargarán aquí dinámicamente -->
            </div>

            <!-- Notas -->
            <div class="input-field col s12">
                <i class="fa-solid fa-note-sticky prefix"></i>
                <textarea name="notas" id="notas_venta" class="materialize-textarea validate @error('notas') invalid @enderror"
                    rows="1" style="height: auto; overflow-y: hidden; resize: vertical;"
                    oninput="this.style.height='';this.style.height=this.scrollHeight+'px'">{{ old('notas',    isset($movventa) ? $movventa->movimiento->comentario : '') }}</textarea>

                <label for="notas_venta" class="{{ isset($movventa) ? 'active' : '' }}">
                    Notas a la venta
                </label>
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
<!-- Campo oculto para 'sucursal_id' -->
<input type="hidden" name="sucursal_id" id="sucursal_id" value="{{ $sucursal_id }}">


<!-- Botones de Acción Final -->
<div class="row padding-1 p-1">
    <div class="col s12 mt20 mt-2">
        <div class="modal-footer right-align">
            <button type="submit" class="btn darken-2"><i class="material-icons right">print</i>   {{ isset($movventa) ? 'GUARDAR' : 'PAGAR' }} </button>
            <a href="#" onclick="back();" class="modal-close waves-effect waves-green btn-flat">CANCELAR</a>
        </div>
    </div>
</div>
