@extends('layouts.app')

@section('template_title')
    {{ __('Create') }} User
@endsection

@section('content')
<div class="main-body">
    <div class="row">
            <div class="col s12">

                <div class="card main-content">
                    <div class="card-content">
                        <span class="card-title">{{ __('Create') }} User</span>
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('users.store') }}"  role="form" enctype="multipart/form-data">
                            @csrf

                            @include('user.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
