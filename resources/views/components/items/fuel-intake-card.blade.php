@props([
    'fuelIntake',
])

@php
    $fuelPushRate = data_get($fuelIntake, 'fuel_push_rate');
    $minimumRate = data_get($fuelIntake, 'minimum_rate');
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-3">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="wind" class="size-4 text-primary" />
            <span>Fuel Intake</span>
        </h2>

        <dl class="grid gap-4 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fuel Push Rate</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit($fuelPushRate, '/s', 2) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Minimum Rate</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit($minimumRate, '/s', 2) }}</dd>
            </div>
        </dl>
    </div>
</div>
