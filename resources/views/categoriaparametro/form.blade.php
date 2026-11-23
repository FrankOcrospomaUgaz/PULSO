<div class="row">
    <div class="col s12">
        <!-- Campo de texto para 'descripcion' -->
        <div class="input-field col s12 m6">
            <input type="text" name="descripcion" id="descripcion"
                class="validate @error('descripcion') invalid @enderror"
                value="{{ old('descripcion', $categoriaparametro?->descripcion) }}">
            <label for="descripcion" class="{{ old('descripcion', $categoriaparametro?->descripcion) ? 'active' : '' }}">
                {{ __('Descripcion') }}
            </label>
            {!! $errors->first(
                'descripcion',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

        <!-- Campo de texto para 'orden' -->
        <div class="input-field col s12 m6">
            <input type="number" name="orden" id="orden" class="validate @error('orden') invalid @enderror"
                value="{{ old('orden', $categoriaparametro?->orden ?? 0) }}" min="0">
            <label for="orden" class="{{ old('orden', $categoriaparametro?->orden ?? 0) ? 'active' : '' }}">
                {{ __('Orden') }}
            </label>
            {!! $errors->first('orden', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
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
