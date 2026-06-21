<?php

declare(strict_types=1);

use App\Models\Game\Faction;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;

beforeEach(function (): void {
    $this->version = createDefaultGameVersion();
});

it('narrows faction facet when a text filter is applied', function (string $filterParam, string $filterValue, array $matchingData, array $nonMatchingData): void {
    $matchingFaction = Faction::factory()->create(['name' => 'Nine Tails']);
    $otherFaction = Faction::factory()->create(['name' => 'Outsiders']);

    $matchingMission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->version)
        ->forMission($matchingMission)
        ->create(array_merge(['faction_id' => $matchingFaction->id], $matchingData));

    $nonMatchingMission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->version)
        ->forMission($nonMatchingMission)
        ->create(array_merge(['faction_id' => $otherFaction->id], $nonMatchingData));

    // Without filter: both factions appear in the facet.
    $unfiltered = $this->getJson('/api/missions/filters');
    $unfiltered->assertSuccessful()->assertJsonStructure(['filters']);
    $unfilteredFacets = collect($unfiltered->json('filters.faction'));
    expect($unfilteredFacets->firstWhere('value', 'Nine Tails'))->not->toBeNull()
        ->and($unfilteredFacets->firstWhere('value', 'Outsiders'))->not->toBeNull();

    // With filter: only the matching faction remains, the other is excluded.
    $filtered = $this->getJson('/api/missions/filters?'.$filterParam.'='.urlencode($filterValue));
    $filtered->assertSuccessful()->assertJsonStructure(['filters']);
    $filteredFacets = collect($filtered->json('filters.faction'));
    expect($filteredFacets->firstWhere('value', 'Nine Tails'))
        ->not->toBeNull()
        ->and($filteredFacets->firstWhere('value', 'Outsiders'))->toBeNull();
})->with([
    'query' => [
        'filter[query]', 'red al',
        ['title' => 'Red Alert', 'description' => 'A dangerous mission'],
        ['title' => 'Blue Patrol', 'description' => 'Routine sweep'],
    ],
    'title' => [
        'filter[title]', 'Delivery',
        ['title' => 'Special Delivery'],
        ['title' => 'Stakeout'],
    ],
    'description' => [
        'filter[description]', 'target',
        ['description' => 'Eliminate the target at the outpost'],
        ['description' => 'Defend the perimeter'],
    ],
]);

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
