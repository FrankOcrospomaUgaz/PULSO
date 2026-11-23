@extends('layouts.app')

@section('template_title')
    {{ $movreserva->name ?? __('Show') . " " . __('Movreserva') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Movreserva</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('movreservas.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Observacion:</strong>
                            {{ $movreserva->observacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Nropersonas:</strong>
                            {{ $movreserva->nropersonas }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechavencimiento:</strong>
                            {{ $movreserva->fechavencimiento }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechainicio:</strong>
                            {{ $movreserva->fechainicio }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechasalida:</strong>
                            {{ $movreserva->fechasalida }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechacancelacion:</strong>
                            {{ $movreserva->fechacancelacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Dias:</strong>
                            {{ $movreserva->dias }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Moneda:</strong>
                            {{ $movreserva->moneda }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipocambio:</strong>
                            {{ $movreserva->tipocambio }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Total:</strong>
                            {{ $movreserva->total }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Habitacionprincipal Id:</strong>
                            {{ $movreserva->habitacionprincipal_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Habitacion Principal:</strong>
                            {{ $movreserva->habitacion_principal }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Habitaciones:</strong>
                            {{ $movreserva->json_habitaciones }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Estado:</strong>
                            {{ $movreserva->estado }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Movimiento Id:</strong>
                            {{ $movreserva->movimiento_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Motivo Id:</strong>
                            {{ $movreserva->motivo_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $movreserva->sucursal_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Celular:</strong>
                            {{ $movreserva->celular }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Email:</strong>
                            {{ $movreserva->email }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
