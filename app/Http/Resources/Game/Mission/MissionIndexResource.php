<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Mission;

use App\Http\Resources\AbstractBaseResource;
use App\Models\Game\Faction;
use App\Support\Formatting\FormatMissionTitle;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'mission_index',
    title: 'Mission Summary',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'title', type: 'string', nullable: true),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'mission_giver', type: 'string', nullable: true),
        new OA\Property(property: 'debug_name', type: 'string', nullable: true),
        new OA\Property(
            property: 'faction',
            properties: [
                new OA\Property(property: 'name', type: 'string', nullable: true),
                new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
                new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(property: 'rank_index', type: 'integer', nullable: true),
        new OA\Property(property: 'illegal', type: 'boolean'),
        new OA\Property(property: 'legality_label', type: 'string'),
        new OA\Property(property: 'shareable', type: 'boolean'),
        new OA\Property(property: 'once_only', type: 'boolean'),
        new OA\Property(property: 'has_combat', type: 'boolean', nullable: true),
        new OA\Property(property: 'has_defend_objective', type: 'boolean', nullable: true),
        new OA\Property(property: 'enemy_count_min', type: 'integer', nullable: true),
        new OA\Property(property: 'enemy_count_max', type: 'integer', nullable: true),
        new OA\Property(property: 'reward_min', type: 'integer', nullable: true),
        new OA\Property(property: 'reward_max', type: 'integer', nullable: true),
        new OA\Property(property: 'reward_currency', type: 'string', nullable: true),
        new OA\Property(property: 'time_to_complete_minutes', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'star_systems', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
        new OA\Property(property: 'variant_count', type: 'integer', nullable: true),
        new OA\Property(
            property: 'variants',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'title', type: 'string', nullable: true),
                ],
                type: 'object'
            ),
            nullable: true
        ),
        new OA\Property(property: 'has_blueprints', type: 'boolean'),
        new OA\Property(property: 'blueprint_drop_chance', type: 'number', format: 'float', nullable: true),
        new OA\Property(
            property: 'blueprints',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mission_index_blueprint'),
            nullable: true
        ),
        new OA\Property(property: 'has_prerequisites', type: 'boolean'),
        new OA\Property(property: 'has_hauling', type: 'boolean'),
        new OA\Property(property: 'min_standing_name', type: 'string', nullable: true),
        new OA\Property(property: 'max_standing_name', type: 'string', nullable: true),
        new OA\Property(property: 'cost', type: 'integer', nullable: true),
        new OA\Property(property: 'min_crime_stat', type: 'integer', nullable: true),
        new OA\Property(property: 'max_crime_stat', type: 'integer', nullable: true),
        new OA\Property(property: 'available_in_prison', type: 'boolean'),
        new OA\Property(
            property: 'reputation_gained',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'faction', type: 'string', nullable: true),
                    new OA\Property(property: 'scope', type: 'string', nullable: true),
                    new OA\Property(property: 'amount', type: 'integer', nullable: true),
                ],
                type: 'object'
            ),
            nullable: true
        ),
        new OA\Property(property: 'max_players_per_instance', type: 'integer', nullable: true),
        new OA\Property(property: 'max_instances_per_player', type: 'integer', nullable: true),
        new OA\Property(property: 'cooldown_seconds', type: 'integer', nullable: true),
        new OA\Property(property: 'cooldown_label', type: 'string', nullable: true),
        new OA\Property(property: 'reaccept_after_abandoning', type: 'boolean'),
        new OA\Property(property: 'reaccept_after_failing', type: 'boolean'),
        new OA\Property(property: 'fail_if_became_criminal', type: 'boolean'),
        new OA\Property(
            property: 'hauling_summary',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'name', type: 'string', nullable: true),
                    new OA\Property(property: 'min_amount', type: 'integer', nullable: true),
                    new OA\Property(property: 'max_amount', type: 'integer', nullable: true),
                ],
                type: 'object'
            ),
            nullable: true
        ),
        new OA\Property(property: 'reward_scope', type: 'string', nullable: true),
        new OA\Property(property: 'reputation_amount', type: 'integer', nullable: true),
        new OA\Property(property: 'game_version', type: 'string', nullable: true),
        new OA\Property(property: 'link', type: 'string', format: 'uri'),
        new OA\Property(property: 'web_url', type: 'string', format: 'uri'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_index_blueprint',
    title: 'Mission Index Blueprint',
    properties: [
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
class MissionIndexResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return ['faction', 'blueprints'];
    }

    public function toArray(Request $request): array
    {
        $mission = $this->resource->mission;
        $data = $this->resource->data;
        $haulingOrders = $data?->get('HaulingOrders');
        $minStanding = $data?->get('MinStanding');
        $maxStanding = $data?->get('MaxStanding');
        $lifetime = $data?->get('Lifetime');
        $cooldown = $data?->get('Cooldown');

        if (isset($this->resource->grouped_star_systems)) {
            try {
                $this->resource->grouped_star_systems = json_decode($this->resource->grouped_star_systems, true, 512, JSON_THROW_ON_ERROR);
            } catch (Exception) {

            }
        }

        if (isset($this->resource->variant_uuids)) {
            try {
                $variants = json_decode($this->resource->variant_uuids, true, 512, JSON_THROW_ON_ERROR);
                $this->resource->variant_uuids = collect($variants)->map(fn (string $uuid): array => [
                    'uuid' => $uuid,
                    'link' => $this->urlWithVersion(
                        route('missions.show', ['mission' => $uuid]),
                        $request,
                    ),
                ])->values()->all();
            } catch (Exception) {
                $this->resource->variant_uuids = null;
            }
        }

        return [
            'uuid' => $mission?->uuid,
            'title' => FormatMissionTitle::format($this->resource->title, $this->resource->debug_name),
            'description' => $this->resource->description,
            'mission_giver' => $this->resource->mission_giver,
            'debug_name' => $this->resource->debug_name,
            'faction' => $this->resource->faction ? [
                'name' => $this->resource->faction->name,
                'uuid' => $this->resource->faction->uuid,
                'link' => $this->urlWithVersion(
                    route('factions.show', ['faction' => $this->resource->faction->uuid]),
                    $request,
                ),
            ] : null,
            'rank_index' => $this->resource->rank_index,
            'illegal' => $this->resource->illegal,
            'legality_label' => $this->resource->illegal ? 'Illegal' : 'Legal',
            'shareable' => $this->resource->shareable,
            'once_only' => $this->resource->once_only,
            'has_combat' => $this->resource->has_combat,
            'has_defend_objective' => $this->resource->has_defend_objective,
            'enemy_count_min' => $this->resource->enemy_count_min,
            'enemy_count_max' => $this->resource->enemy_count_max,
            'reward_min' => $this->resource->reward_min,
            'reward_max' => $this->resource->reward_max,
            'reward_currency' => $this->resource->reward_currency,
            'time_to_complete_minutes' => $this->resource->time_to_complete_minutes,
            'star_systems' => $this->resource->grouped_star_systems ?? $this->resource->star_systems,
            'variant_count' => $this->whenNotNull($this->resource->variant_count),
            'variants' => $this->whenNotNull($this->resource->variant_uuids),
            'has_blueprints' => $this->resource->blueprints->isNotEmpty(),
            'blueprint_drop_chance' => $this->resource->blueprint_drop_chance,
            'blueprints' => $this->when(
                $this->resource->relationLoaded('blueprints') && $this->resource->blueprints->isNotEmpty(),
                fn (): array => $this->mapBlueprints($request),
            ),
            'has_prerequisites' => ($this->resource->prerequisite_groups_count ?? 0) > 0,
            'has_hauling' => is_iterable($haulingOrders) && count($haulingOrders) > 0,
            'min_standing_name' => is_array($minStanding) ? ($minStanding['Name'] ?? null) : null,
            'max_standing_name' => is_array($maxStanding) ? ($maxStanding['Name'] ?? null) : null,
            'cost' => $data?->has('Cost') ? (int) $data->get('Cost') : null,
            'min_crime_stat' => $this->resource->min_crime_stat,
            'max_crime_stat' => $this->resource->max_crime_stat,
            'available_in_prison' => $this->resource->available_in_prison,
            'reputation_gained' => $this->mapReputationGained($data),
            'max_players_per_instance' => $data?->get('MaxPlayersPerInstance'),
            'max_instances_per_player' => is_array($lifetime) ? ($lifetime['MaxInstancesPerPlayer'] ?? null) : null,
            'cooldown_seconds' => is_array($cooldown) ? ($cooldown['PersonalSeconds'] ?? null) : null,
            'cooldown_label' => is_array($cooldown) && isset($cooldown['PersonalSeconds']) && is_numeric($cooldown['PersonalSeconds']) && $cooldown['PersonalSeconds'] > 0
                ? (string) CarbonInterval::seconds((int) $cooldown['PersonalSeconds'])->cascade()->forHumans()
                : null,
            'reaccept_after_abandoning' => (bool) ($data?->get('ReacceptAfterAbandoning') ?? false),
            'reaccept_after_failing' => (bool) ($data?->get('ReacceptAfterFailing') ?? false),
            'fail_if_became_criminal' => (bool) ($data?->get('FailIfBecameCriminal') ?? false),
            'hauling_summary' => $this->mapHaulingSummary($haulingOrders),
            'reward_scope' => $this->resource->reward_scope,
            'reputation_amount' => $this->extractFirstReputationAmount($data),
            'game_version' => $this->resource->gameVersion?->code,
            'link' => $this->urlWithVersion(
                route('missions.show', ['mission' => $mission?->uuid]),
                $request,
            ),
            'web_url' => $this->urlWithVersion(
                route('web.missions.show', ['mission' => $mission?->uuid]),
                $request,
            ),
        ];
    }

    private function mapBlueprints(Request $request): array
    {
        return $this->resource->blueprints->map(fn ($blueprintData): array => [
            'name' => $blueprintData->output_name,
            'uuid' => $blueprintData->output_item_uuid,
            'link' => $blueprintData->blueprint?->uuid !== null
                ? $this->urlWithVersion(
                    route('blueprints.show', ['blueprint' => $blueprintData->blueprint->uuid]),
                    $request,
                )
                : null,
        ])->values()->all();
    }

    private function mapReputationGained($data): ?array
    {
        $reputation = $data?->get('ReputationGained');

        if (! is_array($reputation) || $reputation === []) {
            return null;
        }

        $factionNames = $this->resolveFactionNames($reputation);

        return array_map(static fn (array $entry): array => [
            'faction' => (static fn (): ?string => (
                ($entry['Faction'] ?? null) !== null && ! str_contains($entry['Faction'], 'UNINITIALIZED')
                    ? $entry['Faction']
                    : ($factionNames[$entry['FactionUUID'] ?? ''] ?? $entry['Faction'] ?? null)
            ))(),
            'scope' => $entry['Scope'] ?? null,
            'amount' => isset($entry['Amount']) && is_numeric($entry['Amount']) ? (int) $entry['Amount'] : null,
        ], $reputation);
    }

    private static array $factionNameCache = [];

    public static function setFactionNameCache(array $cache): void
    {
        self::$factionNameCache = $cache;
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

        $cached = array_intersect_key(self::$factionNameCache, array_flip($uuids));

        if (count($cached) === count($uuids)) {
            return $cached;
        }

        return Faction::query()->whereIn('uuid', $uuids)->pluck('name', 'uuid')->all();
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

    private function mapHaulingSummary($haulingOrders): ?array
    {
        if (! is_array($haulingOrders) || $haulingOrders === []) {
            return null;
        }

        return array_map(static function (array $order): array {
            $minAmount = isset($order['MinAmount']) && is_numeric($order['MinAmount']) ? (int) $order['MinAmount'] : 0;
            $maxAmount = isset($order['MaxAmount']) && is_numeric($order['MaxAmount']) ? (int) $order['MaxAmount'] : 0;

            return [
                'name' => $order['Name'] ?? null,
                'min_amount' => min($minAmount, $maxAmount) ?: null,
                'max_amount' => max($minAmount, $maxAmount) ?: null,
            ];
        }, $haulingOrders);
    }
}
