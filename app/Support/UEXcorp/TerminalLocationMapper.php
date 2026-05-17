<?php

declare(strict_types=1);

namespace App\Support\UEXcorp;

use App\Models\Game\StarmapLocationData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class TerminalLocationMapper
{
    public private(set) ?Collection $mapping = null {
        get {
            if ($this->mapping !== null) {
                return $this->mapping;
            }

            $terminals = $this->fetchTerminals();

            if ($terminals->isEmpty()) {
                $this->mapping = collect();
                $this->terminalCodes = collect();

                return $this->mapping;
            }

            $starmapIndex = $this->loadStarmapIndex();

            $overrides = collect(config('uexcorp.terminal_location_overrides', []));

            $this->mapping = collect();
            $this->terminalCodes = collect();

            foreach ($terminals as $terminal) {
                $displayname = $terminal['displayname'] ?? null;

                if ($displayname === null || $displayname === '') {
                    continue;
                }

                $systemName = $terminal['star_system_name'] ?? null;
                $uuid = $this->resolveUuid($displayname, $systemName, $starmapIndex, $overrides);
                $terminalId = (int) $terminal['id'];

                if ($uuid !== null) {
                    $this->mapping->put($terminalId, $uuid);
                }

                if (isset($terminal['code']) && $terminal['code'] !== '') {
                    $this->terminalCodes->put($terminalId, $terminal['code']);
                }
            }

            $total = $terminals->count();
            $matched = $this->mapping->count();

            Log::info('UEXcorp terminal-to-starmap mapping built', [
                'total_terminals' => $total,
                'matched' => $matched,
                'unmatched' => $total - $matched,
            ]);

            return $this->mapping;
        }
    }

    /** @var Collection<int, string> terminal_id => code */
    private Collection $terminalCodes;

    public function __construct(private readonly ?int $gameVersionId = null) {}

    /**
     * @return Collection<int, string> terminal_id => code
     */
    public function terminalCodes(): Collection
    {
        $this->mapping;

        return $this->terminalCodes;
    }

    /**
     * @return array{by_system: Collection<string, Collection<string, string>>, by_name: Collection<string, string>}
     *                                                                                                               by_system: system_key => (name => uuid), by_name: name => uuid
     */
    private function loadStarmapIndex(): array
    {
        $rows = StarmapLocationData::query()
            ->whereNotNull('name')
            ->join('game_starmap_locations', 'game_starmap_location_data.starmap_location_id', '=', 'game_starmap_locations.id')
            ->when($this->gameVersionId !== null, fn ($q) => $q->where('game_starmap_location_data.game_version_id', $this->gameVersionId))
            ->select([
                'game_starmap_location_data.name',
                'game_starmap_location_data.system',
                'game_starmap_locations.uuid',
            ])
            ->get();

        $byName = $rows->pluck('uuid', 'name');

        $bySystem = $rows
            ->filter(fn ($row) => $row->system !== null && $row->system !== '')
            ->groupBy(fn ($row) => self::normalizeSystemName($row->system))
            ->map(fn ($group) => $group->pluck('uuid', 'name'));

        return ['by_system' => $bySystem, 'by_name' => $byName];
    }

    private static function normalizeSystemName(string $system): string
    {
        return strtolower(rtrim(str_replace(' System', '', $system)));
    }

    /**
     * @param  array{by_system: Collection<string, Collection<string, string>>, by_name: Collection<string, string>}  $starmapIndex
     * @param  Collection<string, string>  $overrides  displayname => starmap_name
     */
    private function resolveUuid(string $displayname, ?string $systemName, array $starmapIndex, Collection $overrides): ?string
    {
        $bySystem = $starmapIndex['by_system'];
        $byName = $starmapIndex['by_name'];

        // Manual terminal overrides
        if ($overrides->has($displayname)) {
            $overrideName = $overrides->get($displayname);

            if ($systemName !== null && $systemName !== '') {
                $uuid = $bySystem->get(strtolower($systemName))?->get($overrideName);
                if ($uuid !== null) {
                    return $uuid;
                }
            }

            return $byName->get($overrideName);
        }

        // Exact terminal name in system
        if ($systemName !== null && $systemName !== '') {
            $uuid = $bySystem->get(strtolower($systemName))?->get($displayname);
            if ($uuid !== null) {
                return $uuid;
            }
        }

        // Case-insensitive terminal name search
        $lower = strtolower($displayname);
        if ($systemName !== null && $systemName !== '') {
            $systemMap = $bySystem->get(strtolower($systemName));

            if ($systemMap !== null) {
                foreach ($systemMap as $name => $uuid) {
                    if (strtolower($name) === $lower) {
                        return $uuid;
                    }
                }
            }
        }

        foreach ($byName as $name => $uuid) {
            if (strtolower($name) === $lower) {
                return $uuid;
            }
        }

        // Last resort fuzzy substring search
        foreach ($byName as $name => $uuid) {
            if (str_contains(strtolower($name), $lower) || str_contains($lower, strtolower($name))) {
                Log::info('UEXcorp terminal matched via fuzzy substring', [
                    'terminal_name' => $displayname,
                    'matched_starmap_name' => $name,
                    'starmap_location_uuid' => $uuid,
                ]);

                return $uuid;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function fetchTerminals(): Collection
    {
        $apiUrl = config('uexcorp.api_url').'/terminals';

        $response = Http::timeout(60)->get($apiUrl);

        if (! $response->successful()) {
            Log::error('UEXcorp terminals API request failed', [
                'status' => $response->status(),
            ]);

            return collect();
        }

        return collect($response->json('data', []));
    }
}
