<div class="col s12">
    <input type="hidden" id="switchState" name="switch_state" value="unitario">

    <div class="row">
        <div class="col s12 center-align">
            <div class="switch">
                <label>
                    Unitario
                    <input type="checkbox" id="switchHoras" onchange="toggleHabitacionBloques()"
                        {{ isset($habitacion) && $habitacion->id ? 'disabled' : '' }}>
                    <span class="lever"></span>
                    Por Bloques
                </label>
            </div>
        </div>
    </div>

    <!-- Campo de texto para 'numero' -->
    <div class="input-field col s12 m4" id="unitarioForm">
        <input type="text" name="numero" id="numero" class="validate @error('numero') invalid @enderror"
            value="{{ old('numero', $habitacion?->numero) }}">
        <label for="numero" class="{{ $habitacion?->numero ? 'active' : '' }}">{{ __('Número') }}</label>
        {!! $errors->first('numero', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de texto para 'descripcion' -->
    @if (old('descripcion', $habitacion?->descripcion))
        <div class="input-field col s12 m4">
            <input type="text" name="descripcion" id="descripcion"
                class="validate @error('descripcion') invalid @enderror"
                value="{{ old('descripcion', $habitacion?->descripcion) }}">
            <label for="descripcion" class="active">{{ __('Descripción') }}</label>
            {!! $errors->first(
                'descripcion',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>
        <!-- Campo de texto para 'precio' -->
        <div class="input-field col s12 m4">
            <input type="number" name="precio" id="precio" class="validate @error('precio') invalid @enderror"
                value="{{ old('precio', $habitacion?->precio) }}">
            <label for="precio" class="{{ $habitacion?->precio ? 'active' : '' }}">{{ __('Precio por dia') }}</label>
            {!! $errors->first('precio', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>
    @endif



    <div style="display: none;" id="bloquesForm">
        <div class="input-field col s12 m6">
            <input type="text" name="formato" id="formato" class="validate @error('formato') invalid @enderror"
                value="Hab-">
            <label for="formato" class="active">{{ __('Formato') }}</label>
        </div>
        <div class="input-field col s12 m6">
            <input type="number" id="inicio_habitacion" name="inicio_habitacion" placeholder="Inicio" min="1"
                value="1" />
            <label class="active" for="rango_habitaciones">{{ __('Desde') }}</label>
        </div>

        <div class="input-field col s12 m6">
            <input type="number" id="fin_habitacion" name="fin_habitacion" placeholder="Fin" min="1"
                value="12" />
            <label class="active" for="rango_habitaciones">{{ __('Hasta') }}</label>
        </div>
    </div>

    <!-- Select para 'piso_id' -->
    <div class="input-field col s12 m6">
        <select name="piso_id" id="piso_id" class="validate @error('piso_id') invalid @enderror">
            <option value="" disabled selected>Selecciona el piso</option>
            @foreach ($pisos as $piso)
                <option value="{{ $piso->id }}"
                    {{ old('piso_id', $habitacion?->piso_id) == $piso->id ? 'selected' : '' }}>
                    {{ $piso->nombre }}
                </option>
            @endforeach
        </select>
        <label for="piso_id">{{ __('Piso') }}</label>
        {!! $errors->first(
            'piso_id',
            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
        ) !!}
    </div>

    <!-- Select estilizado para 'tipohabitacion_id' -->
    <div class="input-field col s12 m6">
        <select name="tipohabitacion_id" id="tipohabitacion_id"
            class="validate @error('tipohabitacion_id') invalid @enderror">
            <option value="" disabled selected>Selecciona el tipo de habitación</option>
            @foreach ($tipohabitaciones as $tipohabitacion)
                <option value="{{ $tipohabitacion->id }}"
                    {{ old('tipohabitacion_id', $habitacion?->tipohabitacion_id) == $tipohabitacion->id ? 'selected' : '' }}>
                    {{ $tipohabitacion->nombre }}
                </option>
            @endforeach
        </select>
        <label for="tipohabitacion_id">{{ __('Tipo de Habitación') }}</label>
        {!! $errors->first(
            'tipohabitacion_id',
            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
        ) !!}
    </div>
    <!-- Campo oculto para 'sucursal_id' -->
    <input type="hidden" name="sucursal_id" value="{{ $sucursal_id }}">
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
