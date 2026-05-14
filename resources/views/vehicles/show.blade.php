@php use Illuminate\Support\Str; @endphp
@extends('layouts.app')

@php
    $vehicleName = data_get($vehicle, 'name', 'Vehicle');
    $manufacturerName = data_get($vehicle, 'manufacturer.name');
    $manufacturerCode = data_get($vehicle, 'manufacturer.code');
    $breadcrumbs = data_get($seo, 'breadcrumbs', []);

    $isSpaceship = data_get($vehicle, 'is_spaceship');
    $quantum = data_get($vehicle, 'quantum', []);
    $hasQuantumDrive = data_get($quantum, 'quantum_speed') !== null;

    $drive = data_get($vehicle, 'drive');
    $hasDriveData = $drive !== null && $drive !== [];


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

    $uexPurchasePrices = data_get($vehicle, 'uex_prices.purchase', []);
    $uexRentalPrices = data_get($vehicle, 'uex_prices.rental', []);
    $hasUexPrices = (is_array($uexPurchasePrices) && $uexPurchasePrices !== []) || (is_array($uexRentalPrices) && $uexRentalPrices !== []);

    $technicalEntries = array_values(array_filter([
        data_get($vehicle, 'classification') ? ['label' => 'Classification', 'value' => data_get($vehicle, 'classification'), 'url' => null] : null,
        data_get($vehicle, 'class_name') ? ['label' => 'Class Name', 'value' => data_get($vehicle, 'class_name'), 'url' => null] : null,
    ]));
@endphp
@section('title')
    {!! data_get($seo, 'title', $vehicleName.' - Star Citizen Vehicle') !!}
@endsection
@section('meta_description')
    {!! data_get($seo, 'metaDescription', Str::limit($vehicleName, 160)) !!}
@endsection

@section('meta')
    <x-seo.metadata
        :canonical="data_get($seo, 'canonicalUrl')"
        :keywords="data_get($seo, 'keywords', [])"
        :og-title="data_get($seo, 'ogTitle')"
        :og-description="data_get($seo, 'ogDescription')"
        :twitter-title="data_get($seo, 'twitterTitle')"
        :twitter-description="data_get($seo, 'twitterDescription')"
        :structured-data="data_get($seo, 'structuredData', [])"
    />
@endsection


@section('content')
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
            <x-vehicles.vehicle-breadcrumbs :vehicle="$vehicle" :manufacturerCode="$manufacturerCode" :breadcrumbs="$breadcrumbs" />
        </div>

        <x-resource-search
            title="Search vehicles"
            description="Find vehicles by name across the universe database."
            :route="route('web.vehicles.index')"
            placeholder="Search vehicle names"
            variant="minimal"
            apiEndpoint="/api/vehicles"
        />

        <div class="mx-auto grid w-full gap-4 xl:grid-cols-12">
            <x-vehicles.hero :vehicle="$vehicle" :translations="data_get($vehicle, 'description')" class="xl:col-span-6" />
            <x-vehicles.quick-facts-card :vehicle="$vehicle" class="xl:col-span-6" />
        </div>

        <div class="flex flex-col gap-8">
            <section class="space-y-4">
                <h2 class="text-lg font-semibold tracking-tight">Combat & Systems</h2>
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 ">
                    <x-vehicles.weaponry-card :vehicle="$vehicle" />
                    <x-vehicles.systems-signatures-card :vehicle="$vehicle" />

                    <x-vehicles.armor-card :vehicle="$vehicle" />
                    <x-vehicles.shield-card :vehicle="$vehicle" />
                </div>
                <x-vehicles.hardpoints-components-card :vehicle="$vehicle" />


                <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                    <x-vehicles.systems-breakdown-card :vehicle="$vehicle" />
                    <x-vehicles.relay-network-card :vehicle="$vehicle" />

                    <x-vehicles.parts-turrets-card :vehicle="$vehicle" section="parts" />
                    <x-vehicles.parts-turrets-card :vehicle="$vehicle" section="turrets" />

                    <x-vehicles.crew-medical-card :vehicle="$vehicle" />
                </div>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold tracking-tight">Flight & Mobility</h2>

                <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                    <x-vehicles.flight-characteristics-card :vehicle="$vehicle" />
                    @if ($isSpaceship || $hasQuantumDrive)
                        <x-vehicles.propulsion-card :vehicle="$vehicle" />
                    @endif
                    @if ($hasDriveData)
                        <x-vehicles.drive-characteristics-card :vehicle="$vehicle" />
                    @endif

                    <x-vehicles.thruster-summary-card :vehicle="$vehicle" />
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
                        <x-vehicles.uex-prices-card :purchasePrices="$uexPurchasePrices" :rentalPrices="$uexRentalPrices" />
                    </div>
                @elseif ($hasUexPrices)
                    <x-vehicles.uex-prices-card :purchasePrices="$uexPurchasePrices" :rentalPrices="$uexRentalPrices" />
                @endif
            </section>

            <x-technical-section :entries="$technicalEntries" />
        </div>
    </div>
@endsection
