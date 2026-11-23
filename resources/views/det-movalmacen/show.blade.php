@extends('layouts.app')

@section('template_title')
    {{ $detMovalmacen->name ?? __('Show') . " " . __('Det Movalmacen') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Det Movalmacen</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('det-movalmacens.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Movalmacen Id:</strong>
                            {{ $detMovalmacen->movalmacen_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Producto Id:</strong>
                            {{ $detMovalmacen->producto_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Producto:</strong>
                            {{ $detMovalmacen->json_producto }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Unidad Id:</strong>
                            {{ $detMovalmacen->unidad_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Listaunidad:</strong>
                            {{ $detMovalmacen->json_listaunidad }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Cantidad:</strong>
                            {{ $detMovalmacen->cantidad }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Comentario:</strong>
                            {{ $detMovalmacen->comentario }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $detMovalmacen->situacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $detMovalmacen->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
