<div class="main-body">
    <div class="row">
        <div class="col s12">
            <div class="card-content">
                <p class="grey-text text-darken-3 without-tb center card-title" id="modal-title">
                   <i class="material-icons grey-text text-darken-3 left">edit</i>{{ $salonService->titulo }}
                </p>
            </div>
            <div class="card-body bg-white">
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="form-action-url" value="{{ route('salon.servicios.update', [$salonid, $salonService->id]) }}">
                    @include('salon-service.form')
                </form>
            </div>
        </div>
    </div>
</div>
