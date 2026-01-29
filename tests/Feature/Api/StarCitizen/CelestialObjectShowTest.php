<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\StarCitizen\Starmap\CelestialObject;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a celestial object when the code case differs', function (): void {
    GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $celestialObject = CelestialObject::factory()->create([
        'code' => 'MIN.MOONS.MIN1d',
        'name' => 'Min 1d',
    ]);

    $response = $this->getJson(route('celestial-objects.show', [
        'code' => 'MIN.MOONS.MIN1D',
    ]));

    $response->assertSuccessful();

    expect($response->json('data.id'))->toBe($celestialObject->cig_id);
});
