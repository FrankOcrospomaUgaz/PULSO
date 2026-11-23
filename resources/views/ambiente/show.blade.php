@extends('layouts.app')

@section('template_title')
    {{ $ambiente->name ?? __('Show') . " " . __('Ambiente') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Ambiente</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('ambientes.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Numero:</strong>
                            {{ $ambiente->numero }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tipo:</strong>
                            {{ $ambiente->tipo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Monto:</strong>
                            {{ $ambiente->monto }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Garantia:</strong>
                            {{ $ambiente->garantia }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Piso Id:</strong>
                            {{ $ambiente->piso_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Edificio Id:</strong>
                            {{ $ambiente->edificio_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
