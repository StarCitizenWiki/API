@props([
    'fuelIntake',
])

@php
    $fuelPushRate = data_get($fuelIntake, 'fuel_push_rate');
    $minimumRate = data_get($fuelIntake, 'minimum_rate');
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="wind" class="size-4 text-primary" />
            <span>Fuel Intake Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($fuelPushRate !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fuel Push Rate</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$fuelPushRate, 2) }} units/s</dd>
                </div>
            @endif
            @if ($minimumRate !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Minimum Rate</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$minimumRate, 2) }} units/s</dd>
                </div>
            @endif
        </dl>
    </div>
</div>
