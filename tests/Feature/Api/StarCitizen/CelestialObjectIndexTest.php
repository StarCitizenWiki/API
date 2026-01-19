<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\StarCitizen\Starmap\CelestialObject;
use App\Models\StarCitizen\Starmap\Starsystem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters celestial objects by starsystem, name, designation, and type', function (): void {
    GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $system = Starsystem::factory()->create([
        'cig_id' => 1001,
        'name' => 'Sol',
    ]);

    $target = CelestialObject::factory()->create([
        'starsystem_id' => $system->cig_id,
        'name' => 'Terra',
        'designation' => 'III',
        'type' => 'PLANET',
    ]);

    CelestialObject::factory()->create([
        'starsystem_id' => $system->cig_id,
        'name' => 'Terra Nova',
        'designation' => 'IV',
        'type' => 'MOON',
    ]);

    $response = $this->getJson(route('celestial-objects.index', [
        'include' => 'starsystem',
        'filter' => [
            'starsystem' => 'Sol',
            'name' => 'Terra',
            'designation' => 'III',
            'type' => 'PLANET',
        ],
    ]));

    $response->assertSuccessful();

    $ids = collect($response->json('data'))->pluck('id')->all();

    expect($ids)->toBe([$target->cig_id]);
});

it('sorts celestial objects by sensor population', function (): void {
    GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $low = CelestialObject::factory()->create(['sensor_population' => 1.0]);
    $mid = CelestialObject::factory()->create(['sensor_population' => 5.0]);
    $high = CelestialObject::factory()->create(['sensor_population' => 10.0]);

    $response = $this->getJson(route('celestial-objects.index', [
        'sort' => 'sensor_population',
    ]));

    $response->assertSuccessful();

    $ids = collect($response->json('data'))->pluck('id')->all();

    expect($ids)->toBe([$low->cig_id, $mid->cig_id, $high->cig_id]);
});
