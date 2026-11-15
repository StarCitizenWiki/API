<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Jobs\Game\Concerns\BuildsUexLinks;
use App\Jobs\Game\Concerns\FiltersUexVersions;
use App\Jobs\Game\Concerns\MapsUexPrices;
use App\Jobs\Game\Concerns\UexPriceJobDefaults;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Support\UEXcorp\TerminalLocationMapper;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EnrichItemPrices implements ShouldQueue
{
    use Batchable;
    use BuildsUexLinks;
    use Dispatchable;
    use FiltersUexVersions;
    use InteractsWithQueue;
    use MapsUexPrices;
    use Queueable;
    use SerializesModels;
    use UexPriceJobDefaults;

    public int $tries = 3;

    public int $timeout = 600;

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
        $context = $this->resolveVersionContext();

        if ($context === null) {
            return;
        }

        [$gameVersion, $versionPrefixMap] = $context;

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

        $locationDataLookup = $this->locationDataLookup();

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

            [$apiUuid, $uexId] = $this->resolveUexApiId($uuid, $uuidMap, $idMap, $reverseOverrides);

            if ($apiUuid === '' && $uexId === null) {
                continue;
            }

            $enriched = $this->enrichItem($apiUuid, $uexId, $itemData, $mapper, $locationDataLookup, $versionPrefixMap);

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

        $eligible = collect($apiPrices)->filter(fn (array $p): bool => ($p['game_version'] ?? null) === null || $this->matchesKnownVersion($p['game_version'], $versionPrefixMap));

        if ($eligible->isEmpty()) {
            // The response only carries rows for unknown game versions; writing the empty result would erase prices bulk just imported.
            Log::warning('UEX item prices enrichment rejected every row', [
                'uuid' => $uuid,
                'uex_id' => $uexId,
            ]);

            return true;
        }

        $enrichedPrices = $this->mapTerminalPrices(
            $eligible,
            $mapper,
            $locationDataLookup,
            ['price_buy', 'price_sell'],
            trailingFields: fn (array $p): array => ['uex_link' => $this->buildItemLink($p['item_name'] ?? null)],
            gameVersion: fn (array $p): ?string => $this->resolveDbVersionCode($p['game_version'] ?? null, $versionPrefixMap) ?? reset($versionPrefixMap),
            rowsCarryTerminalCode: true,
        );

        // Count heuristic: a smaller enrichment result never overwrites stored prices; ties prefer the fresher enrichment rows.
        if (count($enrichedPrices) >= count($itemData->uex_prices ?? [])) {
            $itemData->update([
                'uex_prices' => $enrichedPrices,
            ]);
        }

        return true;
    }

    protected function failedLogMessage(): string
    {
        return 'UEX item prices enrichment job failed';
    }

    /**
     * @return array<string, mixed>
     */
    protected function failedLogContext(): array
    {
        return ['chunk_size' => count($this->itemUuids)];
    }
}
