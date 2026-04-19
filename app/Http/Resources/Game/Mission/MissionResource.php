<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Mission;

use App\Http\Resources\AbstractBaseResource;
use App\Models\Game\Faction;
use App\Support\Formatting\FormatMissionTitle;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'mission',
    title: 'Mission',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'title', type: 'string', nullable: true),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'mission_type', type: 'string', nullable: true),
        new OA\Property(property: 'mission_giver', type: 'string', nullable: true),
        new OA\Property(
            property: 'faction',
            properties: [
                new OA\Property(property: 'name', type: 'string', nullable: true),
                new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
                new OA\Property(property: 'faction_type', type: 'string', nullable: true),
                new OA\Property(property: 'lawful', type: 'boolean', nullable: true),
                new OA\Property(property: 'is_npc', type: 'boolean'),
                new OA\Property(property: 'has_reputation', type: 'boolean'),
                new OA\Property(property: 'headquarters', type: 'string', nullable: true),
                new OA\Property(property: 'area', type: 'string', nullable: true),
                new OA\Property(property: 'focus', type: 'string', nullable: true),
                new OA\Property(property: 'founded', type: 'string', nullable: true),
                new OA\Property(property: 'leadership', type: 'string', nullable: true),
                new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
                new OA\Property(
                    property: 'reputation_ladder',
                    ref: '#/components/schemas/mission_faction_reputation_scope',
                    nullable: true,
                ),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(property: 'rank_index', type: 'integer', nullable: true),
        new OA\Property(property: 'illegal', type: 'boolean'),
        new OA\Property(property: 'legality_label', type: 'string'),
        new OA\Property(property: 'shareable', type: 'boolean'),
        new OA\Property(property: 'once_only', type: 'boolean'),
        new OA\Property(property: 'available_in_prison', type: 'boolean'),
        new OA\Property(property: 'has_combat', type: 'boolean', nullable: true),
        new OA\Property(property: 'has_defend_objective', type: 'boolean', nullable: true),
        new OA\Property(property: 'enemy_count_min', type: 'integer', nullable: true),
        new OA\Property(property: 'enemy_count_max', type: 'integer', nullable: true),
        new OA\Property(property: 'min_crime_stat', type: 'integer', nullable: true),
        new OA\Property(property: 'max_crime_stat', type: 'integer', nullable: true),
        new OA\Property(property: 'reward_min', type: 'integer', nullable: true),
        new OA\Property(property: 'reward_max', type: 'integer', nullable: true),
        new OA\Property(property: 'reward_currency', type: 'string', nullable: true),
        new OA\Property(property: 'time_to_complete_minutes', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'star_systems', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
        new OA\Property(property: 'cooldown', ref: '#/components/schemas/mission_cooldown', nullable: true),
        new OA\Property(property: 'lifetime', ref: '#/components/schemas/mission_lifetime', nullable: true),
        new OA\Property(property: 'reaccept_after_failing', type: 'boolean', nullable: true),
        new OA\Property(property: 'reaccept_after_abandoning', type: 'boolean', nullable: true),
        new OA\Property(property: 'blueprints', ref: '#/components/schemas/mission_blueprints', nullable: true),
        new OA\Property(property: 'reward_items', type: 'array', items: new OA\Items(ref: '#/components/schemas/mission_reward_item'), nullable: true),
        new OA\Property(property: 'combat', ref: '#/components/schemas/mission_combat', nullable: true),
        new OA\Property(property: 'completion_tags', type: 'array', items: new OA\Items(ref: '#/components/schemas/mission_completion_tag'), nullable: true),
        new OA\Property(property: 'reputation_gained', type: 'array', items: new OA\Items(ref: '#/components/schemas/mission_reputation'), nullable: true),
        new OA\Property(property: 'reputation_lost', type: 'array', items: new OA\Items(ref: '#/components/schemas/mission_reputation'), nullable: true),
        new OA\Property(property: 'hauling_orders', type: 'array', items: new OA\Items(ref: '#/components/schemas/mission_hauling_order'), nullable: true),
        new OA\Property(property: 'cost', type: 'integer', nullable: true),
        new OA\Property(property: 'max_players_per_instance', type: 'integer', nullable: true),
        new OA\Property(property: 'fail_if_became_criminal', type: 'boolean', nullable: true),
        new OA\Property(
            property: 'min_standing',
            properties: [
                new OA\Property(property: 'name', type: 'string', nullable: true),
                new OA\Property(property: 'min_reputation', type: 'integer', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'max_standing',
            properties: [
                new OA\Property(property: 'name', type: 'string', nullable: true),
                new OA\Property(property: 'min_reputation', type: 'integer', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'mission_tokens',
            properties: [
                new OA\Property(property: 'destinations', type: 'array', items: new OA\Items(type: 'string')),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'deadline',
            properties: [
                new OA\Property(property: 'auto_end', type: 'boolean', nullable: true),
                new OA\Property(property: 'end_reason', type: 'string', nullable: true),
                new OA\Property(property: 'completion_time_minutes', type: 'integer', nullable: true),
                new OA\Property(property: 'result_after_timer', type: 'string', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'broker_reputation_prerequisites',
            properties: [
                new OA\Property(property: 'max_wanted_level', type: 'integer', nullable: true),
                new OA\Property(property: 'min_wanted_level', type: 'integer', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'item_counts',
            properties: [
                new OA\Property(property: 'max_items', type: 'integer', nullable: true),
                new OA\Property(property: 'min_items', type: 'integer', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'entity_spawns',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
                    new OA\Property(property: 'amount', type: 'integer', nullable: true),
                    new OA\Property(property: 'weight', type: 'integer', nullable: true),
                    new OA\Property(property: 'group_name', type: 'string', nullable: true),
                    new OA\Property(property: 'markup_tags', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
                    new OA\Property(property: 'negative_tags', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
                    new OA\Property(property: 'merged_tags', type: 'array', items: new OA\Items(type: 'string')),
                ],
                type: 'object'
            ),
            nullable: true
        ),
        new OA\Property(property: 'hidden_in_mobiglas', type: 'boolean', nullable: true),
        new OA\Property(property: 'notify_on_available', type: 'boolean', nullable: true),
        new OA\Property(property: 'reward_scope', type: 'string', nullable: true),
        new OA\Property(property: 'reputation_amount', type: 'integer', nullable: true),
        new OA\Property(property: 'game_version', type: 'string', nullable: true),
        new OA\Property(
            property: 'starmap_locations',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mission_starmap_location_group'),
            nullable: true
        ),
        new OA\Property(
            property: 'prerequisite_groups',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mission_prerequisite_group'),
            nullable: true
        ),
        new OA\Property(
            property: 'unlock_groups',
            description: 'Completion tag groups that become available after completing this mission',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mission_unlock_group'),
            nullable: true
        ),
        new OA\Property(
            property: 'merged_locations',
            description: 'Starmap locations grouped by logical purpose categories (Destinations, Locations, Availability)',
            type: 'object',
            nullable: true
        ),
        new OA\Property(property: 'has_rewards', type: 'boolean'),
        new OA\Property(property: 'has_combat_section', type: 'boolean'),
        new OA\Property(property: 'has_locations', type: 'boolean'),
        new OA\Property(property: 'has_chain', type: 'boolean'),
        new OA\Property(property: 'link', type: 'string', format: 'uri'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_cooldown',
    title: 'Mission Cooldown',
    properties: [
        new OA\Property(property: 'label', type: 'string', nullable: true),
        new OA\Property(property: 'personal_seconds', type: 'integer'),
        new OA\Property(property: 'abandoned_seconds', type: 'integer'),
        new OA\Property(property: 'personal_variation_seconds', type: 'integer'),
        new OA\Property(property: 'abandoned_variation_seconds', type: 'integer'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_lifetime',
    title: 'Mission Lifetime',
    properties: [
        new OA\Property(property: 'label', type: 'string', nullable: true),
        new OA\Property(property: 'respawn_time_seconds', type: 'integer'),
        new OA\Property(property: 'max_instances', type: 'integer'),
        new OA\Property(property: 'respawn_time_variation_seconds', type: 'integer'),
        new OA\Property(property: 'max_instances_per_player', type: 'integer'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_blueprints',
    title: 'Mission Blueprints',
    properties: [
        new OA\Property(property: 'drop_chance', type: 'number', format: 'float'),
        new OA\Property(property: 'drop_chance_percent', type: 'number', format: 'float', nullable: true),
        new OA\Property(
            property: 'items',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mission_blueprint_item')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_blueprint_item',
    title: 'Mission Blueprint Item',
    properties: [
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'item_link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'blueprint_link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'web_item_link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'web_blueprint_link', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_reward_item',
    title: 'Mission Reward Item',
    properties: [
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'amount', type: 'integer', nullable: true),
        new OA\Property(property: 'send_to_home', type: 'boolean', nullable: true),
        new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'web_link', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_combat',
    title: 'Mission Combat',
    properties: [
        new OA\Property(
            property: 'summary',
            properties: [
                new OA\Property(
                    property: 'total',
                    properties: [
                        new OA\Property(property: 'min', type: 'integer'),
                        new OA\Property(property: 'max', type: 'integer'),
                    ],
                    type: 'object'
                ),
                new OA\Property(
                    property: 'by_group',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'group_name', type: 'string'),
                            new OA\Property(property: 'min', type: 'integer'),
                            new OA\Property(property: 'max', type: 'integer'),
                        ],
                        type: 'object'
                    )
                ),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'spawns',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'role', type: 'string', nullable: true),
                    new OA\Property(property: 'weight', type: 'integer', nullable: true),
                    new OA\Property(property: 'group_name', type: 'string', nullable: true),
                    new OA\Property(property: 'spawn_kind', type: 'string', nullable: true),
                    new OA\Property(property: 'concurrent_amount', type: 'integer', nullable: true),
                ],
                type: 'object'
            )
        ),
        new OA\Property(
            property: 'aggregated_spawns',
            description: 'Spawns grouped by role, group_name, and spawn_kind with aggregated concurrent ranges',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'role', type: 'string'),
                    new OA\Property(property: 'group_name', type: 'string', nullable: true),
                    new OA\Property(property: 'spawn_kind', type: 'string', nullable: true),
                    new OA\Property(property: 'concurrent_min', type: 'integer', nullable: true),
                    new OA\Property(property: 'concurrent_max', type: 'integer', nullable: true),
                    new OA\Property(property: 'weight', type: 'integer', nullable: true),
                ],
                type: 'object'
            )
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_starmap_location_group',
    title: 'Mission Starmap Location Group',
    properties: [
        new OA\Property(property: 'purpose', type: 'string'),
        new OA\Property(
            property: 'locations',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mission_starmap_location')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_starmap_location',
    title: 'Mission Starmap Location',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'system', type: 'string', nullable: true),
        new OA\Property(property: 'type', type: 'string', nullable: true),
        new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'web_link', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_completion_tag',
    title: 'Mission Completion Tag',
    properties: [
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(
            property: 'unlocks_missions',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mission_completion_tag_mission')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_completion_tag_mission',
    title: 'Mission Completion Tag Mission',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'title', type: 'string', nullable: true),
        new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'web_link', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_reputation',
    title: 'Mission Reputation',
    properties: [
        new OA\Property(property: 'tier', type: 'string', nullable: true),
        new OA\Property(property: 'scope', type: 'string', nullable: true),
        new OA\Property(property: 'amount', type: 'integer', nullable: true),
        new OA\Property(property: 'faction', type: 'string', nullable: true),
        new OA\Property(property: 'faction_uuid', type: 'string', format: 'uuid', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_hauling_order',
    title: 'Mission Hauling Order',
    properties: [
        new OA\Property(property: 'kind', type: 'string', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(
            property: 'items',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mission_hauling_order_item')
        ),
        new OA\Property(property: 'max_scu', type: 'integer', nullable: true),
        new OA\Property(property: 'min_scu', type: 'integer', nullable: true),
        new OA\Property(property: 'max_amount', type: 'integer', nullable: true),
        new OA\Property(property: 'min_amount', type: 'integer', nullable: true),
        new OA\Property(property: 'max_container_size', type: 'integer', nullable: true),
        new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'web_link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(
            property: 'or_options',
            type: 'array',
            items: new OA\Items(
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/mission_hauling_order')
            ),
            nullable: true
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_hauling_order_item',
    title: 'Mission Hauling Order Item',
    properties: [
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'web_link', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_starmap_location',
    title: 'Mission Starmap Location',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'system', type: 'string', nullable: true),
        new OA\Property(property: 'type', type: 'string', nullable: true),
        new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_prerequisite_group',
    title: 'Mission Prerequisite Group',
    properties: [
        new OA\Property(property: 'required_count', type: 'integer', nullable: true),
        new OA\Property(
            property: 'required_tags',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'name', type: 'string', nullable: true),
                    new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
                ],
                type: 'object'
            )
        ),
        new OA\Property(
            property: 'excluded_tags',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'name', type: 'string', nullable: true),
                    new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
                ],
                type: 'object'
            )
        ),
        new OA\Property(
            property: 'missions',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'title', type: 'string', nullable: true),
                    new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
                    new OA\Property(property: 'web_link', type: 'string', format: 'uri', nullable: true),
                ],
                type: 'object'
            )
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_unlock_group',
    title: 'Mission Unlock Group',
    description: 'A completion tag group that unlocks missions when this mission is completed.',
    properties: [
        new OA\Property(property: 'tag_name', type: 'string', nullable: true),
        new OA\Property(property: 'tag_uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(
            property: 'missions',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mission_chain_link')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_chain_link',
    title: 'Mission Chain Link',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'title', type: 'string', nullable: true),
        new OA\Property(property: 'mission_type', type: 'string', nullable: true),
        new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'web_link', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_faction_reputation_scope',
    title: 'Mission Faction Reputation Scope',
    properties: [
        new OA\Property(property: 'scope_name', type: 'string', nullable: true),
        new OA\Property(
            property: 'standings',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mission_faction_standing'),
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_faction_standing',
    title: 'Mission Faction Standing',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'display_name', type: 'string', nullable: true),
        new OA\Property(property: 'min_reputation', type: 'integer'),
    ],
    type: 'object'
)]
class MissionResource extends AbstractBaseResource
{
    private const ROLE_ORDER = ['enemy', 'defend_target', 'escort_target'];

    private const ROLE_SORT = ['enemy' => 0, 'defend_target' => 1, 'escort_target' => 2, 'other' => 3];

    private const PURPOSE_GROUP_MAP = [
        'Destinations' => ['Destination', 'Destination1', 'Destination2', 'Destination3', 'Destination4', 'DropoffDestination1', 'Dropoff1', 'GoToLocation'],
        'Locations' => ['Location', 'Location1', 'Location2', 'Location3', 'Location4', 'NearbyLocation', 'NeabyLocation', 'SubLocation'],
        'Availability' => ['availability', null],
    ];

    public static function validIncludes(): array
    {
        return ['faction', 'starmapLocations', 'prerequisiteGroups', 'unlockGroups', 'blueprints', 'rewardItems'];
    }

    public function toArray(Request $request): array
    {
        $mission = $this->resource->mission;
        $data = $this->resource->data;

        return [
            'uuid' => $mission?->uuid,
            'title' => FormatMissionTitle::format($this->resource->title, $this->resource->debug_name),
            'description' => $this->resource->description,
            'mission_type' => $this->resource->mission_type,
            'mission_giver' => $this->resource->mission_giver,
            'faction' => $this->resource->faction ? [
                'name' => $this->resource->faction->name,
                'uuid' => $this->resource->faction->uuid,
                'faction_type' => $this->resource->faction->faction_type,
                'lawful' => $this->resource->faction->lawful,
                'is_npc' => $this->resource->faction->is_npc,
                'has_reputation' => $this->resource->faction->has_reputation,
                'headquarters' => $this->resource->faction->headquarters,
                'area' => $this->resource->faction->area,
                'focus' => $this->resource->faction->focus,
                'founded' => $this->resource->faction->founded,
                'leadership' => $this->resource->faction->leadership,
                'link' => $this->urlWithVersion(
                    route('factions.show', ['faction' => $this->resource->faction->uuid]),
                    $request,
                ),
                'reputation_ladder' => $this->when(
                    $this->resource->faction->relationLoaded('reputationRef') && $this->resource->faction->reputationRef !== null,
                    fn (): ?array => $this->mapFactionReputationLadder(),
                ),
            ] : null,
            'rank_index' => $this->resource->rank_index,
            'illegal' => $this->resource->illegal,
            'legality_label' => $this->resource->illegal ? 'Illegal' : 'Legal',
            'shareable' => $this->resource->shareable,
            'once_only' => $this->resource->once_only,
            'available_in_prison' => $this->resource->available_in_prison,
            'has_combat' => $this->resource->has_combat,
            'has_defend_objective' => $this->resource->has_defend_objective,
            'enemy_count_min' => $this->resource->enemy_count_min,
            'enemy_count_max' => $this->resource->enemy_count_max,
            'min_crime_stat' => $this->resource->min_crime_stat,
            'max_crime_stat' => $this->resource->max_crime_stat,
            'reward_min' => $this->resource->reward_min,
            'reward_max' => $this->resource->reward_max,
            'reward_currency' => $this->resource->reward_currency,
            'time_to_complete_minutes' => $this->resource->time_to_complete_minutes,
            'star_systems' => $this->resource->star_systems,
            'cooldown' => $this->mapCooldown($data),
            'lifetime' => $this->mapLifetime($data),
            'reaccept_after_failing' => $this->parseNullableBool($data?->get('ReacceptAfterFailing')),
            'reaccept_after_abandoning' => $this->parseNullableBool($data?->get('ReacceptAfterAbandoning')),
            'blueprints' => $this->mapBlueprints($request),
            'reward_items' => $this->when(
                $this->resource->relationLoaded('rewardItems'),
                fn (): ?array => $this->mapRewardItemsFromRelation($request),
            ),
            'combat' => $this->mapCombat($data),
            'completion_tags' => $this->mapCompletionTags($data, $request),
            'reputation_gained' => $this->mapReputation($data?->get('ReputationGained')),
            'reputation_lost' => $this->mapReputation($data?->get('ReputationLost')),
            'hauling_orders' => $this->mapHaulingOrders($data, $request),
            'cost' => $data?->has('Cost') ? (int) $data->get('Cost') : null,
            'max_players_per_instance' => $data?->get('MaxPlayersPerInstance'),
            'fail_if_became_criminal' => $this->parseNullableBool($data?->get('FailIfBecameCriminal')),
            'min_standing' => $this->mapStanding($data?->get('MinStanding')),
            'max_standing' => $this->mapStanding($data?->get('MaxStanding')),
            'mission_tokens' => $this->mapMissionTokens($data?->get('MissionTokens')),
            'deadline' => $this->mapDeadline($data?->get('Deadline')),
            'broker_reputation_prerequisites' => $this->mapBrokerReputationPrerequisites($data?->get('BrokerReputationPrerequisites')),
            'item_counts' => $this->mapItemCounts($data?->get('ItemCounts')),
            'entity_spawns' => $this->mapEntitySpawns($data?->get('EntitySpawns')),
            'hidden_in_mobiglas' => $this->parseNullableBool($data?->get('HiddenInMobiglas')),
            'notify_on_available' => $this->parseNullableBool($data?->get('NotifyOnAvailable')),
            'reward_scope' => $this->resource->reward_scope,
            'reputation_amount' => $this->extractFirstReputationAmount($data),
            'game_version' => $this->resource->gameVersion?->code,
            'starmap_locations' => $this->when(
                $this->resource->relationLoaded('starmapLocations'),
                fn (): array => $this->mapStarmapLocations($request),
            ),
            'prerequisite_groups' => $this->when(
                $this->resource->relationLoaded('prerequisiteGroups'),
                fn (): array => $this->mapPrerequisiteGroups($request),
            ),
            'unlock_groups' => $this->when(
                $this->resource->relationLoaded('unlockGroups'),
                fn (): array => $this->mapUnlockGroups($request),
            ),

            'merged_locations' => $this->when(
                $this->resource->relationLoaded('starmapLocations'),
                fn (): array => $this->mapMergedLocations($request),
            ),
            'has_rewards' => $this->computeHasRewards($data),
            'has_combat_section' => $this->computeHasCombatSection($data),
            'has_locations' => $this->resource->relationLoaded('starmapLocations')
                && ($this->resource->starmapLocations?->isNotEmpty() ?? false),
            'has_chain' => $this->computeHasChain(),
            'link' => $this->urlWithVersion(
                route('missions.show', ['mission' => $mission?->uuid]),
                $request,
            ),
        ];
    }

    private function mapCooldown($data): ?array
    {
        $cooldown = $data?->get('Cooldown');

        if (! is_array($cooldown)) {
            return null;
        }

        $personalSeconds = $cooldown['PersonalSeconds'] ?? null;

        return [
            'label' => is_numeric($personalSeconds) && $personalSeconds > 0
                ? (string) CarbonInterval::seconds((int) $personalSeconds)->cascade()->forHumans()
                : null,
            'personal_seconds' => $personalSeconds,
            'abandoned_seconds' => $cooldown['AbandonedSeconds'] ?? null,
            'personal_variation_seconds' => $cooldown['PersonalVariationSeconds'] ?? null,
            'abandoned_variation_seconds' => $cooldown['AbandonedVariationSeconds'] ?? null,
        ];
    }

    private function mapLifetime($data): ?array
    {
        $lifetime = $data?->get('Lifetime');

        if (! is_array($lifetime)) {
            return null;
        }

        $respawnTimeMinutes = $lifetime['RespawnTime'] ?? null;
        $respawnTimeSeconds = is_numeric($respawnTimeMinutes) ? (int) $respawnTimeMinutes * 60 : null;

        return [
            'label' => $respawnTimeSeconds !== null && $respawnTimeSeconds > 0
                ? (string) CarbonInterval::seconds($respawnTimeSeconds)->cascade()->forHumans()
                : null,
            'respawn_time_seconds' => $respawnTimeSeconds,
            'max_instances' => $lifetime['MaxInstances'] ?? null,
            'respawn_time_variation_seconds' => isset($lifetime['RespawnTimeVariation']) && is_numeric($lifetime['RespawnTimeVariation'])
                ? (int) $lifetime['RespawnTimeVariation'] * 60
                : null,
            'max_instances_per_player' => $lifetime['MaxInstancesPerPlayer'] ?? null,
        ];
    }

    private function parseNullableBool(mixed $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    private function mapStanding($standing): ?array
    {
        if (! is_array($standing)) {
            return null;
        }

        return [
            'name' => $standing['Name'] ?? null,
            'min_reputation' => $standing['MinReputation'] ?? null,
        ];
    }

    private function mapMissionTokens($tokens): ?array
    {
        if (! is_array($tokens) || empty($tokens)) {
            return null;
        }

        $destinations = $tokens['Destination'] ?? [];

        if (! is_array($destinations) || empty($destinations)) {
            return null;
        }

        return [
            'destinations' => $destinations,
        ];
    }

    private function mapDeadline($deadline): ?array
    {
        if (! is_array($deadline)) {
            return null;
        }

        return [
            'auto_end' => $deadline['AutoEnd'] ?? null,
            'end_reason' => $deadline['EndReason'] ?? null,
            'completion_time_minutes' => $deadline['CompletionTime'] ?? null,
            'result_after_timer' => $deadline['ResultAfterTimer'] ?? null,
        ];
    }

    private function mapBrokerReputationPrerequisites($prerequisites): ?array
    {
        if (! is_array($prerequisites)) {
            return null;
        }

        return [
            'max_wanted_level' => $prerequisites['MaxWantedLevel'] ?? null,
            'min_wanted_level' => $prerequisites['MinWantedLevel'] ?? null,
        ];
    }

    private function mapItemCounts($counts): ?array
    {
        if (! is_array($counts)) {
            return null;
        }

        return [
            'max_items' => $counts['MaxItems'] ?? null,
            'min_items' => $counts['MinItems'] ?? null,
        ];
    }

    private function mapEntitySpawns($spawns): ?array
    {
        if (! is_array($spawns) || empty($spawns)) {
            return null;
        }

        return array_map(static function (array $spawn): array {
            $tags = $spawn['Tags'] ?? [];
            $markupTags = $spawn['MarkupTags'] ?? [];

            $tagNames = array_filter(array_map(
                static fn (array $tag): ?string => $tag['Name'] ?? null,
                is_array($tags) ? $tags : [],
            ));
            $markupTagNames = array_filter(array_map(
                static fn (array $tag): ?string => $tag['Name'] ?? null,
                is_array($markupTags) ? $markupTags : [],
            ));

            return [
                'tags' => $tags,
                'amount' => $spawn['Amount'] ?? null,
                'weight' => $spawn['Weight'] ?? null,
                'group_name' => $spawn['GroupName'] ?? null,
                'markup_tags' => $markupTags,
                'negative_tags' => $spawn['NegativeTags'] ?? null,
                'merged_tags' => array_values(array_unique(array_merge($tagNames, $markupTagNames))),
            ];
        }, $spawns);
    }

    private function mapBlueprints(Request $request): ?array
    {
        $blueprints = $this->resource->blueprints;

        if ($blueprints === null || $blueprints->isEmpty()) {
            return null;
        }

        return [
            'drop_chance' => $this->resource->blueprint_drop_chance,
            'drop_chance_percent' => is_numeric($this->resource->blueprint_drop_chance)
                ? round((float) $this->resource->blueprint_drop_chance * 100, 1)
                : null,
            'items' => $blueprints->map(fn ($blueprintData): array => [
                'name' => $blueprintData->output_name,
                'uuid' => $blueprintData->output_item_uuid,
                'item_link' => $blueprintData->output_item_uuid !== null
                    ? $this->urlWithVersion(
                        route('items.show', ['identifier' => $blueprintData->output_item_uuid]),
                        $request,
                    )
                    : null,
                'blueprint_link' => $blueprintData->blueprint?->uuid !== null
                    ? $this->urlWithVersion(
                        route('blueprints.show', ['blueprint' => $blueprintData->blueprint->uuid]),
                        $request,
                    )
                    : null,
                'web_item_link' => $blueprintData->output_item_uuid !== null
                    ? route('web.items.show', ['item' => $blueprintData->output_item_uuid])
                    : null,
                'web_blueprint_link' => $blueprintData->blueprint?->uuid !== null
                    ? route('web.blueprints.show', ['blueprint' => $blueprintData->blueprint->uuid])
                    : null,
            ])->values()->all(),
        ];
    }

    private function mapRewardItemsFromRelation(Request $request): ?array
    {
        $items = $this->resource->rewardItems;

        if ($items === null || $items->isEmpty()) {
            return null;
        }

        return $items->map(fn ($itemData): array => [
            'name' => $itemData->name,
            'uuid' => $itemData->item?->uuid,
            'amount' => $itemData->pivot->amount,
            'send_to_home' => $itemData->pivot->send_to_home,
            'link' => $itemData->item?->uuid !== null
                ? $this->urlWithVersion(
                    route('items.show', ['identifier' => $itemData->item->uuid]),
                    $request,
                )
                : null,
            'web_link' => $itemData->item?->uuid !== null
                ? route('web.items.show', ['item' => $itemData->item->uuid])
                : null,
        ])->values()->all();
    }

    private function mapCombat($data): ?array
    {
        $summary = $data?->get('CombatSummary');
        $spawns = $data?->get('Combat');

        $hasSummary = is_array($summary) && isset($summary['Total']);
        $hasSpawns = is_array($spawns) && ! empty($spawns);

        if (! $hasSummary && ! $hasSpawns) {
            return null;
        }

        $result = [];

        if ($hasSummary) {
            $total = $summary['Total'] ?? [];

            $byGroup = collect($summary['ByGroup'] ?? [])->map(fn (array $group): array => [
                'group_name' => $group['GroupName'] ?? null,
                'min' => $group['Min'] ?? null,
                'max' => $group['Max'] ?? null,
            ])->values()->all();

            $result['summary'] = [
                'total' => [
                    'min' => $total['Min'] ?? null,
                    'max' => $total['Max'] ?? null,
                ],
                'by_group' => $byGroup,
            ];
        }

        if ($hasSpawns) {
            $mappedSpawns = collect($spawns)->map(fn (array $spawn): array => [
                'role' => $spawn['Role'] ?? null,
                'weight' => $spawn['Weight'] ?? null,
                'group_name' => $spawn['GroupName'] ?? null,
                'spawn_kind' => $spawn['SpawnKind'] ?? null,
                'concurrent_amount' => $spawn['ConcurrentAmount'] ?? null,
            ])->values()->all();

            $result['spawns'] = $mappedSpawns;
            $result['aggregated_spawns'] = $this->computeAggregatedSpawns($mappedSpawns);
        }

        return $result;
    }

    private function computeAggregatedSpawns(array $spawns): array
    {
        return collect($spawns)
            ->map(function (array $spawn): array {
                $role = $spawn['role'];
                $spawn['_role'] = in_array($role, self::ROLE_ORDER, true) ? $role : 'other';

                return $spawn;
            })
            ->groupBy(fn (array $s): string => $s['_role'].'|'.($s['group_name'] ?? '-').'|'.($s['spawn_kind'] ?? '-'))
            ->map(function ($group): array {
                $first = $group->first();
                $concurrent = $group->map(fn (array $s) => $s['concurrent_amount'])->filter();
                $weights = $group->map(fn (array $s) => $s['weight'])->filter(fn (?int $v): bool => $v !== null && $v > 0);

                return [
                    'role' => $first['_role'],
                    'group_name' => $first['group_name'],
                    'spawn_kind' => $first['spawn_kind'],
                    'concurrent_min' => $concurrent->min(),
                    'concurrent_max' => $concurrent->max(),
                    'weight' => $weights->isNotEmpty() ? $weights->max() : null,
                ];
            })
            ->sortBy(fn (array $item): int => self::ROLE_SORT[$item['role']] ?? 99)
            ->values()
            ->all();
    }

    private function mapCompletionTags($data, Request $request): ?array
    {
        $tags = $data?->get('CompletionTags');

        if (! is_array($tags) || empty($tags)) {
            return null;
        }

        return collect($tags)
            ->filter(fn (array $tag): bool => ! empty($tag['UnlocksMissions']))
            ->map(function (array $tag) use ($request): array {
                return [
                    'name' => $tag['Name'] ?? null,
                    'unlocks_missions' => collect($tag['UnlocksMissions'] ?? [])->map(function (array $m) use ($request): array {
                        return [
                            'uuid' => $m['UUID'] ?? null,
                            'title' => $m['Title'] ?? null,
                            'link' => isset($m['UUID'])
                                ? $this->urlWithVersion(
                                    route('missions.show', ['mission' => $m['UUID']]),
                                    $request,
                                )
                                : null,
                            'web_link' => isset($m['UUID'])
                                ? route('web.missions.show', ['mission' => $m['UUID']])
                                : null,
                        ];
                    })->values()->all(),
                ];
            })->values()->all();
    }

    private function mapReputation($entries): ?array
    {
        if (! is_array($entries) || empty($entries)) {
            return null;
        }

        $factionNames = $this->resolveFactionNames($entries);

        return collect($entries)->map(fn (array $entry): array => [
            'tier' => $entry['Tier'] ?? null,
            'scope' => $entry['Scope'] ?? null,
            'amount' => $entry['Amount'] ?? null,
            'faction' => $this->resolveFactionName($entry['Faction'] ?? null, $entry['FactionUUID'] ?? null, $factionNames),
            'faction_uuid' => $entry['FactionUUID'] ?? null,
        ])->values()->all();
    }

    private function resolveFactionNames(array $entries): array
    {
        $uuids = collect($entries)
            ->filter(fn (array $entry): bool => str_contains($entry['Faction'] ?? '', 'UNINITIALIZED'))
            ->map(fn (array $entry): ?string => $entry['FactionUUID'] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($uuids === []) {
            return [];
        }

        return Faction::query()->whereIn('uuid', $uuids)->pluck('name', 'uuid')->all();
    }

    private function resolveFactionName(?string $raw, ?string $uuid, array $resolvedNames): ?string
    {
        if ($raw !== null && ! str_contains($raw, 'UNINITIALIZED')) {
            return $raw;
        }

        if ($uuid !== null && isset($resolvedNames[$uuid])) {
            return $resolvedNames[$uuid];
        }

        return $raw;
    }

    private function mapHaulingOrders($data, Request $request): ?array
    {
        $orders = $data?->get('HaulingOrders');

        if (! is_array($orders) || empty($orders)) {
            return null;
        }

        return $this->mapHaulingOrderEntries($orders, $request);
    }

    private function mapHaulingOrderEntries(array $entries, Request $request): array
    {
        $result = [];

        foreach ($entries as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $kind = $entry['Kind'] ?? $entry['ItemKind'] ?? null;

            if ($kind === 'Or') {
                $mapped = [
                    'kind' => $kind,
                    'or_options' => collect($entry['OrOptions'] ?? [])
                        ->map(fn (array $group): array => $this->mapHaulingOrderEntries($group, $request))
                        ->values()
                        ->all(),
                ];
            } else {
                $uuid = $entry['UUID'] ?? null;
                $mapped = [
                    'kind' => $kind,
                    'name' => $entry['Name'] ?? null,
                    'uuid' => $uuid,
                    'items' => collect($entry['Items'] ?? [])->map(function (array $item) use ($kind, $request): array {
                        $itemUuid = $item['UUID'] ?? $item['ItemUUID'] ?? null;

                        return [
                            'name' => $item['Name'] ?? null,
                            'uuid' => $itemUuid,
                            'link' => $this->haulingLink($itemUuid, $kind, $request),
                            'web_link' => $this->haulingWebLink($itemUuid, $kind),
                        ];
                    })->values()->all(),
                    'max_scu' => max((int) ($entry['MinScu'] ?? 0), (int) ($entry['MaxScu'] ?? 0)) ?: null,
                    'min_scu' => min((int) ($entry['MinScu'] ?? 0), (int) ($entry['MaxScu'] ?? 0)) ?: null,
                    'max_amount' => max((int) ($entry['MinAmount'] ?? 0), (int) ($entry['MaxAmount'] ?? 0)) ?: null,
                    'min_amount' => min((int) ($entry['MinAmount'] ?? 0), (int) ($entry['MaxAmount'] ?? 0)) ?: null,
                    'max_container_size' => $entry['MaxContainerSize'] ?? null,
                    'link' => $this->haulingLink($uuid, $kind, $request),
                    'web_link' => $this->haulingWebLink($uuid, $kind),
                ];
            }

            $result[] = $mapped;
        }

        return $result;
    }

    private function haulingLink(?string $uuid, ?string $kind, Request $request): ?string
    {
        if ($uuid === null) {
            return null;
        }

        if ($kind === 'Resource') {
            return $this->urlWithVersion(
                route('commodities.show', ['commodity' => $uuid]),
                $request,
            );
        }

        if ($kind === 'Entity' || $kind === 'Entities' || $kind === 'MissionItem') {
            return $this->urlWithVersion(
                route('items.show', ['identifier' => $uuid]),
                $request,
            );
        }

        return null;
    }

    private function haulingWebLink(?string $uuid, ?string $kind): ?string
    {
        if ($uuid === null) {
            return null;
        }

        if ($kind === 'Resource') {
            return route('web.commodities.show', ['identifier' => $uuid]);
        }

        if ($kind === 'Entity' || $kind === 'Entities' || $kind === 'MissionItem') {
            return route('web.items.show', ['item' => $uuid]);
        }

        return null;
    }

    private function mapStarmapLocations(Request $request): array
    {
        $grouped = [];

        foreach ($this->resource->starmapLocations as $location) {
            $purpose = $location->pivot->purpose ?? null;
            $uuid = $location->location?->uuid;

            $grouped[$purpose][] = $this->buildLocationData($location, $uuid, $request);
        }

        return collect($grouped)->map(fn (array $locations, ?string $purpose): array => [
            'purpose' => $purpose ?: 'Availability',
            'locations' => $locations,
        ])->values()->all();
    }

    private function mapMergedLocations(Request $request): array
    {
        $merged = [];

        foreach ($this->resource->starmapLocations as $location) {
            $purpose = $location->pivot->purpose ?? null;
            $matchedGroup = null;

            foreach (self::PURPOSE_GROUP_MAP as $label => $purposes) {
                if (in_array($purpose, $purposes, true)) {
                    $matchedGroup = $label;
                    break;
                }
            }

            $matchedGroup ??= ucfirst((string) ($purpose ?? 'Unknown'));

            $uuid = $location->location?->uuid;
            $merged[$matchedGroup][] = $this->buildLocationData($location, $uuid, $request);
        }

        return $merged;
    }

    private function buildLocationData($location, ?string $uuid, Request $request): array
    {
        return [
            'uuid' => $uuid,
            'name' => $location->name,
            'system' => $location->system,
            'type' => $location->type_name,
            'link' => $uuid !== null
                ? $this->urlWithVersion(
                    route('locations.show', ['identifier' => $uuid]),
                    $request,
                )
                : null,
            'web_link' => $uuid !== null
                ? route('web.locations.show', ['identifier' => $uuid])
                : null,
        ];
    }

    private function mapPrerequisiteGroups(Request $request): array
    {
        return $this->resource->prerequisiteGroups->map(function ($group) use ($request): array {
            return [
                'required_count' => $group->required_count,
                'required_tags' => $group->tags->where('type', 'required')->values()->map(fn ($tag): array => [
                    'name' => $tag->tag_name,
                    'uuid' => $tag->tag_uuid,
                ])->all(),
                'excluded_tags' => $group->tags->where('type', 'excluded')->values()->map(fn ($tag): array => [
                    'name' => $tag->tag_name,
                    'uuid' => $tag->tag_uuid,
                ])->all(),
                'missions' => $group->missions->map(function ($groupMission) use ($request): array {
                    $linked = $groupMission->linkedMissionData;

                    return [
                        'uuid' => $linked?->mission?->uuid,
                        'title' => FormatMissionTitle::format($linked?->title, $linked?->debug_name),
                        'mission_type' => $linked?->mission_type,
                        'link' => $linked?->mission?->uuid !== null
                            ? $this->urlWithVersion(
                                route('missions.show', ['mission' => $linked->mission->uuid]),
                                $request,
                            )
                            : null,
                        'web_link' => $linked?->mission?->uuid !== null
                            ? route('web.missions.show', ['mission' => $linked->mission->uuid])
                            : null,
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }

    private function mapUnlockGroups(Request $request): array
    {
        return $this->resource->unlockGroups->map(function ($group) use ($request): array {
            return [
                'tag_name' => $group->tag_name,
                'tag_uuid' => $group->tag_uuid,
                'missions' => $group->missions->map(function ($groupMission) use ($request): array {
                    $linked = $groupMission->linkedMissionData;

                    return [
                        'uuid' => $linked?->mission?->uuid,
                        'title' => FormatMissionTitle::format($linked?->title, $linked?->debug_name),
                        'mission_type' => $linked?->mission_type,
                        'link' => $linked?->mission?->uuid !== null
                            ? $this->urlWithVersion(
                                route('missions.show', ['mission' => $linked->mission->uuid]),
                                $request,
                            )
                            : null,
                        'web_link' => $linked?->mission?->uuid !== null
                            ? route('web.missions.show', ['mission' => $linked->mission->uuid])
                            : null,
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }

    private function computeHasRewards($data): bool
    {
        if ($this->resource->relationLoaded('rewardItems') && ($this->resource->rewardItems?->isNotEmpty() ?? false)) {
            return true;
        }

        if ($this->resource->blueprints?->isNotEmpty() ?? false) {
            return true;
        }

        if (is_array($data?->get('ReputationGained')) && $data->get('ReputationGained') !== []) {
            return true;
        }

        return is_array($data?->get('ReputationLost')) && $data->get('ReputationLost') !== [];
    }

    private function computeHasCombatSection($data): bool
    {
        $summary = $data?->get('CombatSummary');

        if (is_array($summary) && isset($summary['Total'])) {
            return true;
        }

        if (is_array($data?->get('Combat')) && $data->get('Combat') !== []) {
            return true;
        }

        return is_array($data?->get('EntitySpawns')) && $data->get('EntitySpawns') !== [];
    }

    private function computeHasChain(): bool
    {
        if ($this->resource->relationLoaded('unlockGroups') && ($this->resource->unlockGroups?->isNotEmpty() ?? false)) {
            return true;
        }

        return $this->resource->relationLoaded('prerequisiteGroups') && ($this->resource->prerequisiteGroups?->isNotEmpty() ?? false);
    }

    private function extractFirstReputationAmount($data): ?int
    {
        $reputation = $data?->get('ReputationGained');

        if (! is_array($reputation) || $reputation === []) {
            return null;
        }

        $first = $reputation[0] ?? null;

        if (! is_array($first)) {
            return null;
        }

        return isset($first['Amount']) && is_numeric($first['Amount']) ? (int) $first['Amount'] : null;
    }

    private function mapFactionReputationLadder(): ?array
    {
        $ref = $this->resource->faction->reputationRef;

        if ($ref === null) {
            return null;
        }

        $scope = $ref->scope;

        if ($scope === null) {
            return null;
        }

        return [
            'scope_name' => $scope->scope_name,
            'standings' => $scope->relationLoaded('standings')
                ? $scope->standings->map(fn ($s): array => [
                    'name' => $s->name,
                    'display_name' => $s->display_name,
                    'min_reputation' => $s->min_reputation,
                ])->values()->all()
                : [],
        ];
    }
}
