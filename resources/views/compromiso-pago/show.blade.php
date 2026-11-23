@extends('layouts.app')

@section('template_title')
    {{ $compromisoPago->name ?? __('Show') . " " . __('Compromiso Pago') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Compromiso Pago</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('compromiso_pago.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Contrato Id:</strong>
                            {{ $compromisoPago->contrato_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Personamaestro Id:</strong>
                            {{ $compromisoPago->personamaestro_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Ambiente Id:</strong>
                            {{ $compromisoPago->ambiente_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fecha Vencimiento:</strong>
                            {{ $compromisoPago->fecha_vencimiento }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fecha Pago:</strong>
                            {{ $compromisoPago->fecha_pago }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Numero Cuota:</strong>
                            {{ $compromisoPago->numero_cuota }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Monto:</strong>
                            {{ $compromisoPago->monto }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Forma Pago:</strong>
                            {{ $compromisoPago->forma_pago }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Estado:</strong>
                            {{ $compromisoPago->estado }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
