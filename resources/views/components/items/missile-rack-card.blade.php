@props([
    'missileRack',
])

@php
    $missileCount = data_get($missileRack, 'missile_count');
    $missileSize = data_get($missileRack, 'missile_size');

    $hasCount = $missileCount !== null;
    $hasSize = $missileSize !== null;
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="square-stack" class="size-4 text-primary" />
            <span>Missile Rack Specifications</span>
        </h2>
        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($hasCount)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Missile Count</dt>
                    <dd class="text-sm font-medium">{{ (int)$missileCount }}</dd>
                </div>
            @endif
            @if ($hasSize)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Missile Size</dt>
                    <dd class="text-sm font-medium">Size {{ (int)$missileSize }}</dd>
                </div>
            @endif
        </dl>
    </div>
</div>
