@extends('layouts.app')

@section('title', '503 - Systems Offline')

@section('content')
    <x-error-page
        :code="503"
        icon="server"
        title="Systems Offline"
        message="We're currently performing scheduled maintenance. Check back shortly - we'll be back online soon."
    >
        <x-slot:actions>
            <button onclick="window.location.reload()" class="btn btn-ghost btn-sm">Retry</button>
            <a href="{{ route('home') }}" class="btn btn-ghost btn-sm">Home</a>
        </x-slot:actions>
    </x-error-page>
@endsection
