<div class="modal-content">
    <div class="row without-tb" id="modal-form">
        <div class="col s12">
            <div class="row">
                <!-- Descripción -->
                <div class="col s12 m6 l6 input-field">
                    @php
                        $descripcionValue = old('descripcion', $situacion->descripcion ?? '');
                    @endphp
                    <textarea class="materialize-textarea" id="descripcion" name="descripcion" rows="1" oninput="autoResize(this);">{{ $descripcionValue }}</textarea>
                    <label for="descripcion" class="{{ !empty($descripcionValue) ? 'active' : '' }}">Descripción</label>
                    <span class="helper-text" id="descripcion_spn"></span>
                </div>

                <!-- Selector de Color -->
                <div class="col s12 m6 l3 input-field">
                    @php
                        $colorValue = old('color', $situacion->color ?? '#000000');
                    @endphp
                    <div class="color-picker-container">
                        <input type="color" id="color" name="color" class="color-input" value="{{ $colorValue }}" oninput="this.nextElementSibling.value = this.value">
                        <input type="text" id="color_text" class="color-text-input" name="color_text" value="{{ $colorValue }}" >
                    </div>
                    <label for="color" class="active">Selecciona un Color</label>
                    <span class="helper-text">Haz clic en el recuadro para elegir un color</span>
                </div>

                <!-- Estado (solo en edición) -->
                @if(isset($situacion->id))
                    <div class="col s12 m6 l3 input-field">
                        <p>
                            <label>
                                <input type="checkbox" name="estado" value="1" {{ $situacion->estado ? 'checked' : '' }} />
                                <span>Activo</span>
                            </label>
                        </p>
                    </div>
                @endif
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

<!-- Estilos para selector de color -->
<style>
    .color-picker-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .color-input {
        width: 50px;
        height: 50px;
        border: none;
        padding: 0;
        cursor: pointer;
    }

    .color-text-input {
        width: 80px;
        text-align: center;
        border: 1px solid #ccc;
        padding: 5px;
        border-radius: 5px;
        background: #f9f9f9;
        font-weight: bold;
    }
</style>
