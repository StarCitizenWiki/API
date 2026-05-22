@use('App\Support\Format')
@props([
    'medicalBed',
])

@php
    $resourceContainer = data_get($medicalBed, 'resource_container', []);
    $inventoryContainer = data_get($medicalBed, 'inventory_container', []);
    $ports = data_get($medicalBed, 'ports', []);

    $resourceCapacity = data_get($resourceContainer, 'capacity');
    $resourceCapacitySCU = data_get($resourceCapacity, 'SCU');
    $isImmutable = data_get($resourceContainer, 'immutable');

    $inventoryCapacity = data_get($inventoryContainer, 'SCU');
    $inventoryUnit = data_get($inventoryContainer, 'UnitName', 'μSCU');
    $inventoryDimensions = data_get($inventoryContainer, ['x', 'y', 'z'], []);
    $inventoryWidth = data_get($inventoryDimensions, 'x');
    $inventoryHeight = data_get($inventoryDimensions, 'y');
    $inventoryDepth = data_get($inventoryDimensions, 'z');
    $isExternalContainer = data_get($inventoryContainer, 'IsExternalContainer');
    $isClosedContainer = data_get($inventoryContainer, 'IsClosedContainer');
    $isOpenContainer = data_get($inventoryContainer, 'IsOpenContainer');

    $sections = [];

    $infoRows = array_values(array_filter([
        $inventoryCapacity !== null
            ? ['label' => 'Inventory Capacity', 'value' => Format::valueWithUnit($inventoryCapacity, $inventoryUnit, 0)]
            : null,
        $inventoryWidth !== null
            ? ['label' => 'Dimensions', 'value' => Format::valueWithUnit($inventoryWidth, 'm', 2) . ' × ' . Format::valueWithUnit($inventoryHeight, 'm', 2) . ' × ' . Format::valueWithUnit($inventoryDepth, 'm', 2)]
            : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($infoRows !== []) {
        $sections[] = ['title' => 'Info', 'rows' => $infoRows];
    }

    $storageRows = array_values(array_filter([
        $resourceCapacitySCU !== null
            ? ['label' => 'Resource Capacity', 'value' => Format::valueWithUnit($resourceCapacitySCU, 'SCU', 2)]
            : null,
        $inventoryCapacity !== null
            ? ['label' => 'Inventory Capacity', 'value' => Format::valueWithUnit($inventoryCapacity, $inventoryUnit, 0)]
            : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($storageRows !== []) {
        $sections[] = ['title' => 'Storage', 'rows' => $storageRows];
    }

    $advancedRows = array_values(array_filter([
        $isImmutable !== null
            ? ['label' => 'Immutable', 'value' => $isImmutable ? 'Yes' : 'No']
            : null,
        $isExternalContainer !== null
            ? ['label' => 'External Container', 'value' => $isExternalContainer ? 'Yes' : 'No']
            : null,
        $isClosedContainer !== null
            ? ['label' => 'Closed Container', 'value' => $isClosedContainer ? 'Yes' : 'No']
            : null,
        $isOpenContainer !== null
            ? ['label' => 'Open Container', 'value' => $isOpenContainer ? 'Yes' : 'No']
            : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($advancedRows !== []) {
        $sections[] = ['title' => 'Advanced', 'rows' => $advancedRows];
    }
@endphp

<x-data-card title="Medical Bed" :sections="$sections" {{ $attributes }} />
