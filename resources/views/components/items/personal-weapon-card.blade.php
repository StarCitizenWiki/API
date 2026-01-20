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

    $nonZeroDps = array_filter($dps, fn($v) => $v !== null && $v > 0);
    $nonZeroAlpha = array_filter($alpha, fn($v) => $v !== null && $v > 0);

    $hasFireRate = $rpm !== null || $pelletsPerShot !== null;
    $hasDamage = is_array($damage) && $damage !== [];
    $hasSpread = is_array($spread) && $spread !== [] || is_array($adsSpread) && $adsSpread !== [];
    $hasCharge = is_array($charge) && $charge !== [] || is_array($chargeModifier) && $chargeModifier !== [];
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="crosshair" class="size-4 text-primary" />
            <span>Personal Weapon</span>
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
                    <dd class="text-sm font-medium">{{ (int)$range }} m</dd>
                </div>
            @endif
            @if ($fireMode !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fire Mode</dt>
                    <dd class="text-sm font-medium">{{ $fireMode }}</dd>
                </div>
            @endif
        </dl>

        @if ($hasFireRate)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Fire Rate</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($rpm !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">RPM</dt>
                                <dd class="text-sm font-medium">{{ (int)$rpm }} RPM</dd>
                            </div>
                        @endif
                        @if ($pelletsPerShot !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Pellets per Shot</dt>
                                <dd class="text-sm font-medium">{{ (int)$pelletsPerShot }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasDamage)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Damage Stats</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($dpsTotal !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">DPS Total</dt>
                                <dd class="text-sm font-medium">{{ (int)$dpsTotal }}</dd>
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
                                <dd class="text-sm font-medium">{{ (int)$maximum }} per magazine</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($nonZeroDps !== [])
                        <div class="collapse collapse-arrow border border-base-200 bg-base-100 mt-3">
                            <input type="checkbox" />
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
                            <input type="checkbox" />
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
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Spread</div>
                <div class="collapse-content space-y-4">
                    @if (is_array($spread) && $spread !== [])
                        <div>
                            <h3 class="mb-3 text-sm font-semibold">Hip-fire Spread</h3>
                            <dl class="grid gap-3 sm:grid-cols-2">
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
                    @endif

                    @if (is_array($adsSpread) && $adsSpread !== [])
                        <div>
                            <h3 class="mb-3 text-sm font-semibold">ADS Spread</h3>
                            <dl class="grid gap-3 sm:grid-cols-2">
                                @if (data_get($adsSpread, 'minimum'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Minimum</dt>
                                        <dd class="text-sm font-medium">{{ (int)data_get($adsSpread, 'minimum') }}</dd>
                                    </div>
                                @endif
                                @if (data_get($adsSpread, 'maximum'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum</dt>
                                        <dd class="text-sm font-medium">{{ (int)data_get($adsSpread, 'maximum') }}</dd>
                                    </div>
                                @endif
                                @if (data_get($adsSpread, 'first_attack'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">First Attack</dt>
                                        <dd class="text-sm font-medium">{{ (int)data_get($adsSpread, 'first_attack') }}</dd>
                                    </div>
                                @endif
                                @if (data_get($adsSpread, 'per_attack'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Per Attack</dt>
                                        <dd class="text-sm font-medium">{{ (int)data_get($adsSpread, 'per_attack') }}</dd>
                                    </div>
                                @endif
                                @if (data_get($adsSpread, 'decay'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Decay</dt>
                                        <dd class="text-sm font-medium">{{ (int)data_get($adsSpread, 'decay') }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if ($hasCharge)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Charge</div>
                <div class="collapse-content space-y-4">
                    @if (is_array($charge) && $charge !== [])
                        <div>
                            <h3 class="mb-3 text-sm font-semibold">Charge Timings</h3>
                            <dl class="grid gap-3 sm:grid-cols-2">
                                @if (data_get($charge, 'time'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Time</dt>
                                        <dd class="text-sm font-medium">{{ (int)data_get($charge, 'time') }}s</dd>
                                    </div>
                                @endif
                                @if (data_get($charge, 'overcharge_time'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Overcharge Time</dt>
                                        <dd class="text-sm font-medium">{{ (int)data_get($charge, 'overcharge_time') }}s</dd>
                                    </div>
                                @endif
                                @if (data_get($charge, 'overcharged_time'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Overcharged Time</dt>
                                        <dd class="text-sm font-medium">{{ (int)data_get($charge, 'overcharged_time') }}s</dd>
                                    </div>
                                @endif
                                @if (data_get($charge, 'cooldown_time'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cooldown Time</dt>
                                        <dd class="text-sm font-medium">{{ (int)data_get($charge, 'cooldown_time') }}s</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif

                    @if (is_array($chargeModifier) && $chargeModifier !== [])
                        <div>
                            <h3 class="mb-3 text-sm font-semibold">Charge Modifiers</h3>
                            <dl class="grid gap-3 sm:grid-cols-2">
                                @if (data_get($chargeModifier, 'damage'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Damage</dt>
                                        <dd class="text-sm font-medium">{{ (int)data_get($chargeModifier, 'damage') }}</dd>
                                    </div>
                                @endif
                                @if (data_get($chargeModifier, 'fire_rate'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fire Rate</dt>
                                        <dd class="text-sm font-medium">{{ (int)data_get($chargeModifier, 'fire_rate') }}</dd>
                                    </div>
                                @endif
                                @if (data_get($chargeModifier, 'ammo_speed'))
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ammo Speed</dt>
                                        <dd class="text-sm font-medium">{{ (int)data_get($chargeModifier, 'ammo_speed') }}</dd>
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
