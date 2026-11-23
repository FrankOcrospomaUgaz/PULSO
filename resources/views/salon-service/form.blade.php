<div class="col s12">
    <!-- Campo oculto para 'salon_id' -->
    <input type="hidden" id="salon_id" name="salon_id" value="{{ $salonid }}">

    <!-- Campo de texto para 'titulo' -->
    <div class="input-field col s12 m4">
        <input type="text" name="titulo" id="titulo" class="validate @error('titulo') invalid @enderror"
            value="{{ old('titulo', $salonService?->titulo) }}">
        <label for="titulo" class="{{ $salonService?->titulo ? 'active' : '' }}">{{ __('Título') }}</label>
        {!! $errors->first('titulo', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de texto para 'descripcion' -->
    <div class="input-field col s12 m8">
        <input type="text" name="descripcion" id="descripcion" class="validate @error('descripcion') invalid @enderror"
            value="{{ old('descripcion', $salonService?->descripcion) }}">
        <label for="descripcion" class="{{ $salonService?->descripcion ? 'active' : '' }}">{{ __('Descripción') }}</label>
        {!! $errors->first('descripcion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de texto para 'icono' -->
    <div class="input-field col s12 m4">
        <input type="text" name="icono" id="icono" class="validate @error('icono') invalid @enderror"
            value="{{ old('icono', $salonService?->icono) }}">
        <label for="icono" class="{{ $salonService?->icono ? 'active' : '' }}">
            {{ __('Clase del Ícono') }} <small>(ej: <code>fa-solid fa-cake-candles</code>)</small>
        </label>
        {!! $errors->first('icono', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>
</div>

<!-- Botones de Acción -->
<div class="col s12 mt20 mt-2">
    <div class="modal-footer">
        <button type="submit" class="btn waves-effect waves-light">
            <i class="material-icons left">send</i>{{ __('Guardar') }}
        </button>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">{{ __('Cerrar') }}</a>
    </div>
</div>
