@use('App\Support\Format')
@use('App\Support\ScuBox')
@props([
    'inventory',
])

@php
    $scuConverted = data_get($inventory, 'scu_converted');
    $isOpen = data_get($inventory, 'open');
    $isExternal = data_get($inventory, 'external');
    $isClosed = data_get($inventory, 'closed');
    $width = data_get($inventory, 'width');
    $height = data_get($inventory, 'height');
    $length = data_get($inventory, 'length');
    $maxSize = data_get($inventory, 'max_size');
    $maxScuBox = $maxSize !== null ? ScuBox::largestThatFits($maxSize) : null;

    $type = $isOpen ? 'Open' : ($isClosed ? 'Closed' : ($isExternal ? 'External' : '-'));

    $rows = array_values(array_filter([
        $scuConverted !== null ? ['label' => 'Capacity', 'value' => Format::valueWithUnit($scuConverted, data_get($inventory, 'unit', 'SCU'), 1)] : null,
        $maxScuBox !== null ? ['label' => 'Max Box Size', 'value' => Format::valueWithUnit($maxScuBox, 'SCU', 0)] : null,
        ['label' => 'Type', 'value' => $type],
        ($width !== null || $height !== null || $length !== null) ? ['label' => 'Dimensions', 'value' => Format::valueWithUnit($width, 'm', 1) . ' × ' . Format::valueWithUnit($height, 'm', 1) . ' × ' . Format::valueWithUnit($length, 'm', 1)] : null,
    ], static fn (?array $row): bool => $row !== null));

    $sections = $rows !== [] ? [['title' => 'Info', 'rows' => $rows]] : [];
@endphp

<x-data-card title="Inventory" :sections="$sections" />
