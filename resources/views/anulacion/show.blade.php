@extends('layouts.app')

@section('template_title')
    {{ $anulacion->name ?? __('Show') . " " . __('Anulacion') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Anulacion</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('anulacions.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Movimiento Id:</strong>
                            {{ $anulacion->movimiento_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Usuario Id:</strong>
                            {{ $anulacion->usuario_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fecha:</strong>
                            {{ $anulacion->fecha }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Motivo:</strong>
                            {{ $anulacion->motivo }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
