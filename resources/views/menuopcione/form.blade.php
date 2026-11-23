<div class="col s12">
    <!-- Campo de selección para 'Tabla Id' -->
    <div class="input-field col s12 m6">
        <select name="tabla_id" id="tabla_id" class="validate @error('tabla_id') invalid @enderror">
            <option value="" disabled selected>Selecciona una Tabla</option>
            @foreach($tablas as $tabla)
                <option value="{{ $tabla->id }}" {{ old('tabla_id', $menuopcione?->tabla_id) == $tabla->id ? 'selected' : '' }}>
                    {{ $tabla->comentario }}
                </option>
            @endforeach
        </select>
        <label for="tabla_id" >Tabla</label>
        {!! $errors->first('tabla_id', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de selección para 'Modulo Id' -->
    <div class="input-field col s12 m6">
        <select name="modulo_id" id="modulo_id" class="validate @error('modulo_id') invalid @enderror">
            <option value="" disabled selected>Selecciona un Módulo</option>
            @foreach($modulos as $modulo)
                <option value="{{ $modulo->id }}" {{ old('modulo_id', $menuopcione?->modulo_id) == $modulo->id ? 'selected' : '' }}>
                    {{ $modulo->descripcion }}
                </option>
            @endforeach
        </select>
        <label for="modulo_id">Módulo</label>
        {!! $errors->first('modulo_id', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de selección para 'Tipoopcion Id' -->
    <div class="input-field col s12 m6">
        <select name="tipoopcion_id" id="tipoopcion_id" class="validate @error('tipoopcion_id') invalid @enderror">
            <option value="" disabled selected>Selecciona un Tipo de Opción</option>
            @foreach($tipoopciones as $tipoopcion)
                <option value="{{ $tipoopcion->id }}" {{ old('tipoopcion_id', $menuopcione?->tipoopcion_id) == $tipoopcion->id ? 'selected' : '' }}>
                    {{ $tipoopcion->descripcion }}
                </option>
            @endforeach
        </select>
        <label for="tipoopcion_id" >Tipo de Opción</label>
        {!! $errors->first('tipoopcion_id', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo para 'Condiciones AR' -->
    <div class="input-field col s12 m6">
        <input type="text" name="condiciones_ar" id="condiciones_ar" class="validate @error('condiciones_ar') invalid @enderror" value="{{ old('condiciones_ar', $menuopcione?->condiciones_ar) }}">
        <label for="condiciones_ar" class="{{ $menuopcione?->condiciones_ar ? 'active' : '' }}">Condiciones AR</label>
        {!! $errors->first('condiciones_ar', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo para 'Descripción' -->
    <div class="input-field col s12 m6">
        <input type="text" name="descripcion" id="descripcion" class="validate @error('descripcion') invalid @enderror" value="{{ old('descripcion', $menuopcione?->descripcion) }}">
        <label for="descripcion" class="{{ $menuopcione?->descripcion ? 'active' : '' }}">Descripción</label>
        {!! $errors->first('descripcion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo para 'Orden' -->
    {{-- <div class="input-field col s12 m6">
        <input type="number" name="orden" id="orden" class="validate @error('orden') invalid @enderror" value="{{ old('orden', $menuopcione?->orden ?? 0) }}">
        <label for="orden" class="active">Orden</label>
        {!! $errors->first('orden', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div> --}}

    <!-- Campo para 'Icono' -->
    <div class="input-field col s12 m6">
        <input type="text" name="icono" id="icono" class="validate @error('icono') invalid @enderror" value="{{ old('icono', $menuopcione?->icono) }}">
        <label for="icono" class="{{ $menuopcione?->icono ? 'active' : '' }}">Ícono</label>
        {!! $errors->first('icono', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo para 'Acción' -->
    <div class="input-field col s12 m6">
        <input type="text" name="accion" id="accion" class="validate @error('accion') invalid @enderror" value="{{ old('accion', $menuopcione?->accion) }}">
        <label for="accion" class="{{ $menuopcione?->accion ? 'active' : '' }}">Acción</label>
        {!! $errors->first('accion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo para 'Acceso Rápido' -->
    <div class="input-field col s12 m6" style="display: flex; align-items: center; padding-top: 20px;">
        <label>
            <input type="checkbox" name="acceso_rapido" id="acceso_rapido" value="S" {{ old('acceso_rapido', $menuopcione?->acceso_rapido) == 'S' ? 'checked' : '' }}>
            <span>Acceso Rápido</span>
        </label>
        {!! $errors->first('acceso_rapido', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>
</div>

<!-- Botones de Acción -->
<div class="col s12 mt20 mt-2">
    <div class="modal-footer">
        <button type="submit" class="btn waves-effect waves-light">
            <i class="material-icons left">send</i>Guardar
        </button>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
    </div>
</div>
