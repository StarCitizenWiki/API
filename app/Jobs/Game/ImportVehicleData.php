<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use App\Models\System\Language;
use App\Services\Game\SlugService;
use App\Services\Game\VehicleMatchingService;
use App\Services\ItemRelevanceChecker;
use App\Services\Parser\SC\Labels;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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

    /** @var Collection<int, Manufacturer>|null UUID => Manufacturer */
    private static ?Collection $manufacturerLookup = null;

    /** @var array<string, Vehicle>|null UUID => Vehicle */
    private static ?array $vehicleCache = null;

    private static ?Labels $labels = null;

    public function __construct(
        private readonly int $gameVersionId,
        private readonly string $path,
        private readonly string $diskName = 'scunpacked',
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

        $vehicle = self::$vehicleCache[$payload['UUID']] ??= Vehicle::query()->firstOrCreate(
            ['uuid' => $payload['UUID']],
            ['uuid' => $payload['UUID']]
        );

        $manufacturerId = $this->resolveManufacturerId($payload);
        $shipmatrixId = $this->resolveShipmatrixVehicleId($payload);

        $vehicleData = VehicleData::query()->updateOrCreate(
            [
                'vehicle_id' => $vehicle->id,
                'game_version_id' => $this->gameVersionId,
            ],
            $this->mapVehicleData($payload, $manufacturerId, $shipmatrixId)
        );

        $this->syncInstalledItems($vehicleData, $payload);

        $this->syncTranslations($vehicle, $payload);

        $this->updateSlug($vehicle, $payload);

    }

    /**
     * @throws JsonException
     */
    private function readPayload(): array
    {
        $contents = Storage::disk($this->diskName)->get($this->path);

        return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    }

    private function resolveManufacturerId(array $payload): int
    {
        $manufacturerUuid = Arr::get($payload, 'Manufacturer.UUID', '00000000-0000-0000-0000-000000000000');

        if (self::$manufacturerLookup === null) {
            self::$manufacturerLookup = Manufacturer::query()
                ->get(['id', 'uuid'])
                ->keyBy('uuid');
        }

        $manufacturer = self::$manufacturerLookup->get($manufacturerUuid);

        if ($manufacturer === null) {
            // Lazy-reload: manufacturer may have been created after initial cache load (e.g. in tests)
            self::$manufacturerLookup = Manufacturer::query()
                ->get(['id', 'uuid'])
                ->keyBy('uuid');
            $manufacturer = self::$manufacturerLookup->get($manufacturerUuid);
        }

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

            'is_player_relevant' => ItemRelevanceChecker::isVehiclePlayerRelevant($payload['ClassName'] ?? ''),

            'size' => Arr::get($payload, 'Size'),

            'length' => Arr::get($payload, 'Length'),
            'width' => Arr::get($payload, 'Width'),
            'height' => Arr::get($payload, 'Height'),
            'mass_total' => Arr::get($payload, 'MassTotal'),
            'speed_scm' => Arr::get($payload, 'FlightCharacteristics.Speeds.Scm'),
            'speed_max' => Arr::get($payload, 'FlightCharacteristics.Speeds.Max'),
            'cargo_capacity' => Arr::get($payload, 'Cargo'),
            'crew_min' => Arr::get($payload, 'Crew'),
            'crew_max' => Arr::get($payload, 'Seating.CrewStations'),
            'health' => Arr::get($payload, 'Health'),
            'shield_hp' => Arr::get($payload, 'ShieldsTotal.Hp'),
            'shield_face_type' => Arr::get($payload, 'ShieldController.FaceType'),
            'armor_health' => Arr::get($payload, 'Armor.Health'),
            'vehicle_inventory' => Arr::get($payload, 'Stowage'),
            'cross_section_length' => Arr::get($payload, 'CrossSection.X'),
            'cross_section_width' => Arr::get($payload, 'CrossSection.Y'),
            'cross_section_height' => Arr::get($payload, 'CrossSection.Z'),
            'signature_ir_quantum' => Arr::get($payload, 'Emission.IrQuantum'),
            'signature_ir_shields' => Arr::get($payload, 'Emission.IrShields'),
            'signature_em_quantum' => Arr::get($payload, 'Emission.EmQuantum'),
            'signature_em_shields' => Arr::get($payload, 'Emission.EmShields'),
            'max_medical_tier' => $this->resolveMaxMedicalTier(
                Arr::get($payload, 'Seating.MedicalBeds', [])
            ),

            'data' => $payload,
        ];
    }

    /**
     * Resolve the highest medical tier from a list of medical beds.
     *
     * @param  array<int, array{Tier?: string}>  $medicalBeds
     */
    private function resolveMaxMedicalTier(array $medicalBeds): ?string
    {
        if ($medicalBeds === []) {
            return null;
        }

        $best = null;
        $bestTier = 0;

        foreach ($medicalBeds as $bed) {
            $tierLabel = $bed['Tier'] ?? null;

            if (! is_string($tierLabel) || $tierLabel === '') {
                continue;
            }

            $tier = (int) ltrim($tierLabel, 'T');

            if ($tier > $bestTier) {
                $bestTier = $tier;
                $best = $tierLabel;
            }
        }

        return $best;
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

    /**
     * Sync installed item pivot records based on port UUIDs from the Loadout JSON.
     */
    private function syncInstalledItems(VehicleData $vehicleData, array $payload): void
    {
        $portUuids = $this->extractPortUuids($payload['Loadout'] ?? []);

        if ($portUuids !== []) {
            $itemDataIds = ItemData::query()
                ->where('game_version_id', $this->gameVersionId)
                ->whereHas('item', fn ($q) => $q->whereIn('uuid', $portUuids))
                ->pluck('id');

            $vehicleData->installedItems()->sync($itemDataIds);
        } else {
            $vehicleData->installedItems()->sync([]);
        }
    }

    /**
     * Recursively extract all item UUIDs from the Loadout structure.
     *
     * @return array<int, string>
     */
    private function extractPortUuids(array $loadout): array
    {
        $uuids = [];

        foreach ($loadout as $port) {
            $uuid = $port['UUID'] ?? null;

            if (is_string($uuid) && $uuid !== '') {
                $uuids[] = $uuid;
            }

            if (isset($port['Loadout']) && is_array($port['Loadout'])) {
                $uuids = array_merge($uuids, $this->extractPortUuids($port['Loadout']));
            }
        }

        return array_unique(array_filter($uuids));
    }

    /**
     * Sync description translations from label keys onto the parent Vehicle model.
     */
    private function syncTranslations(Vehicle $vehicle, array $payload): void
    {
        $descriptionKey = Arr::get($payload, 'DescriptionKey');

        if (! is_string($descriptionKey) || trim($descriptionKey) === '') {
            return;
        }

        $updated = false;

        $englishDescription = $payload['DescriptionText'] ?? $payload['Description'] ?? null;

        if (is_string($englishDescription) && trim($englishDescription) !== '') {
            $vehicle->setTranslation('translation', Language::ENGLISH, trim($englishDescription));
            $updated = true;
        }

        foreach (config('translations.locales', []) as $locale) {
            $translation = $this->getLabels()->getTranslation($locale, $descriptionKey);

            if ($translation !== null && $translation !== '') {
                $vehicle->setTranslation('translation', $locale, str_replace('\n', "\n", $translation));
                $updated = true;
            }
        }

        if ($updated) {
            $vehicle->save();
        }
    }

    private function getLabels(): Labels
    {
        if (self::$labels === null) {
            self::$labels = new Labels;
        }

        return self::$labels;
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
