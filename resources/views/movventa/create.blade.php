@extends('layouts.app')
@section('content')
    <div class="main-body">
        <div class="row">
            <div class="card main-content">

                <div class="card-body bg-white">
                    <form id="form" class="form-retroceder" enctype="multipart/form-data">
                        @csrf

                        <!-- Campo oculto para la URL de acción dinámica -->
                        <input type="hidden" id="form-action-url" value="{{ route('movventa.store') }}">
                        @include('movventa.form') <!-- Incluir el formulario con los campos -->
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection
