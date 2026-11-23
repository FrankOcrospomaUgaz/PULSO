@extends('layouts.app')

@section('template_title')
    {{ $conceptopago->name ?? __('Show') . " " . __('Conceptopago') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Conceptopago</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('conceptopagos.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Descripcion:</strong>
                            {{ $conceptopago->descripcion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipo:</strong>
                            {{ $conceptopago->tipo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Restringido:</strong>
                            {{ $conceptopago->restringido }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Comentarios:</strong>
                            {{ $conceptopago->comentarios }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
