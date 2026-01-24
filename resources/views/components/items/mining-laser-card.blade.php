@props([
    'miningLaser',
])

@php
    $laserPowerMin = data_get($miningLaser, 'laser_power.minimum');
    $laserPowerMax = data_get($miningLaser, 'laser_power.maximum');
    $moduleSlots = data_get($miningLaser, 'module_slots');
    $extractionThroughput = data_get($miningLaser, 'extraction_throughput');

    $optimalRange = data_get($miningLaser, 'optimal_range');
    $maximumRange = data_get($miningLaser, 'maximum_range');
    $throttleLerpSpeed = data_get($miningLaser, 'throttle_lerp_speed');
    $throttleMinimum = data_get($miningLaser, 'throttle_minimum');

    $modifierMap = data_get($miningLaser, 'modifier_map', []);
    $hasModifiers = !empty($modifierMap);

    $hasSecondary = $optimalRange !== null || $maximumRange !== null
        || $throttleLerpSpeed !== null || $throttleMinimum !== null;
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="laser" class="size-4 text-primary" />
            <span>Mining Laser</span>
        </h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Laser Power</dt>
                <dd class="text-sm font-medium">{{ fmt_range($laserPowerMin, $laserPowerMax, '') }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Module Slots</dt>
                <dd class="text-sm font-medium">{{ fmt_or_dash($moduleSlots, 0) }}</dd>
            </div>
        </dl>

        @if ($hasSecondary)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Range & Throttle
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                        @if ($optimalRange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Optimal Range</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($optimalRange, 'm', 2) }}</dd>
                            </div>
                        @endif

                        @if ($maximumRange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum Range</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($maximumRange, 'm', 2) }}</dd>
                            </div>
                        @endif

                        @if ($throttleLerpSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Throttle Lerp Speed</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($throttleLerpSpeed, 2) }}</dd>
                            </div>
                        @endif

                        @if ($throttleMinimum !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Throttle Minimum</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($throttleMinimum, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        @if ($hasModifiers && count($modifierMap) >= 2)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Modifiers
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2">
                        @foreach ($modifierMap as $key => $value)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                    {{ str_replace('_', ' ', $key) }}
                                </dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($value, '%', 1) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </details>
        @endif
    </div>
</div>
