<div class="col s12">
    <div class="input-field col s12 m6">
        <input type="text" name="descripcion" id="descripcion" class="validate @error('descripcion') invalid @enderror"
            value="{{ old('descripcion', $categoria?->descripcion) }}">
        <label for="descripcion" class="{{ $categoria?->descripcion ? 'active' : '' }}">{{ __('Descripción') }}</label>
        {!! $errors->first('descripcion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
    </div>

    <div class="input-field col s12 m6">
        <input type="text" name="abreviatura" id="abreviatura" class="validate @error('abreviatura') invalid @enderror"
            value="{{ old('abreviatura', $categoria?->abreviatura) }}">
        <label for="abreviatura" class="{{ $categoria?->abreviatura ? 'active' : '' }}">{{ __('Abreviatura') }}</label>
        {!! $errors->first('abreviatura', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
    </div>
</div>

<div class="col s12">
    <div class="input-field col s12 m6">
<select name="tipocarta" id="tipocarta">
    <option value="RECEPCIÓN" {{ old('tipocarta', $tipocarta_actual ?? '') == 'RECEPCIÓN' ? 'selected' : '' }}>RECEPCIÓN</option>
    <option value="NINGUNA" {{ old('tipocarta', $tipocarta_actual ?? '') == 'NINGUNA' ? 'selected' : '' }}>NINGUNA</option>
</select>

<label for="tipocarta">{{ __('Tipo Carta') }}</label>

    </div>
    <div class="input-field col s12 m6">
        <select name="categoria_id" id="categoria_id">
            <option value="" disabled selected>Seleccione Categoría Padre</option>
            @foreach ($categorias as $cat)
                <option value="{{ $cat->id }}" {{ old('categoria_id', $categoria?->categoria_id) == $cat->id ? 'selected' : '' }}>{{ $cat->descripcion }}</option>
            @endforeach
        </select>
        <label for="categoria_id">{{ __('Categoría Padre') }}</label>
    </div>
    <input type="hidden" name="sucursal_id" value="{{$sucursal_id}}">

    <input type="hidden" name="empresa_id" value="{{$empresa_id}}">

    {{-- <div class="input-field col s12 m6">
        <select name="empresa_id" id="empresa_id">
            <option value="" disabled selected>Seleccione Empresa</option>
            @foreach ($empresas as $empresa)
                <option value="{{ $empresa->id }}" {{ old('empresa_id', $categoria?->empresa_id) == $empresa->id ? 'selected' : '' }}>{{ $empresa->nombre }}</option>
            @endforeach
        </select>
        <label for="empresa_id">{{ __('Empresa') }}</label>
    </div> --}}

    <!-- Campo de carga de imagen -->
<div class="col s12 m6">
    <div class="file-field input-field input-group-field">
        <div class="btn left green">
            <span><i class="fa-solid fa-upload"></i></span>
            <input id="imagen" name="imagen" type="file" accept="image/*">
        </div>
        <div class="file-path-wrapper left-wrapper input-field">
            <input id="imagen_txt" class="file-path validate" type="text" placeholder="No se ha seleccionado ningún archivo">
        </div>
    </div>
</div>

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
