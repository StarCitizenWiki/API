@extends('layouts.app')

@section('title', '419 - Transmission Expired')

@section('content')
    <x-error-page
        :code="419"
        icon="lock"
        title="Transmission Expired"
        message="Your session has expired due to inactivity. Please refresh the page and try again."
        severity="warning"
    >
        <x-slot:actions>
            <button onclick="window.location.reload()" class="btn btn-ghost btn-sm">Retry</button>
            <a href="{{ route('home') }}" class="btn btn-ghost btn-sm">Home</a>
        </x-slot:actions>
    </x-error-page>
@endsection
