<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Faction;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'faction_index',
    title: 'Faction Summary',
    description: 'Compact faction representation used in list endpoints.',
    properties: [
        new OA\Property(property: 'uuid', description: 'Unique faction identifier.', type: 'string', format: 'uuid', example: '4e429470-4d4e-4c2b-a4ac-4de42ada16e0'),
        new OA\Property(property: 'name', description: 'Display name of the faction.', type: 'string', example: 'Aciedo Communications'),
        new OA\Property(property: 'faction_type', description: 'Category of the faction. One of: Lawful, Unlawful, LawEnforcement, PrivateSecurity.', type: 'string', example: 'Lawful', nullable: true),
        new OA\Property(property: 'lawful', description: 'Whether the faction is considered lawful.', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'is_npc', description: 'Whether the faction is NPC-controlled.', type: 'boolean', example: false),
        new OA\Property(property: 'has_reputation', description: 'Whether the faction tracks player reputation.', type: 'boolean', example: true),
        new OA\Property(property: 'link', description: 'API URL for the full faction detail.', type: 'string', format: 'uri'),
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
