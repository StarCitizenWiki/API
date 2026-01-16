<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use App\Http\Resources\Game\Concerns\ExtractsJsonData;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'weapon_modifier',
    title: 'Weapon Modifier',
    description: 'Attachment and weapon modifier stats taken directly from Item.stdItem.WeaponModifier. Legacy v2 fields are kept (deprecated) for backwards compatibility.',
    properties: [
        new OA\Property(property: 'activate_on_attach', type: 'boolean', example: true, nullable: true, description: 'Whether the modifier becomes active as soon as it is attached.'),
        new OA\Property(property: 'ignore_wear', type: 'boolean', example: false, nullable: true, description: 'If true, the modifier ignores item wear/maintenance effects.'),
        new OA\Property(
            property: 'weapon_stats',
            properties: [
                new OA\Property(
                    property: 'base',
                    description: 'Core scalar adjustments applied to the weapon.',
                    properties: [
                        new OA\Property(property: 'fire_rate', description: 'Base fire rate override (usually 0).', type: 'double', example: 0, nullable: true),
                        new OA\Property(property: 'fire_rate_multiplier', description: 'Multiplier applied to fire rate.', type: 'double', example: 1.1, nullable: true),
                        new OA\Property(property: 'damage_multiplier', type: 'double', example: 0.92, nullable: true, description: 'Damage output multiplier.'),
                        new OA\Property(property: 'damage_over_time_multiplier', type: 'double', example: 1.0, nullable: true, description: 'DoT damage multiplier.'),
                        new OA\Property(property: 'projectile_speed_multiplier', type: 'double', example: 0.875, nullable: true, description: 'Multiplier applied to projectile speed.'),
                        new OA\Property(property: 'pellets', type: 'integer', example: 0, nullable: true, description: 'Additional pellets per shot.'),
                        new OA\Property(property: 'burst_shots', type: 'integer', example: 0, nullable: true, description: 'Additional shots fired per burst.'),
                        new OA\Property(property: 'ammo_cost', type: 'integer', example: 0, nullable: true, description: 'Flat ammo cost override.'),
                        new OA\Property(property: 'ammo_cost_multiplier', type: 'double', example: 2.0, nullable: true, description: 'Multiplier applied to ammo usage.'),
                        new OA\Property(property: 'heat_generation_multiplier', type: 'double', example: 0.2, nullable: true, description: 'Multiplier for heat generated per shot.'),
                        new OA\Property(property: 'sound_radius_multiplier', type: 'double', example: 1.2, nullable: true, description: 'Multiplier for the audible radius of the shot.'),
                        new OA\Property(property: 'charge_time_multiplier', type: 'double', example: 1.0, nullable: true, description: 'Charge-up time multiplier.'),
                        new OA\Property(property: 'use_alternate_projectile_visuals', type: 'boolean', example: false, nullable: true, description: 'Switch to alternate projectile FX.'),
                        new OA\Property(property: 'use_augmented_reality_projectiles', type: 'boolean', example: false, nullable: true, description: 'Enable AR projectiles rendering.'),
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(
                    property: 'recoil',
                    description: 'Recoil tuning multipliers.',
                    properties: [
                        new OA\Property(property: 'decay_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'end_decay_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'fire_recoil_time_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'fire_recoil_strength_first_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'fire_recoil_strength_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'angle_recoil_strength_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'randomness_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'randomness_back_push_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'frontal_oscillation_rotation_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'frontal_oscillation_strength_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'frontal_oscillation_decay_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'frontal_oscillation_randomness_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'animated_recoil_multiplier', type: 'double', example: 1.0, nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(
                    property: 'spread',
                    description: 'Spread behaviour tuning multipliers.',
                    properties: [
                        new OA\Property(property: 'min_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'max_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'first_attack_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'attack_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'decay_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'additive_modifier', type: 'double', example: 0, nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(
                    property: 'aim',
                    description: 'ADS and zoom behaviour adjustments.',
                    properties: [
                        new OA\Property(property: 'zoom_scale', type: 'double', example: 4, nullable: true),
                        new OA\Property(property: 'second_zoom_scale', type: 'double', example: 6, nullable: true),
                        new OA\Property(property: 'zoom_time_scale', type: 'double', example: 1.25, nullable: true),
                        new OA\Property(property: 'hide_weapon_in_ads', type: 'boolean', example: false, nullable: true),
                        new OA\Property(property: 'fstop_multiplier', type: 'double', example: 1.0, nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(
                    property: 'regen',
                    description: 'Resource regeneration modifiers for specialised tools.',
                    properties: [
                        new OA\Property(property: 'power_ratio_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'max_ammo_load_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'max_regen_per_sec_multiplier', type: 'double', example: 1.0, nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(
                    property: 'salvage',
                    description: 'Salvage efficiency modifiers.',
                    properties: [
                        new OA\Property(property: 'salvage_speed_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'radius_multiplier', type: 'double', example: 1.0, nullable: true),
                        new OA\Property(property: 'extraction_efficiency', type: 'double', example: 1.0, nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'zeroing',
            description: 'Zeroing distances supported by the optic or attachment.',
            properties: [
                new OA\Property(property: 'default_range', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'max_range', type: 'double', example: 500, nullable: true),
                new OA\Property(property: 'range_increment', type: 'double', example: 100, nullable: true),
                new OA\Property(property: 'auto_zeroing_time', type: 'double', example: 0, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        // Backwards compatibility with v2
        new OA\Property(property: 'fire_rate_multiplier', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'damage_multiplier', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'damage_over_time_multiplier', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'projectile_speed_multiplier', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'ammo_cost_multiplier', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'heat_generation_multiplier', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'sound_radius_multiplier', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'charge_time_multiplier', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'recoil', type: 'array', items: new OA\Items(type: 'object'), nullable: true, deprecated: true),
        new OA\Property(property: 'spread', type: 'array', items: new OA\Items(type: 'object'), nullable: true, deprecated: true),
        new OA\Property(property: 'aim', type: 'array', items: new OA\Items(type: 'object'), nullable: true, deprecated: true),
        new OA\Property(property: 'salvage', type: 'array', items: new OA\Items(type: 'object'), nullable: true, deprecated: true),
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

        $weaponAttachment = $this->extractFromStdItem($this->resource, 'WeaponAttachment');

        $ironSight = Arr::get($weaponAttachment, 'IronSight', []);

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

                    'attack_multiplier' => Arr::get($spread, 'AttackMultiplier'),
                    'attack_change' => round(Arr::get($spread, 'AttackMultiplier', 1) - 1, 2),

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

            $this->mergeWhen(! collect($regen)->reject(fn ($val) => $val == 0)->every(fn ($val) => (float) $val === 1.0), [
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
