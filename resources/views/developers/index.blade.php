@extends('layouts.app')

@section('title', 'Developer Quickstart | Star Citizen Wiki API')
@section('meta_description', 'Learn the Star Citizen Wiki API in four steps: search, show, filter, and set up your project.')

@push('meta')
    <meta property="og:title" content="Developer Quickstart | Star Citizen Wiki API">
    <meta property="og:description" content="A four-step walkthrough for the Star Citizen Wiki API: search, show, filter, and set up your project.">
@endpush

@section('content')
    <div class="mx-auto flex max-w-6xl flex-col gap-6 py-2" data-testid="developer-page">
        @include('developers.partials.hero')
        @include('developers.partials.step-1-search')
        @include('developers.partials.step-2-show')
        @include('developers.partials.step-3-filters')
        @include('developers.partials.step-4-setup')
    </div>

    <div class="mx-auto max-w-6xl pb-10">
        <div class="flex flex-col items-center gap-4 rounded-box border border-base-300 p-4 sm:flex-row sm:p-5">
            <img src="{{ asset('MadeByTheCommunity_White.png') }}" alt="Made by the Community" class="h-10 shrink-0" />
            <p class="text-xs text-center text-subtle sm:text-start">
                This is an unofficial Star Citizen fan site, not affiliated with the
                <a href="https://robertsspaceindustries.com" target="_blank" rel="noopener noreferrer" class="link-primary">Cloud Imperium</a>
                group of companies. All content on this site not authored by its host or users are property of their respective owners.
                Visit the <a href="https://robertsspaceindustries.com" target="_blank" rel="noopener noreferrer" class="link-primary">official Star Citizen website</a>.
            </p>
        </div>
    </div>
@endsection
