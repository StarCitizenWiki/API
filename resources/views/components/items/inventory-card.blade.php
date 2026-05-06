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
@endphp

<x-item-card title="Inventory">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Capacity" :value="$scuConverted">{{ Format::valueWithUnit($scuConverted, data_get($inventory, 'unit', 'SCU'), 1) }}</x-dt-dd>
            <x-dt-dd label="Max Box Size" :value="$maxScuBox">{{ Format::valueWithUnit($maxScuBox, 'SCU', 0) }}</x-dt-dd>
            <x-dt-dd label="Type">{{ $isOpen ? 'Open' : ($isClosed ? 'Closed' : ($isExternal ? 'External' : '-')) }}</x-dt-dd>
            <x-dt-dd label="Dimensions" :value="$width ?? $height ?? $length">{{ Format::valueWithUnit($width, 'm', 1) }} × {{ Format::valueWithUnit($height, 'm', 1) }} × {{ Format::valueWithUnit($length, 'm', 1) }}</x-dt-dd>
        </x-slot:head>
    </x-dl-container>
</x-item-card>
