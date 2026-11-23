<div id="detalleModal" class="modal bottom-sheet" tabindex="0" style="z-index: 1003; display: none;">
    <div class="modal-content">
        <div class="row without-top">
            <h5 class="grey-text text-darken-3 modal-title without-tb center" id="det_movcaja-submodal-title">
                <i class="fa-solid fa-money-bills"></i> Detalle de caja
            </h5>
        </div>
        <div class="row">
            <div class="input-field col s12 m2">
                <select id="tipo_detalle">
                    <option value="Pagado" selected>Pagado</option>
                    <option value="Deuda">Deuda</option>
                </select>
                <label for="tipo_detalle">Tipo</label>
            </div>
            <div class="input-field col s12 m2">
                <input type="number" id="monto_detalle" step="0.01" value="0.00">
                <label for="monto_detalle" class="active">Monto</label>
            </div>
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
            <div class="input-field col s12 m2 custom-dropdown" id="pos_container" style="display: none;">
                <select id="pos_detalle" name="pos_detalle">
                    @foreach ($pos as $posItem)
                        <option value="{{ $posItem->id }}">{{ $posItem->descripcion }}</option>
                    @endforeach
                </select>
                <label for="pos_detalle">P.O.S.</label>
            </div>
            <div class="input-field col s12 m3 custom-dropdown" id="tarjeta_container" style="display: none;">
                <select id="tarjeta_detalle" name="tarjeta_detalle">
                    @foreach ($tarjetas as $tarjeta)
                        <option value="{{ $tarjeta->id }}">
                            {{ $tarjeta->descripcion }} - @if ($tarjeta->tipo === 'C') Crédito @else Débito @endif
                        </option>
                    @endforeach
                </select>
                <label for="tarjeta_detalle">Tarjeta</label>
            </div>
            <div class="input-field col s12 m2 custom-dropdown" id="banco_container" style="display: none;">
                <select id="banco_detalle" name="banco_detalle">
                    @foreach ($bancos as $banco)
                        <option value="{{ $banco->id }}">{{ $banco->descripcion }}</option>
                    @endforeach
                </select>
                <label for="banco_detalle">Banco</label>
            </div>
            <div class="input-field col s12 m2 custom-dropdown" id="billetera_container" style="display: none;">
                <select id="billetera_detalle" name="billetera_detalle">
                    @foreach ($billeteras as $billetera)
                        <option value="{{ $billetera->id }}">{{ $billetera->descripcion }}</option>
                    @endforeach
                </select>
                <label for="billetera_detalle">Billetera Digital</label>
            </div>
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
