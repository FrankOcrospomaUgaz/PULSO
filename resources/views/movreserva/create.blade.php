@extends('layouts.app')
@section('content')
    <div class="main-body">
        <div class="row">
            <div class="card main-content">

                <div class="card-body bg-white">
                    <form id="form" class="form-retroceder" enctype="multipart/form-data">
                        @csrf

                        <!-- Campo oculto para la URL de acción dinámica -->
                        <input type="hidden" id="form-action-url" value="{{ route('movreserva.store') }}">
                        <input type="hidden" id="switchState" name="switch_state" value="unico">

                        <div class="row">
                            <div class="col s12 center-align">
                                <div class="switch">
                                    <label>
                                        Único
                                        <input type="checkbox" id="switchHoras" onchange="toggleModoReserva()">
                                        <span class="lever"></span>
                                        Grupal
                                    </label>
                                </div>
                            </div>
                        </div>
                        @include('movreserva.form') <!-- Incluir el formulario con los campos -->
                    </form>
                </div>
            </div>
        </div>
    </div>

  
@endsection
