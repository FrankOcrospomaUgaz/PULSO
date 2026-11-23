<div class="main-body">
    <div class="row">
        <div class="col s12">


            <div class="card-content">
                <p class="grey-text text-darken-3 without-tb center card-title" id="modal-title">
                    <i class="fa-solid fa-image left"></i>Imágenes del tipo: {{ $tipohabitacion->nombre }}
                </p>
            </div>
            <form id="form" enctype="multipart/form-data" style="margin-top: -120px">
                @csrf
                <input type="hidden" id="form-action-url"
                    value="{{ route('imagenes.actualizar', $tipohabitacion->id) }}">
                <!-- Galería de imágenes existentes -->
                <div id="imagenes-container" class="row">
                    @foreach ($imagenes as $imagen)
                        <div class="col s12 m6 l3 imagen-container">
                            <div class="card hoverable">
                                <div class="card-image">
                                    <img src="{{ asset($imagen->url) }}" alt="Imagen" class="responsive-img">
                                </div>
                                <div class="card-content">
                                    <div class="input-field">
                                        <input type="text" name="reemplazar_imagenes[{{ $imagen->id }}][descripcion]"
                                            value="{{ $imagen->descripcion }}" placeholder="Descripción" class="validate">
                                    </div>

                                    <div class="center-align" style="margin-bottom: 10px;">
                                        <button type="button" class="btn-small teal darken-1 waves-effect"
                                            onclick="document.getElementById('file-input-{{ $imagen->id }}').click();">
                                            <i class="material-icons left">image</i> Reemplazar
                                        </button>
                                        <input type="file" id="file-input-{{ $imagen->id }}"
    name="reemplazar_imagenes[{{ $imagen->id }}][imagen]" accept="image/*"
    style="display: none;" onchange="imageChanged(this, '{{ $imagen->id }}')">

                                    </div>

                                    <p class="center-align">
                                        <label>
                                            <input type="checkbox" class="filled-in red-text" name="eliminar_imagenes[]"
                                                value="{{ $imagen->id }}">
                                            <span>Eliminar esta imagen</span>
                                        </label>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>


                <!-- Acciones -->
                <div class="row">
                    <div class="col s12 m6">
                        <button type="button" class="btn blue darken-2 waves-effect waves-light"
                            onclick="agregarNuevaImagen()">
                            <i class="material-icons left">add_a_photo</i> Agregar Nueva Imagen
                        </button>
                    </div>

                    <div class="col s12 m6 right-align">
                        <div class="modal-footer">
                            <button type="submit" class="btn waves-effect waves-light"><i
                                    class="material-icons left">send</i>{{ __('Guardar') }}</button>
                            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .card-image {
        height: 220px;
        background: #f9f9f9;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
    }

    .card-image img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .card-content {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding-bottom: 15px;
        min-height: 210px;
    }

    .input-field input[type="text"] {
        margin-top: 8px;
        font-size: 14px;
    }

    .btn,
    .btn-small {
        border-radius: 20px;
    }

    .row {
        margin-bottom: 15px;
    }

    .card.hoverable {
        transition: box-shadow 0.3s ease;
    }

    .card.hoverable:hover {
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .center-align .material-icons {
        vertical-align: middle;
    }
</style>