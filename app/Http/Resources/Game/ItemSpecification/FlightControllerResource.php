<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'flight_controller_boost_capacitor',
    title: 'Flight Controller Boost Capacitor',
    description: 'Afterburner (boost) capacitor configuration and regeneration settings.',
    properties: [
        new OA\Property(property: 'capacity', description: 'Maximum afterburner capacitor capacity.', type: 'double', example: 20, nullable: true),
        new OA\Property(property: 'threshold_ratio', description: 'Minimum capacitor fraction required to engage afterburner (0–1).', type: 'double', example: 0.1, nullable: true),
        new OA\Property(property: 'idle_cost', description: 'Capacitor drain per second while afterburner is armed but not thrusting.', type: 'double', example: 1, nullable: true),
        new OA\Property(property: 'linear_cost', description: 'Capacitor drain per second for linear afterburner thrust.', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'angular_cost', description: 'Capacitor drain per second for angular afterburner thrust.', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'regen_per_sec', description: 'Capacitor regeneration rate per second.', type: 'double', example: 0.75, nullable: true),
        new OA\Property(property: 'regen_delay', description: 'Seconds of delay after disengaging before capacitor regen resumes.', type: 'double', example: 0.2, nullable: true),
        new OA\Property(property: 'regen_time', description: 'Seconds required to fully regenerate the capacitor from empty (if provided by data).', type: 'double', example: 10, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'flight_controller_boost_activation',
    title: 'Flight Controller Boost Activation',
    description: 'Afterburner (boost) activation timings.',
    properties: [
        new OA\Property(property: 'pre_delay_time', description: 'Seconds of delay before afterburner thrust begins once activated.', type: 'double', example: 0.0, nullable: true),
        new OA\Property(property: 'ramp_up_time', description: 'Seconds to reach full afterburner output.', type: 'double', example: 0.6, nullable: true),
        new OA\Property(property: 'ramp_down_time', description: 'Seconds to decay from afterburner to normal thrust.', type: 'double', example: 0.2, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'flight_controller_thruster_decay',
    title: 'Flight Controller Thruster Decay',
    description: 'Decay rates applied to acceleration.',
    properties: [
        new OA\Property(property: 'linear_accel', description: 'Decay rate applied to linear acceleration over time.', type: 'double', example: 6, nullable: true),
        new OA\Property(property: 'angular_accel', description: 'Decay rate applied to angular acceleration; higher values slow rotational response.', type: 'double', example: 12, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'flight_controller_multiplier',
    title: 'Flight Controller Multipliers',
    description: 'Flight model multipliers sourced from IFCS.',
    properties: [
        new OA\Property(property: 'torque_imbalance', description: 'Multiplier applied when torque imbalance is detected to stabilize rotation.', type: 'double', example: 0.3, nullable: true),
        new OA\Property(property: 'lift', description: 'Lift multiplier applied to thruster output.', type: 'double', example: 7, nullable: true),
        new OA\Property(property: 'drag', description: 'Drag multiplier scaling atmospheric drag calculations.', type: 'double', example: 5, nullable: true),
        new OA\Property(property: 'scm_max_drag', description: 'Drag multiplier applied specifically while in SCM flight.', type: 'double', example: 4, nullable: true),
        new OA\Property(property: 'precision_landing', description: 'Multiplier applied to maneuvering inputs during precision landing to soften responses.', type: 'double', example: 0.7, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'flight_controller_signed_axis_multiplier',
    title: 'Flight Controller Signed Axis Multiplier',
    description: 'Positive/negative multipliers for a single axis.',
    properties: [
        new OA\Property(property: 'positive', description: 'Multiplier for positive thrust on this axis.', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'negative', description: 'Multiplier for negative thrust on this axis.', type: 'double', example: 1.0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'flight_controller_boost_multiplier',
    title: 'Flight Controller Boost Multipliers',
    description: 'Afterburner (boost) multipliers applied to acceleration and angular rates.',
    properties: [
        new OA\Property(property: 'accel_x', ref: '#/components/schemas/flight_controller_signed_axis_multiplier'),
        new OA\Property(property: 'accel_y', ref: '#/components/schemas/flight_controller_signed_axis_multiplier'),
        new OA\Property(property: 'accel_z', ref: '#/components/schemas/flight_controller_signed_axis_multiplier'),

        new OA\Property(property: 'pitch', description: 'Angular rate multiplier for pitch.', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'yaw', description: 'Angular rate multiplier for yaw.', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'roll', description: 'Angular rate multiplier for roll.', type: 'double', example: 1.0, nullable: true),

        new OA\Property(property: 'pitch_accel', description: 'Angular acceleration multiplier for pitch.', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'yaw_accel', description: 'Angular acceleration multiplier for yaw.', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'roll_accel', description: 'Angular acceleration multiplier for roll.', type: 'double', example: 1.0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'flight_controller_precision_mode',
    title: 'Flight Controller Precision Mode',
    description: 'Precision mode speed caps and distance thresholds.',
    properties: [
        new OA\Property(property: 'max_speed_full_proximity_assist', description: 'Precision mode max speed (m/s) with full proximity assist enabled.', type: 'double', example: 10, nullable: true),
        new OA\Property(property: 'max_speed_zero_proximity_assist', description: 'Precision mode max speed (m/s) when proximity assist is disabled.', type: 'double', example: 30, nullable: true),
        new OA\Property(property: 'min_distance', description: 'Minimum distance in meters where precision landing assist calculations begin.', type: 'double', example: 5, nullable: true),
        new OA\Property(property: 'max_distance', description: 'Maximum distance in meters where precision landing assist calculations apply.', type: 'double', example: 50, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'flight_controller_recall_params',
    title: 'Flight Controller Recall Params',
    description: 'Automated ship recall approach parameters used by docking AI. Can be null if all values are null.',
    properties: [
        new OA\Property(property: 'hover_height_at_destination', description: 'Target hover height in meters when arriving at the recall destination.', type: 'double', example: 30, nullable: true),
        new OA\Property(property: 'forward_offset', description: 'Forward offset in meters from the destination point where the ship stages before final landing.', type: 'double', example: 20, nullable: true),
        new OA\Property(property: 'obstruction_detection_range', description: 'Range in meters to scan for obstacles while recalling.', type: 'double', example: 1.2, nullable: true),
        new OA\Property(property: 'default_platform_detection_range', description: 'Detection radius in meters to locate a viable landing platform.', type: 'double', example: 50, nullable: true),
        new OA\Property(property: 'minimum_recall_distance', description: 'Minimum distance in meters from the player before recall engages.', type: 'double', example: 400, nullable: true),
        new OA\Property(property: 'braking_distance_offset', description: 'Extra buffer distance in meters used to start braking during recall approach.', type: 'double', example: 30, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'flight_controller_collision_detection',
    title: 'Flight Controller Collision Detection',
    description: 'Collision warning thresholds calculated by IFCS. Can be null if all values are null.',
    properties: [
        new OA\Property(property: 'collision_warn_speed', description: 'Relative speed in m/s that triggers a collision warning.', type: 'double', example: 6, nullable: true),
        new OA\Property(property: 'collision_warn_time', description: 'Seconds until projected impact when general collision warning fires.', type: 'double', example: 4, nullable: true),
        new OA\Property(property: 'collision_danger_close_warn_time', description: 'Seconds until impact for the urgent/danger-close warning stage.', type: 'double', example: 2, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'flight_controller_gravlev',
    title: 'Flight Controller Gravlev',
    description: 'Gravlev-related flight controller settings.',
    properties: [
        new OA\Property(property: 'max_speed', description: 'Maximum gravlev hover speed.', type: 'double', example: 30, nullable: true),
        new OA\Property(property: 'turn_friction', description: 'Turning friction factor for gravlev.', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'air_controller_multiplier', description: 'Multiplier for air control when using gravlev.', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'anti_fall_multiplier', description: 'Multiplier used to counteract falling behaviour.', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'lateral_strafe_multiplier', description: 'Multiplier for lateral strafing while in gravlev mode.', type: 'double', example: 1.0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'flight_controller',
    title: 'Flight Controller',
    description: 'IFCS (flight controller) performance data sourced from in-game item files.',
    properties: [
        new OA\Property(property: 'scm_speed', description: 'Space Combat Maneuvering (cruise) speed in meters per second.', type: 'double', example: 227, nullable: true),
        new OA\Property(property: 'boost_speed_forward', description: 'Forward boost speed cap in m/s.', type: 'double', example: 470, nullable: true),
        new OA\Property(property: 'boost_speed_backward', description: 'Reverse boost speed cap in m/s.', type: 'double', example: 240, nullable: true),
        new OA\Property(property: 'max_speed', description: 'Absolute flight envelope speed cap in m/s.', type: 'double', example: 1230, nullable: true),

        new OA\Property(property: 'pitch', description: 'Maximum pitch rate in degrees per second.', type: 'double', example: 59, nullable: true),
        new OA\Property(property: 'yaw', description: 'Maximum yaw rate in degrees per second.', type: 'double', example: 51, nullable: true),
        new OA\Property(property: 'roll', description: 'Maximum roll rate in degrees per second.', type: 'double', example: 137, nullable: true),

        new OA\Property(
            property: 'pitch_boosted',
            description: 'Derived boosted pitch rate (rounded): pitch * boost_multiplier.pitch (defaults to 1 if missing).',
            type: 'integer',
            example: 59,
            nullable: false
        ),
        new OA\Property(
            property: 'yaw_boosted',
            description: 'Derived boosted yaw rate (rounded): yaw * boost_multiplier.yaw (defaults to 1 if missing).',
            type: 'integer',
            example: 51,
            nullable: false
        ),
        new OA\Property(
            property: 'roll_boosted',
            description: 'Derived boosted roll rate (rounded): roll * boost_multiplier.roll (defaults to 1 if missing).',
            type: 'integer',
            example: 137,
            nullable: false
        ),

        new OA\Property(property: 'boost_capacitor', ref: '#/components/schemas/flight_controller_boost_capacitor', description: 'Boost capacitor stats.'),
        new OA\Property(property: 'boost_activation', ref: '#/components/schemas/flight_controller_boost_activation', description: 'Boost activation timings.'),
        new OA\Property(property: 'thruster_decay', ref: '#/components/schemas/flight_controller_thruster_decay', description: 'Acceleration decay settings.'),
        new OA\Property(property: 'multiplier', ref: '#/components/schemas/flight_controller_multiplier', description: 'IFCS multipliers.'),
        new OA\Property(property: 'boost_multiplier', ref: '#/components/schemas/flight_controller_boost_multiplier', description: 'Boost (afterburner) multipliers.'),
        new OA\Property(property: 'precision_mode', ref: '#/components/schemas/flight_controller_precision_mode', description: 'Precision mode settings.'),

        new OA\Property(property: 'recall_params', ref: '#/components/schemas/flight_controller_recall_params', description: 'Ship recall approach parameters.', nullable: true),
        new OA\Property(property: 'collision_detection', ref: '#/components/schemas/flight_controller_collision_detection', description: 'Collision warning thresholds.', nullable: true),

        new OA\Property(property: 'gravlev', ref: '#/components/schemas/flight_controller_gravlev', description: 'Gravlev-related settings.'),
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

        return [
            'scm_speed' => Arr::get($ifcs, 'ScmSpeed'),
            'boost_speed_forward' => Arr::get($ifcs, 'BoostSpeedForward'),
            'boost_speed_backward' => Arr::get($ifcs, 'BoostSpeedBackward'),
            'max_speed' => Arr::get($ifcs, 'MaxSpeed'),

            'pitch' => Arr::get($ifcs, 'Pitch'),
            'yaw' => Arr::get($ifcs, 'Yaw'),
            'roll' => Arr::get($ifcs, 'Roll'),

            'pitch_boosted' => Arr::get($ifcs, 'PitchBoosted'),
            'yaw_boosted' => Arr::get($ifcs, 'YawBoosted'),
            'roll_boosted' => Arr::get($ifcs, 'RollBoosted'),

            'boost_capacitor' => [
                'capacity' => Arr::get($afterburner, 'CapacitorMax'),
                'threshold_ratio' => Arr::get($afterburner, 'AfterburnerCapacitorThresholdRatio'),
                'idle_cost' => Arr::get($afterburner, 'CapacitorAfterburnerIdleCost'),
                'linear_cost' => Arr::get($afterburner, 'CapacitorAfterburnerLinearCost'),
                'angular_cost' => Arr::get($afterburner, 'CapacitorAfterburnerAngularCost'),
                'regen_per_sec' => Arr::get($afterburner, 'CapacitorRegenPerSec'),
                'regen_delay' => Arr::get($afterburner, 'CapacitorRegenDelayAfterUse'),
                'regen_time' => Arr::get($afterburner, 'RegenTime'),
            ],

            'boost_activation' => [
                'pre_delay_time' => Arr::get($afterburner, 'AfterburnerPreDelayTime'),
                'ramp_up_time' => Arr::get($afterburner, 'AfterburnerRampUpTime'),
                'ramp_down_time' => Arr::get($afterburner, 'AfterburnerRampDownTime'),
            ],

            'thruster_decay' => [
                'linear_accel' => Arr::get($ifcs, 'LinearAccelDecay'),
                'angular_accel' => Arr::get($ifcs, 'AngularAccelDecay'),
            ],

            'multiplier' => [
                'torque_imbalance' => Arr::get($ifcs, 'TorqueImbalanceMultiplier'),
                'lift' => Arr::get($ifcs, 'LiftMultiplier'),
                'drag' => Arr::get($ifcs, 'DragMultiplier'),
                'scm_max_drag' => Arr::get($ifcs, 'ScmMaxDragMultiplier'),
                'precision_landing' => Arr::get($ifcs, 'PrecisionLandingMultiplier'),
            ],

            'boost_multiplier' => [
                'accel_x' => [
                    'positive' => Arr::get($afterburner, 'AccelerationMultiplierPositive.x'),
                    'negative' => Arr::get($afterburner, 'AccelerationMultiplierNegative.x'),
                ],
                'accel_y' => [
                    'positive' => Arr::get($afterburner, 'AccelerationMultiplierPositive.y'),
                    'negative' => Arr::get($afterburner, 'AccelerationMultiplierNegative.y'),
                ],
                'accel_z' => [
                    'positive' => Arr::get($afterburner, 'AccelerationMultiplierPositive.z'),
                    'negative' => Arr::get($afterburner, 'AccelerationMultiplierNegative.z'),
                ],
                'pitch' => Arr::get($afterburner, 'AngularMultiplier.Pitch'),
                'yaw' => Arr::get($afterburner, 'AngularMultiplier.Yaw'),
                'roll' => Arr::get($afterburner, 'AngularMultiplier.Roll'),

                'pitch_accel' => Arr::get($afterburner, 'AngularAccelerationMultiplier.Pitch'),
                'yaw_accel' => Arr::get($afterburner, 'AngularAccelerationMultiplier.Yaw'),
                'roll_accel' => Arr::get($afterburner, 'AngularAccelerationMultiplier.Roll'),
            ],

            'precision_mode' => [
                'max_speed_full_proximity_assist' => Arr::get($ifcs, 'MaxSpeedPrecisionModeFullProximityAssist'),
                'max_speed_zero_proximity_assist' => Arr::get($ifcs, 'MaxSpeedPrecisionModeZeroProximityAssist'),

                'min_distance' => Arr::get($ifcs, 'PrecisionMinDistance'),
                'max_distance' => Arr::get($ifcs, 'PrecisionMaxDistance'),
            ],

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

            'gravlev' => [
                'max_speed' => Arr::get($flightController, 'Gravlev.HoverMaxSpeed'),
                'turn_friction' => Arr::get($flightController, 'Gravlev.TurnFriction'),
                'air_controller_multiplier' => Arr::get($flightController, 'Gravlev.AirControllerMultiplier'),
                'anti_fall_multiplier' => Arr::get($flightController, 'Gravlev.AntiFallMultiplier'),
                'lateral_strafe_multiplier' => Arr::get($flightController, 'Gravlev.LateralStafeMultiplier'),
            ],
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
