<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title"><i class="fa-solid fa-circle-up left"></i>Importar productos </span>
            </div>
            <div class="card-body bg-white">
                <!-- Formulario para importar archivo -->
                <form id="form" enctype="multipart/form-data" method="POST">
                    @csrf
                    <!-- Campo oculto para la URL de acción -->
                    <input type="hidden" id="form-action-url" value="{{ route('sucursal.importarProductos', ['id' => $sucursal_id]) }}">
                    
                    <!-- Campo para el archivo -->
                    <div class="file-field input-field col s12 m6">
                        <div class="btn left">
                            <span><i class="fa-solid fa-circle-up"></i></span>
                            <input id="productos" name="productos" type="file" accept=".xls,.xlsx">
                        </div>
                        <div class="file-path-wrapper">
                            <input class="file-path validate" type="text" placeholder="No se ha seleccionado ningún archivo">
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="col s12 mt20 mt-2">
                        <div class="modal-footer right-align">
                            <button type="button" class="btn-flat modal-close">Cancelar</button>
                            <button type="submit" class="btn darken-2">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
