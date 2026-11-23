@extends('layouts.app')

@section('template_title')
    {{ $personamaestro->name ?? __('Show') . " " . __('Personamaestro') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Personamaestro</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('personamaestros.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Tipopersona:</strong>
                            {{ $personamaestro->tipopersona }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Nombres:</strong>
                            {{ $personamaestro->nombres }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Apellidos:</strong>
                            {{ $personamaestro->apellidos }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Nrodoc:</strong>
                            {{ $personamaestro->nrodoc }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Sexo:</strong>
                            {{ $personamaestro->sexo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fechanac:</strong>
                            {{ $personamaestro->fechanac }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
