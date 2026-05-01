@props(['vehicle'])

@php
    $weaponry = data_get($vehicle, 'weaponry', []);

    $pilotDps = data_get($weaponry, 'pilot_dps');
    $pilotAlpha = data_get($weaponry, 'pilot_alpha');
    $pilotSustainedDps = data_get($weaponry, 'pilot_sustained_dps');

    $turretDps = data_get($weaponry, 'turret_dps');
    $turretAlpha = data_get($weaponry, 'turret_alpha');
    $turretSustainedDps = data_get($weaponry, 'turret_sustained_dps');

    $missileCount = data_get($weaponry, 'missiles.count');
    $totalMissileDamage = data_get($weaponry, 'total_missile_damage');

    $formatDps = static fn (mixed $value): string => $value === null ? '-' : number_format((float) $value, 1);
    $formatWhole = static fn (mixed $value): string => $value === null ? '-' : number_format((float) $value, 0);

    $hasPilot = $pilotDps !== null || $pilotAlpha !== null || $pilotSustainedDps !== null;
    $hasTurrets = $turretDps !== null || $turretAlpha !== null || $turretSustainedDps !== null;
    $hasMissiles = $missileCount !== null || $totalMissileDamage !== null;

    $hasWeaponryData = $hasPilot || $hasTurrets || $hasMissiles;
@endphp

@if ($hasWeaponryData)
    <section {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
        <div class="card-body gap-4">
            <h2 class="card-title text-base">Weaponry</h2>

            <div class="grid gap-12 lg:grid-cols-3">
                @if ($hasPilot)
                    <section class="space-y-4">
                        <div class="space-y-1">
                            <h3 class="text-sm font-semibold text-base-content">Pilot Weapons</h3>
                        </div>

                        <dl class="grid grid-cols-2 gap-x-3 gap-y-2">
                            @if ($pilotDps !== null)
                                <dt class="text-sm text-emphasis">DPS</dt>
                                <dd class="text-right text-sm font-semibold text-base-content">
                                    {{ $formatDps($pilotDps) }} <span class="text-xs text-muted">DPS</span>
                                </dd>
                            @endif

                            @if ($pilotAlpha !== null)
                                <dt class="text-sm text-emphasis">Alpha</dt>
                                <dd class="text-right text-sm font-semibold text-base-content">
                                    {{ $formatDps($pilotAlpha) }}
                                </dd>
                            @endif

                            @if ($pilotSustainedDps !== null)
                                <dt class="text-sm text-emphasis">Sustained DPS</dt>
                                <dd class="text-right text-sm font-semibold text-base-content">
                                    {{ $formatDps($pilotSustainedDps) }} <span class="text-xs text-muted">DPS</span>
                                </dd>
                            @endif
                        </dl>
                    </section>
                @endif

                @if ($hasTurrets)
                    <section class="space-y-4">
                        <div class="space-y-1">
                            <h3 class="text-sm font-semibold text-base-content">Turrets</h3>
                        </div>

                        <dl class="grid grid-cols-2 gap-x-3 gap-y-2">
                            @if ($turretDps !== null)
                                <dt class="text-sm text-emphasis">DPS</dt>
                                <dd class="text-right text-sm font-semibold text-base-content">
                                    {{ $formatDps($turretDps) }} <span class="text-xs text-muted">DPS</span>
                                </dd>
                            @endif

                            @if ($turretAlpha !== null)
                                <dt class="text-sm text-emphasis">Alpha</dt>
                                <dd class="text-right text-sm font-semibold text-base-content">
                                    {{ $formatDps($turretAlpha) }}
                                </dd>
                            @endif

                            @if ($turretSustainedDps !== null)
                                <dt class="text-sm text-emphasis">Sustained DPS</dt>
                                <dd class="text-right text-sm font-semibold text-base-content">
                                    {{ $formatDps($turretSustainedDps) }} <span class="text-xs text-muted">DPS</span>
                                </dd>
                            @endif
                        </dl>
                    </section>
                @endif

                @if ($hasMissiles)
                    <section class="space-y-4">
                        <div class="space-y-1">
                            <h3 class="text-sm font-semibold text-base-content">Missiles</h3>
                        </div>

                        <dl class="grid grid-cols-2 gap-x-3 gap-y-2">
                            @if ($missileCount !== null)
                                <dt class="text-sm text-emphasis">Count</dt>
                                <dd class="text-right text-sm font-semibold text-base-content">
                                    {{ $formatWhole($missileCount) }}
                                </dd>
                            @endif

                            @if ($totalMissileDamage !== null)
                                <dt class="text-sm text-emphasis">Total Damage</dt>
                                <dd class="text-right text-sm font-semibold text-base-content">
                                    {{ $formatWhole($totalMissileDamage) }}
                                </dd>
                            @endif
                        </dl>
                    </section>
                @endif
            </div>
        </div>
    </section>
@endif
