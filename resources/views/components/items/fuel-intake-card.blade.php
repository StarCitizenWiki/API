@use('App\Support\Format')
@props([
    'fuelIntake',
])

@php
    $fuelPushRate = data_get($fuelIntake, 'fuel_push_rate');
    $minimumRate = data_get($fuelIntake, 'minimum_rate');
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Fuel Intake</h2>

        <x-dl-section dlClass="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <x-dt-dd label="Fuel Push Rate">{{ Format::valueWithUnit($fuelPushRate, '/s', 2) }}</x-dt-dd>
            <x-dt-dd label="Minimum Rate">{{ Format::valueWithUnit($minimumRate, '/s', 2) }}</x-dt-dd>
        </x-dl-section>
    </div>
</div>
