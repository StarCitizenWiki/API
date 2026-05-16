@extends('layouts.app')

@section('title', '429 - Comms Overloaded')

@section('content')
    <x-error-page
        :code="429"
        icon="wifi"
        title="Comms Overloaded"
        message="You're sending requests faster than our systems can handle. Please wait a moment and try again."
        severity="warning"
    >
        <x-slot:actions>
            <button onclick="window.location.reload()" class="btn btn-ghost btn-sm">Retry</button>
            <a href="{{ route('home') }}" class="btn btn-ghost btn-sm">Home</a>
        </x-slot:actions>
    </x-error-page>
@endsection
