@extends('layouts.app')

@section('title', '500 - System Malfunction')

@section('content')
    <x-error-page
        :code="500"
        icon="octagon-alert"
        title="System Malfunction"
        message="Something broke on our end. Please try again later."
    >
        <x-slot:actions>
            <a href="{{ route('home') }}" class="btn btn-ghost btn-sm">Home</a>
        </x-slot:actions>
    </x-error-page>
@endsection
