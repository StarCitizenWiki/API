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
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Personal Weapon</h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Class</dt>
                <dd class="text-sm font-semibold text-base-content">{{ $class ?? '—' }} {{ $type ?? '—' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Capacity</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($capacity, 'rounds', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Range</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($range, 'm', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Fire Mode</dt>
                <dd class="text-sm font-semibold text-base-content">{{ $fireMode ?? '—' }}</dd>
            </div>
        </dl>

        @if ($hasDamage)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Damage Stats
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($dpsTotal !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">DPS Total</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($dpsTotal, '', 0) }}</dd>
                            </div>
                        @endif
                        @if ($alphaTotal !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Alpha Total</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($alphaTotal, '', 0) }}</dd>
                            </div>
                        @endif
                        @if ($maximum !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Maximum</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($maximum, '', 0) }} per magazine</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($hasFireRate)
                        <h4 class="text-xs font-medium uppercase tracking-wide text-muted mt-4 mb-2">Fire Rate</h4>
                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                            @if ($rpm !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">RPM</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($rpm, '/min', 0) }}</dd>
                                </div>
                            @endif
                            @if ($pelletsPerShot !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Pellets per Shot</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($pelletsPerShot, '', 0) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif

                    @if ($nonZeroDps !== [])
                        <h4 class="text-xs font-medium uppercase tracking-wide text-muted mt-4 mb-2">DPS Breakdown</h4>
                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                            @if (data_get($nonZeroDps, 'physical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Physical</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($nonZeroDps, 'physical'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'energy'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Energy</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($nonZeroDps, 'energy'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'distortion'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Distortion</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($nonZeroDps, 'distortion'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'thermal'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Thermal</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($nonZeroDps, 'thermal'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'biochemical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Biochemical</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($nonZeroDps, 'biochemical'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroDps, 'stun'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Stun</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($nonZeroDps, 'stun'), '', 0) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif

                    @if ($nonZeroAlpha !== [])
                        <h4 class="text-xs font-medium uppercase tracking-wide text-muted mt-4 mb-2">Alpha Breakdown</h4>
                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                            @if (data_get($nonZeroAlpha, 'physical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Physical</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($nonZeroAlpha, 'physical'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'energy'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Energy</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($nonZeroAlpha, 'energy'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'distortion'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Distortion</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($nonZeroAlpha, 'distortion'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'thermal'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Thermal</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($nonZeroAlpha, 'thermal'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'biochemical'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Biochemical</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($nonZeroAlpha, 'biochemical'), '', 0) }}</dd>
                                </div>
                            @endif
                            @if (data_get($nonZeroAlpha, 'stun'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Stun</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($nonZeroAlpha, 'stun'), '', 0) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
            </details>
        @endif

        @if ($hasSpreadSection)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Spread
                </summary>
                    @if (is_array($spread) && $spread !== [])
                        <h4 class="text-xs font-medium uppercase tracking-wide text-muted mb-2">Hip-fire Spread</h4>
                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                            @if (data_get($spread, 'minimum'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Minimum</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($spread, 'minimum'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                            @if (data_get($spread, 'maximum'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Maximum</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($spread, 'maximum'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                            @if (data_get($spread, 'first_attack'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">First Attack</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($spread, 'first_attack'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                            @if (data_get($spread, 'per_attack'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Per Attack</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($spread, 'per_attack'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                            @if (data_get($spread, 'decay'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Decay</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($spread, 'decay'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif

                    @if (is_array($adsSpread) && $adsSpread !== [])
                        <h4 class="text-xs font-medium uppercase tracking-wide text-muted mt-4 mb-2">ADS Spread</h4>
                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                            @if (data_get($adsSpread, 'minimum'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Minimum</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($adsSpread, 'minimum'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                            @if (data_get($adsSpread, 'maximum'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Maximum</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($adsSpread, 'maximum'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                            @if (data_get($adsSpread, 'first_attack'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">First Attack</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($adsSpread, 'first_attack'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                            @if (data_get($adsSpread, 'per_attack'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Per Attack</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($adsSpread, 'per_attack'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                            @if (data_get($adsSpread, 'decay'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Decay</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($adsSpread, 'decay'), 'deg', 1) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
            </details>
        @endif

        @if ($hasChargeSection)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Charge
                </summary>
                    @if (is_array($charge) && $charge !== [])
                        <h4 class="text-xs font-medium uppercase tracking-wide text-muted mb-2">Charge Timings</h4>
                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                            @if (data_get($charge, 'time'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Time</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($charge, 'time'), 's', 2) }}</dd>
                                </div>
                            @endif
                            @if (data_get($charge, 'overcharge_time'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Overcharge Time</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($charge, 'overcharge_time'), 's', 2) }}</dd>
                                </div>
                            @endif
                            @if (data_get($charge, 'overcharged_time'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Overcharged Time</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($charge, 'overcharged_time'), 's', 2) }}</dd>
                                </div>
                            @endif
                            @if (data_get($charge, 'cooldown_time'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Cooldown Time</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($charge, 'cooldown_time'), 's', 2) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif

                    @if (is_array($chargeModifier) && $chargeModifier !== [])
                        <h4 class="text-xs font-medium uppercase tracking-wide text-muted mt-4 mb-2">Charge Modifiers</h4>
                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                            @if (data_get($chargeModifier, 'damage'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Damage</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($chargeModifier, 'damage'), '', 2) }}</dd>
                                </div>
                            @endif
                            @if (data_get($chargeModifier, 'fire_rate'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Fire Rate</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($chargeModifier, 'fire_rate'), '', 2) }}</dd>
                                </div>
                            @endif
                            @if (data_get($chargeModifier, 'ammo_speed'))
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Ammo Speed</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($chargeModifier, 'ammo_speed'), '', 2) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
            </details>
        @endif
    </div>
</div>
