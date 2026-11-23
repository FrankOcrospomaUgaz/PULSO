@extends('layouts.app')

@section('template_title')
    {{ $salon->name ?? __('Show') . " " . __('Salon') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Salon</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('salons.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Titulo:</strong>
                            {{ $salon->titulo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Descripcion Corta:</strong>
                            {{ $salon->descripcion_corta }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Descripcion Larga:</strong>
                            {{ $salon->descripcion_larga }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Imagen:</strong>
                            {{ $salon->imagen }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $salon->situacion }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
