@extends('layouts.app')

@section('template_title')
    {{ $historialHabitacion->name ?? __('Show') . " " . __('Historial Habitacion') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Historial Habitacion</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('historial-habitacions.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion Pago:</strong>
                            {{ $historialHabitacion->situacion_pago }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion Checkout:</strong>
                            {{ $historialHabitacion->situacion_checkout }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Movhabitacion Id:</strong>
                            {{ $historialHabitacion->movhabitacion_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Relturnocaja Id:</strong>
                            {{ $historialHabitacion->relturnocaja_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $historialHabitacion->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
