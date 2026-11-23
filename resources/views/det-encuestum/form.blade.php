<div class="col s12">
    <div class="row">
        <!-- Enunciado -->
        <div class="input-field col s12">
            <input type="text" name="enunciado" id="enunciado" class="validate" value="{{ old('enunciado', $detEncuestum?->enunciado) }}">
            <label for="enunciado" class="{{ $detEncuestum?->enunciado ? 'active' : '' }}">{{ __('Enunciado') }}</label>
        </div>
    
        <!-- Categoría y Tipo -->
        <div class="col s12 m6">
            <div class="input-field">
                <select name="categoriapregunta_id" id="categoriapregunta_id">
                    <option value="" disabled selected>Selecciona una Categoría</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}" {{ old('categoriapregunta_id', $detEncuestum?->categoriapregunta_id) == $categoria->id ? 'selected' : '' }}>
{{ $categoria->nombre }}
                        </option>
                    @endforeach
                </select>
                <label for="categoriapregunta_id">{{ __('Categoría') }}</label>
            </div>
        </div>
    
        <div class="col s12 m6">
            <div class="input-field">
                <select name="tipopregunta_id" id="tipopregunta_id">
                    @foreach ($tipos as $tipo)
                        <option value="{{ $tipo->id }}" 
                                {{ old('tipopregunta_id', $detEncuestum?->tipopregunta_id) == $tipo->id ? 'selected' : '' }}>
{{ $tipo->nombre }}
                        </option>
                    @endforeach
                </select>
                <label for="tipopregunta_id">{{ __('Tipo') }}</label>
            </div>
        </div>
        
        <div class="col s12 m6">
            <p class="left-align">
                <label for="obligatorio">
                    <input type="checkbox" name="obligatorio" id="obligatorio" value="1" 
                    {{ old('obligatorio', $detEncuestum?->obligatorio) ? 'checked' : '' }} />
                    <span>Obligatoria</span>
                    <span class="helper-text" id="obligatorio_spn" data-error="" data-success=""></span>
                </label>
            </p>
        </div>
    


            
        <!-- Opciones (con chips) -->
        <div class="col s12 m6">
            <div class="chips" id="chips-opciones"></div>
            <input type="hidden" name="opciones" id="opciones" value="{{ old('opciones', $detEncuestum?->opciones) }}">
            <small class="grey-text">Pulse "Enter" para agregar ítems</small>
        </div>
        <input type="hidden" name="encuesta_id" value="{{ $encuesta_id }}">

        <!-- Botones -->
        <div class="col s12 right-align">
            <a href="#" class="btn-flat waves-effect">Cancelar</a>
            <button type="submit" class="btn waves-effect waves-light">
                <i class="material-icons left">send</i>{{ __('Guardar') }}
            </button>
        </div>
    </div>

</div>
