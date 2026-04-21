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
        <h2 class="card-title text-base">Mining Laser</h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Laser Power</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_range($laserPowerMin, $laserPowerMax, '') }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Module Slots</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($moduleSlots, 0) }}</dd>
            </div>
        </dl>

        @if ($hasSecondary)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Range & Throttle
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($optimalRange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Optimal Range</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($optimalRange, 'm', 2) }}</dd>
                            </div>
                        @endif

                        @if ($maximumRange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Maximum Range</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($maximumRange, 'm', 2) }}</dd>
                            </div>
                        @endif

                        @if ($throttleLerpSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Throttle Lerp Speed</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($throttleLerpSpeed, 2) }}</dd>
                            </div>
                        @endif

                        @if ($throttleMinimum !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Throttle Minimum</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($throttleMinimum, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasModifiers && count($modifierMap) >= 2)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Modifiers
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @foreach ($modifierMap as $key => $value)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">
                                    {{ str_replace('_', ' ', $key) }}
                                </dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($value, '%', 1) }}</dd>
                            </div>
                        @endforeach
                    </dl>
            </details>
        @endif
    </div>
</div>
