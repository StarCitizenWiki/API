<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use App\Http\Resources\Game\Weapon\WeaponDamageResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'bomb',
    title: 'Bomb',
    description: 'Explosive bomb specifications including timing, blast radius, and damage characteristics. Bomb properties scale with size class (S3/S5/S10).',
    properties: [
        new OA\Property(
            property: 'arm_time',
            description: 'Time in seconds after release before the bomb is armed and can detonate. Scales with bomb size (0.5s for S3, 5.0s for S10).',
            type: 'double',
            example: 3.0,
            nullable: true,
            x: ['suffix' => ' s']
        ),
        new OA\Property(
            property: 'ignite_time',
            description: 'Time in seconds between arming and detonation.',
            type: 'double',
            example: 0.2,
            nullable: true,
            x: ['suffix' => ' s']
        ),
        new OA\Property(
            property: 'collision_delay_time',
            description: 'Delay in seconds before collision detection becomes active after release.',
            type: 'double',
            example: 0.5,
            nullable: true,
            x: ['suffix' => ' s']
        ),
        new OA\Property(
            property: 'explosion_safety_distance',
            description: 'Deprecated: Use explosion.safety_distance instead. Minimum safe distance in meters from the explosion. Typically 50m for all bomb sizes.',
            type: 'double',
            example: 50.0,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'explosion_radius_min',
            description: 'Deprecated: Use explosion.radius_min instead. Minimum explosion radius in meters. Scales with bomb size (40m for S3, 100m for S5, 250m for S10).',
            type: 'double',
            example: 100.0,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'explosion_radius_max',
            description: 'Deprecated: Use explosion.radius_max instead. Maximum explosion radius in meters. Typically equal to minimum radius for uniform blast.',
            type: 'double',
            example: 100.0,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'maximum_drop_angle',
            description: 'Maximum angle in degrees from level flight at which the bomb can be deployed. Typically 90 degrees.',
            type: 'double',
            example: 90.0,
            nullable: true,
            x: ['suffix' => ' °']
        ),
        new OA\Property(
            property: 'damage',
            description: 'Deprecated: use damage_total.',
            type: 'double',
            example: 46702.0,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'damage_total',
            description: 'Total combined damage from all damage types. Scales dramatically with bomb size (27,000 for S3, 568,297 for S10).',
            type: 'double',
            example: 46702.0,
            nullable: true
        ),
        new OA\Property(
            property: 'damages',
            description: 'Deprecated: use damage_map.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/weapon_damage'),
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'explosion',
            description: 'Grouped explosion parameters. Replacement for individual explosion_* fields.',
            properties: [
                new OA\Property(property: 'requires_launcher', description: 'Whether the bomb requires a launcher to deploy.', type: 'boolean', nullable: true),
                new OA\Property(property: 'radius_min', description: 'Minimum explosion radius in meters. Replacement for explosion_radius_min.', type: 'double', nullable: true, x: ['suffix' => ' m']),
                new OA\Property(property: 'radius_max', description: 'Maximum explosion radius in meters. Replacement for explosion_radius_max.', type: 'double', nullable: true, x: ['suffix' => ' m']),
                new OA\Property(property: 'safety_distance', description: 'Minimum safe distance in meters from the explosion. Replacement for explosion_safety_distance.', type: 'double', nullable: true, x: ['suffix' => ' m']),
                new OA\Property(property: 'proximity', description: 'Proximity trigger distance for detonation.', type: 'double', nullable: true, x: ['suffix' => ' m']),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'delays',
            description: 'Grouped timing parameters for bomb arming and detonation.',
            properties: [
                new OA\Property(property: 'arm_time', description: 'Time in seconds after release before the bomb is armed. Same as top-level arm_time.', type: 'double', nullable: true, x: ['suffix' => ' s']),
                new OA\Property(property: 'ignite_time', description: 'Time in seconds between arming and detonation. Same as top-level ignite_time.', type: 'double', nullable: true, x: ['suffix' => ' s']),
                new OA\Property(property: 'collision_delay_time', description: 'Delay before collision detection becomes active. Same as top-level collision_delay_time.', type: 'double', nullable: true, x: ['suffix' => ' s']),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'damage_map',
            description: 'Damage values mapped by snake_case damage type keys. Replacement for damages array.',
            properties: [
                new OA\Property(property: 'physical', description: 'Physical damage value.', type: 'double', nullable: true),
                new OA\Property(property: 'energy', description: 'Energy damage value.', type: 'double', nullable: true),
                new OA\Property(property: 'distortion', description: 'Distortion damage value.', type: 'double', nullable: true),
                new OA\Property(property: 'thermal', description: 'Thermal damage value.', type: 'double', nullable: true),
                new OA\Property(property: 'biochemical', description: 'Biochemical damage value.', type: 'double', nullable: true),
                new OA\Property(property: 'stun', description: 'Stun damage value.', type: 'double', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
    ],
    type: 'object'
)]
class BombResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $bomb = Arr::get($data, 'stdItem.Bomb', []);
        $damageData = Arr::get($bomb, 'Damage', []);

        $damages = $this->buildDamageArray($damageData);
        $totalDamage = $this->calculateTotalDamage($damageData);
        $damageMap = array_filter([
            'physical' => Arr::get($damageData, 'Physical'),
            'energy' => Arr::get($damageData, 'Energy'),
            'distortion' => Arr::get($damageData, 'Distortion'),
            'thermal' => Arr::get($damageData, 'Thermal'),
            'biochemical' => Arr::get($damageData, 'Biochemical'),
            'stun' => Arr::get($damageData, 'Stun'),
        ], static fn ($value) => $value !== null);

        return [
            'arm_time' => Arr::get($bomb, 'ArmTime'),
            'ignite_time' => Arr::get($bomb, 'IgniteTime'),
            'collision_delay_time' => Arr::get($bomb, 'CollisionDelayTime'),
            'explosion_safety_distance' => Arr::get($bomb, 'ExplosionSafetyDistance'),
            'explosion_radius_min' => Arr::get($bomb, 'ExplosionMinRadius'),
            'explosion_radius_max' => Arr::get($bomb, 'ExplosionMaxRadius'),
            'maximum_drop_angle' => Arr::get($bomb, 'MaximumDropAngleFromFlatFlight'),

            'explosion' => [
                'requires_launcher' => Arr::get($bomb, 'RequiresLauncher'),

                'radius_min' => Arr::get($bomb, 'ExplosionMinRadius'),
                'radius_max' => Arr::get($bomb, 'ExplosionMaxRadius'),

                'safety_distance' => Arr::get($bomb, 'ExplosionSafetyDistance'),
                'proximity' => Arr::get($bomb, 'ProjectileProximity'),
            ],

            'delays' => [
                'arm_time' => Arr::get($bomb, 'ArmTime'),
                'ignite_time' => Arr::get($bomb, 'IgniteTime'),
                'collision_delay_time' => Arr::get($bomb, 'CollisionDelayTime'),
            ],

            'damage' => $totalDamage > 0 ? $totalDamage : null, // V2 compatibility
            'damage_total' => $totalDamage > 0 ? $totalDamage : null,
            'damages' => WeaponDamageResource::collection($damages),
            'damage_map' => $damageMap === [] ? null : $damageMap,
        ];
    }
}
