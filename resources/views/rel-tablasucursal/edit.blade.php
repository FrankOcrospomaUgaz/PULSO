

<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title">  <i class="fa-solid fa-plus left"></i>Tablas por sucursal | Editar</span>
            </div>
            <div class="card-body bg-white">
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT') <!-- Agrega este campo para el método correcto -->

                    <input type="hidden" id="form-action-url" value="{{ route('rel_tablasucursal.update', $relTablasucursal->id) }}">
                    @include('rel-tablasucursal.form')
                </form>
            </div>
        </div>
    </div>
</div>
