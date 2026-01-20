@props([
    'temperatureResistance',
])

@php
    $minimum = data_get($temperatureResistance, 'minimum');
    $maximum = data_get($temperatureResistance, 'maximum');
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="thermometer" class="size-4 text-primary" />
            <span>Temperature Resistance Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($minimum !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Minimum Temperature</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$minimum, 1) }}°C</dd>
                </div>
            @endif
            @if ($maximum !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum Temperature</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$maximum, 1) }}°C</dd>
                </div>
            @endif
        </dl>
    </div>
</div>
