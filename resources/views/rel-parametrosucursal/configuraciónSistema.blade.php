@extends('layouts.app')

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top: 0px;">


            <div class="col s12" id="div-Mant{{ $clase }}">
                <div class="card main-content" id="card-Mant{{ $clase }}">
                    <div class="card-content without-padding">
                        <div class="row">
                            <div class="col s12">
                                <ul class="tabs tabs-fixed-width" id="tabs-{{ $clase }}">
                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($parametros as $categoriaparametro => $par_por_cat)
                                        <li class="tab col s3">
                                            <a class="{{ $i == 0 ? 'active' : '' }} "
                                                href="#div-parametro_{{ $i }}">{{ $categoriaparametro }}</a>
                                        </li>
                                        @php
                                            $i++;
                                        @endphp
                                    @endforeach
                                </ul>
                            </div>
                            @php
                                $i = 0;
                            @endphp
                         <form id="form" enctype="multipart/form-data" data-clase="{{ route('rel_parametrosucursal.updateParametros') }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="form-action-url" value="{{ route('rel_parametrosucursal.updateParametros') }}">

                                @foreach ($parametros as $categoriaparametro => $par_por_cat)
                                    <div class="col s12 div-salon div-tab-container" id="div-parametro_{{ $i }}">
                                        <div class="row without-tb"
                                            style="padding-left:calc(24px + 0.75rem);padding-right:calc(24px + 0.75rem)">
                                            @foreach ($par_por_cat as $parametro)
                                                <div class="col s12 m6 l4">
                                                    <div class="input-field">
                                                        @if ($parametro->parametro_id == 8)
                                                 
                                                    
                                                            <i class="fa-solid fa-receipt prefix"></i>
                                                            <select name="parametro_{{ $parametro->parametro_id }}" id="parametro_{{ $parametro->parametro_id }}">
                                                                @foreach ($igvs as $igv)
                                                                    <option value="{{ $igv->id }}" {{ $igv->id == $parametro->valor ? 'selected' : '' }}>{{ $igv->descripcion }}</option>
                                                                @endforeach
                                                            </select>
                                                            <label for="parametro_{{ $parametro->parametro_id }}">{{ $parametro->descripcion}}</label>
                                                          
                                                        @elseif ($parametro->parametro_id == 10)
                                                            <i class="fa-solid fa-receipt prefix"></i>
                                                            <select name="parametro_{{ $parametro->parametro_id }}" id="parametro_{{ $parametro->parametro_id }}">
                                                                @foreach ($tipodocumentos as $tipodocumento)
                                                                    <option value="{{ $tipodocumento->id }}" {{ $tipodocumento->id == $parametro->valor ? 'selected' : '' }}>{{ $tipodocumento->nombre }}</option>
                                                                @endforeach
                                                            </select>
                                                            <label for="parametro_{{ $parametro->parametro_id }}">{{ $parametro->descripcion}}</label>
                                                        @elseif ($parametro->descripcion == 'Tolerancia checkout')
                                                            @php
                                                                $minutosOpciones = [0, 5, 10, 15, 20, 30, 45, 60, 90, 120];
                                                                $valorActual = is_numeric($parametro->valor) ? (int) $parametro->valor : null;
                                                                $valorActualEnOpciones = $valorActual !== null && in_array($valorActual, $minutosOpciones, true);
                                                            @endphp
                                                            <i class="fa-solid fa-hourglass-half prefix"></i>
                                                            <select name="parametro_{{ $parametro->parametro_id }}" id="parametro_{{ $parametro->parametro_id }}">
                                                                @if ($valorActual !== null && !$valorActualEnOpciones)
                                                                    <option value="{{ $valorActual }}" selected>{{ $valorActual }} min</option>
                                                                @endif
                                                                @foreach ($minutosOpciones as $minutos)
                                                                    <option value="{{ $minutos }}" {{ (string) $minutos === (string) $parametro->valor ? 'selected' : '' }}>{{ $minutos }} min</option>
                                                                @endforeach
                                                            </select>
                                                            <label for="parametro_{{ $parametro->parametro_id }}">{{ $parametro->descripcion }}</label>
@elseif (in_array($parametro->descripcion, [
    'Restringir tipo de cambio',
    'Impresiones automáticas',
    'housekeeping',
    'Carrito',
    'Visualizar Finalizado',
    'Ubigeo',
    'Pago anticipado de alquiler',
    'Garantía',
    'Suite',
    'Modificar precios en checkin'
]))
    @php
        $iconos = [
            'Restringir tipo de cambio' => 'fa-scale-balanced',
            'Impresiones automáticas' => 'fa-print',
            'housekeeping' => 'fa-broom',
            'Carrito' => 'fa-cart-shopping',
            'Visualizar Finalizado' => 'fa-eye',
            'Ubigeo' => 'fa-map-location-dot',
            'Pago anticipado de alquiler' => 'fa-money-bill-wave',
            'Garantía' => 'fa-certificate',
            'Suite' => 'fa-bed',
            'Modificar precios en checkin' => 'fa-pen-to-square',
        ];
        $icono = $iconos[$parametro->descripcion] ?? 'fa-toggle-on';
    @endphp

    <i class="fa-solid {{ $icono }} prefix"></i>
    <select name="parametro_{{ $parametro->parametro_id }}" id="parametro_{{ $parametro->parametro_id }}">
        <option value="Si" {{ $parametro->valor == 'Si' ? 'selected' : '' }}>Si</option>
        <option value="No" {{ $parametro->valor == 'No' ? 'selected' : '' }}>No</option>
    </select>
    <label for="parametro_{{ $parametro->parametro_id }}">{{ $parametro->descripcion }}</label>


                                                        @elseif ($parametro->descripcion == 'Horas de entrada y salida')
                                                        <div style="margin-top: -16px;">
                                                            <div class="row" style="display: flex; align-items: center; justify-content: space-between;">
                                                                @php
                                                                    // Corregir el formato del string para hacerlo un JSON válido
                                                                    $parametroValor = preg_replace('/(\[|\])/', '', $parametro->valor); // Eliminar los corchetes []
                                                                    $parametroValor = '{' . $parametroValor . '}'; 
                                                                    // Ahora convertirlo a un array asociativo de PHP
                                                                    $horas = json_decode($parametroValor, true);
                                                                    // Comprobar si json_decode falló
                                                                    if ($horas === null) {
                                                                        dd("Error en la decodificación JSON", $parametroValor);
                                                                    }
                                                                    // Asignar las horas de entrada y salida
                                                                    $hora_entrada = isset($horas['hora_entrada']) ? $horas['hora_entrada'] : '';
                                                                    $hora_salida = isset($horas['hora_salida']) ? $horas['hora_salida'] : '';
                                                                @endphp
                                                         
                                                                <!-- Hora de entrada -->
                                                                <div class="input-field col s6">
                                                                <i class="fa-solid fa-clock prefix"></i>

                                                                    <label for="hora_entrada_{{ $parametro->parametro_id }}" class="active">Hora de entrada</label>
                                                                    <input type="time" id="hora_entrada_{{ $parametro->parametro_id }}" name="hora_entrada"
                                                                        value="{{ old('hora_entrada', $hora_entrada) }}" class="validate">
                                                                </div>

                                                                <!-- Hora de salida -->
                                                                <div class="input-field col s6">
                                                                    <label for="hora_salida_{{ $parametro->parametro_id }}" class="active">Hora de salida</label>
                                                                    <input type="time" id="hora_salida_{{ $parametro->parametro_id }}" name="hora_salida"
                                                                        value="{{ old('hora_salida', $hora_salida) }}" class="validate">
                                                                </div>
                                                            </div>

                                                            <!-- Campo oculto para pasar el JSON con las horas -->
                                                            @php
                                                                // Crear el JSON de las horas seleccionadas
                                                                $horasJSON = json_encode([
                                                                    'hora_entrada' => old('hora_entrada', $hora_entrada),
                                                                    'hora_salida' => old('hora_salida', $hora_salida)
                                                                ]);
                                                            @endphp

                                                            <input type="hidden" name="parametro_{{ $parametro->parametro_id }}" id="parametro_{{ $parametro->parametro_id }}"
                                                                value="{{ old('parametro_'.$parametro->parametro_id, $horasJSON) }}">
                                                        </div>
                                                        @elseif ($parametro->descripcion == 'Tipo Impresión Comprobante')
                                                            <i class="fa-solid fa-print prefix"></i>  <!-- Ícono de impresión para el tipo de impresión -->
                                                            <select name="tipo_impresion" id="tipo_impresion_{{ $parametro->parametro_id }}">
                                                                <option value="Ticket" {{ old('tipo_impresion', $parametro->valor) == 'Ticket' ? 'selected' : '' }}>Ticket</option>
                                                                <option value="A4" {{ old('tipo_impresion', $parametro->valor) == 'A4' ? 'selected' : '' }}>A4</option>
                                                            </select>
                                                            <label for="tipo_impresion_{{ $parametro->parametro_id }}">{{ $parametro->descripcion }}</label>
                                                        @elseif ($parametro->descripcion == 'Vista control de habitaciones')
                                                            <i class="fa-solid fa-eye prefix"></i> <!-- Ícono para vista -->
                                                            <select name="parametro_{{ $parametro->parametro_id }}" id="parametro_{{ $parametro->parametro_id }}">
                                                                <option value="Normal" {{  $parametro->valor == 'Normal' ? 'selected' : '' }}>Normal</option>
                                                                <option value="Amplia" {{ $parametro->valor == 'Amplia' ? 'selected' : '' }}>Amplia</option>
                                                            </select>
                                                            <label for="parametro_{{ $parametro->parametro_id }}">{{ $parametro->descripcion }}</label>

                                                        @elseif ($parametro->descripcion == 'Modalidad tarifario')
                                                            <i class="fa-solid fa-calendar-day prefix"></i>
                                                            <select name="parametro_{{ $parametro->parametro_id }}" id="parametro_{{ $parametro->parametro_id }}">
                                                                <option value="Dias" {{ $parametro->valor == 'Dias' ? 'selected' : '' }}>Días</option>
                                                                <option value="Horas" {{ $parametro->valor == 'Horas' ? 'selected' : '' }}>Horas</option>
                                                                <option value="Ambos" {{ $parametro->valor == 'Ambos' ? 'selected' : '' }}>Ambos</option>
                                                            </select>
                                                            <label for="parametro_{{ $parametro->parametro_id }}">{{ $parametro->descripcion }}</label>
                                                        @elseif ($parametro->descripcion == 'E-mail de administración')
                                                            <i class="fa-solid fa-envelope prefix"></i>
                                                            <div class="chips chips-correo" id="chips_container"></div>
                                                            <input type="hidden" 
                                                                name="parametro_{{ $parametro->parametro_id }}" 
                                                                id="chips_hidden" 
                                                                value="{{ old('parametro_'.$parametro->parametro_id, $parametro->valor) }}">
                                                            <small class="grey-text">Pulse "Enter" para agregar correos</small>
                                                        @elseif($parametro->descripcion == 'Celular de administración')
                                                          {{-- Celulares de administración como chips --}}
                                                            <i class="fa-solid fa-mobile-screen-button prefix"></i>

                                                            <div class="chips chips-celular" id="chips_celulares"></div>

                                                            <input type="hidden"
                                                                name="parametro_{{ $parametro->parametro_id }}"
                                                                id="chips_celulares_hidden"
                                                                value="{{ old('parametro_'.$parametro->parametro_id, $parametro->valor) }}">

                                                            <small class="grey-text">Pulse “Enter” para agregar celulares</small>



                                                        @else
                                                            <i class="fa-solid fa-shield-halved prefix"></i>
                                                            <input autocomplete="off" class="inpt-cantidad submit-form"
                                                                id="parametro_{{ $parametro->parametro_id }}"
                                                                name="parametro_{{ $parametro->parametro_id }}" type="text"
                                                                value="{{ $parametro->valor }}">
                                                            <label class="active"
                                                                for="parametro_{{ $parametro->parametro_id }}">{{ $parametro->descripcion }}</label>
                                                            <span class="helper-text"
                                                                id="parametro_{{ $parametro->parametro_id }}_spn"
                                                                data-error="" data-success=""></span> 
                                                        @endif
                                                       
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @php
                                        $i++;
                                    @endphp
                                @endforeach

                                <div class="row">
                                    <div class="col s12">
                                        <div class="right-align">
                                            <button class="btn waves-effect waves-light btn-mantenimiento" 
                                            type="submit" >GUARDAR<i
                                                class="material-icons right">save</i></button>
                                                <a href="{{ route('movcaja.index') }}"
                                                    class="modal-close waves-effect darken-2 btn-flat btn-cargar-crud"
                                                    data-icono="fa fa-solid fa-cash-register left" 
                                                    data-titulo="Caja chica">{{ __('Cancelar') }} <i class="material-icons right">clear</i></a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                   
                </div>
            </div>

        </div>
    </div>
@endsection
