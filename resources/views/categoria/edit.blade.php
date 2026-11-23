<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title">{{ __('Actualizar') }} Categoría</span>
            </div>
            <div class="card-body bg-white">
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="form-action-url" value="{{ route('categoria.update', $categoria->id) }}">
                    @include('categoria.form')
                </form>
            </div>
        </div>
    </div>
</div>
