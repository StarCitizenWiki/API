@extends('layouts.app')
@section('title', 'Star Citizen Wiki API - Login')
@section('lead', 'Login')

@section('content')
    @include('layouts.heading')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6 col-md-2 offset-sm-3 offset-md-5 mt-3">
                @include('snippets.errors')
                <form role="form" method="POST" action="{{ route('login') }}">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label for="email" aria-label="E-Mail">E-Mail:</label>
                        <input type="email" class="form-control" id="email" name="email" required aria-required="true" aria-labelledby="email" tabindex="1" data-minlength="3" value="{{ old('email') }}" autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password" aria-label="API Key">API Key:</label>
                        <input type="password" class="form-control" id="password" name="password" required aria-required="true" aria-labelledby="password" tabindex="2" data-minlength="3" value="{{ old('password') }}">
                    </div>

                    <button type="submit" class="btn mt-3">Login</button>
                </form>
            </div>
        </div>
    </div>
@endsection
