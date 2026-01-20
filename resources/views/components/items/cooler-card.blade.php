@props([
    'cooler',
])

@php
    $coolingRate = data_get($cooler, 'cooling_rate');
    $suppressionIRFactor = data_get($cooler, 'suppression_ir_factor');
    $suppressionHeatFactor = data_get($cooler, 'suppression_heat_factor');
    $coolantSegmentGeneration = data_get($cooler, 'coolant_segment_generation');

    $hasCoolerData = is_array($cooler) && array_filter($cooler, fn($v) => $v !== null);
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="fan" class="size-4 text-primary" />
            <span>Cooler Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($coolantSegmentGeneration !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Coolant Segment Generation</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$coolantSegmentGeneration, 2) }}</dd>
                </div>
            @endif
        </dl>
    </div>
</div>
