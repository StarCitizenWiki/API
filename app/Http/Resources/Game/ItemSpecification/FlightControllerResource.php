<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'flight_controller',
    title: 'Flight Controller',
    description: 'IFCS (flight controller) performance data sourced from in-game item files. Includes SCM and boost speeds, precision mode caps, torque balancing thresholds, flight model multipliers, and afterburner capacitor behaviour.',
    properties: [
        new OA\Property(
            property: 'scm_speed',
            description: 'Space Combat Maneuvering (cruise) speed in meters per second. Most size 1 controllers fall in the 200–230 m/s range.',
            type: 'double',
            example: 227,
            nullable: true
        ),
        new OA\Property(
            property: 'boost_speed_forward',
            description: 'Forward boost speed cap in m/s. Typical civilian S1 controllers are around 450–500.',
            type: 'double',
            example: 470,
            nullable: true
        ),
        new OA\Property(
            property: 'boost_speed_backward',
            description: 'Reverse boost speed cap in m/s; lower than forward boost (usually ~50% of forward).',
            type: 'double',
            example: 240,
            nullable: true
        ),
        new OA\Property(
            property: 'max_speed',
            description: 'Absolute flight envelope speed cap in m/s outside of precision/IFCS constraints.',
            type: 'double',
            example: 1230,
            nullable: true
        ),
        new OA\Property(
            property: 'max_speed_precision_mode_full_proximity_assist',
            description: 'Precision mode max speed (m/s) with full proximity assist enabled; typically 10 m/s for landing.',
            type: 'double',
            example: 10,
            nullable: true
        ),
        new OA\Property(
            property: 'max_speed_precision_mode_zero_proximity_assist',
            description: 'Precision mode max speed (m/s) when proximity assist is disabled; commonly 30 m/s.',
            type: 'double',
            example: 30,
            nullable: true
        ),
        new OA\Property(
            property: 'torque_distance_threshold',
            description: 'Distance threshold in meters before torque imbalance compensation engages. Negative values disable the check.',
            type: 'double',
            example: 0.5,
            nullable: true
        ),
        new OA\Property(
            property: 'torque_imbalance_multiplier',
            description: 'Multiplier applied when torque imbalance is detected to stabilize rotation.',
            type: 'double',
            example: 0.3,
            nullable: true
        ),
        new OA\Property(
            property: 'refresh_caches_on_landing_mode',
            description: 'Flag (0/1) controlling cache refresh when entering landing mode.',
            type: 'double',
            example: 0,
            nullable: true
        ),
        new OA\Property(
            property: 'lift_multiplier',
            description: 'Lift multiplier applied to thruster output; higher values improve vertical authority in atmosphere.',
            type: 'double',
            example: 7,
            nullable: true
        ),
        new OA\Property(
            property: 'drag_multiplier',
            description: 'Drag multiplier scaling atmospheric drag calculations.',
            type: 'double',
            example: 5,
            nullable: true
        ),
        new OA\Property(
            property: 'precision_min_distance',
            description: 'Minimum distance in meters where precision landing assist calculations begin.',
            type: 'double',
            example: 5,
            nullable: true
        ),
        new OA\Property(
            property: 'precision_max_distance',
            description: 'Maximum distance in meters where precision landing assist calculations apply.',
            type: 'double',
            example: 50,
            nullable: true
        ),
        new OA\Property(
            property: 'precision_landing_multiplier',
            description: 'Multiplier applied to maneuvering inputs during precision landing to soften responses.',
            type: 'double',
            example: 0.7,
            nullable: true
        ),
        new OA\Property(
            property: 'linear_accel_decay',
            description: 'Decay rate applied to linear acceleration over time.',
            type: 'double',
            example: 6,
            nullable: true
        ),
        new OA\Property(
            property: 'angular_accel_decay',
            description: 'Decay rate applied to angular acceleration; higher values slow rotational response.',
            type: 'double',
            example: 12,
            nullable: true
        ),
        new OA\Property(
            property: 'scm_max_drag_multiplier',
            description: 'Drag multiplier applied specifically while in SCM flight.',
            type: 'double',
            example: 4,
            nullable: true
        ),
        new OA\Property(
            property: 'pitch',
            description: 'Maximum pitch rate in degrees per second.',
            type: 'double',
            example: 59,
            nullable: true
        ),
        new OA\Property(
            property: 'yaw',
            description: 'Maximum yaw rate in degrees per second.',
            type: 'double',
            example: 51,
            nullable: true
        ),
        new OA\Property(
            property: 'roll',
            description: 'Maximum roll rate in degrees per second.',
            type: 'double',
            example: 137,
            nullable: true
        ),
        new OA\Property(
            property: 'afterburner',
            description: 'Afterburner capacitor behaviour and timings.',
            properties: [
                new OA\Property(
                    property: 'pre_delay_time',
                    description: 'Seconds of delay before afterburner thrust begins once activated.',
                    type: 'double',
                    example: 0.0,
                    nullable: true
                ),
                new OA\Property(
                    property: 'ramp_up_time',
                    description: 'Seconds to reach full afterburner output.',
                    type: 'double',
                    example: 0.6,
                    nullable: true
                ),
                new OA\Property(
                    property: 'ramp_down_time',
                    description: 'Seconds to decay from afterburner to normal thrust.',
                    type: 'double',
                    example: 0.2,
                    nullable: true
                ),
                new OA\Property(
                    property: 'capacitor_threshold_ratio',
                    description: 'Minimum capacitor fraction required to engage afterburner (0–1).',
                    type: 'double',
                    example: 0.1,
                    nullable: true
                ),
                new OA\Property(
                    property: 'capacitor_max',
                    description: 'Maximum afterburner capacitor capacity.',
                    type: 'double',
                    example: 20,
                    nullable: true
                ),
                new OA\Property(
                    property: 'capacitor_afterburner_idle_cost',
                    description: 'Capacitor drain per second while afterburner is armed but not thrusting.',
                    type: 'double',
                    example: 1,
                    nullable: true
                ),
                new OA\Property(
                    property: 'capacitor_afterburner_linear_cost',
                    description: 'Capacitor drain per second for linear afterburner thrust.',
                    type: 'double',
                    example: 0,
                    nullable: true
                ),
                new OA\Property(
                    property: 'capacitor_afterburner_angular_cost',
                    description: 'Capacitor drain per second for angular afterburner thrust.',
                    type: 'double',
                    example: 0,
                    nullable: true
                ),
                new OA\Property(
                    property: 'capacitor_regen_delay_after_use',
                    description: 'Seconds of delay after disengaging before capacitor regen resumes.',
                    type: 'double',
                    example: 0.2,
                    nullable: true
                ),
                new OA\Property(
                    property: 'capacitor_regen_per_sec',
                    description: 'Capacitor regeneration rate per second.',
                    type: 'double',
                    example: 0.75,
                    nullable: true
                ),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'recall_params',
            description: 'Automated ship recall approach parameters used by docking AI.',
            properties: [
                new OA\Property(
                    property: 'hover_height_at_destination',
                    description: 'Target hover height in meters when arriving at the recall destination.',
                    type: 'double',
                    example: 30,
                    nullable: true
                ),
                new OA\Property(
                    property: 'forward_offset',
                    description: 'Forward offset in meters from the destination point where the ship stages before final landing.',
                    type: 'double',
                    example: 20,
                    nullable: true
                ),
                new OA\Property(
                    property: 'obstruction_detection_range',
                    description: 'Range in meters to scan for obstacles while recalling.',
                    type: 'double',
                    example: 1.2,
                    nullable: true
                ),
                new OA\Property(
                    property: 'default_platform_detection_range',
                    description: 'Detection radius in meters to locate a viable landing platform.',
                    type: 'double',
                    example: 50,
                    nullable: true
                ),
                new OA\Property(
                    property: 'minimum_recall_distance',
                    description: 'Minimum distance in meters from the player before recall engages.',
                    type: 'double',
                    example: 400,
                    nullable: true
                ),
                new OA\Property(
                    property: 'braking_distance_offset',
                    description: 'Extra buffer distance in meters used to start braking during recall approach.',
                    type: 'double',
                    example: 30,
                    nullable: true
                ),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'collision_detection',
            description: 'Collision warning thresholds calculated by IFCS.',
            properties: [
                new OA\Property(
                    property: 'collision_warn_speed',
                    description: 'Relative speed in m/s that triggers a collision warning.',
                    type: 'double',
                    example: 6,
                    nullable: true
                ),
                new OA\Property(
                    property: 'collision_warn_time',
                    description: 'Seconds until projected impact when general collision warning fires.',
                    type: 'double',
                    example: 4,
                    nullable: true
                ),
                new OA\Property(
                    property: 'collision_danger_close_warn_time',
                    description: 'Seconds until impact for the urgent/danger-close warning stage.',
                    type: 'double',
                    example: 2,
                    nullable: true
                ),
            ],
            type: 'object',
            nullable: true
        ),
    ],
    type: 'object'
)]
class FlightControllerResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $ifcs = Arr::get($data, 'stdItem.Ifcs', []);
        $afterburner = Arr::get($ifcs, 'Afterburner', []);
        $flightController = Arr::get($data, 'stdItem.FlightController', []);

        $afterburnerData = [
            'pre_delay_time' => Arr::get($afterburner, 'PreDelayTime'),
            'ramp_up_time' => Arr::get($afterburner, 'RampUpTime'),
            'ramp_down_time' => Arr::get($afterburner, 'RampDownTime'),
            'capacitor_threshold_ratio' => Arr::get($afterburner, 'CapacitorThresholdRatio'),
            'capacitor_max' => Arr::get($afterburner, 'CapacitorMax'),
            'capacitor_afterburner_idle_cost' => Arr::get($afterburner, 'CapacitorAfterburnerIdleCost'),
            'capacitor_afterburner_linear_cost' => Arr::get($afterburner, 'CapacitorAfterburnerLinearCost'),
            'capacitor_afterburner_angular_cost' => Arr::get($afterburner, 'CapacitorAfterburnerAngularCost'),
            'capacitor_regen_delay_after_use' => Arr::get($afterburner, 'CapacitorRegenDelayAfterUse'),
            'capacitor_regen_per_sec' => Arr::get($afterburner, 'CapacitorRegenPerSec'),
        ];

        $afterburnerData = $this->collapseEmpty($afterburnerData);

        return [
            'scm_speed' => Arr::get($ifcs, 'scmSpeed'),
            'boost_speed_forward' => Arr::get($ifcs, 'boostSpeedForward'),
            'boost_speed_backward' => Arr::get($ifcs, 'boostSpeedBackward'),
            'max_speed' => Arr::get($ifcs, 'maxSpeed'),
            'max_speed_precision_mode_full_proximity_assist' => Arr::get($ifcs, 'maxSpeedPrecisionModeFullProximityAssist'),
            'max_speed_precision_mode_zero_proximity_assist' => Arr::get($ifcs, 'maxSpeedPrecisionModeZeroProximityAssist'),
            'torque_distance_threshold' => Arr::get($ifcs, 'torqueDistanceThreshold'),
            'torque_imbalance_multiplier' => Arr::get($ifcs, 'torqueImbalanceMultiplier'),
            'refresh_caches_on_landing_mode' => Arr::get($ifcs, 'refreshCachesOnLandingMode'),
            'lift_multiplier' => Arr::get($ifcs, 'liftMultiplier'),
            'drag_multiplier' => Arr::get($ifcs, 'dragMultiplier'),
            'precision_min_distance' => Arr::get($ifcs, 'precisionMinDistance'),
            'precision_max_distance' => Arr::get($ifcs, 'precisionMaxDistance'),
            'precision_landing_multiplier' => Arr::get($ifcs, 'precisionLandingMultiplier'),
            'linear_accel_decay' => Arr::get($ifcs, 'linearAccelDecay'),
            'angular_accel_decay' => Arr::get($ifcs, 'angularAccelDecay'),
            'scm_max_drag_multiplier' => Arr::get($ifcs, 'scmMaxDragMultiplier'),
            'pitch' => Arr::get($ifcs, 'Pitch'),
            'yaw' => Arr::get($ifcs, 'Yaw'),
            'roll' => Arr::get($ifcs, 'Roll'),
            'afterburner' => $afterburnerData,
            'recall_params' => $this->collapseEmpty([
                'hover_height_at_destination' => Arr::get($flightController, 'RecallParams.HoverHeightAtDestination'),
                'forward_offset' => Arr::get($flightController, 'RecallParams.ForwardOffset'),
                'obstruction_detection_range' => Arr::get($flightController, 'RecallParams.ObstructionDetectionRange'),
                'default_platform_detection_range' => Arr::get($flightController, 'RecallParams.DefaultPlatformDetectionRange'),
                'minimum_recall_distance' => Arr::get($flightController, 'RecallParams.MinimumRecallDistance'),
                'braking_distance_offset' => Arr::get($flightController, 'RecallParams.BrakingDistanceOffset'),
            ]),
            'collision_detection' => $this->collapseEmpty([
                'collision_warn_speed' => Arr::get($flightController, 'CollisionDetection.CollisionWarnSpeed'),
                'collision_warn_time' => Arr::get($flightController, 'CollisionDetection.CollisionWarnTime'),
                'collision_danger_close_warn_time' => Arr::get($flightController, 'CollisionDetection.CollisionDangerCloseWarnTime'),
            ]),
        ];
    }

    /**
     * Collapse arrays that only contain null values to a single null.
     */
    protected function collapseEmpty(array $data): ?array
    {
        foreach ($data as $value) {
            if ($value !== null) {
                return $data;
            }
        }

        return null;
    }
}
