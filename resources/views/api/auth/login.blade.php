@extends('api.layouts.full_width')

@section('body--class', 'bg-dark')

{{-- Page Title --}}
@section('title')
@lang('auth/login.header')
@endsection

@section('topNav--class', 'd-none')

@section('container--class')
@parent mt-5
@endsection

@section('P__content')
    @component('components.heading')
        @slot('class', 'mt-5')
        @slot('contentClass', 'mt-5 text-white')
        @slot('route')
            {{  route('api_index') }}
        @endslot
        @slot('imageClass', 'mb-2 ml-1')
        Star Citizen Wiki API
    @endcomponent

    <div class="col-xs-10 col-sm-6 col-md-2 mx-auto mt-5 text-white">
        @include('components.errors')
        <form method="POST" action="{{ route('auth_login') }}">
            {{ csrf_field() }}
            <div class="form-group">
                <label for="email" aria-label="E-Mail">@lang('auth/login.email'):</label>
                <input type="email" class="form-control" id="email" name="email" required aria-required="true" aria-labelledby="email" tabindex="1" data-minlength="3" value="{{ old('email') }}" autofocus>
            </div>

            <div class="form-group">
                <label for="password" aria-label="API Key">@lang('auth/login.password'):</label>
                <input type="password" class="form-control" id="password" name="password" required aria-required="true" aria-labelledby="password" tabindex="2" data-minlength="3" value="{{ old('password') }}">
            </div>

            <div class="form-group mt-3">
                <button class="btn">
                    @lang('auth/login.login')
                </button>

                <a class="btn btn-link pull-right text-white" href="{{ route('password.request') }}">
                    @lang('auth/login.forgot_password')
                </a>
            </div>
        </form>
    </div>
@endsection
