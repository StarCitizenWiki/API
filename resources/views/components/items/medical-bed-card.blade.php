@use('App\Support\Format')
@props([
    'medicalBed',
])

@php
    $resourceContainer = data_get($medicalBed, 'resource_container', []);
    $inventoryContainer = data_get($medicalBed, 'inventory_container', []);
    $ports = data_get($medicalBed, 'ports', []);

    // Resource Container Data
    $resourceCapacity = data_get($resourceContainer, 'capacity');
    $resourceCapacitySCU = data_get($resourceCapacity, 'SCU');
    $isImmutable = data_get($resourceContainer, 'immutable');

    // Inventory Container Data
    $inventoryCapacity = data_get($inventoryContainer, 'SCU');
    $inventoryUnit = data_get($inventoryContainer, 'UnitName', 'μSCU');
    $inventoryDimensions = data_get($inventoryContainer, ['x', 'y', 'z'], []);
    $inventoryWidth = data_get($inventoryDimensions, 'x');
    $inventoryHeight = data_get($inventoryDimensions, 'y');
    $inventoryDepth = data_get($inventoryDimensions, 'z');
    $isExternalContainer = data_get($inventoryContainer, 'IsExternalContainer');
    $isClosedContainer = data_get($inventoryContainer, 'IsClosedContainer');
    $isOpenContainer = data_get($inventoryContainer, 'IsOpenContainer');

    // Count secondary fields
    $secondaryFieldCount = 0;
    if ($resourceCapacitySCU !== null) {
        $secondaryFieldCount++;
    }
    if ($inventoryCapacity !== null) {
        $secondaryFieldCount++;
    }
    if ($inventoryWidth !== null || $inventoryHeight !== null || $inventoryDepth !== null) {
        $secondaryFieldCount++;
    }
    $showSecondary = $secondaryFieldCount > 0;

    // Count tertiary fields
    $tertiaryFieldCount = 0;
    if ($isImmutable !== null) {
        $tertiaryFieldCount++;
    }
    if ($isExternalContainer !== null) {
        $tertiaryFieldCount++;
    }
    if ($isClosedContainer !== null) {
        $tertiaryFieldCount++;
    }
    if ($isOpenContainer !== null) {
        $tertiaryFieldCount++;
    }
    if (is_array($ports) && !empty($ports)) {
        $tertiaryFieldCount++;
    }
    $showTertiary = $tertiaryFieldCount >= 2;
@endphp

<div {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Medical Bed</h2>

        {{-- Primary Data (Always Visible) --}}
        <x-dl-section dlClass="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @if ($inventoryCapacity !== null)
                <x-dt-dd label="Inventory Capacity">{{ Format::valueWithUnit($inventoryCapacity, $inventoryUnit, 0) }}</x-dt-dd>
            @endif
            @if ($inventoryWidth !== null && $inventoryHeight !== null && $inventoryDepth !== null)
                <x-dt-dd label="Dimensions">{{ Format::valueWithUnit($inventoryWidth, 'm', 2) }} × {{ Format::valueWithUnit($inventoryHeight, 'm', 2) }} × {{ Format::valueWithUnit($inventoryDepth, 'm', 2) }}</x-dt-dd>
            @endif
        </x-dl-section>

        {{-- Secondary Data (Collapsible, default open) --}}
        @if ($showSecondary)
            <x-dl-details title="Storage" :open="true">
                @if ($resourceCapacitySCU !== null)
                    <x-dt-dd label="Resource Capacity">{{ Format::valueWithUnit($resourceCapacitySCU, 'SCU', 2) }}</x-dt-dd>
                @endif
                @if ($inventoryCapacity !== null)
                    <x-dt-dd label="Inventory Capacity">{{ Format::valueWithUnit($inventoryCapacity, $inventoryUnit, 0) }}</x-dt-dd>
                @endif
            </x-dl-details>
        @endif

        {{-- Tertiary Data (Collapsible, default closed) --}}
        @if ($showTertiary)
            <x-dl-details title="Advanced">
                @if ($isImmutable !== null)
                    <x-dt-dd label="Immutable">{{ $isImmutable ? 'Yes' : 'No' }}</x-dt-dd>
                @endif
                @if ($isExternalContainer !== null)
                    <x-dt-dd label="External Container">{{ $isExternalContainer ? 'Yes' : 'No' }}</x-dt-dd>
                @endif
                @if ($isClosedContainer !== null)
                    <x-dt-dd label="Closed Container">{{ $isClosedContainer ? 'Yes' : 'No' }}</x-dt-dd>
                @endif
                @if ($isOpenContainer !== null)
                    <x-dt-dd label="Open Container">{{ $isOpenContainer ? 'Yes' : 'No' }}</x-dt-dd>
                @endif
                @if (is_array($ports) && !empty($ports))
                    <div class="sm:col-span-2">
                        <x-dt-dd label="Ports">
                        <div class="flex flex-wrap gap-2">
                            @foreach ($ports as $port)
                                <span class="badge badge-outline">{{ data_get($port, 'name', 'Unknown') }}</span>
                            @endforeach
                        </div>
                    </x-dt-dd>
                    </div>
                @endif
            </x-dl-details>
        @endif
    </div>
</div>
