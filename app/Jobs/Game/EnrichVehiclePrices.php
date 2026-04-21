<?php

declare(strict_types=1);

namespace App\Jobs\Game;

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
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

    private const int THROTTLE_MICROSECONDS = 200_000;

    /**
     * @param  array<int, string>  $vehicleUuids  wiki UUIDs
     * @param  array<string, string>  $wikiToUexMap  wikiUUID => uexUUID
     */
    public function __construct(
        private readonly int $gameVersionId,
        private readonly array $vehicleUuids,
        private readonly array $wikiToUexMap = [],
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $reverseMap = collect($this->wikiToUexMap);

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
        $locationMapping = $mapper->getMapping();

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

            $uexUuid = $reverseMap->get($wikiUuid, $wikiUuid);

            $enriched = $this->enrichVehicle($apiUrl, $uexUuid, $vehicleData, $locationMapping, $mapper, $locationDataLookup);

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

    private function enrichVehicle(
        string $apiUrl,
        string $uexUuid,
        VehicleData $vehicleData,
        Collection $locationMapping,
        TerminalLocationMapper $mapper,
        Collection $locationDataLookup,
    ): bool {
        $purchaseResponse = Http::timeout(30)->get("{$apiUrl}/vehicles_purchases_prices", ['uuid' => $uexUuid]);
        $rentalResponse = Http::timeout(30)->get("{$apiUrl}/vehicles_rentals_prices", ['uuid' => $uexUuid]);

        $purchaseData = $purchaseResponse->successful() ? $purchaseResponse->json('data', []) : [];
        $rentalData = $rentalResponse->successful() ? $rentalResponse->json('data', []) : [];

        if ((! is_array($purchaseData) || $purchaseData === []) && (! is_array($rentalData) || $rentalData === [])) {
            return false;
        }

        if (is_array($purchaseData) && $purchaseData !== []) {
            $vehicleData->uex_purchase_prices = $this->mapEnrichedPrices($purchaseData, $locationMapping, $mapper, $locationDataLookup, 'price_buy');
        }

        if (is_array($rentalData) && $rentalData !== []) {
            $vehicleData->uex_rental_prices = $this->mapEnrichedPrices($rentalData, $locationMapping, $mapper, $locationDataLookup, 'price_rent');
        }

        $vehicleData->save();

        return true;
    }

    /**
     * @param  Collection<int, string>  $locationMapping
     * @param  Collection<int, int|null>  $locationDataLookup
     */
    private function mapEnrichedPrices(
        array $apiPrices,
        Collection $locationMapping,
        TerminalLocationMapper $mapper,
        Collection $locationDataLookup,
        string $priceField,
    ): array {
        return collect($apiPrices)
            ->unique('id_terminal')
            ->map(function (array $p) use ($locationMapping, $mapper, $locationDataLookup, $priceField): array {
                $terminalId = (int) $p['id_terminal'];
                $locationUuid = $locationMapping->get($terminalId);

                return [
                    'terminal_id' => $terminalId,
                    'terminal_code' => $p['terminal_code'] ?? $mapper->getTerminalCode($terminalId),
                    'terminal_name' => $p['terminal_name'],
                    'starmap_location_uuid' => $locationUuid,
                    'starmap_location_data_id' => $locationUuid !== null
                        ? $locationDataLookup->get($locationUuid)
                        : null,
                    $priceField => $p[$priceField],
                    'date_updated' => Carbon::createFromTimestamp((int) $p['date_modified'])->toIso8601String(),
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
