<div class="col s12">
    <!-- Campo de texto para 'descripcion' -->
    <div class="input-field col s12 m4">
        <input type="text" name="descripcion" id="descripcion" class="validate @error('descripcion') invalid @enderror" value="{{ old('descripcion', $tabla?->descripcion) }}">
        <label for="descripcion" class="{{ $tabla?->descripcion ? 'active' : '' }}">{{ __('Descripcion') }}</label>
        {!! $errors->first('descripcion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de texto para 'comentario' -->
    <div class="input-field col s12 m4">
        <input type="text" name="comentario" id="comentario" class="validate @error('comentario') invalid @enderror" value="{{ old('comentario', $tabla?->comentario) }}">
        <label for="comentario" class="{{ $tabla?->comentario ? 'active' : '' }}">{{ __('Comentario') }}</label>
        {!! $errors->first('comentario', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de selección para 'tipo' -->
    <div class="input-field col s12 m4">
        <select name="tipo" id="tipo" class="@error('tipo') invalid @enderror">
            <option value="Sistema" {{ old('tipo', $tabla?->tipo) == 'Sistema' ? 'selected' : '' }}>Sistema</option>
            <option value="Base de datos" {{ old('tipo', $tabla?->tipo) == 'Base de datos' ? 'selected' : '' }}>Base de datos</option>
        </select>
        <label for="tipo">{{ __('Tipo') }}</label>
        {!! $errors->first('tipo', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>
</div>

<!-- Botones de Acción -->
<div class="col s12 mt20 mt-2">
    <div class="modal-footer">
        <button type="submit" class="btn waves-effect waves-light"><i class="material-icons left">send</i>{{ __('Guardar') }}</button>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
    </div>
</div>
