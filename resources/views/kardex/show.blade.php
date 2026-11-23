@extends('layouts.app')

@section('template_title')
    {{ $kardex->name ?? __('Show') . " " . __('Kardex') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Kardex</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('kardexes.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Detalle Id:</strong>
                            {{ $kardex->detalle_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Producto Id:</strong>
                            {{ $kardex->producto_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Producto:</strong>
                            {{ $kardex->json_producto }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Unidad Id:</strong>
                            {{ $kardex->unidad_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Json Listaunidad:</strong>
                            {{ $kardex->json_listaunidad }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Cantidad:</strong>
                            {{ $kardex->cantidad }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Unidadbase Id:</strong>
                            {{ $kardex->unidadbase_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Cantidadbase:</strong>
                            {{ $kardex->cantidadbase }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Preciounitario:</strong>
                            {{ $kardex->preciounitario }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Moneda:</strong>
                            {{ $kardex->moneda }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipocambio:</strong>
                            {{ $kardex->tipocambio }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Total:</strong>
                            {{ $kardex->total }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fecha:</strong>
                            {{ $kardex->fecha }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $kardex->situacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Usuario Id:</strong>
                            {{ $kardex->usuario_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Usuario:</strong>
                            {{ $kardex->usuario }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Movimiento Id:</strong>
                            {{ $kardex->movimiento_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipomovimiento Id:</strong>
                            {{ $kardex->tipomovimiento_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipodocumento Id:</strong>
                            {{ $kardex->tipodocumento_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $kardex->sucursal_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Stockanterior:</strong>
                            {{ $kardex->stockanterior }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Stockactual:</strong>
                            {{ $kardex->stockactual }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
