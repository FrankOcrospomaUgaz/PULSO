<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title"> Datos de facturación | Nuevo </span>
            </div>
            <div class="card-body bg-white">
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="form-action-url" value="{{ route('facturador.store') }}">

                    @include('facturador.form')

                </form>
            </div>
        </div>
    </div>
</div>