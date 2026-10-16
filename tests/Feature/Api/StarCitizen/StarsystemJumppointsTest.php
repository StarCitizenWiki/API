<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\StarCitizen\Starmap\Affiliation;
use App\Models\StarCitizen\Starmap\CelestialObject;
use App\Models\StarCitizen\Starmap\Jumppoint;
use App\Models\StarCitizen\Starmap\Starsystem;
use Illuminate\Support\Facades\DB;

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

    $exitCO = CelestialObject::factory()->create([
        'starsystem_id' => $starsystem->cig_id,
        'code' => 'SOL-2',
        'designation' => 'Terra Star',
        'type' => 'PLANET',
    ]);

    $jumppoint = Jumppoint::factory()->create([
        'entry_id' => $entryCO->cig_id,
        'exit_id' => $exitCO->cig_id,
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
        ->and($data['jumppoints'][0]['entry']['code'])->toBe($entryCO->code)
        ->and($data['jumppoints'][0]['exit']['code'])->toBe($exitCO->code)
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

it('builds jumppoint system api urls from the starsystem code', function (): void {
    $entrySystem = Starsystem::factory()->create(['code' => 'ELLIS']);
    $exitSystem = Starsystem::factory()->create(['code' => 'NYX']);

    $entryCO = CelestialObject::factory()->create([
        'starsystem_id' => $entrySystem->cig_id,
        'code' => 'ELLIS.JUMPPOINTS.NEXUS',
        'type' => 'JUMPPOINT',
    ]);

    $exitCO = CelestialObject::factory()->create([
        'starsystem_id' => $exitSystem->cig_id,
        'code' => 'NYX.JUMPPOINTS.BREMEN',
        'type' => 'JUMPPOINT',
    ]);

    Jumppoint::factory()->create([
        'entry_id' => $entryCO->cig_id,
        'exit_id' => $exitCO->cig_id,
    ]);

    $response = $this->getJson(route('starsystems.show', [
        'code' => 'ELLIS',
        'include' => 'jumppoints',
    ]));

    $response->assertSuccessful()
        ->assertJsonPath('data.jumppoints.0.entry.system_api_url', route('starsystems.show', ['code' => 'ELLIS']))
        ->assertJsonPath('data.jumppoints.0.exit.system_api_url', route('starsystems.show', ['code' => 'NYX']));

    expect($response->json('data.jumppoints.0.entry.system_api_url'))
        ->toContain('ELLIS')
        ->not->toContain((string) $entrySystem->cig_id);
});

it('keeps the query count flat as jumppoints grow', function (): void {
    $system = Starsystem::factory()->create(['code' => 'FLATJP']);

    $createJumppoint = function () use ($system): void {
        $entryCO = CelestialObject::factory()->create(['starsystem_id' => $system->cig_id]);
        $exitCO = CelestialObject::factory()->create(['starsystem_id' => $system->cig_id]);

        Jumppoint::factory()->create([
            'entry_id' => $entryCO->cig_id,
            'exit_id' => $exitCO->cig_id,
        ]);
    };

    $createJumppoint();

    DB::enableQueryLog();

    // Warm the languages cache so the measured window only contains row-dependent queries.
    $this->getJson(route('starsystems.show', ['code' => 'FLATJP', 'include' => 'jumppoints']))->assertSuccessful();

    DB::flushQueryLog();
    $this->getJson(route('starsystems.show', ['code' => 'FLATJP', 'include' => 'jumppoints']))->assertSuccessful();
    $before = collect(DB::getQueryLog())->count();

    $createJumppoint();
    $createJumppoint();
    $createJumppoint();

    DB::flushQueryLog();
    $this->getJson(route('starsystems.show', ['code' => 'FLATJP', 'include' => 'jumppoints']))->assertSuccessful();
    $after = collect(DB::getQueryLog())->count();

    DB::disableQueryLog();

    expect($after)->toBe($before);
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
        ->and($data['jumppoints'][0]['id'])->toBe($jumppoint->cig_id)
        ->and($data['jumppoints'][0]['entry']['code'])->toBe($entryCO->code);
});
