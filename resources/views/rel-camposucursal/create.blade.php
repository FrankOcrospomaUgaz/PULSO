@extends('layouts.app')

@section('template_title')
    {{ __('Create') }} Rel Camposucursal
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">{{ __('Create') }} Rel Camposucursal</span>
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('rel-camposucursals.store') }}"  role="form" enctype="multipart/form-data">
                            @csrf

                            @include('rel-camposucursal.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
