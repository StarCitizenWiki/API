<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ResourceType;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'resource_type_link',
    title: 'Resource Type Link',
    properties: [
        new OA\Property(property: 'uuid', type: 'string'),
        new OA\Property(property: 'key', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'description', type: 'string'),
        new OA\Property(property: 'refined_version_uuid', type: 'string', nullable: true),
        new OA\Property(property: 'validate_default_cargo_box', type: 'boolean'),
        new OA\Property(property: 'has_default_cargo_containers', type: 'boolean'),
        new OA\Property(
            property: 'box_sizes_scu',
            type: 'array',
            items: new OA\Items(type: 'number')
        ),
        new OA\Property(property: 'link', type: 'string'),
    ],
    type: 'object'
)]
class ResourceTypeLinkResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'refined_version_uuid' => $this->refined_version_uuid,
            'validate_default_cargo_box' => $this->validate_default_cargo_box,
            'has_default_cargo_containers' => $this->has_default_cargo_containers,
            'box_sizes_scu' => $this->box_sizes_scu ?? [],
            'link' => $this->urlWithVersion(
                route('resource-types.blueprints.lookup', ['resourceType' => $this->uuid]),
                $request,
            ),
        ];
    }
}
