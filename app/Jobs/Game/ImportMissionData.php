<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\Faction;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use App\Models\System\Language;
use App\Services\Game\SlugService;
use App\Services\Parser\SC\Labels;
use App\Support\Filters\MissionScopeMapping;
use App\Support\Formatting\FormatMissionText;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportMissionData implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var array<string, int>|null */
    private static ?array $factionLookup = null;

    /** @var array<string, int>|null */
    private static ?array $starmapLocationLookup = null;

    /** @var array<int, int>|null */
    private static ?array $starmapLocationDataLookup = null;

    /** @var array<string, int>|null */
    private static ?array $itemLookup = null;

    /** @var array<int, int>|null */
    private static ?array $itemDataLookup = null;

    /** @var array<string, int>|null */
    private static ?array $blueprintLookup = null;

    /** @var array<int, int>|null */
    private static ?array $blueprintDataLookup = null;

    /** @var array<string, int>|null */
    private static ?array $commodityLookup = null;

    /** @var array<int, string>|null */
    private static ?array $systemLookup = null;

    /** @var array<string, Mission>|null */
    private static ?array $missionCache = null;

    private static ?Labels $labels = null;

    public function __construct(
        private readonly int $gameVersionId,
        private readonly string $path,
        private readonly string $diskName = 'scunpacked',
    ) {}

    private function factionLookup(): array
    {
        if (self::$factionLookup === null) {
            self::$factionLookup = Faction::query()->pluck('id', 'uuid')->all();
        }

        return self::$factionLookup;
    }

    private function starmapLocationLookup(): array
    {
        if (self::$starmapLocationLookup === null) {
            self::$starmapLocationLookup = StarmapLocation::query()->pluck('id', 'uuid')->all();
        }

        return self::$starmapLocationLookup;
    }

    private function starmapLocationDataLookup(): array
    {
        if (self::$starmapLocationDataLookup === null) {
            self::$starmapLocationDataLookup = StarmapLocationData::query()
                ->where('game_version_id', $this->gameVersionId)
                ->pluck('id', 'starmap_location_id')
                ->all();
        }

        return self::$starmapLocationDataLookup;
    }

    private function itemLookup(): array
    {
        if (self::$itemLookup === null) {
            self::$itemLookup = Item::query()->pluck('id', 'uuid')->all();
        }

        return self::$itemLookup;
    }

    private function itemDataLookup(): array
    {
        if (self::$itemDataLookup === null) {
            self::$itemDataLookup = ItemData::query()
                ->where('game_version_id', $this->gameVersionId)
                ->pluck('id', 'item_id')
                ->all();
        }

        return self::$itemDataLookup;
    }

    private function blueprintLookup(): array
    {
        if (self::$blueprintLookup === null) {
            self::$blueprintLookup = Blueprint::query()->pluck('id', 'uuid')->all();
        }

        return self::$blueprintLookup;
    }

    private function blueprintDataLookup(): array
    {
        if (self::$blueprintDataLookup === null) {
            self::$blueprintDataLookup = BlueprintData::query()
                ->where('game_version_id', $this->gameVersionId)
                ->pluck('id', 'blueprint_id')
                ->all();
        }

        return self::$blueprintDataLookup;
    }

    private function commodityLookup(): array
    {
        if (self::$commodityLookup === null) {
            self::$commodityLookup = Commodity::query()
                ->pluck('id', 'uuid')
                ->mapWithKeys(fn ($id, $uuid) => [(string) $uuid => (int) $id])
                ->all();
        }

        return self::$commodityLookup;
    }

    private function systemLookup(): array
    {
        if (self::$systemLookup === null) {
            self::$systemLookup = StarmapLocationData::query()
                ->where('game_version_id', $this->gameVersionId)
                ->whereNotNull('system')
                ->pluck('system', 'starmap_location_id')
                ->all();
        }

        return self::$systemLookup;
    }

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $payload = $this->readPayload();

        $uuid = Arr::get($payload, 'UUID');
        $uuid = is_string($uuid) && trim($uuid) !== '' ? trim($uuid) : null;

        if ($uuid === null) {
            return;
        }

        $mission = Mission::query()->firstOrCreate(
            ['uuid' => $uuid],
            ['uuid' => $uuid]
        );

        if ($mission->slug === null) {
            $this->assignSlug($mission, $payload);
        }

        $factionId = $this->resolveFactionId($payload);

        $values = $this->mapMissionData($payload, $factionId);
        $values['mission_key'] = $this->computeMissionKey($payload);
        $updateColumns = $values
                |> array_keys(...)
                |> (static fn ($x) => array_diff($x, ['mission_id', 'game_version_id']))
                |> array_values(...);

        $now = now();
        $row = array_map(
            static function (mixed $value): mixed {
                return is_array($value) ? json_encode($value, JSON_THROW_ON_ERROR) : $value;
            },
            $values,
        ) + [
            'mission_id' => $mission->id,
            'game_version_id' => $this->gameVersionId,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        DB::table('game_mission_data')->upsert(
            [$row],
            ['mission_id', 'game_version_id'],
            [...$updateColumns, 'updated_at'],
        );

        $missionData = MissionData::query()
            ->where('mission_id', $mission->id)
            ->where('game_version_id', $this->gameVersionId)
            ->first();

        $this->syncStarmapLocations($missionData, $payload);
        $this->syncBlueprints($missionData, $payload);
        $this->syncCommodities($missionData, $payload);
        $this->syncItems($missionData, $payload);
        $this->syncRewardItems($missionData, $payload);
        $this->syncTranslations($mission, $payload);
    }

    private function readPayload(): array
    {
        $contents = Storage::disk($this->diskName)->get($this->path);

        if (! is_string($contents) || trim($contents) === '') {
            return [];
        }

        return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    }

    private function resolveFactionId(array $payload): ?int
    {
        $factionUuid = Arr::get($payload, 'Faction.UUID');
        $factionUuid = is_string($factionUuid) && trim($factionUuid) !== '' ? trim($factionUuid) : null;

        if ($factionUuid === null) {
            return null;
        }

        return $this->factionLookup()[$factionUuid] ?? null;
    }

    private function mapMissionData(array $payload, ?int $factionId): array
    {
        $fixedReward = $payload['FixedReward'] ?? null;
        $calculatedReward = $payload['CalculatedReward'] ?? null;
        $crimeStat = $payload['CrimeStat'] ?? [];

        $missionType = $this->trimOrNull(Arr::get($payload, 'MissionType.Name'));
        $generatorClass = $this->trimOrNull(Arr::get($payload, 'GeneratorClass'));
        $debugName = $this->trimOrNull(Arr::get($payload, 'DebugName'));

        $rewardMin = null;
        $rewardMax = null;
        $rewardCurrency = null;

        if (is_array($fixedReward)) {
            $rewardMin = is_numeric($fixedReward['Amount'] ?? null) ? (int) $fixedReward['Amount'] : null;
            $rewardMax = is_numeric($fixedReward['Max'] ?? null) ? (int) $fixedReward['Max'] : null;
            $currency = $fixedReward['Currency'] ?? null;
            $rewardCurrency = is_string($currency) && trim($currency) !== '' ? trim($currency) : null;
        }

        $combatSummary = $payload['CombatSummary'] ?? null;
        $combat = $payload['Combat'] ?? [];

        $hasCombat = false;
        $enemyCountMin = null;
        $enemyCountMax = null;

        if (is_array($combatSummary) && isset($combatSummary['Total'])) {
            $total = $combatSummary['Total'];
            $min = is_numeric($total['Min'] ?? null) ? (int) $total['Min'] : 0;
            $max = is_numeric($total['Max'] ?? null) ? (int) $total['Max'] : 0;

            if ($min > 0 || $max > 0) {
                $hasCombat = true;
                $enemyCountMin = $min;
                $enemyCountMax = $max;
            }
        }

        if (! $hasCombat && is_array($combat)) {
            foreach ($combat as $entry) {
                if (is_array($entry) && ($entry['Role'] ?? null) === 'enemy') {
                    $hasCombat = true;

                    break;
                }
            }
        }

        $hasDefendObjective = false;

        if (is_array($combat)) {
            foreach ($combat as $entry) {
                if (is_array($entry) && ($entry['Role'] ?? null) === 'defend_target') {
                    $hasDefendObjective = true;

                    break;
                }
            }
        }

        return [
            'debug_name' => $debugName,
            'mission_type' => $missionType,
            'mission_type_uuid' => $this->trimOrNull(Arr::get($payload, 'MissionType.UUID')),
            'mission_giver' => $this->trimOrNull(Arr::get($payload, 'MissionGiver')),
            'title' => $this->trimOrNull(Arr::get($payload, 'DisplayTitle')) ?? $this->trimOrNull(Arr::get($payload, 'Title')),
            'description' => $this->trimOrNull(Arr::get($payload, 'DisplayDescription')) ?? $this->trimOrNull(Arr::get($payload, 'Description')),
            'faction_id' => $factionId,
            'generator_class' => $generatorClass,
            'entry_type' => $this->trimOrNull(Arr::get($payload, 'entry_type')),
            'handler_type' => $this->trimOrNull(Arr::get($payload, 'Type')),
            'illegal' => (bool) ($payload['Illegal'] ?? false),
            'shareable' => (bool) ($payload['Shareable'] ?? false),
            'once_only' => (bool) ($payload['OnceOnly'] ?? false),
            'available_in_prison' => (bool) ($payload['AvailableInPrison'] ?? false),
            'not_for_release' => (bool) ($payload['NotForRelease'] ?? false),
            'work_in_progress' => (bool) ($payload['WorkInProgress'] ?? false),
            'calculated_reward' => is_bool($calculatedReward) ? $calculatedReward : false,
            'rank_index' => is_numeric($payload['RankIndex'] ?? null) ? (int) $payload['RankIndex'] : null,
            'min_crime_stat' => is_numeric($crimeStat['Min'] ?? null) ? (int) $crimeStat['Min'] : null,
            'max_crime_stat' => is_numeric($crimeStat['Max'] ?? null) ? (int) $crimeStat['Max'] : null,
            'time_to_complete_minutes' => is_numeric($payload['TimeToComplete'] ?? null) ? (float) $payload['TimeToComplete'] : null,
            'reward_min' => $rewardMin,
            'reward_max' => $rewardMax,
            'reward_currency' => $rewardCurrency,
            'star_systems' => $this->deriveStarSystems($payload),
            'reputation_scopes' => $this->deriveReputationScopes($payload),
            'has_combat' => $hasCombat,
            'has_defend_objective' => $hasDefendObjective,
            'enemy_count_min' => $enemyCountMin,
            'enemy_count_max' => $enemyCountMax,
            'reward_scope' => MissionScopeMapping::scopeForRow((object) [
                'mission_type' => $missionType,
                'generator_class' => $generatorClass,
                'debug_name' => $debugName,
            ]),
            'max_players_per_instance' => is_numeric($payload['MaxPlayersPerInstance'] ?? null) ? (int) $payload['MaxPlayersPerInstance'] : null,
            'reputation_amount' => $this->extractFirstReputationAmount($payload),
            'data' => $payload,
        ];
    }

    private function deriveReputationScopes(array $payload): array
    {
        $scopes = [];

        foreach ($payload['ReputationGained'] ?? [] as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $scope = $this->trimOrNull($entry['Scope'] ?? null);

            if ($scope !== null) {
                $scopes[] = $scope;
            }
        }

        return array_values(array_unique($scopes));
    }

    private function deriveStarSystems(array $payload): array
    {
        $locationUuids = [];

        foreach ($payload['LocationPools'] ?? [] as $pool) {
            if (! is_array($pool)) {
                continue;
            }

            foreach ($pool['ResolvedLocations'] ?? [] as $location) {
                if (is_array($location) && is_string($location['UUID'] ?? null)) {
                    $locationUuids[] = $location['UUID'];
                }
            }
        }

        foreach ($payload['AvailabilityLocations'] ?? [] as $availabilityLocation) {
            if (! is_array($availabilityLocation)) {
                continue;
            }

            foreach ($availabilityLocation['ResolvedLocations'] ?? [] as $location) {
                if (is_array($location) && is_string($location['UUID'] ?? null)) {
                    $locationUuids[] = $location['UUID'];
                }
            }
        }

        $locationUuids = array_values(array_unique($locationUuids));

        if ($locationUuids === []) {
            return [];
        }

        $systems = [];
        foreach ($locationUuids as $uuid) {
            $locationId = $this->starmapLocationLookup()[$uuid] ?? null;
            if ($locationId === null) {
                continue;
            }
            $system = $this->systemLookup()[$locationId] ?? null;
            if ($system !== null) {
                $systems[] = str_replace(' System', '', $system);
            }
        }

        return array_values(array_unique($systems));
    }

    private function syncStarmapLocations(MissionData $missionData, array $payload): void
    {
        $syncData = [];
        $purposeMap = [];

        foreach ($payload['LocationPools'] ?? [] as $pool) {
            if (! is_array($pool)) {
                continue;
            }

            $purpose = $this->trimOrNull($pool['Purpose'] ?? null);

            foreach ($pool['ResolvedLocations'] ?? [] as $location) {
                $uuid = $this->trimOrNull($location['UUID'] ?? null);

                if ($uuid !== null) {
                    $purposeMap[$uuid] = $purpose;
                }
            }
        }

        foreach ($payload['AvailabilityLocations'] ?? [] as $availabilityLocation) {
            if (! is_array($availabilityLocation)) {
                continue;
            }

            foreach ($availabilityLocation['ResolvedLocations'] ?? [] as $location) {
                $uuid = $this->trimOrNull($location['UUID'] ?? null);

                if ($uuid !== null) {
                    $purposeMap[$uuid] = $purposeMap[$uuid] ?? 'availability';
                }
            }
        }

        foreach ($purposeMap as $uuid => $purpose) {
            $locationId = $this->starmapLocationLookup()[$uuid] ?? null;

            if ($locationId === null) {
                continue;
            }

            $locationDataId = $this->starmapLocationDataLookup()[$locationId] ?? null;

            if ($locationDataId === null) {
                continue;
            }

            $syncData[$locationDataId] = ['purpose' => $purpose];
        }

        $missionData->starmapLocations()->sync($syncData);
    }

    /**
     * Derive the mission grouping key: md5 of sorted unique blueprint pool UUIDs.
     */
    private function computeMissionKey(array $payload): ?string
    {
        $blueprintPayloads = $payload['Blueprints'] ?? null;

        if ($blueprintPayloads === null && isset($payload['Blueprint']) && is_array($payload['Blueprint'])) {
            $blueprintPayloads = [$payload['Blueprint']];
        }

        if (! is_array($blueprintPayloads) || $blueprintPayloads === []) {
            return null;
        }

        $poolUuids = [];

        foreach ($blueprintPayloads as $pool) {
            if (! is_array($pool)) {
                continue;
            }

            $poolUuid = $this->trimOrNull($pool['PoolUUID'] ?? null);

            if ($poolUuid !== null) {
                $poolUuids[] = $poolUuid;
            }
        }

        $sortedPoolUuids = array_unique($poolUuids);
        sort($sortedPoolUuids);

        return $sortedPoolUuids !== [] ? md5(implode(',', $sortedPoolUuids)) : null;
    }

    private function syncBlueprints(MissionData $missionData, array $payload): void
    {
        // Support both new (Blueprints array) and legacy (Blueprint object) formats
        $blueprintPayloads = $payload['Blueprints'] ?? null;

        if ($blueprintPayloads === null && isset($payload['Blueprint']) && is_array($payload['Blueprint'])) {
            $blueprintPayloads = [$payload['Blueprint']];
        }

        if (! is_array($blueprintPayloads) || $blueprintPayloads === []) {
            $missionData->blueprints()->sync([]);

            return;
        }

        $pivots = [];

        foreach ($blueprintPayloads as $pool) {
            if (! is_array($pool)) {
                continue;
            }

            $poolUuid = $this->trimOrNull($pool['PoolUUID'] ?? null);
            $poolChance = isset($pool['Chance']) && is_numeric($pool['Chance'])
                ? (float) $pool['Chance']
                : null;

            foreach ($pool['PoolContents'] ?? [] as $content) {
                if (! is_array($content)) {
                    continue;
                }

                $blueprintUuid = $this->trimOrNull($content['BlueprintUUID'] ?? null);

                if ($blueprintUuid === null) {
                    continue;
                }

                $blueprintId = $this->blueprintLookup()[$blueprintUuid] ?? null;

                if ($blueprintId === null) {
                    continue;
                }

                $blueprintDataId = $this->blueprintDataLookup()[$blueprintId] ?? null;

                if ($blueprintDataId === null) {
                    continue;
                }

                $itemUuid = $this->trimOrNull($content['ItemUUID'] ?? null);
                $itemId = $itemUuid !== null ? ($this->itemLookup()[$itemUuid] ?? null) : null;
                $itemDataId = $itemId !== null ? ($this->itemDataLookup()[$itemId] ?? null) : null;

                if ($itemDataId === null) {
                    continue;
                }

                $pivots[$blueprintDataId.':'.$itemDataId.':'.$poolUuid] = [
                    'blueprint_data_id' => $blueprintDataId,
                    'pool_uuid' => $poolUuid,
                    'item_data_id' => $itemDataId,
                    'chance' => $poolChance,
                ];
            }
        }

        $missionData->blueprints()->detach();

        foreach ($pivots as $pivot) {
            $missionData->blueprints()->attach($pivot['blueprint_data_id'], Arr::except($pivot, 'blueprint_data_id'));
        }
    }

    private function syncCommodities(MissionData $missionData, array $payload): void
    {
        $haulingOrders = $payload['HaulingOrders'] ?? null;

        if (! is_array($haulingOrders)) {
            $missionData->commodities()->sync([]);

            return;
        }

        $commodityUuids = [];

        foreach ($haulingOrders as $order) {
            if (! is_array($order)) {
                continue;
            }

            $kind = $order['Kind'] ?? $order['ItemKind'] ?? null;

            if (is_string($kind) && $kind === 'Or') {
                foreach ($order['OrOptions'] ?? [] as $optionGroup) {
                    if (! is_array($optionGroup)) {
                        continue;
                    }

                    foreach ($optionGroup as $option) {
                        if (! is_array($option)) {
                            continue;
                        }

                        $optionKind = $option['Kind'] ?? null;

                        if ($optionKind === 'Resource') {
                            $uuid = $this->trimOrNull($option['UUID'] ?? $option['ItemUUID'] ?? null);

                            if ($uuid !== null) {
                                $commodityUuids[] = $uuid;
                            }
                        }
                    }
                }
            } elseif ($kind === 'Resource') {
                $uuid = $this->trimOrNull($order['UUID'] ?? $order['ItemUUID'] ?? null);

                if ($uuid !== null) {
                    $commodityUuids[] = $uuid;
                }
            }
        }

        $commodityUuids = array_values(array_unique($commodityUuids));

        if ($commodityUuids === []) {
            $missionData->commodities()->sync([]);

            return;
        }

        $commodityIds = [];
        foreach ($commodityUuids as $uuid) {
            $id = $this->commodityLookup()[$uuid] ?? null;
            if ($id !== null) {
                $commodityIds[] = $id;
            }
        }

        $missionData->commodities()->sync($commodityIds);
    }

    private function syncItems(MissionData $missionData, array $payload): void
    {
        $haulingOrders = $payload['HaulingOrders'] ?? null;

        if (! is_array($haulingOrders)) {
            $missionData->items()->sync([]);

            return;
        }

        $itemUuids = [];

        foreach ($haulingOrders as $order) {
            if (! is_array($order)) {
                continue;
            }

            $kind = $order['Kind'] ?? $order['ItemKind'] ?? null;

            if (is_string($kind) && $kind === 'Or') {
                foreach ($order['OrOptions'] ?? [] as $optionGroup) {
                    if (! is_array($optionGroup)) {
                        continue;
                    }

                    foreach ($optionGroup as $option) {
                        if (! is_array($option)) {
                            continue;
                        }

                        $optionKind = $option['Kind'] ?? null;

                        if ($optionKind === 'Entity') {
                            foreach ($option['Items'] ?? [] as $item) {
                                $uuid = $this->trimOrNull($item['UUID'] ?? $item['ItemUUID'] ?? null);

                                if ($uuid !== null) {
                                    $itemUuids[] = $uuid;
                                }
                            }
                        } elseif ($optionKind === 'MissionItem') {
                            $uuid = $this->trimOrNull($option['UUID'] ?? $option['ItemUUID'] ?? null);

                            if ($uuid !== null) {
                                $itemUuids[] = $uuid;
                            }
                        }
                    }
                }
            } elseif ($kind === 'Entity') {
                foreach ($order['Items'] ?? [] as $item) {
                    $uuid = $this->trimOrNull($item['UUID'] ?? $item['ItemUUID'] ?? null);

                    if ($uuid !== null) {
                        $itemUuids[] = $uuid;
                    }
                }
            } elseif ($kind === 'MissionItem') {
                $uuid = $this->trimOrNull($order['UUID'] ?? $order['ItemUUID'] ?? null);

                if ($uuid !== null) {
                    $itemUuids[] = $uuid;
                }
            }
        }

        $itemUuids = array_values(array_unique($itemUuids));

        if ($itemUuids === []) {
            $missionData->items()->sync([]);

            return;
        }

        $itemDataIds = [];
        foreach ($itemUuids as $uuid) {
            $itemId = $this->itemLookup()[$uuid] ?? null;
            if ($itemId !== null && isset($this->itemDataLookup()[$itemId])) {
                $itemDataIds[] = $this->itemDataLookup()[$itemId];
            }
        }

        $missionData->items()->sync($itemDataIds);
    }

    private function syncRewardItems(MissionData $missionData, array $payload): void
    {
        $items = $payload['Items'] ?? null;

        if (! is_array($items) || $items === []) {
            $missionData->rewardItems()->sync([]);

            return;
        }

        $itemUuids = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $uuid = $this->trimOrNull($item['UUID'] ?? null);

            if ($uuid !== null) {
                $itemUuids[] = $uuid;
            }
        }

        $itemUuids = array_values(array_unique($itemUuids));

        if ($itemUuids === []) {
            $missionData->rewardItems()->sync([]);

            return;
        }

        $syncData = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $uuid = $this->trimOrNull($item['UUID'] ?? null);

            if ($uuid === null) {
                continue;
            }

            $itemId = $this->itemLookup()[$uuid] ?? null;

            if ($itemId === null) {
                continue;
            }

            $itemDataId = $this->itemDataLookup()[$itemId] ?? null;

            if ($itemDataId === null) {
                continue;
            }

            $syncData[$itemDataId] = [
                'amount' => is_numeric($item['Amount'] ?? null) ? (int) $item['Amount'] : null,
                'send_to_home' => isset($item['SendToHome']) ? filter_var($item['SendToHome'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null,
            ];
        }

        $missionData->rewardItems()->sync($syncData);
    }

    private function extractFirstReputationAmount(array $payload): ?int
    {
        $reputation = $payload['ReputationGained'] ?? null;

        if (! is_array($reputation) || $reputation === []) {
            return null;
        }

        $first = $reputation[0] ?? null;

        if (! is_array($first)) {
            return null;
        }

        return isset($first['Amount']) && is_numeric($first['Amount']) ? (int) $first['Amount'] : null;
    }

    private function trimOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * Sync description translations from label keys onto the parent Mission model.
     */
    private function syncTranslations(Mission $mission, array $payload): void
    {
        $descriptionKey = $this->trimOrNull(Arr::get($payload, 'description_key'));

        if ($descriptionKey === null) {
            return;
        }

        $updated = false;

        $englishDescription = $this->trimOrNull(Arr::get($payload, 'DisplayDescription'))
            ?? $this->trimOrNull(Arr::get($payload, 'Description'));

        if ($englishDescription !== null) {
            $mission->setTranslation('translation', Language::ENGLISH, $englishDescription);
            $updated = true;
        }

        foreach (config('translations.locales', []) as $locale) {
            $translation = $this->getLabels()->getTranslation($locale, $descriptionKey);

            if ($translation !== null && $translation !== '') {
                $mission->setTranslation('translation', $locale, str_replace('\n', "\n", $translation));
                $updated = true;
            }
        }

        if ($updated) {
            $mission->save();
        }
    }

    private function getLabels(): Labels
    {
        if (self::$labels === null) {
            self::$labels = new Labels;
        }

        return self::$labels;
    }

    private function assignSlug(Mission $mission, array $payload): void
    {
        $title = $this->trimOrNull(Arr::get($payload, 'Title'));
        $debugName = $this->trimOrNull(Arr::get($payload, 'DebugName'));
        $formatted = FormatMissionText::format($title, $debugName);

        app(SlugService::class)->assignUniqueSlug(
            $mission,
            $formatted ?? '',
            $mission->uuid,
        );
    }
}
