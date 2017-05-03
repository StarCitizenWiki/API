@extends('layouts.app')
@section('title')
    @lang('auth/account/edit.header')
@endsection

@section('content')
    @include('layouts.heading');
    <div class="container">
        <div class="row">
            <div class="col-sm-12 col-md-6 offset-md-3 mt-5">
                @include('components.errors')
                <form role="form" method="POST" action="{{ route('account_update') }}">
                    {{ csrf_field() }}
                    <input name="_method" type="hidden" value="PATCH">
                    <div class="form-group">
                        <label for="name" aria-label="Name">@lang('auth/account/edit.name'):</label>
                        <input type="text" class="form-control" id="name" name="name" aria-labelledby="name" tabindex="1" value="{{ $user->name }}" autofocus>
                    </div>
                    <div class="form-group">
                        <label for="email" aria-label="E-Mail">@lang('auth/account/edit.email'):</label>
                        <input type="email" class="form-control" id="email" name="email" required aria-required="true" aria-labelledby="email" tabindex="2" data-minlength="3" value="{{ $user->email }}">
                    </div>
                    <div class="form-group">
                        <label for="password" aria-label="Passwort">@lang('auth/account/edit.password'):</label>
                        <input type="password" class="form-control" id="password" name="password" aria-labelledby="password" tabindex="3" data-minlength="8">
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation" aria-label="Passwort">@lang('auth/account/edit.password_confirmation'):</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" aria-labelledby="password" tabindex="4" data-minlength="8">
                    </div>
                    <button type="submit" class="btn btn-warning my-3">@lang('auth/account/edit.edit')</button>
                </form>
            </div>
        </div>
    </div>
@endsection

