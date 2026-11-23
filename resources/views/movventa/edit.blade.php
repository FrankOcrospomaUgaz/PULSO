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

                    <!-- Campo oculto para la URL de acción -->
                    <input type="hidden" id="form-action-url" value="{{ route('movventa.update', $movventa->id) }}">
                    @include('movventa.form')
                </form>
            </div>
        </div>
    </div>
</div>


@endsection

