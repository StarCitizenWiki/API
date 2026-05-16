@extends('layouts.app')

@section('title', '403 - Access Restricted')

@section('content')
    <x-error-page
        :code="403"
        icon="shield"
        title="Access Restricted"
        message="You don't have the required clearance to access this resource."
        severity="warning"
    >
        <x-slot:actions>
            <button onclick="history.back()" class="btn btn-ghost btn-sm">Back</button>
            <a href="{{ route('home') }}" class="btn btn-ghost btn-sm">Home</a>
        </x-slot:actions>
    </x-error-page>
@endsection
