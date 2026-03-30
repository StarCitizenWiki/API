@extends('layouts.app')

@section('title', 'Star Citizen Wiki API')
@section('meta_description', 'Create a new account.')

@section('content')
    <div class="mx-auto flex w-full max-w-md flex-col gap-6">
        <div class="text-center">
            <h1 class="text-2xl font-semibold" data-testid="auth-register-heading">Star Citizen Wiki API</h1>
            <p class="text-sm text-base-content/70">Create a new account.</p>
        </div>

        <div class="card border border-base-200 bg-base-100 shadow">
            <form method="POST" action="{{ route('register.store') }}" class="card-body gap-4" data-testid="auth-register-form">
                @csrf

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
                    <span class="label-text">Name</span>
                    <input
                        type="text"
                        name="name"
                        data-testid="auth-register-name"
                        value="{{ old('name') }}"
                        required
                        autocomplete="name"
                        class="input input-bordered w-full"
                    />
                </label>

                <label class="form-control">
                    <span class="label-text">Email</span>
                    <input
                        type="email"
                        name="email"
                        data-testid="auth-register-email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="username"
                        class="input input-bordered w-full"
                    />
                </label>

                <label class="form-control">
                    <span class="label-text">Password</span>
                    <input
                        type="password"
                        name="password"
                        data-testid="auth-register-password"
                        required
                        autocomplete="new-password"
                        class="input input-bordered w-full"
                    />
                </label>

                <label class="form-control">
                    <span class="label-text">Confirm password</span>
                    <input
                        type="password"
                        name="password_confirmation"
                        data-testid="auth-register-password-confirmation"
                        required
                        autocomplete="new-password"
                        class="input input-bordered w-full"
                    />
                </label>

                <button type="submit" class="btn btn-primary w-full" data-testid="auth-register-submit">Create account</button>
            </form>
        </div>

        <div class="text-center text-sm">
            <span class="text-base-content/70">Already have an account?</span>
            <a class="text-primary" data-testid="auth-register-login-link" href="{{ route('login') }}">Sign in</a>
        </div>
    </div>
@endsection
