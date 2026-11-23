<div class="main-body">
    <div class="row">
        <div class="col s12">
           <div class="card-content">
                <p class="grey-text text-darken-3 without-tb center card-title" id="modal-title">
                    <i class="fa-solid fa-edit left"></i>  Tipos de movimiento  | {{ $tipomovimiento->descripcion }} 
                </p>
            </div>
            <div class="card-body bg-white">
                <!-- Formulario sin action definido explícitamente -->
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT') <!-- Usamos el método PUT para actualizar -->

                    <!-- Campo oculto para la URL de acción -->
                    <input type="hidden" id="form-action-url" value="{{ route('tipomovimiento.update', $tipomovimiento->id) }}">
                    
                    @include('tipomovimiento.form') <!-- Incluimos el formulario compartido -->
                </form>
            </div>
        </div>
    </div>
</div>
