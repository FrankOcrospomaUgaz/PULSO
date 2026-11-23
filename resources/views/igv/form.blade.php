<div class="row">
    <div class="col s12">
        <!-- Campo de texto para 'codigo' -->
        <div class="input-field col s12 m2">
            <input type="number" name="codigo" id="codigo" class="validate @error('codigo') invalid @enderror"
                value="{{ old('codigo', $igv?->codigo) }}">
            <label for="codigo" class="{{ $igv?->codigo ? 'active' : '' }}">{{ __('Código') }}</label>
            {!! $errors->first('codigo', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de texto para 'descripcion' -->
        <div class="input-field col s12 m3">
            <input type="text" name="descripcion" id="descripcion"
                class="validate @error('descripcion') invalid @enderror"
                value="{{ old('descripcion', $igv?->descripcion) }}">
            <label for="descripcion" class="{{ $igv?->descripcion ? 'active' : '' }}">{{ __('Descripción') }}</label>
            {!! $errors->first(
                'descripcion',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

        <!-- Campo de texto para 'porcentaje_igv' -->
        <div class="input-field col s12 m2">
            <input type="number" step="0.01" name="porcentaje_igv" id="porcentaje_igv"
                class="validate @error('porcentaje_igv') invalid @enderror"
                value="{{ old('porcentaje_igv', $igv?->porcentaje_igv) ?? 0 }}">
            <label for="porcentaje_igv" class="active">{{ __('Porcentaje IGV') }}</label>
            {!! $errors->first(
                'porcentaje_igv',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

        <!-- Campo de texto para 'orden' -->
        <div class="input-field col s12 m2">
            <input type="number" name="orden" id="orden" class="validate @error('orden') invalid @enderror"
                value="{{ old('orden', $igv?->orden) ?? 0 }}">
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
