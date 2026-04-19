<?php

declare(strict_types=1);

use App\Models\Game\Faction;
use App\Models\Game\FactionReputationRef;
use App\Models\Game\FactionScope;
use App\Models\Game\FactionStanding;
use App\Models\Game\GameVersion;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);
});

it('shows mission with full faction data and reputation ladder', function (): void {
    $scope = FactionScope::factory()->create([
        'scope_name' => 'FactionReputation',
    ]);
    $standing1 = FactionStanding::factory()->create(['faction_scope_id' => $scope->id, 'name' => 'Hostile', 'min_reputation' => -1]);
    $standing2 = FactionStanding::factory()->create(['faction_scope_id' => $scope->id, 'name' => 'Neutral', 'min_reputation' => 0]);
    $standing3 = FactionStanding::factory()->create(['faction_scope_id' => $scope->id, 'name' => 'Allied', 'min_reputation' => 50000]);

    $faction = Faction::factory()->create([
        'name' => 'Nine Tails',
        'faction_type' => 'Unlawful',
        'lawful' => false,
        'headquarters' => 'Grim HEX',
        'area' => 'Stanton',
        'focus' => 'Piracy',
        'founded' => '2872',
        'leadership' => 'Unknown',
        'has_reputation' => true,
    ]);

    FactionReputationRef::factory()->create([
        'faction_id' => $faction->id,
        'faction_scope_id' => $scope->id,
    ]);

    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create(['faction_id' => $faction->id]);

    $response = $this->getJson("/api/missions/{$mission->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.faction.name', 'Nine Tails')
        ->assertJsonPath('data.faction.faction_type', 'Unlawful')
        ->assertJsonPath('data.faction.lawful', false)
        ->assertJsonPath('data.faction.headquarters', 'Grim HEX')
        ->assertJsonPath('data.faction.area', 'Stanton')
        ->assertJsonPath('data.faction.focus', 'Piracy')
        ->assertJsonPath('data.faction.founded', '2872')
        ->assertJsonPath('data.faction.leadership', 'Unknown')
        ->assertJsonPath('data.faction.has_reputation', true)
        ->assertJsonPath('data.faction.reputation_ladder.scope_name', 'FactionReputation');

    $standings = $response->json('data.faction.reputation_ladder.standings');
    expect($standings)->toHaveCount(3)
        ->and($standings[0])->toBe(['name' => 'Hostile', 'display_name' => $standing1->display_name, 'min_reputation' => -1])
        ->and($standings[2])->toBe(['name' => 'Allied', 'display_name' => $standing3->display_name, 'min_reputation' => 50000]);
});

it('shows mission with faction without reputation ladder', function (): void {
    $faction = Faction::factory()->create([
        'name' => 'No Rep Faction',
        'has_reputation' => false,
    ]);

    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create(['faction_id' => $faction->id]);

    $response = $this->getJson("/api/missions/{$mission->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.faction.name', 'No Rep Faction')
        ->assertJsonPath('data.faction.has_reputation', false)
        ->assertJsonPath('data.faction.reputation_ladder', null);
});

it('shows mission without faction', function (): void {
    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create(['faction_id' => null]);

    $response = $this->getJson("/api/missions/{$mission->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.faction', null);
});
