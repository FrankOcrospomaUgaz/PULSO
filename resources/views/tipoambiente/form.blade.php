<div class="col s12">
    <input type="hidden" name="tabla_id" value="89">

    <div class="input-field col s12 m6">
        <input type="text" id="nombre" name="nombre"
            class="validate @error('nombre') invalid @enderror"
            value="{{ old('nombre', $tipoambiente->nombre ?? '') }}">
        <label for="nombre" class="{{ old('nombre', $tipoambiente->nombre ?? '') ? 'active' : '' }}">
            {{ __('Nombre del tipo de ambiente') }}
        </label>
        {!! $errors->first('nombre', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>

    <div class="input-field col s12 m6">
        <textarea id="descripcion" name="descripcion" class="materialize-textarea validate @error('descripcion') invalid @enderror">{{ old('descripcion', $tipoambiente->descripcion ?? '') }}</textarea>
        <label for="descripcion" class="{{ old('descripcion', $tipoambiente->descripcion ?? '') ? 'active' : '' }}">
            {{ __('Descripción') }}
        </label>
        {!! $errors->first('descripcion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
    </div>
</div>

<div class="col s12 mt20 mt-2">
    <div class="modal-footer">
        <button type="submit" class="btn waves-effect waves-light">
            <i class="material-icons left">save</i>{{ __('Guardar') }}
        </button>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">{{ __('Cerrar') }}</a>
    </div>
</div>
