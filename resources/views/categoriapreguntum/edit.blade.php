

@section('template_title')
    {{ __('Actualizar') }} Pregunta
@endsection


<div class="main-body">
    <div class="row">
        <div class="col s12">
                <div class="card-content">
                    <span class="card-title">{{ __('Actualizar') }} Categoría Pregunta</span>
                    </div>
                    <div class="card-body bg-white">
                        <form id="form" enctype="multipart/form-data">
                            @csrf
                            @method('PUT') <!-- Agrega este campo para el método correcto -->
        
                            <!-- Campo oculto para la URL de acción -->
                            <input type="hidden" id="form-action-url" value="{{ route('categoriapregunta.update', $categoriapreguntum->id)  }}">
                            @include('categoriapreguntum.form')
                        </form>
                    </div>
            </div>
        </div>
    </div>

