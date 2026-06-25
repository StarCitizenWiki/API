<?php

declare(strict_types=1);

use App\Models\Game\Faction;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;

beforeEach(function (): void {
    $this->gameVersion = createDefaultGameVersion();
});

it('shows mission with full faction data and reputation ladder', function (): void {
    ['faction' => $faction, 'standings' => $standings] = createFactionWithReputationLadder();

    $missionData = createMissionWithFaction($this->gameVersion, $faction);

    $response = $this->getJson("/api/missions/{$missionData->mission->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.faction.name', 'Nine Tails')
        ->assertJsonPath('data.faction.has_reputation', true)
        ->assertJsonPath('data.faction.reputation_ladder.scope_name', 'FactionReputation');

    $responseStandings = $response->json('data.faction.reputation_ladder.standings');
    expect($responseStandings)->toHaveCount(3)
        ->and($responseStandings[0])->toBe(['name' => 'Hostile', 'display_name' => $standings[0]->display_name, 'min_reputation' => -1])
        ->and($responseStandings[2])->toBe(['name' => 'Allied', 'display_name' => $standings[2]->display_name, 'min_reputation' => 50000]);
});

it('shows mission with faction without reputation ladder', function (): void {
    $faction = Faction::factory()->create([
        'name' => 'No Rep Faction',
        'has_reputation' => false,
    ]);

    $missionData = createMissionWithFaction($this->gameVersion, $faction);

    $response = $this->getJson("/api/missions/{$missionData->mission->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.faction.name', 'No Rep Faction')
        ->assertJsonPath('data.faction.has_reputation', false)
        ->assertJsonPath('data.faction.reputation_ladder', null);
});

it('shows mission without faction', function (): void {
    $missionData = createMissionWithFaction($this->gameVersion, Faction::factory()->create());

    // Override faction_id to null
    $missionData->update(['faction_id' => null]);

    $response = $this->getJson("/api/missions/{$missionData->mission->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.faction', null);
});

it('resolves a mission by slug', function (): void {
    $mission = Mission::factory()->create(['slug' => 'bounty-hunt-target']);
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create(['faction_id' => null, 'title' => 'Bounty Hunt Target']);

    $this->getJson('/api/missions/bounty-hunt-target')
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'Bounty Hunt Target');
});
