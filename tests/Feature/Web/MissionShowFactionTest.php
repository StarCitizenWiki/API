<?php

declare(strict_types=1);

use App\Models\Game\Faction;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use App\Support\Format;

beforeEach(function (): void {
    $this->gameVersion = createDefaultGameVersion();
});

it('shows full faction card with reputation ladder on mission page', function (): void {
    ['faction' => $faction] = createFactionWithReputationLadder();

    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create(['faction_id' => $faction->id, 'title' => 'Nine Tails Heist']);

    $response = $this->get("/missions/{$mission->slug}");

    $response->assertSuccessful()
        ->assertSee('Faction')
        ->assertSee('Unlawful')
        ->assertSee('Grim HEX')
        ->assertSee('FactionReputation')
        ->assertSee('Hostile')
        ->assertSee('Neutral')
        ->assertSee('Allied')
        ->assertSee(Format::number(50000));
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

    $response = $this->get("/missions/{$mission->slug}");

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

    $response = $this->get("/missions/{$mission->slug}");

    $response->assertSuccessful()
        ->assertDontSee('Faction</h2>');
});
