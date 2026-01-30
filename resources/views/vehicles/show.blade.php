@php use Illuminate\Support\Str; @endphp
@extends('layouts.app')

@php
    $pageTitleDecoded = html_entity_decode($pageTitle);

    $vehicleName = data_get($vehicle, 'name', 'Vehicle');
    $manufacturerName = data_get($vehicle, 'manufacturer.name');
    $manufacturerCode = data_get($vehicle, 'manufacturer.code');
    $sizeClass = data_get($vehicle, 'size_class');
    $career = data_get($vehicle, 'career');
    $role = data_get($vehicle, 'role');
    $className = data_get($vehicle, 'class_name');
    $shipMatrixDescription = data_get($vehicle, 'description.en');
    $classification = data_get($vehicle, 'classification');

    $isSpaceship = data_get($vehicle, 'is_spaceship');
    $quantum = data_get($vehicle, 'quantum', []);
    $hasQuantumDrive = data_get($quantum, 'quantum_speed') !== null;

    $insurance = data_get($vehicle, 'insurance', []);
    $hasInsuranceData = $insurance !== [];

    $shipMatrixName = data_get($vehicle, 'shipmatrix_name');
    $shipMatrixMsrp = data_get($vehicle, 'msrp');
    $shipMatrixPledgeUrl = data_get($vehicle, 'pledge_url');
    $shipMatrixLoaner = data_get($vehicle, 'loaner');
    $shipMatrixFoci = data_get($vehicle, 'foci', []);
    $shipMatrixSkus = data_get($vehicle, 'skus', []);
    $hasPurchaseData = $shipMatrixName || $shipMatrixMsrp || $shipMatrixPledgeUrl || $shipMatrixLoaner || $shipMatrixFoci || $shipMatrixSkus;
@endphp

@section('title')
    {!! $pageTitleDecoded !!} - {{ $manufacturerName }} - Star Citizen Vehicle
@endsection
@section('meta_description')
    {!! Str::limit($shipMatrixDescription ?? $vehicleName, 160) !!}
@endsection

@section('meta')
    <meta name="keywords" content="{{ $vehicleName }},{{ $manufacturerName ?? '' }},{{ $sizeClass ? "Size {$sizeClass}" : '' }},{{ $career ?? '' }},{{ $role ?? '' }},Star Citizen,SC">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $vehicleName }} - {{ $manufacturerName ?? '' }} {{ $className ?? '' }}">
    <meta property="og:description" content="{!! Str::limit($shipMatrixDescription ?? $vehicleName, 160) !!}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $vehicleName }} - {{ $manufacturerName ?? '' }}">
    <meta name="twitter:description" content="{!! Str::limit($shipMatrixDescription ?? $vehicleName, 160) !!}">
@endsection


@section('content')
    <div class="flex flex-col gap-3">
        <div class="flex flex-col gap-2">
            <x-vehicles.vehicle-breadcrumbs :vehicle="$vehicle" :manufacturerCode="$manufacturerCode" />
            <h1 class="text-2xl font-semibold tracking-tight">
                {{ $vehicleName }}
                @if ($className)
                    <span class="text-secondary">({{ $className }})</span>
                @endif
            </h1>
        </div>

        <x-resource-search
            title="Search vehicles"
            description="Find vehicles by name across the universe database."
            :route="route('web.vehicles.index')"
            placeholder="Search Vehicles"
        />

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <x-vehicles.quick-summary-card :vehicle="$vehicle" class="col-span-1 xl:col-span-2" />
            <x-vehicles.flight-characteristics-card :vehicle="$vehicle" />
            <x-vehicles.hardpoints-components-card :vehicle="$vehicle" />
            <x-vehicles.systems-signatures-card :vehicle="$vehicle" class="col-span-full" />

            <x-vehicles.parts-turrets-card :vehicle="$vehicle" />

            <x-vehicles.dimensions-mass-card :vehicle="$vehicle" />
            <x-vehicles.propulsion-card :vehicle="$vehicle" />
            <x-vehicles.cargo-inventory-card :vehicle="$vehicle" />
            <x-vehicles.insurance-logistics-card :vehicle="$vehicle" />

            @if($hasPurchaseData)
                <x-vehicles.purchase-variants-card :vehicle="$vehicle" class="md:col-span-2 xl:col-span-1" />
            @endif

            @if ($shipMatrixDescription)
                <details class="collapse collapse-arrow border border-base-300 bg-base-100 shadow col-span-full">
                    <summary class="collapse-title min-h-11 py-3 font-semibold">Description</summary>
                    <div class="collapse-content">
                        <div class="text-sm text-base-content/80 leading-relaxed">
                            {!! nl2br(e($shipMatrixDescription)) !!}
                        </div>
                    </div>
                </details>
            @endif

            <x-vehicles.metadata-footer-card :vehicle="$vehicle" class="mt-6 col-span-full" />
        </div>
    </div>
@endsection
