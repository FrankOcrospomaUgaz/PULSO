@extends('layouts.app')

@section('template_title')
    Reservar Habitación {{ $habitacion->numero }}
@endsection

@section('content')
    <div class="main-body">
        <div class="row mt-3">
            <div class="col s12">
                <div class="card main-content">
                    <div class="row">
                        <!-- Calendario a la izquierda -->
                        <div class="col s12 m7">
                            <div class="card-body p-4">
                                <div id="calendar" class="border rounded-3 shadow-sm p-3 bg-white"></div>
                            </div>
                        </div>

                        <!-- Detalles de reserva a la derecha -->
                        <div class="col s12 m5">
                            <div class="card hoverable z-depth-3 sticky-card" style="border-radius: 15px;">
                                <div class="card-content">
                                    <h5 class="fw-bold"><i class="fas fa-calendar-day"></i> Detalles</h5>

                                    <form action="{{ route('reserva.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="habitacion_id" value="{{ $habitacion->id }}">

                                        <!-- Selección de Persona -->
                                        <div class="row">
                                            <div class="input-field col s12 m10">
                                                <i class="material-icons prefix">person</i>
                                                <input type="hidden" name="persona_principal" id="persona_id"
                                                    value="{{ old('persona_principal', $principalId ?? '') }}">
                                                <input type="text" id="persona_autocomplete"
                                                    class="autocomplete validate @error('persona_principal') invalid @enderror"
                                                    placeholder="Seleccione un huesped"
                                                    data-autocomplete-url="{{ route('autocomplete.personas') }}"
                                                    value="{{ isset($personaCreada) ? "{$personaCreada->personamaestro->nrodoc} - {$personaCreada->apellidos} {$personaCreada->nombres}" : '' }}"
                                                    autocomplete="off" required>
                                                <label for="persona_principal_autocomplete" class="active">
                                                    Huesped</label>
                                                {!! $errors->first(
                                                    'persona_principal',
                                                    '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                                                ) !!}
                                            </div>

                                            <div class="input-field col s12 m2">
                                                <!-- Botón para quitar cliente -->
                                                <button class="btn red left tooltipped" id="btnQuitarCliente"
                                                    data-tooltip="Deseleccionar cliente" type="button"
                                                    onclick="eliminarPersona();"
                                                    style="border-radius: 99em; {{ isset($personaCreada) ? 'display: block;' : 'display: none;' }}">
                                                    <i class="fa-solid fa-user-slash"></i>
                                                </button>

                                                <!-- Botón para agregar nuevo cliente -->
                                                <button
                                                    href="{{ route('persona.create', [$sucursal->id, 'CHECKIN', $habitacion->id ?? null]) }}?es_principal=true"
                                                    class="btn teal right tooltipped btn-crear" type="button"
                                                    data-tooltip="Agregar Nuevo Cliente" id="btnNuevaPersona"
                                                    data-target="modal-nuevo-cliente"
                                                    style="border-radius: 99em; {{ isset($personaCreada) ? 'display: none;' : 'display: block;' }}">
                                                    <i class="material-icons">person_add</i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Motivo de Reserva -->
                                        <div class="input-field col s12 m12">
                                            <select id="motivo_reserva" name="motivo_reserva" required>
                                                <option value="" disabled selected>Seleccionar Motivo</option>
                                                <option value="negocios"
                                                    {{ old('motivo_reserva') == 'negocios' ? 'selected' : '' }}>Negocios
                                                </option>
                                                <option value="turismo"
                                                    {{ old('motivo_reserva') == 'turismo' ? 'selected' : '' }}>Turismo
                                                </option>
                                                <option value="evento"
                                                    {{ old('motivo_reserva') == 'evento' ? 'selected' : '' }}>Evento
                                                </option>
                                                <option value="otros"
                                                    {{ old('motivo_reserva') == 'otros' ? 'selected' : '' }}>Otros</option>
                                            </select>
                                            <label for="motivo_reserva">📄 Motivo de la Reserva</label>
                                        </div>


                                        <!-- Fechas -->
                                        <div class="mb-3">
                                            <label for="fecha_entrada" class="form-label fw-bold">📅 Entrada</label>
                                            <input type="text" id="fecha_entrada" name="fecha_entrada"
                                                class="form-control form-control-lg rounded-pill text-center fw-bold bg-white"
                                                readonly required>
                                        </div>

                                        <div class="mb-4">
                                            <label for="fecha_salida" class="form-label fw-bold">📅 Salida</label>
                                            <input type="text" id="fecha_salida" name="fecha_salida"
                                                class="form-control form-control-lg rounded-pill text-center fw-bold bg-white"
                                                readonly required>
                                        </div>

                                        <div class="text-center">
                                            <button type="submit" class="btn btn-success btn-lg fw-bold px-4 shadow-sm">
                                                <i class="fas fa-check"></i> Reservar
                                            </button>
                                            <a href="{{ route('habitacion.index') }}"
                                                class="btn btn-danger btn-lg fw-bold px-4 shadow-sm ms-2">
                                                <i class="fas fa-times"></i> Cancelar
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .sticky-card {
            position: sticky;
            top: 15px;
        }

        .bg-dark {
            background: #333;
        }

        .btn-success:hover,
        .btn-danger:hover {
            transform: scale(1.05);
            transition: all 0.2s ease-in-out;
        }

        #calendar {
            max-height: 450px;
            overflow-y: auto;
            border: 2px solid #00bbff;
        }

        .form-control-lg {
            font-size: 1.2rem;
        }

        .shadow-lg {
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2) !important;
        }

        a {
            color: #26a69a;
            text-decoration: none;
            -webkit-tap-highlight-color: transparent;
        }
    </style>
@endsection

