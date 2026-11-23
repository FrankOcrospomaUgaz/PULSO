@extends('layouts.app')

@section('template_title')
    {{ $relProductosucursal->name ?? __('Show') . " " . __('Rel Productosucursal') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Rel Productosucursal</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('rel-productosucursals.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $relProductosucursal->situacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechavencimiento:</strong>
                            {{ $relProductosucursal->fechavencimiento }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Stockminimo:</strong>
                            {{ $relProductosucursal->stockminimo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Stockmaximo:</strong>
                            {{ $relProductosucursal->stockmaximo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Stockoptimo:</strong>
                            {{ $relProductosucursal->stockoptimo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Minimovender:</strong>
                            {{ $relProductosucursal->minimovender }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Minimocomprar:</strong>
                            {{ $relProductosucursal->minimocomprar }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Ubicacion Id:</strong>
                            {{ $relProductosucursal->ubicacion_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fila:</strong>
                            {{ $relProductosucursal->fila }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Columna:</strong>
                            {{ $relProductosucursal->columna }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Favorito:</strong>
                            {{ $relProductosucursal->favorito }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Subalmacen Id:</strong>
                            {{ $relProductosucursal->subalmacen_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Igv Id:</strong>
                            {{ $relProductosucursal->igv_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Venta Unitaria:</strong>
                            {{ $relProductosucursal->venta_unitaria }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Producto Id:</strong>
                            {{ $relProductosucursal->producto_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $relProductosucursal->sucursal_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Duracion:</strong>
                            {{ $relProductosucursal->duracion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Proveedor Id:</strong>
                            {{ $relProductosucursal->proveedor_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
