@extends('layouts.app')

@section('template_title')
    {{ $movcaja->name ?? __('Show') . " " . __('Movcaja') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Movcaja</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('movcajas.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Conceptopago Id:</strong>
                            {{ $movcaja->conceptopago_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Moneda:</strong>
                            {{ $movcaja->moneda }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipocambio:</strong>
                            {{ $movcaja->tipocambio }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Total:</strong>
                            {{ $movcaja->total }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Caja Id:</strong>
                            {{ $movcaja->caja_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Caja:</strong>
                            {{ $movcaja->caja }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Turno Id:</strong>
                            {{ $movcaja->turno_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Turno:</strong>
                            {{ $movcaja->json_turno }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Movimiento Id:</strong>
                            {{ $movcaja->movimiento_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $movcaja->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
