<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Jobs\Game\Concerns\BuildsUexLinks;
use App\Jobs\Game\Concerns\FiltersUexVersions;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\StarmapLocationData;
use App\Support\UEXcorp\TerminalLocationMapper;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class EnrichItemPrices implements ShouldQueue
{
    use Batchable;
    use BuildsUexLinks;
    use Dispatchable;
    use FiltersUexVersions;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

    private const int THROTTLE_MICROSECONDS = 200_000;

    /**
     * @param  array<int, string>  $itemUuids
     * @param  array<string, string>  $uuidToUexUuidMap  itemUUID => uexUUID
     * @param  array<string, int>  $uuidToUexIdMap  itemUUID => uexId (for items with empty UUID in UEX)
     */
    public function __construct(
        private readonly int $gameVersionId,
        private readonly array $itemUuids,
        private readonly array $uuidToUexUuidMap = [],
        private readonly array $uuidToUexIdMap = [],
        private readonly ?string $previousVersionCode = null,
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $gameVersion = GameVersion::find($this->gameVersionId);

        if ($gameVersion === null) {
            return;
        }

        $versionPrefixMap = $this->buildVersionPrefixMap($gameVersion->code);

        $items = Item::query()
            ->whereIn('uuid', $this->itemUuids)
            ->pluck('id', 'uuid');

        $itemDataCollection = ItemData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->whereIn('item_id', $items->values())
            ->whereNotNull('uex_prices')
            ->get()
            ->keyBy('item_id');

        $mapper = new TerminalLocationMapper($this->gameVersionId);
        $locationMapping = $mapper->mapping;

        $locationDataLookup = StarmapLocationData::query()
            ->join('game_starmap_locations', 'game_starmap_location_data.starmap_location_id', '=', 'game_starmap_locations.id')
            ->where('game_starmap_location_data.game_version_id', $this->gameVersionId)
            ->pluck('game_starmap_location_data.id', 'game_starmap_locations.uuid');

        $uuidMap = collect($this->uuidToUexUuidMap);
        $idMap = collect($this->uuidToUexIdMap);
        $reverseOverrides = collect(config('uexcorp.item_uuid_overrides', []))->flip();

        $updatedCount = 0;

        foreach ($this->itemUuids as $uuid) {
            $itemId = $items->get($uuid);

            if ($itemId === null) {
                continue;
            }

            $itemData = $itemDataCollection->get($itemId);

            if ($itemData === null) {
                continue;
            }

            $apiUuid = $uuidMap->get($uuid) ?? $reverseOverrides->get($uuid, $uuid);

            if ($apiUuid === '' || ($apiUuid === $uuid && $idMap->has($uuid))) {
                $apiUuid = '';
            }

            $uexId = $idMap->get($uuid);

            if ($apiUuid === '' && $uexId === null) {
                continue;
            }

            $enriched = $this->enrichItem($apiUuid, $uexId, $itemData, $locationMapping, $mapper, $locationDataLookup, $versionPrefixMap);

            if ($enriched) {
                $updatedCount++;
            }

            usleep(self::THROTTLE_MICROSECONDS);
        }

        Log::info('UEX item prices enrichment chunk completed', [
            'count' => $updatedCount,
            'chunk_size' => count($this->itemUuids),
            'game_version_id' => $this->gameVersionId,
        ]);
    }

    /**
     * @param  array<string, string>  $versionPrefixMap
     */
    private function enrichItem(
        string $uuid,
        ?int $uexId,
        ItemData $itemData,
        Collection $locationMapping,
        TerminalLocationMapper $mapper,
        Collection $locationDataLookup,
        array $versionPrefixMap,
    ): bool {
        $apiUrl = config('uexcorp.api_url');

        $query = ($uuid !== '')
            ? ['uuid' => $uuid]
            : ['id_item' => $uexId];

        $response = Http::timeout(30)->get("{$apiUrl}/items_prices", $query);

        if (! $response->successful()) {
            Log::warning('UEX per-item price API failed', [
                'uuid' => $uuid,
                'status' => $response->status(),
            ]);

            return false;
        }

        $apiPrices = $response->json('data', []);

        if (! is_array($apiPrices) || $apiPrices === []) {
            return false;
        }

        $enrichedPrices = collect($apiPrices)
            ->filter(fn (array $p): bool => $this->matchesKnownVersion($p['game_version'] ?? null, $versionPrefixMap))
            ->unique('id_terminal')
            ->map(function (array $p) use ($locationMapping, $mapper, $locationDataLookup, $versionPrefixMap): array {
                $terminalId = (int) $p['id_terminal'];
                $locationUuid = $locationMapping->get($terminalId);

                return [
                    'terminal_id' => $terminalId,
                    'terminal_code' => $p['terminal_code'] ?? $mapper->terminalCodes()->get($terminalId),
                    'terminal_name' => $p['terminal_name'],
                    'starmap_location_uuid' => $locationUuid,
                    'starmap_location_data_id' => $locationUuid !== null
                        ? $locationDataLookup->get($locationUuid)
                        : null,
                    'price_buy' => $p['price_buy'],
                    'price_sell' => $p['price_sell'],
                    'game_version' => $this->resolveDbVersionCode($p['game_version'] ?? null, $versionPrefixMap),
                    'date_updated' => Carbon::createFromTimestamp((int) $p['date_modified'])->toIso8601String(),
                    'uex_link' => $this->buildItemLink($p['item_name'] ?? null),
                ];
            })
            ->values()
            ->toArray();

        $itemData->update([
            'uex_prices' => $enrichedPrices,
        ]);

        return true;
    }

    public function failed(Throwable $exception): void
    {
        Log::error('UEX item prices enrichment job failed', [
            'game_version_id' => $this->gameVersionId,
            'chunk_size' => count($this->itemUuids),
            'message' => $exception->getMessage(),
        ]);
    }
}
