@extends('layouts.app')

@section('title', 'Verify email')
@section('meta_description', 'Verify your email address.')

@section('content')
    <div class="mx-auto flex w-full max-w-md flex-col gap-6">
        <div class="text-center">
            <h1 class="text-2xl font-semibold">Verify your email</h1>
            <p class="text-sm text-base-content/70">We sent a verification link to your email address.</p>
        </div>

        <div class="card border border-base-200 bg-base-100 shadow">
            <div class="card-body gap-4">
                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success text-sm">A new verification link has been sent.</div>
                @endif

                <p class="text-sm text-base-content/70">
                    Before continuing, please check your inbox for a verification link.
                </p>

                <form method="POST" action="{{ route('verification.send') }}" class="flex flex-col gap-3">
                    @csrf
                    <button type="submit" class="btn btn-primary w-full">Resend verification email</button>
                </form>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <button type="submit" class="btn btn-ghost btn-sm">Log out</button>
        </form>
    </div>
@endsection
