@extends('layouts.app')

@section('template_title')
    {{ $contrato->name ?? __('Show') . " " . __('Contrato') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Contrato</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('contratos.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Numero Contrato:</strong>
                            {{ $contrato->numero_contrato }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fecha Inicio:</strong>
                            {{ $contrato->fecha_inicio }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fecha Fin:</strong>
                            {{ $contrato->fecha_fin }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Dia Pago:</strong>
                            {{ $contrato->dia_pago }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Monto:</strong>
                            {{ $contrato->monto }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Garantia:</strong>
                            {{ $contrato->garantia }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Cubre Luz:</strong>
                            {{ $contrato->cubre_luz }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Cubre Agua:</strong>
                            {{ $contrato->cubre_agua }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Descripcion:</strong>
                            {{ $contrato->descripcion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Informacion Adicional:</strong>
                            {{ $contrato->informacion_adicional }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Ambiente Id:</strong>
                            {{ $contrato->ambiente_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Personamaestro Id:</strong>
                            {{ $contrato->personamaestro_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Edificio Id:</strong>
                            {{ $contrato->edificio_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
