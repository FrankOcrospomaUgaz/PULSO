@extends('layouts.app')

@section('template_title')
    {{ $relTurnocaja->name ?? __('Show') . " " . __('Rel Turnocaja') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Rel Turnocaja</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('rel-turnocajas.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Fechainicio:</strong>
                            {{ $relTurnocaja->fechainicio }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechafin:</strong>
                            {{ $relTurnocaja->fechafin }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $relTurnocaja->situacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Movcajainicio Id:</strong>
                            {{ $relTurnocaja->movcajainicio_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Movcajafin Id:</strong>
                            {{ $relTurnocaja->movcajafin_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $relTurnocaja->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
