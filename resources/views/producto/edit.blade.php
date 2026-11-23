
<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title">{{ __('Actualizar') }} Producto</span>
            </div>
            <div class="card-body bg-white">
                <!-- Formulario sin action definido -->
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT') <!-- Agrega este campo para el método correcto -->

                    <!-- Campo oculto para la URL de acción -->
                    <input type="hidden" id="form-action-url" value="{{ route('producto.update', $producto->id) }}">
                    @include('producto.form')
                </form>
            </div>
        </div>
    </div>
</div>
