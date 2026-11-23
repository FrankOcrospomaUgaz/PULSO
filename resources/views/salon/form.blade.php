<div class="row">
    <!-- Columna izquierda: Inputs -->
    <div class="col s12 m7">
        <!-- Campo oculto para 'sucursal_id' -->
        <input type="hidden" name="sucursal_id" id="sucursal_id" value="{{ $sucursal_id }}">

        <!-- Campo de texto para 'titulo' -->
        <div class="input-field col s12 m6">
            <input type="text" name="titulo" id="titulo" class="validate @error('titulo') invalid @enderror"
                value="{{ old('titulo', $salon?->titulo) }}">
            <label for="titulo" class="{{ $salon?->titulo ? 'active' : '' }}">{{ __('Título') }}</label>
            {!! $errors->first('titulo', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de texto para 'descripcion_corta' -->
        <div class="input-field col s12 m6">
            <input type="text" name="descripcion_corta" id="descripcion_corta" class="validate @error('descripcion_corta') invalid @enderror"
                value="{{ old('descripcion_corta', $salon?->descripcion_corta) }}">
            <label for="descripcion_corta" class="{{ $salon?->descripcion_corta ? 'active' : '' }}">{{ __('Descripción Corta') }}</label>
            {!! $errors->first('descripcion_corta', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

<!-- Campo de texto para 'descripcion_larga' -->
<div class="input-field col s12">
    <textarea name="descripcion_larga" id="descripcion_larga"
        class="materialize-textarea validate @error('descripcion_larga') invalid @enderror"
        rows="1"
        style="height: auto; overflow-y: hidden; resize: vertical;"
        oninput="this.style.height='';this.style.height=this.scrollHeight+'px'">{{ old('descripcion_larga', $salon?->descripcion_larga) }}</textarea>

    <label for="descripcion_larga" class="{{ $salon?->descripcion_larga ? 'active' : '' }}">
        {{ __('Descripción Larga') }}
    </label>

    {!! $errors->first('descripcion_larga', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
</div>

    </div>

    <!-- Columna derecha: Imagen -->
    <div class="col s12 m5">
        <div class="card-panel grey lighten-5 center-align">
            <h6 class="teal-text text-darken-2 mb-2">Imagen del Salón</h6>

            <!-- Vista previa -->
            <div class="image-preview mb-2">
                <img id="preview-imagen"
                    src="{{ old('imagen', $salon?->imagen) ? asset($salon->imagen) : 'https://via.placeholder.com/220x150?text=Sin+imagen' }}"
                    alt="Vista previa" class="responsive-img z-depth-1"
                    style="max-height: 200px; object-fit: contain;">
            </div>

            <!-- Imagen antigua -->
            <input type="hidden" name="imagen_antigua" value="{{ old('imagen', $salon?->imagen) }}">

            <!-- Input de archivo -->
            <div class="file-field input-field">
                <div class="btn-small teal darken-2">
                    <span><i class="material-icons left">upload</i>Seleccionar Imagen</span>
                    <input type="file" name="imagen" id="file-imagen" accept="image/*" onchange="previewImagen(this)">
                </div>
                <div class="file-path-wrapper">
                    <input class="file-path validate" type="text" placeholder="Sube una imagen (opcional)">
                </div>
            </div>

            {!! $errors->first('imagen', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
        </div>
    </div>
</div>

<!-- Botones de acción -->
<div class="col s12 mt20 mt-2">
    <div class="modal-footer">
        <button type="submit" class="btn waves-effect waves-light">
            <i class="material-icons left">send</i>Guardar
        </button>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
    </div>
</div>
