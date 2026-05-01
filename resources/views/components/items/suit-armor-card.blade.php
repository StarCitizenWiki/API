@props([
    'suitArmor',
])

@php
    $slot = data_get($suitArmor, 'slot');

    $damageResistanceMap = data_get($suitArmor, 'damage_resistance_map', []);
    $drImpact = data_get($damageResistanceMap, 'impact');
    $drImpactChange = data_get($damageResistanceMap, 'impact_change');
    $drPhysical = data_get($damageResistanceMap, 'physical');
    $drPhysicalChange = data_get($damageResistanceMap, 'physical_change');
    $drEnergy = data_get($damageResistanceMap, 'energy');
    $drEnergyChange = data_get($damageResistanceMap, 'energy_change');
    $drDistortion = data_get($damageResistanceMap, 'distortion');
    $drDistortionChange = data_get($damageResistanceMap, 'distortion_change');
    $drThermal = data_get($damageResistanceMap, 'thermal');
    $drThermalChange = data_get($damageResistanceMap, 'thermal_change');
    $drBiochemical = data_get($damageResistanceMap, 'biochemical');
    $drBiochemicalChange = data_get($damageResistanceMap, 'biochemical_change');
    $drStun = data_get($damageResistanceMap, 'stun');
    $drStunChange = data_get($damageResistanceMap, 'stun_change');

    $signature = data_get($suitArmor, 'signature', []);

    $radiationResistance = data_get($suitArmor, 'radiation_resistance', []);
    $rrMaxCapacity = data_get($radiationResistance, 'maximum_radiation_capacity');
    $rrDissipationRate = data_get($radiationResistance, 'radiation_dissipation_rate');

    if (! function_exists('getChangeColorClass')) {
        function getChangeColorClass($change) {
            if ($change < 0) {
                return 'text-success';
            }
            if ($change > 0) {
                return 'text-warning';
            }

            return '';
        }
    }

    $hasDamageResistanceMap = collect($damageResistanceMap)->filter()->isNotEmpty();
    $hasSignature = collect($signature)->isNotEmpty();
    $hasRadiationResistance = collect($radiationResistance)->filter()->isNotEmpty();
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Suit Armor</h2>

        @if ($slot !== null)
            <div class="space-y-1 col-span-2">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Slot</dt>
                <dd class="text-sm font-semibold text-base-content">{{ $slot }}</dd>
            </div>
        @endif

        <details class="group" open>
            <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                Damage
            </summary>
                <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                    @if ($drPhysicalChange !== null)
                        <div class="space-y-1">
                            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Physical</dt>
                            <dd class="text-sm font-medium {{ color_class($drPhysicalChange) }}">
                                {{ fmt_value_with_unit($drPhysicalChange * 100, '%', 1) }}
                            </dd>
                        </div>
                    @endif

                    @if ($drEnergyChange !== null)
                        <div class="space-y-1">
                            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Energy</dt>
                            <dd class="text-sm font-medium {{ color_class($drEnergyChange) }}">
                                {{ fmt_value_with_unit($drEnergyChange * 100, '%', 1) }}

                            </dd>
                        </div>
                    @endif

                    @if ($drDistortionChange !== null)
                        <div class="space-y-1">
                            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Distortion</dt>
                            <dd class="text-sm font-medium {{ color_class($drDistortionChange) }}">
                                {{ fmt_value_with_unit($drDistortionChange * 100, '%', 1) }}
                            </dd>
                        </div>
                    @endif

                    @if ($drThermalChange !== null)
                        <div class="space-y-1">
                            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Thermal</dt>
                            <dd class="text-sm font-medium {{ color_class($drThermalChange) }}">
                                {{ fmt_value_with_unit($drThermalChange * 100, '%', 1) }}
                            </dd>
                        </div>
                    @endif

                    @if ($drBiochemicalChange !== null)
                        <div class="space-y-1">
                            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Biochemical</dt>
                            <dd class="text-sm font-medium {{ color_class($drBiochemicalChange) }}">
                                {{ fmt_value_with_unit($drBiochemicalChange * 100, '%', 1) }}
                            </dd>
                        </div>
                    @endif

                    @if ($drStunChange !== null)
                        <div class="space-y-1">
                            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Stun</dt>
                            <dd class="text-sm font-medium {{ color_class($drStunChange) }}">
                                {{ fmt_value_with_unit($drStunChange * 100, '%', 1) }}
                            </dd>
                        </div>
                    @endif
                </dl>
        </details>

        @if ($hasSignature)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Signature
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @foreach ($signature as $key => $value)
                            @if ($value !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">{{ $key }}</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($value, '', 2) }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
            </details>
        @endif
    </div>
</div>
