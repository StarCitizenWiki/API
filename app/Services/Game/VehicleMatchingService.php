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
    private ?Collection $shipMatrixCache = null;

    /**
     * Find ship matrix vehicle ID for given game vehicle payload.
     * Returns shipmatrix_id or null if no match found.
     */
    public function findMatch(array $payload): ?int
    {
        $manufacturerData = Arr::get($payload, 'Manufacturer', []);
        $manufacturerCode = Arr::get($manufacturerData, 'Code');
        $manufacturerName = Arr::get($manufacturerData, 'Name');

        $shipmatrixManufacturerId = $this->matchManufacturer($manufacturerCode, $manufacturerName);
        $candidates = $this->buildCandidateNames($payload, $manufacturerName, $manufacturerCode);

        foreach ($candidates as $candidate) {
            $match = $this->findVehicle($candidate, $shipmatrixManufacturerId);
            if ($match !== null) {
                Log::info('Vehicle matched', [
                    'uuid' => $payload['UUID'] ?? null,
                    'game_name' => $payload['Name'] ?? null,
                    'matched_to' => $match->name,
                    'candidate' => $candidate,
                ]);

                return $match->id;
            }
        }

        if ($shipmatrixManufacturerId !== null) {
            foreach ($candidates as $candidate) {
                $match = $this->findVehicle($candidate, null);
                if ($match !== null) {
                    Log::warning('Vehicle matched without manufacturer constraint', [
                        'uuid' => $payload['UUID'] ?? null,
                        'game_name' => $payload['Name'] ?? null,
                        'matched_to' => $match->name,
                        'expected_manufacturer_id' => $shipmatrixManufacturerId,
                        'actual_manufacturer_id' => $match->manufacturer_id,
                    ]);

                    return $match->id;
                }
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

    private function buildCandidateNames(array $payload, ?string $manufacturerName, ?string $manufacturerCode): array
    {
        $candidates = [];
        $payloadName = $this->normalizeName($payload['Name'] ?? $payload['ClassName'] ?? '');

        if ($payloadName !== '') {
            $candidates[] = $payloadName;
        }

        // possible manufacturer short names for prefix stripping
        $manufacturerShortNames = $this->getManufacturerShortNames($manufacturerName);

        foreach ($manufacturerShortNames as $shortName) {
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

        // Best In Show word reordering
        $bisVariants = [];
        foreach ($candidates as $candidate) {
            $reordered = $this->reorderBestInShowName($candidate);
            if ($reordered !== null) {
                $bisVariants[] = $reordered;
            }
        }
        $candidates = array_unique([...$candidates, ...$bisVariants]);

        // ClassName parsing
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

        $withEditionsStripped = [];
        foreach ($candidates as $candidate) {
            $stripped = $this->stripSpecialEditionSuffixes($candidate);
            $withEditionsStripped = [...$withEditionsStripped, ...$stripped];
        }
        $candidates = array_unique([...$candidates, ...$withEditionsStripped]);

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

    /**
     * Get possible short names for a manufacturer to use for prefix stripping.
     *
     * Returns an array of candidates to try when stripping manufacturer prefixes,
     * ordered from most specific to least specific.
     */
    private function getManufacturerShortNames(?string $manufacturerName): array
    {
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

    private function findVehicle(string $candidate, ?int $manufacturerId): ?ShipMatrixVehicle
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
     * Reorder Best In Show edition names to handle word order differences
     * Returns reordered name or null if pattern doesn't match
     */
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
     * Strip known special edition suffixes from vehicle names
     * Returns multiple candidates with various suffixes removed
     */
    private function stripSpecialEditionSuffixes(string $name): array
    {
        $candidates = [$name];

        // patterns to strip (in order of specificity)
        $patterns = [
            // Event variants
            '/\s+Wikelo\s+(War|Sneak|Work|Savior|Speedy|Special)\s+Special$/i',
            '/\s+Wikelo\s+Special$/i',
            '/\s+PYAM\s+Exec$/i',
            '/\s+Teach\'s\s+Special$/i',

            // Best In Show editions (multiple formats)
            '/\s+\d{4}\s+Best\s+In\s+Show\s+Edition$/i',
            '/\s+\d{4}\s+BIS$/i',
            '/\s+Best\s+In\s+Show\s+Edition\s+\d{4}$/i', // Also try reversed

            // Color/Gradient variants
            '/\s+(Snowland|Orange\s+Line|Cool\s+Metal)\s+Color$/i',
            '/\s+Color$/i',

            // Executive editions
            '/\s+Executive\s+Edition$/i',

            // Simple variant suffixes
            '/\s+Dunlevy$/i',
            '/\s+IKTI(\s+Rad)?$/i',
            '/\s+Star\s+Kitten$/i',
        ];

        foreach ($patterns as $pattern) {
            $stripped = preg_replace($pattern, '', $name);
            if ($stripped !== $name && $stripped !== '') {
                $candidates[] = $stripped;
            }
        }

        return array_unique($candidates);
    }

    /**
     * Try fuzzy matching using Levenshtein distance
     * Optimized with caching to avoid loading all vehicles repeatedly
     */
    private function fuzzyMatch(string $candidate, ?int $manufacturerId): ?ShipMatrixVehicle
    {
        if ($this->shipMatrixCache === null) {
            $this->shipMatrixCache = ShipMatrixVehicle::with('manufacturer')->get();
        }

        $filtered = $this->shipMatrixCache;

        if ($manufacturerId !== null) {
            $filtered = $filtered->where('manufacturer_id', $manufacturerId);
        }

        return $filtered->first(function ($vehicle) use ($candidate) {
            $distance = levenshtein(
                mb_strtolower($candidate),
                mb_strtolower($vehicle->name)
            );

            return $distance <= 2;
        });
    }
}
