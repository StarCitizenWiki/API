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

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Medical Bed</h2>

        {{-- Primary Data (Always Visible) --}}
        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @if ($inventoryCapacity !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Inventory Capacity</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($inventoryCapacity, $inventoryUnit, 0) }}</dd>
                </div>
            @endif
            @if ($inventoryWidth !== null && $inventoryHeight !== null && $inventoryDepth !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Dimensions</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($inventoryWidth, 'm', 2) }} × {{ fmt_value_with_unit($inventoryHeight, 'm', 2) }} × {{ fmt_value_with_unit($inventoryDepth, 'm', 2) }}</dd>
                </div>
            @endif
        </dl>

        {{-- Secondary Data (Collapsible, default open) --}}
        @if ($showSecondary)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Storage
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($resourceCapacitySCU !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Resource Capacity</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($resourceCapacitySCU, 'SCU', 2) }}</dd>
                            </div>
                        @endif
                        @if ($inventoryCapacity !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Inventory Capacity</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($inventoryCapacity, $inventoryUnit, 0) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        {{-- Tertiary Data (Collapsible, default closed) --}}
        @if ($showTertiary)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Advanced
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($isImmutable !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Immutable</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ $isImmutable ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($isExternalContainer !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">External Container</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ $isExternalContainer ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($isClosedContainer !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Closed Container</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ $isClosedContainer ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($isOpenContainer !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Open Container</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ $isOpenContainer ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if (is_array($ports) && !empty($ports))
                            <div class="space-y-1 sm:col-span-2">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Ports</dt>
                                <dd class="text-sm">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($ports as $port)
                                            <span class="badge badge-outline">{{ data_get($port, 'name', 'Unknown') }}</span>
                                        @endforeach
                                    </div>
                                </dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif
    </div>
</div>
