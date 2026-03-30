@extends('layouts.app')

@section('title', 'Confirm password')
@section('meta_description', 'Confirm your password to continue.')

@section('content')
    <div class="mx-auto flex w-full max-w-md flex-col gap-6">
        <div class="text-center">
            <h1 class="text-2xl font-semibold" data-testid="auth-confirm-password-heading">Confirm your password</h1>
            <p class="text-sm text-base-content/70">This action requires a fresh confirmation.</p>
        </div>

        <div class="card border border-base-200 bg-base-100 shadow">
            <form method="POST" action="{{ route('password.confirm.store') }}" class="card-body gap-4" data-testid="auth-confirm-password-form">
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
                    <span class="label-text">Password</span>
                    <input
                        type="password"
                        name="password"
                        data-testid="auth-confirm-password-password"
                        required
                        autocomplete="current-password"
                        class="input input-bordered w-full"
                    />
                </label>

                <button type="submit" class="btn btn-primary w-full" data-testid="auth-confirm-password-submit">Confirm</button>
            </form>
        </div>
    </div>
@endsection
