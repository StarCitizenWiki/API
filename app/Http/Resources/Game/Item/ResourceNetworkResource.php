<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\Game\ItemSpecification\AbstractItemSpecificationResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'resource_network_delta',
    title: 'Resource Network Delta',
    description: 'Single resource change entry applied when a network state is active.',
    properties: [
        new OA\Property(property: 'type', description: 'Delta type (Consumption, Generation, Conversion, Storage, NetworkReflection).', type: 'string', example: 'Consumption', nullable: true),
        new OA\Property(property: 'resource', description: 'Target resource affected (Power, Fuel, Coolant, QuantumFuel, Shield, LifeSupport).', type: 'string', example: 'Power', nullable: true),
        new OA\Property(property: 'rate', description: 'Rate applied per tick (game native units). Typical power draw ~2-5; fuel draw often 0.01.', type: 'double', example: 2.2, nullable: true),
        new OA\Property(property: 'minimum_fraction', description: 'Minimum fraction of the resource that must be available before the delta applies.', type: 'double', example: 0.25, nullable: true, x: ['tabulator-formatter' => 'pct']),
        new OA\Property(property: 'generated_resource', description: 'Resource produced by conversion/storage deltas.', type: 'string', example: 'Coolant', nullable: true),
        new OA\Property(property: 'generated_rate', description: 'Rate of the generated resource.', type: 'double', example: 22, nullable: true),
        new OA\Property(property: 'discharge', description: 'Whether stored resource is discharged (0/1 flag).', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'no_over_generation', description: 'Prevents generating above capacity (0/1 flag).', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'binary_evaluation', description: 'Binary reflection value for NetworkReflection deltas.', type: 'double', example: 1, nullable: true),
        new OA\Property(
            property: 'composition',
            description: 'Optional composition entries describing how generated resource is assembled.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'container_resource', description: 'UUID of the container resource.', type: 'string', example: 'bcc8cde9-de58-4e6b-8ee9-37d4aaa507eb', nullable: true),
                    new OA\Property(property: 'ratio', description: 'Ratio of this resource in the composition.', type: 'double', example: 1, nullable: true),
                ],
                type: 'object'
            ),
            nullable: true,
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'resource_network_state',
    title: 'Resource Network State',
    description: 'Named state in the resource network with its signature and deltas.',
    properties: [
        new OA\Property(property: 'name', description: 'State name (e.g. Online). Can be empty on some items.', type: 'string', example: 'Online', nullable: true),
        new OA\Property(
            property: 'signature',
            description: 'Electromagnetic/infrared signature while in this state.',
            properties: [
                new OA\Property(property: 'em', description: 'Electromagnetic signature value in this state.', type: 'double', example: 1490, nullable: true, x: ['suffix' => ' EM']),
                new OA\Property(property: 'ir', description: 'Infrared signature value in this state.', type: 'double', example: 7260, nullable: true, x: ['suffix' => ' IR']),
            ],
            type: 'object',
            nullable: true,
        ),
        new OA\Property(
            property: 'deltas',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/resource_network_delta'),
            nullable: true,
        ),
        new OA\Property(
            property: 'power_ranges',
            description: 'Power range modifiers applied when this state is active.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'start', description: 'Start value of the power range.', type: 'double', nullable: true),
                    new OA\Property(property: 'modifier', description: 'Modifier applied within this power range.', type: 'double', nullable: true),
                    new OA\Property(property: 'register_range', description: 'Whether this range should be registered.', type: 'boolean', nullable: true),
                ],
                type: 'object'
            ),
            nullable: true,
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'resource_network',
    title: 'Resource Network',
    description: 'Networked resource behaviour for an item, including power/fuel consumption, generation, and priority.',
    properties: [
        new OA\Property(property: 'is_networked', description: 'Whether the item participates in the ship/station resource network.', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'is_relay', description: 'True when the item acts as a relay node.', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'default_priority', description: 'Processing priority within the network (higher runs earlier). Commonly 50; fuel tanks use 100.', type: 'integer', example: 50, nullable: true),
        new OA\Property(
            property: 'states',
            description: 'List of available network states and their resource deltas.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/resource_network_state'),
            nullable: true,
        ),
        new OA\Property(
            property: 'repair',
            description: 'Repair configuration for this networked item.',
            properties: [
                new OA\Property(property: 'max_repair_count', description: 'Maximum number of repairs allowed.', type: 'integer', nullable: true),
                new OA\Property(property: 'time_to_repair', description: 'Time required to perform a repair (seconds).', type: 'double', nullable: true, x: ['suffix' => ' s']),
                new OA\Property(property: 'health_ratio', description: 'Health ratio threshold for repair eligibility.', type: 'double', nullable: true, x: ['tabulator-formatter' => 'pct']),
            ],
            type: 'object',
            nullable: true,
        ),
        new OA\Property(
            property: 'usage',
            description: 'Resource usage configuration defining minimum and maximum consumption rates.',
            properties: [
                new OA\Property(
                    property: 'power',
                    description: 'Power usage range.',
                    properties: [
                        new OA\Property(property: 'min', description: 'Minimum power usage.', type: 'double', nullable: true, x: ['suffix' => ' pwr']),
                        new OA\Property(property: 'max', description: 'Maximum power usage.', type: 'double', nullable: true, x: ['suffix' => ' pwr']),
                        new OA\Property(property: 'minimum', description: 'Deprecated: Use min.', type: 'double', nullable: true, deprecated: true),
                        new OA\Property(property: 'maximum', description: 'Deprecated: Use max.', type: 'double', nullable: true, deprecated: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(
                    property: 'coolant',
                    description: 'Coolant usage range.',
                    properties: [
                        new OA\Property(property: 'min', description: 'Minimum coolant usage.', type: 'double', nullable: true),
                        new OA\Property(property: 'max', description: 'Maximum coolant usage.', type: 'double', nullable: true),
                        new OA\Property(property: 'minimum', description: 'Deprecated: Use min.', type: 'double', nullable: true, deprecated: true),
                        new OA\Property(property: 'maximum', description: 'Deprecated: Use max.', type: 'double', nullable: true, deprecated: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
            ],
            type: 'object',
            nullable: true,
        ),
        new OA\Property(
            property: 'generation',
            description: 'Resource generation configuration defining production rates.',
            properties: [
                new OA\Property(property: 'coolant', description: 'Coolant generation rate.', type: 'double', nullable: true),
                new OA\Property(property: 'power', description: 'Power generation rate.', type: 'double', nullable: true, x: ['suffix' => ' pwr']),
            ],
            type: 'object',
            nullable: true,
        ),
    ],
    type: 'object'
)]
class ResourceNetworkResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {

        $resourceNetwork = $this->extractFromStdItem($this->resource, 'ResourceNetwork');

        $states = $resourceNetwork['States'] ?? [];
        $repair = $resourceNetwork['Repair'] ?? null;
        $usage = $resourceNetwork['Usage'] ?? null;
        $powerUsage = $usage['Power'] ?? null;
        $coolantUsage = $usage['Coolant'] ?? null;
        $generation = $resourceNetwork['Generation'] ?? null;

        return [
            'is_networked' => $resourceNetwork['IsNetworked'] ?? null,
            'is_relay' => $resourceNetwork['IsRelay'] ?? null,
            'default_priority' => $resourceNetwork['DefaultPriority'] ?? null,
            'states' => array_map(static function (array $state): array {
                $signature = $state['Signature'] ?? null;
                $deltas = $state['Deltas'] ?? [];
                $powerRanges = $state['PowerRanges'] ?? [];

                return [
                    'name' => $state['Name'] ?? null,
                    'signature' => [
                        'em' => $signature['EM'] ?? null,
                        'ir' => $signature['IR'] ?? null,
                    ],
                    'deltas' => array_map(static fn (array $delta): array => [
                        'type' => $delta['Type'] ?? null,
                        'resource' => $delta['Resource'] ?? null,
                        'rate' => $delta['Rate'] ?? null,
                        'minimum_fraction' => $delta['MinimumFraction'] ?? null,
                        'generated_resource' => $delta['GeneratedResource'] ?? null,
                        'generated_rate' => $delta['GeneratedRate'] ?? null,
                        'discharge' => $delta['Discharge'] ?? null,
                        'no_over_generation' => $delta['NoOverGeneration'] ?? null,
                        'binary_evaluation' => $delta['BinaryEvaluation'] ?? null,
                        'composition' => $delta['Composition'] ?? null,
                    ], $deltas),
                    'power_ranges' => array_map(static fn (array $range): array => [
                        'start' => $range['Start'] ?? null,
                        'modifier' => $range['Modifier'] ?? null,
                        'register_range' => $range['RegisterRange'] ?? null,
                    ], $powerRanges),
                ];
            }, $states),
            'repair' => [
                'max_repair_count' => $repair['MaxRepairCount'] ?? null,
                'time_to_repair' => $repair['TimeToRepair'] ?? null,
                'health_ratio' => $repair['HealthRatio'] ?? null,
            ],
            'usage' => [
                'power' => [
                    'min' => $powerUsage['Minimum'] ?? 0,
                    'max' => $powerUsage['Maximum'] ?? 0,
                    'minimum' => $powerUsage['Minimum'] ?? 0,
                    'maximum' => $powerUsage['Maximum'] ?? 0,
                ],
                'coolant' => [
                    'min' => $coolantUsage['Minimum'] ?? null,
                    'max' => $coolantUsage['Maximum'] ?? null,
                    'minimum' => $coolantUsage['Minimum'] ?? null,
                    'maximum' => $coolantUsage['Maximum'] ?? null,
                ],
            ],
            'generation' => [
                'coolant' => $generation['Coolant'] ?? null,
                'power' => $generation['Power'] ?? null,
            ],
        ];
    }
}
