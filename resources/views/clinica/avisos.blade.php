@extends('layouts.app')
@section('content')
<div class="container" style="max-width:1150px;padding-top:28px"><a href="{{ route('citas.index') }}" class="grey-text">← Agenda clínica</a><h4>Avisos de pacientes</h4>
    <p class="grey-text">Incluye recordatorios de citas y saludos de cumple-mes. Verifica en WhatsApp los avisos inciertos antes de marcarlos como entregados o habilitar un reintento.</p>
    @if(session('success'))<div class="card-panel green lighten-5 green-text text-darken-3">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="card-panel red lighten-5 red-text">{{ session('error') }}</div>@endif
    <div class="card"><div class="card-content" style="overflow:auto"><table class="striped responsive-table"><thead><tr><th>Fecha</th><th>Tipo</th><th>Paciente</th><th>Teléfono</th><th>Estado</th><th>Detalle</th><th></th></tr></thead><tbody>
    @forelse($rows as $n)
        @php($resoluble = $n->estado === 'INCIERTO' || ($n->estado === 'ENVIANDO' && $n->updated_at?->lt(now()->subMinutes(2))))
        <tr>
            <td>{{ optional($n->fecha_evento)->format('d/m/Y') }}</td>
            <td>{{ $n->tipo==='CITA'?'Recordatorio de cita':'Cumple-mes' }}</td>
            <td>{{ $n->paciente?->nombres }} {{ $n->paciente?->apellidos }}</td>
            <td>{{ $n->telefono }}</td>
            <td>{{ $n->estado }}</td>
            <td>{{ $n->error ?: ($n->enviado_at?->format('d/m/Y H:i') ?? 'Pendiente') }}</td>
            <td>
                @if($resoluble)
                    <form method="POST" action="{{ route('avisos-clinicos.resolver',$n->id) }}" style="display:flex;gap:4px;flex-wrap:wrap">
                        @csrf
                        <button class="btn-flat" name="resolucion" value="ENTREGADO" onclick="return confirm('Confirma que verificaste en WhatsApp que el mensaje fue entregado.')">Marcar entregado</button>
                        <button class="btn-flat red-text" name="resolucion" value="NO_ENVIADO" onclick="return confirm('Confirma que verificaste en WhatsApp que el mensaje no fue enviado; se habilitará un nuevo intento.')">Marcar no enviado</button>
                    </form>
                @elseif($n->estado==='FALLIDO')
                    <form method="POST" action="{{ route('avisos-clinicos.reenviar',$n->id) }}">@csrf<button class="btn-flat" onclick="return confirm('¿Reintentar este envío rechazado por WhatsApp?')">Reintentar</button></form>
                @endif
            </td>
        </tr>
    @empty<tr><td colspan="7" class="center grey-text">Aún no hay avisos procesados.</td></tr>@endforelse
    </tbody></table></div><div class="card-action">{{ $rows->links() }}</div></div>
</div>
@endsection
