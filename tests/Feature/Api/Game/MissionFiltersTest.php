<?php

declare(strict_types=1);

use App\Models\Game\Faction;
use App\Models\Game\GameVersion;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;

beforeEach(function (): void {
    $this->version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);
});

it('returns filters matching query-filtered faction missions', function (): void {
    $faction = Faction::factory()->create(['name' => 'Nine Tails']);

    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->version)
        ->forMission($mission)
        ->create([
            'title' => 'Red Alert',
            'description' => 'A dangerous mission',
            'faction_id' => $faction->id,
        ]);

    // Without filter: verify faction appears in facets
    $response = $this->getJson('/api/missions/filters');

    $response->assertSuccessful()
        ->assertJsonStructure(['filters']);
    $factionFilters = collect($response->json('filters.faction'));
    $matched = $factionFilters->first(fn (array $f) => $f['value'] === 'Nine Tails');
    expect($matched)->not->toBeNull()
        ->and($matched['count'])->toBe(1);

    // With query filter: endpoint returns results narrowed by the query
    $filtered = $this->getJson('/api/missions/filters?filter[query]=red%20al');
    $filtered->assertSuccessful()
        ->assertJsonStructure(['filters']);
    $filteredFacets = collect($filtered->json('filters.faction'));
    $filteredMatch = $filteredFacets->first(fn (array $f) => $f['value'] === 'Nine Tails');
    expect($filteredMatch)->not->toBeNull()
        ->and($filteredMatch['count'])->toBe(1);
});

it('returns filters with title filter applied', function (): void {
    $faction = Faction::factory()->create(['name' => 'Outlaws']);

    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->version)
        ->forMission($mission)
        ->create([
            'title' => 'Special Delivery',
            'faction_id' => $faction->id,
        ]);

    // Without filter: verify faction appears in facets
    $response = $this->getJson('/api/missions/filters');

    $response->assertSuccessful()
        ->assertJsonStructure(['filters']);
    $factionFilters = collect($response->json('filters.faction'));
    $matched = $factionFilters->first(fn (array $f) => $f['value'] === 'Outlaws');
    expect($matched)->not->toBeNull()
        ->and($matched['count'])->toBe(1);

    // With title filter: endpoint returns narrowed results without error
    $filtered = $this->getJson('/api/missions/filters?filter[title]=Delivery');
    $filtered->assertSuccessful()
        ->assertJsonStructure(['filters']);
});

it('returns filters with description filter applied', function (): void {
    $faction = Faction::factory()->create(['name' => 'Bounty Hunters']);

    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->version)
        ->forMission($mission)
        ->create([
            'description' => 'Eliminate the target at the outpost',
            'faction_id' => $faction->id,
        ]);

    // Without filter: verify faction appears in facets
    $response = $this->getJson('/api/missions/filters');

    $response->assertSuccessful()
        ->assertJsonStructure(['filters']);
    $factionFilters = collect($response->json('filters.faction'));
    $matched = $factionFilters->first(fn (array $f) => $f['value'] === 'Bounty Hunters');
    expect($matched)->not->toBeNull()
        ->and($matched['count'])->toBe(1);

    // With description filter: endpoint returns narrowed results without error
    $filtered = $this->getJson('/api/missions/filters?filter[description]=target');
    $filtered->assertSuccessful()
        ->assertJsonStructure(['filters']);
});

it('returns filters using star_systems data and accepts system suffix input', function (): void {
    $stantonFaction = Faction::factory()->create(['name' => 'Stanton Contractors']);
    $pyroFaction = Faction::factory()->create(['name' => 'Pyro Contractors']);

    MissionData::factory()
        ->forVersion($this->version)
        ->create([
            'faction_id' => $stantonFaction->id,
            'star_systems' => ['Stanton'],
        ]);

    MissionData::factory()
        ->forVersion($this->version)
        ->create([
            'faction_id' => $pyroFaction->id,
            'star_systems' => ['Pyro'],
        ]);

    $response = $this->getJson('/api/missions/filters?filter[star_system]=Stanton%20System');

    $response->assertSuccessful()
        ->assertJsonStructure(['filters']);

    $factionFilters = collect($response->json('filters.faction'));
    expect($factionFilters->firstWhere('value', 'Stanton Contractors'))
        ->not->toBeNull()
        ->and($factionFilters->firstWhere('value', 'Pyro Contractors'))
        ->toBeNull();

    $starSystemFilters = collect($response->json('filters.star_system'));
    expect($starSystemFilters->firstWhere('value', 'Stanton'))
        ->not->toBeNull();
});
