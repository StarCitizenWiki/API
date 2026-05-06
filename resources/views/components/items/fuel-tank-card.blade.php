@use('App\Support\Format')
@props([
    'fuelTank',
])

@php
// TODO
    $fillRate = data_get($fuelTank, 'fill_rate');
    $drainRate = data_get($fuelTank, 'drain_rate');
    $capacity = data_get($fuelTank, 'capacity');

@endphp

<x-item-card title="Fuel Tank">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Fill Rate" :value="$fillRate">{{ Format::valueWithUnit($fillRate, '/s', 2) }}</x-dt-dd>
            <x-dt-dd label="Drain Rate" :value="$drainRate">{{ Format::valueWithUnit($drainRate, '/s', 2) }}</x-dt-dd>
            <x-dt-dd label="Capacity" :value="$capacity">{{ Format::valueWithUnit($capacity, 'SCU', 0) }}</x-dt-dd>
        </x-slot:head>
    </x-dl-container>
</x-item-card>
