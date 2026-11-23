@extends('layouts.app')


@section('content')
    <div class="main-body">
        <div class="row">
            <div class="card main-content">

                <div class="card-body bg-white">
                    <form id="form" class="form-retroceder" enctype="multipart/form-data">
                        @csrf

                        <!-- Campo oculto para la URL de acción dinámica -->
                        <input type="hidden" id="form-action-url" value="{{ route('movreserva.atenderstore') }}">
                        <input type="hidden" name="movreserva_id" value="{{ $movreserva->id }}">
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
                        @include('movreserva.formatender') <!-- Incluir el formulario con los campos -->



                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
{{-- <div class="row padding-1 p-1">
    <div class="col s12 mt20 mt-2">
        <div class="modal-footer right-align">
            <button type="submit" class="btn darken-2"><i
                    class="material-icons right">print</i>Checkin</button>
            <a href="#" class="modal-close waves-effect waves-green btn-flat">CANCELAR</a>
        </div>
    </div>
</div> --}}