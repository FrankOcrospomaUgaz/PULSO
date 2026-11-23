<div class="col s12">
    <!-- Campo de texto para 'nombre' -->
    <div class="input-field col s12 m6">
        <input type="text" name="nombre" id="nombre" class="validate @error('nombre') invalid @enderror"
               value="{{ old('nombre', $tipodocumento?->nombre) }}">
        <label for="nombre" class="{{ $tipodocumento?->nombre ? 'active' : '' }}">{{ __('Nombre') }}</label>
        {!! $errors->first('nombre', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de texto para 'abreviatura' -->
    <div class="input-field col s12 m6">
        <input type="text" name="abreviatura" id="abreviatura" class="validate @error('abreviatura') invalid @enderror"
               value="{{ old('abreviatura', $tipodocumento?->abreviatura) }}">
        <label for="abreviatura" class="{{ $tipodocumento?->abreviatura ? 'active' : '' }}">{{ __('Abreviatura') }}</label>
        {!! $errors->first('abreviatura', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Select para 'stock' con opciones 'Suma', 'Resta', 'No' -->
    <div class="input-field col s12 m6">
        <select name="stock" id="stock" class="validate @error('stock') invalid @enderror">
            <option value="" disabled selected>Selecciona una opción para Stock</option>
            <option value="Suma" {{ old('stock', $tipodocumento?->stock) == 'Suma' ? 'selected' : '' }}>Suma</option>
            <option value="Resta" {{ old('stock', $tipodocumento?->stock) == 'Resta' ? 'selected' : '' }}>Resta</option>
            <option value="No" {{ old('stock', $tipodocumento?->stock) == 'No' ? 'selected' : '' }}>No</option>
        </select>
        
        <label for="stock">{{ __('Stock') }}</label>
        {!! $errors->first('stock', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Select para 'tipomovimiento_id' cargando los Tipos de Movimiento -->
    <div class="input-field col s12 m6">
        <select name="tipomovimiento_id" id="tipomovimiento_id" class="validate @error('tipomovimiento_id') invalid @enderror">
            <option value="" disabled selected>Selecciona el Tipo de Movimiento</option>
            @foreach ($tipomovimientos as $tipomovimiento)
                <option value="{{ $tipomovimiento->id }}" {{ old('tipomovimiento_id', $tipodocumento?->tipomovimiento_id) == $tipomovimiento->id ? 'selected' : '' }}>
                    {{ $tipomovimiento->descripcion }}
                </option>
            @endforeach
        </select>
        <label for="tipomovimiento_id">{{ __('Tipo de Movimiento') }}</label>
        {!! $errors->first('tipomovimiento_id', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
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
    