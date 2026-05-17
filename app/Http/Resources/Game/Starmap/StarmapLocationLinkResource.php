<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Starmap;

use App\Http\Resources\AbstractBaseResource;
use App\Models\Game\StarmapLocationData;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'starmap_location_link',
    title: 'Starmap Location Link',
    description: 'Lightweight reference to a starmap location with navigation links.',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'slug', type: 'string', nullable: true),
        new OA\Property(property: 'type_name', type: 'string', nullable: true),
        new OA\Property(property: 'parent_name', type: 'string', nullable: true),
        new OA\Property(property: 'star_system_name', type: 'string', nullable: true),
        new OA\Property(property: 'link', description: 'API URL for the starmap location', type: 'string', nullable: true),
        new OA\Property(property: 'web_url', description: 'Web URL for the starmap location', type: 'string', nullable: true),
    ],
    type: 'object'
)]
class StarmapLocationLinkResource extends AbstractBaseResource
{
    public function __construct(
        $resource,
        private readonly ?string $locationUuid = null,
    ) {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        /** @var StarmapLocationData $locationData */
        $locationData = $this->resource;

        return [
            'uuid' => $locationUuid = $this->locationUuid ?? $locationData->location?->uuid,
            'name' => $locationData->name,
            'slug' => $locationData->location?->slug,
            'type_name' => $locationData->type_name,
            'parent_name' => $locationData->parent?->name,
            'star_system_name' => $locationData->parent?->star?->name,
            'link' => $locationUuid !== null
                ? route('locations.show', ['identifier' => $locationUuid])
                : null,
            'web_url' => $locationUuid !== null
                ? $this->urlWithVersion(route('web.locations.show', ['identifier' => $locationUuid]), $request)
                : null,
        ];
    }
}
