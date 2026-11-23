<div class="col s12">
    <!-- Campo de texto para 'descripcion' -->
    <div class="input-field col s12 m3">
        <input type="text" name="descripcion" id="descripcion" class="validate @error('descripcion') invalid @enderror" value="{{ old('descripcion', $tarjetum?->descripcion) }}">
        <label for="descripcion" class="{{ $tarjetum?->descripcion ? 'active' : '' }}">{{ __('Descripcion') }}</label>
        {!! $errors->first('descripcion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de selección para 'tipo' (Crédito o Débito) -->
    <div class="input-field col s12 m2">
        <select name="tipo" id="tipo" class="validate @error('tipo') invalid @enderror">
            <option value="C" {{ old('tipo', $tarjetum?->tipo) == 'C' ? 'selected' : '' }}>Crédito</option>
            <option value="D" {{ old('tipo', $tarjetum?->tipo) == 'D' ? 'selected' : '' }}>Débito</option>
        </select>
        <label for="tipo" class="{{ $tarjetum?->tipo ? 'active' : '' }}">{{ __('Tipo') }}</label>
        {!! $errors->first('tipo', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de texto para 'orden' -->
    <div class="input-field col s12 m2">
        <input type="number" name="orden" id="orden" class="validate @error('orden') invalid @enderror" value="{{ old('orden', $tarjetum?->orden) ?? 0}}">
        <label for="orden" class="active">{{ __('Orden') }}</label>
        {!! $errors->first('orden', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>



    <!-- Campo de texto para 'icono' -->
    <div class="input-field col s12 m4">
        <input type="text" name="icono" id="icono" class="validate @error('icono') invalid @enderror" value="{{ old('icono', $tarjetum?->icono) }}">
        <label for="icono" class="{{ $tarjetum?->icono ? 'active' : '' }}">{{ __('Icono') }}</label>
        {!! $errors->first('icono', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
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
