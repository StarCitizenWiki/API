<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Faction;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'faction_index',
    title: 'Faction Summary',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'faction_type', type: 'string', nullable: true),
        new OA\Property(property: 'lawful', type: 'boolean', nullable: true),
        new OA\Property(property: 'is_npc', type: 'boolean'),
        new OA\Property(property: 'has_reputation', type: 'boolean'),
        new OA\Property(property: 'link', type: 'string', format: 'uri'),
    ],
    type: 'object'
)]
class FactionIndexResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->resource->uuid,
            'name' => $this->resource->name,
            'faction_type' => $this->resource->faction_type,
            'lawful' => $this->resource->lawful,
            'is_npc' => $this->resource->is_npc,
            'has_reputation' => $this->resource->has_reputation,
            'link' => $this->urlWithVersion(
                route('factions.show', ['faction' => $this->resource->uuid]),
                $request,
            ),
        ];
    }
}
