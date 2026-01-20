@props([
    'weaponModifier',
])

@php
    $activateOnAttach = data_get($weaponModifier, 'activate_on_attach');
    $ignoreWear = data_get($weaponModifier, 'ignore_wear');

    $base = data_get($weaponModifier, 'base', []);
    $baseMuzzleFlashMultiplier = data_get($base, 'muzzle_flash_multiplier');
    $baseMuzzleFlashChange = data_get($base, 'muzzle_flash_change');
    $baseFireRateMultiplier = data_get($base, 'fire_rate_multiplier');
    $baseFireRateChange = data_get($base, 'fire_rate_change');
    $baseDamageMultiplier = data_get($base, 'damage_multiplier');
    $baseDamageChange = data_get($base, 'damage_change');
    $baseProjectileSpeedMultiplier = data_get($base, 'projectile_speed_multiplier');
    $baseProjectileSpeedChange = data_get($base, 'projectile_speed_change');
    $baseAmmoCostMultiplier = data_get($base, 'ammo_cost_multiplier');
    $baseAmmoCostChange = data_get($base, 'ammo_cost_change');
    $baseHeatGenerationMultiplier = data_get($base, 'heat_generation_multiplier');
    $baseHeatGenerationChange = data_get($base, 'heat_generation_change');
    $baseSoundRadiusMultiplier = data_get($base, 'sound_radius_multiplier');
    $baseSoundRadiusChange = data_get($base, 'sound_radius_change');
    $baseChargeTimeMultiplier = data_get($base, 'charge_time_multiplier');
    $baseChargeTimeChange = data_get($base, 'charge_time_change');
    $hasBase = is_array($base) && array_filter($base, fn($v) => $v !== null);

    $recoil = data_get($weaponModifier, 'recoil', []);
    $recoilDecayMultiplier = data_get($recoil, 'decay_multiplier');
    $recoilDecayChange = data_get($recoil, 'decay_change');
    $recoilMultiplier = data_get($recoil, 'multiplier');
    $recoilMultiplierChange = data_get($recoil, 'multiplier_change');
    $hasRecoil = is_array($recoil) && array_filter($recoil, fn($v) => $v !== null);

    $spread = data_get($weaponModifier, 'spread', []);
    $spreadMinMultiplier = data_get($spread, 'min_multiplier');
    $spreadMinChange = data_get($spread, 'min_change');
    $spreadMaxMultiplier = data_get($spread, 'max_multiplier');
    $spreadMaxChange = data_get($spread, 'max_change');
    $spreadFirstAttackMultiplier = data_get($spread, 'first_attack_multiplier');
    $spreadFirstAttackChange = data_get($spread, 'first_attack_change');
    $spreadPerAttackMultiplier = data_get($spread, 'per_attack_multiplier');
    $spreadPerAttackChange = data_get($spread, 'per_attack_change');
    $spreadDecayMultiplier = data_get($spread, 'decay_multiplier');
    $spreadDecayChange = data_get($spread, 'decay_change');
    $hasSpread = is_array($spread) && array_filter($spread, fn($v) => $v !== null);

    $aim = data_get($weaponModifier, 'aim', []);
    $aimZoomScale = data_get($aim, 'zoom_scale');
    $aimSecondZoomScale = data_get($aim, 'second_zoom_scale');
    $aimZoomTimeScale = data_get($aim, 'zoom_time_scale');
    $aimZoomTimeChange = data_get($aim, 'zoom_time_change');
    $aimHideWeaponInAds = data_get($aim, 'hide_weapon_in_ads');
    $aimFstopMultiplier = data_get($aim, 'fstop_multiplier');
    $hasAim = is_array($aim) && array_filter($aim, fn($v) => $v !== null);

    $regen = data_get($weaponModifier, 'regen', []);
    $regenPowerRatioMultiplier = data_get($regen, 'power_ratio_multiplier');
    $regenMaxAmmoLoadMultiplier = data_get($regen, 'max_ammo_load_multiplier');
    $regenMaxRegenPerSecMultiplier = data_get($regen, 'max_regen_per_sec_multiplier');
    $hasRegen = is_array($regen) && array_filter($regen, fn($v) => $v !== null);

    $salvage = data_get($weaponModifier, 'salvage', []);
    $salvageSpeedMultiplier = data_get($salvage, 'salvage_speed_multiplier');
    $salvageRadiusMultiplier = data_get($salvage, 'radius_multiplier');
    $salvageExtractionEfficiency = data_get($salvage, 'extraction_efficiency');
    $hasSalvage = is_array($salvage) && array_filter($salvage, fn($v) => $v !== null);

    $zeroing = data_get($weaponModifier, 'zeroing', []);
    $zeroingDefaultRange = data_get($zeroing, 'default_range');
    $zeroingMaxRange = data_get($zeroing, 'max_range');
    $zeroingRangeIncrement = data_get($zeroing, 'range_increment');
    $zeroingAutoZeroingTime = data_get($zeroing, 'auto_zeroing_time');
    $hasZeroing = is_array($zeroing) && array_filter($zeroing, fn($v) => $v !== null);
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="puzzle" class="size-4 text-primary" />
            <span>Weapon Modifier Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($activateOnAttach !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Activate On Attach</dt>
                    <dd class="text-sm font-medium">{{ $activateOnAttach ? 'Yes' : 'No' }}</dd>
                </div>
            @endif
            @if ($ignoreWear !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ignore Wear</dt>
                    <dd class="text-sm font-medium">{{ $ignoreWear ? 'Yes' : 'No' }}</dd>
                </div>
            @endif
        </dl>

        @if ($hasBase)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Base</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($baseMuzzleFlashMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Muzzle Flash Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baseMuzzleFlashMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($baseMuzzleFlashChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Muzzle Flash</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baseMuzzleFlashChange * 100, 2) }}%</dd>
                            </div>
                        @endif
                        @if ($baseFireRateMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fire Rate Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baseFireRateMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($baseFireRateChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fire Rate</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baseFireRateChange * 100, 2) }}%</dd>
                            </div>
                        @endif
                        @if ($baseDamageMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Damage Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baseDamageMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($baseDamageChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Damage</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baseDamageChange * 100, 2) }}%</dd>
                            </div>
                        @endif
                        @if ($baseProjectileSpeedMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Projectile Speed Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baseProjectileSpeedMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($baseProjectileSpeedChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Projectile Speed</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baseProjectileSpeedChange * 100, 2) }}%</dd>
                            </div>
                        @endif
                        @if ($baseAmmoCostMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ammo Cost Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baseAmmoCostMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($baseAmmoCostChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ammo Cost</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baseAmmoCostChange * 100, 2) }}%</dd>
                            </div>
                        @endif
                        @if ($baseHeatGenerationMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Heat Generation Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baseHeatGenerationMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($baseHeatGenerationChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Heat Generation</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baseHeatGenerationChange * 100, 2) }}%</dd>
                            </div>
                        @endif
                        @if ($baseSoundRadiusMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Sound Radius Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baseSoundRadiusMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($baseSoundRadiusChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Sound Radius</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baseSoundRadiusChange * 100, 2) }}%</dd>
                            </div>
                        @endif
                        @if ($baseChargeTimeMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Charge Time Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baseChargeTimeMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($baseChargeTimeChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Charge Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baseChargeTimeChange * 100, 2) }}%</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasRecoil)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Recoil</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($recoilDecayMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Decay Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$recoilDecayMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($recoilDecayChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Decay</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$recoilDecayChange * 100, 2) }}%</dd>
                            </div>
                        @endif
                        @if ($recoilMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$recoilMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($recoilMultiplierChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$recoilMultiplierChange * 100, 2) }}%</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasSpread)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Spread</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($spreadMinMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Min Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$spreadMinMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($spreadMinChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Min</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$spreadMinChange * 100, 2) }}%</dd>
                            </div>
                        @endif
                        @if ($spreadMaxMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$spreadMaxMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($spreadMaxChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$spreadMaxChange * 100, 2) }}%</dd>
                            </div>
                        @endif
                        @if ($spreadFirstAttackMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">First Attack Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$spreadFirstAttackMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($spreadFirstAttackChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">First Attack</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$spreadFirstAttackChange * 100, 2) }}%</dd>
                            </div>
                        @endif
                        @if ($spreadPerAttackMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Per Attack Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$spreadPerAttackMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($spreadPerAttackChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Per Attack</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$spreadPerAttackChange, 2) }}</dd>
                            </div>
                        @endif
                        @if ($spreadDecayMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Decay Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$spreadDecayMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($spreadDecayChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Decay</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$spreadDecayChange, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasAim)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Aim</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($aimZoomScale !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Zoom Scale</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$aimZoomScale, 2) }}</dd>
                            </div>
                        @endif
                        @if ($aimSecondZoomScale !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Second Zoom Scale</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$aimSecondZoomScale, 2) }}</dd>
                            </div>
                        @endif
                        @if ($aimZoomTimeScale !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Zoom Time Scale</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$aimZoomTimeScale, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($aimZoomTimeChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Zoom Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$aimZoomTimeChange, 2) }}</dd>
                            </div>
                        @endif
                        @if ($aimHideWeaponInAds !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Hide Weapon In ADS</dt>
                                <dd class="text-sm font-medium">{{ $aimHideWeaponInAds ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($aimFstopMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">F-Stop Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$aimFstopMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasRegen)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Regen</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($regenPowerRatioMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Power Ratio Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$regenPowerRatioMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($regenMaxAmmoLoadMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Ammo Load Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$regenMaxAmmoLoadMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($regenMaxRegenPerSecMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Regen Per Sec Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$regenMaxRegenPerSecMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasSalvage)
            <div class="collapse collapse-arrow border base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Salvage</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($salvageSpeedMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Salvage Speed Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$salvageSpeedMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($salvageRadiusMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Radius Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$salvageRadiusMultiplier, 2) }}x</dd>
                            </div>
                        @endif
                        @if ($salvageExtractionEfficiency !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Extraction Efficiency</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$salvageExtractionEfficiency, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasZeroing)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Zeroing</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($zeroingDefaultRange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Default Range</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$zeroingDefaultRange, 2) }} m</dd>
                            </div>
                        @endif
                        @if ($zeroingMaxRange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Range</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$zeroingMaxRange, 2) }} m</dd>
                            </div>
                        @endif
                        @if ($zeroingRangeIncrement !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Range Increment</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$zeroingRangeIncrement, 2) }} m</dd>
                            </div>
                        @endif
                        @if ($zeroingAutoZeroingTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Auto Zeroing Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$zeroingAutoZeroingTime, 2) }} s</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif
    </div>
</div>
