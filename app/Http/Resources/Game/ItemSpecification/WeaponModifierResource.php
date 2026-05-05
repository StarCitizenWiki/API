<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use App\Http\Resources\Game\Concerns\ExtractsJsonData;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'weapon_modifier_base',
    title: 'Weapon Modifier Base',
    description: 'Core scalar adjustments and derived delta fields (`*_change`). Only emitted when the modifier meaningfully differs from defaults.',
    properties: [
        new OA\Property(property: 'muzzle_flash_multiplier', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'muzzle_flash_change', description: 'Computed as `round(muzzle_flash_multiplier - 1, 2)`.', type: 'double', example: 0.0, nullable: true),

        new OA\Property(property: 'fire_rate_multiplier', type: 'double', example: 1.1, nullable: true),
        new OA\Property(property: 'fire_rate_change', description: 'Computed as `round(fire_rate_multiplier - 1, 2)`.', type: 'double', example: 0.1, nullable: true),

        new OA\Property(property: 'damage_multiplier', type: 'double', example: 0.92, nullable: true),
        new OA\Property(property: 'damage_change', description: 'Computed as `round(damage_multiplier - 1, 2)`.', type: 'double', example: -0.08, nullable: true),

        new OA\Property(property: 'projectile_speed_multiplier', type: 'double', example: 0.875, nullable: true),
        new OA\Property(property: 'projectile_speed_change', description: 'Computed as `round(projectile_speed_multiplier - 1, 2)`.', type: 'double', example: -0.13, nullable: true),

        new OA\Property(property: 'ammo_cost_multiplier', type: 'double', example: 2.0, nullable: true),
        new OA\Property(property: 'ammo_cost_change', description: 'Computed as `round(ammo_cost_multiplier - 1, 2)`.', type: 'double', example: 1.0, nullable: true),

        new OA\Property(property: 'heat_generation_multiplier', type: 'double', example: 0.2, nullable: true),
        new OA\Property(property: 'heat_generation_change', description: 'Computed as `round(heat_generation_multiplier - 1, 2)`.', type: 'double', example: -0.8, nullable: true),

        new OA\Property(property: 'sound_radius_multiplier', type: 'double', example: 1.2, nullable: true),
        new OA\Property(property: 'sound_radius_change', description: 'Computed as `round(sound_radius_multiplier - 1, 2)`.', type: 'double', example: 0.2, nullable: true),

        new OA\Property(property: 'charge_time_multiplier', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'charge_time_change', description: 'Computed as `round(charge_time_multiplier - 1, 2)`.', type: 'double', example: 0.0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'weapon_modifier_recoil',
    title: 'Weapon Modifier Recoil',
    description: 'Reduced recoil block as emitted by this resource (not a full recoil model export). Only emitted when values differ from defaults.',
    properties: [
        new OA\Property(property: 'decay_multiplier', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'decay_change', description: 'Computed as `round(decay_multiplier - 1, 2)`.', type: 'double', example: 0.0, nullable: true),

        new OA\Property(property: 'multiplier', description: 'Maps to source `RandomnessMultiplier`.', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'multiplier_change', description: 'Computed as `round(multiplier - 1, 2)`.', type: 'double', example: 0.0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'weapon_modifier_spread',
    title: 'Weapon Modifier Spread',
    description: 'Spread tuning multipliers and derived deltas. Only emitted when values differ from defaults.',
    properties: [
        new OA\Property(property: 'min_multiplier', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'min_change', description: 'Computed as `round(min_multiplier - 1, 2)`.', type: 'double', example: 0.0, nullable: true),

        new OA\Property(property: 'max_multiplier', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'max_change', description: 'Computed as `round(max_multiplier - 1, 2)`.', type: 'double', example: 0.0, nullable: true),

        new OA\Property(property: 'first_attack_multiplier', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'first_attack_change', description: 'Computed as `round(first_attack_multiplier - 1, 2)`.', type: 'double', example: 0.0, nullable: true),

        new OA\Property(property: 'per_attack_multiplier', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'per_attack_change', description: 'Computed as `round(per_attack_multiplier - 1, 2)`.', type: 'double', example: 0.0, nullable: true),

        new OA\Property(property: 'decay_multiplier', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'decay_change', description: 'Computed as `round(decay_multiplier - 1, 2)`.', type: 'double', example: 0.0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'weapon_modifier_aim',
    title: 'Weapon Modifier Aim',
    description: 'ADS/zoom adjustments. Only emitted when values differ from defaults.',
    properties: [
        new OA\Property(property: 'zoom_scale', type: 'double', example: 4, nullable: true),
        new OA\Property(property: 'second_zoom_scale', type: 'double', example: 6, nullable: true),
        new OA\Property(property: 'zoom_time_scale', type: 'double', example: 1.25, nullable: true),
        new OA\Property(property: 'zoom_time_change', description: 'Computed as `round(zoom_time_scale - 1, 2)`.', type: 'double', example: 0.25, nullable: true),
        new OA\Property(property: 'hide_weapon_in_ads', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'fstop_multiplier', type: 'double', example: 1.0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'weapon_modifier_regen',
    title: 'Weapon Modifier Regen',
    description: 'Regeneration modifiers. Only emitted when values differ from defaults.',
    properties: [
        new OA\Property(property: 'power_ratio_multiplier', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'max_ammo_load_multiplier', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'max_regen_per_sec_multiplier', type: 'double', example: 1.0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'weapon_modifier_salvage',
    title: 'Weapon Modifier Salvage',
    description: 'Salvage-related multipliers. Only emitted when present per resource logic.',
    properties: [
        new OA\Property(property: 'salvage_speed_multiplier', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'radius_multiplier', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'extraction_efficiency', type: 'double', example: 1.0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'weapon_modifier_zeroing',
    title: 'Weapon Modifier Zeroing',
    description: 'Zeroing distances supported by the optic or attachment. Only emitted when present per resource logic.',
    properties: [
        new OA\Property(property: 'default_range', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'max_range', type: 'double', example: 500, nullable: true),
        new OA\Property(property: 'range_increment', type: 'double', example: 100, nullable: true),
        new OA\Property(property: 'auto_zeroing_time', type: 'double', example: 0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'weapon_modifier',
    title: 'Weapon Modifier',
    description: 'Attachment and weapon modifier stats taken directly from Item.stdItem.WeaponModifier. Legacy v2 fields are kept (deprecated) for backwards compatibility.',
    properties: [
        new OA\Property(
            property: 'activate_on_attach',
            description: 'Whether the modifier becomes active as soon as it is attached.',
            type: 'boolean',
            example: true,
            nullable: true
        ),
        new OA\Property(
            property: 'ignore_wear',
            description: 'If true, the modifier ignores item wear/maintenance effects.',
            type: 'boolean',
            example: false,
            nullable: true
        ),

        new OA\Property(property: 'base', ref: '#/components/schemas/weapon_modifier_base', nullable: true),
        new OA\Property(property: 'recoil', ref: '#/components/schemas/weapon_modifier_recoil', nullable: true),
        new OA\Property(property: 'spread', ref: '#/components/schemas/weapon_modifier_spread', nullable: true),
        new OA\Property(property: 'aim', ref: '#/components/schemas/weapon_modifier_aim', nullable: true),
        new OA\Property(property: 'regen', ref: '#/components/schemas/weapon_modifier_regen', nullable: true),
        new OA\Property(property: 'salvage', ref: '#/components/schemas/weapon_modifier_salvage', nullable: true),
        new OA\Property(property: 'zeroing', ref: '#/components/schemas/weapon_modifier_zeroing', nullable: true),

        // Backwards compatibility with v2
        new OA\Property(
            property: 'fire_rate_multiplier',
            description: 'Deprecated: Use `base.fire_rate_multiplier`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'damage_multiplier',
            description: 'Deprecated: Use `base.damage_multiplier`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'damage_over_time_multiplier',
            description: 'Deprecated: v2 compatibility field. No non-deprecated replacement is currently returned by this resource.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'projectile_speed_multiplier',
            description: 'Deprecated: Use `base.projectile_speed_multiplier`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'ammo_cost_multiplier',
            description: 'Deprecated: Use `base.ammo_cost_multiplier`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'heat_generation_multiplier',
            description: 'Deprecated: Use `base.heat_generation_multiplier`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'sound_radius_multiplier',
            description: 'Deprecated: Use `base.sound_radius_multiplier`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'charge_time_multiplier',
            description: 'Deprecated: Use `base.charge_time_multiplier`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
    ],
    type: 'object'
)]
class WeaponModifierResource extends AbstractItemSpecificationResource
{
    use ExtractsJsonData;

    public function toArray(Request $request): array
    {
        $weaponModifier = $this->extractFromStdItem($this->resource, 'WeaponModifier', []);
        $weaponStats = Arr::get($weaponModifier, 'WeaponStats', []);
        $base = Arr::get($weaponStats, 'Base', []);
        $recoil = Arr::get($weaponStats, 'Recoil', []);
        $spread = Arr::get($weaponStats, 'Spread', []);
        $aim = Arr::get($weaponStats, 'Aim', []);
        $regen = Arr::get($weaponStats, 'Regen', []);
        $salvage = Arr::get($weaponStats, 'Salvage', []);
        $zeroing = Arr::get($weaponModifier, 'Zeroing', []);

        return [
            'activate_on_attach' => Arr::get($weaponModifier, 'ActivateOnAttach'),
            'ignore_wear' => Arr::get($weaponModifier, 'IgnoreWear'),

            $this->mergeWhen(! collect($base)->reject(fn ($val) => $val == 0)->every(fn ($val) => (float) $val === 1.0), [
                'base' => [
                    'muzzle_flash_multiplier' => Arr::get($base, 'MuzzleFlashScale'),
                    'muzzle_flash_change' => round(Arr::get($base, 'MuzzleFlashScale', 1) - 1, 2),

                    'fire_rate_multiplier' => Arr::get($base, 'FireRateMultiplier'),
                    'fire_rate_change' => round(Arr::get($base, 'FireRateMultiplier', 1) - 1, 2),

                    'damage_multiplier' => Arr::get($base, 'DamageMultiplier'),
                    'damage_change' => round(Arr::get($base, 'DamageMultiplier', 1) - 1, 2),

                    'projectile_speed_multiplier' => Arr::get($base, 'ProjectileSpeedMultiplier'),
                    'projectile_speed_change' => round(Arr::get($base, 'ProjectileSpeedMultiplier', 1) - 1, 2),

                    'ammo_cost_multiplier' => Arr::get($base, 'AmmoCostMultiplier'),
                    'ammo_cost_change' => round(Arr::get($base, 'AmmoCostMultiplier', 1) - 1, 2),

                    'heat_generation_multiplier' => Arr::get($base, 'HeatGenerationMultiplier'),
                    'heat_generation_change' => round(Arr::get($base, 'HeatGenerationMultiplier', 1) - 1, 2),

                    'sound_radius_multiplier' => Arr::get($base, 'SoundRadiusMultiplier'),
                    'sound_radius_change' => round(Arr::get($base, 'SoundRadiusMultiplier', 1) - 1, 2),

                    'charge_time_multiplier' => Arr::get($base, 'ChargeTimeMultiplier'),
                    'charge_time_change' => round(Arr::get($base, 'ChargeTimeMultiplier', 1) - 1, 2),
                ],
            ]),

            $this->mergeWhen(! collect($recoil)->reject(fn ($val) => $val == 0)->every(fn ($val) => (float) $val === 1.0), [
                'recoil' => [
                    'decay_multiplier' => Arr::get($recoil, 'DecayMultiplier'),
                    'decay_change' => round(Arr::get($recoil, 'DecayMultiplier', 1) - 1, 2),

                    'multiplier' => Arr::get($recoil, 'RandomnessMultiplier'),
                    'multiplier_change' => round(Arr::get($recoil, 'RandomnessMultiplier', 1) - 1, 2),
                ],
            ]),

            $this->mergeWhen(! collect($spread)->reject(fn ($val) => $val == 0)->every(fn ($val) => (float) $val === 1.0), [
                'spread' => [
                    'min_multiplier' => Arr::get($spread, 'MinMultiplier'),
                    'min_change' => round(Arr::get($spread, 'MinMultiplier', 1) - 1, 2),

                    'max_multiplier' => Arr::get($spread, 'MaxMultiplier'),
                    'max_change' => round(Arr::get($spread, 'MaxMultiplier', 1) - 1, 2),

                    'first_attack_multiplier' => Arr::get($spread, 'FirstAttackMultiplier'),
                    'first_attack_change' => round(Arr::get($spread, 'FirstAttackMultiplier', 1) - 1, 2),

                    'per_attack_multiplier' => Arr::get($spread, 'AttackMultiplier'),
                    'per_attack_change' => round(Arr::get($spread, 'AttackMultiplier', 1) - 1, 2),

                    'decay_multiplier' => Arr::get($spread, 'DecayMultiplier'),
                    'decay_change' => round(Arr::get($spread, 'DecayMultiplier', 1) - 1, 2),
                ],
            ]),

            $this->mergeWhen(! collect($aim)->reject(fn ($val) => $val == 0)->every(fn ($val) => (float) $val === 1.0), [
                'aim' => [
                    'zoom_scale' => Arr::get($aim, 'ZoomScale'),
                    'second_zoom_scale' => Arr::get($aim, 'SecondZoomScale'),
                    'zoom_time_scale' => Arr::get($aim, 'ZoomTimeScale'),
                    'zoom_time_change' => round(Arr::get($aim, 'ZoomTimeScale', 1) - 1, 2),
                    'hide_weapon_in_ads' => Arr::get($aim, 'HideWeaponInAds'),
                    'fstop_multiplier' => Arr::get($aim, 'FstopMultiplier'),
                ],
            ]),

            $this->mergeWhen(! collect($regen)->reject(fn ($val) => $val == 0)->every(fn ($val) => (float) $val === 1.0), [
                'regen' => [
                    'power_ratio_multiplier' => Arr::get($regen, 'PowerRatioMultiplier'),
                    'max_ammo_load_multiplier' => Arr::get($regen, 'MaxAmmoLoadMultiplier'),
                    'max_regen_per_sec_multiplier' => Arr::get($regen, 'MaxRegenPerSecMultiplier'),
                ],
            ]),

            $this->mergeWhen(! collect($salvage)->reject(fn ($val) => $val == 0)->every(fn ($val) => (float) $val === 1.0), [
                'salvage' => [
                    'salvage_speed_multiplier' => Arr::get($salvage, 'SalvageSpeedMultiplier'),
                    'radius_multiplier' => Arr::get($salvage, 'RadiusMultiplier'),
                    'extraction_efficiency' => Arr::get($salvage, 'ExtractionEfficiency'),
                ],
            ]),

            $this->mergeWhen(collect($zeroing)->reject(fn ($item) => $item !== null)->isNotEmpty(), [
                'zeroing' => [
                    'default_range' => Arr::get($zeroing, 'DefaultRange'),
                    'max_range' => Arr::get($zeroing, 'MaxRange'),
                    'range_increment' => Arr::get($zeroing, 'RangeIncrement'),
                    'auto_zeroing_time' => Arr::get($zeroing, 'AutoZeroingTime'),
                ],
            ]),

            'fire_rate_multiplier' => Arr::get($base, 'FireRateMultiplier'),
            'damage_multiplier' => Arr::get($base, 'DamageMultiplier'),
            'damage_over_time_multiplier' => Arr::get($base, 'DamageOverTimeMultiplier'),
            'projectile_speed_multiplier' => Arr::get($base, 'ProjectileSpeedMultiplier'),
            'ammo_cost_multiplier' => Arr::get($base, 'AmmoCostMultiplier'),
            'heat_generation_multiplier' => Arr::get($base, 'HeatGenerationMultiplier'),
            'sound_radius_multiplier' => Arr::get($base, 'SoundRadiusMultiplier'),
            'charge_time_multiplier' => Arr::get($base, 'ChargeTimeMultiplier'),
        ];
    }
}
