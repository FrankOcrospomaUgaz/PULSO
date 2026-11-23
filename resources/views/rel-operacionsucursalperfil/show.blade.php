@extends('layouts.app')

@section('template_title')
    {{ $relOperacionsucursalperfil->name ?? __('Show') . " " . __('Rel Operacionsucursalperfil') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Rel Operacionsucursalperfil</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('rel-operacionsucursalperfils.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $relOperacionsucursalperfil->situacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Operacion Id:</strong>
                            {{ $relOperacionsucursalperfil->operacion_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Tabla Id:</strong>
                            {{ $relOperacionsucursalperfil->tabla_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Perfil Id:</strong>
                            {{ $relOperacionsucursalperfil->perfil_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $relOperacionsucursalperfil->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
