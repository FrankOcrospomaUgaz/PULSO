
<div class="main-body">
    <div class="row">
        <div class="col s12">
           <div class="card-content">
                <p class="grey-text text-darken-3 without-tb center card-title" id="modal-title">
                    <i class="fa-solid fa-plus left"></i>  Tipos de movimiento  | Nuevo 
                </p>
            </div>
            <div class="card-body bg-white">
                <!-- Formulario dinámico con AJAX -->
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    <!-- Campo oculto para la URL dinámica de creación -->
                    <input type="hidden" id="form-action-url" value="{{ route('tipomovimiento.store') }}">
                    @include('tipomovimiento.form')
                </form>
            </div>
        </div>
    </div>
</div>
