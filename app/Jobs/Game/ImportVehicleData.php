<?php

namespace App\Jobs\Game;

use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use App\Models\StarCitizen\Manufacturer\Manufacturer as ShipMatrixManufacturer;
use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle as ShipMatrixVehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;

class ImportVehicleData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $gameVersionId,
        private readonly string $path
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

        $manufacturerId = $this->resolveManufacturer($payload);
        $shipmatrixId = $this->resolveShipmatrixVehicleId($payload);

        VehicleData::query()->updateOrCreate(
            [
                'vehicle_id' => $vehicle->id,
                'game_version_id' => $this->gameVersionId,
            ],
            $this->mapVehicleData($payload, $manufacturerId, $shipmatrixId)
        );
    }

    /**
     * @throws JsonException
     */
    private function readPayload(): array
    {
        $contents = Storage::disk('scunpacked')->get($this->path);

        return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    }

    private function resolveManufacturer(array $payload): int
    {
        $manufacturer = $payload['Manufacturer'] ?? null;

        if (! is_array($manufacturer)) {
            throw new RuntimeException('Manufacturer data missing from payload. UUID: '.$payload['UUID'] ?? 'unknown');
        }

        $uuid = $manufacturer['UUID'] ?? null;

        if (! is_string($uuid) || $uuid === '') {
            throw new RuntimeException('Manufacturer UUID missing from payload. UUID: '.$payload['UUID'] ?? 'unknown');
        }

        $record = Manufacturer::query()->where('uuid', $uuid)->first();

        if ($record === null) {
            throw new RuntimeException(sprintf('Manufacturer with UUID %s does not exist.', $uuid));
        }

        return $record->id;
    }

    private function mapVehicleData(array $payload, ?int $manufacturerId, ?int $shipmatrixId): array
    {
        $insurance = $payload['Insurance'] ?? [];

        return [
            'manufacturer_id' => $manufacturerId,
            'shipmatrix_id' => $shipmatrixId,
            'class_name' => $payload['ClassName'] ?? null,
            'name' => $payload['Name'] ?? null,
            'career' => $payload['Career'] ?? null,
            'role' => $payload['Role'] ?? null,

            'is_vehicle' => (bool) Arr::get($payload, 'IsVehicle', false),
            'is_gravlev' => (bool) Arr::get($payload, 'IsGravlev', false),
            'is_spaceship' => (bool) Arr::get($payload, 'IsSpaceship', false),

            'size' => Arr::get($payload, 'Size'),
            'length' => Arr::get($payload, 'Length'),
            'width' => Arr::get($payload, 'Width'),
            'height' => Arr::get($payload, 'Height'),
            'crew' => Arr::get($payload, 'Crew'),
            'mass' => Arr::get($payload, 'Mass'),
            'cargo' => Arr::get($payload, 'Cargo'),

            'insurance_claim_time' => Arr::get($insurance, 'StandardClaimTime'),
            'insurance_expedited_time' => Arr::get($insurance, 'ExpeditedClaimTime'),
            'insurance_expedited_cost' => Arr::get($insurance, 'ExpeditedCost'),

            'shield_face_type' => Arr::get($payload, 'ShieldFaceType'),
            'shield_hp' => Arr::get($payload, 'ShieldHp'),
            'health' => Arr::get($payload, 'Health'),

            'quantum_speed' => Arr::get($payload, 'Quantum.QuantumSpeed'),
            'quantum_spool_time' => Arr::get($payload, 'Quantum.QuantumSpoolTime'),
            'quantum_fuel_capacity' => Arr::get($payload, 'Quantum.QuantumFuelCapacity'),
            'quantum_range' => Arr::get($payload, 'Quantum.QuantumRange'),

            'fuel_capacity' => Arr::get($payload, 'Fuel.Capacity'),
            'fuel_intake_rate' => Arr::get($payload, 'Fuel.IntakeRate'),
            'fuel_usage_main' => Arr::get($payload, 'Fuel.Usage.Main'),
            'fuel_usage_retro' => Arr::get($payload, 'Fuel.Usage.Retro'),
            'fuel_usage_vtol' => Arr::get($payload, 'Fuel.Usage.Vtol'),
            'fuel_usage_maneuvering' => Arr::get($payload, 'Fuel.Usage.Maneuvering'),

            'json' => $payload,
        ];
    }

    private function resolveShipmatrixVehicleId(array $payload): ?int
    {
        $manufacturerData = Arr::get($payload, 'Manufacturer', []);
        $manufacturerCode = Arr::get($manufacturerData, 'Code');
        $manufacturerName = Arr::get($manufacturerData, 'Name');

        $shipmatrixManufacturerId = $this->matchShipmatrixManufacturer($manufacturerCode, $manufacturerName);

        $candidates = $this->buildVehicleNameCandidates($payload, $manufacturerName, $manufacturerCode);

        foreach ($candidates as $candidate) {
            $match = $this->findShipmatrixVehicle($candidate, $shipmatrixManufacturerId);

            if ($match !== null) {
                Log::info('Vehicle matched', [
                    'uuid' => $payload['UUID'],
                    'game_name' => $payload['Name'],
                    'matched_to' => $match->name,
                    'candidate' => $candidate,
                ]);

                return $match->id;
            }
        }

        if ($shipmatrixManufacturerId !== null) {
            foreach ($candidates as $candidate) {
                $match = $this->findShipmatrixVehicle($candidate, null);

                if ($match !== null) {
                    Log::warning('Vehicle matched without manufacturer constraint', [
                        'uuid' => $payload['UUID'],
                        'game_name' => $payload['Name'],
                        'matched_to' => $match->name,
                        'expected_manufacturer_id' => $shipmatrixManufacturerId,
                        'actual_manufacturer_id' => $match->manufacturer_id,
                    ]);

                    return $match->id;
                }
            }
        }

        Log::warning('Vehicle match failed', [
            'uuid' => $payload['UUID'],
            'game_name' => $payload['Name'],
            'class_name' => $payload['ClassName'],
            'manufacturer' => $manufacturerName,
            'candidates_tried' => $candidates,
        ]);

        return null;
    }

    private function matchShipmatrixManufacturer(?string $code, ?string $name): ?int
    {
        $query = ShipMatrixManufacturer::query();

        if ($code !== null && $code !== '') {
            $manufacturer = (clone $query)->whereRaw('LOWER(name_short) = ?', [mb_strtolower($code)])->first();

            if ($manufacturer !== null) {
                return $manufacturer->id;
            }
        }

        if ($name !== null && $name !== '') {
            $manufacturer = (clone $query)->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();

            if ($manufacturer !== null) {
                return $manufacturer->id;
            }
        }

        return null;
    }

    private function buildVehicleNameCandidates(array $payload, ?string $manufacturerName, ?string $manufacturerCode): array
    {
        $candidates = [];
        $payloadName = $this->normalizeName($payload['Name'] ?? $payload['ClassName'] ?? '');

        if ($payloadName !== '') {
            $candidates[] = $payloadName;
        }

        if ($manufacturerName !== null) {
            $stripped = $this->stripManufacturerPrefix($payloadName, $manufacturerName);
            if ($stripped !== '' && $stripped !== $payloadName) {
                $candidates[] = $stripped;
            }
        }

        if ($manufacturerCode !== null) {
            $stripped = $this->stripManufacturerPrefix($payloadName, $manufacturerCode);
            if ($stripped !== '' && $stripped !== $payloadName) {
                $candidates[] = $stripped;
            }
        }

        $className = Arr::get($payload, 'ClassName');
        if (is_string($className) && $className !== '') {
            $parts = array_filter(explode('_', $className));

            if (count($parts) > 1) {
                array_shift($parts);
            }

            $classCandidate = $this->normalizeName(implode(' ', $parts));
            if ($classCandidate !== '') {
                $candidates[] = $classCandidate;
            }
        }

        $overrides = config('game.vehicle_name_overrides', []);
        if ($payloadName !== '' && array_key_exists($payloadName, $overrides)) {
            array_unshift($candidates, $overrides[$payloadName]);
        }

        $reversed = [];
        foreach ($candidates as $candidate) {
            $parts = preg_split('/\\s+/', $candidate);
            if ($parts !== false && count($parts) > 1) {
                $reversed[] = implode(' ', array_reverse($parts));
            }
        }

        $candidates = [...$candidates, ...$reversed];

        return array_values(array_unique(array_filter($candidates)));
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

    private function findShipmatrixVehicle(string $candidate, ?int $manufacturerId): ?ShipMatrixVehicle
    {
        $baseQuery = ShipMatrixVehicle::query()
            ->when($manufacturerId !== null, static fn ($query) => $query->where('manufacturer_id', $manufacturerId));

        $slug = Str::slug($candidate);

        $match = (clone $baseQuery)->where('slug', $slug)->first();
        if ($match !== null) {
            return $match;
        }

        $match = (clone $baseQuery)->where('name', $candidate)->first();
        if ($match !== null) {
            return $match;
        }

        $match = (clone $baseQuery)->whereRaw('LOWER(name) = ?', [mb_strtolower($candidate)])->first();
        if ($match !== null) {
            return $match;
        }

        $match = (clone $baseQuery)->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($candidate).'%'])->first();

        return $match ?? $this->fuzzyMatch($candidate, $manufacturerId);
    }

    /**
     * Try fuzzy matching using Levenshtein distance
     */
    private function fuzzyMatch(string $candidate, ?int $manufacturerId): ?ShipMatrixVehicle
    {
        $baseQuery = ShipMatrixVehicle::query()
            ->when($manufacturerId !== null, static fn ($query) => $query->where('manufacturer_id', $manufacturerId));

        $vehicles = $baseQuery->get();

        return $vehicles->first(function ($vehicle) use ($candidate) {
            $distance = levenshtein(
                mb_strtolower($candidate),
                mb_strtolower($vehicle->name)
            );

            return $distance <= 2;
        });
    }
}
