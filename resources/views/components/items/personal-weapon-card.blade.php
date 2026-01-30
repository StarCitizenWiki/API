@props([
    'personalWeapon',
])


@php
    $class = data_get($personalWeapon, 'class');
    $type = data_get($personalWeapon, 'type');
    $capacity = data_get($personalWeapon, 'capacity');
    $range = data_get($personalWeapon, 'range');
    $fireMode = data_get($personalWeapon, 'fire_mode');
    $rpm = data_get($personalWeapon, 'rpm');
    $pelletsPerShot = data_get($personalWeapon, 'pellets_per_shot');
    $damage = data_get($personalWeapon, 'damage');
    $spread = data_get($personalWeapon, 'spread');
    $adsSpread = data_get($personalWeapon, 'ads_spread');
    $charge = data_get($personalWeapon, 'charge');
    $chargeModifier = data_get($personalWeapon, 'charge_modifier');

    $dpsTotal = data_get($damage, 'dps_total');
    $alphaTotal = data_get($damage, 'alpha_total');
    $maximum = data_get($damage, 'maximum');
    $dps = data_get($damage, 'dps', []);
    $alpha = data_get($damage, 'alpha', []);

    $nonZeroDps = collect($dps)->filter(fn($v) => $v !== null && $v > 0);
    $nonZeroAlpha = collect($alpha)->filter(fn($v) => $v !== null && $v > 0);

    $hasFireRate = $rpm !== null || $pelletsPerShot !== null;
    $hasDamage = is_array($damage) && $damage !== [];
    $hasSpread = is_array($spread) && $spread !== [] || is_array($adsSpread) && $adsSpread !== [];
    $hasCharge = is_array($charge) && $charge !== [] || is_array($chargeModifier) && $chargeModifier !== [];

    $hasSpreadSection = is_array($spread) && $spread !== [] || is_array($adsSpread) && $adsSpread !== [];
    $hasChargeSection = is_array($charge) && $charge !== [] || is_array($chargeModifier) && $chargeModifier !== [];
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-3">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="pistol" class="size-4 text-primary" />
            <span>Personal Weapon</span>
        </h2>

        <dl class="grid gap-4 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Class</dt>
                <dd class="text-sm font-medium">{{ $class ?? '—' }} {{ $type ?? '—' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Capacity</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit($capacity, 'rounds', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Range</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit($range, 'm', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fire Mode</dt>
                <dd class="text-sm font-medium">{{ $fireMode ?? '—' }}</dd>
            </div>
        </dl>

        @if ($hasDamage)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Damage Stats
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-4 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3">
                        @if ($dpsTotal !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">DPS Total</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($dpsTotal, '', 0) }}</dd>
                            </div>
                        @endif
                        @if ($alphaTotal !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Alpha Total</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($alphaTotal, '', 0) }}</dd>
                            </div>
                        @endif
                        @if ($maximum !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($maximum, '', 0) }} per magazine</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($hasFireRate)
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mt-4 mb-2">Fire Rate</h4>
                        <dl class="grid gap-3 grid-cols-2 sm:grid-cols-2">
                            @if ($rpm !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">RPM</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($rpm, '/min', 0) }}</dd>
                                </div>
                            @endif
                            @if ($pelletsPerShot !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Pellets per Shot</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($pelletsPerShot, '', 0) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif

                    @if ($nonZeroDps !== [])
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mt-4 mb-2">DPS Breakdown</h4>
                        <dl class="grid gap-3 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3">
                            @if (data_get($nonZeroDps, 'physical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($nonZeroDps, 'physical'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'energy'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($nonZeroDps, 'energy'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'distortion'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($nonZeroDps, 'distortion'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'thermal'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($nonZeroDps, 'thermal'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'biochemical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($nonZeroDps, 'biochemical'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'stun'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($nonZeroDps, 'stun'), '', 0) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif

                    @if ($nonZeroAlpha !== [])
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mt-4 mb-2">Alpha Breakdown</h4>
                        <dl class="grid gap-3 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3">
                            @if (data_get($nonZeroAlpha, 'physical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($nonZeroAlpha, 'physical'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'energy'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Energy</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($nonZeroAlpha, 'energy'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'distortion'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($nonZeroAlpha, 'distortion'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'thermal'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thermal</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($nonZeroAlpha, 'thermal'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'biochemical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Biochemical</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($nonZeroAlpha, 'biochemical'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'stun'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stun</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($nonZeroAlpha, 'stun'), '', 0) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
                </div>
            </details>
        @endif

        @if ($hasSpreadSection)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Spread
                </summary>
                <div class="collapse-content">
                    @if (is_array($spread) && $spread !== [])
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-2">Hip-fire Spread</h4>
                        <dl class="grid gap-3 grid-cols-2 sm:grid-cols-2">
                            @if (data_get($spread, 'minimum'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Minimum</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($spread, 'minimum'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                            @if (data_get($spread, 'maximum'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($spread, 'maximum'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                            @if (data_get($spread, 'first_attack'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">First Attack</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($spread, 'first_attack'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                            @if (data_get($spread, 'per_attack'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Per Attack</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($spread, 'per_attack'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                            @if (data_get($spread, 'decay'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Decay</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($spread, 'decay'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif

                    @if (is_array($adsSpread) && $adsSpread !== [])
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mt-4 mb-2">ADS Spread</h4>
                        <dl class="grid gap-3 grid-cols-2 sm:grid-cols-2">
                            @if (data_get($adsSpread, 'minimum'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Minimum</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($adsSpread, 'minimum'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                            @if (data_get($adsSpread, 'maximum'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($adsSpread, 'maximum'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                            @if (data_get($adsSpread, 'first_attack'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">First Attack</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($adsSpread, 'first_attack'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                            @if (data_get($adsSpread, 'per_attack'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Per Attack</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($adsSpread, 'per_attack'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                            @if (data_get($adsSpread, 'decay'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Decay</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($adsSpread, 'decay'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
                </div>
            </details>
        @endif

        @if ($hasChargeSection)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Charge
                </summary>
                <div class="collapse-content">
                    @if (is_array($charge) && $charge !== [])
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-2">Charge Timings</h4>
                        <dl class="grid gap-3 grid-cols-2 sm:grid-cols-2">
                            @if (data_get($charge, 'time'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Time</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($charge, 'time'), 's', 2) }}</dd>
                                </div>
                            @endif
                            @if (data_get($charge, 'overcharge_time'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Overcharge Time</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($charge, 'overcharge_time'), 's', 2) }}</dd>
                                </div>
                            @endif
                            @if (data_get($charge, 'overcharged_time'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Overcharged Time</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($charge, 'overcharged_time'), 's', 2) }}</dd>
                                </div>
                            @endif
                            @if (data_get($charge, 'cooldown_time'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cooldown Time</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($charge, 'cooldown_time'), 's', 2) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif

                    @if (is_array($chargeModifier) && $chargeModifier !== [])
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mt-4 mb-2">Charge Modifiers</h4>
                        <dl class="grid gap-3 grid-cols-2 sm:grid-cols-2">
                            @if (data_get($chargeModifier, 'damage'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Damage</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($chargeModifier, 'damage'), '', 2) }}</dd>
                                </div>
                            @endif
                            @if (data_get($chargeModifier, 'fire_rate'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fire Rate</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($chargeModifier, 'fire_rate'), '', 2) }}</dd>
                                </div>
                            @endif
                            @if (data_get($chargeModifier, 'ammo_speed'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ammo Speed</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($chargeModifier, 'ammo_speed'), '', 2) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
                </div>
            </details>
        @endif
    </div>
</div>
