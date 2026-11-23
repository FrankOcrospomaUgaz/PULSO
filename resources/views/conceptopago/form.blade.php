<div class="col s12">
    <!-- Campo de texto para 'descripcion' -->
    <div class="input-field col s12 m4">
        <input type="text" name="descripcion" id="descripcion" class="validate @error('descripcion') invalid @enderror" value="{{ old('descripcion', $conceptopago?->descripcion) }}">
        <label for="descripcion" class="{{ $conceptopago?->descripcion ? 'active' : '' }}">{{ __('Descripcion') }}</label>
        {!! $errors->first('descripcion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de selección para 'tipo' -->
    <div class="input-field col s12 m4">
        <select name="tipo" id="tipo" class="@error('tipo') invalid @enderror">
            <option value="I" {{ old('tipo', $conceptopago?->tipo) == 'I' ? 'selected' : '' }}>Ingreso</option>
            <option value="E" {{ old('tipo', $conceptopago?->tipo) == 'E' ? 'selected' : '' }}>Egreso</option>
        </select>
        <label for="tipo" class="{{ $conceptopago?->tipo ? 'active' : '' }}">{{ __('Tipo') }}</label>
        {!! $errors->first('tipo', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

<!-- Campo de checkbox para 'restringido' -->
<div class="input-field col s12 m4">
    <label>
        <input type="hidden" name="restringido" value="0">
        <input type="checkbox" name="restringido" id="restringido" value="1"
            {{ old('restringido', $conceptopago?->restringido) == 'Sí' ? 'checked' : '' }}>
        <span>{{ __('Restringido') }}</span>
    </label>
    {!! $errors->first('restringido', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
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
