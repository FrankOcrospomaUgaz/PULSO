
    <div class="col s12">
        <!-- Campo de selección para 'tabla_id' -->
        <div class="input-field col s12 m4">
            <select name="tabla_id" id="tabla_id" class="@error('tabla_id') invalid @enderror">
                @foreach($tablas as $tabla)
                    <option value="{{ $tabla->id }}" {{ old('tabla_id', $parametro?->tabla_id) == $tabla->id ? 'selected' : '' }}>
                        {{ $tabla->comentario }}
                    </option>
                @endforeach
            </select>
            <label for="tabla_id">{{ __('Tabla') }}</label>
            {!! $errors->first('tabla_id', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de texto para 'descripcion' -->
        <div class="input-field col s12 m4">
            <input type="text" name="descripcion" id="descripcion" class="validate @error('descripcion') invalid @enderror" value="{{ old('descripcion', $parametro?->descripcion) }}">
            <label for="descripcion"  class="{{ $parametro?->descripcion ? 'active' : '' }}">{{ __('Descripcion') }}</label>
            {!! $errors->first('descripcion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de texto para 'valor' -->
        <div class="input-field col s12 m4">
            <input type="text" name="valor" id="valor" class="validate @error('valor') invalid @enderror" value="{{ old('valor', $parametro?->valor) }}">
            <label for="valor"  class="{{ $parametro?->valor ? 'active' : '' }}">{{ __('Valor') }}</label>
            {!! $errors->first('valor', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de selección para 'tipo' -->
        <div class="input-field col s12 m4">
            <select name="tipo" id="tipo" class="@error('tipo') invalid @enderror">
                <option value="EMPRESA" {{ old('tipo', $parametro?->tipo) == 'EMPRESA' ? 'selected' : '' }}>{{ __('Empresa') }}</option>
                <option value="SUCURSAL" {{ old('tipo', $parametro?->tipo) == 'SUCURSAL' ? 'selected' : '' }}>{{ __('Sucursal') }}</option>
            </select>
            <label for="tipo">{{ __('Tipo') }}</label>
            {!! $errors->first('tipo', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>
        <!-- Campo de selección para 'categoriaparametro_id' -->
        <div class="input-field col s12 m4">
            <select name="categoriaparametro_id" id="categoriaparametro_id" class="@error('categoriaparametro_id') invalid @enderror">
                @foreach($categoriaparametros as $categoriaparametro)
                    <option value="{{ $categoriaparametro->id }}" {{ old('categoriaparametro_id', $parametro?->categoriaparametro_id) == $categoriaparametro->id ? 'selected' : '' }}>
                        {{ $categoriaparametro->descripcion }}
                    </option>
                @endforeach
            </select>
            <label for="categoriaparametro_id">{{ __('Categoría Parámetro') }}</label>
            {!! $errors->first('categoriaparametro_id', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>
<!-- Campo hidden para 'obligatorio' con valor por defecto 'N' -->
<input type="hidden" name="obligatorio" value="N">

<!-- Campo checkbox para 'obligatorio' -->
<div class="col s12 m4" style="display: flex; align-items: center; padding-top: 30px;">
    <label>
        <input type="checkbox" name="obligatorio" id="obligatorio" value="S" 
               {{ old('obligatorio', $parametro?->obligatorio) == 'S' ? 'checked' : '' }} />
        <span>{{ __('Obligatorio') }}</span>
    </label>
    {!! $errors->first('obligatorio', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
</div>



    </div>
    <!-- Botones de Acción -->
    <div class="col s12 mt20 mt-2">
        <div class="modal-footer">
        <button type="submit" class="btn waves-effect waves-light"><i class="material-icons left">send</i>{{ __('Guardar') }}</button>

            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
        </div>
    </div>

