<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;

beforeEach(function (): void {
    $this->version = createDefaultGameVersion();
});

function createMissionWithReputationAmount(GameVersion $version, ?int $amount, string $title): MissionData
{
    $mission = Mission::factory()->create();

    $reputationGained = $amount !== null
        ? [['Faction' => 'Ninetails', 'Scope' => 'FactionReputation', 'Amount' => $amount]]
        : [];

    return MissionData::factory()
        ->forVersion($version)
        ->forMission($mission)
        ->create([
            'title' => $title,
            'reputation_amount' => $amount,
            'data' => ['ReputationGained' => $reputationGained],
        ]);
}

describe('reputation_amount sort', function (): void {
    it('sorts ascending', function (): void {
        createMissionWithReputationAmount($this->version, 500, 'High Rep');
        createMissionWithReputationAmount($this->version, 100, 'Low Rep');
        createMissionWithReputationAmount($this->version, 250, 'Mid Rep');

        $response = $this->getJson('/api/missions?sort=reputation_amount');

        $response->assertSuccessful();
        $titles = collect($response->json('data'))->pluck('title')->toArray();
        expect($titles)->toBe(['Low Rep', 'Mid Rep', 'High Rep']);
    });

    it('sorts descending', function (): void {
        createMissionWithReputationAmount($this->version, 500, 'High Rep');
        createMissionWithReputationAmount($this->version, 100, 'Low Rep');
        createMissionWithReputationAmount($this->version, 250, 'Mid Rep');

        $response = $this->getJson('/api/missions?sort=-reputation_amount');

        $response->assertSuccessful();
        $titles = collect($response->json('data'))->pluck('title')->toArray();
        expect($titles)->toBe(['High Rep', 'Mid Rep', 'Low Rep']);
    });

    it('places nulls last when sorting descending', function (): void {
        createMissionWithReputationAmount($this->version, 100, 'Has Rep');
        createMissionWithReputationAmount($this->version, null, 'No Rep');
        createMissionWithReputationAmount($this->version, 200, 'More Rep');

        $response = $this->getJson('/api/missions?sort=-reputation_amount');

        $response->assertSuccessful();
        $titles = collect($response->json('data'))->pluck('title')->toArray();
        expect($titles)->toBe(['More Rep', 'Has Rep', 'No Rep']);
    });
});

describe('max_players_per_instance sort', function (): void {
    function createMissionWithMaxPlayers(GameVersion $version, ?int $maxPlayers, string $title): MissionData
    {
        $mission = Mission::factory()->create();

        return MissionData::factory()
            ->forVersion($version)
            ->forMission($mission)
            ->create([
                'title' => $title,
                'max_players_per_instance' => $maxPlayers,
            ]);
    }

    it('sorts ascending', function (): void {
        createMissionWithMaxPlayers($this->version, 10, 'Ten Players');
        createMissionWithMaxPlayers($this->version, 2, 'Two Players');
        createMissionWithMaxPlayers($this->version, 5, 'Five Players');

        $response = $this->getJson('/api/missions?sort=max_players_per_instance');

        $response->assertSuccessful();
        $titles = collect($response->json('data'))->pluck('title')->toArray();
        expect($titles)->toBe(['Two Players', 'Five Players', 'Ten Players']);
    });

    it('sorts descending', function (): void {
        createMissionWithMaxPlayers($this->version, 10, 'Ten Players');
        createMissionWithMaxPlayers($this->version, 2, 'Two Players');
        createMissionWithMaxPlayers($this->version, 5, 'Five Players');

        $response = $this->getJson('/api/missions?sort=-max_players_per_instance');

        $response->assertSuccessful();
        $titles = collect($response->json('data'))->pluck('title')->toArray();
        expect($titles)->toBe(['Ten Players', 'Five Players', 'Two Players']);
    });
});
