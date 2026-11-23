<div class="row">
    <div class="col s12">
        <!-- Campo de texto para 'nombre' -->
        <div class="input-field col s12 m4">
            <input type="text" name="nombre" id="nombre" class="validate @error('nombre') invalid @enderror"
                value="{{ old('nombre', $turno?->nombre) }}">
            <label for="nombre" class="{{ $turno?->nombre ? 'active' : '' }}">{{ __('Nombre') }}</label>
            {!! $errors->first('nombre', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de texto para 'abreviatura' -->
        <div class="input-field col s12 m3">
            <input type="text" name="abreviatura" id="abreviatura" class="validate @error('abreviatura') invalid @enderror"
                value="{{ old('abreviatura', $turno?->abreviatura) }}">
            <label for="abreviatura" class="{{ $turno?->abreviatura ? 'active' : '' }}">{{ __('Abreviatura') }}</label>
            {!! $errors->first('abreviatura', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de hora para 'horainicio' -->
        <div class="input-field col s12 m2">
            <input type="time" name="horainicio" id="horainicio" class="@error('horainicio') invalid @enderror"
                value="{{ old('horainicio', $turno?->horainicio) }}">
            <label for="horainicio" class="active">{{ __('Hora de Inicio') }}</label>
            {!! $errors->first('horainicio', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de hora para 'horafin' -->
        <div class="input-field col s12 m2">
            <input type="time" name="horafin" id="horafin" class="@error('horafin') invalid @enderror"
                value="{{ old('horafin', $turno?->horafin) }}">
            <label for="horafin" class="active">{{ __('Hora de Fin') }}</label>
            {!! $errors->first('horafin', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>
        <input type="hidden" name="sucursal_id" value="{{ $sucursal_id }}">

        <!-- Select para 'sucursal_id' -->
        {{-- <div class="input-field col s12 m3">
            <select name="sucursal_id" id="sucursal_id" class="@error('sucursal_id') invalid @enderror">
                <option value="" disabled selected>{{ __('Seleccione una Sucursal') }}</option>
                @foreach($sucursales as $sucursal)
                    <option value="{{ $sucursal->id }}" {{ old('sucursal_id', $turno?->sucursal_id) == $sucursal->id ? 'selected' : '' }}>
                        {{ $sucursal->nombre }}
                    </option>
                @endforeach
            </select>
            <label for="sucursal_id">{{ __('Sucursal') }}</label>
            {!! $errors->first('sucursal_id', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div> --}}
    </div>

    <!-- Botones de acción -->
    <div class="col s12 mt20 mt-2">
        <div class="modal-footer">
            <button type="submit" class="btn waves-effect waves-light">
                <i class="material-icons left">send</i>{{ __('Guardar') }}
            </button>
            <a href="#" class="waves-effect waves-green btn-flat">Cancelar</a>
        </div>
    </div>
</div>
