<div class="modal-content">
    <div class="row without-tb" id="modal-form">
        <div class="col s12">
            <div class="row">
                <!-- Descripción -->
                <div class="col s12 m6 l6 input-field">
                    @php
                        $descripcionValue = old('descripcion', $origen->descripcion ?? '');
                    @endphp
                    <textarea class="materialize-textarea" id="descripcion" name="descripcion" rows="1" oninput="autoResize(this);">{{ $descripcionValue }}</textarea>
                    <label for="descripcion" class="{{ !empty($descripcionValue) ? 'active' : '' }}">Descripción</label>
                    <span class="helper-text" id="descripcion_spn"></span>
                </div>

                <!-- Selector de Color -->
                <div class="col s12 m6 l3 input-field">
                    @php
                        $colorValue = old('color', $origen->color ?? '#000000');
                    @endphp
                    <div class="color-picker-container">
                        <input type="color" id="color" name="color" class="color-input" value="{{ $colorValue }}" oninput="this.nextElementSibling.value = this.value">
                        <input type="text" id="color_text" class="color-text-input" name="color_text" value="{{ $colorValue }}" readonly>
                    </div>
                    <label for="color" class="active">Selecciona un Color</label>
                    <span class="helper-text">Haz clic en el recuadro para elegir un color</span>
                </div>

                <!-- Selección de Logo con Diseño Mejorado -->
                <div class="col s12 m6 l3">
                    <div class="file-field input-field input-group-field">
                        <div class="btn left">
                            <span><i class="fa-solid fa-circle-up"></i></span>
                            <input id="logo" name="logo" type="file" accept=".png,.jpg,.jpeg">
                        </div>
                        <div class="file-path-wrapper left-wrapper input-field">
                            <input id="logo_txt" class="file-path validate" type="text" placeholder="No se ha seleccionado ningún archivo">
                            <span class="helper-text" id="logo_spn"></span>
                        </div>
                    </div>
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

<!-- Estilos Mejorados -->
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
