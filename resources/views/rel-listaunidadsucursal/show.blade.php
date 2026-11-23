@extends('layouts.app')

@section('template_title')
    {{ $relListaunidadsucursal->name ?? __('Show') . " " . __('Rel Listaunidadsucursal') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Rel Listaunidadsucursal</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('rel-listaunidadsucursals.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Preciocompra:</strong>
                            {{ $relListaunidadsucursal->preciocompra }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Preciomanoobra:</strong>
                            {{ $relListaunidadsucursal->preciomanoobra }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Precioventa:</strong>
                            {{ $relListaunidadsucursal->precioventa }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Moneda:</strong>
                            {{ $relListaunidadsucursal->moneda }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Listaunidad Id:</strong>
                            {{ $relListaunidadsucursal->listaunidad_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $relListaunidadsucursal->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
