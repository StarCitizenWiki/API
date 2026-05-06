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

@endphp

<x-item-card title="Medical Bed">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Inventory Capacity" :value="$inventoryCapacity">{{ Format::valueWithUnit($inventoryCapacity, $inventoryUnit, 0) }}</x-dt-dd>
            <x-dt-dd label="Dimensions" :value="$inventoryWidth">
                {{ Format::valueWithUnit($inventoryWidth, 'm', 2) }} × {{ Format::valueWithUnit($inventoryHeight, 'm', 2) }} × {{ Format::valueWithUnit($inventoryDepth, 'm', 2) }}
            </x-dt-dd>
        </x-slot:head>

        <x-dl-details title="Storage" :open="true">
            <x-dt-dd label="Resource Capacity" :value="$resourceCapacitySCU">{{ Format::valueWithUnit($resourceCapacitySCU, 'SCU', 2) }}</x-dt-dd>
            <x-dt-dd label="Inventory Capacity" :value="$inventoryCapacity">{{ Format::valueWithUnit($inventoryCapacity, $inventoryUnit, 0) }}</x-dt-dd>
        </x-dl-details>

        <x-dl-details title="Advanced">
            <x-dt-dd label="Immutable" :value="$isImmutable">{{ $isImmutable ? 'Yes' : 'No' }}</x-dt-dd>
            <x-dt-dd label="External Container" :value="$isExternalContainer">{{ $isExternalContainer ? 'Yes' : 'No' }}</x-dt-dd>
            <x-dt-dd label="Closed Container" :value="$isClosedContainer">{{ $isClosedContainer ? 'Yes' : 'No' }}</x-dt-dd>
            <x-dt-dd label="Open Container" :value="$isOpenContainer">{{ $isOpenContainer ? 'Yes' : 'No' }}</x-dt-dd>
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
    </x-dl-container>
</x-item-card>
