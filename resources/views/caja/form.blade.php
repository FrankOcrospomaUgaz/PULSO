<div class="row">
    <div class="col s12">
        <!-- Campo de texto para 'numero' -->
        <div class="input-field col s12 m3">
            <input type="text" name="numero" id="numero" class="validate @error('numero') invalid @enderror"
                value="{{ old('numero', $caja?->numero) }}">
            <label for="numero" class="{{ $caja?->numero ? 'active' : '' }}">{{ __('Número') }}</label>
            {!! $errors->first('numero', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de texto para 'serie' -->
        <div class="input-field col s12 m3">
            <input type="text" name="serie" id="serie" class="validate @error('serie') invalid @enderror"
                value="{{ old('serie', $caja?->serie) }}">
            <label for="serie" class="{{ $caja?->serie ? 'active' : '' }}">{{ __('Serie para comprobante') }}</label>
            {!! $errors->first('serie', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>
        <!-- Campo oculto para 'sucursal_id' -->
        <input type="hidden" name="sucursal_id" value="{{ $sucursal_id }}">
    </div>

 
</div>
   <!-- Botones de acción -->
    <div class="col s12 mt20 mt-2">
        <div class="modal-footer">
            <button type="submit" class="btn waves-effect waves-light">
                <i class="material-icons left">send</i>{{ __('Guardar') }}
            </button>
           <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
        </div>
    </div>