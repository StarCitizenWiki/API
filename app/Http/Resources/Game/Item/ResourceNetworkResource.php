<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\Game\ItemSpecification\AbstractItemSpecificationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'resource_network_delta',
    title: 'Resource Network Delta',
    description: 'Single resource change entry applied when a network state is active.',
    properties: [
        new OA\Property(property: 'type', description: 'Delta type (Consumption, Generation, Conversion, Storage, NetworkReflection).', type: 'string', example: 'Consumption', nullable: true),
        new OA\Property(property: 'resource', description: 'Target resource affected (Power, Fuel, Coolant, QuantumFuel, Shield, LifeSupport).', type: 'string', example: 'Power', nullable: true),
        new OA\Property(property: 'rate', description: 'Rate applied per tick (game native units). Typical power draw ~2–5; fuel draw often 0.01.', type: 'double', example: 2.2, nullable: true),
        new OA\Property(property: 'minimum_fraction', description: 'Minimum fraction of the resource that must be available before the delta applies.', type: 'double', example: 0.25, nullable: true),
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
                    new OA\Property(property: 'container_resource', type: 'string', example: 'bcc8cde9-de58-4e6b-8ee9-37d4aaa507eb', nullable: true),
                    new OA\Property(property: 'ratio', type: 'double', example: 1, nullable: true),
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
                new OA\Property(property: 'em', type: 'double', example: 1490, nullable: true),
                new OA\Property(property: 'ir', type: 'double', example: 7260, nullable: true),
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
    ],
    type: 'object'
)]
class ResourceNetworkResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $stdItem = $this->extractStdItem($data);
        $resourceNetwork = Arr::get($stdItem, 'ResourceNetwork', []);

        $states = collect(Arr::get($resourceNetwork, 'States', []))
            ->map(function (array $state): array {
                $signature = Arr::get($state, 'Signature', null);

                $deltas = collect(Arr::get($state, 'Deltas', []))
                    ->map(function (array $delta): array {
                        return array_filter([
                            'type' => Arr::get($delta, 'Type'),
                            'resource' => Arr::get($delta, 'Resource'),
                            'rate' => Arr::get($delta, 'Rate'),
                            'minimum_fraction' => Arr::get($delta, 'MinimumFraction'),
                            'generated_resource' => Arr::get($delta, 'GeneratedResource'),
                            'generated_rate' => Arr::get($delta, 'GeneratedRate'),
                            'discharge' => Arr::get($delta, 'Discharge'),
                            'no_over_generation' => Arr::get($delta, 'NoOverGeneration'),
                            'binary_evaluation' => Arr::get($delta, 'BinaryEvaluation'),
                            'composition' => collect(Arr::get($delta, 'Composition', []))
                                ->map(fn (array $entry): array => [
                                    'container_resource' => Arr::get($entry, 'ContainerResource'),
                                    'ratio' => Arr::get($entry, 'Ratio'),
                                ])
                                ->values()
                                ->toArray(),
                        ], static fn ($value) => $value !== null && $value !== []);
                    })
                    ->values()
                    ->toArray();

                return array_filter([
                    'name' => Arr::get($state, 'Name'),
                    'signature' => $signature !== null ? [
                        'em' => Arr::get($signature, 'EM'),
                        'ir' => Arr::get($signature, 'IR'),
                    ] : null,
                    'deltas' => $deltas,
                ], static fn ($value) => $value !== null && $value !== []);
            })
            ->values()
            ->toArray();

        return array_filter([
            'is_networked' => Arr::get($resourceNetwork, 'IsNetworked'),
            'is_relay' => Arr::get($resourceNetwork, 'IsRelay'),
            'default_priority' => Arr::get($resourceNetwork, 'DefaultPriority'),
            'states' => $states,
        ], static fn ($value) => $value !== null && $value !== []);
    }
}
