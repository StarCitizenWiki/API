<?php

declare(strict_types=1);

use App\Models\Game\Faction;
use App\Models\Game\GameVersion;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;

beforeEach(function (): void {
    $this->version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);
});

it('resolves location by slug', function (): void {
    $location = StarmapLocation::factory()->create(['slug' => 'area18']);
    StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->version, 'gameVersion')
        ->create([
            'name' => 'Area18',
            'system' => 'Stanton',
            'type_name' => 'LandingZone',
            'data' => ['Type' => ['Classification' => 'Landing Zone']],
        ]);

    $response = $this->get('/locations/area18');

    $response->assertSuccessful();
});

it('shows mission count in overview stats on location show page', function (): void {
    $location = StarmapLocation::factory()->create();
    StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->version, 'gameVersion')
        ->create([
            'name' => 'Area18',
            'system' => 'Stanton',
            'type_name' => 'LandingZone',
            'data' => ['Type' => ['Classification' => 'Landing Zone']],
        ]);

    $response = $this->get('/locations/'.$location->uuid);

    $response->assertSuccessful();
    $response->assertSee('Missions');
    $response->assertSee('data-testid="starmap-location-quick-facts"', false);
});

it('shows missions section when location has missions', function (): void {
    $location = StarmapLocation::factory()->create();
    $locationData = StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->version, 'gameVersion')
        ->create([
            'name' => 'Area18',
            'system' => 'Stanton',
            'type_name' => 'LandingZone',
            'data' => ['Type' => ['Classification' => 'Landing Zone']],
        ]);

    $faction = Faction::factory()->create(['name' => 'Nine Tails']);

    $mission = Mission::factory()->create();
    $missionData = MissionData::factory()
        ->for($mission, 'mission')
        ->for($this->version, 'gameVersion')
        ->for($faction, 'faction')
        ->create([
            'title' => 'Bounty Hunt Target',
            'mission_type' => 'Bounty Hunter',
            'illegal' => true,
            'has_combat' => true,
            'reward_min' => 5000,
            'reward_max' => 10000,
            'reward_currency' => 'aUEC',
        ]);

    $locationData->missions()->attach($missionData->id, ['purpose' => 'availability']);

    $response = $this->get('/locations/'.$location->uuid);

    $response->assertSuccessful();
    $response->assertSee('data-testid="starmap-location-missions-section"', false);
    $response->assertSee('data-testid="starmap-location-missions"', false);
    $response->assertSee('Bounty Hunt Target');
    $response->assertSee('Available at Location');
    $response->assertSee('View all');
    $response->assertSee('missions');
});

it('does not show missions section when location has no missions', function (): void {
    $location = StarmapLocation::factory()->create();
    StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->version, 'gameVersion')
        ->create([
            'name' => 'Empty Outpost',
            'system' => 'Stanton',
            'type_name' => 'Outpost',
            'data' => ['Type' => ['Classification' => 'Manmade']],
        ]);

    $response = $this->get('/locations/'.$location->uuid);

    $response->assertSuccessful();
    $response->assertDontSee('data-testid="starmap-location-missions-section"', false);
});

it('includes link to filtered missions index', function (): void {
    $location = StarmapLocation::factory()->create();
    $locationData = StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->version, 'gameVersion')
        ->create([
            'name' => 'Area18',
            'system' => 'Stanton',
            'type_name' => 'LandingZone',
            'data' => ['Type' => ['Classification' => 'Landing Zone']],
        ]);

    $mission = Mission::factory()->create();
    $missionData = MissionData::factory()
        ->for($mission, 'mission')
        ->for($this->version, 'gameVersion')
        ->create(['title' => 'Test Mission']);

    $locationData->missions()->attach($missionData->id, ['purpose' => 'availability']);

    $response = $this->get('/locations/'.$location->uuid);

    $response->assertSuccessful();
    $response->assertSee('filter[location]='.$location->uuid, false);
});

it('links mission cards to mission show page', function (): void {
    $location = StarmapLocation::factory()->create();
    $locationData = StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->version, 'gameVersion')
        ->create([
            'name' => 'Area18',
            'system' => 'Stanton',
            'type_name' => 'LandingZone',
            'data' => ['Type' => ['Classification' => 'Landing Zone']],
        ]);

    $mission = Mission::factory()->create();
    $missionData = MissionData::factory()
        ->for($mission, 'mission')
        ->for($this->version, 'gameVersion')
        ->for(Faction::factory()->create(['name' => 'Nine Tails']), 'faction')
        ->create([
            'title' => 'Bounty Hunt',
            'mission_type' => 'Bounty Hunter',
            'illegal' => true,
            'has_combat' => true,
        ]);

    $locationData->missions()->attach($missionData->id, ['purpose' => 'availability']);

    $response = $this->get('/locations/'.$location->uuid);

    $response->assertSuccessful();
    $response->assertSee(route('web.missions.show', ['mission' => $mission->slug]), false);
});
