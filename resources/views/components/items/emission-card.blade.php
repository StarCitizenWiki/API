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
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Emission</h2>
        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">IR Emission</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($ir, 1) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">EM Range</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_range($emMin, $emMax, '', 1) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">EM Decay</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($emDecay, 2) }}</dd>
            </div>
            @if($emPerSegment)
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">EM Per Segment</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($emPerSegment, 0) }}</dd>
            </div>
           @endif
        </dl>
    </div>
</div>
