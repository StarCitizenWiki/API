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

    $nonZeroDps = array_filter($dps, static fn($v) => $v !== null && $v > 0);
    $nonZeroAlpha = array_filter($alpha, static fn($v) => $v !== null && $v > 0);

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

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="crosshair" class="size-4 text-primary" />
            <span>Vehicle Weapon Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Class</dt>
                <dd class="text-sm font-medium">{{ $class ?? '-' }} {{ $type ?? '-' }}</dd>
            </div>
            @if ($capacity !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Capacity</dt>
                    <dd class="text-sm font-medium">{{ (int)$capacity }} rounds</dd>
                </div>
            @endif
            @if ($range !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Range</dt>
                    <dd class="text-sm font-medium">{{ (int)$range }} meters</dd>
                </div>
            @endif
            @if ($rpm !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">RPM</dt>
                    <dd class="text-sm font-medium">{{ (int)$rpm }} RPM</dd>
                </div>
            @endif
        </dl>

        @if ($hasDamage)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox"/>
                <div class="collapse-title text-sm font-semibold">Damage Stats</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($sustained60s !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Sustained 60s</dt>
                                <dd class="text-sm font-medium">{{ (int)$sustained60s }}</dd>
                            </div>
                        @endif
                        @if ($burst !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Burst</dt>
                                <dd class="text-sm font-medium">{{ (int)$burst }}</dd>
                            </div>
                        @endif
                        @if ($alphaTotal !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Alpha Total</dt>
                                <dd class="text-sm font-medium">{{ (int)$alphaTotal }}</dd>
                            </div>
                        @endif
                        @if ($maximum !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum</dt>
                                <dd class="text-sm font-medium">{{ (int)$maximum }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($nonZeroDps !== [])
                        <div class="collapse collapse-arrow border border-base-200 bg-base-100 mt-3">
                            <input type="checkbox"/>
                            <div class="collapse-title text-xs font-semibold">DPS Breakdown</div>
                            <div class="collapse-content">
                                <dl class="grid gap-3 sm:grid-cols-2">
                                    @if (data_get($nonZeroDps, 'physical'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($nonZeroDps, 'physical') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($nonZeroDps, 'energy'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($nonZeroDps, 'energy') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($nonZeroDps, 'distortion'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($nonZeroDps, 'distortion') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($nonZeroDps, 'thermal'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($nonZeroDps, 'thermal') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($nonZeroDps, 'biochemical'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($nonZeroDps, 'biochemical') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($nonZeroDps, 'stun'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($nonZeroDps, 'stun') }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    @endif

                    @if ($nonZeroAlpha !== [])
                        <div class="collapse collapse-arrow border border-base-200 bg-base-100 mt-3">
                            <input type="checkbox"/>
                            <div class="collapse-title text-xs font-semibold">Alpha Breakdown</div>
                            <div class="collapse-content">
                                <dl class="grid gap-3 sm:grid-cols-2">
                                    @if (data_get($nonZeroAlpha, 'physical'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($nonZeroAlpha, 'physical') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($nonZeroAlpha, 'energy'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($nonZeroAlpha, 'energy') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($nonZeroAlpha, 'distortion'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($nonZeroAlpha, 'distortion') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($nonZeroAlpha, 'thermal'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($nonZeroAlpha, 'thermal') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($nonZeroAlpha, 'biochemical'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($nonZeroAlpha, 'biochemical') }}</dd>
                                        </div>
                                    @endif
                                    @if (data_get($nonZeroAlpha, 'stun'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                            <dd class="text-sm font-medium">{{ (int)data_get($nonZeroAlpha, 'stun') }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if ($hasSpread)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox"/>
                <div class="collapse-title text-sm font-semibold">Spread</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if (data_get($spread, 'minimum'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Minimum</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($spread, 'minimum') }}</dd>
                            </div>
                        @endif
                        @if (data_get($spread, 'maximum'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($spread, 'maximum') }}</dd>
                            </div>
                        @endif
                        @if (data_get($spread, 'first_attack'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">First Attack</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($spread, 'first_attack') }}</dd>
                            </div>
                        @endif
                        @if (data_get($spread, 'per_attack'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Per Attack</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($spread, 'per_attack') }}</dd>
                            </div>
                        @endif
                        @if (data_get($spread, 'decay'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Decay</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($spread, 'decay') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasBarrelSpin)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox"/>
                <div class="collapse-title text-sm font-semibold">Barrel Spin Time</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($barrelUp !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Up</dt>
                                <dd class="text-sm font-medium">{{ (int)$barrelUp }}s</dd>
                            </div>
                        @endif
                        @if ($barrelDown !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Down</dt>
                                <dd class="text-sm font-medium">{{ (int)$barrelDown }}s</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasHeat)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox"/>
                <div class="collapse-title text-sm font-semibold">Heat</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($heatPerShot !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Per Shot</dt>
                                <dd class="text-sm font-medium">{{ (int)$heatPerShot }}</dd>
                            </div>
                        @endif
                        @if ($heatCoolingDelay !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cooling Delay</dt>
                                <dd class="text-sm font-medium">{{ (int)$heatCoolingDelay }}s</dd>
                            </div>
                        @endif
                        @if ($heatCoolingPerSecond !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cooling Per Second</dt>
                                <dd class="text-sm font-medium">{{ (int)$heatCoolingPerSecond }}</dd>
                            </div>
                        @endif
                        @if ($heatOverheatMaxShots !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Overheat Max Shots</dt>
                                <dd class="text-sm font-medium">{{ (int)$heatOverheatMaxShots }}</dd>
                            </div>
                        @endif
                        @if ($heatOverheatMaxTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Overheat Max Time</dt>
                                <dd class="text-sm font-medium">{{ (int)$heatOverheatMaxTime }}s</dd>
                            </div>
                        @endif
                        @if ($heatOverheatCooldown !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Overheat Cooldown</dt>
                                <dd class="text-sm font-medium">{{ (int)$heatOverheatCooldown }}s</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasCapacitor)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox"/>
                <div class="collapse-title text-sm font-semibold">Capacitor</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($capMaxAmmoLoad !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Ammo Load</dt>
                                <dd class="text-sm font-medium">{{ (int)$capMaxAmmoLoad }}</dd>
                            </div>
                        @endif
                        @if ($capRegenPerSecond !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Regen Per Second</dt>
                                <dd class="text-sm font-medium">{{ (int)$capRegenPerSecond }}</dd>
                            </div>
                        @endif
                        @if ($capCooldown !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cooldown</dt>
                                <dd class="text-sm font-medium">{{ (int)$capCooldown }}s</dd>
                            </div>
                        @endif
                        @if ($capRequestedAmmoLoad !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Requested Ammo Load</dt>
                                <dd class="text-sm font-medium">{{ (int)$capRequestedAmmoLoad }}</dd>
                            </div>
                        @endif
                        @if ($capCostsPerShot !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Costs Per Shot</dt>
                                <dd class="text-sm font-medium">{{ (int)$capCostsPerShot }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasCharge || $hasChargeModifier)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox"/>
                <div class="collapse-title text-sm font-semibold">Charge</div>
                <div class="collapse-content space-y-4">
                    @if ($hasCharge)
                        <div>
                            <h3 class="mb-3 text-sm font-semibold">Charge Timings</h3>
                            <dl class="grid gap-3 sm:grid-cols-2">
                                @if ($chargeTime !== null)
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Time</dt>
                                        <dd class="text-sm font-medium">{{ (int)$chargeTime }}s</dd>
                                    </div>
                                @endif
                                @if ($chargeOverchargeTime !== null)
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Overcharge Time</dt>
                                        <dd class="text-sm font-medium">{{ (int)$chargeOverchargeTime }}s</dd>
                                    </div>
                                @endif
                                @if ($chargeOverchargedTime !== null)
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Overcharged Time</dt>
                                        <dd class="text-sm font-medium">{{ (int)$chargeOverchargedTime }}s</dd>
                                    </div>
                                @endif
                                @if ($chargeCooldownTime !== null)
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cooldown Time</dt>
                                        <dd class="text-sm font-medium">{{ (int)$chargeCooldownTime }}s</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif

                    @if ($hasChargeModifier)
                        <div>
                            <h3 class="mb-3 text-sm font-semibold">Charge Modifiers</h3>
                            <dl class="grid gap-3 sm:grid-cols-2">
                                @if ($chargeModDamage !== null)
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Damage</dt>
                                        <dd class="text-sm font-medium">{{ (int)$chargeModDamage }}</dd>
                                    </div>
                                @endif
                                @if ($chargeModFireRate !== null)
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fire Rate</dt>
                                        <dd class="text-sm font-medium">{{ (int)$chargeModFireRate }}</dd>
                                    </div>
                                @endif
                                @if ($chargeModAmmoSpeed !== null)
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ammo Speed</dt>
                                        <dd class="text-sm font-medium">{{ (int)$chargeModAmmoSpeed }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
