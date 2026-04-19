<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Faction;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'faction',
    title: 'Faction',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'default_reaction', type: 'string'),
        new OA\Property(property: 'faction_type', type: 'string', nullable: true),
        new OA\Property(property: 'able_to_arrest', type: 'boolean'),
        new OA\Property(property: 'polices_lawful_trespass', type: 'boolean'),
        new OA\Property(property: 'polices_criminality', type: 'boolean'),
        new OA\Property(property: 'no_legal_rights', type: 'boolean'),
        new OA\Property(property: 'has_reputation', type: 'boolean'),
        new OA\Property(property: 'headquarters', type: 'string', nullable: true),
        new OA\Property(property: 'founded', type: 'string', nullable: true),
        new OA\Property(property: 'leadership', type: 'string', nullable: true),
        new OA\Property(property: 'area', type: 'string', nullable: true),
        new OA\Property(property: 'focus', type: 'string', nullable: true),
        new OA\Property(property: 'lawful', type: 'boolean', nullable: true),
        new OA\Property(property: 'sort_order_scope', type: 'string', nullable: true),
        new OA\Property(property: 'is_npc', type: 'boolean'),
        new OA\Property(
            property: 'reputation_ladder',
            ref: '#/components/schemas/faction_reputation_ladder',
            nullable: true,
        ),
        new OA\Property(property: 'link', type: 'string', format: 'uri'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'faction_reputation_ladder',
    title: 'Faction Reputation Ladder',
    properties: [
        new OA\Property(property: 'scope_name', type: 'string'),
        new OA\Property(property: 'display_name', type: 'string', nullable: true),
        new OA\Property(property: 'reputation_ceiling', type: 'integer'),
        new OA\Property(property: 'initial_reputation', type: 'integer'),
        new OA\Property(
            property: 'standings',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/faction_standing'),
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'faction_standing',
    title: 'Faction Standing',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'display_name', type: 'string', nullable: true),
        new OA\Property(property: 'min_reputation', type: 'integer'),
        new OA\Property(property: 'drift_reputation', type: 'integer'),
        new OA\Property(property: 'drift_time_hours', type: 'integer'),
        new OA\Property(property: 'gated', type: 'boolean'),
    ],
    type: 'object'
)]
class FactionResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return ['reputationLadder'];
    }

    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->resource->uuid,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'default_reaction' => $this->resource->default_reaction,
            'faction_type' => $this->resource->faction_type,
            'able_to_arrest' => $this->resource->able_to_arrest,
            'polices_lawful_trespass' => $this->resource->polices_lawful_trespass,
            'polices_criminality' => $this->resource->polices_criminality,
            'no_legal_rights' => $this->resource->no_legal_rights,
            'has_reputation' => $this->resource->has_reputation,
            'headquarters' => $this->resource->headquarters,
            'founded' => $this->resource->founded,
            'leadership' => $this->resource->leadership,
            'area' => $this->resource->area,
            'focus' => $this->resource->focus,
            'lawful' => $this->resource->lawful,
            'sort_order_scope' => $this->resource->sort_order_scope,
            'is_npc' => $this->resource->is_npc,
            'reputation_ladder' => $this->when(
                $this->resource->relationLoaded('reputationRef') && $this->resource->reputationRef !== null,
                fn (): ?array => $this->mapReputationLadder(),
            ),
            'link' => $this->urlWithVersion(
                route('factions.show', ['faction' => $this->resource->uuid]),
                $request,
            ),
        ];
    }

    private function mapReputationLadder(): ?array
    {
        $ref = $this->resource->reputationRef;

        if ($ref === null) {
            return null;
        }

        $scope = $ref->scope;

        if ($scope === null) {
            return null;
        }

        return [
            'scope_name' => $scope->scope_name,
            'display_name' => $scope->display_name,
            'reputation_ceiling' => $scope->reputation_ceiling,
            'initial_reputation' => $scope->initial_reputation,
            'standings' => $scope->relationLoaded('standings')
                ? $scope->standings->map(fn ($standing): array => [
                    'uuid' => $standing->uuid,
                    'name' => $standing->name,
                    'display_name' => $standing->display_name,
                    'min_reputation' => $standing->min_reputation,
                    'drift_reputation' => $standing->drift_reputation,
                    'drift_time_hours' => $standing->drift_time_hours,
                    'gated' => $standing->gated,
                ])->values()->all()
                : [],
        ];
    }
}
