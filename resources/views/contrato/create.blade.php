@extends('layouts.app')
@section('content')
    <div class="main-body">
        <div class="row">
            <div class="card main-content">

                <div class="card-body bg-white">
                    <form id="form" class="form-retroceder" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="form-action-url" value="{{ route('contrato.store') }}">

                            @include('contrato.form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection