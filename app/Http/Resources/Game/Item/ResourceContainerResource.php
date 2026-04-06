<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use App\Models\Game\Commodity\Commodity;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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
                ref: '#/components/schemas/resource_container_composition_entry'
            ),
            nullable: true
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'resource_container_composition_entry',
    title: 'Resource Container Composition Entry',
    properties: [
        new OA\Property(property: 'entry', type: 'string', nullable: true),
        new OA\Property(property: 'weight', type: 'double', nullable: true),
        new OA\Property(
            property: 'commodity',
            ref: '#/components/schemas/resource_container_commodity_link',
            nullable: true
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'resource_container_commodity_link',
    title: 'Commodity Link',
    description: 'Link to the commodity that this composition entry references.',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'slug', type: 'string', nullable: true),
        new OA\Property(property: 'link', type: 'string', nullable: true),
    ],
    type: 'object'
)]
class ResourceContainerResource extends AbstractBaseResource
{
    /**
     * @param  array  $containerData  Raw ResourceContainer data from stdItem
     * @param  Collection<int, Commodity>|null  $commodities  Loaded commodities keyed by UUID
     */
    public function __construct(
        ?array $containerData,
        private readonly ?Collection $commodities = null,
    ) {
        parent::__construct($containerData ?? []);
    }

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
                ->map(fn (array $entry) => $this->mapCompositionEntry($entry, $request))
                ->values()
                ->toArray(),
        ];
    }

    private function mapCompositionEntry(array $entry, Request $request): array
    {
        $uuid = Arr::get($entry, 'Entry');

        $result = [
            'entry' => $uuid,
            'weight' => Arr::get($entry, 'Weight'),
        ];

        if ($this->commodities === null || ! is_string($uuid) || $uuid === '') {
            return $result;
        }

        $commodity = $this->commodities->get($uuid);

        if ($commodity === null) {
            return $result;
        }

        $result['commodity'] = [
            'uuid' => $commodity->uuid,
            'name' => $commodity->name,
            'slug' => $commodity->slug,
            'link' => $this->urlWithVersion(route('commodities.show', ['commodity' => $commodity->uuid]), $request),
        ];

        return $result;
    }
}
