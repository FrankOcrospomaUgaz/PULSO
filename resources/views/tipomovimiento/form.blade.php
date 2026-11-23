<div class="row">
    <div class="col s12">
        <div class="input-field col s12 m6">
            <input type="text" name="descripcion" id="descripcion" class="validate @error('descripcion') invalid @enderror" value="{{ old('descripcion', $tipomovimiento?->descripcion) }}">
            <!-- Agregar la clase 'active' si el campo ya tiene valor -->
            <label for="descripcion" class="{{ $tipomovimiento?->descripcion ? 'active' : '' }}">{{ __('Descripcion') }}</label>
            {!! $errors->first('descripcion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
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
</div>
