<?php

declare(strict_types=1);

namespace App\Support\UEXcorp;

use App\Models\Game\StarmapLocationData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class TerminalLocationMapper
{
    private ?Collection $mapping = null;

    /** @var Collection<int, string> terminal_id => code */
    private Collection $terminalCodes;

    public function __construct(private readonly ?int $gameVersionId = null) {}

    /**
     * @return Collection<int, string> terminal_id => starmap_location_uuid
     */
    public function getMapping(): Collection
    {
        if ($this->mapping !== null) {
            return $this->mapping;
        }

        $terminals = $this->fetchTerminals();

        if ($terminals->isEmpty()) {
            $this->mapping = collect();
            $this->terminalCodes = collect();

            return $this->mapping;
        }

        $starmapNames = $this->loadStarmapNameIndex();

        $overrides = collect(config('uexcorp.terminal_location_overrides', []));
        $lowerMap = $starmapNames->mapWithKeys(fn (string $uuid, string $name): array => [strtolower($name) => $uuid]);

        $this->mapping = collect();
        $this->terminalCodes = collect();

        foreach ($terminals as $terminal) {
            $displayname = $terminal['displayname'] ?? null;

            if ($displayname === null || $displayname === '') {
                continue;
            }

            $uuid = $this->resolveUuid($displayname, $starmapNames, $overrides, $lowerMap);
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

    public function resolveUuidForTerminal(int $terminalId): ?string
    {
        return $this->getMapping()->get($terminalId);
    }

    public function getTerminalCode(int $terminalId): ?string
    {
        $this->getMapping();

        return $this->terminalCodes->get($terminalId);
    }

    /**
     * @return Collection<string, string> name => uuid
     */
    private function loadStarmapNameIndex(): Collection
    {
        return StarmapLocationData::query()
            ->whereNotNull('name')
            ->join('game_starmap_locations', 'game_starmap_location_data.starmap_location_id', '=', 'game_starmap_locations.id')
            ->when($this->gameVersionId !== null, fn ($q) => $q->where('game_starmap_location_data.game_version_id', $this->gameVersionId))
            ->pluck('game_starmap_locations.uuid', 'game_starmap_location_data.name');
    }

    /**
     * @param  Collection<string, string>  $starmapNames  name => uuid
     * @param  Collection<string, string>  $overrides  displayname => starmap_name
     * @param  Collection<string, string>  $lowerMap  strtolower(name) => uuid
     */
    private function resolveUuid(string $displayname, Collection $starmapNames, Collection $overrides, Collection $lowerMap): ?string
    {
        if ($overrides->has($displayname)) {
            return $starmapNames->get($overrides->get($displayname));
        }

        if ($starmapNames->has($displayname)) {
            return $starmapNames->get($displayname);
        }

        $lower = strtolower($displayname);
        if ($lowerMap->has($lower)) {
            return $lowerMap->get($lower);
        }

        foreach ($starmapNames as $name => $uuid) {
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
