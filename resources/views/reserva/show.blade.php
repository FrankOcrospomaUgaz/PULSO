@extends('layouts.app')

@section('template_title')
    {{ $reserva->name ?? __('Show') . " " . __('Reserva') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Reserva</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('reservas.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Fecha:</strong>
                            {{ $reserva->fecha }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechasalida:</strong>
                            {{ $reserva->fechasalida }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Observacion:</strong>
                            {{ $reserva->observacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $reserva->situacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Persona Id:</strong>
                            {{ $reserva->persona_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $reserva->sucursal_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Habitacion Id:</strong>
                            {{ $reserva->habitacion_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
