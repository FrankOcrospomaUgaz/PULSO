<div class="row">
    <div class="col s12">
        <!-- Campo de texto para 'codigo' -->
        <div class="input-field col s12 m4">
            <input type="number" name="codigo" id="codigo" class="validate @error('codigo') invalid @enderror"
                value="{{ old('codigo', $motivo?->codigo) }}">
            <label for="codigo" class="{{ $motivo?->codigo ? 'active' : '' }}">{{ __('Código') }}</label>
            {!! $errors->first('codigo', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <div class="input-field col s12 m4">
            <input type="text" name="descripcion" id="descripcion"
                class="validate @error('descripcion') invalid @enderror"
                value="{{ old('descripcion', $motivo?->descripcion) }}">
            <label for="descripcion" class="{{ $motivo?->descripcion ? 'active' : '' }}">{{ __('Descripción') }}</label>
            {!! $errors->first(
                'descripcion',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

        <div class="input-field col s12 m4">
            <select name="tipomovimiento_id" id="tipomovimiento_id"
                class="validate @error('tipomovimiento_id') invalid @enderror">
                <option value="" disabled selected>Selecciona un Tipo de Opción</option>
                @foreach ($tipomovimientos as $tipo)
                    <option value="{{ $tipo->id }}"
                        {{ old('tipomovimiento_id', $motivo?->tipomovimiento_id) == $tipo->id ? 'selected' : '' }}>
                        {{ $tipo->descripcion }}
                    </option>
                @endforeach
            </select>
            <label for="tipomovimiento_id">Tipo de Movimiento</label>
            {!! $errors->first(
                'tipomovimiento_id',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

        <!-- Campo de texto para 'orden' -->
        <div class="input-field col s12 m4">
            <input type="number" name="orden" id="orden" class="validate @error('orden') invalid @enderror"
                value="{{ old('orden', $motivo?->orden) ?? 0 }}">
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
