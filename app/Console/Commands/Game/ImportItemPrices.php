<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Jobs\Game\EnrichItemPrices as EnrichItemPricesJob;
use App\Jobs\Game\EnrichVehiclePrices as EnrichVehiclePricesJob;
use App\Jobs\Game\ImportItemPrices as ImportItemPricesJob;
use App\Models\Game\GameVersion;
use App\Models\Game\ItemData;
use App\Models\Game\VehicleData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

class ImportItemPrices extends Command
{
    protected $signature = 'game:import-item-prices {--chunk=50 : Number of UUIDs per enrichment job}';

    protected $description = 'Import item & vehicle prices from UEX Corp API for the default game version';

    public function handle(): int
    {
        $gameVersion = GameVersion::query()
            ->where('is_default', true)
            ->first();

        if ($gameVersion === null) {
            $this->error('No default game version found.');

            return self::FAILURE;
        }

        $chunkSize = (int) $this->option('chunk');

        $this->info("Dispatching price import for version {$gameVersion->code}...");

        Bus::batch([new ImportItemPricesJob($gameVersion->id)])
            ->then(function () use ($gameVersion, $chunkSize): void {
                self::dispatchEnrichmentBatches($gameVersion, $chunkSize);
            })
            ->dispatch();

        $this->info('Import batch dispatched. Enrichment will follow after import completes.');

        return self::SUCCESS;
    }

    public static function dispatchEnrichmentBatches(GameVersion $gameVersion, int $chunkSize): void
    {
        self::dispatchItemEnrichment($gameVersion, $chunkSize);
        self::dispatchVehicleEnrichment($gameVersion, $chunkSize);
    }

    private static function dispatchItemEnrichment(GameVersion $gameVersion, int $chunkSize): void
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

        $chunks = collect($itemUuids)->chunk($chunkSize);

        $jobs = $chunks->map(
            fn ($chunk): EnrichItemPricesJob => new EnrichItemPricesJob($gameVersion->id, $chunk->values()->toArray()),
        )->all();

        Bus::batch($jobs)->allowFailures()->dispatch();
    }

    private static function dispatchVehicleEnrichment(GameVersion $gameVersion, int $chunkSize): void
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

        $wikiToUexMap = self::buildWikiToUexUuidMap();

        $chunks = collect($vehicleUuids)->chunk($chunkSize);

        $jobs = $chunks->map(
            fn ($chunk): EnrichVehiclePricesJob => new EnrichVehiclePricesJob($gameVersion->id, $chunk->values()->toArray(), $wikiToUexMap),
        )->all();

        Bus::batch($jobs)->allowFailures()->dispatch();
    }

    /**
     * @return array<string, string> wikiUUID => uexUUID
     */
    private static function buildWikiToUexUuidMap(): array
    {
        $apiUrl = config('uexcorp.api_url');

        $response = Http::timeout(60)->get("{$apiUrl}/vehicles");

        if (! $response->successful()) {
            return [];
        }

        $vehiclesList = collect($response->json('data', []));
        $uuidOverrides = collect(config('uexcorp.vehicle_uuid_overrides', []));
        $nameOverrides = collect(config('uexcorp.vehicle_name_to_uuid_overrides', []));

        $wikiToUex = [];

        foreach ($vehiclesList as $vehicle) {
            if (! is_array($vehicle) || ! array_key_exists('id', $vehicle)) {
                continue;
            }

            $uexUuid = $vehicle['uuid'] ?? null;
            $vehicleName = $vehicle['name'] ?? null;

            if ($nameOverrides->has($vehicleName)) {
                $wikiToUex[$nameOverrides->get($vehicleName)] = $uexUuid;

                continue;
            }

            if ($uexUuid === null || $uexUuid === '') {
                continue;
            }

            $wikiUuid = $uuidOverrides->get($uexUuid, $uexUuid);

            if ($wikiUuid !== $uexUuid) {
                $wikiToUex[$wikiUuid] = $uexUuid;
            }
        }

        return $wikiToUex;
    }
}
