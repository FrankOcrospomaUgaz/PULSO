@extends('layouts.app')

@section('template_title')
    {{ $rolpersona->name ?? __('Show') . " " . __('Rolpersona') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Rolpersona</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('rolpersonas.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Persona Id:</strong>
                            {{ $rolpersona->persona_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Rol Id:</strong>
                            {{ $rolpersona->rol_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $rolpersona->sucursal_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
