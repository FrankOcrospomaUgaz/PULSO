@extends('layouts.app')

@section('template_title')
    {{ $movhabitacion->name ?? __('Show') . " " . __('Movhabitacion') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Movhabitacion</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('movhabitacions.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Descuento:</strong>
                            {{ $movhabitacion->descuento }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechaingreso:</strong>
                            {{ $movhabitacion->fechaingreso }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechasalida:</strong>
                            {{ $movhabitacion->fechasalida }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Dias:</strong>
                            {{ $movhabitacion->dias }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Total:</strong>
                            {{ $movhabitacion->total }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Preciohabitacion:</strong>
                            {{ $movhabitacion->preciohabitacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $movhabitacion->situacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Comentario:</strong>
                            {{ $movhabitacion->comentario }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Moneda:</strong>
                            {{ $movhabitacion->moneda }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Habitacion Id:</strong>
                            {{ $movhabitacion->habitacion_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Reserva Id:</strong>
                            {{ $movhabitacion->reserva_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Movimiento Id:</strong>
                            {{ $movhabitacion->movimiento_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Early Checkin:</strong>
                            {{ $movhabitacion->early_checkin }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Late Checkout:</strong>
                            {{ $movhabitacion->late_checkout }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Day Use:</strong>
                            {{ $movhabitacion->day_use }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $movhabitacion->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
