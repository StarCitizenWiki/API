<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ammunition_damage_falloff',
    title: 'Ammunition Damage Falloff (v2)',
    description: 'Legacy damage falloff entry grouped by type. Deprecated in favour of the damage_drop_* fields.',
    properties: [
        new OA\Property(property: 'Physical', type: 'double', nullable: true),
        new OA\Property(property: 'Energy', type: 'double', nullable: true),
        new OA\Property(property: 'Distortion', type: 'double', nullable: true),
        new OA\Property(property: 'Thermal', type: 'double', nullable: true),
        new OA\Property(property: 'Biochemical', type: 'double', nullable: true),
        new OA\Property(property: 'Stun', type: 'double', nullable: true),
    ],
    type: 'object',
    deprecated: true
)]

#[OA\Schema(
    schema: 'ammunition',
    title: 'Ammunition',
    description: 'Projectile behaviour and damage data from Item.stdItem.Ammunition. Values are taken directly from game data without simulation or derived calculations.',
    properties: [
        new OA\Property(property: 'speed', description: 'Projectile speed in m/s.', type: 'double', example: 600, nullable: true),
        new OA\Property(property: 'lifetime', description: 'Lifetime in seconds before the projectile despawns.', type: 'double', example: 2.0, nullable: true),
        new OA\Property(property: 'range', description: 'Effective range in meters (speed × lifetime when provided by the game).', type: 'double', example: 1200, nullable: true),
        new OA\Property(property: 'size', description: 'Projectile size used by the game for collision/damage logic.', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'capacity', description: 'Maximum ammo or charge stored in the weapon battery/magazine for this ammo definition.', type: 'integer', example: 80, nullable: true),
        new OA\Property(property: 'initial_capacity', description: 'Starting ammo or charge loaded when the item spawns.', type: 'integer', example: 80, nullable: true),
        new OA\Property(property: 'damage_falloff_level_1', description: 'Legacy piercability falloff level 1 value.', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'damage_falloff_level_2', description: 'Legacy piercability falloff level 2 value.', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'damage_falloff_level_3', description: 'Legacy piercability falloff level 3 value.', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'max_penetration_thickness', description: 'Maximum armor or material thickness (m) this round can pierce.', type: 'double', example: 0.5, nullable: true),
        new OA\Property(
            property: 'penetration',
            description: 'Penetration behaviour detailing distance and angle effectiveness.',
            properties: [
                new OA\Property(property: 'base_penetration_distance', type: 'double', example: 60, nullable: true),
                new OA\Property(property: 'angle', description: 'Maximum impact angle (degrees) before ricochet/stop.', type: 'double', example: 14.6, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'impact_damage',
            description: 'Direct hit damage per projectile, split by damage type. Zero values are omitted.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/weapon_damage_entry'),
            nullable: true
        ),
        new OA\Property(
            property: 'detonation_damage',
            description: 'Explosion damage applied on detonation-capable projectiles.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/weapon_damage_entry'),
            nullable: true
        ),
        new OA\Property(property: 'impulse_scale', description: 'Impulse multiplier applied on impact (engine value).', type: 'double', example: 1, nullable: true),
        new OA\Property(property: 'bullet_type', description: 'Internal bullet type identifier.', type: 'integer', example: -1, nullable: true),
        new OA\Property(
            property: 'damage_drop_min_distance',
            description: 'Per-type distance (m) before damage falloff begins.',
            properties: [
                new OA\Property(property: 'Physical', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'Energy', type: 'double', example: 200, nullable: true),
                new OA\Property(property: 'Distortion', type: 'double', example: 200, nullable: true),
                new OA\Property(property: 'Thermal', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'Biochemical', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'Stun', type: 'double', example: 200, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'damage_drop_per_meter',
            description: 'Per-type damage reduction applied each meter after min distance.',
            properties: [
                new OA\Property(property: 'Physical', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'Energy', type: 'double', example: 0.01, nullable: true),
                new OA\Property(property: 'Distortion', type: 'double', example: 0.01, nullable: true),
                new OA\Property(property: 'Thermal', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'Biochemical', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'Stun', type: 'double', example: 0.01, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'damage_drop_min_damage',
            description: 'Floor values for damage after falloff.',
            properties: [
                new OA\Property(property: 'Physical', type: 'double', example: 10, nullable: true),
                new OA\Property(property: 'Energy', type: 'double', example: 10, nullable: true),
                new OA\Property(property: 'Distortion', type: 'double', example: 5, nullable: true),
                new OA\Property(property: 'Thermal', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'Biochemical', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'Stun', type: 'double', example: 6, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'bullet_impulse_falloff',
            description: 'Impact impulse falloff parameters.',
            properties: [
                new OA\Property(property: 'MinDistance', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'DropFalloff', type: 'double', example: 0.005, nullable: true),
                new OA\Property(property: 'MaxFalloff', type: 'double', example: 0.3, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'bullet_electron',
            description: 'Electron chain parameters for weapons that jump between targets.',
            properties: [
                new OA\Property(property: 'JumpRange', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'MaximumJumps', type: 'integer', example: 0, nullable: true),
                new OA\Property(property: 'ResidualChargeMultiplier', type: 'double', example: 0, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        // Backward compatibility with v2 naming
        new OA\Property(
            property: 'damage_falloffs',
            description: 'Legacy grouping of damage drop data.',
            properties: [
                new OA\Property(property: 'min_distance', ref: '#/components/schemas/ammunition_damage_falloff', nullable: true),
                new OA\Property(property: 'per_meter', ref: '#/components/schemas/ammunition_damage_falloff', nullable: true),
                new OA\Property(property: 'min_damage', ref: '#/components/schemas/ammunition_damage_falloff', nullable: true),
            ],
            type: 'object',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'piercability',
            description: 'Deprecated: retained for v2 compatibility; maps to damage_falloff_level_* and max_penetration_thickness when present.',
            type: 'object',
            nullable: true,
            deprecated: true
        ),
    ],
    type: 'object'
)]
class AmmunitionResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);
        $ammunition = Arr::get($stdItem, 'Ammunition', []);

        $impactDamage = $this->buildDamageArray(Arr::get($ammunition, 'ImpactDamage', []), 'ImpactDamage');
        $detonationDamage = $this->buildDamageArray(Arr::get($ammunition, 'DetonationDamage', []), 'DetonationDamage');

        $mapper = static fn ($value, $key) => [Str::snake($key) => $value];

        $damageDropMinDistance = collect(Arr::get($ammunition, 'DamageDropMinDistance', []))->mapWithKeys($mapper);
        $damageDropPerMeter = collect(Arr::get($ammunition, 'DamageDropPerMeter', []))->mapWithKeys($mapper);
        $damageDropMinDamage = collect(Arr::get($ammunition, 'DamageDropMinDamage', []))->mapWithKeys($mapper);

        if ($damageDropMinDamage->isNotEmpty()) {
            $damageDropMinDamage = $damageDropMinDamage->put('total', $damageDropMinDamage->sum())->toArray();
        }

        if ($damageDropPerMeter->isNotEmpty()) {
            $damageDropPerMeter = $damageDropPerMeter->put('total', $damageDropPerMeter->sum())->toArray();
        }

        if ($damageDropMinDistance->isNotEmpty()) {
            $damageDropMinDistance = $damageDropMinDistance->put('total', $damageDropMinDistance->sum())->toArray();
        }

        $penetration = Arr::get($ammunition, 'Penetration');

        $bulletImpulseFalloff = [
            'min_distance' => Arr::get($ammunition, 'BulletImpulseFalloff.MinDistance'),
            'drop_falloff' => Arr::get($ammunition, 'BulletImpulseFalloff.DropFalloff'),
            'max_falloff' => Arr::get($ammunition, 'BulletImpulseFalloff.MaxFalloff'),
        ];

        $damageFalloffs = [
            'min_distance' => $damageDropMinDistance,
            'per_meter' => $damageDropPerMeter,
            'min_damage' => $damageDropMinDamage,
        ];

        return [
            'uuid' => Arr::get($ammunition, 'UUID'),
            'size' => Arr::get($ammunition, 'Size'),
            'lifetime' => Arr::get($ammunition, 'Lifetime'),
            'speed' => Arr::get($ammunition, 'Speed'),
            'range' => Arr::get($ammunition, 'Range'),

            'capacity' => Arr::get($ammunition, 'Capacity'),
            'initial_capacity' => Arr::get($ammunition, 'InitialCapacity'),

            'damage_falloff_level_1' => Arr::get($ammunition, 'DamageFalloffLevel1'),
            'damage_falloff_level_2' => Arr::get($ammunition, 'DamageFalloffLevel2'),
            'damage_falloff_level_3' => Arr::get($ammunition, 'DamageFalloffLevel3'),
            'max_penetration_thickness' => Arr::get($ammunition, 'MaxPenetrationThickness'),

            'penetration' => is_array($penetration) ? [
                'base_distance' => Arr::get($penetration, 'BasePenetrationDistance'),
                'near_radius' => Arr::get($penetration, 'NearRadius'),
                'far_radius' => Arr::get($penetration, 'FarRadius'),
                'angle' => Arr::get($penetration, 'Angle'),
            ] : null,

            $this->mergeWhen(! empty($impactDamage), [
                'impact_damage' => $impactDamage,
                'impact_damage_map' => collect($impactDamage)->mapWithKeys(static fn ($entry) => [Str::snake($entry['name']) => $entry['damage']])->toArray(),
            ]),

            $this->mergeWhen(! empty($detonationDamage), [
                'detonation_damage' => $detonationDamage,
                'detonation_damage_map' => collect($detonationDamage)->mapWithKeys(static fn ($entry) => [Str::snake($entry['name']) => $entry['damage']])->toArray(),
            ]),

            $this->mergeWhen(Arr::get($ammunition, 'ExplosionRadius') !== null, [
                'explosion_radius' => [
                    'min' => Arr::get($ammunition, 'ExplosionRadius.Minimum'),
                    'max' => Arr::get($ammunition, 'ExplosionRadius.Maximum'),
                ],
            ]),

            $this->mergeWhen(! empty($damageDropMinDistance), [
                'damage_drop_min_distance' => $damageDropMinDistance,
            ]),
            $this->mergeWhen(! empty($damageDropPerMeter), [
                'damage_drop_per_meter' => $damageDropPerMeter,
            ]),
            $this->mergeWhen(! empty($damageDropMinDamage), [
                'damage_drop_min_damage' => $damageDropMinDamage,
            ]),

            $this->mergeWhen(collect($bulletImpulseFalloff)->filter()->isNotEmpty(), [
                'bullet_impulse_falloff' => $bulletImpulseFalloff,
            ]),

            $this->mergeWhen(Arr::get($ammunition, 'BulletElectron') !== null, [
                'bullet_electron' => [
                    'jump_range' => Arr::get($ammunition, 'BulletElectron.JumpRange'),
                    'maximum_jumps' => Arr::get($ammunition, 'BulletElectron.MaximumJumps'),
                    'residual_charge_multiplier' => Arr::get($ammunition, 'BulletElectron.ResidualChargeMultiplier'),
                ],
            ]),

            'impulse_scale' => Arr::get($ammunition, 'ImpulseScale'),
            'bullet_type' => Arr::get($ammunition, 'BulletType'),

            $this->mergeWhen(collect($damageFalloffs)->filter()->isNotEmpty(), [
                'damage_falloffs' => $damageFalloffs,
            ]),
        ];
    }
}
