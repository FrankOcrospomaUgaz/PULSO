@extends('layouts.app')

@section('template_title')
    {{ $tarjetum->name ?? __('Show') . " " . __('Tarjetum') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Tarjetum</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('tarjeta.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Descripcion:</strong>
                            {{ $tarjetum->descripcion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipo:</strong>
                            {{ $tarjetum->tipo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Orden:</strong>
                            {{ $tarjetum->orden }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $tarjetum->situacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Icono:</strong>
                            {{ $tarjetum->icono }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
