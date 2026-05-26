<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Jobs\Game\EnrichCommodityPrices as EnrichCommodityPricesJob;
use App\Jobs\Game\EnrichItemPrices as EnrichItemPricesJob;
use App\Jobs\Game\EnrichVehiclePrices as EnrichVehiclePricesJob;
use App\Jobs\Game\ImportCommodityPrices as ImportCommodityPricesJob;
use App\Jobs\Game\ImportItemPrices as ImportItemPricesJob;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\VehicleData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportItemPrices extends Command
{
    protected $signature = 'game:import-item-prices {--chunk=50 : Number of UUIDs per enrichment job}';

    protected $description = 'Import item, vehicle & commodity prices from UEX Corp API for the default game version';

    public function handle(): int
    {
        $gameVersion = GameVersion::query()
            ->where('is_default', true)
            ->first();

        if ($gameVersion === null) {
            $this->error('No default game version found.');

            return self::FAILURE;
        }

        $previousVersion = $gameVersion->findPreviousVersionFamily();
        $previousVersionCode = $previousVersion?->code;

        $chunkSize = (int) $this->option('chunk');

        $this->info("Dispatching price import for version {$gameVersion->code}...");

        if ($previousVersion !== null) {
            $this->info("Including previous version {$previousVersion->code} for enrichment.");
        }

        Bus::batch([
            new ImportItemPricesJob($gameVersion->id),
            new ImportCommodityPricesJob($gameVersion->id, $previousVersionCode),
        ])
            ->then(function () use ($gameVersion, $chunkSize, $previousVersionCode): void {
                self::dispatchEnrichmentBatches($gameVersion, $chunkSize, $previousVersionCode);
            })
            ->dispatch();

        $this->info('Import batch dispatched. Enrichment will follow after import completes.');

        return self::SUCCESS;
    }

    public static function dispatchEnrichmentBatches(GameVersion $gameVersion, int $chunkSize, ?string $previousVersionCode = null): void
    {
        self::dispatchItemEnrichment($gameVersion, $chunkSize, $previousVersionCode);
        self::dispatchVehicleEnrichment($gameVersion, $chunkSize, $previousVersionCode);
        self::dispatchCommodityEnrichment($gameVersion, $chunkSize, $previousVersionCode);
    }

    private static function dispatchItemEnrichment(GameVersion $gameVersion, int $chunkSize, ?string $previousVersionCode): void
    {
        $itemUuids = ItemData::query()
            ->where('game_version_id', $gameVersion->id)
            ->whereNotNull('uex_prices')
            ->join('game_items', 'game_item_data.item_id', '=', 'game_items.id')
            ->pluck('game_items.uuid')
            ->unique()
            ->values()
            ->toArray();

        if ($itemUuids === []) {
            return;
        }

        $maps = self::buildItemUuidToUexMaps();

        $chunks = collect($itemUuids)->chunk($chunkSize);

        $jobs = $chunks->map(
            fn ($chunk): EnrichItemPricesJob => new EnrichItemPricesJob($gameVersion->id, $chunk->values()->toArray(), $maps['uuidToUexUuid'], $maps['uuidToUexId'], $previousVersionCode),
        )->all();

        Bus::batch($jobs)->allowFailures()->dispatch();
    }

    private static function dispatchVehicleEnrichment(GameVersion $gameVersion, int $chunkSize, ?string $previousVersionCode): void
    {
        $vehicleUuids = VehicleData::query()
            ->where('game_version_id', $gameVersion->id)
            ->where(fn ($q) => $q->whereNotNull('uex_purchase_prices')->orWhereNotNull('uex_rental_prices'))
            ->join('game_vehicles', 'game_vehicle_data.vehicle_id', '=', 'game_vehicles.id')
            ->pluck('game_vehicles.uuid')
            ->unique()
            ->values()
            ->toArray();

        if ($vehicleUuids === []) {
            return;
        }

        $maps = self::buildUuidToUexMaps();

        $chunks = collect($vehicleUuids)->chunk($chunkSize);

        $jobs = $chunks->map(
            fn ($chunk): EnrichVehiclePricesJob => new EnrichVehiclePricesJob($gameVersion->id, $chunk->values()->toArray(), $maps['uuidToUexUuid'], $maps['uuidToUexId'], $previousVersionCode),
        )->all();

        Bus::batch($jobs)->allowFailures()->dispatch();
    }

    private static function dispatchCommodityEnrichment(GameVersion $gameVersion, int $chunkSize, ?string $previousVersionCode): void
    {
        $commodityIds = Commodity::query()
            ->whereNotNull('uex_prices')
            ->pluck('id')
            ->values()
            ->toArray();

        if ($commodityIds === []) {
            return;
        }

        $chunks = collect($commodityIds)->chunk($chunkSize);

        $jobs = $chunks->map(
            fn ($chunk): EnrichCommodityPricesJob => new EnrichCommodityPricesJob($gameVersion->id, $chunk->values()->toArray(), $previousVersionCode),
        )->all();

        Bus::batch($jobs)->allowFailures()->dispatch();
    }

    /**
     * @return array{uuidToUexUuid: array<string, string>, uuidToUexId: array<string, int>}
     */
    private static function buildUuidToUexMaps(): array
    {
        $apiUrl = config('uexcorp.api_url');

        $response = Http::timeout(60)->get("{$apiUrl}/vehicles");

        if (! $response->successful()) {
            Log::warning('UEX vehicles API call failed during map building', [
                'status' => $response->status(),
            ]);

            return ['uuidToUexUuid' => [], 'uuidToUexId' => []];
        }

        $vehiclesList = collect($response->json('data', []));
        $uuidOverrides = collect(config('uexcorp.vehicle_uuid_overrides', []));
        $nameOverrides = collect(config('uexcorp.vehicle_name_to_uuid_overrides', []));

        $uuidToUexUuid = [];
        $uuidToUexId = [];

        foreach ($vehiclesList as $vehicle) {
            if (! is_array($vehicle) || ! array_key_exists('id', $vehicle)) {
                continue;
            }

            $uexUuid = $vehicle['uuid'] ?? null;
            $uexId = (int) $vehicle['id'];
            $vehicleName = $vehicle['name'] ?? null;

            if ($nameOverrides->has($vehicleName)) {
                $wikiUuid = $nameOverrides->get($vehicleName);

                if ($uexUuid !== null && $uexUuid !== '') {
                    $uuidToUexUuid[$wikiUuid] = $uexUuid;
                } else {
                    $uuidToUexId[$wikiUuid] = $uexId;
                }

                continue;
            }

            if ($uexUuid === null || $uexUuid === '') {
                continue;
            }

            $wikiUuid = $uuidOverrides->get($uexUuid, $uexUuid);

            if ($wikiUuid !== $uexUuid) {
                $uuidToUexUuid[$wikiUuid] = $uexUuid;
            }
        }

        return ['uuidToUexUuid' => $uuidToUexUuid, 'uuidToUexId' => $uuidToUexId];
    }

    /**
     * @return array{uuidToUexUuid: array<string, string>, uuidToUexId: array<string, int>}
     */
    private static function buildItemUuidToUexMaps(): array
    {
        $apiUrl = config('uexcorp.api_url');

        $response = Http::timeout(120)->get("{$apiUrl}/items_prices_all");

        if (! $response->successful()) {
            return ['uuidToUexUuid' => [], 'uuidToUexId' => []];
        }

        $itemsList = collect($response->json('data', []));
        $nameOverrides = collect(config('uexcorp.item_name_to_uuid_overrides', []));

        // Build slug => uuid lookup from DB items
        $dbItemSlugs = Item::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->pluck('uuid', 'slug');

        $uuidToUexUuid = [];
        $uuidToUexId = [];

        foreach ($itemsList as $item) {
            if (! is_array($item) || ! array_key_exists('id', $item)) {
                continue;
            }

            $uexUuid = $item['uuid'] ?? null;
            $uexId = (int) $item['id'];
            $itemName = $item['name'] ?? null;

            // Check name overrides first
            if ($nameOverrides->has($itemName)) {
                $wikiUuid = $nameOverrides->get($itemName);

                if ($uexUuid !== null && $uexUuid !== '') {
                    $uuidToUexUuid[$wikiUuid] = $uexUuid;
                } else {
                    $uuidToUexId[$wikiUuid] = $uexId;
                }

                continue;
            }

            // Auto-match by slug
            if ($itemName !== null) {
                $slug = Str::slug($itemName);
                $wikiUuid = $dbItemSlugs->get($slug);

                if ($wikiUuid !== null) {

                    if ($uexUuid !== null && $uexUuid !== '') {
                        if ($wikiUuid !== $uexUuid) {
                            $uuidToUexUuid[$wikiUuid] = $uexUuid;
                        }
                    } else {
                        $uuidToUexId[$wikiUuid] = $uexId;
                    }
                }
            }
        }

        return ['uuidToUexUuid' => $uuidToUexUuid, 'uuidToUexId' => $uuidToUexId];
    }
}
