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
    <div class="card-body gap-3">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="fan" class="size-4 text-primary" />
            <span>Cooler</span>
        </h2>

        <dl class="grid gap-4 grid-cols-2">

            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Coolant Generation</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit($coolantSegmentGeneration, 'Segments', 0) }}</dd>
            </div>
        </dl>
    </div>
</div>
