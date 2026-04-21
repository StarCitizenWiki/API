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

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Fuel Push Rate</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($fuelPushRate, '/s', 2) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Minimum Rate</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($minimumRate, '/s', 2) }}</dd>
            </div>
        </dl>
    </div>
</div>
