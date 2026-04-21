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

    $hasSignalMultipliers = $signalCs !== null || $signalIr !== null || $signalEm !== null;
    $hasDamageMultipliers = collect([$damagePhys, $damageEnergy, $damageDist, $damageTherm, $damageBio, $damageStun])->filter()->isNotEmpty();
    $hasResistanceMultipliers = collect([$resPhys, $resEnergy, $resDist, $resTherm, $resBio, $resStun])->filter()->isNotEmpty();
    $hasPenetrationResistance = $penetrationBase !== null || collect([$penetrationPhys, $penetrationEnergy, $penetrationDist, $penetrationTherm, $penetrationBio, $penetrationStun])->filter()->isNotEmpty();
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Armor</h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Health</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($health, 'HP', 0) }}</dd>
            </div>
        </dl>

        @if ($hasSignalMultipliers)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Detection Signal Change
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($signalCsChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Cross Section</dt>
                                <dd class="text-sm font-medium {{ color_class($signalCsChange) }}">
                                    {{ fmt_value_with_unit($signalCsChange * 100, '%', 1, sign: true) }}
                                </dd>
                            </div>
                        @endif

                        @if ($signalIrChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Infrared</dt>
                                <dd class="text-sm font-medium {{ color_class($signalIrChange) }}">
                                    {{ fmt_value_with_unit($signalIrChange * 100, '%', 1, sign: true) }}
                                </dd>
                            </div>
                        @endif

                        @if ($signalEmChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Electromagnetic</dt>
                                <dd class="text-sm font-medium {{ color_class($signalEmChange) }}">
                                    {{ fmt_value_with_unit($signalEmChange * 100, '%', 1, sign: true) }}
                                </dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasDamageMultipliers)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Damage
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($damagePhys !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Physical</dt>
                                <dd class="text-sm font-medium {{ color_class($damagePhysChange) }}">
                                    {{ fmt_value_with_unit($damagePhysChange * 100, '%', 1) }}
                                </dd>
                            </div>
                        @endif

                        @if ($damageEnergy !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Energy</dt>
                                <dd class="text-sm font-medium {{ color_class($damageEnergyChange) }}">
                                    {{ fmt_value_with_unit($damageEnergyChange * 100, '%', 1) }}

                                </dd>
                            </div>
                        @endif

                        @if ($damageDist !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Distortion</dt>
                                <dd class="text-sm font-medium {{ color_class($damageDistChange) }}">
                                    {{ fmt_value_with_unit($damageDistChange * 100, '%', 1) }}
                                </dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasResistanceMultipliers)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Resistance Multipliers
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($resPhys !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Physical</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(($resPhys - 1) * 100, '%', 1) }}</dd>
                            </div>
                        @endif
                        @if ($resEnergy !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Energy</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(($resEnergy - 1) * 100, '%', 1) }}</dd>
                            </div>
                        @endif
                        @if ($resDist !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Distortion</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(($resDist - 1) * 100, '%', 1) }}</dd>
                            </div>
                        @endif
                        @if ($resTherm !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Thermal</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(($resTherm - 1) * 100, '%', 1) }}</dd>
                            </div>
                        @endif
                        @if ($resBio !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Biochemical</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(($resBio - 1) * 100, '%', 1) }}</dd>
                            </div>
                        @endif
                        @if ($resStun !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Stun</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(($resStun - 1) * 100, '%', 1) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasPenetrationResistance)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Penetration Resistance
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($penetrationBase !== null)
                            <div class="space-y-1 col-span-2">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Base</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($penetrationBase, 2) }}</dd>
                            </div>
                        @endif
                        @if ($penetrationPhys !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Physical</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($penetrationPhys, 2) }}</dd>
                            </div>
                        @endif
                        @if ($penetrationEnergy !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Energy</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($penetrationEnergy, 2) }}</dd>
                            </div>
                        @endif
                        @if ($penetrationDist !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Distortion</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($penetrationDist, 2) }}</dd>
                            </div>
                        @endif
                        @if ($penetrationTherm !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Thermal</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($penetrationTherm, 2) }}</dd>
                            </div>
                        @endif
                        @if ($penetrationBio !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Biochemical</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($penetrationBio, 2) }}</dd>
                            </div>
                        @endif
                        @if ($penetrationStun !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Stun</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($penetrationStun, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif
    </div>
</div>
