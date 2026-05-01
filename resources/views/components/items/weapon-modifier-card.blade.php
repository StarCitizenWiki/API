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
    $hasBase = is_array($base) && collect($base)->filter(fn($v) => $v !== null)->isNotEmpty();

    $recoil = data_get($weaponModifier, 'recoil', []);
    $recoilDecayMultiplier = data_get($recoil, 'decay_multiplier');
    $recoilDecayChange = data_get($recoil, 'decay_change');
    $recoilMultiplier = data_get($recoil, 'multiplier');
    $recoilMultiplierChange = data_get($recoil, 'multiplier_change');
    $hasRecoil = is_array($recoil) && collect($recoil)->filter(fn($v) => $v !== null)->isNotEmpty();

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
    $hasSpread = is_array($spread) && collect($spread)->filter(fn($v) => $v !== null)->isNotEmpty();

    $aim = data_get($weaponModifier, 'aim', []);
    $aimZoomScale = data_get($aim, 'zoom_scale');
    $aimSecondZoomScale = data_get($aim, 'second_zoom_scale');
    $aimZoomTimeScale = data_get($aim, 'zoom_time_scale');
    $aimZoomTimeChange = data_get($aim, 'zoom_time_change');
    $aimHideWeaponInAds = data_get($aim, 'hide_weapon_in_ads');
    $aimFstopMultiplier = data_get($aim, 'fstop_multiplier');
    $hasAim = is_array($aim) && collect($aim)->filter(fn($v) => $v !== null)->isNotEmpty();

    $regen = data_get($weaponModifier, 'regen', []);
    $regenPowerRatioMultiplier = data_get($regen, 'power_ratio_multiplier');
    $regenMaxAmmoLoadMultiplier = data_get($regen, 'max_ammo_load_multiplier');
    $regenMaxRegenPerSecMultiplier = data_get($regen, 'max_regen_per_sec_multiplier');
    $hasRegen = is_array($regen) && collect($regen)->filter(fn($v) => $v !== null)->isNotEmpty();

    $salvage = data_get($weaponModifier, 'salvage', []);
    $salvageSpeedMultiplier = data_get($salvage, 'salvage_speed_multiplier');
    $salvageRadiusMultiplier = data_get($salvage, 'radius_multiplier');
    $salvageExtractionEfficiency = data_get($salvage, 'extraction_efficiency');
    $hasSalvage = is_array($salvage) && collect($salvage)->filter(fn($v) => $v !== null)->isNotEmpty();

    $zeroing = data_get($weaponModifier, 'zeroing', []);
    $zeroingDefaultRange = data_get($zeroing, 'default_range');
    $zeroingMaxRange = data_get($zeroing, 'max_range');
    $zeroingRangeIncrement = data_get($zeroing, 'range_increment');
    $zeroingAutoZeroingTime = data_get($zeroing, 'auto_zeroing_time');
    $hasZeroing = is_array($zeroing) && collect($zeroing)->filter(fn($v) => $v !== null)->isNotEmpty();
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Weapon Modifier</h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @if ($activateOnAttach !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Activate On Attach</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ $activateOnAttach ? 'Yes' : 'No' }}</dd>
                </div>
            @endif
            @if ($ignoreWear !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Ignore Wear</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ $ignoreWear ? 'Yes' : 'No' }}</dd>
                </div>
            @endif
        </dl>

        @if ($hasBase)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Base
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($baseMuzzleFlashMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Muzzle Flash Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baseMuzzleFlashMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($baseMuzzleFlashChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Muzzle Flash</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baseMuzzleFlashChange * 100, '%', 2) }}</dd>
                            </div>
                        @endif
                        @if ($baseFireRateMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Fire Rate Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baseFireRateMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($baseFireRateChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Fire Rate</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baseFireRateChange * 100, '%', 2) }}</dd>
                            </div>
                        @endif
                        @if ($baseDamageMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Damage Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baseDamageMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($baseDamageChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Damage</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baseDamageChange * 100, '%', 2) }}</dd>
                            </div>
                        @endif
                        @if ($baseProjectileSpeedMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Projectile Speed Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baseProjectileSpeedMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($baseProjectileSpeedChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Projectile Speed</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baseProjectileSpeedChange * 100, '%', 2) }}</dd>
                            </div>
                        @endif
                        @if ($baseAmmoCostMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Ammo Cost Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baseAmmoCostMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($baseAmmoCostChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Ammo Cost</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baseAmmoCostChange * 100, '%', 2) }}</dd>
                            </div>
                        @endif
                        @if ($baseHeatGenerationMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Heat Generation Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baseHeatGenerationMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($baseHeatGenerationChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Heat Generation</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baseHeatGenerationChange * 100, '%', 2) }}</dd>
                            </div>
                        @endif
                        @if ($baseSoundRadiusMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Sound Radius Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baseSoundRadiusMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($baseSoundRadiusChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Sound Radius</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baseSoundRadiusChange * 100, '%', 2) }}</dd>
                            </div>
                        @endif
                        @if ($baseChargeTimeMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Charge Time Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baseChargeTimeMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($baseChargeTimeChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Charge Time</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baseChargeTimeChange * 100, '%', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasRecoil)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Recoil
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($recoilDecayMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Decay Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($recoilDecayMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($recoilDecayChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Decay</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($recoilDecayChange * 100, '%', 2) }}</dd>
                            </div>
                        @endif
                        @if ($recoilMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($recoilMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($recoilMultiplierChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($recoilMultiplierChange * 100, '%', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasSpread)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Spread
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($spreadMinMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Min Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($spreadMinMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($spreadMinChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Min</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($spreadMinChange * 100, '%', 2) }}</dd>
                            </div>
                        @endif
                        @if ($spreadMaxMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($spreadMaxMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($spreadMaxChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($spreadMaxChange * 100, '%', 2) }}</dd>
                            </div>
                        @endif
                        @if ($spreadFirstAttackMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">First Attack Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($spreadFirstAttackMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($spreadFirstAttackChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">First Attack</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($spreadFirstAttackChange * 100, '%', 2) }}</dd>
                            </div>
                        @endif
                        @if ($spreadPerAttackMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Per Attack Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($spreadPerAttackMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($spreadPerAttackChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Per Attack</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($spreadPerAttackChange * 100, '%', 2) }}</dd>
                            </div>
                        @endif
                        @if ($spreadDecayMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Decay Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($spreadDecayMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($spreadDecayChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Decay</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($spreadDecayChange * 100, '%', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasAim)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Aim
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($aimZoomScale !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Zoom Scale</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($aimZoomScale, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($aimSecondZoomScale !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Second Zoom Scale</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($aimSecondZoomScale, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($aimZoomTimeScale !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Zoom Time Scale</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($aimZoomTimeScale, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($aimZoomTimeChange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Zoom Time</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($aimZoomTimeChange * 100, '%', 2) }}</dd>
                            </div>
                        @endif
                        @if ($aimHideWeaponInAds !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Hide Weapon In ADS</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ $aimHideWeaponInAds ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($aimFstopMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">F-Stop Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($aimFstopMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasRegen)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Regen
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($regenPowerRatioMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Power Ratio Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($regenPowerRatioMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($regenMaxAmmoLoadMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Ammo Load Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($regenMaxAmmoLoadMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($regenMaxRegenPerSecMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Regen Per Sec Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($regenMaxRegenPerSecMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasSalvage)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Salvage
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($salvageSpeedMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Salvage Speed Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($salvageSpeedMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($salvageRadiusMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Radius Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($salvageRadiusMultiplier, 'x', 2) }}</dd>
                            </div>
                        @endif
                        @if ($salvageExtractionEfficiency !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Extraction Efficiency</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($salvageExtractionEfficiency, 'x', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasZeroing)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Zeroing
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($zeroingDefaultRange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Default Range</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($zeroingDefaultRange, 'm', 2) }}</dd>
                            </div>
                        @endif
                        @if ($zeroingMaxRange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Range</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($zeroingMaxRange, 'm', 2) }}</dd>
                            </div>
                        @endif
                        @if ($zeroingRangeIncrement !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Range Increment</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($zeroingRangeIncrement, 'm', 2) }}</dd>
                            </div>
                        @endif
                        @if ($zeroingAutoZeroingTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Auto Zeroing Time</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($zeroingAutoZeroingTime, 's', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif
    </div>
</div>
