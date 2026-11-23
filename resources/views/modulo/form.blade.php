
    <div class="col s12">
        <!-- Campo para 'descripcion' -->
        <div class="input-field col s12 m6">
            <input type="text" name="descripcion" id="descripcion" class="validate @error('descripcion') invalid @enderror" value="{{ old('descripcion', $modulo?->descripcion) }}">
            <label for="descripcion" class="{{ $modulo?->descripcion ? 'active' : '' }}">{{ __('Descripcion') }}</label>
            {!! $errors->first('descripcion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo para 'abreviatura' -->
        <div class="input-field col s12 m6">
            <input type="text" name="abreviatura" id="abreviatura" class="validate @error('abreviatura') invalid @enderror" value="{{ old('abreviatura', $modulo?->abreviatura) }}">
            <label for="abreviatura" class="{{ $modulo?->abreviatura ? 'active' : '' }}">{{ __('Abreviatura') }}</label>
            {!! $errors->first('abreviatura', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo para 'orden' -->
        <div class="input-field col s12 m6">
            <input type="text" name="orden" id="orden" class="validate @error('orden') invalid @enderror" value="{{ old('orden', $modulo?->orden) }}">
            <label for="orden" class="{{ $modulo?->orden ? 'active' : '' }}">{{ __('Orden') }}</label>
            {!! $errors->first('orden', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo para 'icono' -->
        <div class="input-field col s12 m6">
            <input type="text" name="icono" id="icono" class="validate @error('icono') invalid @enderror" value="{{ old('icono', $modulo?->icono) }}">
            <label for="icono" class="{{ $modulo?->icono ? 'active' : '' }}">{{ __('Icono') }}</label>
            {!! $errors->first('icono', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>
    </div>
    <!-- Botones de Acción -->
    <div class="col s12 mt20 mt-2">
        <div class="modal-footer">
        <button type="submit" class="btn waves-effect waves-light"><i class="material-icons left">send</i>{{ __('Guardar') }}</button>

            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
        </div>
    </div>
