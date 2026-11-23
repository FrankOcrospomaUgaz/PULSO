<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <p class="grey-text text-darken-3 without-tb center card-title" id="modal-title">
                    <i class="fa-solid fa-edit left"></i> Pasarela de Pago | {{ $pasarelapago->descripcion }}
            </div>
            <div class="card-body bg-white">
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Campo oculto para la URL de acción -->
                    <input type="hidden" id="form-action-url" value="{{ route('pasarelapago.update', $pasarelapago->id) }}">
                    @include('pasarelapago.form')
                </form>
            </div>
        </div>
    </div>
</div>
