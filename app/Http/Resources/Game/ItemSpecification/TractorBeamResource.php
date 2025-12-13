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
        $rawParams = Arr::get($data, 'Raw.Entity.Components.SCItemWeaponComponentParams.fireActions.SWeaponActionFireTractorBeamParams', []);

        $force = [
            'min' => Arr::get($tractor, 'MinForce'),
            'max' => Arr::get($tractor, 'MaxForce'),
            'max_volume' => Arr::get($tractor, 'MaxVolume'),
            'volume_force_coefficient' => Arr::get($tractor, 'VolumeForceCoefficient'),
        ];

        $range = [
            'min_distance' => Arr::get($tractor, 'MinDistance'),
            'max_distance' => Arr::get($tractor, 'MaxDistance'),
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

        $energy = [
            'min_energy_draw' => Arr::get($tractor, 'MinEnergyDraw'),
            'max_energy_draw' => Arr::get($tractor, 'MaxEnergyDraw'),
            'heat_per_second' => Arr::get($tractor, 'HeatPerSecond'),
            'wear_per_second' => Arr::get($tractor, 'WearPerSecond'),
        ];

        $handling = [
            'max_player_look_rotation_scale' => Arr::get($tractor, 'MaxPlayerLookRotationScale'),
            'should_tractor_self' => Arr::has($tractor, 'ShouldTractorSelf') ? (bool) Arr::get($tractor, 'ShouldTractorSelf') : null,
            'should_fire_in_hangars' => Arr::has($tractor, 'ShouldFireInHangars') ? (bool) Arr::get($tractor, 'ShouldFireInHangars') : null,
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

        $movementParams = Arr::get($rawParams, 'movementParams', []);
        $movement = is_array($movementParams) ? [
            'acceleration_factor' => Arr::get($movementParams, 'accelerationFactor'),
            'max_acceleration' => Arr::get($movementParams, 'maxAcceleration'),
            'min_acceleration' => Arr::get($movementParams, 'minAcceleration'),
            'max_speed' => Arr::get($movementParams, 'maxSpeed'),
            'min_speed' => Arr::get($movementParams, 'minSpeed'),
            'enter_push_pull_threshold' => Arr::get($movementParams, 'enterPushPullThreshold'),
            'exit_push_pull_threshold' => Arr::get($movementParams, 'exitPushPullThreshold'),
            'rotation_single_axis_deadzone' => Arr::get($movementParams, 'rotationSingleAxisDeadzone'),
        ] : null;

        $rotationParams = Arr::get($rawParams, 'rotationParams', []);
        $rotation = is_array($rotationParams) ? [
            'degrees_per_action' => Arr::get($rotationParams, 'degreesPerAction'),
            'degrees_per_action_scroll_wheel' => Arr::get($rotationParams, 'degreesPerActionScrollWheel'),
            'force_fraction_rotation' => Arr::get($rotationParams, 'forceFractionRotation'),
            'max_angular_acceleration' => Arr::get($rotationParams, 'maxAngularAcceleration'),
            'max_angular_velocity' => Arr::get($rotationParams, 'maxAngularVelocity'),
        ] : null;

        $grappleParams = Arr::get($rawParams, 'grappleParams', []);
        $grapple = is_array($grappleParams) ? [
            'max_speed' => Arr::get($grappleParams, 'maxSpeed'),
            'max_acceleration' => Arr::get($grappleParams, 'maxAcceleration'),
            'coast_speed' => Arr::get($grappleParams, 'coastSpeed'),
            'positive_acceleration_limit' => Arr::get($grappleParams, 'positiveAccelerationLimit'),
            'negative_acceleration_limit' => Arr::get($grappleParams, 'negativeAccelerationLimit'),
            'look_orientation_influence_factor' => Arr::get($grappleParams, 'lookOrientationInfluenceFactor'),
        ] : null;

        $detachParams = Arr::get($rawParams, 'attachDetachParams', []);
        $detachment = is_array($detachParams) ? [
            'detach_aim_range' => Arr::get($detachParams, 'detachAimRange'),
            'release_distance_cargo_attachment' => Arr::get($detachParams, 'releaseDistanceCargoAttachment'),
            'release_distance_placement' => Arr::get($detachParams, 'releaseDistancePlacement'),
            'detach_acceleration_pop_amount' => Arr::get($detachParams, 'detachAccelerationPopAmount'),
            'throw_charge_time' => Arr::get($detachParams, 'throwChargeTime'),
            'min_throw_force' => Arr::get($detachParams, 'minThrowForce'),
            'max_throw_force' => Arr::get($detachParams, 'maxThrowForce'),
            'is_cargo_mode' => Arr::has($detachParams, 'isCargoMode') ? (bool) Arr::get($detachParams, 'isCargoMode') : null,
            'is_detach_mode' => Arr::has($detachParams, 'isDetachMode') ? (bool) Arr::get($detachParams, 'isDetachMode') : null,
            'vision_field_multiplier' => Arr::get($detachParams, 'visionFieldMultiplier'),
            'attach_holo_default_range' => Arr::get($detachParams, 'attachHoloDefaultRange'),
            'attach_holo_range_modifier' => Arr::get($detachParams, 'attachHoloRangeModifier'),
            'allowed_target_types' => collect(Arr::get($detachParams, 'allowedDetachTypes', []))
                ->pluck('Type')
                ->filter()
                ->values()
                ->all(),
        ] : null;

        $multitractorParams = Arr::get($rawParams, 'multitractorParams', []);
        $multitractor = is_array($multitractorParams) ? [
            'beam_alignment_slope_coefficient' => Arr::get($multitractorParams, 'beamAlignmentSlopeCoefficient'),
            'enter_lead_force_threshold_modifier' => Arr::get($multitractorParams, 'enterLeadForceThresholdModifier'),
            'follow_beam_deadzone_alignment' => Arr::get($multitractorParams, 'followBeamDeadzoneAlignment'),
            'follow_beam_deadzone_blend_start' => Arr::get($multitractorParams, 'followBeamDeadzoneBlendStart'),
            'follow_beam_deadzone_end' => Arr::get($multitractorParams, 'followBeamDeadzoneEnd'),
            'follow_beam_deadzone_misalignment' => Arr::get($multitractorParams, 'followBeamDeadzoneMisalignment'),
            'lead_beam_deadzone_blend_start' => Arr::get($multitractorParams, 'leadBeamDeadzoneBlendStart'),
            'lead_beam_deadzone_end' => Arr::get($multitractorParams, 'leadBeamDeadzoneEnd'),
        ] : null;

        $beamStrengthValues = Arr::get($rawParams, 'beamStrengthValues', []);
        $beamStrength = is_array($beamStrengthValues) ? [
            'stable' => Arr::get($beamStrengthValues, 'stable'),
            'unstable' => Arr::get($beamStrengthValues, 'unstable'),
            'breaking' => Arr::get($beamStrengthValues, 'breaking'),
            'impact_grapple' => Arr::get($beamStrengthValues, 'impactGrapple'),
            'impact_controlling_target' => Arr::get($beamStrengthValues, 'impactControllingTarget'),
            'impact_invalid_target' => Arr::get($beamStrengthValues, 'impactInvalidTarget'),
        ] : null;

        return [
            'force' => $force,
            'range' => $range,
            'tether' => $tether,
            'energy' => $energy,
            'handling' => $handling,
            'ammo_type' => Arr::get($tractor, 'AmmoType'),
            'cargo_mode_override' => $cargoModeOverride,
            'movement' => $movement,
            'rotation' => $rotation,
            'grapple' => $grapple,
            'detachment' => $detachment,
            'multitractor' => $multitractor,
            'beam_strength' => $beamStrength,

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
