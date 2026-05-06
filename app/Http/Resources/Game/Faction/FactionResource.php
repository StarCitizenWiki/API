<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Faction;

use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'faction',
    title: 'Faction',
    description: 'Full faction detail including reputation ladder when the faction has a reputation system.',
    properties: [
        new OA\Property(property: 'uuid', description: 'Unique faction identifier.', type: 'string', format: 'uuid', example: '4e429470-4d4e-4c2b-a4ac-4de42ada16e0'),
        new OA\Property(property: 'name', description: 'Display name of the faction.', type: 'string', example: 'Aciedo Communications'),
        new OA\Property(property: 'description', description: 'Lore description of the faction.', type: 'string', nullable: true),
        new OA\Property(property: 'default_reaction', description: 'Default reaction towards the player. One of: Friendly, Hostile, Neutral.', type: 'string', example: 'Neutral'),
        new OA\Property(property: 'faction_type', description: 'Category of the faction. One of: Lawful, Unlawful, LawEnforcement, PrivateSecurity.', type: 'string', example: 'Lawful'),
        new OA\Property(property: 'able_to_arrest', description: 'Whether the faction can arrest players.', type: 'boolean', example: false),
        new OA\Property(property: 'polices_lawful_trespass', description: 'Whether the faction enforces trespass violations against lawful characters.', type: 'boolean', example: false),
        new OA\Property(property: 'polices_criminality', description: 'Whether the faction actively polices criminal activity.', type: 'boolean', example: false),
        new OA\Property(property: 'no_legal_rights', description: 'Whether the faction operates without legal protections.', type: 'boolean', example: false),
        new OA\Property(property: 'has_reputation', description: 'Whether the faction tracks player reputation.', type: 'boolean', example: true),
        new OA\Property(property: 'headquarters', description: 'Location of the faction\'s headquarters.', type: 'string', nullable: true),
        new OA\Property(property: 'founded', description: 'Founding date or era of the faction.', type: 'string', nullable: true),
        new OA\Property(property: 'leadership', description: 'Current leadership of the faction.', type: 'string', nullable: true),
        new OA\Property(property: 'area', description: 'Primary area of operations.', type: 'string', nullable: true),
        new OA\Property(property: 'focus', description: 'Primary focus or industry of the faction.', type: 'string', nullable: true),
        new OA\Property(property: 'lawful', description: 'Whether the faction is considered lawful.', type: 'boolean', nullable: true),
        new OA\Property(property: 'sort_order_scope', description: 'Scope key used for sort ordering within reputation tiers.', type: 'string', nullable: true),
        new OA\Property(property: 'is_npc', description: 'Whether the faction is NPC-controlled.', type: 'boolean', example: false),
        new OA\Property(
            property: 'reputation_ladder',
            ref: '#/components/schemas/faction_reputation_ladder',
            description: 'Reputation ladder with standings. Requires ?include=reputationLadder.',
            nullable: true,
        ),
        new OA\Property(property: 'link', description: 'API URL for this faction.', type: 'string', format: 'uri'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'faction_reputation_ladder',
    title: 'Faction Reputation Ladder',
    description: 'Reputation ladder defining standings and thresholds for a faction.',
    properties: [
        new OA\Property(property: 'scope_name', description: 'Internal scope name for the reputation system.', type: 'string', example: 'FactionReputation'),
        new OA\Property(property: 'display_name', description: 'Human-readable label for the reputation scope.', type: 'string', example: 'Standing', nullable: true),
        new OA\Property(property: 'reputation_ceiling', description: 'Maximum attainable reputation value.', type: 'integer', example: 95250),
        new OA\Property(property: 'initial_reputation', description: 'Starting reputation value for new players.', type: 'integer', example: 0),
        new OA\Property(
            property: 'standings',
            description: 'Ordered list of reputation tiers from highest to lowest.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/faction_standing'),
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'faction_standing',
    title: 'Faction Standing',
    description: 'A single reputation tier within a faction\'s reputation ladder.',
    properties: [
        new OA\Property(property: 'uuid', description: 'Unique standing identifier.', type: 'string', format: 'uuid', example: 'bcd1f776-9a6e-4ffd-85c4-6df64f4cf2ed'),
        new OA\Property(property: 'name', description: 'Internal standing name.', type: 'string', example: 'Affinity_Good_100_Exalted'),
        new OA\Property(property: 'display_name', description: 'Human-readable standing name.', type: 'string', example: 'Exalted', nullable: true),
        new OA\Property(property: 'min_reputation', description: 'Minimum reputation required for this standing.', type: 'integer', example: 10000),
        new OA\Property(property: 'drift_reputation', description: 'Reputation change applied per drift cycle.', type: 'integer', example: 0),
        new OA\Property(property: 'drift_time_hours', description: 'Hours between drift cycles.', type: 'integer', example: 0),
        new OA\Property(property: 'gated', description: 'Whether this standing is gated and cannot be reached through normal progression.', type: 'boolean', example: false),
    ],
    type: 'object'
)]
class FactionResource extends FactionIndexResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'description' => $this->resource->description,
            'default_reaction' => $this->resource->default_reaction,
            'able_to_arrest' => $this->resource->able_to_arrest,
            'polices_lawful_trespass' => $this->resource->polices_lawful_trespass,
            'polices_criminality' => $this->resource->polices_criminality,
            'no_legal_rights' => $this->resource->no_legal_rights,
            'headquarters' => $this->resource->headquarters,
            'founded' => $this->resource->founded,
            'leadership' => $this->resource->leadership,
            'area' => $this->resource->area,
            'focus' => $this->resource->focus,
            'sort_order_scope' => $this->resource->sort_order_scope,
            'reputation_ladder' => $this->when(
                $this->resource->relationLoaded('reputationRef') && $this->resource->reputationRef !== null,
                fn (): ?array => $this->mapReputationLadder(),
            ),
        ]);
    }

    private function mapReputationLadder(): ?array
    {
        $ref = $this->resource->reputationRef;

        if ($ref === null) {
            return null;
        }

        $scope = $ref->factionScope;

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
