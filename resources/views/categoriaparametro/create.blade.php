
@section('template_title')
    {{ __('Crear') }} Encuesta
@endsection
<div class="main-body">
    <div class="row">
        <div class="col s12">
                <div class="card-content">
                    <p class="grey-text text-darken-3 without-tb center card-title" id="modal-title">
                        <i class="fa-solid fa-plus left"></i>Categoría parametro | Nuevo
                    </p>                </div>
                <div class="card-body bg-white">
                        <form method="POST" action="{{ route('categoriaparametros.store') }}"  role="form" enctype="multipart/form-data">
                            @csrf

                            @include('categoriaparametro.form')

                        </form>
                </div>
        </div>
    </div>
</div>


