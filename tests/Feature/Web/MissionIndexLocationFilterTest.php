<?php

declare(strict_types=1);

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

it('includes location filter in tabulator endpoint config', function (): void {
    $location = StarmapLocation::factory()->create();
    $locationData = StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->version, 'gameVersion')
        ->create([
            'name' => 'Area18',
            'system' => 'Stanton',
            'type_name' => 'LandingZone',
        ]);

    $mission = Mission::factory()->create();
    $missionData = MissionData::factory()
        ->for($mission, 'mission')
        ->for($this->version, 'gameVersion')
        ->create(['title' => 'Test Mission']);

    $locationData->missions()->attach($missionData->id, ['purpose' => 'availability']);

    $response = $this->get('/missions?filter[location]='.$location->uuid);

    $response->assertSuccessful();
    $content = $response->getContent();
    expect($content)->toContain($location->uuid)
        ->and($content)->toContain('filter');
});

it('shows active location filter badge with name and clear link', function (): void {
    $location = StarmapLocation::factory()->create();
    $locationData = StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->version, 'gameVersion')
        ->create([
            'name' => 'Area18',
            'system' => 'Stanton',
            'type_name' => 'LandingZone',
        ]);

    $mission = Mission::factory()->create();
    $missionData = MissionData::factory()
        ->for($mission, 'mission')
        ->for($this->version, 'gameVersion')
        ->create(['title' => 'Test Mission']);

    $locationData->missions()->attach($missionData->id, ['purpose' => 'availability']);

    $response = $this->get('/missions?filter[location]='.$location->uuid);

    $response->assertSuccessful();
    $response->assertSee('data-testid="missions-location-filter-badge"', false);
    $response->assertSee('Filtered by:');
    $response->assertSee('Area18');
    $response->assertSee(route('web.missions.index'), false);
});

it('does not show location filter badge when filter is not active', function (): void {
    $response = $this->get('/missions');

    $response->assertSuccessful();
    $response->assertDontSee('data-testid="missions-location-filter-badge"', false);
    $response->assertDontSee('Filtered by:');
});

it('does not include location filter in endpoint when not provided', function (): void {
    $response = $this->get('/missions');

    $response->assertSuccessful();
    $response->assertDontSee('filter[location]', false);
    $response->assertDontSee('filter%5Blocation%5D', false);
});
