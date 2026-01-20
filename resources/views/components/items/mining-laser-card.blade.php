@props([
    'miningLaser',
])

@php
    $laserPowerMin = data_get($miningLaser, 'laser_power.minimum');
    $laserPowerMax = data_get($miningLaser, 'laser_power.maximum');
    $moduleSlots = data_get($miningLaser, 'module_slots');
    $throttleLerpSpeed = data_get($miningLaser, 'throttle_lerp_speed');
    $throttleMinimum = data_get($miningLaser, 'throttle_minimum');
    $optimalRange = data_get($miningLaser, 'optimal_range');
    $maximumRange = data_get($miningLaser, 'maximum_range');
    $extractionThroughput = data_get($miningLaser, 'extraction_throughput');

    $hasLaserPower = $laserPowerMin !== null || $laserPowerMax !== null;
    $hasModuleSlots = $moduleSlots !== null;
    $hasThrottle = $throttleLerpSpeed !== null || $throttleMinimum !== null;
    $hasRange = $optimalRange !== null || $maximumRange !== null;
    $hasExtraction = $extractionThroughput !== null;
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="target" class="size-4 text-primary" />
            <span>Mining Laser Specifications</span>
        </h2>

        @if ($hasLaserPower || $hasModuleSlots || $hasThrottle)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Power & Control</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($hasLaserPower)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Laser Power</dt>
                                <dd class="text-sm font-medium">{{ $laserPowerMin ?? '-' }} - {{ $laserPowerMax ?? '-' }}</dd>
                            </div>
                        @endif
                        @if ($hasModuleSlots)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Module Slots</dt>
                                <dd class="text-sm font-medium">{{ (int)$moduleSlots }}</dd>
                            </div>
                        @endif
                        @if ($throttleLerpSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Throttle Lerp Speed</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$throttleLerpSpeed, 2) }}</dd>
                            </div>
                        @endif
                        @if ($throttleMinimum !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Throttle Minimum</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$throttleMinimum, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasRange)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Range</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($optimalRange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Optimal Range</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$optimalRange, 2) }} m</dd>
                            </div>
                        @endif
                        @if ($maximumRange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum Range</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$maximumRange, 2) }} m</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasExtraction)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Extraction</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($extractionThroughput !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Extraction Throughput</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$extractionThroughput, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif
    </div>
</div>
