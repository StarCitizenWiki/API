@use('App\Support\Format')
@props([
    'clothing',
    'temperatureResistance',
    'inventory',
    'gforceResistance',
])

@php
    $slot = data_get($clothing, 'slot');
    $temperatureResistance = $temperatureResistance ?? [];
    $gforceResistance = $gforceResistance ?? null;
    $scuConverted = data_get($inventory, 'scu_converted');
    $inventoryUnit = data_get($inventory, 'unit', 'SCU');

    $infoRows = array_values(array_filter([
        ['label' => 'Slot', 'value' => $slot],
        ['label' => 'Inventory', 'value' => Format::valueWithUnit($scuConverted, $inventoryUnit, 1)],
    ], static fn (array $row): bool => $row['value'] !== null));

    $gforceRows = $gforceResistance !== null
        ? [['label' => 'Modifier', 'value' => Format::valueWithUnit($gforceResistance * 100, '%', 1), 'class' => Format::colorClass($gforceResistance)]]
        : [];

    $tempRows = array_values(array_filter([
        data_get($temperatureResistance, 'minimum') !== null
            ? ['label' => 'Min', 'value' => Format::valueWithUnit(data_get($temperatureResistance, 'minimum'), '°C', 1)]
            : null,
        data_get($temperatureResistance, 'maximum') !== null
            ? ['label' => 'Max', 'value' => Format::valueWithUnit(data_get($temperatureResistance, 'maximum'), '°C', 1)]
            : null,
    ], static fn (?array $row): bool => $row !== null));

    $sections = array_values(array_filter([
        $infoRows !== [] ? ['title' => 'Info', 'rows' => $infoRows] : null,
        $gforceRows !== [] ? ['title' => 'G-Force Resistance', 'rows' => $gforceRows] : null,
        $tempRows !== [] ? ['title' => 'Temperature Resistance', 'rows' => $tempRows] : null,
    ], static fn (?array $s): bool => $s !== null));
@endphp

<x-data-card title="Clothing" :sections="$sections" {{ $attributes }} />
