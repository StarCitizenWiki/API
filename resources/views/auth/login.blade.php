@extends('layouts.app')
@section('title', 'Login')

@section('content')
    @include('layouts.heading')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6 col-md-3 mx-auto mt-3">
                @include('components.errors')
                <form role="form" method="POST" action="{{ route('auth_login') }}">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label for="email" aria-label="E-Mail">E-Mail:</label>
                        <input type="email" class="form-control" id="email" name="email" required aria-required="true" aria-labelledby="email" tabindex="1" data-minlength="3" value="{{ old('email') }}" autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password" aria-label="API Key">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" required aria-required="true" aria-labelledby="password" tabindex="2" data-minlength="3" value="{{ old('password') }}">
                    </div>

                    <div class="form-group mt-3">
                        <button type="submit" class="btn">
                            Login
                        </button>

                        <a class="btn btn-link" href="{{ route('password.request') }}">
                            Forgot Your Password?
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
