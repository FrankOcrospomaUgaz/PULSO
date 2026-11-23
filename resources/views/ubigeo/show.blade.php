@extends('layouts.app')

@section('template_title')
    {{ $ubigeo->name ?? __('Show') . " " . __('Ubigeo') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Ubigeo</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('ubigeos.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Descripcion:</strong>
                            {{ $ubigeo->descripcion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Codigo:</strong>
                            {{ $ubigeo->codigo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Inei:</strong>
                            {{ $ubigeo->inei }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipo:</strong>
                            {{ $ubigeo->tipo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Ubigeo Id:</strong>
                            {{ $ubigeo->ubigeo_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
