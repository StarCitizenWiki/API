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

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Fuel Tank</h2>

        <x-dl-section dlClass="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <x-dt-dd label="Fill Rate">{{ Format::valueWithUnit($fillRate, '/s', 2) }}</x-dt-dd>
            <x-dt-dd label="Drain Rate">{{ Format::valueWithUnit($drainRate, '/s', 2) }}</x-dt-dd>
            <x-dt-dd label="Capacity">{{ Format::valueWithUnit($capacity, 'SCU', 0) }}</x-dt-dd>
        </x-dl-section>
    </div>
</div>
