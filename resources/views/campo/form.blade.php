<div class="col s12">

    <!-- Campo de selección para 'tabla_id' -->
    <div class="input-field col s12 m2">
        <select name="tabla_id" id="tabla_id" class="@error('tabla_id') invalid @enderror">
            @foreach ($tablas as $tabla)
                <option value="{{ $tabla->id }}"
                    {{ old('tabla_id', $campo?->tabla_id) == $tabla->id ? 'selected' : '' }}>
                    {{ $tabla->comentario }}
                </option>
            @endforeach
        </select>
        <label for="tabla_id" class="{{ $campo?->tabla_id ? 'active' : '' }}">{{ __('Tabla') }}</label>
        {!! $errors->first('tabla_id', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de texto para 'descripcion' -->
    <div class="input-field col s12 m3">
        <input type="text" name="descripcion" id="descripcion" class="validate @error('descripcion') invalid @enderror"
            value="{{ old('descripcion', $campo?->descripcion) }}">
        <label for="descripcion" class="{{ $campo?->descripcion ? 'active' : '' }}">{{ __('Descripción') }}</label>
        {!! $errors->first('descripcion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de texto para 'comentario' -->
    <div class="input-field col s12 m3">
        <input type="text" name="comentario" id="comentario" class="validate @error('comentario') invalid @enderror"
            value="{{ old('comentario', $campo?->comentario) }}">
        <label for="comentario" class="{{ $campo?->comentario ? 'active' : '' }}">{{ __('Comentario') }}</label>
        {!! $errors->first('comentario', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <!-- Campo de selección para 'alineacionreporte' -->
    <div class="input-field col s12 m3">
        <select name="alineacionreporte" id="alineacionreporte" class="@error('alineacionreporte') invalid @enderror">
            <option value="L" {{ old('alineacionreporte', $campo?->alineacionreporte) == 'L' ? 'selected' : '' }}>
                {{ __('Izquierda') }}</option>
            <option value="C" {{ old('alineacionreporte', $campo?->alineacionreporte) == 'C' ? 'selected' : '' }}>
                {{ __('Centro') }}</option>
            <option value="R" {{ old('alineacionreporte', $campo?->alineacionreporte) == 'R' ? 'selected' : '' }}>
                {{ __('Derecha') }}</option>
        </select>
        <label for="alineacionreporte">{{ __('Alineación Reporte') }}</label>
        {!! $errors->first(
            'alineacionreporte',
            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
        ) !!}
    </div>


    <!-- Campo checkbox para 'validacion' -->
    <div class="col s12 m1" style="display: flex; align-items: center; padding-top: 30px;">
        <label>
            <input type="checkbox" name="validacion" id="validacion" value="S"
                {{ old('validacion', $campo?->validacion) == 'S' ? 'checked' : '' }} />
            <span>{{ __('Ordenable') }}</span>
        </label>
        {!! $errors->first('validacion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

</div>

<!-- Botones de Acción -->
<div class="col s12 mt20 mt-2">
    <div class="modal-footer">
        <button type="submit" class="btn waves-effect waves-light"><i class="material-icons left">send</i>{{ __('Guardar') }}</button>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
    </div>
</div>
