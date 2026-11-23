@extends('layouts.app')

@section('template_title')
    {{ $movreservaPaquete->name ?? __('Show') . " " . __('Movreserva Paquete') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Movreserva Paquete</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('movreserva-paquetes.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Observacion:</strong>
                            {{ $movreservaPaquete->observacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Nropersonas:</strong>
                            {{ $movreservaPaquete->nropersonas }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechavencimiento:</strong>
                            {{ $movreservaPaquete->fechavencimiento }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechainicio:</strong>
                            {{ $movreservaPaquete->fechainicio }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechasalida:</strong>
                            {{ $movreservaPaquete->fechasalida }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechacancelacion:</strong>
                            {{ $movreservaPaquete->fechacancelacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Precio Paquete:</strong>
                            {{ $movreservaPaquete->precio_paquete }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Total:</strong>
                            {{ $movreservaPaquete->total }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Salon Package Id:</strong>
                            {{ $movreservaPaquete->salon_package_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Salon Package Name:</strong>
                            {{ $movreservaPaquete->salon_package_name }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Paquetes:</strong>
                            {{ $movreservaPaquete->json_paquetes }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Estado:</strong>
                            {{ $movreservaPaquete->estado }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Movimiento Id:</strong>
                            {{ $movreservaPaquete->movimiento_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $movreservaPaquete->sucursal_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Celular:</strong>
                            {{ $movreservaPaquete->celular }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Email:</strong>
                            {{ $movreservaPaquete->email }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
