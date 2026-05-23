<?php

declare(strict_types=1);

use App\Models\Game\Faction;
use App\Models\Game\FactionReputationRef;
use App\Models\Game\FactionScope;
use App\Models\Game\FactionStanding;
use App\Models\Game\GameVersion;

beforeEach(function (): void {
    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);
});

it('lists factions with default sort by name', function (): void {
    $factionA = Faction::factory()->create(['name' => 'Zeus Dynamics', 'hide_in_delphi_app' => false]);
    $factionB = Faction::factory()->create(['name' => 'Alpha Corp', 'hide_in_delphi_app' => false]);

    $response = $this->getJson('/api/factions');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'Alpha Corp')
        ->assertJsonPath('data.1.name', 'Zeus Dynamics')
        ->assertJsonPath('data.0.uuid', $factionB->uuid)
        ->assertJsonPath('data.0.has_reputation', $factionB->has_reputation)
        ->assertJsonPath('data.0.is_npc', $factionB->is_npc)
        ->assertJsonPath('data.0.link', route('factions.show', ['faction' => $factionB->uuid]));
});

it('excludes factions hidden from delphi app', function (): void {
    Faction::factory()->create(['name' => 'Visible', 'hide_in_delphi_app' => false]);
    Faction::factory()->create(['name' => 'Hidden', 'hide_in_delphi_app' => true]);

    $response = $this->getJson('/api/factions');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Visible');
});

it('filters factions by faction_type', function (): void {
    Faction::factory()->create(['faction_type' => 'Lawful', 'hide_in_delphi_app' => false]);
    Faction::factory()->create(['faction_type' => 'Unlawful', 'hide_in_delphi_app' => false]);

    $response = $this->getJson('/api/factions?filter[faction_type]=Lawful');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.faction_type', 'Lawful');
});

it('filters factions by has_reputation', function (): void {
    Faction::factory()->create(['name' => 'With Rep', 'has_reputation' => true, 'hide_in_delphi_app' => false]);
    Faction::factory()->create(['name' => 'No Rep', 'has_reputation' => false, 'hide_in_delphi_app' => false]);

    $response = $this->getJson('/api/factions?filter[has_reputation]=1');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'With Rep');
});

it('filters factions by lawful', function (): void {
    Faction::factory()->create(['name' => 'Lawful', 'lawful' => true, 'hide_in_delphi_app' => false]);
    Faction::factory()->create(['name' => 'Unlawful', 'lawful' => false, 'hide_in_delphi_app' => false]);

    $response = $this->getJson('/api/factions?filter[lawful]=1');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Lawful');
});

it('filters factions by query (name search)', function (): void {
    Faction::factory()->create(['name' => 'ArcCorp', 'hide_in_delphi_app' => false]);
    Faction::factory()->create(['name' => 'MicroTech', 'hide_in_delphi_app' => false]);

    $response = $this->getJson('/api/factions?filter[query]=arc');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'ArcCorp');
});

it('shows a single faction with full details', function (): void {
    $faction = Faction::factory()->create([
        'name' => 'Test Faction',
        'description' => 'A test faction',
        'default_reaction' => 'Neutral',
        'faction_type' => 'Lawful',
        'hide_in_delphi_app' => false,
        'headquarters' => 'Test HQ',
        'lawful' => true,
    ]);

    $response = $this->getJson("/api/factions/{$faction->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $faction->uuid)
        ->assertJsonPath('data.name', 'Test Faction')
        ->assertJsonPath('data.description', 'A test faction')
        ->assertJsonPath('data.default_reaction', 'Neutral')
        ->assertJsonPath('data.faction_type', 'Lawful')
        ->assertJsonPath('data.headquarters', 'Test HQ')
        ->assertJsonPath('data.lawful', true)
        ->assertJsonPath('data.link', route('factions.show', ['faction' => $faction->uuid]))
        ->assertJsonPath('data.reputation_ladder', null);
});

it('shows a faction with reputation ladder', function (): void {
    $scope = FactionScope::factory()->create([
        'scope_name' => 'FactionReputation',
        'reputation_ceiling' => 95250,
    ]);
    $standing1 = FactionStanding::factory()->create(['faction_scope_id' => $scope->id, 'name' => 'Hostile', 'min_reputation' => -1]);
    $standing2 = FactionStanding::factory()->create(['faction_scope_id' => $scope->id, 'name' => 'Neutral', 'min_reputation' => 0]);

    $faction = Faction::factory()->create([
        'has_reputation' => true,
        'hide_in_delphi_app' => false,
    ]);

    FactionReputationRef::factory()->create([
        'faction_id' => $faction->id,
        'faction_scope_id' => $scope->id,
    ]);

    $response = $this->getJson("/api/factions/{$faction->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.has_reputation', true)
        ->assertJsonPath('data.reputation_ladder.scope_name', 'FactionReputation')
        ->assertJsonPath('data.reputation_ladder.reputation_ceiling', 95250);

    $standings = $response->json('data.reputation_ladder.standings');
    expect($standings)->toHaveCount(2);
});

it('returns 404 for non-existent faction uuid', function (): void {
    $response = $this->getJson('/api/factions/00000000-0000-0000-0000-000000000000');

    $response->assertNotFound();
});

it('returns 404 for faction hidden from delphi app', function (): void {
    $faction = Faction::factory()->create(['hide_in_delphi_app' => true]);

    $response = $this->getJson("/api/factions/{$faction->uuid}");

    $response->assertNotFound();
});

it('sorts factions by name descending', function (): void {
    Faction::factory()->create(['name' => 'Alpha', 'hide_in_delphi_app' => false]);
    Faction::factory()->create(['name' => 'Zeta', 'hide_in_delphi_app' => false]);

    $response = $this->getJson('/api/factions?sort=-name');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Zeta')
        ->assertJsonPath('data.1.name', 'Alpha');
});
