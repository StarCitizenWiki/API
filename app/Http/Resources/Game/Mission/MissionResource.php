<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Mission;

use App\Http\Resources\AbstractBaseResource;
use App\Models\Game\Faction;
use App\Services\TagItemResolverService;
use App\Support\Formatting\FormatMissionText;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'mission',
    title: 'Mission',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'title', type: 'string', nullable: true),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(
            property: 'description_html',
            description: 'HTML-rendered mission description. Escapes raw text, preserves EM4 styling, and renders exact mission token placeholders as tooltip spans.',
            type: 'string',
            nullable: true,
        ),
        new OA\Property(
            property: 'description_variants',
            description: 'Rendered HTML variants when the raw description is a single mission token with multiple possible values.',
            type: 'array',
            items: new OA\Items(type: 'string'),
            nullable: true,
        ),
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
        new OA\Property(property: 'not_for_release', type: 'boolean'),
        new OA\Property(property: 'work_in_progress', type: 'boolean'),
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
        new OA\Property(property: 'max_players_per_instance', type: 'integer'),
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
            description: 'Resolved mission token values. Keys are token identifiers (e.g. "Location|Address", "Danger", "Contractor"). Values are arrays of possible resolved strings. Keys preserve original case and pipe syntax exactly.',
            type: 'object',
            nullable: true,
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string'),
            ),
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
        new OA\Property(property: 'reputation_amount', type: 'integer'),
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
        new OA\Property(property: 'has_blueprints', type: 'boolean'),
        new OA\Property(property: 'released', description: 'Whether this mission is released (not marked as not_for_release or work_in_progress).', type: 'boolean'),
        new OA\Property(property: 'link', type: 'string', format: 'uri'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_blueprints',
    title: 'Mission Blueprints',
    description: 'Array of blueprint pools',
    type: 'array',
    items: new OA\Items(ref: '#/components/schemas/mission_blueprint_pool')
)]
#[OA\Schema(
    schema: 'mission_blueprint_pool',
    title: 'Mission Blueprint Pool',
    properties: [
        new OA\Property(property: 'drop_chance', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'drop_chance_percent', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'pool_uuid', type: 'string', nullable: true),
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
        new OA\Property(property: 'web_url', type: 'string', format: 'uri', nullable: true),
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
    public function toArray(Request $request): array
    {
        $mission = $this->resource->mission;
        $data = $this->resource->data;

        $this->setCanonicalResource(
            'mission',
            $mission?->uuid ?? '',
            $mission?->slug,
            $this->urlWithVersion(route('missions.show', ['mission' => $mission?->uuid]), $request),
            $this->urlWithVersion(route('web.missions.show', ['mission' => $mission?->slug ?? $mission?->uuid]), $request),
            $this->resource->gameVersion?->code,
        );

        $makeApiUrl = fn (string $route, array $params, Request $req): string => $this->urlWithVersion(route($route, $params), $req);
        $makeWebUrl = fn (string $route, array $params, Request $req): string => $this->urlWithVersion(route($route, $params), $req);

        $haulingResource = new MissionHaulingResource(
            null,
            $makeApiUrl,
            $makeWebUrl,
            app(TagItemResolverService::class),
            $this->resource->gameVersion?->id,
        );
        $chainResource = new MissionChainResource(null, $makeApiUrl, $makeWebUrl);
        $locationResource = new MissionLocationResource(null, $makeApiUrl, $makeWebUrl);
        $tokens = MissionDataBlockResource::mapMissionTokens(Arr::get($data, 'MissionTokens'));

        return [
            'uuid' => $mission?->uuid,
            'title' => FormatMissionText::format($this->resource->title, $this->resource->debug_name),
            'description' => $this->resource->description,
            'description_html' => FormatMissionText::description($this->resource->description, $tokens),
            'description_variants' => FormatMissionText::descriptionVariants($this->resource->description, $tokens),
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
            'not_for_release' => $this->resource->not_for_release,
            'work_in_progress' => $this->resource->work_in_progress,
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
            'cooldown' => ($c = Arr::get($data, 'Cooldown')) !== null ? (new MissionCooldownResource($c))->toArray($request) : null,
            'lifetime' => ($l = Arr::get($data, 'Lifetime')) !== null ? (new MissionLifetimeResource($l))->toArray($request) : null,
            'reaccept_after_failing' => $this->parseNullableBool(Arr::get($data, 'ReacceptAfterFailing')),
            'reaccept_after_abandoning' => $this->parseNullableBool(Arr::get($data, 'ReacceptAfterAbandoning')),
            'blueprints' => $this->mapBlueprints($request),
            'reward_items' => $this->when(
                $this->resource->relationLoaded('rewardItems'),
                fn (): ?array => $this->mapRewardItemsFromRelation($request),
            ),
            'combat' => (new MissionCombatResource($data))->toArray($request),
            'completion_tags' => $chainResource->mapCompletionTags($data, $request),
            'reputation_gained' => $this->mapReputation(Arr::get($data, 'ReputationGained')),
            'reputation_lost' => $this->mapReputation(Arr::get($data, 'ReputationLost')),
            'hauling_orders' => $haulingResource->mapHaulingOrders($data, $request),
            'cost' => Arr::has($data, 'Cost') && Arr::get($data, 'Cost') !== null ? (int) Arr::get($data, 'Cost') : null,
            'max_players_per_instance' => $this->resource->max_players_per_instance,
            'fail_if_became_criminal' => $this->parseNullableBool(Arr::get($data, 'FailIfBecameCriminal')),
            'min_standing' => MissionDataBlockResource::mapStanding(Arr::get($data, 'MinStanding')),
            'max_standing' => MissionDataBlockResource::mapStanding(Arr::get($data, 'MaxStanding')),
            'mission_tokens' => $tokens,
            'deadline' => MissionDataBlockResource::mapDeadline(Arr::get($data, 'Deadline')),
            'broker_reputation_prerequisites' => MissionDataBlockResource::mapBrokerReputationPrerequisites(Arr::get($data, 'BrokerReputationPrerequisites')),
            'entity_spawns' => MissionDataBlockResource::mapEntitySpawns(Arr::get($data, 'EntitySpawns')),
            'hidden_in_mobiglas' => $this->parseNullableBool(Arr::get($data, 'HiddenInMobiglas')),
            'notify_on_available' => $this->parseNullableBool(Arr::get($data, 'NotifyOnAvailable')),
            'reward_scope' => $this->resource->reward_scope,
            'reputation_amount' => $this->resource->reputation_amount,
            'game_version' => $this->resource->gameVersion?->code,
            'starmap_locations' => $this->when(
                $this->resource->relationLoaded('starmapLocations'),
                fn (): array => $locationResource->mapStarmapLocations($this->resource->starmapLocations, $request),
            ),
            'prerequisite_groups' => $this->when(
                $this->resource->relationLoaded('prerequisiteGroups'),
                fn (): array => $chainResource->mapPrerequisiteGroups($this->resource->prerequisiteGroups, $request),
            ),
            'unlock_groups' => $this->when(
                $this->resource->relationLoaded('unlockGroups'),
                fn (): array => $chainResource->mapUnlockGroups($this->resource->unlockGroups, $request),
            ),

            'merged_locations' => $this->when(
                $this->resource->relationLoaded('starmapLocations'),
                fn (): array => $locationResource->mapMergedLocations($this->resource->starmapLocations, $request),
            ),
            'has_rewards' => $this->computeHasRewards($data),
            'has_combat_section' => $this->computeHasCombatSection($data),
            'has_locations' => $this->resource->relationLoaded('starmapLocations')
                && ($this->resource->starmapLocations?->isNotEmpty() ?? false),
            'has_chain' => $this->computeHasChain(),
            'has_blueprints' => $this->resource->blueprints->isNotEmpty(),
            'released' => ! $this->resource->not_for_release && ! $this->resource->work_in_progress,
            'link' => $this->urlWithVersion(
                route('missions.show', ['mission' => $mission?->uuid]),
                $request,
            ),
            'web_url' => $this->urlWithVersion(
                route('web.missions.show', ['mission' => $mission?->slug ?? $mission?->uuid]),
                $request,
            ),
        ];
    }

    private function parseNullableBool(mixed $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    private function mapBlueprints(Request $request): ?array
    {
        $blueprints = $this->resource->blueprints;

        if ($blueprints === null || $blueprints->isEmpty()) {
            return null;
        }

        // Group pivot rows by pool_uuid
        $grouped = $blueprints->groupBy(fn ($blueprintData) => $blueprintData->pivot->pool_uuid ?? '_null_');

        return $grouped->map(function ($poolItems, $poolUuid) use ($request): array {
            $dropChance = $poolItems->first()?->pivot?->chance;

            return [
                'drop_chance' => $dropChance,
                'drop_chance_percent' => is_numeric($dropChance)
                    ? round((float) $dropChance * 100, 1)
                    : null,
                'pool_uuid' => $poolUuid !== '_null_' ? $poolUuid : null,
                'items' => $poolItems->map(fn ($blueprintData): array => [
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
                        ? route('web.blueprints.show', ['blueprint' => $blueprintData->blueprint->slug ?? $blueprintData->blueprint->uuid])
                        : null,
                ])->values()->all(),
            ];
        })->values()->all();
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
            'web_url' => $itemData->item?->uuid !== null
                ? $this->urlWithVersion(route('web.items.show', ['item' => $itemData->item->slug ?? $itemData->item->uuid]), $request)
                : null,
        ])->values()->all();
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

    private function computeHasRewards($data): bool
    {
        if ($this->hasPositiveRewardAmount($this->resource->reward_min) || $this->hasPositiveRewardAmount($this->resource->reward_max)) {
            return true;
        }

        $fixedReward = Arr::get($data, 'FixedReward');
        if (is_array($fixedReward) && (
            $this->hasPositiveRewardAmount($fixedReward['Amount'] ?? null)
            || $this->hasPositiveRewardAmount($fixedReward['Max'] ?? null)
        )) {
            return true;
        }

        if ($this->resource->calculated_reward || Arr::get($data, 'CalculatedReward') === true) {
            return true;
        }

        if ($this->resource->relationLoaded('rewardItems') && ($this->resource->rewardItems?->isNotEmpty() ?? false)) {
            return true;
        }

        if ($this->resource->blueprints?->isNotEmpty() ?? false) {
            return true;
        }

        if (is_array(Arr::get($data, 'ReputationGained')) && Arr::get($data, 'ReputationGained') !== []) {
            return true;
        }

        return is_array(Arr::get($data, 'ReputationLost')) && Arr::get($data, 'ReputationLost') !== [];
    }

    private function hasPositiveRewardAmount(mixed $value): bool
    {
        return is_numeric($value) && (int) $value > 0;
    }

    private function computeHasCombatSection($data): bool
    {
        $summary = Arr::get($data, 'CombatSummary');

        if (is_array($summary) && isset($summary['Total'])) {
            return true;
        }

        if (is_array(Arr::get($data, 'Combat')) && Arr::get($data, 'Combat') !== []) {
            return true;
        }

        return is_array(Arr::get($data, 'EntitySpawns')) && Arr::get($data, 'EntitySpawns') !== [];
    }

    private function computeHasChain(): bool
    {
        if ($this->resource->relationLoaded('unlockGroups') && ($this->resource->unlockGroups?->isNotEmpty() ?? false)) {
            return true;
        }

        return $this->resource->relationLoaded('prerequisiteGroups') && ($this->resource->prerequisiteGroups?->isNotEmpty() ?? false);
    }

    private function mapFactionReputationLadder(): ?array
    {
        $ref = $this->resource->faction->reputationRef;

        if ($ref === null) {
            return null;
        }

        $scope = $ref->factionScope;

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
