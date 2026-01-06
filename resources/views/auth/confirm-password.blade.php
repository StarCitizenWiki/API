@extends('layouts.app')

@section('title', 'Confirm password')
@section('meta_description', 'Confirm your password to continue.')

@section('content')
    @php
        $versionCode = $selectedGameVersionCode ?? session('game_version_code') ?? request()->query('version');
        $versionQuery = $versionCode ? ['version' => $versionCode] : [];
    @endphp

    <div class="mx-auto flex w-full max-w-md flex-col gap-6">
        <div class="text-center">
            <h1 class="text-2xl font-semibold">Confirm your password</h1>
            <p class="text-sm text-base-content/70">This action requires a fresh confirmation.</p>
        </div>

        <div class="card border border-base-200 bg-base-100 shadow">
            <form method="POST" action="{{ route('password.confirm.store', $versionQuery) }}" class="card-body gap-4">
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
                        required
                        autocomplete="current-password"
                        class="input input-bordered w-full"
                    />
                </label>

                <button type="submit" class="btn btn-primary w-full">Confirm</button>
            </form>
        </div>
    </div>
@endsection
