@extends('layouts.app')

@section('title', 'Stats')
@section('meta_description', 'Latest fund and fleet stats.')

@section('content')
    @php
        $funds = $latestStats['funds'] ?? null;
        $fleet = $latestStats['fleet'] ?? null;
        $timestamp = $latestStats['timestamp'] ?? null;

        $fundsValue = $funds !== null ? number_format((float) $funds, 2, '.', ',') : '—';
        $fleetValue = $fleet !== null ? number_format((float) $fleet) : '—';
        $updatedLabel = $timestamp
            ? \Illuminate\Support\Carbon::parse($timestamp)->toDayDateTimeString()
            : '—';
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-semibold tracking-tight">Stats</h1>
            <p class="text-sm text-base-content/70">Latest fund and fleet snapshot.</p>
        </div>

        <div class="stats stats-vertical bg-base-100 shadow sm:stats-horizontal" data-testid="stats-summary">
            <div class="stat" data-testid="stats-funds-card">
                <div class="stat-title" data-testid="stats-funds-title">Funds</div>
                <div class="stat-value" data-testid="stats-funds-value">${{ $fundsValue }}</div>
                <div class="stat-desc" data-testid="stats-funds-updated">Updated {{ $updatedLabel }}</div>
            </div>

            <div class="stat" data-testid="stats-fleet-card">
                <div class="stat-title" data-testid="stats-fleet-title">Fleet</div>
                <div class="stat-value" data-testid="stats-fleet-value">{{ $fleetValue }}</div>
                <div class="stat-desc" data-testid="stats-fleet-updated">Updated {{ $updatedLabel }}</div>
            </div>
        </div>
    </div>
@endsection
