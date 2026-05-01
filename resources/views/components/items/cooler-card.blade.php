@props([
    'cooler',
])

@php
    $coolingRate = data_get($cooler, 'cooling_rate');
    $suppressionIRFactor = data_get($cooler, 'suppression_ir_factor');
    $suppressionHeatFactor = data_get($cooler, 'suppression_heat_factor');
    $coolantSegmentGeneration = data_get($cooler, 'coolant_segment_generation');

    $hasCoolerData = is_array($cooler) && collect($cooler)->filter(fn($v) => $v !== null)->isNotEmpty();
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Cooler</h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">

            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Coolant Generation</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($coolantSegmentGeneration, 'Segments', 0) }}</dd>
            </div>
        </dl>
    </div>
</div>
