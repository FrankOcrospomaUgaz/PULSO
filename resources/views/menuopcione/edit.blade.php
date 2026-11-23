@section('template_title')
    {{ __('Actualizar') }} Opción de Menú
@endsection

<div class="main-body">
    <div class="row">
        <div class="col s12">
             <div class="card-content">
                <p class="grey-text text-darken-3 without-tb center card-title" id="modal-title">
                    <i class="fa-solid fa-edit left"></i> Opciones de Menú | {{ $menuopcione->descripcion }} 
                </p>
            </div>
            <div class="card-body bg-white">
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT') <!-- Método PUT para actualizar -->

                    <!-- Campo oculto para la URL de acción -->
                    <input type="hidden" id="form-action-url" value="{{ route('menuopciones.update', $menuopcione->id) }}">

                    <!-- Incluir el formulario con los campos -->
                    @include('menuopcione.form')
                </form>
            </div>
        </div>
    </div>
</div>
