<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Jobs\Game\Concerns\BuildsUexLinks;
use App\Jobs\Game\Concerns\FiltersUexVersions;
use App\Models\Game\GameVersion;
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

class EnrichVehiclePrices implements ShouldQueue
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
     * @param  array<int, string>  $vehicleUuids  wiki UUIDs
     * @param  array<string, string>  $uuidToUexUuidMap  vehicleUUID => uexUUID
     * @param  array<string, int>  $uuidToUexIdMap  vehicleUUID => uexId (for vehicles with empty UUID in UEX)
     */
    public function __construct(
        private readonly int $gameVersionId,
        private readonly array $vehicleUuids,
        private readonly array $uuidToUexUuidMap = [],
        private readonly array $uuidToUexIdMap = [],
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

        $uuidMap = collect($this->uuidToUexUuidMap);
        $idMap = collect($this->uuidToUexIdMap);

        $vehicles = Vehicle::query()
            ->whereIn('uuid', $this->vehicleUuids)
            ->pluck('id', 'uuid');

        $vehicleDataCollection = VehicleData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->whereIn('vehicle_id', $vehicles->values())
            ->where(fn ($q) => $q->whereNotNull('uex_purchase_prices')->orWhereNotNull('uex_rental_prices'))
            ->get()
            ->keyBy('vehicle_id');

        $mapper = new TerminalLocationMapper($this->gameVersionId);
        $locationMapping = $mapper->mapping;

        $locationDataLookup = StarmapLocationData::query()
            ->join('game_starmap_locations', 'game_starmap_location_data.starmap_location_id', '=', 'game_starmap_locations.id')
            ->where('game_starmap_location_data.game_version_id', $this->gameVersionId)
            ->pluck('game_starmap_location_data.id', 'game_starmap_locations.uuid');

        $apiUrl = config('uexcorp.api_url');
        $updatedCount = 0;

        foreach ($this->vehicleUuids as $wikiUuid) {
            $vehicleId = $vehicles->get($wikiUuid);

            if ($vehicleId === null) {
                continue;
            }

            $vehicleData = $vehicleDataCollection->get($vehicleId);

            if ($vehicleData === null) {
                continue;
            }

            $uexUuid = $uuidMap->get($wikiUuid, $wikiUuid) ?? $wikiUuid;
            $uexId = $idMap->get($wikiUuid);

            if ($uexUuid === $wikiUuid && $uexId !== null) {
                $uexUuid = '';
            }

            if ($uexUuid === '' && $uexId === null) {
                continue;
            }

            $enriched = $this->enrichVehicle($apiUrl, $uexUuid, $uexId, $vehicleData, $locationMapping, $mapper, $locationDataLookup, $versionPrefixMap);

            if ($enriched) {
                $updatedCount++;
            }

            usleep(self::THROTTLE_MICROSECONDS);
        }

        Log::info('UEX vehicle prices enrichment chunk completed', [
            'count' => $updatedCount,
            'chunk_size' => count($this->vehicleUuids),
            'game_version_id' => $this->gameVersionId,
        ]);
    }

    /**
     * @param  Collection<int, string>  $locationMapping
     * @param  Collection<int, int|null>  $locationDataLookup
     * @param  array<string, string>  $versionPrefixMap
     */
    private function enrichVehicle(
        string $apiUrl,
        string $uexUuid,
        ?int $uexId,
        VehicleData $vehicleData,
        Collection $locationMapping,
        TerminalLocationMapper $mapper,
        Collection $locationDataLookup,
        array $versionPrefixMap,
    ): bool {
        $query = ($uexUuid !== '')
            ? ['uuid' => $uexUuid]
            : ['id_vehicle' => $uexId];

        $purchaseResponse = Http::timeout(30)->get("{$apiUrl}/vehicles_purchases_prices", $query);
        $rentalResponse = Http::timeout(30)->get("{$apiUrl}/vehicles_rentals_prices", $query);

        if (! $purchaseResponse->successful()) {
            Log::warning('UEX vehicle purchase price API failed', [
                'uuid' => $uexUuid,
                'uex_id' => $uexId,
                'status' => $purchaseResponse->status(),
            ]);
        }

        if (! $rentalResponse->successful()) {
            Log::warning('UEX vehicle rental price API failed', [
                'uuid' => $uexUuid,
                'uex_id' => $uexId,
                'status' => $rentalResponse->status(),
            ]);
        }

        $purchaseData = $purchaseResponse->successful() ? $purchaseResponse->json('data', []) : [];
        $rentalData = $rentalResponse->successful() ? $rentalResponse->json('data', []) : [];

        if ((! is_array($purchaseData) || $purchaseData === []) && (! is_array($rentalData) || $rentalData === [])) {
            return false;
        }

        $jsonValues = [];

        if (is_array($purchaseData) && $purchaseData !== []) {
            $mapped = $this->mapEnrichedPrices($purchaseData, $locationMapping, $mapper, $locationDataLookup, 'price_buy', $versionPrefixMap);

            if ($mapped !== []) {
                $jsonValues['uex_purchase_prices'] = $mapped;
            }
        }

        if (is_array($rentalData) && $rentalData !== []) {
            $mapped = $this->mapEnrichedPrices($rentalData, $locationMapping, $mapper, $locationDataLookup, 'price_rent', $versionPrefixMap);

            if ($mapped !== []) {
                $jsonValues['uex_rental_prices'] = $mapped;
            }
        }

        if ($jsonValues !== []) {
            $vehicleData->update($jsonValues);
        }

        return true;
    }

    /**
     * @param  Collection<int, string>  $locationMapping
     * @param  Collection<int, int|null>  $locationDataLookup
     * @param  array<string, string>  $versionPrefixMap
     */
    private function mapEnrichedPrices(
        array $apiPrices,
        Collection $locationMapping,
        TerminalLocationMapper $mapper,
        Collection $locationDataLookup,
        string $priceField,
        array $versionPrefixMap,
    ): array {
        return collect($apiPrices)
            ->filter(fn (array $p): bool => $this->matchesKnownVersion($p['game_version'] ?? null, $versionPrefixMap))
            ->unique('id_terminal')
            ->map(function (array $p) use ($locationMapping, $mapper, $locationDataLookup, $priceField, $versionPrefixMap): array {
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
                    $priceField => $p[$priceField],
                    'game_version' => $this->resolveDbVersionCode($p['game_version'] ?? null, $versionPrefixMap),
                    'date_updated' => Carbon::createFromTimestamp((int) $p['date_modified'])->toIso8601String(),
                    'uex_link' => $this->buildVehicleLink($terminalId, $priceField),
                ];
            })
            ->values()
            ->toArray();
    }

    public function failed(Throwable $exception): void
    {
        Log::error('UEX vehicle prices enrichment job failed', [
            'game_version_id' => $this->gameVersionId,
            'chunk_size' => count($this->vehicleUuids),
            'message' => $exception->getMessage(),
        ]);
    }
}
