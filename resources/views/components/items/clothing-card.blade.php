@use('App\Support\Format')
@props([
    'clothing',
    'temperatureResistance',
    'inventory',
])

@php
    $slot = data_get($clothing, 'slot');
    $temperatureResistance = $temperatureResistance ?? [];
    $scuConverted = data_get($inventory, 'scu_converted');
    $inventoryUnit = data_get($inventory, 'unit', 'SCU');

@endphp

<x-item-card title="Clothing">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Slot" :value="$slot">{{ $slot }}</x-dt-dd>
            <x-dt-dd label="Inventory" :value="$scuConverted">{{ Format::valueWithUnit($scuConverted, $inventoryUnit, 1) }}</x-dt-dd>
        </x-slot:head>

        <x-dl-section title="Temperature Resistance">
            <x-dt-dd label="Min" :value="data_get($temperatureResistance, 'minimum')">{{ Format::valueWithUnit(data_get($temperatureResistance, 'minimum'), '°C', 1) }}</x-dt-dd>
            <x-dt-dd label="Max" :value="data_get($temperatureResistance, 'maximum')">{{ Format::valueWithUnit(data_get($temperatureResistance, 'maximum'), '°C', 1) }}</x-dt-dd>
        </x-dl-section>
    </x-dl-container>
</x-item-card>
