@extends('layouts.app')

@section('template_title')
    {{ $solicitud->name ?? __('Show') . " " . __('Solicitud') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Solicitud</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('solicituds.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Tipo Solicitud:</strong>
                            {{ $solicitud->tipo_solicitud }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Data:</strong>
                            {{ $solicitud->data }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fecha:</strong>
                            {{ $solicitud->fecha }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechaenvio:</strong>
                            {{ $solicitud->fechaenvio }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fecharespuesta:</strong>
                            {{ $solicitud->fecharespuesta }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechaanulacion:</strong>
                            {{ $solicitud->fechaanulacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Estado:</strong>
                            {{ $solicitud->estado }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Nombre:</strong>
                            {{ $solicitud->nombre }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Total:</strong>
                            {{ $solicitud->total }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Moneda:</strong>
                            {{ $solicitud->moneda }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipo Documento:</strong>
                            {{ $solicitud->tipo_documento }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Serie:</strong>
                            {{ $solicitud->serie }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Correlativo:</strong>
                            {{ $solicitud->correlativo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Ticket Solicitud:</strong>
                            {{ $solicitud->ticket_solicitud }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Persona Id:</strong>
                            {{ $solicitud->persona_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Persona:</strong>
                            {{ $solicitud->json_persona }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $solicitud->sucursal_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Sucursal:</strong>
                            {{ $solicitud->json_sucursal }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Facturador Id:</strong>
                            {{ $solicitud->facturador_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Facturador:</strong>
                            {{ $solicitud->json_facturador }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Movimiento Id:</strong>
                            {{ $solicitud->movimiento_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Solicitud Facturador Id:</strong>
                            {{ $solicitud->solicitud_facturador_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Error Code:</strong>
                            {{ $solicitud->error_code }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Error Text:</strong>
                            {{ $solicitud->error_text }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Public Name:</strong>
                            {{ $solicitud->public_name }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
