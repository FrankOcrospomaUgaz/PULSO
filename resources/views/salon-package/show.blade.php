@extends('layouts.app')

@section('template_title')
    {{ $salonPackage->name ?? __('Show') . " " . __('Salon Package') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Salon Package</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('salon-packages.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Salon Id:</strong>
                            {{ $salonPackage->salon_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Nombre:</strong>
                            {{ $salonPackage->nombre }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Precio:</strong>
                            {{ $salonPackage->precio }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Content:</strong>
                            {{ $salonPackage->content }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Imagen:</strong>
                            {{ $salonPackage->imagen }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
