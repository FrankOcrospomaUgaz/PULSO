@extends('layouts.app')
@section('content')
<div class="container" style="max-width:1050px;padding-top:28px"><a href="{{ route('citas.index') }}" class="grey-text">← Agenda clínica</a><h4>Horarios de atención</h4>
    @if(session('success'))<div class="card-panel green lighten-5 green-text text-darken-3">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="card-panel red lighten-5 red-text">{{ $errors->first() }}</div>@endif
    <div class="card"><div class="card-content"><h5>Agregar turno semanal</h5><form method="POST" action="{{ route('horarios-clinicos.store') }}" class="row">@csrf
        <div class="input-field col s12 m4"><select name="profesional_id" required><option value="" disabled selected>Seleccionar profesional</option>@foreach($profesionales as $p)<option value="{{ $p->id }}">{{ $p->nombres }} {{ $p->apellidos }} · {{ $p->especialidad }}</option>@endforeach</select><label>Profesional</label></div>
        <div class="input-field col s12 m3"><select name="dia_semana" required><option value="1">Lunes</option><option value="2">Martes</option><option value="3">Miércoles</option><option value="4">Jueves</option><option value="5">Viernes</option><option value="6">Sábado</option><option value="7">Domingo</option></select><label>Día</label></div>
        <div class="input-field col s6 m2"><input type="time" name="hora_inicio" required><label class="active">Desde</label></div><div class="input-field col s6 m2"><input type="time" name="hora_fin" required><label class="active">Hasta</label></div><div class="col s12 m1"><button class="btn" aria-label="Guardar horario">+</button></div>
    </form></div></div>
    <div class="card"><div class="card-content"><table class="striped responsive-table"><thead><tr><th>Profesional</th><th>Día</th><th>Horario</th><th></th></tr></thead><tbody>
    @php($dias=[1=>'Lunes',2=>'Martes',3=>'Miércoles',4=>'Jueves',5=>'Viernes',6=>'Sábado',7=>'Domingo'])
    @forelse($rows as $r)<tr><td>{{ $r->profesional?->nombres }} {{ $r->profesional?->apellidos }}</td><td>{{ $dias[$r->dia_semana] ?? $r->dia_semana }}</td><td>{{ substr($r->hora_inicio,0,5) }}–{{ substr($r->hora_fin,0,5) }}</td><td><form method="POST" action="{{ route('horarios-clinicos.destroy',$r->id) }}">@csrf @method('DELETE')<button class="btn-flat red-text" onclick="return confirm('¿Eliminar este horario?')">Eliminar</button></form></td></tr>@empty<tr><td colspan="4" class="center grey-text">Aún no hay horarios configurados.</td></tr>@endforelse
    </tbody></table></div></div>
</div><script>document.addEventListener('DOMContentLoaded',()=>{if(window.M)M.FormSelect.init(document.querySelectorAll('select'));});</script>
@endsection
