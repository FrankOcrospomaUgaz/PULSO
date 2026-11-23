@extends('layouts.app')

@section('template_title')
    {{ __('Update') }} Housekeeping Detalle
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="">
            <div class="col-md-12">

                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">{{ __('Update') }} Housekeeping Detalle</span>
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('housekeeping-detalles.update', $housekeepingDetalle->id) }}"  role="form" enctype="multipart/form-data">
                            {{ method_field('PATCH') }}
                            @csrf

                            @include('housekeeping-detalle.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
