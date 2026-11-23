@extends('layouts.app')

@section('template_title')
    {{ $plantillaContrato->name ?? __('Show') . " " . __('Plantilla Contrato') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Plantilla Contrato</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('plantilla-contratos.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Titulo:</strong>
                            {{ $plantillaContrato->titulo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Contenido Html:</strong>
                            {{ $plantillaContrato->contenido_html }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Descripcion:</strong>
                            {{ $plantillaContrato->descripcion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $plantillaContrato->sucursal_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipo Ambiente Id:</strong>
                            {{ $plantillaContrato->tipo_ambiente_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
