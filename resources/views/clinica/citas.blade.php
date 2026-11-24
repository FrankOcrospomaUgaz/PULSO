@extends('layouts.app')

@section('template_title')
    Citas y reservas
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top:0"><div class="col s12">
            <div class="card main-content"><div class="card-content">
                <div class="row" style="display:flex;align-items:center;flex-wrap:wrap;margin-bottom:8px">
                    <div class="col s12 l5"><span class="card-title"><i class="fa-regular fa-calendar-check left"></i>Citas y reservas</span></div>
                    <div class="col s12 l7 right-align">
                        <a class="btn-flat" href="{{ route('horarios-clinicos.index') }}"><i class="fa-regular fa-clock left"></i>Horarios</a>
                        <a class="btn-flat" href="{{ route('calendario-citas.index') }}"><i class="fa-regular fa-calendar left"></i>Calendario</a>
                        <a class="btn-flat" href="{{ route('report.citas.index') }}"><i class="fa-solid fa-chart-column left"></i>Reportes</a>
                        <a class="btn-flat" href="{{ route('avisos-clinicos.index') }}"><i class="fa-regular fa-bell left"></i>Avisos</a>
                    </div>
                </div>
                @if(session('success'))<div class="card-panel green lighten-5 green-text text-darken-3">{{ session('success') }}</div>@endif
                @if($errors->any())<div class="card-panel red lighten-5 red-text text-darken-3">{{ $errors->first() }}</div>@endif

                <form class="row" method="GET" style="margin-bottom:0">
                    <div class="input-field col s12 m3"><input id="fecha" type="date" name="fecha" value="{{ $date }}"><label for="fecha" class="active">Día de atención</label></div>
                    <div class="input-field col s12 m3"><select id="servicio_id_filter" name="servicio_id"><option value="">Todos los servicios</option>@foreach($servicios as $s)<option value="{{ $s->id }}" @selected(request('servicio_id')==$s->id)>{{ $s->nombre }}</option>@endforeach</select><label for="servicio_id_filter">Servicio</label></div>
                    <div class="input-field col s12 m3"><select id="profesional_id_filter" name="profesional_id"><option value="">Todos los profesionales</option>@foreach($profesionales as $p)<option value="{{ $p->id }}" @selected(request('profesional_id')==$p->id)>{{ $p->nombres }} {{ $p->apellidos }}</option>@endforeach</select><label for="profesional_id_filter">Profesional</label></div>
                    <div class="col s12 m3" style="padding-top:12px"><button class="btn waves-effect waves-light" type="submit"><i class="fa-solid fa-magnifying-glass left"></i>Consultar</button></div>
                </form>

                <div class="divider" style="margin:8px 0 20px"></div>
                <h6 class="card-title"><i class="fa-solid fa-plus left"></i>Reservar una cita</h6>
                <form method="POST" action="{{ route('citas.store') }}" class="row">@csrf
                    <div class="input-field col s12 m6"><select id="paciente_id" name="paciente_id" required><option value="" disabled selected>Selecciona un paciente</option>@foreach($pacientes as $p)<option value="{{ $p->id }}">{{ $p->nombres }} {{ $p->apellidos }}{{ $p->numero_documento?' · '.$p->numero_documento:'' }}</option>@endforeach</select><label for="paciente_id">Paciente</label></div>
                    <div class="input-field col s12 m6"><select id="profesional_id" name="profesional_id" required><option value="" disabled selected>Selecciona un profesional</option>@foreach($profesionales as $p)<option value="{{ $p->id }}">{{ $p->nombres }} {{ $p->apellidos }} · {{ $p->especialidad }}</option>@endforeach</select><label for="profesional_id">Profesional</label></div>
                    <div class="input-field col s12 m6"><select id="servicio_id" name="servicio_id" required><option value="" disabled selected>Selecciona un servicio</option>@foreach($servicios as $s)<option value="{{ $s->id }}">{{ $s->nombre }} · {{ $s->duracion_minutos }} min · S/ {{ number_format($s->precio,2) }}</option>@endforeach</select><label for="servicio_id">Servicio</label></div>
                    <div class="input-field col s12 m6"><input id="inicio" type="datetime-local" name="inicio" min="{{ now()->format('Y-m-d\\TH:i') }}" required><label for="inicio" class="active">Fecha y hora</label></div>
                    <div class="input-field col s12 m6"><input id="availability_date" type="date" min="{{ now()->toDateString() }}" value="{{ $date }}" required><label for="availability_date" class="active">Fecha para consultar disponibilidad</label></div>
                    <div class="col s12 m6" id="availability" aria-live="polite" style="padding-top:14px"><small class="grey-text">Elige profesional, servicio y fecha para consultar horarios.</small></div>
                    <div class="input-field col s12"><input id="motivo" name="motivo" maxlength="1000"><label for="motivo">Motivo de consulta (opcional)</label></div>
                    <div class="col s12"><button class="btn waves-effect waves-light" type="submit"><i class="fa-solid fa-check left"></i>Confirmar reserva</button></div>
                </form>

                <div class="divider" style="margin:14px 0 20px"></div>
                <h6 class="card-title">Agenda del {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h6>
                <div class="table-wrapper"><div class="table-scroll" style="overflow-x:auto">
                    <table class="highlight striped centered" style="width:max-content;min-width:100%">
                        <thead class="thead-custom"><tr><th>Hora</th><th>Paciente</th><th>Servicio</th><th>Profesional</th><th>Estado</th><th>Operaciones</th></tr></thead>
                        <tbody>
                            @forelse($rows as $c)
                                <tr>
                                    <td>{{ $c->inicio->format('H:i') }}–{{ $c->fin->format('H:i') }}</td>
                                    <td>{{ $c->paciente->nombres }} {{ $c->paciente->apellidos }}<br><small>{{ $c->paciente->telefono }}</small></td>
                                    <td>{{ $c->servicio->nombre }}</td><td>{{ $c->profesional->nombres }} {{ $c->profesional->apellidos }}</td>
                                    <td><span class="new badge blue-grey" data-badge-caption="">{{ str_replace('_',' ',$c->estado) }}</span></td>
                                    <td>
                                        @if(in_array($c->estado,['RESERVADA','CONFIRMADA','POSTERGADA']))
                                            <form method="POST" action="{{ route('citas.estado',$c->id) }}" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">@csrf @method('PATCH')
                                                <select name="estado" class="browser-default" style="width:150px"><option value="CONFIRMADA">Confirmar</option><option value="ATENDIDA">Atendida</option><option value="CANCELADA">Cancelar</option><option value="NO_ASISTIO">No asistió</option><option value="POSTERGADA">Postergar</option></select>
                                                <input type="datetime-local" name="nuevo_inicio" min="{{ now()->format('Y-m-d\\TH:i') }}" aria-label="Nueva fecha y hora" style="width:190px">
                                                <button class="btn-small waves-effect waves-light" type="submit">Actualizar</button>
                                            </form>
                                        @else — @endif
                                    </td>
                                </tr>
                            @empty<tr><td colspan="6" class="center grey-text">No hay citas para esta fecha.</td></tr>@endforelse
                        </tbody>
                    </table>
                </div></div>
                <div class="row" style="margin-bottom:0"><div class="col s12 m6"><p>Mostrando {{ $rows->firstItem() ?? 0 }} a {{ $rows->lastItem() ?? 0 }} de {{ $rows->total() }} citas</p></div><div class="col s12 m6 right-align">{{ $rows->links() }}</div></div>
            </div></div>
        </div></div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded',()=>{
        if(window.M)M.FormSelect.init(document.querySelectorAll('select'));
        const form=document.querySelector('form[action="{{ route('citas.store') }}"]');
        const availability=document.getElementById('availability');
        if(!form||!availability)return;
        const professional=form.elements.profesional_id, service=form.elements.servicio_id, patient=form.elements.paciente_id, start=form.elements.inicio, date=document.getElementById('availability_date');
        let requestId=0;
        const loadSlots=async()=>{
            const current=++requestId;
            if(!professional.value||!service.value||!date.value){availability.innerHTML='<small class="grey-text">Elige profesional, servicio y fecha para consultar horarios.</small>';return;}
            const params=new URLSearchParams({profesional_id:professional.value,servicio_id:service.value,fecha:date.value});
            if(patient.value)params.set('paciente_id',patient.value);
            availability.innerHTML='<small class="grey-text">Consultando horarios…</small>';
            try{
                const response=await fetch('{{ route('citas.disponibilidad') }}?'+params,{headers:{Accept:'application/json'}});
                const data=await response.json(); if(current!==requestId)return;
                if(!response.ok)throw new Error(data.message||'No se pudo consultar disponibilidad.');
                availability.replaceChildren();
                const label=document.createElement('small');label.className='grey-text';label.textContent=data.horarios.length?'Horarios disponibles:':'No hay horarios disponibles para esta selección.';availability.append(label);
                data.horarios.forEach(slot=>{const button=document.createElement('button');button.type='button';button.className='btn-flat';button.textContent=slot.hora;button.addEventListener('click',()=>{start.value=slot.inicio.slice(0,16);});availability.append(button);});
            }catch(error){if(current===requestId)availability.textContent=error.message;}
        };
        [professional,service,patient,date].forEach(element=>element.addEventListener('change',loadSlots));
    });
    </script>
@endsection
