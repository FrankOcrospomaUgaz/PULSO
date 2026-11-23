@extends('layouts.app')

@section('template_title')
    {{ __('Update') }} User
@endsection

@section('content')
<div class="main-body">
    <div class="row">
            <div class="col s12">

                <div class="card main-content">
                    <div class="card-content">
                        <span class="card-title">{{ __('Update') }} User</span>
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('users.update', $user->id) }}"  role="form" enctype="multipart/form-data">
                            {{ method_field('PATCH') }}
                            @csrf

                            @include('user.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
