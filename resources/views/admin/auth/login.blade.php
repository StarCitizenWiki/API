@extends('admin.layouts.full_width')

@section('body--class', 'bg-dark')

{{-- Page Title --}}
@section('title')
    @lang('auth/login.header')
@endsection

@section('topNav--class', 'd-none')

@section('P__content')
    @component('components.heading')
        @slot('class', 'mt-5')
        @slot('contentClass', 'mt-5 text-white')
        @slot('route', '/')
        Star Citizen Wiki API Admin
    @endcomponent

    <div class="col-sm-6 col-md-3 mx-auto mt-3 text-white">
        @include('components.errors')
        <form role="form" method="POST" action="{{ route('admin_login') }}">
            {{ csrf_field() }}
            <div class="form-group">
                <label for="username" aria-label="E-Mail">@lang('admin/auth.username'):</label>
                <input type="text" class="form-control" id="username" name="username" required aria-required="true" aria-labelledby="username" tabindex="1" data-minlength="3" value="{{ old('username') }}" autofocus>
            </div>

            <div class="form-group">
                <label for="password" aria-label="API Key">@lang('admin/auth.password'):</label>
                <input type="password" class="form-control" id="password" name="password" required aria-required="true" aria-labelledby="password" tabindex="2" data-minlength="3" value="{{ old('password') }}">
            </div>

            <div class="form-group mt-3">
                <button type="submit" class="btn">
                    @lang('auth/login.login')
                </button>
            </div>
        </form>
    </div>
@endsection
