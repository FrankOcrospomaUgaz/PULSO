@extends('layouts.app')

@section('template_title')
    {{ $movhabitacionMovimiento->name ?? __('Show') . " " . __('Movhabitacion Movimiento') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Movhabitacion Movimiento</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('movhabitacion-movimientos.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Movhabitacion Id:</strong>
                            {{ $movhabitacionMovimiento->movhabitacion_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Movimiento Id:</strong>
                            {{ $movhabitacionMovimiento->movimiento_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Dias Pagados:</strong>
                            {{ $movhabitacionMovimiento->dias_pagados }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Horaspagadas:</strong>
                            {{ $movhabitacionMovimiento->horaspagadas }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
