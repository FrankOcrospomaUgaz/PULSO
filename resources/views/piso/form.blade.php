<input type="hidden" id="switchState" name="switch_state" value="unitario">

<div class="row">
    <div class="col s12 center-align">
        <div class="switch">
            <label>
                Unitario
                <input type="checkbox" id="switchHoras" onchange="togglePisoBloques()"
                       {{ isset($piso) && $piso->id ? 'disabled' : '' }}>
                <span class="lever"></span>
                Por Bloques
            </label>
        </div>
    </div>
</div>
<div class="col s12">
    <div class="input-field col s12 m4" id="unitarioForm">
        <input type="text" name="nombre" id="nombre" class="validate @error('nombre') invalid @enderror"
            value="{{ old('nombre', $piso?->nombre) }}">
        <!-- Agregar la clase 'active' si el campo ya tiene valor -->
        <label for="nombre" class="{{ $piso?->nombre ? 'active' : '' }}">{{ __('Nombre') }}</label>
        {!! $errors->first('nombre', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
    </div>
        
    <div style="display: none;" id="bloquesForm">
        <div class="input-field col s12 m4"   >
            <input type="number" id="inicio_piso" name="inicio_piso" placeholder="Inicio" min="1" value="1" />
            <label class="active" for="rango_pisos">{{ __('Desde') }}</label>
        </div>
    
        <div class="input-field col s12 m4"  >
            <label class="active" for="rango_pisos">{{ __('Hasta') }}</label>
            <input type="number" id="fin_piso" name="fin_piso" placeholder="Fin" min="1" value="8" />
        </div>
    
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


