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
        new OA\Property(property: 'uuid', description: 'Unique identifier for this ammunition type.', type: 'string', nullable: true),
        new OA\Property(property: 'speed', description: 'Projectile speed in m/s.', type: 'double', example: 600, nullable: true, x: ['suffix' => ' m/s']),
        new OA\Property(property: 'lifetime', description: 'Lifetime in seconds before the projectile despawns.', type: 'double', example: 2.0, nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'range', description: 'Effective range in meters (speed × lifetime when provided by the game).', type: 'double', example: 1200, nullable: true, x: ['suffix' => ' m']),
        new OA\Property(property: 'size', description: 'Projectile size used by the game for collision/damage logic.', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'capacity', description: 'Maximum ammo or charge stored in the weapon battery/magazine for this ammo definition.', type: 'integer', example: 80, nullable: true),
        new OA\Property(property: 'initial_capacity', description: 'Starting ammo or charge loaded when the item spawns.', type: 'integer', example: 80, nullable: true),
        new OA\Property(property: 'damage_falloff_level_1', description: 'Damage retention percentage at penetration level 1.', type: 'double', example: 0, nullable: true, x: ['tabulator-formatter' => 'pct']),
        new OA\Property(property: 'damage_falloff_level_2', description: 'Damage retention percentage at penetration level 2.', type: 'double', example: 0, nullable: true, x: ['tabulator-formatter' => 'pct']),
        new OA\Property(property: 'damage_falloff_level_3', description: 'Damage retention percentage at penetration level 3.', type: 'double', example: 0, nullable: true, x: ['tabulator-formatter' => 'pct']),
        new OA\Property(property: 'max_penetration_thickness', description: 'Maximum armor or material thickness (m) this round can pierce.', type: 'double', example: 0.5, nullable: true, x: ['suffix' => ' m']),
        new OA\Property(
            property: 'penetration',
            description: 'Penetration behaviour detailing distance and angle effectiveness.',
            properties: [
                new OA\Property(property: 'base_distance', description: 'Base penetration distance.', type: 'double', example: 60, nullable: true, x: ['suffix' => ' m']),
                new OA\Property(property: 'near_radius', description: 'Near radius for penetration falloff.', type: 'double', nullable: true, x: ['suffix' => ' m']),
                new OA\Property(property: 'far_radius', description: 'Far radius for penetration falloff.', type: 'double', nullable: true, x: ['suffix' => ' m']),
                new OA\Property(property: 'angle', description: 'Maximum impact angle (degrees) before ricochet/stop.', type: 'double', example: 14.6, nullable: true, x: ['suffix' => ' °']),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'impact_damage',
            description: 'Deprecated: Use impact_damage_map instead. Direct hit damage per projectile, split by damage type. Zero values are omitted.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/weapon_damage_entry'),
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'detonation_damage',
            description: 'Deprecated: Use detonation_damage_map instead. Explosion damage applied on detonation-capable projectiles.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/weapon_damage_entry'),
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'impact_damage_map',
            description: 'Direct hit damage mapped by snake_case damage type keys. Replacement for impact_damage array.',
            properties: [
                new OA\Property(property: 'physical', description: 'Physical impact damage.', type: 'double', nullable: true),
                new OA\Property(property: 'energy', description: 'Energy impact damage.', type: 'double', nullable: true),
                new OA\Property(property: 'distortion', description: 'Distortion impact damage.', type: 'double', nullable: true),
                new OA\Property(property: 'thermal', description: 'Thermal impact damage.', type: 'double', nullable: true),
                new OA\Property(property: 'biochemical', description: 'Biochemical impact damage.', type: 'double', nullable: true),
                new OA\Property(property: 'stun', description: 'Stun impact damage.', type: 'double', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'detonation_damage_map',
            description: 'Explosion damage mapped by snake_case damage type keys. Replacement for detonation_damage array.',
            properties: [
                new OA\Property(property: 'physical', description: 'Physical explosion damage.', type: 'double', nullable: true),
                new OA\Property(property: 'energy', description: 'Energy explosion damage.', type: 'double', nullable: true),
                new OA\Property(property: 'distortion', description: 'Distortion explosion damage.', type: 'double', nullable: true),
                new OA\Property(property: 'thermal', description: 'Thermal explosion damage.', type: 'double', nullable: true),
                new OA\Property(property: 'biochemical', description: 'Biochemical explosion damage.', type: 'double', nullable: true),
                new OA\Property(property: 'stun', description: 'Stun explosion damage.', type: 'double', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'explosion_radius',
            description: 'Minimum and maximum explosion radius for detonation-capable projectiles.',
            properties: [
                new OA\Property(property: 'min', description: 'Minimum explosion radius (meters).', type: 'double', nullable: true, x: ['suffix' => ' m']),
                new OA\Property(property: 'max', description: 'Maximum explosion radius (meters).', type: 'double', nullable: true, x: ['suffix' => ' m']),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(property: 'impulse_scale', description: 'Impulse multiplier applied on impact. Typically always 1.', type: 'double', example: 1, nullable: true),
        new OA\Property(property: 'bullet_type', description: 'Projectile type: 2 = ballistic, -1 = energy/laser.', type: 'integer', example: -1, nullable: true),
        new OA\Property(
            property: 'damage_drop_min_distance',
            description: 'Damage values at the distance where falloff begins. Damage remains constant up to this point.',
            properties: [
                new OA\Property(property: 'physical', description: 'Physical damage at start of falloff.', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'energy', description: 'Energy damage at start of falloff.', type: 'double', example: 200, nullable: true),
                new OA\Property(property: 'distortion', description: 'Distortion damage at start of falloff.', type: 'double', example: 200, nullable: true),
                new OA\Property(property: 'thermal', description: 'Thermal damage at start of falloff.', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'biochemical', description: 'Biochemical damage at start of falloff.', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'stun', description: 'Stun damage at start of falloff.', type: 'double', example: 200, nullable: true),
                new OA\Property(property: 'total', description: 'Sum of all damage types.', type: 'double', example: 400, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'damage_drop_per_meter',
            description: 'Damage reduction per meter after the min distance is exceeded.',
            properties: [
                new OA\Property(property: 'physical', description: 'Physical damage lost per meter.', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'energy', description: 'Energy damage lost per meter.', type: 'double', example: 0.01, nullable: true),
                new OA\Property(property: 'distortion', description: 'Distortion damage lost per meter.', type: 'double', example: 0.01, nullable: true),
                new OA\Property(property: 'thermal', description: 'Thermal damage lost per meter.', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'biochemical', description: 'Biochemical damage lost per meter.', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'stun', description: 'Stun damage lost per meter.', type: 'double', example: 0.01, nullable: true),
                new OA\Property(property: 'total', description: 'Sum of all damage drop rates.', type: 'double', example: 0.03, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'damage_drop_min_damage',
            description: 'Floor damage values that the projectile never drops below regardless of distance.',
            properties: [
                new OA\Property(property: 'physical', description: 'Minimum physical damage floor.', type: 'double', example: 10, nullable: true),
                new OA\Property(property: 'energy', description: 'Minimum energy damage floor.', type: 'double', example: 10, nullable: true),
                new OA\Property(property: 'distortion', description: 'Minimum distortion damage floor.', type: 'double', example: 5, nullable: true),
                new OA\Property(property: 'thermal', description: 'Minimum thermal damage floor.', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'biochemical', description: 'Minimum biochemical damage floor.', type: 'double', example: 0, nullable: true),
                new OA\Property(property: 'stun', description: 'Minimum stun damage floor.', type: 'double', example: 6, nullable: true),
                new OA\Property(property: 'total', description: 'Sum of all minimum damage floors.', type: 'double', example: 31, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'bullet_impulse_falloff',
            description: 'How impact force (impulse) diminishes with distance.',
            properties: [
                new OA\Property(property: 'min_distance', description: 'Distance in meters before impulse falloff begins.', type: 'double', example: 0, nullable: true, x: ['suffix' => ' m']),
                new OA\Property(property: 'drop_falloff', description: 'Impulse reduction rate per meter after min distance.', type: 'double', example: 0.005, nullable: true),
                new OA\Property(property: 'max_falloff', description: 'Maximum impulse reduction fraction (0-1).', type: 'double', example: 0.3, nullable: true, x: ['tabulator-formatter' => 'progress']),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'bullet_electron',
            description: 'Electron chain parameters for weapons that arc between targets.',
            properties: [
                new OA\Property(property: 'jump_range', description: 'Maximum range of each electron jump in meters.', type: 'double', example: 0, nullable: true, x: ['suffix' => ' m']),
                new OA\Property(property: 'maximum_jumps', description: 'Maximum number of chain jumps to additional targets.', type: 'integer', example: 0, nullable: true),
                new OA\Property(property: 'residual_charge_multiplier', description: 'Damage multiplier for each subsequent jump.', type: 'double', example: 0, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),

        new OA\Property(
            property: 'conversion_rate',
            description: 'Cargo volume consumed per round in microSCU.',
            type: 'integer',
            example: 129,
            nullable: true,
            x: ['suffix' => ' µSCU']
        ),

        new OA\Property(
            property: 'damage_falloffs',
            description: 'Deprecated: Use damage_drop* data.',
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
            description: 'Deprecated: use damage_falloff_level_* and max_penetration_thickness when present.',
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

        $legacyDamageDropMinDistance = $this->legacyDamageFalloff(Arr::get($ammunition, 'DamageDropMinDistance', []));
        $legacyDamageDropPerMeter = $this->legacyDamageFalloff(Arr::get($ammunition, 'DamageDropPerMeter', []));
        $legacyDamageDropMinDamage = $this->legacyDamageFalloff(Arr::get($ammunition, 'DamageDropMinDamage', []));

        $damageDropMinDistance = $this->damageDropMap($legacyDamageDropMinDistance);
        $damageDropPerMeter = $this->damageDropMap($legacyDamageDropPerMeter);
        $damageDropMinDamage = $this->damageDropMap($legacyDamageDropMinDamage);

        $penetration = Arr::get($ammunition, 'Penetration');

        $bulletImpulseFalloff = [
            'min_distance' => Arr::get($ammunition, 'BulletImpulseFalloff.MinDistance'),
            'drop_falloff' => Arr::get($ammunition, 'BulletImpulseFalloff.DropFalloff'),
            'max_falloff' => Arr::get($ammunition, 'BulletImpulseFalloff.MaxFalloff'),
        ];

        $damageFalloffs = [
            'min_distance' => $legacyDamageDropMinDistance,
            'per_meter' => $legacyDamageDropPerMeter,
            'min_damage' => $legacyDamageDropMinDamage,
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

            'conversion_rate' => Arr::get($ammunition, 'ConversionRateMicroScu'),

            'impulse_scale' => Arr::get($ammunition, 'ImpulseScale'),
            'bullet_type' => Arr::get($ammunition, 'BulletType'),

            $this->mergeWhen(collect($damageFalloffs)->filter()->isNotEmpty(), [
                'damage_falloffs' => $damageFalloffs,
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function legacyDamageFalloff(mixed $values): array
    {
        return is_array($values) ? $values : [];
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function damageDropMap(array $values): array
    {
        if ($values === []) {
            return [];
        }

        $damageDrop = collect($values)
            ->mapWithKeys(static fn ($value, $key): array => [Str::snake((string) $key) => $value]);

        return $damageDrop
            ->put('total', $damageDrop->sum())
            ->toArray();
    }
}
