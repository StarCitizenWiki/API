<?php

declare(strict_types=1);

use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Resource\ResourceData;
use App\Models\Game\Resource\ResourceLocation;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

it('fails when the game version does not exist', function (): void {
    Storage::fake('scunpacked');

    $this->artisan('game:import-resource-data', ['version' => 'missing'])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('Game version "missing" does not exist. Please create it first.');
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

it('runs all three import steps in order', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create(['code' => '4.0.1-LIVE']);

    StarmapLocationData::factory()->create([
        'starmap_location_id' => StarmapLocation::factory()->create()->id,
        'game_version_id' => $version->id,
    ]);

    $commodityUuid = fake()->uuid();
    Storage::disk('scunpacked')->put('resources/commodities.json', json_encode([[
        'UUID' => $commodityUuid,
        'Key' => 'Quartz',
        'Name' => 'Quartz',
    ]], JSON_THROW_ON_ERROR));

    $resourceUuid = fake()->uuid();
    Storage::disk('scunpacked')->put('resources/resources.json', json_encode([[
        'UUID' => $resourceUuid,
        'Key' => 'Quartz',
        'Name' => 'Quartz',
        'Kind' => 'Mineable',
    ]], JSON_THROW_ON_ERROR));

    $objectUuid = StarmapLocation::first()->uuid;
    Storage::disk('scunpacked')->put('resources/locations.json', json_encode([[
        'Provider' => [
            'UUID' => fake()->uuid(),
            'Name' => 'HPP_Test',
            'PresetFile' => 'hpp_test',
        ],
        'Locations' => [[
            'Key' => 'Stanton4',
            'Object' => $objectUuid,
            'Location' => 'Stanton4',
            'MatchStrategy' => 'tag',
            'System' => 'Stanton',
            'Name' => 'microTech',
            'Type' => 'planet',
        ]],
        'Areas' => [],
        'Groups' => [[
            'GroupName' => 'SpaceShip_Mineables',
            'GroupProbability' => 0.8,
            'Deposits' => [[
                'ResourceUUID' => $resourceUuid,
                'RelativeProbability' => 0.5,
            ]],
        ]],
    ]], JSON_THROW_ON_ERROR));

    $this->artisan('game:import-resource-data', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS);

    expect(Commodity::query()->count())->toBe(1)
        ->and(ResourceData::query()->count())->toBe(1)
        ->and(ResourceLocation::query()->count())->toBe(1);
});

it('returns failure if commodities import fails', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create(['code' => '4.0.2-LIVE']);

    Storage::disk('scunpacked')->put('resources/commodities.json', 'not-json');

    $this->artisan('game:import-resource-data', ['version' => $version->code])
        ->assertExitCode(Command::FAILURE);
});

it('returns failure if resources import fails', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create(['code' => '4.0.3-LIVE']);

    Storage::disk('scunpacked')->put('resources/commodities.json', json_encode([], JSON_THROW_ON_ERROR));
    Storage::disk('scunpacked')->put('resources/resources.json', 'not-json');

    $this->artisan('game:import-resource-data', ['version' => $version->code])
        ->assertExitCode(Command::FAILURE);
});
