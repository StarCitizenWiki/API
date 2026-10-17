<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Jobs\Game\Concerns\BuildsUexLinks;
use App\Jobs\Game\Concerns\MapsUexPrices;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
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
use Illuminate\Support\Str;
use Throwable;

class ImportItemPrices implements ShouldQueue
{
    use Batchable;
    use BuildsUexLinks;
    use Dispatchable;
    use InteractsWithQueue;
    use MapsUexPrices;
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
                throw $exception;
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
        $allPrices = collect($apiData);

        $uuidPrices = $allPrices->filter(fn (array $p): bool => ($p['item_uuid'] ?? '') !== '');
        $emptyUuidPrices = $allPrices->filter(fn (array $p): bool => ($p['item_uuid'] ?? '') === '');

        $grouped = $uuidPrices->groupBy('item_uuid');

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

        $slugMatched = collect();

        if ($emptyUuidPrices->isNotEmpty()) {
            $nameOverrides = collect(config('uexcorp.item_name_to_uuid_overrides', []));

            $byIdItem = $emptyUuidPrices->groupBy('id_item');

            $slugs = $byIdItem->map(fn (Collection $prices): string => Str::slug($prices->first()['item_name'] ?? ''))
                ->filter()
                ->unique()
                ->values();

            $dbSlugToUuid = $slugs->isNotEmpty()
                ? Item::query()->whereIn('slug', $slugs->toArray())->pluck('uuid', 'slug')
                : collect();

            foreach ($byIdItem as $prices) {
                $itemName = $prices->first()['item_name'] ?? null;

                if ($nameOverrides->has($itemName)) {
                    $slugMatched->put($nameOverrides->get($itemName), $prices);

                    continue;
                }

                if ($itemName !== null) {
                    $slug = Str::slug($itemName);
                    $wikiUuid = $dbSlugToUuid->get($slug);

                    if ($wikiUuid !== null) {
                        $slugMatched->put($wikiUuid, $prices);
                    }
                }
            }
        }

        foreach ($slugMatched as $uuid => $prices) {
            $existing = $grouped->get($uuid, collect());
            $grouped->put($uuid, $existing->merge($prices));
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

        $locationDataLookup = $this->locationDataLookup();

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

            $pricesData = $this->mapTerminalPrices(
                $prices,
                $mapper,
                $locationDataLookup,
                ['price_buy', 'price_sell'],
                trailingFields: fn (array $p): array => ['uex_link' => self::buildItemLink($p['item_name'] ?? null)],
                gameVersion: $gameVersionCode,
            );

            $itemData->update([
                'uex_prices' => $pricesData,
            ]);

            $updatedCount++;
        }

        Log::info('UEX prices imported', [
            'count' => $updatedCount,
            'game_version_id' => $this->gameVersionId,
            'slug_matched_empty_uuid' => $slugMatched->count(),
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

        $locationDataLookup = $this->locationDataLookup();

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

            $purchasePrices = $this->mapTerminalPrices(
                $purchases,
                $mapper,
                $locationDataLookup,
                ['price_buy'],
                trailingFields: fn (array $p, int $terminalId): array => ['uex_link' => self::buildVehicleLink($terminalId, 'price_buy')],
                gameVersion: $gameVersionCode,
            );
            $rentalPrices = $this->mapTerminalPrices(
                $rentals,
                $mapper,
                $locationDataLookup,
                ['price_rent'],
                trailingFields: fn (array $p, int $terminalId): array => ['uex_link' => self::buildVehicleLink($terminalId, 'price_rent')],
                gameVersion: $gameVersionCode,
            );

            $vehicleData->update([
                'uex_purchase_prices' => $purchasePrices,
                'uex_rental_prices' => $rentalPrices,
            ]);

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

    public function failed(Throwable $exception): void
    {
        Log::error('UEX price import job failed', [
            'game_version_id' => $this->gameVersionId,
            'message' => $exception->getMessage(),
        ]);
    }
}
