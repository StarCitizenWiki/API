<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'tractor_beam_force',
    title: 'Tractor Beam Force',
    description: 'Push / pull strength and volume scaling for the beam.',
    properties: [
        new OA\Property(property: 'min', type: 'double', example: 1500, nullable: true),
        new OA\Property(property: 'max', type: 'double', example: 500000, nullable: true),
        new OA\Property(
            property: 'max_volume',
            description: 'Maximum object volume the beam can handle (µSCU).',
            type: 'double',
            example: 300000,
            nullable: true,
        ),
        new OA\Property(
            property: 'volume_force_coefficient',
            description: 'Force falloff based on target volume.',
            type: 'double',
            example: 0.3,
            nullable: true,
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'tractor_beam_range',
    title: 'Tractor Beam Range',
    description: 'Effective distances and cone limits.',
    properties: [
        new OA\Property(property: 'min_distance', type: 'double', example: 0.5, nullable: true),
        new OA\Property(property: 'max_distance', type: 'double', example: 135, nullable: true),
        new OA\Property(property: 'full_strength_distance', type: 'double', example: 68, nullable: true),
        new OA\Property(property: 'max_angle', type: 'double', example: 80, nullable: true),
        new OA\Property(property: 'hit_radius', type: 'double', example: 0.1, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'tractor_beam_energy',
    title: 'Tractor Beam Energy & Wear',
    properties: [
        new OA\Property(property: 'min_energy_draw', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'max_energy_draw', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'heat_per_second', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'wear_per_second', type: 'double', example: 0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'tractor_beam_tether',
    title: 'Tractor Beam Tether',
    properties: [
        new OA\Property(
            property: 'tether_break_time',
            description: 'Seconds before the tether breaks under excessive strain.',
            type: 'double',
            example: 2.25,
            nullable: true,
        ),
        new OA\Property(
            property: 'safe_range_value_factor',
            description: 'Safety factor used to determine stable range (typically 0.85).',
            type: 'double',
            example: 0.85,
            nullable: true,
        ),
        new OA\Property(
            property: 'allow_scrolling_into_breaking_range',
            description: 'Whether players can scroll past the safe range into breaking range.',
            type: 'boolean',
            example: true,
            nullable: true,
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'tractor_beam_handling',
    title: 'Tractor Beam Handling',
    properties: [
        new OA\Property(property: 'max_player_look_rotation_scale', type: 'double', example: 1, nullable: true),
        new OA\Property(property: 'should_tractor_self', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'should_fire_in_hangars', type: 'boolean', example: true, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'tractor_beam_movement',
    title: 'Tractor Beam Movement',
    description: 'How fast the beam can move or accelerate targets during push/pull.',
    properties: [
        new OA\Property(property: 'acceleration_factor', type: 'double', example: 5, nullable: true),
        new OA\Property(property: 'max_acceleration', type: 'double', example: 20, nullable: true),
        new OA\Property(property: 'min_acceleration', type: 'double', example: 0.1, nullable: true),
        new OA\Property(property: 'max_speed', type: 'double', example: 10, nullable: true),
        new OA\Property(property: 'min_speed', type: 'double', example: 5, nullable: true),
        new OA\Property(property: 'enter_push_pull_threshold', type: 'double', example: 0.5, nullable: true),
        new OA\Property(property: 'exit_push_pull_threshold', type: 'double', example: 0.1, nullable: true),
        new OA\Property(property: 'rotation_single_axis_deadzone', type: 'double', example: 10, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'tractor_beam_rotation',
    title: 'Tractor Beam Rotation',
    description: 'Rotation handling while holding a target.',
    properties: [
        new OA\Property(property: 'degrees_per_action', type: 'double', example: 10, nullable: true),
        new OA\Property(property: 'degrees_per_action_scroll_wheel', type: 'double', example: 1000, nullable: true),
        new OA\Property(property: 'force_fraction_rotation', type: 'double', example: 0.001, nullable: true),
        new OA\Property(property: 'max_angular_acceleration', type: 'double', example: 30, nullable: true),
        new OA\Property(property: 'max_angular_velocity', type: 'double', example: 2.5, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'tractor_beam_grapple',
    title: 'Tractor Beam Grapple',
    description: 'Limits applied when grappling a target.',
    properties: [
        new OA\Property(property: 'max_speed', type: 'double', example: 50, nullable: true),
        new OA\Property(property: 'max_acceleration', type: 'double', example: 10, nullable: true),
        new OA\Property(property: 'coast_speed', type: 'double', example: 10, nullable: true),
        new OA\Property(property: 'positive_acceleration_limit', type: 'double', example: 10, nullable: true),
        new OA\Property(property: 'negative_acceleration_limit', type: 'double', example: -10, nullable: true),
        new OA\Property(property: 'look_orientation_influence_factor', type: 'double', example: 0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'tractor_beam_detachment',
    title: 'Tractor Beam Detach/Attach',
    description: 'Throw/placement behaviour and valid targets when detaching.',
    properties: [
        new OA\Property(property: 'detach_aim_range', type: 'double', example: 1.5, nullable: true),
        new OA\Property(property: 'release_distance_cargo_attachment', type: 'double', example: 2, nullable: true),
        new OA\Property(property: 'release_distance_placement', type: 'double', example: 0.1, nullable: true),
        new OA\Property(property: 'detach_acceleration_pop_amount', type: 'double', example: 10, nullable: true),
        new OA\Property(property: 'throw_charge_time', type: 'double', example: 3, nullable: true),
        new OA\Property(property: 'min_throw_force', type: 'double', example: 5, nullable: true),
        new OA\Property(property: 'max_throw_force', type: 'double', example: 45, nullable: true),
        new OA\Property(property: 'is_cargo_mode', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'is_detach_mode', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'vision_field_multiplier', type: 'double', example: 0.9, nullable: true),
        new OA\Property(property: 'attach_holo_default_range', type: 'double', example: 0.5, nullable: true),
        new OA\Property(property: 'attach_holo_range_modifier', type: 'double', example: 0.2, nullable: true),
        new OA\Property(
            property: 'allowed_target_types',
            description: 'Item types this beam can detach/attach (Type values from weapon action params).',
            type: 'array',
            items: new OA\Items(type: 'string'),
            nullable: true,
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'tractor_beam_multitractor',
    title: 'Multi-Tractor Behaviour',
    description: 'How beams cooperate when multiple tractors are active.',
    properties: [
        new OA\Property(property: 'beam_alignment_slope_coefficient', type: 'double', example: 1.2, nullable: true),
        new OA\Property(property: 'enter_lead_force_threshold_modifier', type: 'double', example: -0.1, nullable: true),
        new OA\Property(property: 'follow_beam_deadzone_alignment', type: 'double', example: 0.8, nullable: true),
        new OA\Property(property: 'follow_beam_deadzone_blend_start', type: 'double', example: 3, nullable: true),
        new OA\Property(property: 'follow_beam_deadzone_end', type: 'double', example: 8, nullable: true),
        new OA\Property(property: 'follow_beam_deadzone_misalignment', type: 'double', example: -0.7, nullable: true),
        new OA\Property(property: 'lead_beam_deadzone_blend_start', type: 'double', example: 1.5, nullable: true),
        new OA\Property(property: 'lead_beam_deadzone_end', type: 'double', example: 3.5, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'tractor_beam_strength',
    title: 'Beam Strength States',
    description: 'Strength multipliers used for beam feedback and stability.',
    properties: [
        new OA\Property(property: 'stable', type: 'double', example: 1, nullable: true),
        new OA\Property(property: 'unstable', type: 'double', example: 0.5, nullable: true),
        new OA\Property(property: 'breaking', type: 'double', example: 0.1, nullable: true),
        new OA\Property(property: 'impact_grapple', type: 'double', example: 1, nullable: true),
        new OA\Property(property: 'impact_controlling_target', type: 'double', example: 1, nullable: true),
        new OA\Property(property: 'impact_invalid_target', type: 'double', example: 0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'tractor_beam_cargo_mode_override',
    title: 'Cargo Mode Overrides',
    description: 'Stronger cargo-handling profile used when grappling cargo or containers.',
    properties: [
        new OA\Property(property: 'min_force', type: 'double', example: 9450001, nullable: true),
        new OA\Property(property: 'max_force', type: 'double', example: 9500001, nullable: true),
        new OA\Property(property: 'min_acceleration', type: 'double', example: 15, nullable: true),
        new OA\Property(property: 'max_acceleration', type: 'double', example: 75, nullable: true),
        new OA\Property(property: 'min_speed', type: 'double', example: 12.25, nullable: true),
        new OA\Property(property: 'max_speed', type: 'double', example: 24.5, nullable: true),
        new OA\Property(property: 'acceleration_factor', type: 'double', example: 10, nullable: true),
        new OA\Property(property: 'degrees_per_action', type: 'double', example: 10, nullable: true),
        new OA\Property(property: 'max_angular_acceleration', type: 'double', example: 150, nullable: true),
        new OA\Property(property: 'max_angular_velocity', type: 'double', example: 55, nullable: true),
        new OA\Property(property: 'degrees_per_action_scroll_wheel', type: 'double', example: 1000, nullable: true),
        new OA\Property(property: 'force_fraction_rotation', type: 'double', example: 0.1, nullable: true),
        new OA\Property(property: 'min_distance', type: 'double', example: 0.5, nullable: true),
        new OA\Property(property: 'max_distance', type: 'double', example: 225, nullable: true),
        new OA\Property(property: 'full_strength_distance', type: 'double', example: 100, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'tractor_beam',
    title: 'Tractor Beam',
    description: 'Star Citizen tractor / towing beam gameplay stats from stdItem.TractorBeam plus select gameplay tuning fields from weapon action params.',
    properties: [
        new OA\Property(property: 'force', ref: '#/components/schemas/tractor_beam_force', nullable: true),
        new OA\Property(property: 'range', ref: '#/components/schemas/tractor_beam_range', nullable: true),
        new OA\Property(property: 'tether', ref: '#/components/schemas/tractor_beam_tether', nullable: true),
        new OA\Property(property: 'energy', ref: '#/components/schemas/tractor_beam_energy', nullable: true),
        new OA\Property(property: 'handling', ref: '#/components/schemas/tractor_beam_handling', nullable: true),
        new OA\Property(property: 'ammo_type', type: 'string', example: 'Primary', nullable: true),
        new OA\Property(property: 'cargo_mode_override', ref: '#/components/schemas/tractor_beam_cargo_mode_override', nullable: true),
        new OA\Property(property: 'movement', ref: '#/components/schemas/tractor_beam_movement', nullable: true),
        new OA\Property(property: 'rotation', ref: '#/components/schemas/tractor_beam_rotation', nullable: true),
        new OA\Property(property: 'grapple', ref: '#/components/schemas/tractor_beam_grapple', nullable: true),
        new OA\Property(property: 'detachment', ref: '#/components/schemas/tractor_beam_detachment', nullable: true),
        new OA\Property(property: 'multitractor', ref: '#/components/schemas/tractor_beam_multitractor', nullable: true),
        new OA\Property(property: 'beam_strength', ref: '#/components/schemas/tractor_beam_strength', nullable: true),
        // Legacy flat keys retained for backwards compatibility
        new OA\Property(property: 'min_force', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'max_force', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'min_distance', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'max_distance', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'full_strength_distance', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'max_angle', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'max_volume', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'volume_force_coefficient', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'tether_break_time', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'safe_range_value_factor', type: 'double', nullable: true, deprecated: true),
    ],
    type: 'object'
)]
class TractorBeamResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $stdItem = $this->extractStdItem($data);
        $tractor = Arr::get($stdItem, 'TractorBeam', []);

        $force = [
            'min' => Arr::get($tractor, 'MinForce'),
            'max' => Arr::get($tractor, 'MaxForce'),
            'max_volume' => Arr::get($tractor, 'MaxVolume'),
            'volume_force_coefficient' => Arr::get($tractor, 'VolumeForceCoefficient'),
        ];

        $range = [
            'min' => Arr::get($tractor, 'MinDistance'),
            'max' => Arr::get($tractor, 'MaxDistance'),
            'full_strength_distance' => Arr::get($tractor, 'FullStrengthDistance'),
            'max_angle' => Arr::get($tractor, 'MaxAngle'),
            'hit_radius' => Arr::get($tractor, 'HitRadius'),
        ];

        $tether = [
            'tether_break_time' => Arr::get($tractor, 'TetherBreakTime'),
            'safe_range_value_factor' => Arr::get($tractor, 'SafeRangeValueFactor'),
            'allow_scrolling_into_breaking_range' => Arr::has($tractor, 'AllowScrollingIntoBreakingRange')
                ? (bool) Arr::get($tractor, 'AllowScrollingIntoBreakingRange')
                : null,
        ];

        $cargoOverride = Arr::get($tractor, 'CargoModeOverride', []);
        $cargoModeOverride = is_array($cargoOverride) ? [
            'min_force' => Arr::get($cargoOverride, 'MinForceOverride'),
            'max_force' => Arr::get($cargoOverride, 'MaxForceOverride'),
            'min_acceleration' => Arr::get($cargoOverride, 'MinAccelerationOverride'),
            'max_acceleration' => Arr::get($cargoOverride, 'MaxAccelerationOverride'),
            'min_speed' => Arr::get($cargoOverride, 'MinSpeedOverride'),
            'max_speed' => Arr::get($cargoOverride, 'MaxSpeedOverride'),
            'acceleration_factor' => Arr::get($cargoOverride, 'AccelerationFactorOverride'),
            'degrees_per_action' => Arr::get($cargoOverride, 'DegreesPerActionOverride'),
            'max_angular_acceleration' => Arr::get($cargoOverride, 'MaxAngularAccelerationOverride'),
            'max_angular_velocity' => Arr::get($cargoOverride, 'MaxAngularVelocityOverride'),
            'degrees_per_action_scroll_wheel' => Arr::get($cargoOverride, 'DegreesPerActionScrollWheelOverride'),
            'force_fraction_rotation' => Arr::get($cargoOverride, 'ForceFractionRotationOverride'),
            'min_distance' => Arr::get($cargoOverride, 'MinDistanceOverride'),
            'max_distance' => Arr::get($cargoOverride, 'MaxDistanceOverride'),
            'full_strength_distance' => Arr::get($cargoOverride, 'FullStrengthDistanceOverride'),
        ] : null;

        return [
            'force' => $force,
            'range' => $range,
            'tether' => $tether,

            'cargo_mode_override' => $cargoModeOverride,

            'towing' => [
                'force' => Arr::get($tractor, 'Towing.TowingForce'),
                'max_acceleration' => Arr::get($tractor, 'Towing.TowingMaxAcceleration'),
                'max_distance' => Arr::get($tractor, 'Towing.TowingMaxDistance'),
                'qt_mass_limit' => Arr::get($tractor, 'Towing.QuantumTowMassLimit'),
            ],

            // Backward compatibility
            'min_force' => Arr::get($tractor, 'MinForce'),
            'max_force' => Arr::get($tractor, 'MaxForce'),
            'min_distance' => Arr::get($tractor, 'MinDistance'),
            'max_distance' => Arr::get($tractor, 'MaxDistance'),
            'full_strength_distance' => Arr::get($tractor, 'FullStrengthDistance'),
            'max_angle' => Arr::get($tractor, 'MaxAngle'),
            'max_volume' => Arr::get($tractor, 'MaxVolume'),
            'volume_force_coefficient' => Arr::get($tractor, 'VolumeForceCoefficient'),
            'tether_break_time' => Arr::get($tractor, 'TetherBreakTime'),
            'safe_range_value_factor' => Arr::get($tractor, 'SafeRangeValueFactor'),
        ];
    }
}
