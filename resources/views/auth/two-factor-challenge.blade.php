@extends('layouts.app')

@section('title', 'Two-factor challenge')
@section('meta_description', 'Complete two-factor authentication.')

@section('content')
    <div class="mx-auto flex w-full max-w-md flex-col gap-6">
        <div class="text-center">
            <h1 class="text-2xl font-semibold">Two-factor challenge</h1>
            <p class="text-sm text-subtle">Enter your authentication code or a recovery code.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-error text-sm">
                <ul class="list-disc space-y-1 pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card border border-base-200 bg-base-100 shadow">
            <form method="POST" action="{{ route('two-factor.login.store') }}" class="card-body gap-4">
                @csrf
                <label class="form-control">
                    <span class="label-text">Authentication code</span>
                    <input
                        type="text"
                        name="code"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        class="input input-bordered w-full"
                    />
                </label>
                <button type="submit" class="btn btn-primary w-full">Verify code</button>
            </form>
        </div>

        <div class="divider">Or</div>

        <div class="card border border-base-200 bg-base-100 shadow">
            <form method="POST" action="{{ route('two-factor.login.store') }}" class="card-body gap-4">
                @csrf
                <label class="form-control">
                    <span class="label-text">Recovery code</span>
                    <input
                        type="text"
                        name="recovery_code"
                        autocomplete="one-time-code"
                        class="input input-bordered w-full"
                    />
                </label>
                <button type="submit" class="btn btn-ghost w-full">Use recovery code</button>
            </form>
        </div>
    </div>
@endsection
