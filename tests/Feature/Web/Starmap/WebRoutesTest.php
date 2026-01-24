<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\StarCitizen\Starmap\CelestialObject;
use App\Models\StarCitizen\Starmap\Starsystem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);
});

it('starsystem web route accepts code parameter', function (): void {
    $starsystem = Starsystem::factory()->create(['code' => 'TESTSYS']);
    $response = $this->get(route('web.starmap.systems.show', ['code' => $starsystem->code]));
    $response->assertSuccessful();
});

it('celestial object web route accepts code parameter', function (): void {
    $celestial = CelestialObject::factory()->create(['code' => 'TESTOBJ']);
    $response = $this->get(route('web.starmap.celestial-objects.show', ['code' => $celestial->code]));
    $response->assertSuccessful();
});
