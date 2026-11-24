@extends('layouts.app')

@section('template_title')
    Horarios de atención
@endsection

@section('content')
    <div class="main-body">
        <div class="row" style="margin-top:0"><div class="col s12">
            <div class="card main-content"><div class="card-content">
                <div class="row" style="display:flex;align-items:center;flex-wrap:wrap;margin-bottom:12px">
                    <div class="col s12 m8"><span class="card-title"><i class="fa-regular fa-clock left"></i>Horarios de atención</span></div>
                    <div class="col s12 m4 right-align"><a class="btn-flat" href="{{ route('citas.index') }}"><i class="fa-solid fa-arrow-left left"></i>Volver a citas</a></div>
                </div>
                @if(session('success'))<div class="card-panel green lighten-5 green-text text-darken-3">{{ session('success') }}</div>@endif
                @if($errors->any())<div class="card-panel red lighten-5 red-text text-darken-3">{{ $errors->first() }}</div>@endif

                <h6 class="card-title">Agregar turno semanal</h6>
                <form method="POST" action="{{ route('horarios-clinicos.store') }}" class="row">@csrf
                    <div class="input-field col s12 m4"><select id="profesional_id" name="profesional_id" required><option value="" disabled selected>Seleccionar profesional</option>@foreach($profesionales as $p)<option value="{{ $p->id }}">{{ $p->nombres }} {{ $p->apellidos }} · {{ $p->especialidad }}</option>@endforeach</select><label for="profesional_id">Profesional</label>@error('profesional_id')<small class="red-text">{{ $message }}</small>@enderror</div>
                    <div class="input-field col s12 m3"><select id="dia_semana" name="dia_semana" required>@foreach([1=>'Lunes',2=>'Martes',3=>'Miércoles',4=>'Jueves',5=>'Viernes',6=>'Sábado',7=>'Domingo'] as $day=>$name)<option value="{{ $day }}">{{ $name }}</option>@endforeach</select><label for="dia_semana">Día</label>@error('dia_semana')<small class="red-text">{{ $message }}</small>@enderror</div>
                    <div class="input-field col s6 m2"><input id="hora_inicio" type="time" name="hora_inicio" required><label for="hora_inicio" class="active">Desde</label>@error('hora_inicio')<small class="red-text">{{ $message }}</small>@enderror</div>
                    <div class="input-field col s6 m2"><input id="hora_fin" type="time" name="hora_fin" required><label for="hora_fin" class="active">Hasta</label>@error('hora_fin')<small class="red-text">{{ $message }}</small>@enderror</div>
                    <div class="col s12 m1" style="padding-top:12px"><button class="btn waves-effect waves-light" type="submit" aria-label="Guardar horario"><i class="fa-solid fa-plus"></i></button></div>
                </form>

                <div class="divider" style="margin:8px 0 20px"></div>
                <div class="table-wrapper"><div class="table-scroll" style="overflow-x:auto">
                    <table class="highlight striped centered" style="width:max-content;min-width:100%">
                        <thead class="thead-custom"><tr><th>Profesional</th><th>Día</th><th>Horario</th><th>Operaciones</th></tr></thead>
                        <tbody>
                            @php($dias=[1=>'Lunes',2=>'Martes',3=>'Miércoles',4=>'Jueves',5=>'Viernes',6=>'Sábado',7=>'Domingo'])
                            @forelse($rows as $r)
                                <tr><td>{{ $r->profesional?->nombres }} {{ $r->profesional?->apellidos }}</td><td>{{ $dias[$r->dia_semana] ?? $r->dia_semana }}</td><td>{{ substr($r->hora_inicio,0,5) }}–{{ substr($r->hora_fin,0,5) }}</td>
                                    <td><form method="POST" action="{{ route('horarios-clinicos.destroy',$r->id) }}" class="delete-form">@csrf @method('DELETE')<button type="submit" class="btn-floating red waves-effect waves-light tooltipped" data-tooltip="Eliminar horario" aria-label="Eliminar horario"><i class="fa-solid fa-trash"></i></button></form></td>
                                </tr>
                            @empty<tr><td colspan="4" class="center grey-text">Aún no hay horarios configurados.</td></tr>@endforelse
                        </tbody>
                    </table>
                </div></div>
            </div></div>
        </div></div>
    </div>
    <script>document.addEventListener('DOMContentLoaded',()=>{if(window.M)M.FormSelect.init(document.querySelectorAll('select'));});</script>
@endsection
