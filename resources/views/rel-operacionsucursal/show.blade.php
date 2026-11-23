@extends('layouts.app')

@section('template_title')
    {{ $relOperacionsucursal->name ?? __('Show') . " " . __('Rel Operacionsucursal') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Rel Operacionsucursal</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('rel-operacionsucursals.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Orden:</strong>
                            {{ $relOperacionsucursal->orden }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $relOperacionsucursal->situacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Operacion Id:</strong>
                            {{ $relOperacionsucursal->operacion_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tabla Id:</strong>
                            {{ $relOperacionsucursal->tabla_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $relOperacionsucursal->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
