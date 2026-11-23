

@section('template_title')
    {{ __('Crear') }} Sucursal
@endsection

<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title">{{ __('Nueva') }} Sucursal</span>
            </div>
            <div class="card-body bg-white">
                <!-- Formulario sin action definido -->
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    <!-- Campo oculto para la URL de acción -->
                    <input type="hidden" id="form-action-url" value="{{ route('sucursal.store') }}">
                    @include('sucursal.form')
                </form>
            </div>
        </div>
    </div>
</div>
