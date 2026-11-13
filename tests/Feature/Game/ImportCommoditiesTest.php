<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

it('fails when the commodities file is missing', function (): void {
    Storage::fake('scunpacked');

    $this->artisan('game:import-commodities')
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('resources/commodities.json not found in scunpacked storage.');
});

it('imports commodities and resolves refined versions', function (): void {
    Storage::fake('scunpacked');

    $refinedUuid = fake()->uuid();
    $oreUuid = fake()->uuid();

    $payload = [
        [
            'UUID' => $refinedUuid,
            'Key' => 'Agricium',
            'Name' => 'Agricium',
            'Description' => 'Refined agricium.',
            'RefinedVersionUUID' => null,
            'RefinedVersionName' => null,
            'ValidateDefaultCargoBox' => true,
            'HasDefaultCargoContainers' => true,
            'CargoContainers' => [
                ['UUID' => fake()->uuid(), 'Name' => 'one_eighthSCU', 'Size' => 0.125],
                ['UUID' => fake()->uuid(), 'Name' => 'oneSCU', 'Size' => 1],
                ['UUID' => fake()->uuid(), 'Name' => 'twoSCU', 'Size' => 2],
            ],
            'QualityDistributionUUID' => null,
            'QualityLocationOverrideUUID' => null,
            'Tier' => null,
        ],
        [
            'UUID' => $oreUuid,
            'Key' => 'Ore_Agricium',
            'Name' => 'Agricium (Ore)',
            'Description' => 'Raw agricium ore.',
            'RefinedVersionUUID' => $refinedUuid,
            'RefinedVersionName' => 'Agricium',
            'ValidateDefaultCargoBox' => true,
            'HasDefaultCargoContainers' => true,
            'CargoContainers' => [
                ['UUID' => fake()->uuid(), 'Name' => 'oneSCU', 'Size' => 1],
                ['UUID' => fake()->uuid(), 'Name' => 'twoSCU', 'Size' => 2],
                ['UUID' => fake()->uuid(), 'Name' => 'fourSCU', 'Size' => 4],
            ],
            'QualityDistributionUUID' => '4fb1e7e6-9b98-443a-8409-71b94a6e5d0f',
            'QualityLocationOverrideUUID' => '4867be55-137a-42ee-b41c-84b39ab29d00',
            'Tier' => 'uncommon',
            'Volatility' => 7.5,
            'VolatilityHealthDecayPerSecond' => 1.25,
        ],
    ];

    Storage::disk('scunpacked')->put('resources/commodities.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-commodities')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 2 commodities (2 new, 0 updated). Skipped 0 invalid.');

    $commodity = Commodity::query()->where('uuid', $oreUuid)->first();

    expect($commodity)->not->toBeNull()
        ->and($commodity->key)->toBe('Ore_Agricium')
        ->and($commodity->refined_version_uuid)->toBe($refinedUuid)
        ->and($commodity->refined_version_name)->toBe('Agricium')
        ->and($commodity->refinedVersion?->uuid)->toBe($refinedUuid)
        ->and($commodity->tier)->toBe('uncommon')
        ->and($commodity->box_sizes_scu)->toBe([1, 2, 4])
        ->and($commodity->quality_distribution_uuid)->toBe('4fb1e7e6-9b98-443a-8409-71b94a6e5d0f')
        ->and($commodity->quality_location_override_uuid)->toBe('4867be55-137a-42ee-b41c-84b39ab29d00')
        ->and((float) $commodity->volatility)->toBe(7.5)
        ->and((float) $commodity->volatility_health_decay_per_second)->toBe(1.25)
        ->and(Arr::get($commodity->data, 'Name'))->toBe('Agricium (Ore)');
});

it('does not suffix an existing commodity slug when reimporting the same commodity', function (): void {
    Storage::fake('scunpacked');

    $uuid = fake()->uuid();

    Commodity::query()->create([
        'uuid' => $uuid,
        'key' => 'Aslarite',
        'name' => 'Aslarite',
        'slug' => 'aslarite',
        'description' => 'Existing description',
        'refined_version_uuid' => null,
        'validate_default_cargo_box' => false,
        'has_default_cargo_containers' => false,
        'box_sizes_scu' => [],
        'data' => ['Name' => 'Aslarite'],
    ]);

    Storage::disk('scunpacked')->put('resources/commodities.json', json_encode([[
        'UUID' => $uuid,
        'Key' => 'Aslarite',
        'Name' => 'Aslarite',
        'Description' => 'Updated description',
    ]], JSON_THROW_ON_ERROR));

    $this->artisan('game:import-commodities')
        ->assertExitCode(Command::SUCCESS);

    expect(Commodity::query()->where('uuid', $uuid)->value('slug'))->toBe('aslarite');
});

it('freezes an existing slug even when reimporting would produce a cleaner one', function (): void {
    Storage::fake('scunpacked');

    $uuid = fake()->uuid();

    Commodity::query()->create([
        'uuid' => $uuid,
        'key' => 'Aslarite',
        'name' => 'Aslarite',
        'slug' => 'aslarite-2',
        'description' => 'Existing description',
        'refined_version_uuid' => null,
        'validate_default_cargo_box' => false,
        'has_default_cargo_containers' => false,
        'box_sizes_scu' => [],
        'data' => ['Name' => 'Aslarite'],
    ]);

    Storage::disk('scunpacked')->put('resources/commodities.json', json_encode([[
        'UUID' => $uuid,
        'Key' => 'Aslarite',
        'Name' => 'Aslarite',
        'Description' => 'Updated description',
    ]], JSON_THROW_ON_ERROR));

    $this->artisan('game:import-commodities')
        ->assertExitCode(Command::SUCCESS);

    // A slug is immutable once assigned: a previously suffixed slug is kept
    // rather than regenerated, so indexed URLs stay stable.
    expect(Commodity::query()->where('uuid', $uuid)->value('slug'))->toBe('aslarite-2');
});

it('keeps the slug when the commodity name changes on reimport', function (): void {
    Storage::fake('scunpacked');

    $uuid = fake()->uuid();

    Commodity::query()->create([
        'uuid' => $uuid,
        'key' => 'Agricium',
        'name' => 'Agricium',
        'slug' => 'agricium',
        'description' => 'Existing description',
        'refined_version_uuid' => null,
        'validate_default_cargo_box' => false,
        'has_default_cargo_containers' => false,
        'box_sizes_scu' => [],
        'data' => ['Name' => 'Agricium'],
    ]);

    Storage::disk('scunpacked')->put('resources/commodities.json', json_encode([[
        'UUID' => $uuid,
        'Key' => 'Agricium',
        'Name' => 'Agricium Renamed',
        'Description' => 'Updated description',
    ]], JSON_THROW_ON_ERROR));

    $this->artisan('game:import-commodities')
        ->assertExitCode(Command::SUCCESS);

    $commodity = Commodity::query()->where('uuid', $uuid)->first();

    expect($commodity->name)->toBe('Agricium Renamed')
        ->and($commodity->slug)->toBe('agricium');
});

it('upserts existing commodities when the payload changes', function (): void {
    Storage::fake('scunpacked');

    $uuid = fake()->uuid();

    Commodity::query()->create([
        'uuid' => $uuid,
        'key' => 'Agricium',
        'name' => 'Old Name',
        'description' => 'Old description',
        'refined_version_uuid' => null,
        'validate_default_cargo_box' => false,
        'has_default_cargo_containers' => false,
        'box_sizes_scu' => [1],
        'data' => ['Name' => 'Old Name'],
    ]);

    $payload = [[
        'UUID' => $uuid,
        'Key' => 'Agricium',
        'Name' => 'Updated Agricium',
        'Description' => 'Updated description',
        'RefinedVersionUUID' => null,
        'RefinedVersionName' => null,
        'ValidateDefaultCargoBox' => true,
        'HasDefaultCargoContainers' => true,
        'CargoContainers' => [
            ['UUID' => fake()->uuid(), 'Name' => 'oneSCU', 'Size' => 1],
            ['UUID' => fake()->uuid(), 'Name' => 'twoSCU', 'Size' => 2],
            ['UUID' => fake()->uuid(), 'Name' => 'fourSCU', 'Size' => 4],
        ],
        'QualityDistributionUUID' => null,
        'QualityLocationOverrideUUID' => null,
        'Tier' => null,
    ]];

    Storage::disk('scunpacked')->put('resources/commodities.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-commodities')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 1 commodities (0 new, 1 updated). Skipped 0 invalid.');

    $commodity = Commodity::query()->where('uuid', $uuid)->first();

    expect($commodity)->not->toBeNull()
        ->and($commodity->name)->toBe('Updated Agricium')
        ->and($commodity->validate_default_cargo_box)->toBeTrue()
        ->and($commodity->box_sizes_scu)->toBe([1, 2, 4])
        ->and(Commodity::query()->count())->toBe(1);
});

it('refreshes commodity rarity facets after a commodity import', function (): void {
    Storage::fake('scunpacked');
    GameVersion::factory()->create(['code' => '4.0.0-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);
    $commodity = Commodity::factory()->create(['tier' => 'common']);
    app()->instance('env', 'production');

    expect(array_column($this->getJson('/api/commodities/filters')->assertSuccessful()->json('filters.rarity'), 'value'))->toContain('common');

    Storage::disk('scunpacked')->put('resources/commodities.json', json_encode([[
        'UUID' => $commodity->uuid, 'Key' => $commodity->key, 'Name' => $commodity->name, 'Tier' => 'rare',
    ]], JSON_THROW_ON_ERROR));
    $this->artisan('game:import-commodities')->assertExitCode(Command::SUCCESS);

    $values = array_column($this->getJson('/api/commodities/filters')->assertSuccessful()->json('filters.rarity'), 'value');
    expect($values)->toContain('rare')->not->toContain('common');
});

it('refreshes blueprint ingredient labels after a commodity import', function (): void {
    Storage::fake('scunpacked');
    $version = GameVersion::factory()->create(['code' => '4.0.0-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);
    $commodity = Commodity::factory()->create(['name' => 'Old Ingredient']);
    BlueprintData::factory()->for(Blueprint::factory(), 'blueprint')->for($version, 'gameVersion')
        ->withIngredients($commodity)->create(['data' => ['tiers' => []]]);
    app()->instance('env', 'production');

    $before = $this->getJson('/api/blueprints/filters')->assertSuccessful()->json('filters');
    expect(collect($before['ingredient.uuid'])->firstWhere('value', $commodity->uuid)['label'])->toBe('Old Ingredient');

    Storage::disk('scunpacked')->put('resources/commodities.json', json_encode([[
        'UUID' => $commodity->uuid, 'Key' => $commodity->key, 'Name' => 'New Ingredient',
    ]], JSON_THROW_ON_ERROR));
    $this->artisan('game:import-commodities')->assertExitCode(Command::SUCCESS);

    $after = $this->getJson('/api/blueprints/filters')->assertSuccessful()->json('filters');
    expect(collect($after['ingredient.uuid'])->firstWhere('value', $commodity->uuid)['label'])->toBe('New Ingredient');
});
