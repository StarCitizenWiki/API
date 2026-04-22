<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\StarmapLocationData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
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

class ImportItemPrices implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    private const string API_URL = 'https://api.uexcorp.uk/2.0/items_prices_all';

    public function __construct(
        private readonly int $gameVersionId
    ) {}

    public function handle(): void
    {
        $gameVersion = GameVersion::find($this->gameVersionId);

        if ($gameVersion === null) {
            Log::error('Game version not found', ['game_version_id' => $this->gameVersionId]);

            return;
        }

        $response = Http::timeout(60)->get(self::API_URL);

        if (! $response->successful()) {
            Log::error('UEX API request failed', [
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
            Log::error('UEX API returned invalid data format', [
                'game_version_id' => $this->gameVersionId,
            ]);

            return;
        }

        $mapper = new TerminalLocationMapper($this->gameVersionId);
        $this->processPrices($data, $mapper, $gameVersion->code);
        $this->processVehiclePrices($mapper, $gameVersion->code);
    }

    private function processPrices(array $apiData, TerminalLocationMapper $mapper, string $gameVersionCode): void
    {
        $grouped = collect($apiData)->groupBy('item_uuid');

        $itemUuidOverrides = collect(config('uexcorp.item_uuid_overrides', []));

        if ($itemUuidOverrides->isNotEmpty()) {
            $remapped = collect();
            foreach ($grouped as $uuid => $prices) {
                $targetUuid = $itemUuidOverrides->get($uuid, $uuid);
                $existing = $remapped->get($targetUuid, collect());
                $remapped->put($targetUuid, $existing->merge($prices));
            }
            $grouped = $remapped;
        }

        $uuids = $grouped->keys()->filter()->unique()->toArray();

        if ($uuids === []) {
            Log::warning('UEX API returned no item UUIDs');

            return;
        }

        $items = Item::query()
            ->whereIn('uuid', $uuids)
            ->pluck('id', 'uuid');

        $missingUuids = collect($uuids)->diff($items->keys());
        if ($missingUuids->isNotEmpty()) {
            Log::warning('UEX items not found in database', [
                'count' => $missingUuids->count(),
                'uuids' => $missingUuids->take(10)->toArray(),
            ]);
        }

        $itemDataCollection = ItemData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->whereIn('item_id', $items->values())
            ->get()
            ->keyBy('item_id');

        $locationMapping = $mapper->getMapping();

        $locationDataLookup = StarmapLocationData::query()
            ->join('game_starmap_locations', 'game_starmap_location_data.starmap_location_id', '=', 'game_starmap_locations.id')
            ->where('game_starmap_location_data.game_version_id', $this->gameVersionId)
            ->pluck('game_starmap_location_data.id', 'game_starmap_locations.uuid');

        $updatedCount = 0;

        foreach ($grouped as $uuid => $prices) {
            $itemId = $items->get($uuid);

            if ($itemId === null) {
                continue;
            }

            $itemData = $itemDataCollection->get($itemId);

            if ($itemData === null) {
                Log::debug('ItemData not found for item', [
                    'item_id' => $itemId,
                    'game_version_id' => $this->gameVersionId,
                ]);

                continue;
            }

            $pricesData = collect($prices)
                ->unique('id_terminal')
                ->map(function (array $p) use ($locationMapping, $mapper, $locationDataLookup, $gameVersionCode): array {
                    $terminalId = (int) $p['id_terminal'];
                    $locationUuid = $locationMapping->get($terminalId);

                    return [
                        'terminal_id' => $terminalId,
                        'terminal_code' => $mapper->getTerminalCode($terminalId),
                        'terminal_name' => $p['terminal_name'],
                        'starmap_location_uuid' => $locationUuid,
                        'starmap_location_data_id' => $locationUuid !== null
                            ? $locationDataLookup->get($locationUuid)
                            : null,
                        'price_buy' => $p['price_buy'],
                        'price_sell' => $p['price_sell'],
                        'game_version' => $gameVersionCode,
                        'date_updated' => Carbon::createFromTimestamp((int) $p['date_modified'])->toIso8601String(),
                    ];
                })
                ->values()
                ->toArray();

            $itemData->uex_prices = $pricesData;
            $itemData->save();

            $updatedCount++;
        }

        Log::info('UEX prices imported', [
            'count' => $updatedCount,
            'game_version_id' => $this->gameVersionId,
        ]);
    }

    private function processVehiclePrices(TerminalLocationMapper $mapper, string $gameVersionCode): void
    {
        $apiUrl = config('uexcorp.api_url');

        $vehiclesResponse = Http::timeout(60)->get("{$apiUrl}/vehicles");

        if (! $vehiclesResponse->successful()) {
            Log::error('UEX vehicles API request failed', [
                'status' => $vehiclesResponse->status(),
                'game_version_id' => $this->gameVersionId,
            ]);

            return;
        }

        $vehiclesList = collect($vehiclesResponse->json('data', []));

        if ($vehiclesList->isEmpty()) {
            Log::warning('UEX vehicles API returned empty data');

            return;
        }

        $uuidOverrides = collect(config('uexcorp.vehicle_uuid_overrides', []));
        $nameOverrides = collect(config('uexcorp.vehicle_name_to_uuid_overrides', []));

        $idVehicleToUuid = $this->buildVehicleIdMapping($vehiclesList, $uuidOverrides, $nameOverrides);

        $purchasePrices = $this->fetchBulkPrices("{$apiUrl}/vehicles_purchases_prices_all", 'purchase');
        $rentalPrices = $this->fetchBulkPrices("{$apiUrl}/vehicles_rentals_prices_all", 'rental');

        $locationMapping = $mapper->getMapping();

        $locationDataLookup = StarmapLocationData::query()
            ->join('game_starmap_locations', 'game_starmap_location_data.starmap_location_id', '=', 'game_starmap_locations.id')
            ->where('game_starmap_location_data.game_version_id', $this->gameVersionId)
            ->pluck('game_starmap_location_data.id', 'game_starmap_locations.uuid');

        $vehicleUuids = $idVehicleToUuid->values()->unique()->filter()->values()->toArray();

        $vehicleIds = Vehicle::query()
            ->whereIn('uuid', $vehicleUuids)
            ->pluck('id', 'uuid');

        $vehicleDataCollection = VehicleData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->whereIn('vehicle_id', $vehicleIds->values())
            ->get()
            ->keyBy('vehicle_id');

        $vehicleIdToVehicleDataId = collect();
        foreach ($vehicleIds as $uuid => $vehicleId) {
            $vehicleData = $vehicleDataCollection->get($vehicleId);
            if ($vehicleData !== null) {
                $vehicleIdToVehicleDataId->put($uuid, $vehicleData);
            }
        }

        $purchaseGrouped = $purchasePrices->groupBy('id_vehicle');
        $rentalGrouped = $rentalPrices->groupBy('id_vehicle');

        $updatedCount = 0;

        foreach ($idVehicleToUuid as $idVehicle => $wikiUuid) {
            if ($wikiUuid === null) {
                continue;
            }

            $vehicleData = $vehicleIdToVehicleDataId->get($wikiUuid);

            if ($vehicleData === null) {
                continue;
            }

            $purchases = $purchaseGrouped->get($idVehicle, collect());
            $rentals = $rentalGrouped->get($idVehicle, collect());

            $vehicleData->uex_purchase_prices = $this->mapVehiclePrices($purchases, $locationMapping, $mapper, $locationDataLookup, 'price_buy', $gameVersionCode);
            $vehicleData->uex_rental_prices = $this->mapVehiclePrices($rentals, $locationMapping, $mapper, $locationDataLookup, 'price_rent', $gameVersionCode);
            $vehicleData->save();

            $updatedCount++;
        }

        Log::info('UEX vehicle prices imported', [
            'count' => $updatedCount,
            'game_version_id' => $this->gameVersionId,
        ]);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $vehiclesList
     * @param  Collection<string, string>  $uuidOverrides
     * @param  Collection<string, string>  $nameOverrides
     * @return Collection<int, string> id_vehicle => wiki_uuid
     */
    private function buildVehicleIdMapping(Collection $vehiclesList, Collection $uuidOverrides, Collection $nameOverrides): Collection
    {
        $mapping = collect();

        foreach ($vehiclesList as $vehicle) {
            if (! is_array($vehicle) || ! array_key_exists('id', $vehicle)) {
                continue;
            }

            $idVehicle = (int) $vehicle['id'];
            $uexUuid = $vehicle['uuid'] ?? null;
            $vehicleName = $vehicle['name'] ?? null;

            if ($nameOverrides->has($vehicleName)) {
                $mapping->put($idVehicle, $nameOverrides->get($vehicleName));

                continue;
            }

            if ($uexUuid === null || $uexUuid === '') {
                $mapping->put($idVehicle, null);

                continue;
            }

            $mapping->put($idVehicle, $uuidOverrides->get($uexUuid, $uexUuid));
        }

        return $mapping;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function fetchBulkPrices(string $url, string $type): Collection
    {
        $response = Http::timeout(60)->get($url);

        if (! $response->successful()) {
            Log::error("UEX vehicle {$type} prices API failed", [
                'status' => $response->status(),
                'game_version_id' => $this->gameVersionId,
            ]);

            return collect();
        }

        return collect($response->json('data', []));
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $prices
     * @param  Collection<int, string>  $locationMapping
     * @param  Collection<int, int|null>  $locationDataLookup
     */
    private function mapVehiclePrices(
        Collection $prices,
        Collection $locationMapping,
        TerminalLocationMapper $mapper,
        Collection $locationDataLookup,
        string $priceField,
        string $gameVersionCode,
    ): array {
        return $prices
            ->unique('id_terminal')
            ->map(function (array $p) use ($locationMapping, $mapper, $locationDataLookup, $priceField, $gameVersionCode): array {
                $terminalId = (int) $p['id_terminal'];
                $locationUuid = $locationMapping->get($terminalId);

                return [
                    'terminal_id' => $terminalId,
                    'terminal_code' => $mapper->getTerminalCode($terminalId),
                    'terminal_name' => $p['terminal_name'],
                    'starmap_location_uuid' => $locationUuid,
                    'starmap_location_data_id' => $locationUuid !== null
                        ? $locationDataLookup->get($locationUuid)
                        : null,
                    $priceField => $p[$priceField],
                    'game_version' => $gameVersionCode,
                    'date_updated' => Carbon::createFromTimestamp((int) $p['date_modified'])->toIso8601String(),
                ];
            })
            ->values()
            ->toArray();
    }

    public function failed(Throwable $exception): void
    {
        Log::error('UEX price import job failed', [
            'game_version_id' => $this->gameVersionId,
            'message' => $exception->getMessage(),
        ]);
    }
}
