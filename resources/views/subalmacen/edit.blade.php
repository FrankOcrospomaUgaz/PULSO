@extends('layouts.app')

@section('template_title')
    {{ __('Update') }} Subalmacen
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="">
            <div class="col-md-12">

                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">{{ __('Update') }} Subalmacen</span>
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('subalmacens.update', $subalmacen->id) }}"  role="form" enctype="multipart/form-data">
                            {{ method_field('PATCH') }}
                            @csrf

                            @include('subalmacen.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
