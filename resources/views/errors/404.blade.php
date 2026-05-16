@extends('layouts.app')

@section('title', '404 - Signal Lost')

@section('content')
    <x-error-page
        :code="404"
        icon="search-x"
        title="Signal Lost"
        message="We couldn't locate the page you're looking for."
        severity="info"
    >
        <x-slot:actions>
            <button onclick="history.back()" class="btn btn-ghost btn-sm">Back</button>
            <a href="{{ route('home') }}" class="btn btn-ghost btn-sm">Home</a>
        </x-slot:actions>
    </x-error-page>
@endsection
