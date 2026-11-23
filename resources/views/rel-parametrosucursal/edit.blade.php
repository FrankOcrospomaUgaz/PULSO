

<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title">  <i class="fa-solid fa-plus left"></i>Parámetros por sucursal | Editar</span>
            </div>
            <div class="card-body bg-white">
                <!-- Formulario sin action definido -->
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT') <!-- Agrega este campo para el método correcto -->

                    <!-- Campo oculto para la URL de acción -->
                    <input type="hidden" id="form-action-url" value="{{ route('rel_parametrosucursal.update', $relParametrosucursal->id) }}">
                    @include('rel-parametrosucursal.form')
                </form>
            </div>
        </div>
    </div>
</div>

