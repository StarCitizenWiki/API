@use('App\Support\Format')
@props([
    'cargoGrid',
])

@php
    $scuConverted = data_get($cargoGrid, 'scu');
    $isOpen = data_get($cargoGrid, 'open');
    $isExternal = data_get($cargoGrid, 'external');
    $isClosed = data_get($cargoGrid, 'closed');
    $width = data_get($cargoGrid, 'width');
    $height = data_get($cargoGrid, 'height');
    $length = data_get($cargoGrid, 'length');
    $minScuBox = data_get($cargoGrid, 'min_scu_box');
    $maxScuBox = data_get($cargoGrid, 'max_scu_box');

    $boxSizeValue = ($minScuBox !== null && $maxScuBox !== null && $minScuBox !== $maxScuBox)
        ? Format::valueWithUnit($minScuBox, 'SCU', 0) . ' - ' . Format::valueWithUnit($maxScuBox, 'SCU', 0)
        : Format::valueWithUnit($maxScuBox ?? $minScuBox, 'SCU', 0);

    $typeValue = $isOpen ? 'Open' : ($isClosed ? 'Closed' : ($isExternal ? 'External' : '-'));

    $sections = [
        [
            'title' => 'Info',
            'rows' => array_values(array_filter([
                ['label' => 'Capacity', 'value' => Format::valueWithUnit($scuConverted, 'SCU', 1)],
                ['label' => 'Box Size', 'value' => $boxSizeValue],
                ['label' => 'Type', 'value' => $typeValue],
                ($width ?? $height ?? $length) !== null
                    ? ['label' => 'Dimensions', 'value' => Format::valueWithUnit($width, 'm', 1) . ' × ' . Format::valueWithUnit($height, 'm', 1) . ' × ' . Format::valueWithUnit($length, 'm', 1)]
                    : null,
            ], static fn (?array $row): bool => $row !== null)),
        ],
    ];
@endphp

<x-data-card title="Cargo Grid" :sections="$sections" {{ $attributes }} />
