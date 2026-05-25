@extends('layouts.app')

@section('title', 'Route Planner | Star Citizen Wiki API')
@section('meta_description', 'Plan multi-system quantum travel routes. Calculate distance, time, fuel, and refuel requirements across Stanton, Pyro, and Nyx.')

@push('meta')
    <meta property="og:title" content="Route Planner | Star Citizen Wiki API">
    <meta property="og:description" content="Plan multi-system quantum travel routes. Calculate distance, time, fuel, and refuel requirements across Stanton, Pyro, and Nyx.">
@endpush

@section('content')
    <div class="mx-auto flex max-w-6xl flex-col gap-4 py-2">
        <section class="flex flex-col gap-2">
            <h1 class="text-2xl font-bold sm:text-3xl">Route Planner (WIP)</h1>
        </section>

        <x-route-planner />
    </div>
@endsection
