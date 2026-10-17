<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Jobs\Game\Concerns\BuildsUexLinks;
use App\Jobs\Game\Concerns\FiltersUexVersions;
use App\Jobs\Game\Concerns\MapsUexPrices;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Support\UEXcorp\TerminalLocationMapper;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportCommodityPrices implements ShouldQueue
{
    use Batchable;
    use BuildsUexLinks;
    use Dispatchable;
    use FiltersUexVersions;
    use InteractsWithQueue;
    use MapsUexPrices;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    private const string API_URL = 'https://api.uexcorp.uk/2.0/commodities_prices_all';

    public function __construct(
        private readonly int $gameVersionId,
        private readonly ?string $previousVersionCode = null,
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $gameVersion = GameVersion::find($this->gameVersionId);

        if ($gameVersion === null) {
            Log::error('Game version not found', ['game_version_id' => $this->gameVersionId]);

            return;
        }

        $response = Http::timeout(60)->get(self::API_URL);

        if (! $response->successful()) {
            Log::error('UEX commodity prices API request failed', [
                'status' => $response->status(),
                'game_version_id' => $this->gameVersionId,
            ]);

            $exception = $response->toException();
            if ($exception !== null) {
                throw $exception;
            }

            return;
        }

        $data = $response->json('data', []);

        if (! is_array($data)) {
            Log::error('UEX commodity prices API returned invalid data format', [
                'game_version_id' => $this->gameVersionId,
            ]);

            return;
        }

        $this->processPrices($data, $gameVersion->code, $this->buildVersionPrefixMap($gameVersion->code));
    }

    /**
     * @param  array<string, string>  $versionPrefixMap
     */
    private function processPrices(array $apiData, string $gameVersionCode, array $versionPrefixMap): void
    {
        $filtered = collect($apiData)
            ->filter(fn (array $p): bool => ($p['game_version'] ?? null) === null || $this->matchesKnownVersion($p['game_version'], $versionPrefixMap))
            ->filter(fn (array $p): bool => ($p['price_buy'] ?? 0) > 0 || ($p['price_sell'] ?? 0) > 0);

        $commodityNames = $filtered->pluck('commodity_name')->unique()->filter()->values()->toArray();

        $commodities = Commodity::query()
            ->whereIn('name', $commodityNames)
            ->pluck('id', 'name');

        $mapper = new TerminalLocationMapper($this->gameVersionId);

        $locationDataLookup = $this->locationDataLookup();

        $grouped = $filtered->groupBy('commodity_name');

        $updatedCount = 0;

        foreach ($grouped as $commodityName => $prices) {
            $commodityId = $commodities->get($commodityName);

            if ($commodityId === null) {
                continue;
            }

            $uexLink = self::buildCommodityLink($commodityName);

            $pricesData = $this->mapTerminalPrices(
                $prices,
                $mapper,
                $locationDataLookup,
                ['price_buy', 'price_sell'],
                trailingFields: fn (array $p): array => ['uex_link' => $uexLink, 'uex_commodity_id' => (int) $p['id_commodity']],
                gameVersion: fn (array $p): ?string => $this->resolveDbVersionCode($p['game_version'] ?? null, $versionPrefixMap) ?? $gameVersionCode,
                optionalFields: ['scu_sell_stock', 'container_sizes', 'status_buy', 'status_sell'],
            );

            Commodity::where('id', $commodityId)->update(['uex_prices' => $pricesData]);
            $updatedCount++;
        }

        Log::info('UEX commodity prices imported', [
            'count' => $updatedCount,
            'game_version_id' => $this->gameVersionId,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('UEX commodity price import job failed', [
            'game_version_id' => $this->gameVersionId,
            'message' => $exception->getMessage(),
        ]);
    }
}
