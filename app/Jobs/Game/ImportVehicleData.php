<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use App\Services\Game\SlugService;
use App\Services\Game\VehicleMatchingService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use JsonException;
use RuntimeException;

class ImportVehicleData implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $gameVersionId,
        private readonly string $path,
        private readonly ?VehicleMatchingService $matcher = null
    ) {}

    /**
     * Execute the job.
     *
     * @throws JsonException
     */
    public function handle(): void
    {
        $payload = $this->readPayload();

        if (! isset($payload['UUID'])) {
            return;
        }

        $vehicle = Vehicle::query()->firstOrCreate(
            ['uuid' => $payload['UUID']],
            ['uuid' => $payload['UUID']]
        );

        $manufacturerId = $this->resolveManufacturerId($payload);
        $shipmatrixId = $this->resolveShipmatrixVehicleId($payload);

        VehicleData::query()->updateOrCreate(
            [
                'vehicle_id' => $vehicle->id,
                'game_version_id' => $this->gameVersionId,
            ],
            $this->mapVehicleData($payload, $manufacturerId, $shipmatrixId)
        );

        $this->updateSlug($vehicle, $payload);

    }

    /**
     * @throws JsonException
     */
    private function readPayload(): array
    {
        $contents = Storage::disk('scunpacked')->get($this->path);

        return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    }

    private function resolveManufacturerId(array $payload): int
    {
        $manufacturerUuid = Arr::get($payload, 'Manufacturer.UUID', '00000000-0000-0000-0000-000000000000');

        $manufacturer = Manufacturer::query()
            ->where('uuid', $manufacturerUuid)
            ->first();

        if ($manufacturer === null) {
            throw new RuntimeException(sprintf('Manufacturer with uuid %s does not exist for vehicle %s.', $manufacturerUuid, $payload['UUID']));
        }

        return $manufacturer->id;
    }

    private function mapVehicleData(array $payload, ?int $manufacturerId, ?int $shipmatrixId): array
    {
        return [
            'manufacturer_id' => $manufacturerId,
            'shipmatrix_id' => $shipmatrixId,
            'class_name' => $payload['ClassName'] ?? null,
            'name' => $payload['Name'] ?? null,
            'display_name' => $this->generateDisplayName($payload, $manufacturerId),
            'career' => $payload['Career'] ?? null,
            'role' => $payload['Role'] ?? null,

            'is_vehicle' => (bool) Arr::get($payload, 'IsVehicle', false),
            'is_gravlev' => (bool) Arr::get($payload, 'IsGravlev', false),
            'is_spaceship' => (bool) Arr::get($payload, 'IsSpaceship', false),

            'size' => Arr::get($payload, 'Size'),

            'data' => $payload,
        ];
    }

    private function resolveShipmatrixVehicleId(array $payload): ?int
    {
        return ($this->matcher ?? app(VehicleMatchingService::class))->findMatch($payload);
    }

    private function normalizeName(string $name): string
    {
        $name = str_replace('_', ' ', $name);
        $name = preg_replace('/\\s+/', ' ', $name ?? '');

        return trim((string) $name);
    }

    private function stripManufacturerPrefix(string $name, string $manufacturer): string
    {
        $pattern = sprintf('/^%s\\s+/i', preg_quote($manufacturer, '/'));

        return trim((string) preg_replace($pattern, '', $name));
    }

    /**
     * Get possible short names for a manufacturer to use for prefix stripping.
     *
     * Returns an array of candidates to try when stripping manufacturer prefixes,
     * ordered from most specific to least specific.
     */
    private function getManufacturerShortNames(array $manufacturerData): array
    {
        $manufacturerName = Arr::get($manufacturerData, 'Name');

        if ($manufacturerName === null || $manufacturerName === '') {
            return [];
        }

        $candidates = [];

        $specialCases = [
            'Roberts Space Industries' => ['RSI'],
            'Consolidated Outland' => ['C.O.'],
            'Musashi Industrial & Starflight Concern' => ['MISC'],
        ];

        if (isset($specialCases[$manufacturerName])) {
            $candidates = array_merge($candidates, $specialCases[$manufacturerName]);
        }

        $candidates[] = $manufacturerName;

        $parts = explode(' ', $manufacturerName);
        if (count($parts) > 0 && $parts[0] !== '') {
            $candidates[] = $parts[0];
        }

        return array_unique($candidates);
    }

    /**
     * Generate a display name by stripping manufacturer prefix from the vehicle name.
     *
     * Examples:
     * - "RSI Constellation Andromeda" > "Constellation Andromeda"
     * - "Anvil F7C Hornet" > "F7C Hornet"
     * - "F8C Lightning PYAM Exec" > "F8C Lightning PYAM Exec" (no prefix)
     */
    private function generateDisplayName(array $payload, ?int $manufacturerId): ?string
    {
        $rawName = $payload['Name'] ?? null;

        if ($rawName === null || $rawName === '') {
            return null;
        }

        $normalized = $this->normalizeName($rawName);

        if ($manufacturerId === null) {
            return $normalized;
        }

        $manufacturerData = Arr::get($payload, 'Manufacturer', []);

        $shortNames = $this->getManufacturerShortNames($manufacturerData);

        foreach ($shortNames as $shortName) {
            $stripped = $this->stripManufacturerPrefix($normalized, $shortName);
            if ($stripped !== '' && $stripped !== $normalized) {
                return $stripped;
            }
        }

        return $normalized;
    }

    private function updateSlug(Vehicle $vehicle, array $payload): void
    {
        if ($vehicle->slug !== null && $vehicle->slug !== '') {
            return;
        }

        $className = $payload['ClassName'] ?? null;

        if ($className === null || $className === '') {
            return;
        }

        app(SlugService::class)->assignUniqueSlug($vehicle, $className, "vehicle-{$vehicle->id}");
    }
}
