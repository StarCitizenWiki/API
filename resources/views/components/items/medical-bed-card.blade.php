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
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="activity" class="size-4 text-primary" />
            <span>Medical Bed</span>
        </h2>

        {{-- Primary Data (Always Visible) --}}
        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @if ($inventoryCapacity !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Inventory Capacity</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($inventoryCapacity, $inventoryUnit, 0) }}</dd>
                </div>
            @endif
            @if ($inventoryWidth !== null && $inventoryHeight !== null && $inventoryDepth !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Dimensions</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($inventoryWidth, 'm', 2) }} × {{ fmt_value_with_unit($inventoryHeight, 'm', 2) }} × {{ fmt_value_with_unit($inventoryDepth, 'm', 2) }}</dd>
                </div>
            @endif
        </dl>

        {{-- Secondary Data (Collapsible, default open) --}}
        @if ($showSecondary)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3">
                    <h3 class="text-sm font-semibold">Storage</h3>
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                        @if ($resourceCapacitySCU !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Resource Capacity</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($resourceCapacitySCU, 'SCU', 2) }}</dd>
                            </div>
                        @endif
                        @if ($inventoryCapacity !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Inventory Capacity</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($inventoryCapacity, $inventoryUnit, 0) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        {{-- Tertiary Data (Collapsible, default closed) --}}
        @if ($showTertiary)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3">
                    <h3 class="text-sm font-semibold">Advanced</h3>
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2">
                        @if ($isImmutable !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Immutable</dt>
                                <dd class="text-sm font-medium">{{ $isImmutable ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($isExternalContainer !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">External Container</dt>
                                <dd class="text-sm font-medium">{{ $isExternalContainer ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($isClosedContainer !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Closed Container</dt>
                                <dd class="text-sm font-medium">{{ $isClosedContainer ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($isOpenContainer !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Open Container</dt>
                                <dd class="text-sm font-medium">{{ $isOpenContainer ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if (is_array($ports) && !empty($ports))
                            <div class="space-y-1 sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ports</dt>
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
                </div>
            </details>
        @endif
    </div>
</div>
