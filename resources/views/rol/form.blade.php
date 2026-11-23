<div class="col s12">

    <!-- Campo de texto para 'descripcion' -->
    <div class="input-field col s12 m4">
        <input type="text" name="descripcion" id="descripcion" class="validate @error('descripcion') invalid @enderror" value="{{ old('descripcion', $rol?->descripcion) }}">
        <label for="descripcion" class="{{ $rol?->descripcion ? 'active' : '' }}">{{ __('Descripción del Rol') }}</label>
        {!! $errors->first('descripcion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
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
