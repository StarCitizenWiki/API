<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'thruster_performance',
    title: 'Thruster Performance',
    properties: [
        new OA\Property(
            property: 'thrust_capacity',
            description: 'Maximum thrust output in newtons. Main thrusters on capital ships can exceed 300M N, while S1 maneuver thrusters are around 1.4M N.',
            type: 'double',
            example: 1431557,
            nullable: true,
        ),
        new OA\Property(
            property: 'thrust_capacity_new',
            description: 'Newton output using the newer balance pass values when present (null for most items).',
            type: 'double',
            example: 9023031,
            nullable: true,
        ),
        new OA\Property(
            property: 'max_supported_atmospheric_efficiency',
            description: 'Cap for atmospheric efficiency scaling. Currently 2 across available thrusters.',
            type: 'double',
            example: 2,
            nullable: true,
        ),
        new OA\Property(
            property: 'min_health_thrust_multiplier',
            description: 'Minimum thrust fraction when the thruster is at critical health (e.g., 0.25 = 25% thrust when badly damaged).',
            type: 'double',
            example: 0.25,
            nullable: true,
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'thruster_fuel',
    title: 'Thruster Fuel Use',
    properties: [
        new OA\Property(
            property: 'burn_rate_per_10k_newton',
            description: 'Fuel consumed per 10,000 newtons of thrust. Lower is more efficient; common maneuver thrusters burn around 0.125.',
            type: 'double',
            example: 0.125,
            nullable: true,
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'thruster_backwash',
    title: 'Thruster Backwash',
    properties: [
        new OA\Property(property: 'enabled', description: 'Whether backwash effects are enabled.', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'automate_size', description: 'Automatically size backwash based on output.', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'max_speed', description: 'Max speed (m/s) at which backwash applies.', type: 'double', example: 80, nullable: true),
        new OA\Property(property: 'max_density', description: 'Max atmospheric density where backwash is considered.', type: 'double', example: 7, nullable: true),
        new OA\Property(property: 'max_resistance', description: 'Max resistance value for backwash interaction.', type: 'double', example: 100, nullable: true),
        new OA\Property(property: 'afterburner_multiplier', description: 'Backwash intensity multiplier while afterburning.', type: 'double', example: 1.1, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'thruster_handling',
    title: 'Thruster Handling',
    properties: [
        new OA\Property(property: 'strength_smoothing', description: 'Smoothing factor applied to thrust changes.', type: 'double', example: 0.2, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'thruster',
    title: 'Thruster',
    description: 'Ship thruster characteristics focused on performance, fuel efficiency, and backwash behaviour.',
    properties: [
        new OA\Property(property: 'performance', ref: '#/components/schemas/thruster_performance', nullable: true),
        new OA\Property(property: 'fuel', ref: '#/components/schemas/thruster_fuel', nullable: true),
        new OA\Property(
            property: 'role',
            description: 'Thruster role within the ship: Main, Maneuver, or Retro.',
            type: 'string',
            example: 'Maneuver',
            nullable: true,
        ),
        new OA\Property(
            property: 'vtol_only',
            description: 'True if the thruster only activates in VTOL mode.',
            type: 'boolean',
            example: false,
            nullable: true,
        ),
        new OA\Property(property: 'backwash', ref: '#/components/schemas/thruster_backwash', nullable: true),
        new OA\Property(property: 'handling', ref: '#/components/schemas/thruster_handling', nullable: true),

        // Legacy flat keys kept for backward compatibility
        new OA\Property(property: 'thrust_capacity', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'min_health_thrust_multiplier', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'fuel_burn_per_10k_newton', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'type', type: 'string', nullable: true, deprecated: true),
    ],
    type: 'object'
)]
class ThrusterResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $stdItem = $this->extractStdItem($data);
        $thruster = Arr::get($stdItem, 'Thruster', []);

        $performance = [
            'thrust_capacity' => Arr::get($thruster, 'ThrustCapacity'),
            'thrust_capacity_new' => Arr::get($thruster, 'ThrustCapacityNew'),
            'max_supported_atmospheric_efficiency' => Arr::get($thruster, 'MaxSupportedAtmosphericEfficiency'),
            'min_health_thrust_multiplier' => Arr::get($thruster, 'MinHealthThrustMultiplier'),
        ];

        $fuel = [
            'burn_rate_per_10k_newton' => Arr::get($thruster, 'FuelBurnRatePer10KNewton'),
        ];

        $backwash = [
            'enabled' => Arr::get($thruster, 'ToggleThrusterBackwash'),
            'automate_size' => Arr::get($thruster, 'AutomateBackwashSize'),
            'max_speed' => Arr::get($thruster, 'ThrusterBackwashMaxSpeed'),
            'max_density' => Arr::get($thruster, 'ThrusterBackwashMaxDensity'),
            'max_resistance' => Arr::get($thruster, 'ThrusterBackwashMaxResistance'),
            'afterburner_multiplier' => Arr::get($thruster, 'ThrusterBackwashAfterburnerMultiplier'),
        ];

        $handling = [
            'strength_smoothing' => Arr::get($thruster, 'ThrusterStrengthSmoothing'),
        ];

        return [
            'performance' => $performance,
            'fuel' => $fuel,
            'role' => Arr::get($thruster, 'ThrusterType'),
            'vtol_only' => Arr::has($thruster, 'OnlyActiveInVTOL') ? (bool) Arr::get($thruster, 'OnlyActiveInVTOL') : null,
            'backwash' => $backwash,
            'handling' => $handling,

            // Legacy flat keys (backward compatibility)
            'thrust_capacity' => Arr::get($thruster, 'ThrustCapacity'),
            'min_health_thrust_multiplier' => Arr::get($thruster, 'MinHealthThrustMultiplier'),
            'fuel_burn_per_10k_newton' => Arr::get($thruster, 'FuelBurnRatePer10KNewton'),
            'type' => Arr::get($thruster, 'ThrusterType'),
        ];
    }
}
