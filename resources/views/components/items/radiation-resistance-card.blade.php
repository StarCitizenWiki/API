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

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @if ($maximumRadiationCapacity !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Maximum Radiation Capacity</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($maximumRadiationCapacity, 'REM', 0) }}</dd>
                </div>
            @endif
            @if ($radiationDissipationRate !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Radiation Dissipation Rate</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($radiationDissipationRate, 'REM/s', 0) }}</dd>
                </div>
            @endif
        </dl>
    </div>
</div>
