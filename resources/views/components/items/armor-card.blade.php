@props([
    'armor',
])

@php
    $health = data_get($armor, 'health');

    $signalMultipliers = data_get($armor, 'signal_multiplier', []);
    $signalCs = data_get($signalMultipliers, 'cross_section');
    $signalCsChange = data_get($signalMultipliers, 'cross_section_change');
    $signalIr = data_get($signalMultipliers, 'infrared');
    $signalIrChange = data_get($signalMultipliers, 'infrared_change');
    $signalEm = data_get($signalMultipliers, 'electromagnetic');
    $signalEmChange = data_get($signalMultipliers, 'electromagnetic_change');

    $damageMultipliers = data_get($armor, 'damage_multiplier', []);
    $damagePhys = data_get($damageMultipliers, 'physical');
    $damagePhysChange = data_get($damageMultipliers, 'physical_change');
    $damageEnergy = data_get($damageMultipliers, 'energy');
    $damageEnergyChange = data_get($damageMultipliers, 'energy_change');
    $damageDist = data_get($damageMultipliers, 'distortion');
    $damageDistChange = data_get($damageMultipliers, 'distortion_change');
    $damageTherm = data_get($damageMultipliers, 'thermal');
    $damageThermChange = data_get($damageMultipliers, 'thermal_change');
    $damageBio = data_get($damageMultipliers, 'biochemical');
    $damageBioChange = data_get($damageMultipliers, 'biochemical_change');
    $damageStun = data_get($damageMultipliers, 'stun');
    $damageStunChange = data_get($damageMultipliers, 'stun_change');

    $resistanceMultipliers = data_get($armor, 'resistance_multiplier', []);
    $resPhys = data_get($resistanceMultipliers, 'physical');
    $resEnergy = data_get($resistanceMultipliers, 'energy');
    $resDist = data_get($resistanceMultipliers, 'distortion');
    $resTherm = data_get($resistanceMultipliers, 'thermal');
    $resBio = data_get($resistanceMultipliers, 'biochemical');
    $resStun = data_get($resistanceMultipliers, 'stun');

    $penetrationResist = data_get($armor, 'penetration_resistance', []);
    $penetrationBase = data_get($penetrationResist, 'base');
    $penetrationPhys = data_get($penetrationResist, 'physical');
    $penetrationEnergy = data_get($penetrationResist, 'energy');
    $penetrationDist = data_get($penetrationResist, 'distortion');
    $penetrationTherm = data_get($penetrationResist, 'thermal');
    $penetrationBio = data_get($penetrationResist, 'biochemical');
    $penetrationStun = data_get($penetrationResist, 'stun');

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

    $hasSignalMultipliers = $signalCs !== null || $signalIr !== null || $signalEm !== null;
    $hasDamageMultipliers = collect([$damagePhys, $damageEnergy, $damageDist, $damageTherm, $damageBio, $damageStun])->filter()->isNotEmpty();
    $hasResistanceMultipliers = collect([$resPhys, $resEnergy, $resDist, $resTherm, $resBio, $resStun])->filter()->isNotEmpty();
    $hasPenetrationResistance = $penetrationBase !== null || collect([$penetrationPhys, $penetrationEnergy, $penetrationDist, $penetrationTherm, $penetrationBio, $penetrationStun])->filter()->isNotEmpty();
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="brick-wall-shield" class="size-4 text-primary" />
            <span>Armor Specifications</span>
        </h2>

        @if ($health !== null)
            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Health</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$health, 0) }}</dd>
                </div>
            </dl>
        @endif

        @if ($hasSignalMultipliers)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Signal Multipliers</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($signalCs !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cross Section</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$signalCs, 2) }}</dd>
                            </div>
                        @endif
                        @if ($signalCs !== null && $signalCsChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cross Section Change</dt>
                                <dd class="text-sm font-medium {{ getChangeColorClass($signalCsChange) }}">{{ formatPercentChange($signalCsChange) }}</dd>
                            </div>
                        @endif
                        @if ($signalIr !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Infrared</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$signalIr, 2) }}</dd>
                            </div>
                        @endif
                        @if ($signalIr !== null && $signalIrChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Infrared Change</dt>
                                <dd class="text-sm font-medium {{ getChangeColorClass($signalIrChange) }}">{{ formatPercentChange($signalIrChange) }}</dd>
                            </div>
                        @endif
                        @if ($signalEm !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Electromagnetic</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$signalEm, 2) }}</dd>
                            </div>
                        @endif
                        @if ($signalEm !== null && $signalEmChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Electromagnetic Change</dt>
                                <dd class="text-sm font-medium {{ getChangeColorClass($signalEmChange) }}">{{ formatPercentChange($signalEmChange) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasDamageMultipliers)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Damage Multipliers</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($damagePhys !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$damagePhys, 2) }}</dd>
                            </div>
                        @endif
                        @if ($damagePhys !== null && $damagePhysChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical Change</dt>
                                <dd class="text-sm font-medium {{ getChangeColorClass($damagePhysChange) }}">{{ formatPercentChange($damagePhysChange) }}</dd>
                            </div>
                        @endif
                        @if ($damageEnergy !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$damageEnergy, 2) }}</dd>
                            </div>
                        @endif
                        @if ($damageEnergy !== null && $damageEnergyChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy Change</dt>
                                <dd class="text-sm font-medium {{ getChangeColorClass($damageEnergyChange) }}">{{ formatPercentChange($damageEnergyChange) }}</dd>
                            </div>
                        @endif
                        @if ($damageDist !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$damageDist, 2) }}</dd>
                            </div>
                        @endif
                        @if ($damageDist !== null && $damageDistChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion Change</dt>
                                <dd class="text-sm font-medium {{ getChangeColorClass($damageDistChange) }}">{{ formatPercentChange($damageDistChange) }}</dd>
                            </div>
                        @endif
                        @if ($damageTherm !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$damageTherm, 2) }}</dd>
                            </div>
                        @endif
                        @if ($damageTherm !== null && $damageThermChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal Change</dt>
                                <dd class="text-sm font-medium {{ getChangeColorClass($damageThermChange) }}">{{ formatPercentChange($damageThermChange) }}</dd>
                            </div>
                        @endif
                        @if ($damageBio !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$damageBio, 2) }}</dd>
                            </div>
                        @endif
                        @if ($damageBio !== null && $damageBioChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical Change</dt>
                                <dd class="text-sm font-medium {{ getChangeColorClass($damageBioChange) }}">{{ formatPercentChange($damageBioChange) }}</dd>
                            </div>
                        @endif
                        @if ($damageStun !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$damageStun, 2) }}</dd>
                            </div>
                        @endif
                        @if ($damageStun !== null && $damageStunChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun Change</dt>
                                <dd class="text-sm font-medium {{ getChangeColorClass($damageStunChange) }}">{{ formatPercentChange($damageStunChange) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasResistanceMultipliers)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Resistance Multipliers</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($resPhys !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$resPhys, 2) }}</dd>
                            </div>
                        @endif
                        @if ($resEnergy !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$resEnergy, 2) }}</dd>
                            </div>
                        @endif
                        @if ($resDist !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$resDist, 2) }}</dd>
                            </div>
                        @endif
                        @if ($resTherm !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$resTherm, 2) }}</dd>
                            </div>
                        @endif
                        @if ($resBio !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$resBio, 2) }}</dd>
                            </div>
                        @endif
                        @if ($resStun !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$resStun, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasPenetrationResistance)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Penetration Resistance</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($penetrationBase !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Base</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$penetrationBase, 2) }}</dd>
                            </div>
                        @endif
                        @if ($penetrationPhys !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$penetrationPhys, 2) }}</dd>
                            </div>
                        @endif
                        @if ($penetrationEnergy !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$penetrationEnergy, 2) }}</dd>
                            </div>
                        @endif
                        @if ($penetrationDist !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$penetrationDist, 2) }}</dd>
                            </div>
                        @endif
                        @if ($penetrationTherm !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$penetrationTherm, 2) }}</dd>
                            </div>
                        @endif
                        @if ($penetrationBio !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$penetrationBio, 2) }}</dd>
                            </div>
                        @endif
                        @if ($penetrationStun !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$penetrationStun, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif
    </div>
</div>
