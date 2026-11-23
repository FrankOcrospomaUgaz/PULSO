@extends('layouts.app')

@section('template_title')
    {{ $movimiento->name ?? __('Show') . " " . __('Movimiento') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Movimiento</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('movimientos.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Numero:</strong>
                            {{ $movimiento->numero }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fecha:</strong>
                            {{ $movimiento->fecha }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Usuario Id:</strong>
                            {{ $movimiento->usuario_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Usuario:</strong>
                            {{ $movimiento->usuario }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Persona Id:</strong>
                            {{ $movimiento->persona_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Persona:</strong>
                            {{ $movimiento->persona }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Responsable Id:</strong>
                            {{ $movimiento->responsable_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Responsable:</strong>
                            {{ $movimiento->responsable }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Comentario:</strong>
                            {{ $movimiento->comentario }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $movimiento->situacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipomovimiento Id:</strong>
                            {{ $movimiento->tipomovimiento_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipodocumento Id:</strong>
                            {{ $movimiento->tipodocumento_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $movimiento->sucursal_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Movimiento Id:</strong>
                            {{ $movimiento->movimiento_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
