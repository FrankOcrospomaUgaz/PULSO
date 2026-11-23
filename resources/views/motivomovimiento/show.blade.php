@extends('layouts.app')

@section('template_title')
    {{ $motivomovimiento->name ?? __('Show') . " " . __('Motivomovimiento') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Motivomovimiento</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('motivomovimientos.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Codigo:</strong>
                            {{ $motivomovimiento->codigo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipomovimiento Id:</strong>
                            {{ $motivomovimiento->tipomovimiento_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Orden:</strong>
                            {{ $motivomovimiento->orden }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $motivomovimiento->situacion }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
