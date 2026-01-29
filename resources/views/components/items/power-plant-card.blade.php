@props([
    'powerPlant',
])

@php
    $powerOutput = data_get($powerPlant, 'power_output');
    $powerSegmentGeneration = data_get($powerPlant, 'power_segment_generation');
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-3">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="power" class="size-4 text-primary" />
            <span>Power Plant</span>
        </h2>

        <dl class="grid gap-4 grid-cols-1">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Power Segment Generation</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit($powerSegmentGeneration, 'Segments', 0) }}</dd>
            </div>
        </dl>
    </div>
</div>
