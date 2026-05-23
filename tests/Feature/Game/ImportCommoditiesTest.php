<?php

declare(strict_types=1);

use App\Models\Game\Commodity\Commodity;
use Illuminate\Console\Command;
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
        ->and($commodity->data->get('Name'))->toBe('Agricium (Ore)');
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
