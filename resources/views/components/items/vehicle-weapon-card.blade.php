@props([
    'vehicleWeapon',
 ])

@php
    $class = data_get($vehicleWeapon, 'class');
    $type = data_get($vehicleWeapon, 'type');
    $capacity = data_get($vehicleWeapon, 'capacity');
    $range = data_get($vehicleWeapon, 'range');
    $rpm = data_get($vehicleWeapon, 'rpm');

    $damage = data_get($vehicleWeapon, 'damage', []);
    $dpsTotal = data_get($damage, 'dps_total');
    $alphaTotal = data_get($damage, 'alpha_total');
    $maximum = data_get($damage, 'maximum');
    $sustained60s = data_get($damage, 'sustained_60s');
    $burst = data_get($damage, 'burst');
    $dps = data_get($damage, 'dps', []);
    $alpha = data_get($damage, 'alpha', []);

    $nonZeroDps = collect($dps)->filter(static fn($v) => $v !== null && $v > 0);
    $nonZeroAlpha = collect($alpha)->filter(static fn($v) => $v !== null && $v > 0);

    $hasDamage = is_array($damage) && $damage !== [];

    $spread = data_get($vehicleWeapon, 'spread', []);
    $hasSpread = is_array($spread) && $spread !== [];

    $barrelSpinTime = data_get($vehicleWeapon, 'barrel_spin_time', []);
    $barrelUp = data_get($barrelSpinTime, 'up');
    $barrelDown = data_get($barrelSpinTime, 'down');
    $hasBarrelSpin = is_array($barrelSpinTime) && $barrelSpinTime !== [];

    $heat = data_get($vehicleWeapon, 'heat', []);
    $heatPerShot = data_get($heat, 'per_shot');
    $heatCoolingDelay = data_get($heat, 'cooling_delay');
    $heatCoolingPerSecond = data_get($heat, 'cooling_per_second');
    $heatOverheatMaxShots = data_get($heat, 'overheat_max_shots');
    $heatOverheatMaxTime = data_get($heat, 'overheat_max_time');
    $heatOverheatCooldown = data_get($heat, 'overheat_cooldown');
    $hasHeat = is_array($heat) && $heat !== [];

    $capacitor = data_get($vehicleWeapon, 'capacitor', []);
    $capMaxAmmoLoad = data_get($capacitor, 'max_ammo_load');
    $capRegenPerSecond = data_get($capacitor, 'regen_per_second');
    $capCooldown = data_get($capacitor, 'cooldown');
    $capRequestedAmmoLoad = data_get($capacitor, 'requested_ammo_load');
    $capCostsPerShot = data_get($capacitor, 'costs_per_shot');
    $hasCapacitor = is_array($capacitor) && $capacitor !== [];

    $charge = data_get($vehicleWeapon, 'charge', []);
    $chargeTime = data_get($charge, 'time');
    $chargeOverchargeTime = data_get($charge, 'overcharge_time');
    $chargeOverchargedTime = data_get($charge, 'overcharged_time');
    $chargeCooldownTime = data_get($charge, 'cooldown_time');
    $hasCharge = is_array($charge) && $charge !== [];

    $chargeModifier = data_get($vehicleWeapon, 'charge_modifier', []);
    $chargeModDamage = data_get($chargeModifier, 'damage');
    $chargeModFireRate = data_get($chargeModifier, 'fire_rate');
    $chargeModAmmoSpeed = data_get($chargeModifier, 'ammo_speed');
    $hasChargeModifier = is_array($chargeModifier) && $chargeModifier !== [];
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="sword" class="size-4 text-primary" />
            <span>Vehicle Weapon</span>
        </h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Class</dt>
                <dd class="text-sm font-medium">{{ $class ?? '-' }} {{ $type ?? '-' }}</dd>
            </div>
            @if ($capacity !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Capacity</dt>
                    <dd class="text-sm font-medium">{{ $capacity === 0 ? 'Infinite' : fmt_value_with_unit($capacity, 'rounds', 0) }}</dd>
                </div>
            @endif
            @if ($range !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Range</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($range, 'm', 0) }}</dd>
                </div>
            @endif
            @if ($rpm !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">RPM</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($rpm, 'RPM', 0) }}</dd>
                </div>
            @endif
        </dl>

        @if ($hasDamage)
            <details id="damage-stats" class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold" aria-expanded="true" aria-controls="damage-stats-content">
                    Damage Stats
                </summary>
                <div id="damage-stats-content" class="collapse-content">
                    <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
{{--                        @if ($sustained60s !== null)--}}
{{--                            <div class="space-y-1">--}}
{{--                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Sustained 60s</dt>--}}
{{--                                <dd class="text-sm font-medium">{{ fmt_compact($sustained60s, 2) }}</dd>--}}
{{--                            </div>--}}
{{--                        @endif--}}
                        @if ($alphaTotal !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Alpha</dt>
                                <dd class="text-sm font-medium text-info">{{ fmt_compact($alphaTotal, 0) }}</dd>
                            </div>
                        @endif
                        @if ($burst !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Burst</dt>
                                <dd class="text-sm font-medium">{{ fmt_compact($burst, 2) }}</dd>
                            </div>
                        @endif
                        @if ($maximum !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum</dt>
                                <dd class="text-sm font-medium" title="{{ $maximum }}">{{ $maximum === 'Infinite' ? $maximum : fmt_compact($maximum) }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($nonZeroDps !== [])
                        <div class="divider"></div>
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-2">DPS Breakdown</h4>
                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2">
                            @if (data_get($nonZeroDps, 'physical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                    <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroDps, 'physical'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'energy'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                    <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroDps, 'energy'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'distortion'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                    <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroDps, 'distortion'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'thermal'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                    <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroDps, 'thermal'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'biochemical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                    <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroDps, 'biochemical'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'stun'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                    <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroDps, 'stun'), 0) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif

                    @if (!empty($nonZeroAlpha))
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-2 mt-4">Alpha Breakdown</h4>
                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2">
                            @if (data_get($nonZeroAlpha, 'physical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                    <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroAlpha, 'physical'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'energy'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                    <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroAlpha, 'energy'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'distortion'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                    <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroAlpha, 'distortion'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'thermal'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                    <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroAlpha, 'thermal'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'biochemical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                    <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroAlpha, 'biochemical'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'stun'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                    <dd class="text-sm font-medium">{{ fmt_or_dash(data_get($nonZeroAlpha, 'stun'), 0) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
                </div>
            </details>
        @endif

        @if ($hasSpread)
            <details id="spread" class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold" aria-expanded="false" aria-controls="spread-content">
                    Spread
                </summary>
                <div id="spread-content" class="collapse-content">
                    <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                        @if (data_get($spread, 'minimum'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Min/Max</dt>
                                <dd class="text-sm font-medium">{{ fmt_range(data_get($spread, 'minimum'), data_get($spread, 'maximum'), 'deg', 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($spread, 'first_attack'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">First Attack</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($spread, 'first_attack'), 'deg', 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($spread, 'per_attack'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Per Attack</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($spread, 'per_attack'), 'deg', 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($spread, 'decay'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Decay</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($spread, 'decay'), 'deg/s', 0) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        @if ($hasBarrelSpin)
            <details id="barrel-spin-time" class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold" aria-expanded="false" aria-controls="barrel-spin-time-content">
                    Barrel Spin Time
                </summary>
                <div id="barrel-spin-time-content" class="collapse-content">
                    <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                        @if ($barrelUp !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Up</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($barrelUp, 's', 2) }}</dd>
                            </div>
                        @endif
                        @if ($barrelDown !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Down</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($barrelDown, 's', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        @if ($hasHeat)
            <details id="heat" class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold" aria-expanded="false" aria-controls="heat-content">
                    Heat
                </summary>
                <div id="heat-content" class="collapse-content">
                    <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                        @if ($heatOverheatMaxShots !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Overheat Max Shots</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($heatOverheatMaxShots, 0) }}</dd>
                            </div>
                        @endif
                        @if ($heatOverheatMaxTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Overheat Max Time</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($heatOverheatMaxTime, 's', 2) }}</dd>
                            </div>
                        @endif
                        @if ($heatPerShot !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Per Shot</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($heatPerShot, 0) }}</dd>
                            </div>
                        @endif
                        @if ($heatCoolingDelay !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cooling Delay</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($heatCoolingDelay, 's', 2) }}</dd>
                            </div>
                        @endif
                        @if ($heatCoolingPerSecond !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cooling Per Second</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($heatCoolingPerSecond, 0) }}</dd>
                            </div>
                        @endif

                        @if ($heatOverheatCooldown !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Overheat Cooldown</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($heatOverheatCooldown, 's', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        @if ($hasCapacitor)
            <details id="capacitor" class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold" aria-expanded="true" aria-controls="capacitor-content">
                    Capacitor
                </summary>
                <div id="capacitor-content" class="collapse-content">
                    <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                        @if ($capMaxAmmoLoad !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Ammo Load</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($capMaxAmmoLoad, 0) }}</dd>
                            </div>
                        @endif
                        @if ($capRegenPerSecond !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Regen Per Second</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($capRegenPerSecond, 0) }}</dd>
                            </div>
                        @endif
                        @if ($capCooldown !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cooldown</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($capCooldown, 's', 2) }}</dd>
                            </div>
                        @endif
                        @if ($capRequestedAmmoLoad !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Requested Ammo Load</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($capRequestedAmmoLoad, 0) }}</dd>
                            </div>
                        @endif
                        @if ($capCostsPerShot !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Costs Per Shot</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($capCostsPerShot, 0) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        @if ($hasCharge || $hasChargeModifier)
            <details id="charge" class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold" aria-expanded="false" aria-controls="charge-content">
                    Charge
                </summary>
                <div id="charge-content" class="collapse-content">
                    <div class="space-y-4">
                        @if ($hasCharge)
                            <div>
                                <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-2">Charge Timings</h4>
                                <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2">
                                    @if ($chargeTime !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Time</dt>
                                            <dd class="text-sm font-medium">{{ fmt_value_with_unit($chargeTime, 's', 2) }}</dd>
                                        </div>
                                    @endif
                                    @if ($chargeOverchargeTime !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Overcharge Time</dt>
                                            <dd class="text-sm font-medium">{{ fmt_value_with_unit($chargeOverchargeTime, 's', 2) }}</dd>
                                        </div>
                                    @endif
                                    @if ($chargeOverchargedTime !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Overcharged Time</dt>
                                            <dd class="text-sm font-medium">{{ fmt_value_with_unit($chargeOverchargedTime, 's', 2) }}</dd>
                                        </div>
                                    @endif
                                    @if ($chargeCooldownTime !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cooldown Time</dt>
                                            <dd class="text-sm font-medium">{{ fmt_value_with_unit($chargeCooldownTime, 's', 2) }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endif

                        @if ($hasChargeModifier)
                            <div>
                                <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-2">Charge Modifiers</h4>
                                <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2">
                                    @if ($chargeModDamage !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Damage</dt>
                                            <dd class="text-sm font-medium">{{ fmt_or_dash($chargeModDamage, 0) }}</dd>
                                        </div>
                                    @endif
                                    @if ($chargeModFireRate !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fire Rate</dt>
                                            <dd class="text-sm font-medium">{{ fmt_or_dash($chargeModFireRate, 0) }}</dd>
                                        </div>
                                    @endif
                                    @if ($chargeModAmmoSpeed !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ammo Speed</dt>
                                            <dd class="text-sm font-medium">{{ fmt_or_dash($chargeModAmmoSpeed, 0) }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endif
                    </div>
                </div>
            </details>
        @endif
    </div>
</div>
