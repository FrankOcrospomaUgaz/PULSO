@extends('layouts.app')

@section('template_title')
    {{ $igv->name ?? __('Show') . " " . __('Igv') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Igv</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('igvs.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Codigo:</strong>
                            {{ $igv->codigo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Descripcion:</strong>
                            {{ $igv->descripcion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Porcentaje Igv:</strong>
                            {{ $igv->porcentaje_igv }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Orden:</strong>
                            {{ $igv->orden }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $igv->situacion }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
