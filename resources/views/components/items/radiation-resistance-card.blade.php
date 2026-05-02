@use('App\Support\Format')
@props([
    'radiationResistance',
])

@php
    $maximumRadiationCapacity = data_get($radiationResistance, 'maximum_radiation_capacity');
    $radiationDissipationRate = data_get($radiationResistance, 'radiation_dissipation_rate');
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Radiation Resistance</h2>

        <x-dl-section dlClass="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @if ($maximumRadiationCapacity !== null)
                <x-dt-dd label="Maximum Radiation Capacity">{{ Format::valueWithUnit($maximumRadiationCapacity, 'REM', 0) }}</x-dt-dd>
            @endif
            @if ($radiationDissipationRate !== null)
                <x-dt-dd label="Radiation Dissipation Rate">{{ Format::valueWithUnit($radiationDissipationRate, 'REM/s', 0) }}</x-dt-dd>
            @endif
        </x-dl-section>
    </div>
</div>
