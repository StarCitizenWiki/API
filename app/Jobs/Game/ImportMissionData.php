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
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class ImportMissionData implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $gameVersionId,
        private readonly string $path,
    ) {}

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

        $factionId = $this->resolveFactionId($payload);

        $missionData = MissionData::query()->updateOrCreate(
            [
                'mission_id' => $mission->id,
                'game_version_id' => $this->gameVersionId,
            ],
            $this->mapMissionData($payload, $factionId)
        );

        $this->syncStarmapLocations($missionData, $payload);
        $this->syncBlueprints($missionData, $payload);
        $this->syncCommodities($missionData, $payload);
        $this->syncItems($missionData, $payload);
    }

    private function readPayload(): array
    {
        $contents = Storage::disk('scunpacked')->get($this->path);

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

        return Faction::query()
            ->where('uuid', $factionUuid)
            ->value('id');
    }

    private function mapMissionData(array $payload, ?int $factionId): array
    {
        $fixedReward = $payload['FixedReward'] ?? null;
        $calculatedReward = $payload['CalculatedReward'] ?? null;
        $crimeStat = $payload['CrimeStat'] ?? [];

        $rewardMin = null;
        $rewardMax = null;
        $rewardCurrency = null;

        if (is_array($fixedReward)) {
            $rewardMin = is_numeric($fixedReward['Amount'] ?? null) ? (int) $fixedReward['Amount'] : null;
            $rewardMax = is_numeric($fixedReward['Max'] ?? null) ? (int) $fixedReward['Max'] : null;
            $currency = $fixedReward['Currency'] ?? null;
            $rewardCurrency = is_string($currency) && trim($currency) !== '' ? trim($currency) : null;
        }

        return [
            'debug_name' => $this->trimOrNull(Arr::get($payload, 'DebugName')),
            'mission_type' => $this->trimOrNull(Arr::get($payload, 'MissionType.Name')),
            'mission_type_uuid' => $this->trimOrNull(Arr::get($payload, 'MissionType.UUID')),
            'mission_giver' => $this->trimOrNull(Arr::get($payload, 'MissionGiver')),
            'title' => $this->trimOrNull(Arr::get($payload, 'Title')),
            'description' => $this->trimOrNull(Arr::get($payload, 'Description')),
            'faction_id' => $factionId,
            'generator_class' => $this->trimOrNull(Arr::get($payload, 'GeneratorClass')),
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
            'data' => $payload,
        ];
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

        return StarmapLocationData::query()
            ->join('game_starmap_locations', 'game_starmap_location_data.starmap_location_id', '=', 'game_starmap_locations.id')
            ->where('game_starmap_location_data.game_version_id', $this->gameVersionId)
            ->whereIn('game_starmap_locations.uuid', $locationUuids)
            ->whereNotNull('game_starmap_location_data.system')
            ->pluck('game_starmap_location_data.system')
            ->map(static fn (string $system): string => str_replace(' System', '', $system))
            ->unique()
            ->values()
            ->all();
    }

    private function syncStarmapLocations(MissionData $missionData, array $payload): void
    {
        $syncData = [];

        foreach ($payload['AvailabilityLocations'] ?? [] as $availabilityLocation) {
            if (! is_array($availabilityLocation)) {
                continue;
            }

            foreach ($availabilityLocation['ResolvedLocations'] ?? [] as $location) {
                $locationDataId = $this->resolveStarmapLocationDataId($location['UUID'] ?? null);

                if ($locationDataId === null) {
                    continue;
                }

                $syncData[$locationDataId] = [];
            }
        }

        $missionData->starmapLocations()->sync($syncData);
    }

    private function resolveStarmapLocationDataId(mixed $uuid): ?int
    {
        $uuid = $this->trimOrNull($uuid);

        if ($uuid === null) {
            return null;
        }

        $location = StarmapLocation::query()->where('uuid', $uuid)->first();

        if ($location === null) {
            return null;
        }

        return StarmapLocationData::query()
            ->where('starmap_location_id', $location->id)
            ->where('game_version_id', $this->gameVersionId)
            ->value('id');
    }

    private function syncBlueprints(MissionData $missionData, array $payload): void
    {
        $blueprintPayload = $payload['Blueprint'] ?? null;

        if (! is_array($blueprintPayload)) {
            $missionData->blueprints()->sync([]);

            return;
        }

        $chance = isset($blueprintPayload['Chance']) && is_numeric($blueprintPayload['Chance'])
            ? (float) $blueprintPayload['Chance']
            : null;
        $poolUuid = $this->trimOrNull($blueprintPayload['PoolUUID'] ?? null);

        $itemUuids = [];

        foreach ($blueprintPayload['PoolContents'] ?? [] as $content) {
            if (! is_array($content)) {
                continue;
            }

            $itemUuid = $this->trimOrNull($content['ItemUUID'] ?? null);

            if ($itemUuid !== null) {
                $itemUuids[] = $itemUuid;
            }
        }

        $itemIdLookup = Item::query()->whereIn('uuid', $itemUuids)->pluck('id', 'uuid');

        $itemDataLookup = ItemData::query()
            ->whereIn('item_id', $itemIdLookup->values())
            ->where('game_version_id', $this->gameVersionId)
            ->pluck('id', 'item_id');

        $pivots = [];

        foreach ($blueprintPayload['PoolContents'] ?? [] as $content) {
            if (! is_array($content)) {
                continue;
            }

            $blueprintUuid = $this->trimOrNull($content['BlueprintUUID'] ?? null);

            if ($blueprintUuid === null) {
                continue;
            }

            $blueprint = Blueprint::query()->where('uuid', $blueprintUuid)->first();

            if ($blueprint === null) {
                continue;
            }

            $blueprintDataId = BlueprintData::query()
                ->where('blueprint_id', $blueprint->id)
                ->where('game_version_id', $this->gameVersionId)
                ->value('id');

            if ($blueprintDataId === null) {
                continue;
            }

            $itemUuid = $this->trimOrNull($content['ItemUUID'] ?? null);
            $itemId = $itemUuid !== null ? $itemIdLookup->get($itemUuid) : null;
            $itemDataId = $itemId !== null ? $itemDataLookup->get($itemId) : null;

            $pivots[$blueprintDataId.':'.($itemDataId ?? '')] = [
                'blueprint_data_id' => $blueprintDataId,
                'chance' => $chance,
                'pool_uuid' => $poolUuid,
                'item_data_id' => $itemDataId,
            ];
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

        $commodityIds = Commodity::query()
            ->whereIn('uuid', $commodityUuids)
            ->pluck('id')
            ->all();

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
            }
        }

        $itemUuids = array_values(array_unique($itemUuids));

        if ($itemUuids === []) {
            $missionData->items()->sync([]);

            return;
        }

        $itemIds = Item::query()
            ->whereIn('uuid', $itemUuids)
            ->pluck('id', 'uuid');

        $itemDataIds = ItemData::query()
            ->whereIn('item_id', $itemIds->values())
            ->where('game_version_id', $this->gameVersionId)
            ->pluck('id')
            ->all();

        $missionData->items()->sync($itemDataIds);
    }

    private function trimOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
