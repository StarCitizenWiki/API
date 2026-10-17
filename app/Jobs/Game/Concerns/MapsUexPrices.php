<?php

declare(strict_types=1);

namespace App\Jobs\Game\Concerns;

use App\Models\Game\StarmapLocationData;
use App\Support\UEXcorp\TerminalLocationMapper;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

trait MapsUexPrices
{
    /**
     * @return Collection<string, int> starmap location uuid => starmap_location_data id
     */
    private function locationDataLookup(): Collection
    {
        return StarmapLocationData::query()
            ->join('game_starmap_locations', 'game_starmap_location_data.starmap_location_id', '=', 'game_starmap_locations.id')
            ->where('game_starmap_location_data.game_version_id', $this->gameVersionId)
            ->pluck('game_starmap_location_data.id', 'game_starmap_locations.uuid');
    }

    /**
     * Resolve the UEX API identifiers for a wiki uuid.
     *
     * @param  Collection<string, string>  $uuidToUexUuidMap  wiki uuid => uex uuid
     * @param  Collection<string, int>  $uuidToUexIdMap  wiki uuid => uex id, for entities whose UEX uuid is empty
     * @param  Collection<string, string>  $reverseOverrides  wiki uuid => uex uuid overrides; empty when the family has none
     * @return array{0: string, 1: int|null} uex uuid ('' when only the id lookup applies) and uex id
     */
    private function resolveUexApiId(string $uuid, Collection $uuidToUexUuidMap, Collection $uuidToUexIdMap, Collection $reverseOverrides): array
    {
        $uexUuid = $uuidToUexUuidMap->get($uuid) ?? $reverseOverrides->get($uuid, $uuid);
        $uexId = $uuidToUexIdMap->get($uuid);

        if ($uexUuid === '' || ($uexUuid === $uuid && $uuidToUexIdMap->has($uuid))) {
            $uexUuid = '';
        }

        return [$uexUuid, $uexId];
    }

    /**
     * Map UEX price rows onto the terminal price rows stored in the uex_*_prices columns.
     *
     * @param  Collection<int, array<string, mixed>>  $prices
     * @param  Collection<string, int>  $locationDataLookup
     * @param  array<int, string>  $priceFields  price keys copied verbatim from the API row
     * @param  Closure(array<string, mixed>, int): array<string, mixed>  $trailingFields  row tail appended after date_updated; receives the API row and the terminal id
     * @param  string|Closure(array<string, mixed>): ?string  $gameVersion  constant version code, or a per-row resolver
     * @param  array<int, string>  $optionalFields  keys copied with a null fallback, inserted after the price fields
     * @param  bool  $rowsCarryTerminalCode  per-entity endpoints include terminal_code, bulk endpoints do not
     * @return array<int, array<string, mixed>>
     */
    private function mapTerminalPrices(
        Collection $prices,
        TerminalLocationMapper $mapper,
        Collection $locationDataLookup,
        array $priceFields,
        Closure $trailingFields,
        string|Closure $gameVersion,
        array $optionalFields = [],
        bool $rowsCarryTerminalCode = false,
    ): array {
        $locationMapping = $mapper->mapping;

        return $prices
            ->unique('id_terminal')
            ->map(function (array $p) use ($locationMapping, $mapper, $locationDataLookup, $priceFields, $trailingFields, $gameVersion, $optionalFields, $rowsCarryTerminalCode): array {
                $terminalId = (int) $p['id_terminal'];
                $locationUuid = $locationMapping->get($terminalId);

                $row = [
                    'terminal_id' => $terminalId,
                    'terminal_code' => $rowsCarryTerminalCode
                        ? ($p['terminal_code'] ?? $mapper->terminalCodes()->get($terminalId))
                        : $mapper->terminalCodes()->get($terminalId),
                    'terminal_name' => $p['terminal_name'],
                    'starmap_location_uuid' => $locationUuid,
                    'starmap_location_data_id' => $locationUuid !== null
                        ? $locationDataLookup->get($locationUuid)
                        : null,
                ];

                foreach ($priceFields as $priceField) {
                    $row[$priceField] = $p[$priceField];
                }

                foreach ($optionalFields as $optionalField) {
                    $row[$optionalField] = $p[$optionalField] ?? null;
                }

                $row['game_version'] = is_string($gameVersion) ? $gameVersion : $gameVersion($p);
                $row['date_updated'] = Carbon::createFromTimestamp((int) $p['date_modified'])->toIso8601String();

                return [...$row, ...$trailingFields($p, $terminalId)];
            })
            ->values()
            ->toArray();
    }
}
