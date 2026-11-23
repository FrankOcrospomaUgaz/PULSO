@extends('layouts.app')

@section('template_title')
    {{ $housekeepingDetalle->name ?? __('Show') . " " . __('Housekeeping Detalle') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Housekeeping Detalle</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('housekeeping-detalles.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Jornada Id:</strong>
                            {{ $housekeepingDetalle->jornada_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Habitacion Id:</strong>
                            {{ $housekeepingDetalle->habitacion_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Inicio Limpieza:</strong>
                            {{ $housekeepingDetalle->inicio_limpieza }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fin Limpieza:</strong>
                            {{ $housekeepingDetalle->fin_limpieza }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tiempo:</strong>
                            {{ $housekeepingDetalle->tiempo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Estado:</strong>
                            {{ $housekeepingDetalle->estado }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Observaciones:</strong>
                            {{ $housekeepingDetalle->observaciones }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Empleado Id:</strong>
                            {{ $housekeepingDetalle->empleado_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
