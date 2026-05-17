<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Jobs\Game\Concerns\BuildsUexLinks;
use App\Jobs\Game\Concerns\FiltersUexVersions;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
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

class EnrichCommodityPrices implements ShouldQueue
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
     * @param  array<int, int>  $commodityIds  our commodity IDs to enrich
     */
    public function __construct(
        private readonly int $gameVersionId,
        private readonly array $commodityIds,
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

        $commodities = Commodity::query()
            ->whereIn('id', $this->commodityIds)
            ->whereNotNull('uex_prices')
            ->get()
            ->keyBy('id');

        $mapper = new TerminalLocationMapper($this->gameVersionId);
        $locationMapping = $mapper->mapping;

        $locationDataLookup = StarmapLocationData::query()
            ->join('game_starmap_locations', 'game_starmap_location_data.starmap_location_id', '=', 'game_starmap_locations.id')
            ->where('game_starmap_location_data.game_version_id', $this->gameVersionId)
            ->pluck('game_starmap_location_data.id', 'game_starmap_locations.uuid');

        $apiUrl = config('uexcorp.api_url');
        $updatedCount = 0;

        foreach ($this->commodityIds as $commodityId) {
            $commodity = $commodities->get($commodityId);

            if ($commodity === null) {
                continue;
            }

            $uexCommodityId = $this->extractUexCommodityId($commodity->uex_prices ?? []);

            if ($uexCommodityId === null) {
                continue;
            }

            $enriched = $this->enrichCommodity($apiUrl, $uexCommodityId, $commodity, $locationMapping, $mapper, $locationDataLookup, $versionPrefixMap);

            if ($enriched) {
                $updatedCount++;
            }

            usleep(self::THROTTLE_MICROSECONDS);
        }

        Log::info('UEX commodity prices enrichment chunk completed', [
            'count' => $updatedCount,
            'chunk_size' => count($this->commodityIds),
            'game_version_id' => $this->gameVersionId,
        ]);
    }

    private function extractUexCommodityId(array $prices): ?int
    {
        $first = $prices[0] ?? null;

        if ($first === null) {
            return null;
        }

        return $first['uex_commodity_id'] ?? null;
    }

    /**
     * @param  array<string, string>  $versionPrefixMap
     */
    private function enrichCommodity(
        string $apiUrl,
        int $uexCommodityId,
        Commodity $commodity,
        Collection $locationMapping,
        TerminalLocationMapper $mapper,
        Collection $locationDataLookup,
        array $versionPrefixMap,
    ): bool {
        $response = Http::timeout(30)->get("{$apiUrl}/commodities_prices", ['id_commodity' => $uexCommodityId]);

        if (! $response->successful()) {
            Log::warning('UEX per-commodity price API failed', [
                'uex_commodity_id' => $uexCommodityId,
                'status' => $response->status(),
            ]);

            return false;
        }

        $apiPrices = $response->json('data', []);

        if (! is_array($apiPrices) || $apiPrices === []) {
            return false;
        }

        $uexLink = self::buildCommodityLink($commodity->name);

        $enrichedPrices = collect($apiPrices)
            ->filter(fn (array $p): bool => $this->matchesKnownVersion($p['game_version'] ?? null, $versionPrefixMap))
            ->filter(fn (array $p): bool => ($p['price_buy'] ?? 0) > 0 || ($p['price_sell'] ?? 0) > 0)
            ->unique('id_terminal')
            ->map(function (array $p) use ($locationMapping, $mapper, $locationDataLookup, $versionPrefixMap, $uexLink, $uexCommodityId): array {
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
                    'price_buy_avg' => $p['price_buy_avg'] ?? null,
                    'price_sell_avg' => $p['price_sell_avg'] ?? null,
                    'scu_sell_stock' => $p['scu_sell_stock'] ?? null,
                    'scu_sell_stock_avg' => $p['scu_sell_stock_avg'] ?? null,
                    'container_sizes' => $p['container_sizes'] ?? null,
                    'status_buy' => $p['status_buy'] ?? null,
                    'status_sell' => $p['status_sell'] ?? null,
                    'game_version' => $this->resolveDbVersionCode($p['game_version'] ?? null, $versionPrefixMap),
                    'date_updated' => Carbon::createFromTimestamp((int) $p['date_modified'])->toIso8601String(),
                    'uex_link' => $uexLink,
                    'uex_commodity_id' => $uexCommodityId,
                ];
            })
            ->values()
            ->toArray();

        $commodity->uex_prices = $enrichedPrices;
        $commodity->save();

        return true;
    }

    public function failed(Throwable $exception): void
    {
        Log::error('UEX commodity prices enrichment job failed', [
            'game_version_id' => $this->gameVersionId,
            'chunk_size' => count($this->commodityIds),
            'message' => $exception->getMessage(),
        ]);
    }
}
