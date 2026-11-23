<div class="modal-content">
    <div class="row without-tb" id="modal-form">
        <div class="col s12">
            <div class="row">
                <input type="hidden" name="sucursal_id" id="sucursal_id"
                    value="{{ old('sucursal_id', $edificio->sucursal_id ?? $sucursal_id ?? '') }}">

                <!-- Campo de texto para 'nombre' -->
                <div class="input-field col s12 m6">
                    <input type="text" name="nombre" id="nombre" class="validate @error('nombre') invalid @enderror"
                        value="{{ old('nombre', $edificio?->nombre) }}">
                    <label for="nombre"
                        class="{{ $edificio?->nombre ? 'active' : '' }}">{{ __('Nombre del Edificio') }}</label>
                    {!! $errors->first('nombre', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
                </div>

                <!-- Campo de texto para 'direccion' -->
                <div class="input-field col s12 m6">
                    <input type="text" name="direccion" id="direccion"
                        class="validate @error('direccion') invalid @enderror"
                        value="{{ old('direccion', $edificio?->direccion) }}">
                    <label for="direccion"
                        class="{{ $edificio?->direccion ? 'active' : '' }}">{{ __('Dirección') }}</label>
                    {!! $errors->first('direccion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
                </div>


            </div>
        </div>
    </div>
</div>

<!-- Botones de acción -->
<div class="col s12 mt20 mt-2">
    <div class="modal-footer">
        <button type="submit" class="btn waves-effect waves-light">
            <i class="material-icons left">send</i>Guardar
        </button>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
    </div>
</div>