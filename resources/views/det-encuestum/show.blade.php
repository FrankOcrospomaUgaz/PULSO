@extends('layouts.app')

@section('template_title')
    {{ $detEncuestum->name ?? __('Show') . " " . __('Det Encuestum') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Det Encuestum</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('det-encuesta.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Enunciado:</strong>
                            {{ $detEncuestum->enunciado }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Orden:</strong>
                            {{ $detEncuestum->orden }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Obligatorio:</strong>
                            {{ $detEncuestum->obligatorio }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Opciones:</strong>
                            {{ $detEncuestum->opciones }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $detEncuestum->situacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Categoriapregunta Id:</strong>
                            {{ $detEncuestum->categoriapregunta_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Categoriapregunta:</strong>
                            {{ $detEncuestum->categoriapregunta }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipopregunta Id:</strong>
                            {{ $detEncuestum->tipopregunta_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Encuesta Id:</strong>
                            {{ $detEncuestum->encuesta_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $detEncuestum->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
