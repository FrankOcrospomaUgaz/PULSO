<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <p class="grey-text text-darken-3 without-tb center card-title" id="modal-title">
                    <i class="fa-solid fa-plus left"></i>Salón | Nuevo Servicio
                </p>
            </div>
            <div class="card-body bg-white">
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="form-action-url" value="{{ route('salon.servicios.store', [$salonid, $salonService->id]) }}">
                    @include('salon-service.form')
                </form>
            </div>
        </div>
    </div>
</div>
