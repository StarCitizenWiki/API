@extends('layouts.app')

@section('title', 'Forgot password')
@section('meta_description', 'Request a password reset link.')

@section('content')
    <div class="mx-auto flex w-full max-w-md flex-col gap-6">
        <div class="text-center">
            <h1 class="text-2xl font-semibold" data-testid="auth-forgot-password-heading">Reset your password</h1>
            <p class="text-sm text-base-content/70">We will email you a reset link.</p>
        </div>

        <div class="card border border-base-200 bg-base-100 shadow">
            <form method="POST" action="{{ route('password.email') }}" class="card-body gap-4" data-testid="auth-forgot-password-form">
                @csrf

                @if (session('status'))
                    <div class="alert alert-success text-sm">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-error text-sm">
                        <ul class="list-disc space-y-1 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <label class="form-control">
                    <span class="label-text">Email</span>
                    <input
                        type="email"
                        name="email"
                        data-testid="auth-forgot-password-email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="username"
                        class="input input-bordered w-full"
                    />
                </label>

                <button type="submit" class="btn btn-primary w-full" data-testid="auth-forgot-password-submit">Send reset link</button>
            </form>
        </div>

        <div class="text-center text-sm">
            <a class="text-primary" data-testid="auth-forgot-password-login-link" href="{{ route('login') }}">Back to sign in</a>
        </div>
    </div>
@endsection
