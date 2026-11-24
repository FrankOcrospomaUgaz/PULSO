@extends('layouts.app')
@section('template_title') Avisos de pacientes @endsection
@section('content')
    <div class="main-body"><div class="row" style="margin-top:0"><div class="col s12">
        <div class="card main-content"><div class="card-content">
            <div class="row" style="display:flex;align-items:center;flex-wrap:wrap;margin-bottom:8px">
                <div class="col s12 m8"><span class="card-title"><i class="fa-regular fa-bell left"></i>Avisos de pacientes</span></div>
                <div class="col s12 m4 right-align"><a href="{{ route('citas.index') }}" class="btn-flat"><i class="fa-solid fa-arrow-left left"></i>Volver a citas</a></div>
            </div>
            <p class="grey-text">Recordatorios de citas y saludos de cumple-mes. Verifica en WhatsApp los avisos inciertos antes de marcarlos como entregados o reintentarlos.</p>
            @if(session('success'))<div class="card-panel green lighten-5 green-text text-darken-3">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="card-panel red lighten-5 red-text text-darken-3">{{ session('error') }}</div>@endif
            <div class="table-wrapper"><div class="table-scroll" style="overflow-x:auto">
                <table class="highlight striped centered" style="width:max-content;min-width:100%"><thead class="thead-custom"><tr><th>Fecha</th><th>Tipo</th><th>Paciente</th><th>Teléfono</th><th>Estado</th><th>Detalle</th><th>Operaciones</th></tr></thead><tbody>
                    @forelse($rows as $n)
                        @php($resoluble = $n->estado === 'INCIERTO' || ($n->estado === 'ENVIANDO' && $n->updated_at?->lt(now()->subMinutes(2))))
                        <tr><td>{{ optional($n->fecha_evento)->format('d/m/Y') }}</td><td>{{ $n->tipo==='CITA'?'Recordatorio de cita':'Cumple-mes' }}</td><td>{{ $n->paciente?->nombres }} {{ $n->paciente?->apellidos }}</td><td>{{ $n->telefono }}</td><td>{{ $n->estado }}</td><td>{{ $n->error ?: ($n->enviado_at?->format('d/m/Y H:i') ?? 'Pendiente') }}</td>
                            <td style="white-space:nowrap">
                                @if($resoluble)
                                    <form method="POST" action="{{ route('avisos-clinicos.resolver',$n->id) }}" style="display:flex;gap:4px;flex-wrap:wrap">@csrf
                                        <button class="btn-small green waves-effect" name="resolucion" value="ENTREGADO" onclick="return confirm('Confirma que verificaste en WhatsApp que el mensaje fue entregado.')">Marcar entregado</button>
                                        <button class="btn-small red waves-effect" name="resolucion" value="NO_ENVIADO" onclick="return confirm('Confirma que verificaste en WhatsApp que el mensaje no fue enviado; se habilitará un nuevo intento.')">Marcar no enviado</button>
                                    </form>
                                @elseif($n->estado==='FALLIDO')
                                    <form method="POST" action="{{ route('avisos-clinicos.reenviar',$n->id) }}">@csrf<button class="btn-small waves-effect waves-light" onclick="return confirm('¿Reintentar este envío rechazado por WhatsApp?')">Reintentar</button></form>
                                @else — @endif
                            </td>
                        </tr>
                    @empty<tr><td colspan="7" class="center grey-text">Aún no hay avisos procesados.</td></tr>@endforelse
                </tbody></table>
            </div></div>
            <div class="row" style="margin-bottom:0"><div class="col s12 m6"><p>Mostrando {{ $rows->firstItem() ?? 0 }} a {{ $rows->lastItem() ?? 0 }} de {{ $rows->total() }} avisos</p></div><div class="col s12 m6 right-align">{{ $rows->links() }}</div></div>
        </div></div>
    </div></div></div>
@endsection
