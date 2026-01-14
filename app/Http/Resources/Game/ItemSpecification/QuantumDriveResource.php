<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'quantum_drive',
    title: 'Quantum Drive',
    description: 'Quantum drive performance taken from stdItem.QuantumDrive in game data, including fuel usage, jump ranges, heat, and both standard and spline jump profiles.',
    properties: [
        new OA\Property(
            property: 'quantum_fuel_requirement',
            description: 'Total quantum fuel consumed to complete a spool. In game data snub and size-1 drives start around 0.0049, capital drives peak near 1.0.',
            type: 'double',
            example: 0.006758,
            nullable: true
        ),
        new OA\Property(
            property: 'fuel_rate',
            description: 'Continuous quantum fuel burn per meter travelled. Observed values span 4.9e-9 (small civilian) to 1e-6 (capital).',
            type: 'double',
            example: 6.758e-9,
            nullable: true
        ),
        new OA\Property(
            property: 'jump_range',
            description: 'Maximum permitted quantum jump distance in meters. Current game entries use float max (≈3.4e38) to represent uncapped range.',
            type: 'double',
            example: 3.402823e+38,
            nullable: true
        ),
        new OA\Property(
            property: 'disconnect_range',
            description: 'Automatic disengage distance when approaching destination (meters). Dataset ranges roughly 14,000–73,000.',
            type: 'double',
            example: 34693,
            nullable: true
        ),
        new OA\Property(
            property: 'heat',
            description: 'Thermal energy draw (heat units/s) for each phase. Small drives start near 101; largest entries approach 9.5M during ramp phases.',
            properties: [
                new OA\Property(property: 'pre_ramp_up_thermal_energy_draw', type: 'double', example: 8700, nullable: true),
                new OA\Property(property: 'ramp_up_thermal_energy_draw', type: 'double', example: 8700, nullable: true),
                new OA\Property(property: 'in_flight_thermal_energy_draw', type: 'double', example: 8700, nullable: true),
                new OA\Property(property: 'ramp_down_thermal_energy_draw', type: 'double', example: 8700, nullable: true),
                new OA\Property(property: 'post_ramp_down_thermal_energy_draw', type: 'double', example: 8700, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'standard_jump',
            ref: '#/components/schemas/quantum_drive_jump_profile',
            description: 'Primary point-to-point quantum travel profile. DriveSpeed ranges ~138,000,000–876,000,000; spool-up 4–9s; cooldown up to 92s depending on size/grade.',
            nullable: true
        ),
        new OA\Property(
            property: 'spline_jump',
            ref: '#/components/schemas/quantum_drive_jump_profile',
            description: 'Spline (QT beacon) travel profile with lower speeds/accels. DriveSpeed typically 400,000–500,000 with matching calibration and spool parameters.',
            nullable: true
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
                'seconds' => Arr::get($quantumDrive, 'TravelTimePer10GMSeconds'),
                'formatted' => Arr::get($quantumDrive, 'TravelTime10GM'),
            ],
        ];
    }
}
