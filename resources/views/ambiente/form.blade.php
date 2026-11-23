<div class="col s12">
    <input type="hidden" name="sucursal_id" id="sucursal_id"
        value="{{ old('sucursal_id', $ambiente->sucursal_id ?? $sucursal_id ?? '') }}">
    <!-- Formulario unitario -->
    <div class="input-field col s12 m4" id="unitarioForm">
        <input type="text" name="numero" id="numero" class="validate @error('numero') invalid @enderror"
            value="{{ old('numero', $ambiente->numero ?? '') }}">
        <label for="numero"
            class="{{ !empty(old('numero', $ambiente->numero ?? '')) ? 'active' : '' }}">{{ __('Número') }}</label>
        {!! $errors->first('numero', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <div class="input-field col s12 m4">
        <input type="number" name="monto" id="monto" class="validate @error('monto') invalid @enderror"
            value="{{ old('monto', $ambiente->monto ?? '') }}">
        <label for="monto"
            class="{{ !empty(old('monto', $ambiente->monto ?? '')) ? 'active' : '' }}">{{ __('Monto') }}</label>
        {!! $errors->first('monto', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <div class="input-field col s12 m4">
        <input type="number" name="garantia" id="garantia" class="validate @error('garantia') invalid @enderror"
            value="{{ old('garantia', $ambiente->garantia ?? '') }}">
        <label for="garantia"
            class="{{ !empty(old('garantia', $ambiente->garantia ?? '')) ? 'active' : '' }}">{{ __('Garantía') }}</label>
        {!! $errors->first('garantia', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Formulario por bloques -->
    <div style="display: none;" id="bloquesForm">
        <div class="input-field col s12 m6">
            <input type="text" name="formato" id="formato" class="validate" value="Amb-">
            <label for="formato" class="active">{{ __('Formato') }}</label>
        </div>

        <div class="input-field col s12 m3">
            <input type="number" id="inicio_ambiente" name="inicio_ambiente" placeholder="Inicio" min="1" value="1">
            <label class="active" for="inicio_ambiente">{{ __('Desde') }}</label>
        </div>

        <div class="input-field col s12 m3">
            <input type="number" id="fin_ambiente" name="fin_ambiente" placeholder="Fin" min="1" value="10">
            <label class="active" for="fin_ambiente">{{ __('Hasta') }}</label>
        </div>
    </div>

<!-- Select de tipo de ambiente -->
<div class="input-field col s12 m6">
    <select name="tipoambiente_id" id="tipoambiente_id" class="validate @error('tipoambiente_id') invalid @enderror">
        <option value="" disabled selected>Selecciona tipo</option>
        @foreach ($tipos as $tipoambiente)
            <option value="{{ $tipoambiente->id }}" {{ old('tipoambiente_id', $ambiente->tipoambiente_id ?? '') == $tipoambiente->id ? 'selected' : '' }}>
                {{ $tipoambiente->nombre }}
            </option>
        @endforeach
    </select>
    <label for="tipoambiente_id">{{ __('Tipo de Ambiente') }}</label>
    {!! $errors->first('tipoambiente_id', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
</div>

    <!-- Select de piso -->
    <div class="input-field col s12 m6">
        <select name="piso_id" id="piso_id" class="validate @error('piso_id') invalid @enderror">
            <option value="" disabled selected>Selecciona el piso</option>
            @foreach ($pisos as $piso)
                <option value="{{ $piso->id }}" {{ old('piso_id', $ambiente->piso_id ?? '') == $piso->id ? 'selected' : '' }}>
                    {{ $piso->nombre }}
                </option>
            @endforeach
        </select>
        <label for="piso_id">{{ __('Piso') }}</label>
        {!! $errors->first('piso_id', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Select de edificio -->
    <div class="input-field col s12 m6">
        <select name="edificio_id" id="edificio_id" class="validate @error('edificio_id') invalid @enderror">
            <option value="" disabled selected>Selecciona el edificio</option>
            @foreach ($edificios as $edificio)
                <option value="{{ $edificio->id }}" {{ old('edificio_id', $ambiente->edificio_id ?? '') == $edificio->id ? 'selected' : '' }}>
                    {{ $edificio->nombre }}
                </option>
            @endforeach
        </select>
        <label for="edificio_id">{{ __('Edificio') }}</label>
        {!! $errors->first('edificio_id', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>
</div>

<!-- Botones -->
<div class="col s12 mt20 mt-2">
    <div class="modal-footer">
        <button type="submit" class="btn waves-effect waves-light">
            <i class="material-icons left">send</i>{{ __('Guardar') }}
        </button>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
    </div>
</div>