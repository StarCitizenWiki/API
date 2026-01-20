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

    if (!function_exists('getChangeColorClass')) {
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

    if (!function_exists('formatPercentChange')) {
        function formatPercentChange($value) {
            if ($value === null) {
                return '-';
            }
            $sign = $value > 0 ? '+' : '';
            return $sign . round($value * 100) . '%';
        }
    }

    $hasDamageResistanceMap = collect($damageResistanceMap)->filter()->isNotEmpty();
    $hasSignature = collect($signature)->isNotEmpty();
    $hasRadiationResistance = collect($radiationResistance)->filter()->isNotEmpty();
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="shield" class="size-4 text-primary" />
            <span>Suit Armor</span>
        </h2>

        @if ($slot !== null)
            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Slot</dt>
                    <dd class="text-sm font-medium">{{ $slot }}</dd>
                </div>
            </dl>
        @endif

        @if ($hasDamageResistanceMap)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Damage Resistance Map</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($drImpact !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Impact</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$drImpact, 2) }}</dd>
                            </div>
                        @endif
                        @if ($drImpact !== null && $drImpactChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Impact</dt>
                                <dd class="text-sm font-medium {{ getChangeColorClass($drImpactChange) }}">{{ formatPercentChange($drImpactChange) }}</dd>
                            </div>
                        @endif
                        @if ($drPhysical !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$drPhysical, 2) }}</dd>
                            </div>
                        @endif
                        @if ($drPhysical !== null && $drPhysicalChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                <dd class="text-sm font-medium {{ getChangeColorClass($drPhysicalChange) }}">{{ formatPercentChange($drPhysicalChange) }}</dd>
                            </div>
                        @endif
                        @if ($drEnergy !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$drEnergy, 2) }}</dd>
                            </div>
                        @endif
                        @if ($drEnergy !== null && $drEnergyChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                <dd class="text-sm font-medium {{ getChangeColorClass($drEnergyChange) }}">{{ formatPercentChange($drEnergyChange) }}</dd>
                            </div>
                        @endif
                        @if ($drDistortion !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$drDistortion, 2) }}</dd>
                            </div>
                        @endif
                        @if ($drDistortion !== null && $drDistortionChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                <dd class="text-sm font-medium {{ getChangeColorClass($drDistortionChange) }}">{{ formatPercentChange($drDistortionChange) }}</dd>
                            </div>
                        @endif
                        @if ($drThermal !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$drThermal, 2) }}</dd>
                            </div>
                        @endif
                        @if ($drThermal !== null && $drThermalChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                <dd class="text-sm font-medium {{ getChangeColorClass($drThermalChange) }}">{{ formatPercentChange($drThermalChange) }}</dd>
                            </div>
                        @endif
                        @if ($drBiochemical !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$drBiochemical, 2) }}</dd>
                            </div>
                        @endif
                        @if ($drBiochemical !== null && $drBiochemicalChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                <dd class="text-sm font-medium {{ getChangeColorClass($drBiochemicalChange) }}">{{ formatPercentChange($drBiochemicalChange) }}</dd>
                            </div>
                        @endif
                        @if ($drStun !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$drStun, 2) }}</dd>
                            </div>
                        @endif
                        @if ($drStun !== null && $drStunChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                <dd class="text-sm font-medium {{ getChangeColorClass($drStunChange) }}">{{ formatPercentChange($drStunChange) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasSignature)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Signature</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @foreach ($signature as $key => $value)
                            @if ($value !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">{{ $key }}</dt>
                                    <dd class="text-sm font-medium">{{ number_format((float)$value, 2) }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasRadiationResistance)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Radiation Resistance</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($rrMaxCapacity !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum Radiation Capacity</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$rrMaxCapacity, 2) }} REM</dd>
                            </div>
                        @endif
                        @if ($rrDissipationRate !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Radiation Dissipation Rate</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$rrDissipationRate, 2) }} REM/s</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif
    </div>
</div>
