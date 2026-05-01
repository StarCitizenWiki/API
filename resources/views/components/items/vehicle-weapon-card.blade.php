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
        <h2 class="card-title text-base">Vehicle Weapon</h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Class</dt>
                <dd class="text-sm font-semibold text-base-content">{{ $class ?? '-' }} {{ $type ?? '-' }}</dd>
            </div>
            @if ($capacity !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Capacity</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ $capacity === 0 ? 'Infinite' : fmt_value_with_unit($capacity, 'rounds', 0) }}</dd>
                </div>
            @endif
            @if ($range !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Range</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($range, 'm', 0) }}</dd>
                </div>
            @endif
            @if ($rpm !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">RPM</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($rpm, 'RPM', 0) }}</dd>
                </div>
            @endif
        </dl>

        @if ($hasDamage)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Damage Stats
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
{{--                        @if ($sustained60s !== null)--}}
{{--                            <div class="space-y-1">--}}
{{--                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Sustained 60s</dt>--}}
{{--                                <dd class="text-sm font-semibold text-base-content">{{ fmt_compact($sustained60s, 2) }}</dd>--}}
{{--                            </div>--}}
{{--                        @endif--}}
                        @if ($alphaTotal !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Alpha</dt>
                                <dd class="text-sm font-semibold text-base-content text-info">{{ fmt_compact($alphaTotal, 0) }}</dd>
                            </div>
                        @endif
                        @if ($burst !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Burst</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_compact($burst, 2) }}</dd>
                            </div>
                        @endif
                        @if ($maximum !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Maximum</dt>
                                <dd class="text-sm font-semibold text-base-content" title="{{ $maximum }}">{{ $maximum === 'Infinite' ? $maximum : fmt_compact($maximum) }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($nonZeroDps !== [])
                        <div class="divider"></div>
                        <h4 class="text-xs font-medium uppercase tracking-wide text-muted mb-2">DPS Breakdown</h4>
                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                            @if (data_get($nonZeroDps, 'physical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Physical</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroDps, 'physical'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'energy'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Energy</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroDps, 'energy'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'distortion'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Distortion</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroDps, 'distortion'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'thermal'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Thermal</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroDps, 'thermal'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'biochemical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Biochemical</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroDps, 'biochemical'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'stun'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Stun</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroDps, 'stun'), 0) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif

                    @if (!empty($nonZeroAlpha))
                        <h4 class="text-xs font-medium uppercase tracking-wide text-muted mb-2 mt-4">Alpha Breakdown</h4>
                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                            @if (data_get($nonZeroAlpha, 'physical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Physical</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroAlpha, 'physical'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'energy'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Energy</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroAlpha, 'energy'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'distortion'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Distortion</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroAlpha, 'distortion'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'thermal'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Thermal</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroAlpha, 'thermal'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'biochemical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Biochemical</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroAlpha, 'biochemical'), 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'stun'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Stun</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash(data_get($nonZeroAlpha, 'stun'), 0) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
            </details>
        @endif

        @if ($hasSpread)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Spread
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if (data_get($spread, 'minimum'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Min/Max</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_range(data_get($spread, 'minimum'), data_get($spread, 'maximum'), 'deg', 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($spread, 'first_attack'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">First Attack</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($spread, 'first_attack'), 'deg', 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($spread, 'per_attack'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Per Attack</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($spread, 'per_attack'), 'deg', 0) }}</dd>
                            </div>
                        @endif
                        @if (data_get($spread, 'decay'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Decay</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($spread, 'decay'), 'deg/s', 0) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasBarrelSpin)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Barrel Spin Time
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($barrelUp !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Up</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($barrelUp, 's', 2) }}</dd>
                            </div>
                        @endif
                        @if ($barrelDown !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Down</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($barrelDown, 's', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasHeat)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Heat
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($heatOverheatMaxShots !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Overheat Max Shots</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($heatOverheatMaxShots, 0) }}</dd>
                            </div>
                        @endif
                        @if ($heatOverheatMaxTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Overheat Max Time</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($heatOverheatMaxTime, 's', 2) }}</dd>
                            </div>
                        @endif
                        @if ($heatPerShot !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Per Shot</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($heatPerShot, 0) }}</dd>
                            </div>
                        @endif
                        @if ($heatCoolingDelay !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Cooling Delay</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($heatCoolingDelay, 's', 2) }}</dd>
                            </div>
                        @endif
                        @if ($heatCoolingPerSecond !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Cooling Per Second</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($heatCoolingPerSecond, 0) }}</dd>
                            </div>
                        @endif

                        @if ($heatOverheatCooldown !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Overheat Cooldown</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($heatOverheatCooldown, 's', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasCapacitor)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Capacitor
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($capMaxAmmoLoad !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Ammo Load</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($capMaxAmmoLoad, 0) }}</dd>
                            </div>
                        @endif
                        @if ($capRegenPerSecond !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Regen Per Second</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($capRegenPerSecond, 0) }}</dd>
                            </div>
                        @endif
                        @if ($capCooldown !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Cooldown</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($capCooldown, 's', 2) }}</dd>
                            </div>
                        @endif
                        @if ($capRequestedAmmoLoad !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Requested Ammo Load</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($capRequestedAmmoLoad, 0) }}</dd>
                            </div>
                        @endif
                        @if ($capCostsPerShot !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Costs Per Shot</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($capCostsPerShot, 0) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasCharge || $hasChargeModifier)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Charge
                </summary>
                    <div class="space-y-4">
                        @if ($hasCharge)
                            <div>
                                <h4 class="text-xs font-medium uppercase tracking-wide text-muted mb-2">Charge Timings</h4>
                                <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                                    @if ($chargeTime !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Time</dt>
                                            <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($chargeTime, 's', 2) }}</dd>
                                        </div>
                                    @endif
                                    @if ($chargeOverchargeTime !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Overcharge Time</dt>
                                            <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($chargeOverchargeTime, 's', 2) }}</dd>
                                        </div>
                                    @endif
                                    @if ($chargeOverchargedTime !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Overcharged Time</dt>
                                            <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($chargeOverchargedTime, 's', 2) }}</dd>
                                        </div>
                                    @endif
                                    @if ($chargeCooldownTime !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Cooldown Time</dt>
                                            <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($chargeCooldownTime, 's', 2) }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endif

                        @if ($hasChargeModifier)
                            <div>
                                <h4 class="text-xs font-medium uppercase tracking-wide text-muted mb-2">Charge Modifiers</h4>
                                <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                                    @if ($chargeModDamage !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Damage</dt>
                                            <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($chargeModDamage, 0) }}</dd>
                                        </div>
                                    @endif
                                    @if ($chargeModFireRate !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Fire Rate</dt>
                                            <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($chargeModFireRate, 0) }}</dd>
                                        </div>
                                    @endif
                                    @if ($chargeModAmmoSpeed !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Ammo Speed</dt>
                                            <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($chargeModAmmoSpeed, 0) }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endif
                    </div>
            </details>
        @endif
    </div>
</div>
