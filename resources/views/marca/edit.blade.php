<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title">{{ __('Actualizar') }} Marca</span>
            </div>
            <div class="card-body bg-white">
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="form-action-url" value="{{ route('marca.update', $marca->id) }}">
                    @include('marca.form')
                </form>
            </div>
        </div>
    </div>
</div>
