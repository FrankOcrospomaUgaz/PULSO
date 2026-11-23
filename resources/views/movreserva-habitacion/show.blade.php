@extends('layouts.app')

@section('template_title')
    {{ $movreservaHabitacion->name ?? __('Show') . " " . __('Movreserva Habitacion') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Movreserva Habitacion</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('movreserva-habitacions.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Movreserva Id:</strong>
                            {{ $movreservaHabitacion->movreserva_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Habitacion Id:</strong>
                            {{ $movreservaHabitacion->habitacion_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Principal:</strong>
                            {{ $movreservaHabitacion->principal }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tarifa:</strong>
                            {{ $movreservaHabitacion->tarifa }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Habitacion:</strong>
                            {{ $movreservaHabitacion->json_habitacion }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
