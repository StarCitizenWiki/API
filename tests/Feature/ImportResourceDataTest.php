<?php

declare(strict_types=1);

use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Resource\ResourceData;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

it('fails when the game version does not exist', function (): void {
    assertFailsOnMissingVersion('game:import-resource-data');
});

it('fails when starmap data does not exist for the version', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create(['code' => '4.0.0-LIVE']);

    Storage::disk('scunpacked')->put('resources/commodities.json', json_encode([], JSON_THROW_ON_ERROR));
    Storage::disk('scunpacked')->put('resources/resources.json', json_encode([], JSON_THROW_ON_ERROR));

    $this->artisan('game:import-resource-data', ['version' => $version->code])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('No starmap data found for this version. Please import starmap data first.');
});

it('short-circuits on commodities failure without running resources or locations', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create(['code' => '4.0.1-LIVE']);

    // commodities.json is invalid JSON -> import-commodities should fail
    Storage::disk('scunpacked')->put('resources/commodities.json', 'not-json');
    // resources.json would succeed on its own, but must not run
    Storage::disk('scunpacked')->put('resources/resources.json', json_encode([[
        'UUID' => fake()->uuid(),
        'Key' => 'Quartz',
        'Name' => 'Quartz',
        'Kind' => 'Mineable',
    ]], JSON_THROW_ON_ERROR));

    $this->artisan('game:import-resource-data', ['version' => $version->code])
        ->assertExitCode(Command::FAILURE);

    // Neither resources nor locations were imported
    expect(ResourceData::query()->count())->toBe(0);
});

it('short-circuits on resources failure without running locations', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create(['code' => '4.0.2-LIVE']);

    // commodities.json is valid but empty -> import-commodities succeeds
    Storage::disk('scunpacked')->put('resources/commodities.json', json_encode([], JSON_THROW_ON_ERROR));
    // resources.json is invalid JSON -> import-resources should fail
    Storage::disk('scunpacked')->put('resources/resources.json', 'not-json');

    $this->artisan('game:import-resource-data', ['version' => $version->code])
        ->assertExitCode(Command::FAILURE);

    // Commodities import succeeded (empty but valid), resources failed, locations not run
    expect(Commodity::query()->count())->toBe(0);
});

it('runs the full pipeline when all sub-commands succeed', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.0.3-LIVE',
        'channel' => 'live',
        'is_default' => true,
    ]);

    StarmapLocationData::factory()->create([
        'starmap_location_id' => StarmapLocation::factory()->create()->id,
        'game_version_id' => $version->id,
    ]);

    Storage::disk('scunpacked')->put('resources/commodities.json', json_encode([[
        'UUID' => fake()->uuid(),
        'Key' => 'Quartz',
        'Name' => 'Quartz',
    ]], JSON_THROW_ON_ERROR));

    Storage::disk('scunpacked')->put('resources/resources.json', json_encode([[
        'UUID' => fake()->uuid(),
        'Key' => 'Quartz',
        'Name' => 'Quartz',
        'Kind' => 'Mineable',
    ]], JSON_THROW_ON_ERROR));

    Storage::disk('scunpacked')->put('resources/locations.json', json_encode([], JSON_THROW_ON_ERROR));

    $this->artisan('game:import-resource-data', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS);

    // All three sub-commands ran without error
    expect(Commodity::query()->count())->toBe(1)
        ->and(ResourceData::query()->count())->toBe(1);
});
