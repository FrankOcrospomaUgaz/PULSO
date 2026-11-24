@extends('layouts.app')
@section('content')
<div class="container" style="max-width:1200px;padding-top:28px">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">
        <div><span class="grey-text">PULSO+ · ADMISIÓN</span><h4 style="margin:6px 0">{{ $title }}</h4></div>
        <a class="btn waves-effect" href="{{ route($type.'.create') }}"><i class="material-icons left">add</i>Nuevo registro</a>
    </div>
    @if(session('success'))<div class="card-panel green lighten-5 green-text text-darken-3">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="card-panel red lighten-5 red-text text-darken-3">{{ session('error') }}</div>@endif
    <form method="GET" class="card-panel" style="display:flex;gap:12px;align-items:center">
        <input name="q" value="{{ $search }}" placeholder="Buscar por nombre, documento o contacto" style="margin:0">
        <button class="btn-flat" type="submit">Buscar</button>
    </form>
    <div class="card"><div class="card-content" style="overflow:auto"><table class="striped responsive-table"><thead><tr>
        @if($type==='servicios-clinicos')<th>Servicio</th><th>Duración</th><th>Precio</th><th>Estado</th>
        @elseif($type==='profesionales')<th>Profesional</th><th>Especialidad</th><th>Contacto</th><th>Estado</th>
        @else<th>Paciente</th><th>Documento</th><th>Fecha nac.</th><th>Contacto</th><th>Tutor</th>@endif<th></th>
    </tr></thead><tbody>
    @forelse($rows as $row)<tr>
        @if($type==='servicios-clinicos')<td>{{ $row->nombre }}</td><td>{{ $row->duracion_minutos }} min</td><td>S/ {{ number_format($row->precio,2) }}</td><td>{{ $row->activo?'Activo':'Inactivo' }}</td>
        @elseif($type==='profesionales')<td>{{ $row->nombres }} {{ $row->apellidos }}</td><td>{{ $row->especialidad }}</td><td>{{ $row->telefono }}<br>{{ $row->email }}</td><td>{{ $row->activo?'Activo':'Inactivo' }}</td>
        @else<td>{{ $row->nombres }} {{ $row->apellidos }}</td><td>{{ $row->tipo_documento }} {{ $row->numero_documento }}</td><td>{{ optional($row->fecha_nacimiento)->format('d/m/Y') }}</td><td>{{ $row->telefono }}<br>{{ $row->email }}</td><td>{{ $row->tutor_nombres }}<br>{{ $row->tutor_telefono }}</td>@endif
        <td><a href="{{ route($type.'.edit',$row->id) }}">Editar</a></td>
    </tr>@empty<tr><td colspan="7" class="center grey-text">No hay registros para mostrar.</td></tr>@endforelse
    </tbody></table></div><div class="card-action">{{ $rows->links() }}</div></div>
</div>
@endsection
