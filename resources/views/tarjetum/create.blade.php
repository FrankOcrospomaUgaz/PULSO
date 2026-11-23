

<div class="main-body">
    <div class="row">
        <div class="col s12">
             <div class="card-content">
                <p class="grey-text text-darken-3 without-tb center card-title" id="modal-title">
                    <i class="fa-solid fa-plus left"></i> Tarjeta | Nuevo
            </div>
            <div class="card-body bg-white">
                <!-- Formulario sin action definido -->
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    <!-- Campo oculto para la URL de acción -->
                    <input type="hidden" id="form-action-url" value="{{ route('tarjeta.store') }}">
                    @include('tarjetum.form')
                </form>
            </div>
        </div>
    </div>
</div>

