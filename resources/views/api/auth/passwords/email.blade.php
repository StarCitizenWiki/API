@extends('api.layouts.full_width')

@section('body--class', 'bg-dark')

{{-- Page Title --}}
@section('P__title')
    @lang('auth/passwords/email.header')
@endsection

@section('topNav--class', 'bg-blue-grey navbar-fixed-top w-100')

@section('P__content')
    @component('components.heading')
        @slot('class', 'mt-5')
        @slot('contentClass', 'mt-5 text-white')
        Star Citizen Wiki API
    @endcomponent

    <div class="col-sm-6 col-md-3 mx-auto mt-3 text-white">
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <form class="form-horizontal" role="form" method="POST" action="{{ route('password.email') }}">
            {{ csrf_field() }}

            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                <label for="email" class="control-label">@lang('auth/passwords/email.email'):</label>
                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>

                @if ($errors->has('email'))
                    <span class="help-block">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                @endif
            </div>

            <div class="form-group">
                <button type="submit" class="btn">
                    @lang('auth/passwords/email.send_mail')
                </button>
            </div>
        </form>
    </div>
@endsection
