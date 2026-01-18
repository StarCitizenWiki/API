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
        new OA\Property(property: 'min', description: 'Minimum effective distance.', type: 'double', example: 0.5, nullable: true),
        new OA\Property(property: 'max', description: 'Maximum effective distance.', type: 'double', example: 135, nullable: true),
        new OA\Property(property: 'full_strength_distance', type: 'double', example: 68, nullable: true),
        new OA\Property(property: 'max_angle', type: 'double', example: 80, nullable: true),
        new OA\Property(property: 'hit_radius', type: 'double', example: 0.1, nullable: true),
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
    schema: 'tractor_beam_towing',
    title: 'Tractor Beam Towing',
    description: 'Towing parameters emitted under the `towing` key.',
    properties: [
        new OA\Property(property: 'force', description: 'Towing force (Towing.TowingForce).', type: 'double', example: 100000, nullable: true),
        new OA\Property(property: 'max_acceleration', description: 'Maximum towing acceleration (Towing.TowingMaxAcceleration).', type: 'double', example: 10, nullable: true),
        new OA\Property(property: 'max_distance', description: 'Maximum towing distance (Towing.TowingMaxDistance).', type: 'double', example: 100, nullable: true),
        new OA\Property(property: 'qt_mass_limit', description: 'Quantum tow mass limit (Towing.QuantumTowMassLimit).', type: 'double', example: 0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'tractor_beam',
    title: 'Tractor Beam',
    description: 'Star Citizen tractor / towing beam gameplay stats from stdItem.TractorBeam.',
    properties: [
        new OA\Property(property: 'force', ref: '#/components/schemas/tractor_beam_force', nullable: true),
        new OA\Property(property: 'range', ref: '#/components/schemas/tractor_beam_range', nullable: true),
        new OA\Property(property: 'tether', ref: '#/components/schemas/tractor_beam_tether', nullable: true),
        new OA\Property(property: 'cargo_mode_override', ref: '#/components/schemas/tractor_beam_cargo_mode_override', nullable: true),
        new OA\Property(property: 'towing', ref: '#/components/schemas/tractor_beam_towing', nullable: true),

        // Legacy flat keys retained for backwards compatibility
        new OA\Property(
            property: 'min_force',
            description: 'Deprecated. Use `force.min`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'max_force',
            description: 'Deprecated. Use `force.max`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'min_distance',
            description: 'Deprecated. Use `range.min`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'max_distance',
            description: 'Deprecated. Use `range.max`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'full_strength_distance',
            description: 'Deprecated. Use `range.full_strength_distance`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'max_angle',
            description: 'Deprecated. Use `range.max_angle`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'max_volume',
            description: 'Deprecated. Use `force.max_volume`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'volume_force_coefficient',
            description: 'Deprecated. Use `force.volume_force_coefficient`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'tether_break_time',
            description: 'Deprecated. Use `tether.tether_break_time`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'safe_range_value_factor',
            description: 'Deprecated. Use `tether.safe_range_value_factor`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
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
