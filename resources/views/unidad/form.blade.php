<div class="col s12">
    <div class="input-field col s12 m6">
        <input type="text" name="descripcion" id="descripcion" class="validate @error('descripcion') invalid @enderror"
            value="{{ old('descripcion', $unidad?->descripcion) }}">
        <label for="descripcion" class="{{ $unidad?->descripcion ? 'active' : '' }}">{{ __('Descripción') }}</label>
        {!! $errors->first('descripcion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
    </div>

    <div class="input-field col s12 m6">
        <input type="text" name="abreviatura" id="abreviatura" class="validate @error('abreviatura') invalid @enderror"
            value="{{ old('abreviatura', $unidad?->abreviatura) }}">
        <label for="abreviatura" class="{{ $unidad?->abreviatura ? 'active' : '' }}">{{ __('Abreviatura') }}</label>
        {!! $errors->first('abreviatura', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
    </div>

    <div class="input-field col s12 m6">
        <select name="tipo" id="tipo" class="@error('tipo') invalid @enderror">
            <option value="" disabled {{ old('tipo', $unidad?->tipo) ? '' : 'selected' }}>Selecciona un tipo</option>
            <option value="Masa" {{ old('tipo', $unidad?->tipo) == 'Masa' ? 'selected' : '' }}>Masa</option>
            <option value="Longitud" {{ old('tipo', $unidad?->tipo) == 'Longitud' ? 'selected' : '' }}>Longitud</option>
            <option value="Área" {{ old('tipo', $unidad?->tipo) == 'Área' ? 'selected' : '' }}>Área</option>
            <option value="Otros" {{ old('tipo', $unidad?->tipo) == 'Otros' ? 'selected' : '' }}>Otros</option>
        </select>
        <label for="tipo">{{ __('Tipo') }}</label>
        {!! $errors->first('tipo', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
    </div>

    <div class="input-field col s12 m6">
        <!-- Campo oculto para enviar false si el checkbox no está marcado -->
        <input type="hidden" name="sunat" value="0">
        <label>
            <input type="checkbox" name="sunat" id="sunat" value="1" {{ old('sunat', $unidad?->sunat) ? 'checked' : '' }} />
            <span>{{ __('Aceptado por SUNAT') }}</span>
        </label>
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
