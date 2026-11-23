@extends('layouts.app')

@section('template_title')
    {{ $salonService->name ?? __('Show') . " " . __('Salon Service') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Salon Service</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('salon-services.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Salon Id:</strong>
                            {{ $salonService->salon_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Titulo:</strong>
                            {{ $salonService->titulo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Descripcion:</strong>
                            {{ $salonService->descripcion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Icono:</strong>
                            {{ $salonService->icono }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
