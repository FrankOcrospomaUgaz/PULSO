<div class="modal-content">
    <div class="row without-tb" id="modal-form">
        <div class="col s12">
            <div class="row">
                <!-- Código -->
                <div class="col s12 m6 l4 input-field">
                    @php
                        $codigoValue = old('codigo', $motivoReserva->codigo ?? '');
                    @endphp
                    <input class="submit-form" id="codigo" name="codigo" type="text" value="{{ $codigoValue }}">
                    <label for="codigo" class="{{ !empty($codigoValue) ? 'active' : '' }}">Código</label>
                    <span class="helper-text" id="codigo_spn"></span>
                </div>

                <!-- Descripción -->
                <div class="col s12 m6 l8 input-field">
                    @php
                        $descripcionValue = old('descripcion', $motivoReserva->descripcion ?? '');
                    @endphp
                    <textarea class="materialize-textarea" id="descripcion" name="descripcion" rows="1" oninput="autoResize(this);">{{ $descripcionValue }}</textarea>
                    <label for="descripcion" class="{{ !empty($descripcionValue) ? 'active' : '' }}">Descripción</label>
                    <span class="helper-text" id="descripcion_spn"></span>
                </div>

                <!-- Orden -->
                <div class="col s12 m6 l4 input-field">
                    @php
                        $ordenValue = old('orden', $motivoReserva->orden ?? '');
                    @endphp
                    <input class="submit-form" id="orden" name="orden" type="number" value="{{ $ordenValue }}">
                    <label for="orden" class="{{ !empty($ordenValue) ? 'active' : '' }}">Orden</label>
                    <span class="helper-text" id="orden_spn"></span>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Botones de Acción -->
<div class="col s12 mt20 mt-2">
    <div class="modal-footer">
        <button type="submit" class="btn waves-effect waves-light">
            <i class="material-icons left">send</i>{{ __('Guardar') }}
        </button>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
    </div>
</div>
