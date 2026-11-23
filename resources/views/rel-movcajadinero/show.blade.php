@extends('layouts.app')

@section('template_title')
    {{ $relMovcajadinero->name ?? __('Show') . " " . __('Rel Movcajadinero') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Rel Movcajadinero</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('rel-movcajadineros.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Cantidad:</strong>
                            {{ $relMovcajadinero->cantidad }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Comentario:</strong>
                            {{ $relMovcajadinero->comentario }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Dinero Id:</strong>
                            {{ $relMovcajadinero->dinero_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Movcaja Id:</strong>
                            {{ $relMovcajadinero->movcaja_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $relMovcajadinero->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
