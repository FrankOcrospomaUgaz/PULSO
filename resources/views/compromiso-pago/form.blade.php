<div class="col s12">
    <input type="hidden" name="sucursal_id" id="sucursal_id" value="{{ old('sucursal_id', $sucursal_id ?? '') }}">
        <input type="hidden" name="compromiso_id" id="compromiso_id" value="{{ old('compromiso_id', $item->id ?? '') }}">
        <input type="hidden" name="contrato_id" id="contrato_id" value="{{ old('contrato_id', $contrato->id ?? '') }}">



    <!-- Select de Persona -->
    <div class="input-field col s12 m6">
        <select name="persona_id" id="persona_id" class="validate @error('persona_id') invalid @enderror">
            <option value="" disabled selected>Selecciona una persona</option>
            @foreach ($personas as $persona)
                <option value="{{ $persona->id }}" {{ old('persona_id', $item->persona_id ?? '') == $persona->id ? 'selected' : '' }}>
                    {{ $persona->nombres . ' ' . $persona->apellidos }}
                </option>
            @endforeach
        </select>
        <label for="persona_id">{{ __('Persona') }}</label>
        {!! $errors->first('persona_id', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
    </div>

    <!-- Select de Ambiente -->
    <div class="input-field col s12 m6">
        <select name="ambiente_id" id="ambiente_id" class="validate @error('ambiente_id') invalid @enderror">
            <option value="" disabled selected>Selecciona un ambiente</option>
            @foreach ($ambientes as $ambiente)
                <option value="{{ $ambiente->id }}" {{ old('ambiente_id', $item->ambiente_id ?? '') == $ambiente->id ? 'selected' : '' }}>
                    {{ $ambiente->numero }} - {{ $ambiente->piso->nombre ?? '' }}
                </option>
            @endforeach
        </select>
        <label for="ambiente_id">{{ __('Ambiente') }}</label>
        {!! $errors->first('ambiente_id', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
    </div>

    <!-- Select de Edificio (opcional si aplicas edificios) -->
    @if(isset($edificios))
        <div class="input-field col s12 m6">
            <select name="edificio_id" id="edificio_id" class="validate @error('edificio_id') invalid @enderror">
                <option value="" disabled selected>Selecciona un edificio</option>
                @foreach ($edificios as $edificio)
                    <option value="{{ $edificio->id }}" {{ old('edificio_id', $item->ambiente->edificio_id ?? '') == $edificio->id ? 'selected' : '' }}>
                        {{ $edificio->nombre }}
                    </option>
                @endforeach
            </select>
            <label for="edificio_id">{{ __('Edificio') }}</label>
            {!! $errors->first('edificio_id', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
        </div>
    @endif

    <!-- Fecha Vencimiento -->
    <div class="input-field col s12 m6">
        <input type="date" name="fecha_vencimiento" id="fecha_vencimiento"
            class="validate @error('fecha_vencimiento') invalid @enderror"
            value="{{ old('fecha_vencimiento', $item->fecha_vencimiento ?? '') }}">
        <label for="fecha_vencimiento" class="active">Fecha Vencimiento</label>
        {!! $errors->first('fecha_vencimiento', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
    </div>

    <!-- Fecha Pago -->
    <div class="input-field col s12 m6">
        <input type="date" name="fecha_pago" id="fecha_pago"
            class="validate @error('fecha_pago') invalid @enderror"
            value="{{ old('fecha_pago', $item->fecha_pago ?? '') }}">
        <label for="fecha_pago" class="active">Fecha Pago</label>
        {!! $errors->first('fecha_pago', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
    </div>

    <!-- Número de Cuota -->
    <div class="input-field col s12 m6">
        <input type="number" name="numero_cuota" id="numero_cuota"
            class="validate @error('numero_cuota') invalid @enderror"
            value="{{ old('numero_cuota', $item->numero_cuota ?? '') }}">
        <label for="numero_cuota" class="active">Número de Cuota</label>
        {!! $errors->first('numero_cuota', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
    </div>

    <!-- Monto -->
    <div class="input-field col s12 m6">
        <input type="number" step="0.01" name="monto" id="monto"
            class="validate @error('monto') invalid @enderror"
            value="{{ old('monto', $item->monto ?? '') }}">
        <label for="monto" class="active">Monto</label>
        {!! $errors->first('monto', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
    </div>

<!-- Forma de Pago -->
<div class="input-field col s12 m6">
    <select name="forma_pago" id="forma_pago" class="validate @error('forma_pago') invalid @enderror">
        <option value="" disabled {{ old('forma_pago', $item->forma_pago ?? '') == '' ? 'selected' : '' }}>Selecciona forma de pago</option>
        @foreach (['efectivo', 'tarjeta', 'deposito', 'billetera', 'Transferencia'] as $opcion)
            <option value="{{ $opcion }}" {{ old('forma_pago', $item->forma_pago ?? '') == $opcion ? 'selected' : '' }}>
                {{ ucfirst($opcion) }}
            </option>
        @endforeach
    </select>
    <label for="forma_pago">Forma de Pago</label>
    {!! $errors->first('forma_pago', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
</div>


<!-- Estado -->
<div class="input-field col s12 m6">
    <select name="estado" id="estado" class="validate @error('estado') invalid @enderror">
        <option value="" disabled {{ old('estado', $item->estado ?? '') == '' ? 'selected' : '' }}>Selecciona estado</option>
        @foreach (['pendiente', 'pagado', 'vencido'] as $opcion)
            <option value="{{ $opcion }}" {{ old('estado', $item->estado ?? '') == $opcion ? 'selected' : '' }}>
                {{ ucfirst($opcion) }}
            </option>
        @endforeach
    </select>
    <label for="estado">Estado</label>
    {!! $errors->first('estado', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
</div>

    <!-- Botones -->
    <div class="col s12 mt-2">
        <div class="modal-footer">
            <button type="submit" class="btn waves-effect waves-light">
                <i class="material-icons left">send</i>Guardar
            </button>
            <a href="#" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
        </div>
    </div>
</div>
