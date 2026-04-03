<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Starmap;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_starmap_location',
    title: 'Game Starmap Location',
    description: 'Versioned starmap location data imported from game starmap data.',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'system_uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'system_name', type: 'string', nullable: true),
        new OA\Property(property: 'parent_name', type: 'string', nullable: true),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'type_name', type: 'string'),
        new OA\Property(property: 'type_classification', type: 'string', nullable: true),
        new OA\Property(property: 'respawn_location_type', type: 'string', nullable: true),
        new OA\Property(property: 'size', type: 'number', nullable: true),
        new OA\Property(property: 'minimum_display_size', type: 'number', nullable: true),
        new OA\Property(property: 'child_count', type: 'integer'),
        new OA\Property(property: 'is_scannable', type: 'boolean'),
        new OA\Property(property: 'hide_in_starmap', type: 'boolean'),
        new OA\Property(property: 'hide_in_world', type: 'boolean'),
        new OA\Property(property: 'block_travel', type: 'boolean'),
        new OA\Property(property: 'jurisdiction_name', type: 'string', nullable: true),
        new OA\Property(property: 'jurisdiction_is_prison', type: 'boolean', nullable: true),
        new OA\Property(property: 'affiliation_name', type: 'string', nullable: true),
        new OA\Property(property: 'amenities_label', type: 'string', nullable: true),
        new OA\Property(property: 'tag_name', type: 'string', nullable: true),
        new OA\Property(property: 'quantum_travel', type: 'object', nullable: true),
        new OA\Property(property: 'asteroid_ring', type: 'object', nullable: true),
        new OA\Property(property: 'data', type: 'object'),
        new OA\Property(property: 'link', type: 'string'),
        new OA\Property(property: 'version', type: 'string', nullable: true),
        new OA\Property(
            property: 'location',
            properties: [
                new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
                new OA\Property(property: 'system_uuid', type: 'string', format: 'uuid', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'parent',
            properties: [
                new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'type_name', type: 'string'),
                new OA\Property(property: 'link', type: 'string'),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'children',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'type_name', type: 'string'),
                    new OA\Property(property: 'link', type: 'string'),
                ],
                type: 'object'
            ),
            nullable: true
        ),
        new OA\Property(
            property: 'amenities',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'display_name', type: 'string', nullable: true),
                ],
                type: 'object'
            ),
            nullable: true
        ),
        new OA\Property(
            property: 'tag',
            properties: [
                new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
                new OA\Property(property: 'name', type: 'string'),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ],
    type: 'object'
)]
class StarmapLocationResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'location',
            'parent',
            'children',
            'amenities',
            'tag',
        ];
    }

    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->location?->uuid,
            'system_uuid' => $this->location?->system_uuid,
            'name' => $this->name,
            'system_name' => $this->location?->system?->data?->first()?->name,
            'parent_name' => $this->parent?->name,
            'description' => $this->description,
            'type_name' => $this->type_name,
            'type_classification' => $this->type_classification,
            'respawn_location_type' => $this->respawn_location_type,
            'size' => $this->size,
            'child_count' => (int) ($this->child_count ?? 0),
            'is_scannable' => $this->is_scannable,
            'hide_in_starmap' => $this->hide_in_starmap,
            'hide_in_world' => $this->hide_in_world,
            'block_travel' => $this->block_travel,
            'jurisdiction_name' => $this->jurisdiction_name,
            'jurisdiction_is_prison' => $this->jurisdiction_is_prison,
            'affiliation_name' => $this->affiliation_name,
            'amenities_label' => $this->amenities
                ->map(static fn ($amenity): string => $amenity->display_name ?? $amenity->name)
                ->filter()
                ->implode(', '),
            'tag_name' => $this->locationHierarchyEntityTag?->name,
            'quantum_travel' => $this->quantum_travel,
            'asteroid_ring' => $this->asteroid_ring,
            'location' => $this->whenLoaded('location', fn (): array => [
                'uuid' => $this->location->uuid,
                'system_uuid' => $this->location->system_uuid,
            ]),
            'parent' => $this->whenLoaded('parent', function (): ?array {
                if ($this->parent === null || $this->parent->location === null) {
                    return null;
                }

                return $this->buildLinkedLocation($this->parent, request());
            }),
            'children' => $this->whenLoaded('children', fn (): array => $this->children
                ->filter(fn ($child) => $child->location !== null)
                ->map(fn ($child): array => $this->buildLinkedLocation($child, request()))
                ->values()
                ->all()),
            'amenities' => $this->whenLoaded('amenities', fn (): array => $this->amenities
                ->map(static fn ($amenity): array => [
                    'uuid' => $amenity->uuid,
                    'name' => $amenity->name,
                    'display_name' => $amenity->display_name,
                ])
                ->values()
                ->all()),
            'tag' => $this->whenLoaded('locationHierarchyEntityTag', fn (): array => [
                'uuid' => $this->locationHierarchyEntityTag->uuid,
                'name' => $this->locationHierarchyEntityTag->name,
            ]),
            'link' => $this->buildApiUrl($request),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'version' => $this->relationLoaded('gameVersion')
                ? $this->gameVersion?->code
                : null,
        ];
    }

    private function buildApiUrl(Request $request): string
    {
        return $this->urlWithVersion(
            route('starmap-locations.show', ['identifier' => $this->location?->uuid]),
            $request
        );
    }

    /**
     * @return array{uuid: string, name: string, type_name: string, link: string}
     */
    private function buildLinkedLocation(object $locationData, Request $request): array
    {
        return [
            'uuid' => $locationData->location->uuid,
            'name' => $locationData->name,
            'type_name' => $locationData->type_name,
            'link' => $this->urlWithVersion(
                route('starmap-locations.show', ['identifier' => $locationData->location->uuid]),
                $request
            ),
        ];
    }
}
