<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->version = createDefaultGameVersion();
});

describe('reputation_scope filter', function (): void {
    function createMissionWithReputation(GameVersion $version, array $reputationGained, array $overrides = []): MissionData
    {
        $mission = Mission::factory()->create();
        $reputationScopes = collect($reputationGained)
            ->pluck('Scope')
            ->filter(static fn (mixed $scope): bool => is_string($scope) && trim($scope) !== '')
            ->unique()
            ->values()
            ->all();

        return MissionData::factory()
            ->forVersion($version)
            ->forMission($mission)
            ->create(array_merge([
                'data' => ['ReputationGained' => $reputationGained],
                'reputation_scopes' => $reputationScopes,
            ], $overrides));
    }

    it('filters by single reputation_scope', function (): void {
        createMissionWithReputation($this->version, [
            ['Faction' => 'Ninetails', 'Scope' => 'FactionReputation', 'Amount' => 100],
        ], ['title' => 'Faction Mission']);

        createMissionWithReputation($this->version, [
            ['Faction' => 'UEE', 'Scope' => 'Hauling', 'Amount' => 50],
        ], ['title' => 'Haul Mission']);

        createMissionWithReputation($this->version, [], ['title' => 'No Rep Mission']);

        $response = $this->getJson('/api/missions?filter[reputation_scope]=FactionReputation');

        $response->assertSuccessful();
        $titles = collect($response->json('data'))->pluck('title');
        expect($titles)->toContain('Faction Mission')
            ->and($titles)->not->toContain('Haul Mission')
            ->and($titles)->not->toContain('No Rep Mission');
    })->skip(fn (): bool => DB::connection()->getDriverName() !== 'pgsql', 'PostgreSQL only test')
        ->group('db-pgsql');

    it('filters by multiple reputation_scope values', function (): void {
        createMissionWithReputation($this->version, [
            ['Scope' => 'FactionReputation', 'Amount' => 100],
        ], ['title' => 'Faction']);

        createMissionWithReputation($this->version, [
            ['Scope' => 'Hauling', 'Amount' => 50],
        ], ['title' => 'Haul']);

        createMissionWithReputation($this->version, [
            ['Scope' => 'Affinity', 'Amount' => 10],
        ], ['title' => 'Affinity']);

        $response = $this->getJson('/api/missions?filter[reputation_scope][]=FactionReputation&filter[reputation_scope][]=Hauling');

        $response->assertSuccessful();
        $titles = collect($response->json('data'))->pluck('title');
        expect($titles)->toContain('Faction')
            ->and($titles)->toContain('Haul')
            ->and($titles)->not->toContain('Affinity');
    })->skip(fn (): bool => DB::connection()->getDriverName() !== 'pgsql', 'PostgreSQL only test')
        ->group('db-pgsql');

    it('returns empty for non-existent reputation_scope', function (): void {
        createMissionWithReputation($this->version, [
            ['Scope' => 'FactionReputation', 'Amount' => 100],
        ], ['title' => 'Exists']);

        $response = $this->getJson('/api/missions?filter[reputation_scope]=NonExistent');

        $response->assertSuccessful();
        $titles = collect($response->json('data'))->pluck('title');
        expect($titles)->not->toContain('Exists');
    })->skip(fn (): bool => DB::connection()->getDriverName() !== 'pgsql', 'PostgreSQL only test')
        ->group('db-pgsql');

    it('includes reputation_scope in filters endpoint', function (): void {
        createMissionWithReputation($this->version, [
            ['Scope' => 'FactionReputation', 'Amount' => 100],
        ]);
        createMissionWithReputation($this->version, [
            ['Scope' => 'Hauling', 'Amount' => 50],
        ]);

        $response = $this->getJson('/api/missions/filters');

        $response->assertSuccessful();
        $scopes = collect($response->json('filters.reputation_scope'));
        expect($scopes)->not->toBeEmpty();

        $scopeValues = $scopes->pluck('value')->all();
        expect($scopeValues)->toContain('FactionReputation')
            ->and($scopeValues)->toContain('Hauling');
    })->skip(fn (): bool => DB::connection()->getDriverName() !== 'pgsql', 'PostgreSQL only test')
        ->group('db-pgsql');

    it('counts missions correctly in filter facets', function (): void {
        createMissionWithReputation($this->version, [
            ['Scope' => 'FactionReputation', 'Amount' => 100],
        ], ['title' => 'A']);

        createMissionWithReputation($this->version, [
            ['Scope' => 'FactionReputation', 'Amount' => 200],
        ], ['title' => 'B']);

        $response = $this->getJson('/api/missions/filters');

        $response->assertSuccessful();
        $scopes = collect($response->json('filters.reputation_scope'));
        $factionRep = $scopes->first(fn (array $item) => $item['value'] === 'FactionReputation');
        expect($factionRep['count'])->toBe(2);
    })->skip(fn (): bool => DB::connection()->getDriverName() !== 'pgsql', 'PostgreSQL only test')
        ->group('db-pgsql');
});

describe('reward_scope filter', function (): void {
    function createMissionData(GameVersion $version, array $overrides = []): MissionData
    {
        $mission = Mission::factory()->create();

        return MissionData::factory()
            ->forVersion($version)
            ->forMission($mission)
            ->create($overrides);
    }

    it('filters by reward_scope column', function (): void {
        createMissionData($this->version, ['reward_scope' => 'Bounty Hunter', 'title' => 'BH Mission']);
        createMissionData($this->version, ['reward_scope' => 'Hauling', 'title' => 'Haul Mission']);

        $response = $this->getJson('/api/missions?filter[reward_scope]=Bounty Hunter');

        $response->assertSuccessful();
        $titles = collect($response->json('data'))->pluck('title');
        expect($titles)->toContain('BH Mission')
            ->and($titles)->not->toContain('Haul Mission');
    });

    it('supports multiple reward_scope values', function (): void {
        createMissionData($this->version, ['reward_scope' => 'Bounty Hunter', 'title' => 'BH']);
        createMissionData($this->version, ['reward_scope' => 'Hauling', 'title' => 'Haul']);
        createMissionData($this->version, ['reward_scope' => 'Salvage', 'title' => 'Salvage']);

        $response = $this->getJson('/api/missions?filter[reward_scope][]=Bounty Hunter&filter[reward_scope][]=Salvage');

        $response->assertSuccessful();
        $titles = collect($response->json('data'))->pluck('title');
        expect($titles)->toContain('BH')
            ->and($titles)->toContain('Salvage')
            ->and($titles)->not->toContain('Haul');
    });

    it('returns empty for non-existent reward_scope', function (): void {
        createMissionData($this->version, ['reward_scope' => 'Bounty Hunter', 'title' => 'BH']);

        $response = $this->getJson('/api/missions?filter[reward_scope]=NonExistent');

        $response->assertSuccessful();
        $titles = collect($response->json('data'))->pluck('title');
        expect($titles)->not->toContain('BH');
    });
});
