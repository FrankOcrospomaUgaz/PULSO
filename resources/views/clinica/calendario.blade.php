@extends('layouts.app')
@section('template_title') Calendario de citas @endsection
@section('content')
    <div class="main-body"><div class="row" style="margin-top:0"><div class="col s12">
        <div class="card main-content"><div class="card-content">
            <div class="row" style="display:flex;align-items:center;flex-wrap:wrap;margin-bottom:8px">
                <div class="col s12 m8"><span class="card-title"><i class="fa-regular fa-calendar left"></i>Calendario de citas</span></div>
                <div class="col s12 m4 right-align"><a class="btn waves-effect waves-light" href="{{ route('citas.index') }}"><i class="fa-solid fa-list left"></i>Gestionar citas</a></div>
            </div>
            <div id="clinical-calendar"></div>
        </div></div>
    </div></div></div>
    <script>document.addEventListener('DOMContentLoaded',()=>{const el=document.getElementById('clinical-calendar');if(window.FullCalendar)new FullCalendar.Calendar(el,{initialView:'timeGridWeek',locale:'es',nowIndicator:true,slotMinTime:'07:00:00',slotMaxTime:'21:00:00',height:'auto',events:'{{ route('calendario-citas.eventos') }}'}).render();});</script>
@endsection
