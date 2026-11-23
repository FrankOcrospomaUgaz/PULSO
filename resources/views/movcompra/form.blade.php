<div class="row padding-1 p-1">
    <div class="col s12 m12 l7">
        <div class="row without-tb">
            <div class="input-field col s12 m4">
                @if (isset($movcompra->id))
                    <input type="hidden" name="tipodocumento_id" value="{{ $movcompra->movimiento->tipodocumento_id }}">
                @endif
                <select name="tipodocumento_id" id="tipodocumento_id" class="validate"
                    onchange="cambiarTipoDocCompra(this.value);" {{ isset($movcompra->id) ? 'disabled' : '' }}>
                    @foreach ($tiposDocumento as $tipoDocumento)
                        <option value="{{ $tipoDocumento->id }}"
                            data-abreviatura="{{ $tipoDocumento->abreviatura }}"
                            {{ ($tipoDocumentoPredeterminado->id ?? '') == $tipoDocumento->id ? 'selected' : '' }}>
                            {{ $tipoDocumento->nombre }}
                        </option>
                    @endforeach
                </select>
                <label for="tipodocumento_id">Tipo</label>
            </div>

            <div class="input-field col s6 m2">
                <input type="text" name="serie" id="serie_compra" value="{{ old('serie', $serieInput ?? '000') }}"
                    {{ isset($movcompra->id) ? 'readonly' : '' }}>
                <label for="serie_compra" class="active">Serie</label>
            </div>

            <div class="input-field col s6 m2">
                <input type="text" name="numero_movimiento_manual" id="numero_movimiento_manual"
                    value="{{ old('numero_movimiento_manual', $correlativo ?? '00000001') }}"
                    {{ isset($movcompra->id) ? 'readonly' : '' }}>
                <label for="numero_movimiento_manual" class="active">Número</label>
            </div>

            <div class="input-field col s12 m4">
                <input type="date" name="fecha_compra" id="fecha_compra"
                    value="{{ old('fecha_compra', isset($movcompra->movimiento) ? \Carbon\Carbon::parse($movcompra->movimiento->fecha)->format('Y-m-d') : date('Y-m-d')) }}">
                <label for="fecha_compra" class="active">Fecha de compra</label>
            </div>

            <div class="input-field col s12 m4">
                <select name="tipo_detalle" id="tipo_detalle_compra" onchange="toggleBotonAgregarCompra();">
                    <option value="DETALLADO" {{ old('tipo_detalle', $movcompra->tipo_detalle ?? 'DETALLADO') == 'DETALLADO' ? 'selected' : '' }}>Compra detallada</option>
                    <option value="GLOSA" {{ old('tipo_detalle', $movcompra->tipo_detalle ?? '') == 'GLOSA' ? 'selected' : '' }}>Compra por glosa</option>
                </select>
                <label for="tipo_detalle_compra">Tipo de compra</label>
            </div>
        </div>

        <input type="hidden" name="detalles_json" id="detalles_json"
            value='{!! old('detalles_json', '[]') !!}' />

        <div class="row without-top">
            <div class="col s12 m12">
                <div class="table-wrapper">
                    <div class="table-scroll" style="overflow-x: auto;">
                        <table class="highlight striped centered detalle-productos" id="data-table"
                            style="width: max-content; min-width: 100%;">
                            <thead class="thead-custom">
                                <tr style="color: white">
                                    <th class="no-hide">Código</th>
                                    <th class="no-hide">Descripción</th>
                                    <th class="no-hide">Unid.</th>
                                    <th class="no-hide">Cant.</th>
                                    <th class="no-hide">IGV</th>
                                    <th class="no-hide">P. unit.</th>
                                    <th class="no-hide">Importe</th>
                                    <th>Notas</th>
                                    <th class="center all">
                                        <button class="btn btn-floating btn-small tooltipped white"
                                            id="det_movcompra-btn-agregar" style="display: none;"
                                            data-tooltip="Agregar glosa" type="button"
                                            onclick="agregarGlosaCompra();">
                                            <i class="material-icons black-text">add</i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($movcompra->detMovcompras ?? [] as $detalle)
                                    @php
                                        $jsonProducto = json_decode($detalle->json_producto, true) ?? [];
                                        $precioUnitario = $detalle->cantidad > 0 ? $detalle->monto / $detalle->cantidad : 0;
                                        $porcentajeLinea = optional($detalle->igv)->porcentaje_igv ?? ($porcentajeIgv ?? 18);
                                        $igvLinea = $detalle->monto * ($porcentajeLinea / 100);
                                    @endphp
                                    <tr onclick="editarDetalleCompra(this, event)"
                                        data-producto-id="{{ $detalle->producto_id }}">
                                        <td>{{ $detalle->codigo }}</td>
                                        <td>{{ $detalle->descripcion }}</td>
                                        <td>{{ $detalle->unidad->descripcion ?? 'Unidad' }}</td>
                                        <td><span class="inpt-cantidad-mc cantidad">{{ number_format($detalle->cantidad, 2) }}</span></td>
                                        <td><span class="inpt-cantidad-mc igv">{{ number_format($igvLinea, 2) }}</span></td>
                                        <td><span class="inpt-cantidad-mc precio-unitario">{{ number_format($precioUnitario, 2) }}</span></td>
                                        <td><span class="inpt-cantidad-mc precio-total">{{ number_format($detalle->monto, 2) }}</span></td>
                                        <td>{{ $detalle->comentario }}</td>
                                        <td>
                                            <button type="button" class="btn btn-floating btn-small red white-text tooltipped"
                                                data-tooltip="Quitar" onclick="quitarProductoDeTablaCompra({{ $detalle->producto_id ?? 0 }}, this); event.stopPropagation();">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row without-tb">
            <div class="col s12 m6">
                <label>
                    <input type="checkbox" id="incluye_igv" value="S"
                        {{ old('incluye_igv', isset($movcompra->id) ? $movcompra->getRawOriginal('incluye_igv') : 'N') === 'S' ? 'checked' : '' }}
                        onchange="actualizarTotalesCompra();" />
                    <span>Incluye IGV</span>
                </label>
            </div>
            <div class="col s12 m6 right-align">
                <label>
                    <input type="radio" name="moneda" id="moneda_pen" value="PEN"
                        {{ old('moneda', $movcompra->moneda ?? 'PEN') === 'PEN' ? 'checked' : '' }} />
                    <span>S/.</span>
                </label>
                <label style="margin-left: 30px;">
                    <input type="radio" name="moneda" id="moneda_usd" value="USD"
                        {{ old('moneda', $movcompra->moneda ?? '') === 'USD' ? 'checked' : '' }} />
                    <span>$</span>
                </label>
            </div>
        </div>

        <div class="row without-tb">
            <div class="input-field col s12 m3">
                <input type="number" name="tipocambio" id="tipocambio_compra" step="0.001"
                    value="{{ old('tipocambio', $tipocambio ?? 3.71) }}">
                <label for="tipocambio_compra" class="active">Tipo de cambio</label>
            </div>
            <div class="input-field col s12 m3">
                <input type="number" name="subtotal" id="subtotal_compra" step="0.01" readonly
                    value="{{ old('subtotal', $movcompra->subtotal ?? '0.00') }}">
                <label for="subtotal_compra" class="active">Subtotal</label>
            </div>
            <div class="input-field col s12 m3">
                <input type="number" name="igv" id="igv_compra" step="0.01" readonly
                    value="{{ old('igv', $movcompra->igv ?? '0.00') }}">
                <label for="igv_compra" class="active">IGV</label>
            </div>
            <div class="input-field col s12 m3">
                <input type="number" name="total" id="total_compra" step="0.01" readonly
                    value="{{ old('total', $movcompra->total ?? '0.00') }}">
                <label for="total_compra" class="active">Total</label>
            </div>
        </div>

        <div class="row without-tb">
            <div class="input-field col s12 m8">
                <textarea name="comentario" id="notas_compra" class="materialize-textarea">{{ old('comentario', isset($movcompra->movimiento) ? $movcompra->movimiento->comentario : '') }}</textarea>
                <label for="notas_compra">Notas a la compra</label>
            </div>
            <div class="col s12 m4">
                <label>
                    <input type="checkbox" id="afecta_kardex" value="S"
                        {{ old('afecta_kardex', isset($movcompra->id) ? $movcompra->getRawOriginal('afecta_kardex') : 'S') === 'S' ? 'checked' : '' }} />
                    <span>Genera movimiento de stock</span>
                </label>
                <div class="input-field">
                    <select name="sucursal_destino_id" id="sucursal_destino_id" disabled>
                        <option value="{{ $sucursal_id }}">{{ $sucursal->descripcion ?? 'Sucursal actual' }}</option>
                    </select>
                    <label for="sucursal_destino_id">Sucursal de destino</label>
                </div>
            </div>
        </div>
    </div>

    <div class="col s12 m12 l5">
        <ul class="tabs tabs-compra">
            <li class="tab col s6"><a href="#tab-datos-compra" class="active">DATOS DE COMPRA</a></li>
            <li class="tab col s6"><a href="#tab-productos-compra">PRODUCTOS</a></li>
        </ul>

        <div id="tab-datos-compra" class="col s12" style="padding-top: 15px;">
            <div class="input-field col s12 persona-wrapper">
                <div class="persona-flex">
                    <input type="text" id="persona_autocomplete"
                        class="autocomplete validate" placeholder="Seleccionar proveedor"
                        data-autocomplete-url="{{ route('autocomplete.personas') }}"
                        value="{{ isset($movcompra->movimiento)
                            ? ($movcompra->movimiento->Persona->personamaestro->nrodoc ?? '0') . ' - ' . ($movcompra->movimiento->persona ?? 'CLIENTES VARIOS')
                            : '0 - CLIENTES VARIOS' }}">
                    <button class="btn red left tooltipped" id="btnQuitarCliente" data-tooltip="Deseleccionar proveedor"
                        type="button" onclick="eliminarPersona();" style="border-radius: 99em">
                        <i class="fa-solid fa-user-slash"></i>
                    </button>
                </div>
                <input type="hidden" name="persona_id" id="persona_id"
                    value="{{ old('persona_id', $movcompra->movimiento->persona_id ?? '') }}">
                <label for="persona_autocomplete" class="active">Proveedor</label>
            </div>

            <div class="input-field col s12">
                <i class="fa-solid fa-user-tie prefix"></i>
                <select name="responsable_id" id="responsable_id">
                    @foreach ($responsables as $responsable)
                        <option value="{{ $responsable->id }}"
                            {{ old('responsable_id', $movcompra->movimiento->responsable_id ?? Auth::user()->persona_id) == $responsable->id ? 'selected' : '' }}>
                            {{ $responsable->personamaestro->nrodoc ?? '' }} - {{ $responsable->apellidos }} {{ $responsable->nombres }}
                        </option>
                    @endforeach
                </select>
                <label for="responsable_id">Responsable</label>
            </div>

            @if (!isset($movcompra->id))
                <div class="col s12">
                    <label>
                        <input type="checkbox" id="afecta_caja" value="S" checked
                            onchange="toggleSeccionCajaCompra();" />
                        <span>Genera egreso en caja</span>
                    </label>
                </div>

                <div id="seccion-caja-compra">
                    <div class="input-field col s12 m6">
                        <select name="caja_id" id="caja_id">
                            @foreach ($cajas as $caja)
                                <option value="{{ $caja->id }}">{{ $caja->numero }}</option>
                            @endforeach
                        </select>
                        <label for="caja_id">Caja</label>
                    </div>
                    <div class="input-field col s12 m6">
                        <input type="date" name="fecha_movcaja" id="fecha_movcaja" value="{{ date('Y-m-d') }}">
                        <label for="fecha_movcaja" class="active">Fecha de mov. de caja</label>
                    </div>

                    <input type="hidden" name="detalles_movimientos_json" id="detalles_movimientos_json"
                        value="{{ old('detalles_movimientos_json', json_encode($detallesMovimientos ?? [])) }}">

                    <div class="col s12">
                        <div class="table-wrapper">
                            <div class="table-scroll" style="overflow-x: auto;">
                                <table class="highlight striped centered data-table-2" id="tabla-medios-pago"
                                    style="width: max-content; min-width: 100%;">
                                    <thead class="thead-custom">
                                        <tr style="color: white">
                                            <th>Tipo</th>
                                            <th>Monto</th>
                                            <th>F. venc.</th>
                                            <th>Medio</th>
                                            <th>Detalles</th>
                                            <th>Notas</th>
                                            <th>
                                                <button type="button" class="btn btn-small green white"
                                                    style="border-radius: 50%;" onclick="agregarMovimiento()">
                                                    <i class="material-icons">add</i>
                                                </button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="detalle-movcaja">
                                        @if (!empty($detallesMovimientos))
                                            @foreach ($detallesMovimientos as $detalle)
                                                <tr onclick="editarMovimiento(this, event)">
                                                    <td><span class="new badge {{ ($detalle['tipo'] ?? '') === 'Pagado' ? 'green' : 'red' }} white-text" data-badge-caption="{{ $detalle['tipo'] ?? 'Pagado' }}"></span></td>
                                                    <td class="inpt-cantidad-mc monto"><span class="inpt-cantidad-mc monto">{{ number_format($detalle['monto'] ?? 0, 2) }}</span></td>
                                                    <td class="fecha-vencimiento">{{ $detalle['fecha_vencimiento'] ?? '' }}</td>
                                                    <td class="medio-pago" data-mediopago-id="{{ $detalle['mediopago_id'] ?? 1 }}">{{ $detalle['medio_pago'] ?? 'Efectivo' }}</td>
                                                    <td class="detalles"></td>
                                                    <td class="notas">{{ $detalle['notas'] ?? '' }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-small red darken-2"
                                                            onclick="eliminarFila(this); event.stopPropagation();"
                                                            style="border-radius: 50%;">
                                                            <i class="material-icons">remove</i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr onclick="editarMovimiento(this, event)">
                                                <td><span class="new badge green white-text" data-badge-caption="Pagado"></span></td>
                                                <td class="inpt-cantidad-mc monto"><span class="inpt-cantidad-mc monto">0.00</span></td>
                                                <td class="fecha-vencimiento"></td>
                                                <td class="medio-pago" data-mediopago-id="1">Efectivo</td>
                                                <td class="detalles"></td>
                                                <td class="notas"></td>
                                                <td>
                                                    <button type="button" class="btn btn-small red darken-2"
                                                        onclick="eliminarFila(this); event.stopPropagation();"
                                                        style="border-radius: 50%;">
                                                        <i class="material-icons">remove</i>
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
            @else
                <input type="hidden" name="afecta_caja" value="{{ $movcompra->getRawOriginal('afecta_caja') }}">
            @endif
        </div>

        <div id="tab-productos-compra" class="col s12" style="padding-top: 15px;">
            <div class="input-field col s10">
                <i class="fa-solid fa-bag-shopping prefix"></i>
                <input type="text" name="buscar_producto" id="producto_autocomplete" class="autocomplete validate"
                    autocomplete="off" data-autocomplete-url="{{ route('autocomplete.productos') }}"
                    onfocus="activarAutocomplete()" />
                <input type="hidden" name="producto_id" id="producto_id">
                <label for="producto_autocomplete">Productos...</label>
            </div>
            <div class="input-field col s2">
                <button type="button" class="btn green tooltipped" data-tooltip="Agregar producto"
                    onclick="agregarProductoDesdeAutocompleteCompra();" style="border-radius: 99em">
                    <i class="material-icons">add</i>
                </button>
            </div>

            <div id="div-categoria-1" class="col s12">
                <div id="slider-categoria-1" class="slick-slider">
                    @foreach ($categorias as $categoria)
                        <div class="categoria-wrapper">
                            <div class="div-btn-categoria">
                                <a class="btn btn-categoria tooltipped" data-tooltip="{{ $categoria->descripcion }}"
                                    id="btn-categoria-{{ $categoria->id }}" nivel="1"
                                    onclick="cargarProductosPorCategoria('{{ $categoria->id }}', 'Bien')">
                                    {{ $categoria->descripcion }}
                                </a>
                            </div>
                        </div>
                    @endforeach

                    @for ($i = count($categorias); $i < 12; $i++)
                        <div class="categoria-wrapper empty-slide"></div>
                    @endfor
                </div>
            </div>

            <div class="row">
                <div class="row products-container">
                </div>
            </div>
        </div>
    </div>
</div>

@include('movcompra.partials.modal-detalle-caja')

<input type="hidden" name="sucursal_id" id="sucursal_id" value="{{ $sucursal_id }}">
<input type="hidden" name="incluye_igv_hidden" id="incluye_igv_hidden" value="N">
<input type="hidden" name="afecta_kardex_hidden" id="afecta_kardex_hidden" value="S">
<input type="hidden" name="afecta_caja_hidden" id="afecta_caja_hidden" value="N">

<div class="row padding-1 p-1">
    <div class="col s12 mt20 mt-2">
        <div class="modal-footer right-align">
            <button type="submit" class="btn darken-2">
                <i class="material-icons left">save</i> GUARDAR
            </button>
            <a href="#" onclick="back();" class="modal-close waves-effect waves-green btn-flat">CANCELAR</a>
        </div>
    </div>
</div>
