@extends('layouts.app')

@section('template_title')
    {{ $detMovventum->name ?? __('Show') . " " . __('Det Movventum') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Det Movventum</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('det-movventa.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Tipo Detalle:</strong>
                            {{ $detMovventum->tipo_detalle }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Movventa Id:</strong>
                            {{ $detMovventum->movventa_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Codigo:</strong>
                            {{ $detMovventum->codigo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Descripcion:</strong>
                            {{ $detMovventum->descripcion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Producto Id:</strong>
                            {{ $detMovventum->producto_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Producto:</strong>
                            {{ $detMovventum->json_producto }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Medidaproducto:</strong>
                            {{ $detMovventum->json_medidaproducto }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Unidad Id:</strong>
                            {{ $detMovventum->unidad_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Unidad:</strong>
                            {{ $detMovventum->json_unidad }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Igv Id:</strong>
                            {{ $detMovventum->igv_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Igv:</strong>
                            {{ $detMovventum->json_igv }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Cantidad:</strong>
                            {{ $detMovventum->cantidad }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Monto:</strong>
                            {{ $detMovventum->monto }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Dcto Porcentaje:</strong>
                            {{ $detMovventum->dcto_porcentaje }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Monto Original:</strong>
                            {{ $detMovventum->monto_original }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Comentario:</strong>
                            {{ $detMovventum->comentario }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Det Categoria:</strong>
                            {{ $detMovventum->det_categoria }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Opc Categoria:</strong>
                            {{ $detMovventum->opc_categoria }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Det Movventa Id:</strong>
                            {{ $detMovventum->det_movventa_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Complementos:</strong>
                            {{ $detMovventum->complementos }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $detMovventum->situacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $detMovventum->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
