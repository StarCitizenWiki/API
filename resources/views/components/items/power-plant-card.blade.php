@props([
    'powerPlant',
])

@php
    $powerOutput = data_get($powerPlant, 'power_output');
    $powerSegmentGeneration = data_get($powerPlant, 'power_segment_generation');

    $hasPowerPlantData = is_array($powerPlant) && array_filter($powerPlant, fn($v) => $v !== null);
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="plug" class="size-4 text-primary" />
            <span>Power Plant Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($powerSegmentGeneration !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Power Segment Generation</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$powerSegmentGeneration, 2) }}</dd>
                </div>
            @endif
        </dl>
    </div>
</div>
