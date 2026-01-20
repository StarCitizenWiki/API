@props([
    'emission',
])

@php
    $ir = data_get($emission, 'ir');
    $emMin = data_get($emission, 'em_min');
    $emMax = data_get($emission, 'em_max');
    $emDecay = data_get($emission, 'em_decay');
    $emPerSegment = data_get($emission, 'em_per_segment');

    $hasIr = $ir !== null;
    $hasEmRange = $emMin !== null || $emMax !== null;
    $hasEmDecay = $emDecay !== null;
    $hasEmPerSegment = $emPerSegment !== null;
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="activity" class="size-4 text-primary" />
            <span>Emission Specifications</span>
        </h2>
        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($hasIr)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">IR Emission</dt>
                    <dd class="text-sm font-medium">{{ (int)$ir }}</dd>
                </div>
            @endif
            @if ($hasEmRange)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">EM Range</dt>
                    <dd class="text-sm font-medium">{{ $emMin ?? '-' }} - {{ $emMax ?? '-' }}</dd>
                </div>
            @endif
            @if ($hasEmDecay)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">EM Decay</dt>
                    <dd class="text-sm font-medium">{{ (int)$emDecay }}</dd>
                </div>
            @endif
            @if ($hasEmPerSegment)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">EM Per Segment</dt>
                    <dd class="text-sm font-medium">{{ (int)$emPerSegment }}</dd>
                </div>
            @endif
        </dl>
    </div>
</div>
