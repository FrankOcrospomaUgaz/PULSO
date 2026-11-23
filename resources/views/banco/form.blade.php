<div class="row">
    <div class="col s12">
        <!-- Campo de texto para 'descripcion' -->
        <div class="input-field col s12 m4">
            <input type="text" name="descripcion" id="descripcion" class="validate @error('descripcion') invalid @enderror"
                value="{{ old('descripcion', $banco?->descripcion) }}">
            <label for="descripcion" class="{{ $banco?->descripcion ? 'active' : '' }}">{{ __('Descripción') }}</label>
            {!! $errors->first('descripcion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de texto para 'orden' -->
        <div class="input-field col s12 m4">
            <input type="number" name="orden" id="orden" class="validate @error('orden') invalid @enderror"
                value="{{ old('orden', $banco?->orden) ?? 0}}">
            <label for="orden" class="active">{{ __('Orden') }}</label>
            {!! $errors->first('orden', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

    </div>

    <!-- Botones de acción -->
    <div class="col s12 mt20 mt-2">
        <div class="modal-footer">
            <button type="submit" class="btn waves-effect waves-light">
                <i class="material-icons left">send</i>{{ __('Guardar') }}
            </button>
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
        </div>
    </div>
</div>
