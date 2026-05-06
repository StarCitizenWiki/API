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
@endphp

<x-item-card title="Cargo Grid">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Capacity" :value="$scuConverted">{{ Format::valueWithUnit($scuConverted, 'SCU', 1) }}</x-dt-dd>
            <x-dt-dd label="Box Size" :value="$minScuBox ?? $maxScuBox">@if ($minScuBox !== null && $maxScuBox !== null && $minScuBox !== $maxScuBox) {{ Format::valueWithUnit($minScuBox, 'SCU', 0) }} – {{ Format::valueWithUnit($maxScuBox, 'SCU', 0) }} @else {{ Format::valueWithUnit($maxScuBox ?? $minScuBox, 'SCU', 0) }} @endif</x-dt-dd>
            <x-dt-dd label="Type">{{ $isOpen ? 'Open' : ($isClosed ? 'Closed' : ($isExternal ? 'External' : '-')) }}</x-dt-dd>
            <x-dt-dd label="Dimensions" :value="$width ?? $height ?? $length">{{ Format::valueWithUnit($width, 'm', 1) }} × {{ Format::valueWithUnit($height, 'm', 1) }} × {{ Format::valueWithUnit($length, 'm', 1) }}</x-dt-dd>
        </x-slot:head>
    </x-dl-container>
</x-item-card>
