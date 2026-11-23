@section('template_title')
    {{ __('Crear') }} Opción de Menú
@endsection

<div class="main-body">
    <div class="row">
        <div class="col s12">
               <div class="card-content">
                <p class="grey-text text-darken-3 without-tb center card-title" id="modal-title">
                    <i class="fa-solid fa-plus left"></i> Opciones de Menú| Nuevo 
                </p>
            </div>
                <div class="card-body bg-white">
                    <form id="form" enctype="multipart/form-data">
                        @csrf
                        <!-- Aquí colocas la URL de acción dinámica -->
                        <input type="hidden" id="form-action-url" value="{{ route('menuopciones.store') }}">
                        
                        <!-- Incluir el formulario del recurso -->
                        @include('menuopcione.form')
                    </form>
                </div>
        </div>
    </div>
</div>
