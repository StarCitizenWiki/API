@extends('layouts.app')

@section('title', 'Developer Quickstart | Star Citizen Wiki API')
@section('meta_description', 'Learn the Star Citizen Wiki API in four steps: search, show, filter, and set up your project.')

@push('meta')
    <meta property="og:title" content="Developer Quickstart | Star Citizen Wiki API">
    <meta property="og:description" content="A four-step walkthrough for the Star Citizen Wiki API: search, show, filter, and set up your project.">
@endpush

@section('content')
    <div class="mx-auto flex max-w-7xl flex-col gap-6 py-2" data-testid="developer-page">
        @include('developers.partials.hero')
        @include('developers.partials.step-1-search')
        @include('developers.partials.step-2-show')
        @include('developers.partials.step-3-filters')
        @include('developers.partials.step-4-setup')
    </div>
@endsection
