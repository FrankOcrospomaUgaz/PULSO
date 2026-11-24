@extends('layouts.app')
@section('content')
<div class="container" style="max-width:1200px;padding-top:28px"><div style="display:flex;justify-content:space-between;align-items:center"><div><span class="grey-text">PULSO+ · AGENDA CLÍNICA</span><h4>Calendario de citas</h4></div><a class="btn" href="{{ route('citas.index') }}">Gestionar citas</a></div><div class="card"><div class="card-content"><div id="clinical-calendar"></div></div></div></div>
<script>document.addEventListener('DOMContentLoaded',()=>{const el=document.getElementById('clinical-calendar');if(window.FullCalendar)new FullCalendar.Calendar(el,{initialView:'timeGridWeek',locale:'es',nowIndicator:true,slotMinTime:'07:00:00',slotMaxTime:'21:00:00',height:'auto',events:'{{ route('calendario-citas.eventos') }}'}).render();});</script>
@endsection
