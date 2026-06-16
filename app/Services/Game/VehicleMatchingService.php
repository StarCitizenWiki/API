<?php

declare(strict_types=1);

namespace App\Services\Game;

use App\Models\StarCitizen\ShipMatrix\Manufacturer as ShipMatrixManufacturer;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle as ShipMatrixVehicle;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VehicleMatchingService
{
    private static ?Collection $shipMatrixCache = null;

    /** @var array<string, int>|null lowercase name_short/name => id */
    private static ?array $manufacturerLookup = null;

    /** @var array<string, ShipMatrixVehicle>|null slug => vehicle */
    private static ?array $slugLookup = null;

    /** @var array<string, ShipMatrixVehicle>|null lowercase name => vehicle */
    private static ?array $lowerNameLookup = null;

    public static function resetState(): void
    {
        self::$shipMatrixCache = null;
        self::$manufacturerLookup = null;
        self::$slugLookup = null;
        self::$lowerNameLookup = null;
    }

    public function findMatch(array $payload): ?int
    {
        $manufacturerData = Arr::get($payload, 'Manufacturer', []);
        $manufacturerCode = Arr::get($manufacturerData, 'Code');
        $manufacturerName = Arr::get($manufacturerData, 'Name');

        $manufacturerId = $this->matchManufacturer($manufacturerCode, $manufacturerName);
        $candidates = $this->buildCandidateNames($payload, $manufacturerName, $manufacturerCode);

        // Strict tier first over all candidates, so an exact hit on one beats a
        // loose hit on another ("MOLE" exact wins over "Argo MOLE" substring).
        $tiers = [
            $this->matchExact(...),
            $this->matchContains(...),
            $this->fuzzyMatch(...),
        ];

        foreach ($tiers as $tier) {
            foreach ($candidates as $candidate) {
                if (! $match = $tier((string) $candidate, $manufacturerId)) {
                    continue;
                }

                Log::info('Vehicle matched', [
                    'uuid' => $payload['UUID'] ?? null,
                    'game_name' => $payload['Name'] ?? null,
                    'matched_to' => $match->name,
                    'candidate' => $candidate,
                ]);

                return $match->id;
            }
        }

        // Unconstrained exact-only fallback for ships imported under the wrong maker
        if ($manufacturerId !== null) {
            foreach ($candidates as $candidate) {
                if (! $match = $this->matchExact((string) $candidate, null)) {
                    continue;
                }

                Log::warning('Vehicle matched without manufacturer constraint', [
                    'uuid' => $payload['UUID'] ?? null,
                    'game_name' => $payload['Name'] ?? null,
                    'matched_to' => $match->name,
                    'expected_manufacturer_id' => $manufacturerId,
                    'actual_manufacturer_id' => $match->manufacturer_id,
                ]);

                return $match->id;
            }
        }

        Log::warning('Vehicle match failed', [
            'uuid' => $payload['UUID'] ?? null,
            'game_name' => $payload['Name'] ?? null,
            'class_name' => $payload['ClassName'] ?? null,
            'manufacturer' => $manufacturerName,
            'candidates_tried' => $candidates,
        ]);

        return null;
    }

    private function matchManufacturer(?string $code, ?string $name): ?int
    {
        if (self::$manufacturerLookup === null) {
            self::$manufacturerLookup = [];
            foreach (ShipMatrixManufacturer::query()->get() as $mfr) {
                if ($mfr->name_short !== null) {
                    self::$manufacturerLookup[mb_strtolower($mfr->name_short)] = $mfr->id;
                }

                if ($mfr->name !== null) {
                    self::$manufacturerLookup[mb_strtolower($mfr->name)] = $mfr->id;
                }
            }
        }

        if ($code !== null && $code !== '') {
            return self::$manufacturerLookup[mb_strtolower($code)] ?? null;
        }

        if ($name !== null && $name !== '') {
            return self::$manufacturerLookup[mb_strtolower($name)] ?? null;
        }

        return null;
    }

    private function buildCandidateNames(array $payload, ?string $manufacturerName, ?string $manufacturerCode): array
    {
        $candidates = [];
        $payloadName = $this->normalizeName($payload['Name'] ?? $payload['ClassName'] ?? '');

        if ($payloadName !== '') {
            $candidates[] = $payloadName;
        }

        foreach ($this->getManufacturerShortNames($manufacturerName) as $shortName) {
            $stripped = $this->stripManufacturerPrefix($payloadName, $shortName);

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

        $candidates = array_unique([
            ...$candidates,
            ...array_filter(array_map($this->reorderBestInShowName(...), $candidates)),
        ]);

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

        $candidates = array_unique([
            ...$candidates,
            ...array_merge(...array_map($this->stripSpecialEditionSuffixes(...), $candidates)),
        ]);

        return $candidates
                |> array_filter(...)
                |> array_unique(...)
                |> array_values(...);
    }

    private function normalizeName(string $name): string
    {
        return str_replace('_', ' ', $name)
                |> (static fn($x) => preg_replace('/\s+/', ' ', $x))
                |> trim(...);
    }

    private function stripManufacturerPrefix(string $name, string $manufacturer): string
    {
        $pattern = sprintf('/^%s\\s+/i', preg_quote($manufacturer, '/'));

        return trim(preg_replace($pattern, '', $name));
    }

    private function getManufacturerShortNames(?string $manufacturerName): array
    {
        if ($manufacturerName === null || $manufacturerName === '') {
            return [];
        }

        $candidates = [
            'Roberts Space Industries' => ['RSI'],
            'Consolidated Outland' => ['C.O.'],
            'Musashi Industrial & Starflight Concern' => ['MISC'],
        ][$manufacturerName] ?? [];

        $candidates[] = $manufacturerName;

        $firstWord = explode(' ', $manufacturerName)[0];
        if ($firstWord !== '') {
            $candidates[] = $firstWord;
        }

        return array_unique($candidates);
    }

    private function matchExact(string $candidate, ?int $manufacturerId): ?ShipMatrixVehicle
    {
        $this->ensureShipMatrixCache();

        $match = self::$slugLookup[Str::slug($candidate)] ?? null;
        if ($match !== null && ($manufacturerId === null || $match->manufacturer_id === $manufacturerId)) {
            return $match;
        }

        $match = self::$lowerNameLookup[mb_strtolower($candidate)] ?? null;
        if ($match !== null && ($manufacturerId === null || $match->manufacturer_id === $manufacturerId)) {
            return $match;
        }

        return null;
    }

    private function matchContains(string $candidate, ?int $manufacturerId): ?ShipMatrixVehicle
    {
        $this->ensureShipMatrixCache();

        $filtered = self::$shipMatrixCache;
        if ($manufacturerId !== null) {
            $filtered = $filtered->where('manufacturer_id', $manufacturerId);
        }

        $lowerCandidate = mb_strtolower($candidate);

        return $filtered->first(fn (ShipMatrixVehicle $v) => str_contains(mb_strtolower($v->name), $lowerCandidate));
    }

    /**
     * Manufacturer-gated Levenshtein: without it a loose match can pair an
     * unrelated ship across makers (e.g. NPC "Mauler" -> pledge "Mule").
     */
    private function fuzzyMatch(string $candidate, ?int $manufacturerId): ?ShipMatrixVehicle
    {
        if ($manufacturerId === null) {
            return null;
        }

        $this->ensureShipMatrixCache();

        return self::$shipMatrixCache
            ->where('manufacturer_id', $manufacturerId)
            ->first(fn (ShipMatrixVehicle $vehicle) => levenshtein(
                mb_strtolower($candidate),
                mb_strtolower($vehicle->name),
            ) <= 2);
    }

    private function ensureShipMatrixCache(): void
    {
        if (self::$shipMatrixCache !== null) {
            return;
        }

        self::$shipMatrixCache = ShipMatrixVehicle::with('manufacturer')->get();

        self::$slugLookup = [];
        self::$lowerNameLookup = [];
        foreach (self::$shipMatrixCache as $vehicle) {
            self::$slugLookup[$vehicle->slug] = $vehicle;
            self::$lowerNameLookup[mb_strtolower($vehicle->name)] = $vehicle;
        }
    }

    private function reorderBestInShowName(string $name): ?string
    {
        if (preg_match('/^(.+?)\s+(\d{4})\s+(Best\s+In\s+Show\s+Edition)$/i', $name, $matches)) {
            return trim($matches[1].' '.$matches[3].' '.$matches[2]);
        }

        if (preg_match('/^(.+?)\s+(Best\s+In\s+Show\s+Edition)\s+(\d{4})$/i', $name, $matches)) {
            return trim($matches[1].' '.$matches[3].' '.$matches[2]);
        }

        return null;
    }

    /**
     * Strip suffixes whose base model is what the ship matrix tracks.
     * Obtainable editions with no own MSRP (Wikelo/Teach's/PYAM/IKTI/Star Kitten/Executive) are intentionally not collapsed onto a base.
     */
    private function stripSpecialEditionSuffixes(string $name): array
    {
        $candidates = [$name];

        $patterns = [
            '/\s+\d{4}\s+BIS$/i',
            '/\s+(Snowland|Orange\s+Line|Cool\s+Metal)\s+Color$/i',
            '/\s+Dunlevy$/i',
        ];

        foreach ($patterns as $pattern) {
            $stripped = preg_replace($pattern, '', $name);
            if ($stripped !== $name && $stripped !== '') {
                $candidates[] = $stripped;
            }
        }

        return array_unique($candidates);
    }
}
