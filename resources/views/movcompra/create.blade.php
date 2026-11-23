@extends('layouts.app')
@section('content')
    <div class="main-body">
        <div class="row">
            <div class="card main-content">
                <div class="card-body bg-white">
                    <form id="form" class="form-retroceder form-movcompra" enctype="multipart/form-data" data-modulo="compra">
                        @csrf
                        <input type="hidden" id="form-action-url" value="{{ route('movcompra.store') }}">
                        <input type="hidden" id="porcentaje_igv" value="{{ $porcentajeIgv }}">
                        @include('movcompra.form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
