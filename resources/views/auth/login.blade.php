@extends('layouts.app')

@section('title', 'Sign in')
@section('meta_description', 'Sign in to your account.')

@section('content')
    <div class="mx-auto flex w-full max-w-md flex-col gap-6">
        <div class="text-center">
            <h1 class="text-2xl font-semibold">Welcome back</h1>
            <p class="text-sm text-base-content/70">Sign in to continue.</p>
        </div>

        <div class="card border border-base-200 bg-base-100 shadow">
            <form method="POST" action="{{ route('login.store') }}" class="card-body gap-4">
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
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="input input-bordered w-full"
                    />
                </label>

                <label class="form-control">
                    <div class="label justify-between">
                        <span class="label-text">Password</span>
                        <a class="text-xs text-primary" href="{{ route('password.request') }}">Forgot?</a>
                    </div>
                    <input
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="input input-bordered w-full"
                    />
                </label>

                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" name="remember" class="checkbox checkbox-sm" />
                    <span class="label-text">Remember me</span>
                </label>

                <button type="submit" class="btn btn-primary w-full">Sign in</button>
            </form>
        </div>

        @if (Route::has('register'))
            <div class="text-center text-sm">
                <span class="text-base-content/70">New here?</span>
                <a class="text-primary" href="{{ route('register') }}">Create an account</a>
            </div>
        @endif
    </div>
@endsection
