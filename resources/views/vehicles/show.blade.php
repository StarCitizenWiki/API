@php use Illuminate\Support\Arr; use Illuminate\Support\Str; @endphp
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

    if (is_array($shipMatrixDescription)) {
        $shipMatrixDescription = Arr::first($shipMatrixDescription, static fn (mixed $value): bool => is_string($value) && $value !== '');
    }

    if ($shipMatrixDescription === null || $shipMatrixDescription === '') {
        $shipMatrixDescription = data_get($vehicle, 'description');

        if (is_array($shipMatrixDescription)) {
            $shipMatrixDescription = Arr::first($shipMatrixDescription, static fn (mixed $value): bool => is_string($value) && $value !== '');
        }
    }
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
    $hasOwnershipData = $hasInsuranceData || $hasPurchaseData;
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
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
            <x-vehicles.vehicle-breadcrumbs :vehicle="$vehicle" :manufacturerCode="$manufacturerCode" />
        </div>

        <x-resource-search
            title="Search vehicles"
            description="Find vehicles by name across the universe database."
            :route="route('web.vehicles.index')"
            placeholder="Search Vehicles"
            variant="minimal"
        />

        <div class="mx-auto grid w-full gap-4 xl:grid-cols-12 xl:items-stretch">
            <x-vehicles.hero :vehicle="$vehicle" class="xl:col-span-6" />
            <x-vehicles.quick-facts-card :vehicle="$vehicle" class="xl:col-span-6" />
        </div>

        <div class="flex flex-col gap-8">
            <section class="space-y-4">
                <h2 class="text-lg font-semibold tracking-tight">Flight & Mobility</h2>

                <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                    <x-vehicles.flight-characteristics-card :vehicle="$vehicle" />
                    <x-vehicles.propulsion-card :vehicle="$vehicle" />
                </div>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold tracking-tight">Combat & Systems</h2>

                <div class="flex flex-col gap-4">
                    <x-vehicles.hardpoints-components-card :vehicle="$vehicle" />

                    <div class="grid grid-cols-1 gap-4 2xl:grid-cols-2">
                        <x-vehicles.systems-signatures-card :vehicle="$vehicle" />
                        <x-vehicles.systems-breakdown-card :vehicle="$vehicle" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                        <x-vehicles.parts-turrets-card :vehicle="$vehicle" section="parts" />
                        <x-vehicles.parts-turrets-card :vehicle="$vehicle" section="turrets" />
                    </div>
                </div>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold tracking-tight">Dimensions & Cargo</h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-vehicles.dimensions-mass-card :vehicle="$vehicle" />
                    <x-vehicles.cargo-inventory-card :vehicle="$vehicle" />
                </div>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold tracking-tight">Purchase & Insurance</h2>

                @if ($hasOwnershipData)
                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                        <x-vehicles.purchase-variants-card :vehicle="$vehicle" />
                    </div>
                @endif
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold tracking-tight">Technical</h2>

                <div class="grid grid-cols-1 gap-4">
                    <x-vehicles.metadata-footer-card :vehicle="$vehicle" />
                </div>
            </section>
        </div>
    </div>
@endsection
