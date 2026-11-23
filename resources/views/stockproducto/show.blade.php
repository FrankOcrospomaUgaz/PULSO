@extends('layouts.app')

@section('template_title')
    {{ $stockproducto->name ?? __('Show') . " " . __('Stockproducto') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Stockproducto</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('stockproductos.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Stockbase:</strong>
                            {{ $stockproducto->stockbase }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Producto Id:</strong>
                            {{ $stockproducto->producto_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Unidadbase Id:</strong>
                            {{ $stockproducto->unidadbase_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $stockproducto->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
