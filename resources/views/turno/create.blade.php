

<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title"> Turno | Nuevo </span>
            </div>
            <div class="card-body bg-white">
                <!-- Formulario sin action definido -->
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    <!-- Campo oculto para la URL de acción -->
                    <input type="hidden" id="form-action-url" value="{{ route('turno.store') }}">
                    @include('turno.form')
                </form>
            </div>
        </div>
    </div>
</div>

