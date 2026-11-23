    <div class="row">
        <!-- Columna izquierda: Inputs -->
        <div class="col s12 m7">
            <!-- Campo oculto para 'salon_id' -->
            <input type="hidden" name="salon_id" id="salon_id" value="{{ old('salon_id', $salonid) }}">

            
                <!-- Nombre -->
                <div class="input-field col s12 m6">
                    <input type="text" name="nombre" id="nombre"
                        class="validate @error('nombre') invalid @enderror"
                        value="{{ old('nombre', $salonPackage?->nombre) }}">
                    <label for="nombre" class="{{ $salonPackage?->nombre ? 'active' : '' }}">Nombre</label>
                    {!! $errors->first('nombre', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
                </div>

                <!-- Precio -->
                <div class="input-field col s12 m6">
                    <input type="number" name="precio" id="precio" step="0.01"
                        class="validate @error('precio') invalid @enderror"
                        value="{{ old('precio', $salonPackage?->precio) }}">
                    <label for="precio" class="{{ $salonPackage?->precio ? 'active' : '' }}">Precio</label>
                    {!! $errors->first('precio', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
                </div>
   

            <!-- Content -->
            <div class="input-field col s12">
                <textarea name="content" id="content" class="materialize-textarea validate @error('content') invalid @enderror"
                    rows="1" style="height: auto; overflow-y: hidden; resize: vertical;"
                    oninput="this.style.height='';this.style.height=this.scrollHeight+'px'">{{ old('content', $salonPackage?->content) }}</textarea>

                <label for="content" class="{{ $salonPackage?->content ? 'active' : '' }}">Contenido del
                    Paquete</label>
                {!! $errors->first('content', '<span class="helper-text red-text"><strong>:message</strong></span>') !!}
            </div>

        </div>

        <!-- Columna derecha: Imagen -->
        <div class="col s12 m5">
            <div class="card-panel grey lighten-5 center-align">
                <h6 class="teal-text text-darken-2 mb-2">Imagen del Paquete</h6>

                <!-- Vista previa -->
                <div class="image-preview mb-2">
                    <img id="preview-imagen"
                        src="{{ old('imagen', $salonPackage?->imagen) ? asset($salonPackage->imagen) : 'https://via.placeholder.com/220x150?text=Sin+imagen' }}"
                        alt="Vista previa" class="responsive-img z-depth-1"
                        style="max-height: 200px; object-fit: contain;">
                </div>

                <!-- Hidden imagen antigua -->
                <input type="hidden" name="imagen_antigua" value="{{ old('imagen', $salonPackage?->imagen) }}">

                <!-- Input de archivo -->
                <div class="file-field input-field">
                    <div class="btn-small teal darken-2">
                        <span><i class="material-icons left">upload</i>Seleccionar Imagen</span>
                        <input type="file" name="imagen" id="file-imagen" accept="image/*"
                            onchange="previewImagen(this)">
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
