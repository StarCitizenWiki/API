<?php

declare(strict_types=1);

namespace App\Jobs\Game;

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
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

    private const int THROTTLE_MICROSECONDS = 200_000;

    /**
     * @param  array<int, string>  $itemUuids
     */
    public function __construct(
        private readonly int $gameVersionId,
        private readonly array $itemUuids,
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
        $locationMapping = $mapper->getMapping();

        $locationDataLookup = StarmapLocationData::query()
            ->join('game_starmap_locations', 'game_starmap_location_data.starmap_location_id', '=', 'game_starmap_locations.id')
            ->where('game_starmap_location_data.game_version_id', $this->gameVersionId)
            ->pluck('game_starmap_location_data.id', 'game_starmap_locations.uuid');

        $updatedCount = 0;

        $reverseOverrides = collect(config('uexcorp.item_uuid_overrides', []))->flip();

        foreach ($this->itemUuids as $uuid) {
            $itemId = $items->get($uuid);

            if ($itemId === null) {
                continue;
            }

            $itemData = $itemDataCollection->get($itemId);

            if ($itemData === null) {
                continue;
            }

            $apiUuid = $reverseOverrides->get($uuid, $uuid);

            $enriched = $this->enrichItem($apiUuid, $itemData, $locationMapping, $mapper, $locationDataLookup, $versionPrefixMap);

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
     * @return array<string, string> apiVersionPrefix => dbVersionCode
     */
    private function buildVersionPrefixMap(string $currentVersionCode): array
    {
        $map = [];

        $map[$this->extractMajorMinor($currentVersionCode)] = $currentVersionCode;

        if ($this->previousVersionCode !== null) {
            $map[$this->extractMajorMinor($this->previousVersionCode)] = $this->previousVersionCode;
        }

        return $map;
    }

    private function extractMajorMinor(string $version): string
    {
        if (preg_match('/^(\d+\.\d+)/', $version, $matches)) {
            return $matches[1];
        }

        return $version;
    }

    /**
     * @param  array<string, string>  $versionPrefixMap
     */
    private function enrichItem(
        string $uuid,
        ItemData $itemData,
        Collection $locationMapping,
        TerminalLocationMapper $mapper,
        Collection $locationDataLookup,
        array $versionPrefixMap,
    ): bool {
        $apiUrl = config('uexcorp.api_url');

        $response = Http::timeout(30)->get("{$apiUrl}/items_prices", ['uuid' => $uuid]);

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
                    'terminal_code' => $p['terminal_code'] ?? $mapper->getTerminalCode($terminalId),
                    'terminal_name' => $p['terminal_name'],
                    'starmap_location_uuid' => $locationUuid,
                    'starmap_location_data_id' => $locationUuid !== null
                        ? $locationDataLookup->get($locationUuid)
                        : null,
                    'price_buy' => $p['price_buy'],
                    'price_sell' => $p['price_sell'],
                    'game_version' => $this->resolveDbVersionCode($p['game_version'] ?? null, $versionPrefixMap),
                    'date_updated' => Carbon::createFromTimestamp((int) $p['date_modified'])->toIso8601String(),
                ];
            })
            ->values()
            ->toArray();

        $itemData->uex_prices = $enrichedPrices;
        $itemData->save();

        return true;
    }

    /**
     * @param  array<string, string>  $versionPrefixMap
     */
    private function matchesKnownVersion(?string $apiVersion, array $versionPrefixMap): bool
    {
        if ($apiVersion === null) {
            return false;
        }

        return array_any($versionPrefixMap, fn($_, $prefix) => str_starts_with($apiVersion, $prefix));
    }

    /**
     * @param  array<string, string>  $versionPrefixMap
     */
    private function resolveDbVersionCode(?string $apiVersion, array $versionPrefixMap): ?string
    {
        if ($apiVersion === null) {
            return null;
        }

        return array_find($versionPrefixMap, fn($dbCode, $prefix) => str_starts_with($apiVersion, $prefix));
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
