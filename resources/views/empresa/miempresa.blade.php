@extends('layouts.app')

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">
            <div class="col s12">
                <div class="card main-content">
                    <span class="card-title">Mis datos</span>
                    <div class="row">
                        <div class="col s12 m12 l12 left-align">
                            <p>Para actualizar sus datos principales, comuníquese con Garzasoft pulsando <a
                                    href="https://api.whatsapp.com/send?phone=+51%20979293176&amp;text=¡Hola!%20Quisiera%20actualizar%20mi%20información%20en%20el%20sistema%20360Sys."
                                    target="_blank"><strong>aquí</strong></a>
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Campo de texto para 'ruc' -->
                        <div class="input-field col s12 m4">
                            <input type="text" name="ruc" id="ruc"
                                class="validate @error('ruc') invalid @enderror" value="{{ old('ruc', $empresa?->ruc) }}" disabled>
                            <label for="ruc" class="{{ $empresa?->ruc ? 'active' : '' }}">{{ __('RUC') }}</label>

                            {!! $errors->first('ruc', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
                        </div>
                        <!-- Campo de texto para 'razon_social' -->
                        <div class="input-field col s12 m4">
                            <input type="text" name="razon_social" id="razonsocial"
                                class="validate @error('razon_social') invalid @enderror"
                                value="{{ old('razon_social', $empresa?->razon_social) }}" disabled>
                            <label for="razon_social"
                                class="{{ $empresa?->razon_social ? 'active' : '' }}">{{ __('Razón Social') }}</label>
                            {!! $errors->first(
                                'razon_social',
                                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                            ) !!}
                        </div>
                        <!-- Campo de texto para 'direccion' -->
                        <div class="input-field col s12 m4">
                            <input type="text" name="direccion" id="direccion"
                                class="validate @error('direccion') invalid @enderror"
                                value="{{ old('direccion', $empresa?->direccion) }}">
                            <label for="direccion"
                                class="{{ $empresa?->direccion ? 'active' : '' }}">{{ __('Dirección') }}</label>
                            {!! $errors->first(
                                'direccion',
                                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                            ) !!}
                        </div>

                        <!-- Campo de texto para 'email' -->
                        <div class="input-field col s12 m4">
                            <input type="email" name="email" id="email"
                                class="validate @error('email') invalid @enderror"
                                value="{{ old('email', $empresa?->email) }}">
                            <label for="email"
                                class="{{ $empresa?->email ? 'active' : '' }}">{{ __('Email') }}</label>
                            {!! $errors->first('email', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
                        </div>
                        <!-- Campo de texto para 'telefono_fijo' -->
                        <div class="input-field col s12 m4">
                            <input type="text" name="telefono_fijo" id="telefono_fijo"
                                class="validate @error('telefono_fijo') invalid @enderror"
                                value="{{ old('telefono_fijo', $empresa?->telefono_fijo) }}">
                            <label for="telefono_fijo"
                                class="{{ $empresa?->telefono_fijo ? 'active' : '' }}">{{ __('Teléfono Fijo') }}</label>
                            {!! $errors->first(
                                'telefono_fijo',
                                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                            ) !!}
                        </div>

                        <!-- Campo de texto para 'telefono_movil' -->
                        <div class="input-field col s12 m4">
                            <input type="text" name="telefono_movil" id="telefono_movil"
                                class="validate @error('telefono_movil') invalid @enderror"
                                value="{{ old('telefono_movil', $empresa?->telefono_movil) }}">
                            <label for="telefono_movil"
                                class="{{ $empresa?->telefono_movil ? 'active' : '' }}">{{ __('Teléfono Móvil') }}</label>
                            {!! $errors->first(
                                'telefono_movil',
                                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                            ) !!}
                        </div>

                    </div>
                    <div class="card-action right-align" style="border-top:0px;">
                        <div id="opcionesFooter">
                            <button class="btn mt20 ml10 waves-effect waves-light yellow grey-text text-darken-3 btn-mantenimiento" id="cmdEdit" type="button" onclick="editarSucursal()">ACTUALIZAR DATOS<i class="material-icons right">edit</i></button>
                            <a href="{{ route('verMisSucursales', $empresa->id) }}" class="btn mt20 ml10 waves-effect waves-light blue btn-mantenimiento btn-cargar-crud" data-tooltip="Ver sucursales" data-icono="fa-solid fa-landmark left" data-titulo="HOTEL DEMO - Sucursales">
                            SUCURSALES<i class="material-icons right">account_balance</i></a>
                                                        </a>
                        </div>
                        <div id="mantFooter" style="display:none;">
                            <button class="btn-flat waves-effect waves-dark btn-cancelar btn-mantenimiento grey-text text-darken-3" id="btn-cancel" type="button" onclick="cancelarEditar();">CANCELAR<i class="material-icons right">clear</i></button>
                            <button class="btn waves-effect waves-light teal btn-mantenimiento" id="btn-save" type="button" style="margin-left: 10px;">GUARDAR<i class="material-icons right">save</i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
