@extends('layouts.app')

@section('template_title')
    {{ $ubicacion->name ?? __('Show') . " " . __('Ubicacion') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Ubicacion</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('ubicacions.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Codigo:</strong>
                            {{ $ubicacion->codigo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Descripcion:</strong>
                            {{ $ubicacion->descripcion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Totalfilas:</strong>
                            {{ $ubicacion->totalfilas }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Totalcolumnas:</strong>
                            {{ $ubicacion->totalcolumnas }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
