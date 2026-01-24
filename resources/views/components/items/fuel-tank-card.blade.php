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
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="droplet" class="size-4 text-primary" />
            <span>Fuel Tank</span>
        </h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fill Rate</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit($fillRate, '/s', 2) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Drain Rate</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit($drainRate, '/s', 2) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Capacity</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit($capacity, 'SCU', 0) }}</dd>
            </div>
        </dl>
    </div>
</div>
