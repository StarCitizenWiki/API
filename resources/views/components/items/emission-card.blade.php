@props([
    'emission',
])

@php
    $ir = data_get($emission, 'ir');
    $emMin = data_get($emission, 'em_min');
    $emMax = data_get($emission, 'em_max');
    $emDecay = data_get($emission, 'em_decay');
    $emPerSegment = data_get($emission, 'em_per_segment');
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-3">
        <h4 class="card-title text-sm flex items-center gap-2">
            <x-icon name="activity" class="size-4 text-primary" />
            <span>Emission</span>
        </h4>
        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">IR Emission</dt>
                <dd class="text-sm font-medium">{{ fmt_or_dash($ir, 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">EM Range</dt>
                <dd class="text-sm font-medium">{{ fmt_range($emMin, $emMax, '', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">EM Decay</dt>
                <dd class="text-sm font-medium">{{ fmt_or_dash($emDecay, 0) }}</dd>
            </div>
            @if($emPerSegment)
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">EM Per Segment</dt>
                <dd class="text-sm font-medium">{{ fmt_or_dash($emPerSegment, 0) }}</dd>
            </div>
           @endif
        </dl>
    </div>
</div>
