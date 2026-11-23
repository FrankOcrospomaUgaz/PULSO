@extends('layouts.app')

@section('template_title')
    {{ $detMovhabitacion->name ?? __('Show') . " " . __('Det Movhabitacion') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Det Movhabitacion</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('det-movhabitacions.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Tipo Detalle:</strong>
                            {{ $detMovhabitacion->tipo_detalle }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Movhabitacion Id:</strong>
                            {{ $detMovhabitacion->movhabitacion_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Codigo:</strong>
                            {{ $detMovhabitacion->codigo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Descripcion:</strong>
                            {{ $detMovhabitacion->descripcion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Producto Id:</strong>
                            {{ $detMovhabitacion->producto_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Producto:</strong>
                            {{ $detMovhabitacion->json_producto }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Medidaproducto:</strong>
                            {{ $detMovhabitacion->json_medidaproducto }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Unidad Id:</strong>
                            {{ $detMovhabitacion->unidad_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Unidad:</strong>
                            {{ $detMovhabitacion->json_unidad }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Igv Id:</strong>
                            {{ $detMovhabitacion->igv_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Igv:</strong>
                            {{ $detMovhabitacion->json_igv }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Cantidad:</strong>
                            {{ $detMovhabitacion->cantidad }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Monto:</strong>
                            {{ $detMovhabitacion->monto }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Dcto Porcentaje:</strong>
                            {{ $detMovhabitacion->dcto_porcentaje }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Monto Original:</strong>
                            {{ $detMovhabitacion->monto_original }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Comentario:</strong>
                            {{ $detMovhabitacion->comentario }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Det Categoria:</strong>
                            {{ $detMovhabitacion->det_categoria }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Opc Categoria:</strong>
                            {{ $detMovhabitacion->opc_categoria }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Det Movventa Id:</strong>
                            {{ $detMovhabitacion->det_movventa_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Complementos:</strong>
                            {{ $detMovhabitacion->complementos }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $detMovhabitacion->situacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $detMovhabitacion->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
