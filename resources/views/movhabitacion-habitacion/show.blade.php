@extends('layouts.app')

@section('template_title')
    {{ $movhabitacionHabitacion->name ?? __('Show') . " " . __('Movhabitacion Habitacion') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Movhabitacion Habitacion</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('movhabitacion-habitacions.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Movhabitacion Id:</strong>
                            {{ $movhabitacionHabitacion->movhabitacion_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Habitacion Id:</strong>
                            {{ $movhabitacionHabitacion->habitacion_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Principal:</strong>
                            {{ $movhabitacionHabitacion->principal }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tarifa:</strong>
                            {{ $movhabitacionHabitacion->tarifa }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Habitacion:</strong>
                            {{ $movhabitacionHabitacion->json_habitacion }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
