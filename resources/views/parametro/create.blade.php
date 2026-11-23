
<div class="main-body">
    <div class="row">
        <div class="col s12">
              <div class="card-content">
                <p class="grey-text text-darken-3 without-tb center card-title" id="modal-title">
                    <i class="fa-solid fa-plus left"></i> Parámetros | Nuevo 
                </p>
            </div>
            <div class="card-body bg-white">
                <form id="form" enctype="multipart/form-data">
                    @csrf

                    <!-- Campo oculto para la URL de acción dinámica -->
                    <input type="hidden" id="form-action-url" value="{{ route('parametros.store') }}">

                    @include('parametro.form') <!-- Incluir el formulario con los campos -->
                </form>
            </div>
        </div>
    </div>
</div>
