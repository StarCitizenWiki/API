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
                $this->fail($exception);
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
        $locationMapping = $mapper->mapping;

        $locationDataLookup = StarmapLocationData::query()
            ->join('game_starmap_locations', 'game_starmap_location_data.starmap_location_id', '=', 'game_starmap_locations.id')
            ->where('game_starmap_location_data.game_version_id', $this->gameVersionId)
            ->pluck('game_starmap_location_data.id', 'game_starmap_locations.uuid');

        $grouped = $filtered->groupBy('commodity_name');

        $updatedCount = 0;

        foreach ($grouped as $commodityName => $prices) {
            $commodityId = $commodities->get($commodityName);

            if ($commodityId === null) {
                continue;
            }

            $uexLink = self::buildCommodityLink($commodityName);

            $pricesData = $prices
                ->unique('id_terminal')
                ->map(function (array $p) use ($locationMapping, $mapper, $locationDataLookup, $gameVersionCode, $versionPrefixMap, $uexLink): array {
                    $terminalId = (int) $p['id_terminal'];
                    $locationUuid = $locationMapping->get($terminalId);

                    return [
                        'terminal_id' => $terminalId,
                        'terminal_code' => $mapper->terminalCodes()->get($terminalId),
                        'terminal_name' => $p['terminal_name'],
                        'starmap_location_uuid' => $locationUuid,
                        'starmap_location_data_id' => $locationUuid !== null
                            ? $locationDataLookup->get($locationUuid)
                            : null,
                        'price_buy' => $p['price_buy'],
                        'price_sell' => $p['price_sell'],
                        'scu_sell_stock' => $p['scu_sell_stock'] ?? null,
                        'container_sizes' => $p['container_sizes'] ?? null,
                        'status_buy' => $p['status_buy'] ?? null,
                        'status_sell' => $p['status_sell'] ?? null,
                        'game_version' => $this->resolveDbVersionCode($p['game_version'] ?? null, $versionPrefixMap) ?? $gameVersionCode,
                        'date_updated' => Carbon::createFromTimestamp((int) $p['date_modified'])->toIso8601String(),
                        'uex_link' => $uexLink,
                        'uex_commodity_id' => (int) $p['id_commodity'],
                    ];
                })
                ->values()
                ->toArray();

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
