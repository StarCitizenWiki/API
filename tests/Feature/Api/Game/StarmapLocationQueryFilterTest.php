<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->defaultVersion = GameVersion::factory()->create([
        'code' => '4.1.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);
});

it('filters locations by query matching name', function (): void {
    $matchLocation = StarmapLocation::factory()->create();
    StarmapLocationData::factory()
        ->for($matchLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'ArcCorp',
            'system' => 'Stanton',
            'type_name' => 'Planet',
            'data' => [],
        ]);

    $otherLocation = StarmapLocation::factory()->create();
    StarmapLocationData::factory()
        ->for($otherLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'MicroTech',
            'system' => 'Stanton',
            'type_name' => 'Planet',
            'data' => [],
        ]);

    $response = $this->getJson('/api/locations?filter[query]=ArcCorp');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $matchLocation->uuid);
});

it('returns empty when query matches nothing', function (): void {
    $location = StarmapLocation::factory()->create();
    StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Existing Location',
            'system' => 'Stanton',
            'type_name' => 'Planet',
            'data' => [],
        ]);

    $response = $this->getJson('/api/locations?filter[query]=zzznonexistent');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});
