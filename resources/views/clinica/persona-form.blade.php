<div class="main-body clinica-persona-form">
    <div class="row" style="margin-top:0">
        <div class="col s12">
            <div class="card-content">
                <span class="card-title"><i class="fa-solid fa-plus left"></i>Pacientes | Nuevo</span>
            </div>
            <div class="card-body bg-white">
                <form id="form" method="POST" enctype="multipart/form-data" class="clinica-form">
                    @csrf
                    <input type="hidden" id="form-action-url" value="{{ !empty($editing) ? route('persona.update', $persona->id) : route('persona.store') }}">
                    <input type="hidden" name="registro_clinico" value="1">
                    @if (!empty($editing)) @method('PUT') @endif
                    @include('persona.form')
                </form>
            </div>
        </div>
    </div>
</div>
