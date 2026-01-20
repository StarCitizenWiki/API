@props([
    'fuelTank',
])

@php
    $fillRate = data_get($fuelTank, 'fill_rate');
    $drainRate = data_get($fuelTank, 'drain_rate');
    $capacity = data_get($fuelTank, 'capacity');
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="droplet" class="size-4 text-primary" />
            <span>Fuel Tank Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($fillRate !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fill Rate</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$fillRate, 2) }} units/s</dd>
                </div>
            @endif
            @if ($drainRate !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Drain Rate</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$drainRate, 2) }} units/s</dd>
                </div>
            @endif
            @if ($capacity !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Capacity</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$capacity) }} SCU</dd>
                </div>
            @endif
        </dl>
    </div>
</div>
