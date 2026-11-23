

<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title">  <i class="fa-solid fa-plus left"></i>Parámetros por sucursal | Nuevo</span>
            </div>
            <div class="card-body bg-white">
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="form-action-url" value="{{ route('rel_parametrosucursal.store') }}">
                    @include('rel-parametrosucursal.form')
                </form>
            </div>
        </div>
    </div>
</div>
