<?php

declare(strict_types=1);

use App\Models\StarCitizen\Starmap\CelestialObject;
use App\Models\StarCitizen\Starmap\Starsystem;

beforeEach(function (): void {
    createDefaultGameVersion();
});

it('starsystem resource generates correct web_url', function (): void {
    $starsystem = Starsystem::factory()->create([
        'code' => 'TESTSYS',
        'cig_id' => 12345,
    ]);

    $response = $this->getJson(route('starsystems.show', ['code' => $starsystem->code]));

    $webUrl = $response->json('data')['web_url'];

    expect($webUrl)->toBe(url('/starmap/systems/TESTSYS'));
});

it('celestial object resource generates correct web_url', function (): void {
    $starsystem = Starsystem::factory()->create([
        'code' => 'TESTSYS',
    ]);

    $celestial = CelestialObject::factory()->create([
        'code' => 'TESTOBJ',
        'cig_id' => 67890,
        'starsystem_id' => $starsystem->id,
    ]);

    $response = $this->getJson(route('celestial-objects.show', ['code' => $celestial->code]));

    $webUrl = $response->json('data')['web_url'];

    expect($webUrl)->toBe(route('web.starmap.celestial-objects.show', ['code' => $celestial->code]));
});