<div class="col s12">
    <!-- Campo de selección para 'parametro_id' -->
    <div class="input-field col s12 m6">
        <select name="parametro_id" id="parametro_id" class="validate @error('parametro_id') invalid @enderror">
            <option value="" disabled selected>Selecciona un Parámetro</option>
            @foreach($parametros as $parametro)
                <option value="{{ $parametro->id }}" {{ old('parametro_id', $relParametroempresa?->parametro_id) == $parametro->id ? 'selected' : '' }}>
                    {{ $parametro->descripcion }}
                </option>
            @endforeach
        </select>
        <label for="parametro_id">{{ __('Parámetro') }}</label>
        {!! $errors->first('parametro_id', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

<!-- Campo para 'Valor' -->     
<div class="input-field col s12 m6">
    <input type="text" name="valor" id="valor" class="validate @error('valor') invalid @enderror" value="{{ old('valor', $relParametroempresa?->valor) }}">
    <label for="valor" class="{{ $relParametroempresa?->valor ? 'active' : '' }}">{{ __('Valor') }}</label>
    {!! $errors->first('valor', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
</div>


<!-- Campo oculto para 'Sucursal ID' -->
<input type="hidden" name="empresa_id" value="{{ $empresa_id }}">

<!-- Botones de Acción -->
<div class="col s12 mt20 mt-2">
    <div class="modal-footer">
        <button type="submit" class="btn waves-effect waves-light"><i class="material-icons left">send</i>{{ __('Guardar') }}</button>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
    </div>
</div>
</div>
