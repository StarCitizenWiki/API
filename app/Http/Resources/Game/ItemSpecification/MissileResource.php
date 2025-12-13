<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use App\Http\Resources\Game\Weapon\WeaponDamageResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'missile',
    title: 'Missile',
    description: 'Comprehensive missile specifications including tracking, flight performance, countermeasure resistance, and damage characteristics. Missiles feature multi-phase flight dynamics (boost, intercept, terminal) and various targeting systems.',
    properties: [
        // === BASIC PROPERTIES ===
        new OA\Property(
            property: 'cluster_size',
            description: 'Number of submunitions released when the missile splits. Only applicable when is_cluster is true.',
            type: 'number',
            example: 3,
            nullable: true
        ),
        new OA\Property(
            property: 'is_cluster',
            description: 'Whether this missile splits into multiple submunitions during flight. Cluster missiles release multiple warheads for area coverage.',
            type: 'boolean',
            example: false,
            nullable: true
        ),
        new OA\Property(
            property: 'is_dumb_missile',
            description: 'Whether the missile operates in dumb-fire mode by default without guidance systems. Dumb missiles fly straight without tracking targets.',
            type: 'boolean',
            example: false,
            nullable: true
        ),
        new OA\Property(
            property: 'requires_launcher',
            description: 'Whether the missile must be fired from a dedicated missile rack or launcher. All combat missiles require launchers.',
            type: 'boolean',
            example: true,
            nullable: true
        ),

        // === TARGETING & LOCK MECHANICS ===
        new OA\Property(
            property: 'signal_type',
            description: 'Type of tracking signal used for target acquisition. CrossSection (radar) tracks ship hulls, Infrared tracks heat signatures, Electromagnetic tracks power signatures. Affects lock behavior and countermeasure effectiveness.',
            type: 'string',
            example: 'CrossSection',
            nullable: true
        ),
        new OA\Property(
            property: 'lock_time',
            description: 'Time in seconds required to establish a target lock. Light missiles typically 0.4-0.5s, heavy missiles 0.1-0.84s. Faster locks enable quick-reaction engagements.',
            type: 'double',
            example: 0.5,
            nullable: true
        ),
        new OA\Property(
            property: 'lock_range_max',
            description: 'Maximum lock acquisition range in meters. Typically 10,000m for most missiles. Targets beyond this range cannot be locked.',
            type: 'double',
            example: 10000.0,
            nullable: true
        ),
        new OA\Property(
            property: 'lock_range_min',
            description: 'Minimum lock acquisition range in meters. Typically 1,700-1,800m. Targets closer than this cannot be locked for safety.',
            type: 'double',
            example: 1700.0,
            nullable: true
        ),
        new OA\Property(
            property: 'lock_angle',
            description: 'Maximum angle in degrees from missile bore-sight for lock acquisition (cone of acquisition). Light missiles typically 60°, heavy missiles may have narrower 45° cones for precision.',
            type: 'double',
            example: 60.0,
            nullable: true
        ),
        new OA\Property(
            property: 'tracking_signal_min',
            description: 'Minimum target signal strength required for tracking. Varies by signal type: CrossSection (4-5), Infrared (15), Electromagnetic (13.86). Higher values require stronger target signatures.',
            type: 'double',
            example: 4.0,
            nullable: true
        ),
        new OA\Property(
            property: 'lock_signal_amplifier',
            description: 'Signal amplification factor applied during lock acquisition. Torpedoes use 5.0× amplification for strong locks, light missiles use 2.5×. Higher values enable locks on weaker signatures.',
            type: 'double',
            example: 2.5,
            nullable: true
        ),
        new OA\Property(
            property: 'lock_increase_rate',
            description: 'Rate at which lock strength increases per second. CrossSection typically 1.6, Infrared 4.0 (fastest), Electromagnetic 0.6 (slowest). Determines how quickly locks are established.',
            type: 'double',
            example: 1.6,
            nullable: true
        ),
        new OA\Property(
            property: 'allow_dumb_firing',
            description: 'Whether the missile can be manually fired without a target lock in unguided mode. Enables emergency firing or ballistic shots.',
            type: 'boolean',
            example: true,
            nullable: true
        ),

        // === COUNTERMEASURE RESISTANCE ===
        new OA\Property(
            property: 'signal_resilience_min',
            description: 'Minimum countermeasure resistance factor. Typically 1.0 for all missiles. Values above 1.0 make locks harder to break with chaff/flares.',
            type: 'double',
            example: 1.0,
            nullable: true
        ),
        new OA\Property(
            property: 'signal_resilience_max',
            description: 'Maximum countermeasure resistance factor. Typically 1.7 for all missiles. Higher values provide better resistance to countermeasures at close range.',
            type: 'double',
            example: 1.7,
            nullable: true
        ),

        // === FLIGHT PERFORMANCE ===
        new OA\Property(
            property: 'speed',
            description: 'Linear cruise velocity in meters per second during intercept phase. Light missiles (S1-S2): 1,000-1,400 m/s, torpedoes (S9-S10): 25-50 m/s. Primary factor in engagement time.',
            type: 'double',
            example: 1372.0,
            nullable: true
        ),
        new OA\Property(
            property: 'boost_speed',
            description: 'Initial boost phase velocity in meters per second immediately after launch. Light missiles have high boost (165-400 m/s) for rapid acceleration, torpedoes have low boost (15 m/s) for stable launch.',
            type: 'double',
            example: 165.0,
            nullable: true
        ),
        new OA\Property(
            property: 'boost_phase_duration',
            description: 'Duration of the initial boost phase in seconds. Typically 1.5-2.0s for most missiles. During this phase, missile accelerates to intercept speed.',
            type: 'double',
            example: 1.5,
            nullable: true
        ),
        new OA\Property(
            property: 'intercept_speed',
            description: 'Velocity in meters per second during main intercept/cruise phase. Represents sustained flight speed after boost ends. Light missiles: 400-1,000 m/s, torpedoes: 25-50 m/s.',
            type: 'double',
            example: 400.0,
            nullable: true
        ),
        new OA\Property(
            property: 'terminal_speed',
            description: 'Final approach velocity in meters per second during terminal phase. Light missiles accelerate to 475-1,400 m/s for penetration, torpedoes maintain 50 m/s for accuracy.',
            type: 'double',
            example: 475.0,
            nullable: true
        ),
        new OA\Property(
            property: 'terminal_phase_engagement_time',
            description: 'Time in seconds before impact when terminal phase activates. Typically 3-6s. Terminal phase enables maximum speed and final tracking corrections.',
            type: 'double',
            example: 5.0,
            nullable: true
        ),
        new OA\Property(
            property: 'terminal_phase_engagement_angle',
            description: 'Maximum angle in degrees from target at which terminal phase can engage. Typically 35° for most missiles. Ensures missile has clear approach vector.',
            type: 'double',
            example: 35.0,
            nullable: true
        ),
        new OA\Property(
            property: 'fuel_tank_size',
            description: 'Fuel capacity in arbitrary units. Larger values enable longer flight duration. Combined with speed determines maximum effective range.',
            type: 'double',
            example: 25000.0,
            nullable: true
        ),

        // === LIFETIME & TIMING ===
        new OA\Property(
            property: 'max_lifetime',
            description: 'Maximum flight time in seconds before missile self-destructs. Light missiles (S1): 15s, medium (S3): 35s, torpedoes (S9): 60s. Prevents indefinite flight and determines absolute maximum range.',
            type: 'double',
            example: 15.0,
            nullable: true
        ),
        new OA\Property(
            property: 'enable_lifetime',
            description: 'Whether the maximum lifetime limit is enforced. All combat missiles use lifetime enforcement to prevent indefinite flight.',
            type: 'boolean',
            example: true,
            nullable: true
        ),
        new OA\Property(
            property: 'arm_time',
            description: 'Time in seconds after launch before warhead arms and can detonate. Safety mechanism to prevent close-range detonation. Light missiles: 0.8-1.5s, medium missiles: 1.25s.',
            type: 'double',
            example: 0.8,
            nullable: true
        ),
        new OA\Property(
            property: 'ignite_time',
            description: 'Delay in seconds between launch and engine ignition. Determines when boost phase begins.',
            type: 'double',
            example: 0.1,
            nullable: true
        ),
        new OA\Property(
            property: 'collision_delay_time',
            description: 'Delay in seconds before collision detection and fusing become active after launch. Prevents premature detonation near launcher.',
            type: 'double',
            example: 0.5,
            nullable: true
        ),

        // === EXPLOSION & DAMAGE ===
        new OA\Property(
            property: 'explosion_safety_distance',
            description: 'Minimum safe distance in meters from the explosion center. Typically matches explosion radius. Used for AI safety calculations.',
            type: 'double',
            example: 2.0,
            nullable: true
        ),
        new OA\Property(
            property: 'explosion_radius_min',
            description: 'Minimum explosion damage radius in meters. Light missiles (S1): 1-2m, medium (S2): 1.5-3.2m. Smaller than maximum radius creates damage falloff zone.',
            type: 'double',
            example: 1.0,
            nullable: true
        ),
        new OA\Property(
            property: 'explosion_radius_max',
            description: 'Maximum explosion damage radius in meters. Light missiles (S1): 2m, medium (S2): 2.5-4.5m. Defines outer edge of blast zone with reduced damage.',
            type: 'double',
            example: 2.0,
            nullable: true
        ),
        new OA\Property(
            property: 'damage_total',
            description: 'Total combined damage from all damage types. Calculated sum of Physical, Energy, Distortion, Thermal, Biochemical, and Stun damage. Light missiles: 1,075-1,140, medium missiles: 1,670-2,400.',
            type: 'double',
            example: 1075.0,
            nullable: true
        ),
        new OA\Property(
            property: 'damages',
            description: 'Array of individual damage components by type. Missiles typically deal Physical damage as primary (90-95%), with possible Energy, Thermal, or Distortion secondary effects.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/weapon_damage'),
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

        return [
            // === BASIC PROPERTIES ===
            'cluster_size' => Arr::has($missile, 'Cluster.Size') ? Arr::get($missile, 'Cluster.Size') : null,
            'is_cluster' => Arr::get($missile, 'IsCluster'),
            'is_dumb_missile' => Arr::get($gcs, 'IsDumbMissile'),
            'requires_launcher' => Arr::get($missile, 'RequiresLauncher'),

            // === TARGETING & LOCK MECHANICS ===
            'signal_type' => Arr::get($targeting, 'TrackingSignalType'),
            'lock_time' => Arr::get($targeting, 'LockTime'),
            'lock_range_max' => Arr::get($targeting, 'LockRangeMax'),
            'lock_range_min' => Arr::get($targeting, 'LockRangeMin'),
            'lock_angle' => Arr::get($targeting, 'LockingAngle'),
            'tracking_signal_min' => Arr::get($targeting, 'TrackingSignalMin'),
            'lock_signal_amplifier' => Arr::get($targeting, 'LockSignalAmplifier'),
            'lock_increase_rate' => Arr::get($targeting, 'LockIncreaseRate'),
            'allow_dumb_firing' => Arr::get($targeting, 'AllowDumbFiring'),

            // === COUNTERMEASURE RESISTANCE ===
            'signal_resilience_min' => Arr::get($targeting, 'SignalResilienceMin'),
            'signal_resilience_max' => Arr::get($targeting, 'SignalResilienceMax'),

            // === FLIGHT PERFORMANCE ===
            'speed' => Arr::get($gcs, 'LinearSpeed'),
            'boost_speed' => Arr::get($gcs, 'BoostSpeed'),
            'boost_phase_duration' => Arr::get($gcs, 'BoostPhaseDuration'),
            'intercept_speed' => Arr::get($gcs, 'InterceptSpeed'),
            'terminal_speed' => Arr::get($gcs, 'TerminalSpeed'),
            'terminal_phase_engagement_time' => Arr::get($gcs, 'TerminalPhaseEngagementTime'),
            'terminal_phase_engagement_angle' => Arr::get($gcs, 'TerminalPhaseEngagementAngle'),
            'fuel_tank_size' => Arr::get($gcs, 'FuelTankSize'),

            // === LIFETIME & TIMING ===
            'max_lifetime' => Arr::get($missile, 'MaxLifetime'),
            'enable_lifetime' => Arr::get($missile, 'EnableLifetime'),
            'arm_time' => Arr::get($missile, 'ArmTime'),
            'ignite_time' => Arr::get($missile, 'IgniteTime'),
            'collision_delay_time' => Arr::get($missile, 'CollisionDelayTime'),

            // === EXPLOSION & DAMAGE ===
            'explosion_safety_distance' => Arr::get($missile, 'ExplosionSafetyDistance'),
            'explosion_radius_min' => Arr::get($missile, 'ExplosionMinRadius'),
            'explosion_radius_max' => Arr::get($missile, 'ExplosionMaxRadius'),
            'damage_total' => $totalDamage > 0 ? $totalDamage : null,
            'damages' => WeaponDamageResource::collection($damages),
        ];
    }
}
