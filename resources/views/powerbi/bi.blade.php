@extends('layouts.app')

@section('content')
<div class="main-body">
    <div class="row" style="margin-top: 0;">
        <div class="col s12">
            <div class="card main-content" style="padding: 0; overflow: hidden; height: calc(100vh + 140px);border-radius: 30px;">
                <iframe 
                    title="{{ $sucursal->nombrecomercial }}" 
                    class="bi-frame"
                    src="{{ $sucursal->business_intelligence_url }}" 
                    frameborder="0"
                    allowfullscreen="true"
                    style="width: 100%; height: 100%;   border-radius: 30px; margin-top: -1px;">
                </iframe>
            </div>
        </div>
    </div>
</div>
@endsection
