@use('App\Support\Format')
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

    $hasPilot = $pilotDps !== null || $pilotAlpha !== null || $pilotSustainedDps !== null;
    $hasTurrets = $turretDps !== null || $turretAlpha !== null || $turretSustainedDps !== null;
    $hasMissiles = $missileCount !== null || $totalMissileDamage !== null;

    $hasWeaponryData = $hasPilot || $hasTurrets || $hasMissiles;
@endphp

@if ($hasWeaponryData)
    <section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
        <div class="card-body gap-4">
            <h2 class="card-title text-base">Weaponry</h2>

            <div class="grid gap-12 lg:grid-cols-3">
                @if ($hasPilot)
                    <x-dl-section title="Pilot Weapons">
                        @if ($pilotDps !== null)
                            <x-dt-dd label="DPS">
                                {{ Format::numberOrDash($pilotDps, 1) }} <span class="text-xs text-muted">DPS</span>
                            </x-dt-dd>
                        @endif

                        @if ($pilotAlpha !== null)
                            <x-dt-dd label="Alpha">
                                {{ Format::numberOrDash($pilotAlpha, 1) }}
                            </x-dt-dd>
                        @endif

                        @if ($pilotSustainedDps !== null)
                            <x-dt-dd label="Sustained DPS">
                                {{ Format::numberOrDash($pilotSustainedDps, 1) }} <span class="text-xs text-muted">DPS</span>
                            </x-dt-dd>
                        @endif
                    </x-dl-section>
                @endif

                @if ($hasTurrets)
                    <x-dl-section title="Turrets">
                        @if ($turretDps !== null)
                            <x-dt-dd label="DPS">
                                {{ Format::numberOrDash($turretDps, 1) }} <span class="text-xs text-muted">DPS</span>
                            </x-dt-dd>
                        @endif

                        @if ($turretAlpha !== null)
                            <x-dt-dd label="Alpha">
                                {{ Format::numberOrDash($turretAlpha, 1) }}
                            </x-dt-dd>
                        @endif

                        @if ($turretSustainedDps !== null)
                            <x-dt-dd label="Sustained DPS">
                                {{ Format::numberOrDash($turretSustainedDps, 1) }} <span class="text-xs text-muted">DPS</span>
                            </x-dt-dd>
                        @endif
                    </x-dl-section>
                @endif

                @if ($hasMissiles)
                    <x-dl-section title="Missiles">
                        @if ($missileCount !== null)
                            <x-dt-dd label="Count">
                                {{ Format::numberOrDash($missileCount) }}
                            </x-dt-dd>
                        @endif

                        @if ($totalMissileDamage !== null)
                            <x-dt-dd label="Total Damage">
                                {{ Format::numberOrDash($totalMissileDamage) }}
                            </x-dt-dd>
                        @endif
                    </x-dl-section>
                @endif
            </div>
        </div>
    </section>
@endif
