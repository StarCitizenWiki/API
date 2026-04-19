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

it('shows full faction card with reputation ladder on mission page', function (): void {
    $scope = FactionScope::factory()->create([
        'scope_name' => 'FactionReputation',
    ]);
    FactionStanding::factory()->create(['faction_scope_id' => $scope->id, 'name' => 'Hostile', 'display_name' => 'Hostile', 'min_reputation' => -1]);
    FactionStanding::factory()->create(['faction_scope_id' => $scope->id, 'name' => 'Neutral', 'display_name' => 'Neutral', 'min_reputation' => 0]);
    FactionStanding::factory()->create(['faction_scope_id' => $scope->id, 'name' => 'Allied', 'display_name' => 'Allied', 'min_reputation' => 50000]);

    $faction = Faction::factory()->create([
        'name' => 'Nine Tails',
        'faction_type' => 'Unlawful',
        'lawful' => false,
        'headquarters' => 'Grim HEX',
        'area' => 'Stanton',
        'focus' => 'Piracy',
        'founded' => '2872',
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
        ->create(['faction_id' => $faction->id, 'title' => 'Nine Tails Heist']);

    $response = $this->get("/missions/{$mission->uuid}");

    $response->assertSuccessful()
        ->assertSee('Faction')
        ->assertSee('Unlawful')
        ->assertSee('Grim HEX')
        ->assertSee('FactionReputation')
        ->assertSee('Hostile')
        ->assertSee('Neutral')
        ->assertSee('Allied')
        ->assertSee('50,000');
});

it('shows faction card without ladder when faction has no reputation', function (): void {
    $faction = Faction::factory()->create([
        'name' => 'Civilians',
        'faction_type' => 'Lawful',
        'lawful' => true,
        'has_reputation' => false,
    ]);

    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create(['faction_id' => $faction->id, 'title' => 'Simple Mission']);

    $response = $this->get("/missions/{$mission->uuid}");

    $response->assertSuccessful()
        ->assertSee('Faction')
        ->assertSee('Lawful')
        ->assertDontSee('Rank');
});

it('does not show faction card when mission has no faction', function (): void {
    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create(['faction_id' => null, 'title' => 'No Faction Mission']);

    $response = $this->get("/missions/{$mission->uuid}");

    $response->assertSuccessful()
        ->assertDontSee('Faction</h2>');
});
