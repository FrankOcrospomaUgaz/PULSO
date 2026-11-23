@extends('layouts.app')

@section('template_title')
    {{ $detRespuestum->name ?? __('Show') . " " . __('Det Respuestum') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Det Respuestum</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('det-respuesta.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Det Encuesta Id:</strong>
                            {{ $detRespuestum->det_encuesta_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Respuesta Id:</strong>
                            {{ $detRespuestum->respuesta_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $detRespuestum->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
