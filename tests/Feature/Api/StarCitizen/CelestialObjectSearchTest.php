<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\StarCitizen\Starmap\CelestialObject;
use App\Models\StarCitizen\Starmap\Starsystem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('searches celestial objects by text query without bigint cast errors', function (): void {
    GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $starsystem = Starsystem::factory()->create(['cig_id' => 12001]);

    $matchingObject = CelestialObject::factory()->create([
        'cig_id' => 42001,
        'starsystem_id' => $starsystem->cig_id,
        'name' => 'FLIGHT MODEL TEST',
        'code' => 'FLTMDL',
    ]);

    CelestialObject::factory()->create([
        'cig_id' => 42002,
        'starsystem_id' => $starsystem->cig_id,
        'name' => 'ASTEROID BELT',
        'code' => 'ASTRO',
    ]);

    $response = $this->postJson(route('celestial-objects.search'), [
        'query' => 'flight model',
    ]);

    $response->assertSuccessful()
        ->assertHeader('Deprecated', 'true');

    expect($response->json('meta.deprecated'))->toBeTrue()
        ->and(collect($response->json('data'))->pluck('id'))->toContain($matchingObject->cig_id);
});

it('searches celestial objects by numeric cig id', function (): void {
    GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $starsystem = Starsystem::factory()->create(['cig_id' => 12011]);

    $targetObject = CelestialObject::factory()->create([
        'cig_id' => 42011,
        'starsystem_id' => $starsystem->cig_id,
        'name' => 'TARGET OBJECT',
        'code' => 'TGT01',
    ]);

    CelestialObject::factory()->create([
        'cig_id' => 42012,
        'starsystem_id' => $starsystem->cig_id,
        'name' => 'OTHER OBJECT',
        'code' => 'OTH01',
    ]);

    $response = $this->postJson(route('celestial-objects.search'), [
        'query' => (string) $targetObject->cig_id,
    ]);

    $response->assertSuccessful();

    expect(collect($response->json('data'))->pluck('id'))
        ->toContain($targetObject->cig_id);
});
