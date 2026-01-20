@props([
    'radiationResistance',
])

@php
    $maximumRadiationCapacity = data_get($radiationResistance, 'maximum_radiation_capacity');
    $radiationDissipationRate = data_get($radiationResistance, 'radiation_dissipation_rate');
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="thermometer" class="size-4 text-primary" />
            <span>Radiation Resistance Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($maximumRadiationCapacity !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum Radiation Capacity</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$maximumRadiationCapacity, 2) }} REM</dd>
                </div>
            @endif
            @if ($radiationDissipationRate !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Radiation Dissipation Rate</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$radiationDissipationRate, 2) }} REM/s</dd>
                </div>
            @endif
        </dl>
    </div>
</div>
