@props([
    'temperatureResistance',
])

@php
    $minimum = data_get($temperatureResistance, 'minimum');
    $maximum = data_get($temperatureResistance, 'maximum');
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Temperature Resistance</h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Min</dt>
                <dd class="text-sm font-semibold text-base-content">
                    {{ fmt_value_with_unit($minimum, '°C', 1) }}
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Max</dt>
                <dd class="text-sm font-semibold text-base-content">
                    {{ fmt_value_with_unit($maximum, '°C', 1) }}
                </dd>
            </div>
        </dl>
    </div>
</div>
