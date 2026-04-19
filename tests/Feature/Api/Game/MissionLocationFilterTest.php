<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);
});

it('filters missions by starmap location uuid', function (): void {
    $location = StarmapLocation::factory()->create();
    $locationData = StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->version, 'gameVersion')
        ->create();

    $mission = Mission::factory()->create();
    $matchingMission = MissionData::factory()
        ->forVersion($this->version)
        ->forMission($mission)
        ->create(['title' => 'At Location']);

    $matchingMission->starmapLocations()->attach($locationData->id, ['purpose' => 'availability']);

    $otherMission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->version)
        ->forMission($otherMission)
        ->create(['title' => 'Elsewhere']);

    $response = $this->getJson('/api/missions?filter[location]='.$location->uuid);

    $response->assertSuccessful();
    $titles = collect($response->json('data'))->pluck('title');
    expect($titles)->toContain('At Location')
        ->and($titles)->not->toContain('Elsewhere');
});

it('returns empty when location uuid has no missions', function (): void {
    $location = StarmapLocation::factory()->create();
    StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->version, 'gameVersion')
        ->create();

    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->version)
        ->forMission($mission)
        ->create(['title' => 'Unrelated']);

    $response = $this->getJson('/api/missions?filter[location]='.$location->uuid);

    $response->assertSuccessful();
    expect($response->json('data'))->toHaveCount(0);
});

it('returns missions from all purpose types for a location', function (): void {
    $location = StarmapLocation::factory()->create();
    $locationData = StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->version, 'gameVersion')
        ->create();

    $missionA = Mission::factory()->create();
    $mdA = MissionData::factory()->forVersion($this->version)->forMission($missionA)->create(['title' => 'Available']);
    $mdA->starmapLocations()->attach($locationData->id, ['purpose' => 'availability']);

    $missionB = Mission::factory()->create();
    $mdB = MissionData::factory()->forVersion($this->version)->forMission($missionB)->create(['title' => 'Destination']);
    $mdB->starmapLocations()->attach($locationData->id, ['purpose' => 'Destination']);

    $response = $this->getJson('/api/missions?filter[location]='.$location->uuid);

    $response->assertSuccessful();
    $titles = collect($response->json('data'))->pluck('title');
    expect($titles)->toContain('Available')
        ->and($titles)->toContain('Destination');
});
