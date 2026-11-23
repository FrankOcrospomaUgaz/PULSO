<div class="col s12">
    <div class="input-field col s12 m6">
        <input type="text" name="descripcion" id="descripcion" class="validate @error('descripcion') invalid @enderror"
            value="{{ old('descripcion', $marca?->descripcion) }}">
        <label for="descripcion" class="{{ $marca?->descripcion ? 'active' : '' }}">{{ __('Descripción') }}</label>
        {!! $errors->first('descripcion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
    </div>

    <div class="input-field col s12 m6">
        <input type="text" name="abreviatura" id="abreviatura" class="validate @error('abreviatura') invalid @enderror"
            value="{{ old('abreviatura', $marca?->abreviatura) }}">
        <label for="abreviatura" class="{{ $marca?->abreviatura ? 'active' : '' }}">{{ __('Abreviatura') }}</label>
        {!! $errors->first('abreviatura', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
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
