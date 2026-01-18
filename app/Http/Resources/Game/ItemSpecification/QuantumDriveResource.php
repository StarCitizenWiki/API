<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'quantum_drive_thermal_energy_draw',
    title: 'Quantum Drive Thermal Energy Draw',
    description: 'Thermal energy draw (heat units/s) for each quantum travel phase.',
    properties: [
        new OA\Property(property: 'pre_ramp_up', type: 'double', example: 8700, nullable: true),
        new OA\Property(property: 'ramp_up', type: 'double', example: 8700, nullable: true),
        new OA\Property(property: 'in_flight', type: 'double', example: 8700, nullable: true),
        new OA\Property(property: 'ramp_down', type: 'double', example: 8700, nullable: true),
        new OA\Property(property: 'post_ramp_down', type: 'double', example: 8700, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'quantum_drive_travel_time_10gm',
    title: 'Quantum Drive Travel Time 10GM',
    description: 'Travel time for a 10GM reference distance as provided by the source data.',
    properties: [
        new OA\Property(property: 'seconds', description: 'Travel time in seconds.', type: 'double', example: 56.0, nullable: true),
        new OA\Property(property: 'formatted', description: 'Formatted travel time string.', type: 'string', example: '00:56', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'quantum_drive',
    title: 'Quantum Drive',
    description: 'Quantum drive performance taken from stdItem.QuantumDrive in game data, including fuel usage, jump ranges, thermal draw, jump profiles, and derived mode listings.',
    properties: [
        new OA\Property(
            property: 'quantum_fuel_requirement',
            description: 'Total quantum fuel consumed to complete a spool (QuantumFuelRequirement).',
            type: 'double',
            example: 0.006758,
            nullable: true
        ),
        new OA\Property(
            property: 'fuel_rate',
            description: 'Continuous quantum fuel burn per meter travelled (FuelRate).',
            type: 'double',
            example: 6.758e-9,
            nullable: true
        ),
        new OA\Property(
            property: 'jump_range',
            description: 'Maximum permitted quantum jump distance in meters (JumpRange).',
            type: 'double',
            example: 3.402823e+38,
            nullable: true
        ),
        new OA\Property(
            property: 'disconnect_range',
            description: 'Automatic disengage distance when approaching destination in meters (DisconnectRange).',
            type: 'double',
            example: 34693,
            nullable: true
        ),

        new OA\Property(
            property: 'thermal_energy_draw',
            ref: '#/components/schemas/quantum_drive_thermal_energy_draw',
            description: 'Thermal energy draw values for each phase (from QuantumDrive.Heat.*).'
        ),

        new OA\Property(
            property: 'standard_jump',
            ref: '#/components/schemas/quantum_drive_jump_profile',
            description: 'Primary point-to-point quantum travel profile (from QuantumDrive.StandardJump).'
        ),
        new OA\Property(
            property: 'spline_jump',
            ref: '#/components/schemas/quantum_drive_jump_profile',
            description: 'Spline (QT beacon) travel profile (from QuantumDrive.SplineJump).'
        ),

        new OA\Property(
            property: 'modes',
            description: 'List of jump profiles with an explicit mode identifier.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/quantum_drive_jump_profile')
        ),

        new OA\Property(
            property: 'fuel_consumption_scu_per_gm',
            description: 'Fuel consumption in SCU per GM (FuelConsumptionSCUPerGM).',
            type: 'double',
            example: 0.12,
            nullable: true
        ),
        new OA\Property(
            property: 'fuel_efficiency',
            description: 'Fuel efficiency in GM per SCU (FuelEfficiencyGMPerSCU).',
            type: 'double',
            example: 8.3,
            nullable: true
        ),

        new OA\Property(
            property: 'travel_time_10gm',
            ref: '#/components/schemas/quantum_drive_travel_time_10gm',
            description: 'Travel time reference for 10GM.'
        ),
    ],
    type: 'object'
)]
class QuantumDriveResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $quantumDrive = Arr::get($data, 'stdItem.QuantumDrive', []);

        $heat = Arr::get($quantumDrive, 'Heat', []);
        $standardJump = Arr::get($quantumDrive, 'StandardJump', []);
        $splineJump = Arr::get($quantumDrive, 'SplineJump', []);

        return [
            'quantum_fuel_requirement' => Arr::get($quantumDrive, 'QuantumFuelRequirement'),
            'jump_range' => Arr::get($quantumDrive, 'JumpRange'),
            'disconnect_range' => Arr::get($quantumDrive, 'DisconnectRange'),
            'fuel_rate' => Arr::get($quantumDrive, 'FuelRate'),
            'thermal_energy_draw' => [
                'pre_ramp_up' => Arr::get($heat, 'PreRampUpThermalEnergyDraw'),
                'ramp_up' => Arr::get($heat, 'RampUpThermalEnergyDraw'),
                'in_flight' => Arr::get($heat, 'InFlightThermalEnergyDraw'),
                'ramp_down' => Arr::get($heat, 'RampDownThermalEnergyDraw'),
                'post_ramp_down' => Arr::get($heat, 'PostRampDownThermalEnergyDraw'),
            ],
            'standard_jump' => new QuantumDriveJumpProfileResource($standardJump),
            'spline_jump' => new QuantumDriveJumpProfileResource($splineJump),

            'modes' => [
                new QuantumDriveJumpProfileResource($standardJump, 'normal_jump'),
                new QuantumDriveJumpProfileResource($splineJump, 'spline_jump'),
            ],

            'fuel_consumption_scu_per_gm' => Arr::get($quantumDrive, 'FuelConsumptionSCUPerGM'),
            'fuel_efficiency' => Arr::get($quantumDrive, 'FuelEfficiencyGMPerSCU'),

            'travel_time_10gm' => [
                'seconds' => Arr::get($quantumDrive, 'TravelTime10GMSeconds'),
                'formatted' => Arr::get($quantumDrive, 'TravelTime10GM'),
            ],
        ];
    }
}
