@use('App\Support\Format')
@props([
    'fuelIntake',
])

@php
    $fuelPushRate = data_get($fuelIntake, 'fuel_push_rate');
    $minimumRate = data_get($fuelIntake, 'minimum_rate');

@endphp

<x-item-card title="Fuel Intake">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Fuel Push Rate" :value="$fuelPushRate">{{ Format::valueWithUnit($fuelPushRate, '/s', 2) }}</x-dt-dd>
            <x-dt-dd label="Minimum Rate" :value="$minimumRate">{{ Format::valueWithUnit($minimumRate, '/s', 2) }}</x-dt-dd>
        </x-slot:head>
    </x-dl-container>
</x-item-card>
