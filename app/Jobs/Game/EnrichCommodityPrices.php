<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Jobs\Game\Concerns\BuildsUexLinks;
use App\Jobs\Game\Concerns\FiltersUexVersions;
use App\Jobs\Game\Concerns\MapsUexPrices;
use App\Jobs\Game\Concerns\UexPriceJobDefaults;
use App\Models\Game\Commodity\Commodity;
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

class EnrichCommodityPrices implements ShouldQueue
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
     * @param  array<int, int>  $commodityIds  our commodity IDs to enrich
     */
    public function __construct(
        private readonly int $gameVersionId,
        private readonly array $commodityIds,
        private readonly ?string $previousVersionCode = null,
    ) {}

    public function handle(): void
    {
        $context = $this->resolveVersionContext();

        if ($context === null) {
            return;
        }

        [$gameVersion, $versionPrefixMap] = $context;

        $commodities = Commodity::query()
            ->whereIn('id', $this->commodityIds)
            ->whereNotNull('uex_prices')
            ->get()
            ->keyBy('id');

        $mapper = new TerminalLocationMapper($this->gameVersionId);

        $locationDataLookup = $this->locationDataLookup();

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

            $enriched = $this->enrichCommodity($apiUrl, $uexCommodityId, $commodity, $mapper, $locationDataLookup, $versionPrefixMap);

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

        $enrichedPrices = $this->mapTerminalPrices(
            collect($apiPrices)
                ->filter(fn (array $p): bool => $this->matchesKnownVersion($p['game_version'] ?? null, $versionPrefixMap))
                ->filter(fn (array $p): bool => ($p['price_buy'] ?? 0) > 0 || ($p['price_sell'] ?? 0) > 0),
            $mapper,
            $locationDataLookup,
            ['price_buy', 'price_sell'],
            trailingFields: fn (array $p): array => ['uex_link' => $uexLink, 'uex_commodity_id' => $uexCommodityId],
            gameVersion: fn (array $p): ?string => $this->resolveDbVersionCode($p['game_version'] ?? null, $versionPrefixMap),
            optionalFields: ['price_buy_avg', 'price_sell_avg', 'scu_sell_stock', 'scu_sell_stock_avg', 'container_sizes', 'status_buy', 'status_sell'],
            rowsCarryTerminalCode: true,
        );

        if ($enrichedPrices === []) {
            return false;
        }

        $commodity->update([
            'uex_prices' => $enrichedPrices,
        ]);

        return true;
    }

    protected function failedLogMessage(): string
    {
        return 'UEX commodity prices enrichment job failed';
    }

    /**
     * @return array<string, mixed>
     */
    protected function failedLogContext(): array
    {
        return ['chunk_size' => count($this->commodityIds)];
    }
}
