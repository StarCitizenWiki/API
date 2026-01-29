@props([
    'temperatureResistance',
])

@php
    $minimum = data_get($temperatureResistance, 'minimum');
    $maximum = data_get($temperatureResistance, 'maximum');
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-3">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="thermometer" class="size-4 text-primary" />
            <span>Temperature Resistance</span>
        </h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Min</dt>
                <dd class="text-sm font-medium">
                    {{ fmt_value_with_unit($minimum, '°C', 1) }}
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max</dt>
                <dd class="text-sm font-medium">
                    {{ fmt_value_with_unit($maximum, '°C', 1) }}
                </dd>
            </div>
        </dl>
    </div>
</div>
