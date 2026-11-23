@extends('layouts.app')

@section('template_title')
    {{ $detalleHabitacion->name ?? __('Show') . " " . __('Detalle Habitacion') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Detalle Habitacion</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('detalle-habitacions.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Habitacion Id:</strong>
                            {{ $detalleHabitacion->habitacion_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Descripcion:</strong>
                            {{ $detalleHabitacion->descripcion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Icono:</strong>
                            {{ $detalleHabitacion->icono }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $detalleHabitacion->situacion }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
