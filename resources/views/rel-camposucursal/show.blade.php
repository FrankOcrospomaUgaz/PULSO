@extends('layouts.app')

@section('template_title')
    {{ $relCamposucursal->name ?? __('Show') . " " . __('Rel Camposucursal') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Rel Camposucursal</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('rel-camposucursals.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Descripcion:</strong>
                            {{ $relCamposucursal->descripcion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipo:</strong>
                            {{ $relCamposucursal->tipo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Orden:</strong>
                            {{ $relCamposucursal->orden }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Diccionario:</strong>
                            {{ $relCamposucursal->diccionario }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $relCamposucursal->situacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Campo Id:</strong>
                            {{ $relCamposucursal->campo_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tabla Id:</strong>
                            {{ $relCamposucursal->tabla_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $relCamposucursal->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
