<div class="col s12">
    <!-- Campo de selección para 'Tabla Id' -->
    <div class="input-field col s12 m6">
        <select name="tabla_id" id="tabla_id" class="validate @error('tabla_id') invalid @enderror">
            <option value="" disabled selected>Selecciona una Tabla</option>
            @foreach($tablas as $tabla)
                <option value="{{ $tabla->id }}" {{ old('tabla_id', $relTablasucursal?->tabla_id) == $tabla->id ? 'selected' : '' }}>
                    {{ $tabla->comentario }}
                </option>
            @endforeach
        </select>
        <label for="tabla_id">{{ __('Tabla Id') }}</label>
        {!! $errors->first('tabla_id', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>


    <input type="hidden" name="sucursal_id" value="{{ $sucursal_id }}">

    <!-- Botones de Acción -->
    <div class="col s12 mt20 mt-2">
        <div class="modal-footer">
            <button type="submit" class="btn waves-effect waves-light"><i class="material-icons left">send</i>{{ __('Guardar') }}</button>
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
        </div>
    </div>
</div>
