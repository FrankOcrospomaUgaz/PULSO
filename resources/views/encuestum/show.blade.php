@extends('layouts.app')

@section('template_title')
    {{ $encuestum->name ?? __('Show') . " " . __('Encuestum') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Encuestum</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('encuesta.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Uuid:</strong>
                            {{ $encuestum->uuid }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Nombre:</strong>
                            {{ $encuestum->nombre }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Descripcion:</strong>
                            {{ $encuestum->descripcion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fecha:</strong>
                            {{ $encuestum->fecha }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechavencimiento:</strong>
                            {{ $encuestum->fechavencimiento }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Mensaje Finalizacion:</strong>
                            {{ $encuestum->mensaje_finalizacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Imprimible:</strong>
                            {{ $encuestum->imprimible }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Estado:</strong>
                            {{ $encuestum->estado }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $encuestum->situacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $encuestum->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
