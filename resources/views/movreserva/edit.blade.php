@extends('layouts.app')
@section('content')
    <div class="main-body">
        <div class="row">
            <div class="card main-content">

                <div class="card-body bg-white">
                <!-- Formulario sin action definido -->
                <form id="form" class="form-retroceder" enctype="multipart/form-data">
                    @csrf
                    @method('PUT') <!-- Agrega este campo para el método correcto -->

              <!-- Campo oculto para la URL de acción dinámica -->
              <input type="hidden" id="form-action-url" value="{{ route('movreserva.update', $movreserva->id) }}">
              <input type="hidden" id="switchState" name="switch_state"
              value="{{ isset($movreserva) && $movreserva->modo === 'G' ? 'grupal' : 'unico' }}">
          
              <div class="row">
                <div class="col s12 center-align">
                    <div class="switch">
                        <label>
                            Único
                            <input type="checkbox" id="switchHoras" onchange="toggleModoReserva()"
                                {{ $movreserva->modo === 'G' ? 'checked' : '' }}>
                            <span class="lever"></span>
                            Grupal
                        </label>
                    </div>
                </div>
            </div>
            
                    @include('movreserva.form')
                </form>
            </div>
        </div>
    </div>
</div>


@endsection

