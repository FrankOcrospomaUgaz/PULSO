<div class="col s12">
    <!-- Campo de selección para 'tabla_id' -->
    <div class="input-field col s12 m3">

        <select name="tabla_id" id="tabla_id" class="@error('tabla_id') invalid @enderror"
            @if (old('tabla_id', $operacione?->tabla_id))  @endif>
            @foreach ($tablas as $tabla)
                <option value="{{ $tabla->id }}"
                    {{ old('tabla_id', $operacione?->tabla_id) == $tabla->id ? 'selected' : '' }}>
                    {{ $tabla->comentario }}
                </option>
            @endforeach
        </select>

        <label for="tabla_id" >{{ __('Tabla') }}</label>
        {!! $errors->first(
            'tabla_id',
            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
        ) !!}
    </div>



    <div class="input-field col s12 m3">
        <input type="text" name="descripcion" id="descripcion"
            class="validate @error('descripcion') invalid @enderror"
            value="{{ old('descripcion', $operacione?->descripcion) }}">
        <label for="descripcion" class="{{ $operacione?->descripcion ? 'active' : '' }}">{{ __('Descripcion') }}</label>
        {!! $errors->first(
            'descripcion',
            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
        ) !!}
    </div>

    <div class="input-field col s12 m3">
        <input type="text" name="comentario" id="comentario" class="validate @error('comentario') invalid @enderror"
            value="{{ old('comentario', $operacione?->comentario) }}">
        <label for="comentario"class="{{ $operacione?->comentario ? 'active' : '' }}">{{ __('Nombre') }}</label>
        {!! $errors->first(
            'comentario',
            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
        ) !!}
    </div>

    <div class="input-field col s12 m3">
        <input type="text" name="accion" id="accion" class="validate @error('accion') invalid @enderror"
            value="{{ old('accion', $operacione?->accion) }}">
        <label for="accion"class="{{ $operacione?->accion ? 'active' : '' }}">{{ __('Acción') }}</label>
        {!! $errors->first('accion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <div class="input-field col s12 m3">
        <input type="text" name="imagen" id="imagen" class="validate @error('imagen') invalid @enderror"
            value="{{ old('imagen', $operacione?->imagen) }}">
        <label for="imagen" class="{{ $operacione?->imagen ? 'active' : '' }}">{{ __('Ícono') }}</label>
        {!! $errors->first('imagen', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <div class="input-field col s12 m3">
        <input type="text" name="color" id="color" class="validate @error('color') invalid @enderror"
            value="{{ old('color', $operacione?->color) }}">
        <label for="color" class="{{ $operacione?->color ? 'active' : '' }}">{{ __('color') }}</label>
        {!! $errors->first('color', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <div class="input-field col s12 m3">
        <input type="text" name="parametros" id="parametros" class="validate @error('parametros') invalid @enderror"
            value="{{ old('parametros', $operacione?->parametros ?? '{}') }}">
        <label for="parametros" class="{{ $operacione?->parametros ? 'active' : 'active' }}">{{ __('Parametros') }}</label>
        {!! $errors->first(
            'parametros',
            '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
        ) !!}
    </div>
    <div class="input-field col s12 m3">
        <select name="tipo" id="tipo" class="@error('tipo') invalid @enderror">
            <option value="t" {{ old('tipo', $operacione?->tipo) === 'Tabla' ? 'selected' : '' }}>Tabla</option>
            <option value="r" {{ old('tipo', $operacione?->tipo) === 'Registro' ? 'selected' : '' }}>Registro</option>
        </select>
        <label for="tipo">{{ __('Tipo') }}</label>
        {!! $errors->first('tipo', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>


</div>

<!-- Botones de Acción -->
{{-- <div class="col s12 mt20 mt-2">
    <button type="submit" class="btn waves-effect waves-light">
        <i class="material-icons left">send</i>{{ __('Guardar') }}
    </button>
    @isset($id)
        <a href="{{ route('tablas.operaciones.index', $id) }}" class="btn-flat waves-effect"><i
                class="material-icons left">cancel</i>{{ __('Cancelar') }}</a>
    @else
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
    @endisset



</div> --}}



<!-- Botones de Acción -->
<div class="col s12 mt20 mt-2">
    <div class="modal-footer">
        <button type="submit" class="btn waves-effect waves-light">
            <i class="material-icons left">send</i>{{ __('Guardar') }}
        </button>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
    </div>
</div>
