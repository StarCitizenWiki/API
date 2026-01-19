<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'resource_container',
    title: 'Resource Container',
    description: 'Container data for items that can hold resources (e.g. cargo or consumables).',
    properties: [
        new OA\Property(property: 'mass', type: 'double', nullable: true),
        new OA\Property(property: 'immutable', type: 'boolean', nullable: true),
        new OA\Property(
            property: 'default_fill_fraction',
            description: 'Initial fill fraction (0-1).',
            type: 'double',
            nullable: true
        ),
        new OA\Property(
            property: 'capacity',
            properties: [
                new OA\Property(property: 'value', type: 'double', nullable: true),
                new OA\Property(property: 'unit', type: 'string', nullable: true),
                new OA\Property(property: 'unit_name', type: 'string', nullable: true),
                new OA\Property(property: 'scu', type: 'double', nullable: true),
            ],
            type: 'object',
            nullable: true,
        ),
        new OA\Property(
            property: 'inclusive_resources',
            description: 'UUIDs of resources allowed in this container.',
            type: 'array',
            items: new OA\Items(type: 'string'),
            nullable: true
        ),
        new OA\Property(
            property: 'default_composition',
            description: 'Default composition entries and weights.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'entry', type: 'string', nullable: true),
                    new OA\Property(property: 'weight', type: 'double', nullable: true),
                ],
                type: 'object'
            ),
            nullable: true
        ),
    ],
    type: 'object'
)]
class ResourceContainerResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        $capacity = Arr::get($this, 'Capacity', []);

        return [
            'mass' => Arr::get($this, 'Mass'),
            'immutable' => Arr::get($this, 'Immutable'),
            'default_fill_fraction' => Arr::get($this, 'DefaultFillFraction'),
            'capacity' => [
                'value' => Arr::get($capacity, 'Value'),
                'unit' => Arr::get($capacity, 'Unit'),
                'unit_name' => Arr::get($capacity, 'UnitName'),
                'scu' => Arr::get($capacity, 'SCU'),
            ],
            'inclusive_resources' => Arr::get($this, 'InclusiveResources', []),
            'default_composition' => collect(Arr::get($this, 'DefaultComposition', []))
                ->map(fn (array $entry) => [
                    'entry' => Arr::get($entry, 'Entry'),
                    'weight' => Arr::get($entry, 'Weight'),
                ])
                ->values()
                ->toArray(),
        ];
    }
}
