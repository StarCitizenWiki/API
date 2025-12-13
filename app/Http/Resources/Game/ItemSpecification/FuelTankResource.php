<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'fuel_tank',
    title: 'Fuel Tank',
    description: 'Fuel and quantum fuel tank characteristics including flow rates, container capacity, resource network behaviour.',
    properties: [
        new OA\Property(
            property: 'fill_rate',
            description: 'Maximum generation/refill rate in standard resource units per second. Typical civilian tanks are 0.25–10.',
            type: 'double',
            example: 10,
            nullable: true
        ),
        new OA\Property(
            property: 'drain_rate',
            description: 'Maximum consumption/usage rate in standard resource units per second.',
            type: 'double',
            example: 10,
            nullable: true
        ),
        new OA\Property(
            property: 'discharge_rate',
            description: 'Configured discharge rate; most tanks use 0.',
            type: 'double',
            example: 0,
            nullable: true
        ),
        new OA\Property(
            property: 'capacity',
            description: 'Container capacity. Values range from 0.65 SCU micro-tanks up to 10,000 SCU capital tanks (e.g. Javelin).',
            properties: [
                new OA\Property(property: 'value', type: 'double', example: 4.35, nullable: true),
                new OA\Property(property: 'unit', type: 'string', example: 'SStandardCargoUnit', nullable: true),
                new OA\Property(property: 'unit_name', type: 'string', example: 'SCU', nullable: true),
                new OA\Property(property: 'scu', type: 'double', example: 4.35, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(property: 'mass', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'immutable', type: 'boolean', example: false, nullable: true),
        new OA\Property(
            property: 'default_fill_fraction',
            description: 'Fraction (0–1) the tank spawns filled with. Game data shows 1 for stocked tanks.',
            type: 'double',
            example: 1,
            nullable: true
        ),
        new OA\Property(
            property: 'inclusive_resources',
            description: 'UUIDs of resources this tank can accept (e.g. quantum or hydrogen fuel).',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['f4846780-182f-49d1-a711-f08c0172735e'],
            nullable: true
        ),
        new OA\Property(
            property: 'exclusive_resources',
            description: 'UUIDs of resources explicitly disallowed.',
            type: 'array',
            items: new OA\Items(type: 'string'),
            nullable: true
        ),
        new OA\Property(
            property: 'inclusive_groups',
            description: 'Resource group UUIDs the tank accepts.',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['9379b129-8688-4777-9f65-9dc09ba935ee'],
            nullable: true
        ),
        new OA\Property(
            property: 'exclusive_groups',
            description: 'Resource group UUIDs the tank rejects.',
            type: 'array',
            items: new OA\Items(type: 'string'),
            nullable: true
        ),
        new OA\Property(
            property: 'default_composition',
            description: 'Spawn composition entries with weights.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'entry', type: 'string', example: 'f4846780-182f-49d1-a711-f08c0172735e', nullable: true),
                    new OA\Property(property: 'weight', type: 'double', example: 1, nullable: true),
                ],
                type: 'object'
            ),
            nullable: true
        ),
        new OA\Property(
            property: 'resource_network',
            description: 'Resource network configuration taken from ItemResourceComponentParams.',
            properties: [
                new OA\Property(property: 'is_networked', type: 'boolean', example: true, nullable: true),
                new OA\Property(property: 'is_relay', type: 'boolean', example: false, nullable: true),
                new OA\Property(property: 'is_connected_to_room', type: 'boolean', example: false, nullable: true),
                new OA\Property(property: 'wireless_connection', type: 'boolean', example: false, nullable: true),
                new OA\Property(property: 'default_priority', type: 'integer', example: 100, nullable: true),
                new OA\Property(property: 'filter_params', type: 'array', items: new OA\Items, nullable: true),
                new OA\Property(property: 'control_parameters', type: 'array', items: new OA\Items, nullable: true),
                new OA\Property(property: 'control_blocks', type: 'array', items: new OA\Items, nullable: true),
                new OA\Property(property: 'functionality_modifiers', type: 'array', items: new OA\Items, nullable: true),
                new OA\Property(property: 'range_params', type: 'array', items: new OA\Items, nullable: true),
                new OA\Property(property: 'power_plant_override', type: 'array', items: new OA\Items, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'signatures',
            description: 'Electromagnetic and infrared signature parameters when the tank is online.',
            properties: [
                new OA\Property(
                    property: 'em',
                    properties: [
                        new OA\Property(property: 'nominal_signature', type: 'double', example: 0, nullable: true),
                        new OA\Property(property: 'decay_rate', type: 'double', example: 0.15, nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(
                    property: 'ir',
                    properties: [
                        new OA\Property(property: 'nominal_signature', type: 'double', example: 0, nullable: true),
                        new OA\Property(property: 'decay_rate', type: 'double', example: 0.15, nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'states',
            description: 'Current ItemResourceState metadata.',
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Online', nullable: true),
                new OA\Property(property: 'linked_interaction_states', type: 'array', items: new OA\Items, nullable: true),
                new OA\Property(property: 'range_params', type: 'array', items: new OA\Items, nullable: true),
                new OA\Property(property: 'transfer_modifiers', type: 'array', items: new OA\Items, nullable: true),
                new OA\Property(property: 'consumption_composition', type: 'array', items: new OA\Items, nullable: true),
                new OA\Property(property: 'dynamic_resource_override', type: 'array', items: new OA\Items, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'tank',
            description: 'Raw SCItemFuelTankParams.',
            properties: [
                new OA\Property(property: 'open_state', type: 'string', example: 'null', nullable: true),
                new OA\Property(property: 'closed_state', type: 'string', example: 'null', nullable: true),
                new OA\Property(property: 'pumping_state', type: 'string', example: 'null', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
    ],
    type: 'object'
)]
class FuelTankResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $container = Arr::get($data, 'stdItem.ResourceContainer', []);
        $component = Arr::get($data, 'Raw.Entity.Components.ItemResourceComponentParams', []);
        $tankParams = Arr::get($data, 'Raw.Entity.Components.SCItemFuelTankParams', []);

        $state = Arr::get($component, 'states.ItemResourceState', []);
        $deltaStorage = Arr::get($state, 'deltas.ItemResourceDeltaStorage', []);

        $generation = Arr::get($deltaStorage, 'generation.resourceAmountPerSecond.SStandardResourceUnit.standardResourceUnits');
        $consumption = Arr::get($deltaStorage, 'consumption.resourceAmountPerSecond.SStandardResourceUnit.standardResourceUnits');

        $signatureParams = Arr::get($state, 'signatureParams', []);
        $emSignature = Arr::get($signatureParams, 'EMSignature', []);
        $irSignature = Arr::get($signatureParams, 'IRSignature', []);

        return [
            'fill_rate' => $generation,
            'drain_rate' => $consumption,
            'discharge_rate' => Arr::get($deltaStorage, 'discharge'),
            'capacity' => [
                'value' => Arr::get($container, 'Capacity.Value'),
                'unit' => Arr::get($container, 'Capacity.Unit'),
                'unit_name' => Arr::get($container, 'Capacity.UnitName'),
                'scu' => Arr::get($container, 'Capacity.SCU'),
            ],
            'mass' => Arr::get($container, 'Mass'),
            'immutable' => Arr::get($container, 'Immutable'),
            'default_fill_fraction' => Arr::get($container, 'DefaultFillFraction'),
            'inclusive_resources' => Arr::get($container, 'InclusiveResources', []),
            'exclusive_resources' => Arr::get($container, 'ExclusiveResources', []),
            'inclusive_groups' => Arr::get($container, 'InclusiveGroups', []),
            'exclusive_groups' => Arr::get($container, 'ExclusiveGroups', []),
            'default_composition' => collect(Arr::get($container, 'DefaultComposition', []))
                ->map(fn (array $entry) => [
                    'entry' => Arr::get($entry, 'Entry'),
                    'weight' => Arr::get($entry, 'Weight'),
                ])
                ->values()
                ->toArray(),
            'resource_network' => [
                'is_networked' => Arr::get($component, 'isResourceNetworked'),
                'is_relay' => Arr::get($component, 'isRelay'),
                'is_connected_to_room' => Arr::get($component, 'isConnectedToRoom'),
                'wireless_connection' => Arr::get($component, 'wirelessConnection'),
                'default_priority' => Arr::get($component, 'defaultPriority'),
                'filter_params' => Arr::get($component, 'filterParams', []),
                'control_parameters' => Arr::get($component, 'controlParameters', []),
                'control_blocks' => Arr::get($component, 'controlBlocks', []),
                'functionality_modifiers' => Arr::get($component, 'functionalityModifiers', []),
                'range_params' => Arr::get($component, 'rangeParams', []),
                'power_plant_override' => Arr::get($component, 'powerPlantOverride', []),
            ],
            'signatures' => [
                'em' => [
                    'nominal_signature' => Arr::get($emSignature, 'nominalSignature'),
                    'decay_rate' => Arr::get($emSignature, 'decayRate'),
                ],
                'ir' => [
                    'nominal_signature' => Arr::get($irSignature, 'nominalSignature'),
                    'decay_rate' => Arr::get($irSignature, 'decayRate'),
                ],
            ],
            'states' => [
                'name' => Arr::get($state, 'name'),
                'linked_interaction_states' => Arr::get($state, 'linkedInteractionStates', []),
                'range_params' => Arr::get($state, 'rangeParams', []),
                'transfer_modifiers' => Arr::get($deltaStorage, 'transferModifiers', []),
                'consumption_composition' => Arr::get($deltaStorage, 'consumptionComposition.values', []),
                'dynamic_resource_override' => Arr::get($deltaStorage, 'dynamicResourceOverride', []),
            ],
            'tank' => [
                'open_state' => Arr::get($tankParams, 'openState'),
                'closed_state' => Arr::get($tankParams, 'closedState'),
                'pumping_state' => Arr::get($tankParams, 'pumpingState'),
            ],
        ];
    }
}
