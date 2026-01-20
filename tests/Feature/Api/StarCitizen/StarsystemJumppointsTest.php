<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\StarCitizen\Starmap\Affiliation;
use App\Models\StarCitizen\Starmap\CelestialObject;
use App\Models\StarCitizen\Starmap\Jumppoint;
use App\Models\StarCitizen\Starmap\Starsystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);
});

it('returns jumppoints when explicitly included', function (): void {
    $starsystem = Starsystem::factory()->create(['code' => 'SOL']);
    $affiliation = Affiliation::factory()->create();
    $starsystem->affiliation()->attach($affiliation);

    $entryCO = CelestialObject::factory()->create([
        'starsystem_id' => $starsystem->cig_id,
        'code' => 'SOL-1',
        'designation' => 'Sol Star',
        'type' => 'STAR',
    ]);

    $jumppoint = Jumppoint::factory()->create([
        'entry_id' => $entryCO->cig_id,
        'exit_id' => fake()->numberBetween(1000, 999999),
        'name' => 'Terra Jump',
        'size' => 'MEDIUM',
    ]);

    $response = $this->getJson(route('starsystems.show', [
        'code' => 'SOL',
        'include' => 'jumppoints',
    ]));

    $response->assertSuccessful();

    $data = $response->json('data');

    expect($data['jumppoints'])->toHaveCount(1)
        ->and($data['jumppoints'][0]['id'])->toBe($jumppoint->cig_id)
        ->and($data['jumppoints'][0]['name'])->toBe($jumppoint->name)
        ->and($data['affiliation'])->toHaveCount(1);
});

it('does not return jumppoints when not included', function (): void {
    $starsystem = Starsystem::factory()->create(['code' => 'SOL']);

    $entryCO = CelestialObject::factory()->create([
        'starsystem_id' => $starsystem->cig_id,
        'code' => 'SOL-1',
        'designation' => 'Sol Star',
        'type' => 'STAR',
    ]);

    Jumppoint::factory()->create([
        'entry_id' => $entryCO->cig_id,
        'exit_id' => fake()->numberBetween(1000, 999999),
        'name' => 'Terra Jump',
        'size' => 'MEDIUM',
    ]);

    $response = $this->getJson(route('starsystems.show', ['code' => 'SOL']));

    $response->assertSuccessful();

    $data = $response->json('data');

    expect($data)->not->toHaveKey('jumppoints');
});

it('can include jumppoints in index endpoint', function (): void {
    $starsystem = Starsystem::factory()->create(['code' => 'SOL']);

    $entryCO = CelestialObject::factory()->create([
        'starsystem_id' => $starsystem->cig_id,
        'code' => 'SOL-1',
        'designation' => 'Sol Star',
        'type' => 'STAR',
    ]);

    $jumppoint = Jumppoint::factory()->create([
        'entry_id' => $entryCO->cig_id,
        'exit_id' => fake()->numberBetween(1000, 999999),
        'name' => 'Terra Jump',
        'size' => 'MEDIUM',
    ]);

    $response = $this->getJson(route('starsystems.index', [
        'include' => 'jumppoints',
        'page[size]' => 10,
    ]));

    $response->assertSuccessful();

    $data = $response->json('data.0');

    expect($data['jumppoints'])->toHaveCount(1)
        ->and($data['jumppoints'][0]['id'])->toBe($jumppoint->cig_id);
});

it('maintains low query count when including jumppoints', function (): void {
    $starsystem = Starsystem::factory()->create(['code' => 'SOL']);

    $entryCO = CelestialObject::factory()->create([
        'starsystem_id' => $starsystem->cig_id,
        'code' => 'SOL-1',
        'designation' => 'Sol Star',
        'type' => 'STAR',
    ]);

    Jumppoint::factory()->create([
        'entry_id' => $entryCO->cig_id,
        'exit_id' => fake()->numberBetween(1000, 999999),
        'name' => 'Terra Jump',
        'size' => 'MEDIUM',
    ]);

    DB::enableQueryLog();

    $this->getJson(route('starsystems.show', [
        'code' => 'SOL',
        'include' => 'jumppoints',
    ]));

    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    $queryCount = count($queries);

    expect($queryCount)->toBeLessThanOrEqual(7);
});
