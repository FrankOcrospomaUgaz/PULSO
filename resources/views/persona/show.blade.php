@extends('layouts.app')

@section('template_title')
    {{ $persona->name ?? __('Show') . " " . __('Persona') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Persona</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('personas.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Nombres:</strong>
                            {{ $persona->nombres }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Apellidos:</strong>
                            {{ $persona->apellidos }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Direccion:</strong>
                            {{ $persona->direccion }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Email:</strong>
                            {{ $persona->email }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Telefonofijo:</strong>
                            {{ $persona->telefonofijo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Telefonomovil:</strong>
                            {{ $persona->telefonomovil }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Imagen:</strong>
                            {{ $persona->imagen }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Compartido:</strong>
                            {{ $persona->compartido }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Personamaestro Id:</strong>
                            {{ $persona->personamaestro_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sucursal Id:</strong>
                            {{ $persona->sucursal_id }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Distrito Id:</strong>
                            {{ $persona->distrito_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
