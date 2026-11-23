@section('template_title')
    {{ __('Actualizar') }} Tabla
@endsection

<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <p class="grey-text text-darken-3 without-tb center card-title" id="modal-title">
                    <i class="fa-solid fa-edit left"></i>Tablas | {{ $tabla->descripcion }}
                </p>
            </div>
            <div class="card-content">
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH') <!-- Método PATCH para actualizar -->

                    <!-- Campo oculto para la URL de acción dinámica -->
                    <input type="hidden" id="form-action-url" value="{{ route('tablas.update', $tabla->id) }}">

                    @include('tabla.form') <!-- Incluir el formulario con los campos -->
                </form>
            </div>
        </div>
    </div>
</div>
