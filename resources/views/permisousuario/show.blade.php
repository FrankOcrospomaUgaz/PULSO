@extends('layouts.app')

@section('template_title')
    {{ $permisousuario->name ?? __('Show') . " " . __('Permisousuario') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Permisousuario</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('permisousuarios.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Situacion:</strong>
                            {{ $permisousuario->situacion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Menuopcion Id:</strong>
                            {{ $permisousuario->menuopcion_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Perfil Id:</strong>
                            {{ $permisousuario->perfil_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $permisousuario->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
