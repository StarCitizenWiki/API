<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use App\Http\Resources\Game\Weapon\WeaponDamageResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'missile_flight',
    title: 'Missile Flight',
    description: 'Missile flight performance and phase timings.',
    properties: [
        new OA\Property(property: 'enable_lifetime', description: 'Whether the maximum lifetime limit is enforced.', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'max_lifetime', description: 'Maximum flight time in seconds before missile self-destructs.', type: 'double', example: 15.0, nullable: true),
        new OA\Property(property: 'range', description: 'Maximum travel distance/range as provided by the source data.', type: 'double', example: 10000.0, nullable: true),

        new OA\Property(property: 'speed', description: 'Linear cruise velocity in meters per second (LinearSpeed).', type: 'double', example: 1372.0, nullable: true),
        new OA\Property(property: 'boost_speed', description: 'Initial boost phase velocity in meters per second (BoostSpeed).', type: 'double', example: 165.0, nullable: true),
        new OA\Property(property: 'intercept_speed', description: 'Intercept phase speed in meters per second (InterceptSpeed).', type: 'double', example: 400.0, nullable: true),
        new OA\Property(property: 'terminal_speed', description: 'Terminal phase speed in meters per second (TerminalSpeed).', type: 'double', example: 475.0, nullable: true),

        new OA\Property(property: 'boost_phase_duration', description: 'Duration of the boost phase in seconds (BoostPhaseDuration).', type: 'double', example: 1.5, nullable: true),
        new OA\Property(property: 'terminal_phase_engagement_time', description: 'Seconds before impact when terminal phase activates (TerminalPhaseEngagementTime).', type: 'double', example: 5.0, nullable: true),
        new OA\Property(property: 'terminal_phase_engagement_angle', description: 'Maximum engagement angle in degrees for terminal phase (TerminalPhaseEngagementAngle).', type: 'double', example: 35.0, nullable: true),

        new OA\Property(property: 'fuel_tank_size', description: 'Fuel capacity in arbitrary units (FuelTankSize).', type: 'double', example: 25000.0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'missile_target_lock',
    title: 'Missile Target Lock',
    description: 'Target lock acquisition and countermeasure resilience parameters.',
    properties: [
        new OA\Property(property: 'signal_resilience_min', description: 'Minimum countermeasure resistance factor.', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'signal_resilience_max', description: 'Maximum countermeasure resistance factor.', type: 'double', example: 1.7, nullable: true),

        new OA\Property(property: 'range_max', description: 'Maximum lock acquisition range in meters.', type: 'double', example: 10000.0, nullable: true),
        new OA\Property(property: 'range_min', description: 'Minimum lock acquisition range in meters.', type: 'double', example: 1700.0, nullable: true),
        new OA\Property(property: 'angle', description: 'Maximum lock acquisition angle in degrees (cone of acquisition).', type: 'double', example: 60.0, nullable: true),

        new OA\Property(property: 'signal_amplifier', description: 'Signal amplification factor applied during lock acquisition.', type: 'double', example: 2.5, nullable: true),
        new OA\Property(property: 'increase_rate', description: 'Rate at which lock strength increases per second.', type: 'double', example: 1.6, nullable: true),

        new OA\Property(property: 'allow_dumb_firing', description: 'Whether the missile can be manually fired without a target lock.', type: 'boolean', example: true, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'missile_explosion',
    title: 'Missile Explosion',
    description: 'Explosion/warhead behavior including cluster properties, radius, and proximity settings.',
    properties: [
        new OA\Property(property: 'is_cluster', description: 'Whether this missile splits into multiple submunitions during flight.', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'cluster_size', description: 'Number of submunitions released when the missile splits (when applicable).', type: 'number', example: 3, nullable: true),
        new OA\Property(property: 'requires_launcher', description: 'Whether the missile must be fired from a dedicated launcher/rack.', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'allow_dumb_firing', description: 'Whether the missile can be fired without a lock.', type: 'boolean', example: true, nullable: true),

        new OA\Property(property: 'radius_min', description: 'Minimum explosion damage radius in meters.', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'radius_max', description: 'Maximum explosion damage radius in meters.', type: 'double', example: 2.0, nullable: true),

        new OA\Property(property: 'safety_distance', description: 'Minimum safe distance in meters from the explosion center.', type: 'double', example: 2.0, nullable: true),
        new OA\Property(property: 'proximity', description: 'Proximity fuse / projectile proximity value (as provided by source data).', type: 'double', example: 0.0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'missile_delays',
    title: 'Missile Delays',
    description: 'Arming/ignition/collision and lock timing delays.',
    properties: [
        new OA\Property(property: 'arm_time', description: 'Time in seconds after launch before warhead arms.', type: 'double', example: 0.8, nullable: true),
        new OA\Property(property: 'ignite_time', description: 'Delay in seconds between launch and engine ignition.', type: 'double', example: 0.1, nullable: true),
        new OA\Property(property: 'collision_delay_time', description: 'Delay in seconds before collision detection is active after launch.', type: 'double', example: 0.5, nullable: true),
        new OA\Property(property: 'lock_time', description: 'Time in seconds required to establish a target lock.', type: 'double', example: 0.5, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'missile_damage_map',
    title: 'Missile Damage Map',
    description: 'Map of damage type to value. Only non-null damage types are included; null when no damage values are present.',
    type: 'object',
    additionalProperties: new OA\AdditionalProperties(type: 'double')
)]
#[OA\Schema(
    schema: 'missile',
    title: 'Missile',
    description: 'Missile specifications sourced from stdItem.Missile.',
    properties: [
        new OA\Property(
            property: 'cluster_size',
            description: 'Deprecated. Use `explosion.cluster_size`.',
            type: 'number',
            example: 3,
            nullable: true,
            deprecated: true
        ),

        new OA\Property(
            property: 'signal_type',
            description: 'Type of tracking signal used for target acquisition.',
            type: 'string',
            example: 'CrossSection',
            nullable: true
        ),
        new OA\Property(
            property: 'tracking_signal_min',
            description: 'Minimum target signal strength required for tracking.',
            type: 'double',
            example: 4.0,
            nullable: true
        ),

        new OA\Property(
            property: 'lock_time',
            description: 'Deprecated. Use `delays.lock_time`.',
            type: 'double',
            example: 0.5,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'lock_range_max',
            description: 'Deprecated. Use `target_lock.range_max`.',
            type: 'double',
            example: 10000.0,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'lock_range_min',
            description: 'Deprecated. Use `target_lock.range_min`.',
            type: 'double',
            example: 1700.0,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'lock_angle',
            description: 'Deprecated. Use `target_lock.angle`.',
            type: 'double',
            example: 60.0,
            nullable: true,
            deprecated: true
        ),

        new OA\Property(
            property: 'speed',
            description: 'Deprecated. Use `flight.speed`.',
            type: 'double',
            example: 1372.0,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'fuel_tank_size',
            description: 'Deprecated. Use `flight.fuel_tank_size`.',
            type: 'double',
            example: 25000.0,
            nullable: true,
            deprecated: true
        ),

        new OA\Property(
            property: 'explosion_radius_min',
            description: 'Deprecated. Use `explosion.radius_min`.',
            type: 'double',
            example: 1.0,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'explosion_radius_max',
            description: 'Deprecated. Use `explosion.radius_max`.',
            type: 'double',
            example: 2.0,
            nullable: true,
            deprecated: true
        ),

        new OA\Property(property: 'flight', ref: '#/components/schemas/missile_flight'),
        new OA\Property(property: 'target_lock', ref: '#/components/schemas/missile_target_lock'),
        new OA\Property(property: 'explosion', ref: '#/components/schemas/missile_explosion'),
        new OA\Property(property: 'delays', ref: '#/components/schemas/missile_delays'),

        new OA\Property(
            property: 'damage_total',
            description: 'Total combined damage from all damage types.',
            type: 'double',
            example: 1075.0,
            nullable: true
        ),

        new OA\Property(
            property: 'damages',
            description: 'Deprecated. Use `damage_map`.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/weapon_damage'),
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'damage_map',
            ref: '#/components/schemas/missile_damage_map',
            description: 'Map of damage types to values (preferred representation).',
            nullable: true
        ),
    ],
    type: 'object'
)]
class MissileResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $missile = Arr::get($data, 'stdItem.Missile', []);
        $targeting = Arr::get($missile, 'Targeting', []);
        $gcs = Arr::get($missile, 'GCS', []);
        $damageData = Arr::get($missile, 'Damage', []);

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
            'cluster_size' => Arr::has($missile, 'Cluster.Size') ? Arr::get($missile, 'Cluster.Size') : null,
            'signal_type' => Arr::get($targeting, 'TrackingSignalType'),
            'tracking_signal_min' => Arr::get($targeting, 'TrackingSignalMin'),

            'lock_time' => Arr::get($targeting, 'LockTime'),
            'lock_range_max' => Arr::get($targeting, 'LockRangeMax'),
            'lock_range_min' => Arr::get($targeting, 'LockRangeMin'),
            'lock_angle' => Arr::get($targeting, 'LockingAngle'),
            'speed' => Arr::get($gcs, 'LinearSpeed'),
            'fuel_tank_size' => Arr::get($gcs, 'FuelTankSize'),
            'explosion_radius_min' => Arr::get($missile, 'ExplosionRadius.Minimum'),
            'explosion_radius_max' => Arr::get($missile, 'ExplosionRadius.Maximum'),

            'flight' => [
                'enable_lifetime' => Arr::get($missile, 'EnableLifetime'),
                'max_lifetime' => Arr::get($missile, 'MaxLifetime'),
                'range' => Arr::get($missile, 'Distance'),

                'speed' => Arr::get($gcs, 'LinearSpeed'),
                'boost_speed' => Arr::get($gcs, 'BoostSpeed'),
                'intercept_speed' => Arr::get($gcs, 'InterceptSpeed'),
                'terminal_speed' => Arr::get($gcs, 'TerminalSpeed'),

                'boost_phase_duration' => Arr::get($gcs, 'BoostPhaseDuration'),
                'terminal_phase_engagement_time' => Arr::get($gcs, 'TerminalPhaseEngagementTime'),
                'terminal_phase_engagement_angle' => Arr::get($gcs, 'TerminalPhaseEngagementAngle'),

                'fuel_tank_size' => Arr::get($gcs, 'FuelTankSize'),
            ],

            'target_lock' => [
                'signal_resilience_min' => Arr::get($targeting, 'SignalResilienceMin'),
                'signal_resilience_max' => Arr::get($targeting, 'SignalResilienceMax'),

                'range_max' => Arr::get($targeting, 'LockRangeMax'),
                'range_min' => Arr::get($targeting, 'LockRangeMin'),

                'angle' => Arr::get($targeting, 'LockingAngle'),

                'signal_amplifier' => Arr::get($targeting, 'LockSignalAmplifier'),
                'increase_rate' => Arr::get($targeting, 'LockIncreaseRate'),

                'allow_dumb_firing' => Arr::get($targeting, 'AllowDumbFiring'),
            ],

            'explosion' => [
                'is_cluster' => Arr::get($missile, 'IsCluster'),
                'cluster_size' => Arr::has($missile, 'Cluster.Size') ? Arr::get($missile, 'Cluster.Size') : null,
                'requires_launcher' => Arr::get($missile, 'RequiresLauncher'),
                'allow_dumb_firing' => Arr::get($targeting, 'AllowDumbFiring'),

                'radius_min' => Arr::get($missile, 'ExplosionRadius.Minimum'),
                'radius_max' => Arr::get($missile, 'ExplosionRadius.Maximum'),

                'safety_distance' => Arr::get($missile, 'ExplosionSafetyDistance'),
                'proximity' => Arr::get($missile, 'ProjectileProximity'),
            ],

            'delays' => [
                'arm_time' => Arr::get($missile, 'ArmTime'),
                'ignite_time' => Arr::get($missile, 'IgniteTime'),
                'collision_delay_time' => Arr::get($missile, 'CollisionDelayTime'),
                'lock_time' => Arr::get($targeting, 'LockTime'),
            ],

            'damage_total' => $totalDamage > 0 ? $totalDamage : null,
            // Deprecated:
            'damages' => WeaponDamageResource::collection($damages),
            'damage_map' => $damageMap === [] ? null : $damageMap,
        ];
    }
}
