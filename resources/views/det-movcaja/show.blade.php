@extends('layouts.app')

@section('template_title')
    {{ $detMovcaja->name ?? __('Show') . " " . __('Det Movcaja') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Det Movcaja</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('det-movcajas.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Movcaja Id:</strong>
                            {{ $detMovcaja->movcaja_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipo:</strong>
                            {{ $detMovcaja->tipo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechavencimiento:</strong>
                            {{ $detMovcaja->fechavencimiento }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechapago:</strong>
                            {{ $detMovcaja->fechapago }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Mediopago Id:</strong>
                            {{ $detMovcaja->mediopago_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Mediopago:</strong>
                            {{ $detMovcaja->mediopago }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Numero:</strong>
                            {{ $detMovcaja->numero }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tarjeta Id:</strong>
                            {{ $detMovcaja->tarjeta_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tarjeta:</strong>
                            {{ $detMovcaja->tarjeta }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Banco Id:</strong>
                            {{ $detMovcaja->banco_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Banco:</strong>
                            {{ $detMovcaja->banco }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Billeteradigital Id:</strong>
                            {{ $detMovcaja->billeteradigital_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Billeteradigital:</strong>
                            {{ $detMovcaja->billeteradigital }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Pos Id:</strong>
                            {{ $detMovcaja->pos_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Pos:</strong>
                            {{ $detMovcaja->pos }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Monto:</strong>
                            {{ $detMovcaja->monto }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Comentario:</strong>
                            {{ $detMovcaja->comentario }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $detMovcaja->situacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $detMovcaja->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
