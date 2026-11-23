@extends('layouts.app')

@section('template_title')
    {{ $movventum->name ?? __('Show') . " " . __('Movventum') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Movventum</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('movventa.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Json Sucursal:</strong>
                            {{ $movventum->json_sucursal }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Serie:</strong>
                            {{ $movventum->serie }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Anio:</strong>
                            {{ $movventum->anio }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipo Detalle:</strong>
                            {{ $movventum->tipo_detalle }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Consumo:</strong>
                            {{ $movventum->consumo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipo Pago:</strong>
                            {{ $movventum->tipo_pago }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Estado:</strong>
                            {{ $movventum->estado }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipo Venta:</strong>
                            {{ $movventum->tipo_venta }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Moneda:</strong>
                            {{ $movventum->moneda }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipocambio:</strong>
                            {{ $movventum->tipocambio }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Subtotal:</strong>
                            {{ $movventum->subtotal }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Igv:</strong>
                            {{ $movventum->igv }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Total:</strong>
                            {{ $movventum->total }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Movimiento Id:</strong>
                            {{ $movventum->movimiento_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $movventum->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
