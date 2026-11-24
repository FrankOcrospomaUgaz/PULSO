@extends('layouts.app')
@section('content')
<div class="container" style="max-width:1280px;padding-top:28px">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap"><div><span class="grey-text">PULSO+ · ADMISIÓN</span><h4 style="margin:6px 0 18px">Citas y reservas</h4></div><div><a class="btn-flat" href="{{ route('horarios-clinicos.index') }}">Horarios profesionales</a><a class="btn-flat" href="{{ route('avisos-clinicos.index') }}">Avisos</a><a class="btn-flat" href="{{ route('calendario-citas.index') }}">Ver calendario</a><a class="btn-flat" href="{{ route('report.citas.index') }}">Reportes</a></div></div>
    @if(session('success'))<div class="card-panel green lighten-5 green-text text-darken-3">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="card-panel red lighten-5 red-text">{{ $errors->first() }}</div>@endif
    <form class="card-panel row" method="GET" style="margin-bottom:20px"><div class="input-field col s12 m3"><input type="date" name="fecha" value="{{ $date }}"><label class="active">Día de atención</label></div><div class="input-field col s12 m4"><select name="servicio_id"><option value="">Todos los servicios</option>@foreach($servicios as $s)<option value="{{ $s->id }}" @selected(request('servicio_id')==$s->id)>{{ $s->nombre }}</option>@endforeach</select><label>Servicio</label></div><div class="input-field col s12 m3"><select name="profesional_id"><option value="">Todos los profesionales</option>@foreach($profesionales as $p)<option value="{{ $p->id }}" @selected(request('profesional_id')==$p->id)>{{ $p->nombres }} {{ $p->apellidos }}</option>@endforeach</select><label>Profesional</label></div><div class="col s12 m2"><button class="btn-flat">Consultar agenda</button></div></form>
    <div class="card"><div class="card-content"><h5>Reservar una cita</h5><form method="POST" action="{{ route('citas.store') }}" class="row">@csrf
        <div class="input-field col s12 m6"><select name="paciente_id" required><option value="" disabled selected>Selecciona un paciente</option>@foreach($pacientes as $p)<option value="{{ $p->id }}">{{ $p->nombres }} {{ $p->apellidos }}{{ $p->numero_documento?' · '.$p->numero_documento:'' }}</option>@endforeach</select><label>Paciente</label></div>
        <div class="input-field col s12 m6"><select name="profesional_id" required><option value="" disabled selected>Selecciona un profesional</option>@foreach($profesionales as $p)<option value="{{ $p->id }}">{{ $p->nombres }} {{ $p->apellidos }} · {{ $p->especialidad }}</option>@endforeach</select><label>Profesional</label></div>
        <div class="input-field col s12 m6"><select name="servicio_id" required><option value="" disabled selected>Selecciona un servicio</option>@foreach($servicios as $s)<option value="{{ $s->id }}">{{ $s->nombre }} · {{ $s->duracion_minutos }} min · S/ {{ number_format($s->precio,2) }}</option>@endforeach</select><label>Servicio</label></div>
        <div class="input-field col s12 m6"><input type="datetime-local" name="inicio" min="{{ now()->format('Y-m-d\TH:i') }}" required><label class="active">Inicio</label></div>
        <div class="input-field col s12 m6"><input type="date" id="availability_date" min="{{ now()->toDateString() }}" value="{{ $date }}" required><label class="active">Fecha para consultar disponibilidad</label></div>
        <div class="col s12" id="availability" aria-live="polite"><small class="grey-text">Elige profesional, servicio y fecha para consultar los horarios disponibles.</small></div>
        <div class="input-field col s12"><input name="motivo" maxlength="1000"><label>Motivo de consulta (opcional)</label></div><div class="col s12"><button class="btn">Confirmar reserva</button></div>
    </form></div></div>
    <div class="card"><div class="card-content" style="overflow:auto"><h5>Agenda del {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h5><table class="striped responsive-table"><thead><tr><th>Hora</th><th>Paciente</th><th>Servicio</th><th>Profesional</th><th>Estado</th><th>Acción</th></tr></thead><tbody>
    @forelse($rows as $c)<tr><td>{{ $c->inicio->format('H:i') }}–{{ $c->fin->format('H:i') }}</td><td>{{ $c->paciente->nombres }} {{ $c->paciente->apellidos }}<br><small>{{ $c->paciente->telefono }}</small></td><td>{{ $c->servicio->nombre }}</td><td>{{ $c->profesional->nombres }} {{ $c->profesional->apellidos }}</td><td><span class="new badge blue-grey" data-badge-caption="">{{ str_replace('_',' ',$c->estado) }}</span></td><td>@if(in_array($c->estado,['RESERVADA','CONFIRMADA','POSTERGADA']))<form method="POST" action="{{ route('citas.estado',$c->id) }}" style="display:flex;gap:4px;align-items:center;flex-wrap:wrap">@csrf @method('PATCH')<select name="estado" style="width:145px"><option value="CONFIRMADA">Confirmar</option><option value="ATENDIDA">Atendida</option><option value="CANCELADA">Cancelar</option><option value="NO_ASISTIO">No asistió</option><option value="POSTERGADA">Postergar</option></select><input type="datetime-local" name="nuevo_inicio" min="{{ now()->format('Y-m-d\TH:i') }}" aria-label="Nueva fecha y hora" style="width:190px"><button class="btn-small">Actualizar</button></form>@else—@endif</td></tr>@empty<tr><td colspan="6" class="center grey-text">No hay citas para esta fecha.</td></tr>@endforelse
    </tbody></table></div><div class="card-action">{{ $rows->links() }}</div></div>
</div><script>
document.addEventListener('DOMContentLoaded',()=>{
    if(window.M)M.FormSelect.init(document.querySelectorAll('select'));
    const form=document.querySelector('form[action="{{ route('citas.store') }}"]');
    const availability=document.getElementById('availability');
    if(!form||!availability)return;
    const professional=form.elements.profesional_id, service=form.elements.servicio_id, patient=form.elements.paciente_id, start=form.elements.inicio, date=document.getElementById('availability_date');
    let requestId=0;
    const loadSlots=async()=>{
        const current=++requestId;
        if(!professional.value||!service.value||!date.value){availability.innerHTML='<small class="grey-text">Elige profesional, servicio y fecha para consultar los horarios disponibles.</small>';return;}
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
