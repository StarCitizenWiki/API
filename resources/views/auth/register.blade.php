@extends('layouts.app')

@section('title', 'Create account')
@section('meta_description', 'Create a new account.')

@section('content')
    @php
        $versionCode = $selectedGameVersionCode ?? session('game_version_code') ?? request()->query('version');
        $versionQuery = $versionCode ? ['version' => $versionCode] : [];
    @endphp

    <div class="mx-auto flex w-full max-w-md flex-col gap-6">
        <div class="text-center">
            <h1 class="text-2xl font-semibold">Create account</h1>
            <p class="text-sm text-base-content/70">Join the platform in seconds.</p>
        </div>

        <div class="card border border-base-200 bg-base-100 shadow">
            <form method="POST" action="{{ route('register.store', $versionQuery) }}" class="card-body gap-4">
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
                        required
                        autocomplete="new-password"
                        class="input input-bordered w-full"
                    />
                </label>

                <button type="submit" class="btn btn-primary w-full">Create account</button>
            </form>
        </div>

        <div class="text-center text-sm">
            <span class="text-base-content/70">Already have an account?</span>
            <a class="text-primary" href="{{ route('login', $versionQuery) }}">Sign in</a>
        </div>
    </div>
@endsection
