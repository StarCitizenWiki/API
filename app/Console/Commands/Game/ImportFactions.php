<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Models\Game\Faction;
use App\Models\Game\FactionReputationRef;
use App\Models\Game\FactionScope;
use App\Models\Game\FactionStanding;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use JsonException;

class ImportFactions extends Command
{
    protected $signature = 'game:import-factions';

    protected $description = 'Import game factions from scunpacked data';

    public function handle(): int
    {
        $files = collect(Storage::disk('scunpacked')->files('factions'))
            ->filter(static fn (string $path): bool => str_ends_with($path, '.json'))
            ->values();

        if ($files->isEmpty()) {
            $this->warn('No faction files found in storage/app/api/scunpacked-data/factions.');

            return self::SUCCESS;
        }

        $imported = 0;
        $skipped = 0;

        foreach ($files as $path) {
            try {
                $contents = Storage::disk('scunpacked')->get($path);
                $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                $this->warn(sprintf('Failed to decode %s: %s', $path, $exception->getMessage()));
                $skipped++;

                continue;
            }

            if (! is_array($data)) {
                $skipped++;

                continue;
            }

            $name = $this->resolveName($data);

            if ($name === null || str_contains($name, 'UNINITIALIZED')) {
                $skipped++;

                continue;
            }

            $hasReputation = isset($data['Reputation']) && is_array($data['Reputation']);

            if ($hasReputation) {
                DB::transaction(fn () => $this->importReputationFaction($data, $name));
            } else {
                DB::transaction(fn () => $this->importSimpleFaction($data, $name));
            }

            $imported++;
        }

        $this->info(sprintf('Imported %d factions. Skipped %d.', $imported, $skipped));

        return self::SUCCESS;
    }

    private function resolveName(array $data): ?string
    {
        if (isset($data['Reputation']['DisplayName']) && is_string($data['Reputation']['DisplayName'])) {
            $name = trim($data['Reputation']['DisplayName']);

            if ($name !== '') {
                return $name;
            }
        }

        if (isset($data['Name']) && is_string($data['Name'])) {
            $name = trim($data['Name']);

            if ($name !== '') {
                return $name;
            }
        }

        return null;
    }

    private function buildFactionAttributes(array $data, string $name, bool $hasReputation, array $properties = [], array $reputation = []): array
    {
        $headquarters = substr($properties['Headquarters'] ?? '', 0, 255);

        return [
            'name' => $name,
            'description' => $data['Description'] ?? $properties['Description'] ?? null,
            'default_reaction' => $data['DefaultReaction'] ?? 'Neutral',
            'faction_type' => $data['FactionType'] ?? 'Lawful',
            'able_to_arrest' => (bool) ($data['AbleToArrest'] ?? false),
            'polices_lawful_trespass' => (bool) ($data['PolicesLawfulTrespass'] ?? false),
            'polices_criminality' => (bool) ($data['PolicesCriminality'] ?? false),
            'no_legal_rights' => (bool) ($data['NoLegalRights'] ?? false),
            'has_reputation' => $hasReputation,
            'headquarters' => empty($headquarters) ? null : $headquarters,
            'founded' => $properties['Founded'] ?? null,
            'leadership' => $properties['Leadership'] ?? null,
            'area' => $properties['Area'] ?? null,
            'focus' => $properties['Focus'] ?? null,
            'lawful' => isset($properties['Lawful']) ? (bool) $properties['Lawful'] : null,
            'sort_order_scope' => $reputation['Context']['SortOrderScope'] ?? null,
            'is_npc' => (bool) ($reputation['IsNpc'] ?? false),
            'hide_in_delphi_app' => (bool) ($reputation['HideInDelphiApp'] ?? false),
        ];
    }

    private function importSimpleFaction(array $data, string $name): void
    {
        Faction::query()->updateOrCreate(
            ['uuid' => $data['UUID']],
            $this->buildFactionAttributes($data, $name, false),
        );
    }

    private function importReputationFaction(array $data, string $name): void
    {
        $reputation = $data['Reputation'];
        $properties = $reputation['Properties'] ?? [];

        if (isset($reputation['Context']['PrimaryScope']) && is_array($reputation['Context']['PrimaryScope'])) {
            $this->upsertScopeWithStandings($reputation['Context']['PrimaryScope']);
        }

        if (isset($reputation['Hostility']['Scope']) && is_array($reputation['Hostility']['Scope'])) {
            $this->upsertScopeWithStandings($reputation['Hostility']['Scope']);
        }

        $hostilityScopeUuid = $reputation['Hostility']['ScopeUUID']
            ?? $reputation['Hostility']['Scope']['UUID']
            ?? null;

        if (isset($reputation['Hostility']['Standing']) && is_array($reputation['Hostility']['Standing']) && $hostilityScopeUuid !== null) {
            $this->upsertStanding($reputation['Hostility']['Standing'], $hostilityScopeUuid);
        }

        $faction = Faction::query()->updateOrCreate(
            ['uuid' => $data['UUID']],
            $this->buildFactionAttributes($data, $name, true, $properties, $reputation),
        );

        $factionScopeUuid = $hostilityScopeUuid
            ?? $reputation['Allied']['ScopeUUID']
            ?? null;

        $factionScope = $factionScopeUuid !== null
            ? FactionScope::query()->where('uuid', $factionScopeUuid)->first()
            : null;

        FactionReputationRef::query()->updateOrCreate(
            ['faction_id' => $faction->id],
            [
                'faction_scope_id' => $factionScope?->id,
            ],
        );
    }

    private function upsertScopeWithStandings(array $scopeData): void
    {
        $scope = FactionScope::query()->updateOrCreate(
            ['uuid' => $scopeData['UUID']],
            [
                'scope_name' => $scopeData['ScopeName'],
                'display_name' => $scopeData['DisplayName'],
                'reputation_ceiling' => (int) ($scopeData['ReputationCeiling'] ?? 0),
                'initial_reputation' => (int) ($scopeData['InitialReputation'] ?? 0),
            ],
        );

        $standings = $scopeData['Standings'] ?? [];

        if (! is_array($standings)) {
            return;
        }

        foreach ($standings as $standing) {
            if (! is_array($standing)) {
                continue;
            }

            $this->upsertStandingForScope($standing, $scope->id);
        }
    }

    private function upsertStanding(array $standingData, string $scopeUuid): void
    {
        $scope = FactionScope::query()->where('uuid', $scopeUuid)->first();

        if ($scope === null) {
            return;
        }

        $this->upsertStandingForScope($standingData, $scope->id);
    }

    private function upsertStandingForScope(array $standingData, int $scopeId): void
    {
        FactionStanding::query()->updateOrCreate(
            ['uuid' => $standingData['UUID']],
            [
                'faction_scope_id' => $scopeId,
                'name' => $standingData['Name'],
                'display_name' => $standingData['DisplayName'] ?? null,
                'min_reputation' => (int) ($standingData['MinReputation'] ?? 0),
                'drift_reputation' => (int) ($standingData['DriftReputation'] ?? 0),
                'drift_time_hours' => (int) ($standingData['DriftTimeHours'] ?? 0),
                'gated' => (bool) ($standingData['Gated'] ?? false),
            ],
        );
    }
}
