@use('App\Support\Format')
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


@endphp

<x-item-card title="Inventory">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Capacity" :value="$scuConverted">{{ Format::valueWithUnit($scuConverted, data_get($inventory, 'unit', 'SCU'), 1) }}</x-dt-dd>
            <x-dt-dd label="Dimensions" :value="$width ?? $height ?? $length">{{ Format::valueWithUnit($width, 'm', 1) }} × {{ Format::valueWithUnit($height, 'm', 1) }} × {{ Format::valueWithUnit($length, 'm', 1) }}</x-dt-dd>
            <x-dt-dd label="Type">{{ $isOpen ? 'Open' : ($isClosed ? 'Closed' : ($isExternal ? 'External' : '-')) }}</x-dt-dd>
        </x-slot:head>
    </x-dl-container>
</x-item-card>
