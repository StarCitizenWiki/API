@props([
    'missileRack',
])

@php
    $missileCount = data_get($missileRack, 'missile_count');
    $missileSize = data_get($missileRack, 'missile_size');
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Missile Rack</h2>
        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @if ($missileCount !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Missile Count</dt>
                    <dd class="text-sm font-medium">{{ fmt_or_dash($missileCount, 0) }}</dd>
                </div>
            @endif
            @if ($missileSize !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Missile Size</dt>
                    <dd class="text-sm font-medium">S{{ fmt_or_dash($missileSize, 0) }}</dd>
                </div>
            @endif
        </dl>
    </div>
</div>
