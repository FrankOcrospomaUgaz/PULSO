@extends('layouts.app')

@section('template_title')
    {{ $relParametrosucursal->name ?? __('Show') . " " . __('Rel Parametrosucursal') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Rel Parametrosucursal</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('rel_parametrosucursal.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Valor:</strong>
                            {{ $relParametrosucursal->valor }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Parametro Id:</strong>
                            {{ $relParametrosucursal->parametro_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $relParametrosucursal->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
