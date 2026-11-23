@extends('layouts.app')

@section('template_title')
    {{ $respuestum->name ?? __('Show') . " " . __('Respuestum') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Respuestum</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('respuesta.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Codigo:</strong>
                            {{ $respuestum->codigo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fecha:</strong>
                            {{ $respuestum->fecha }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Persona Id:</strong>
                            {{ $respuestum->persona_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Persona:</strong>
                            {{ $respuestum->persona }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Encuesta Id:</strong>
                            {{ $respuestum->encuesta_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Movimiento Id:</strong>
                            {{ $respuestum->movimiento_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Movimiento:</strong>
                            {{ $respuestum->movimiento }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $respuestum->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
