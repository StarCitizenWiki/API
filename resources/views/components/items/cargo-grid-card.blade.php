@use('App\Support\Format')
@use('App\Support\ScuBox')
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
    $maxSize = data_get($cargoGrid, 'max_size');
    $maxScuBox = $maxSize !== null ? ScuBox::largestThatFits($maxSize) : null;
@endphp

<x-item-card title="Cargo Grid">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Capacity" :value="$scuConverted">{{ Format::valueWithUnit($scuConverted, 'SCU', 1) }}</x-dt-dd>
            <x-dt-dd label="Max Box Size" :value="$maxScuBox">{{ Format::valueWithUnit($maxScuBox, 'SCU', 0) }}</x-dt-dd>
            <x-dt-dd label="Type">{{ $isOpen ? 'Open' : ($isClosed ? 'Closed' : ($isExternal ? 'External' : '-')) }}</x-dt-dd>
            <x-dt-dd label="Dimensions" :value="$width ?? $height ?? $length">{{ Format::valueWithUnit($width, 'm', 1) }} × {{ Format::valueWithUnit($height, 'm', 1) }} × {{ Format::valueWithUnit($length, 'm', 1) }}</x-dt-dd>
        </x-slot:head>
    </x-dl-container>
</x-item-card>
