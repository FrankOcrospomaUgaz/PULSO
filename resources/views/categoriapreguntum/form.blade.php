<div class="col s12">
    <div class="row">
        <!-- nombre -->
        <div class="input-field col s12 m4">
            <input type="text" name="nombre" id="nombre" class="validate" value="{{ old('nombre', $categoriapreguntum?->nombre) }}">
            <label for="nombre" class="{{ $categoriapreguntum?->nombre ? 'active' : '' }}">{{ __('Nombre') }}</label>
        </div>
        <input type="hidden" name="sucursal_id" value="{{ $sucursal_id }}">

        <!-- Botones -->
        <div class="col s12 right-align">
            <a href="#" class="btn-flat waves-effect">Cancelar</a>
            <button type="submit" class="btn waves-effect waves-light">
                <i class="material-icons left">send</i>{{ __('Guardar') }}
            </button>
        </div>
    </div>

</div>
