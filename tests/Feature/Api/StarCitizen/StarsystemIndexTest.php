<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\StarCitizen\Starmap\Starsystem;

it('filters starsystems by code, name, status, and type', function (): void {
    GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $target = Starsystem::factory()->create([
        'code' => 'SOL',
        'name' => 'Sol',
        'status' => 'ACTIVE',
        'type' => 'SYSTEM',
    ]);

    Starsystem::factory()->create([
        'code' => 'BETA',
        'name' => 'Beta Prime',
        'status' => 'INACTIVE',
        'type' => 'NEBULA',
    ]);

    $response = $this->getJson(route('starsystems.index', [
        'filter' => [
            'code' => 'SOL',
            'name' => 'Sol',
            'status' => 'ACTIVE',
            'type' => 'SYSTEM',
        ],
    ]));

    $response->assertSuccessful();

    $ids = collect($response->json('data'))->pluck('id')->all();

    expect($ids)->toBe([$target->cig_id]);
});

it('sorts starsystems by aggregated population', function (): void {
    GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $low = Starsystem::factory()->create(['aggregated_population' => 10.0]);
    $mid = Starsystem::factory()->create(['aggregated_population' => 50.0]);
    $high = Starsystem::factory()->create(['aggregated_population' => 100.0]);

    $response = $this->getJson(route('starsystems.index', [
        'sort' => 'aggregated_population',
    ]));

    $response->assertSuccessful();

    $ids = collect($response->json('data'))->pluck('id')->all();

    expect($ids)->toBe([$low->cig_id, $mid->cig_id, $high->cig_id]);
});
