<?php

declare(strict_types=1);

use App\Models\Game\ResourceType;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('fails when the resource types file is missing', function (): void {
    Storage::fake('scunpacked');

    $this->artisan('game:import-resource-types')
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('resource-types.json not found in scunpacked storage.');
});

it('imports resource types and resolves refined versions', function (): void {
    Storage::fake('scunpacked');

    $refinedUuid = fake()->uuid();
    $oreUuid = fake()->uuid();

    $payload = [
        [
            'uuid' => $refinedUuid,
            'key' => 'Agricium',
            'name' => 'Agricium',
            'description' => 'Refined agricium.',
            'refined_version_uuid' => null,
            'validate_default_cargo_box' => true,
            'has_default_cargo_containers' => true,
            'box_sizes_scu' => [0.125, 1, 2],
        ],
        [
            'uuid' => $oreUuid,
            'key' => 'Ore_Agricium',
            'name' => 'Agricium (Ore)',
            'description' => 'Raw agricium ore.',
            'refined_version_uuid' => $refinedUuid,
            'validate_default_cargo_box' => true,
            'has_default_cargo_containers' => true,
            'box_sizes_scu' => [1, 2, 4],
        ],
    ];

    Storage::disk('scunpacked')->put('resource-types.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-resource-types')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 2 resource types (2 new, 0 updated). Skipped 0 invalid.');

    $resourceType = ResourceType::query()->where('uuid', $oreUuid)->first();

    expect($resourceType)->not->toBeNull()
        ->and($resourceType->key)->toBe('Ore_Agricium')
        ->and($resourceType->refined_version_uuid)->toBe($refinedUuid)
        ->and($resourceType->refinedVersion?->uuid)->toBe($refinedUuid)
        ->and($resourceType->box_sizes_scu)->toBe([1, 2, 4])
        ->and($resourceType->data->get('name'))->toBe('Agricium (Ore)');
});

it('upserts existing resource types when the payload changes', function (): void {
    Storage::fake('scunpacked');

    $uuid = fake()->uuid();

    ResourceType::query()->create([
        'uuid' => $uuid,
        'key' => 'Agricium',
        'name' => 'Old Name',
        'description' => 'Old description',
        'refined_version_uuid' => null,
        'validate_default_cargo_box' => false,
        'has_default_cargo_containers' => false,
        'box_sizes_scu' => [1],
        'data' => ['name' => 'Old Name'],
    ]);

    $payload = [[
        'uuid' => $uuid,
        'key' => 'Agricium',
        'name' => 'Updated Agricium',
        'description' => 'Updated description',
        'refined_version_uuid' => null,
        'validate_default_cargo_box' => true,
        'has_default_cargo_containers' => true,
        'box_sizes_scu' => [1, 2, 4],
    ]];

    Storage::disk('scunpacked')->put('resource-types.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-resource-types')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 1 resource types (0 new, 1 updated). Skipped 0 invalid.');

    $resourceType = ResourceType::query()->where('uuid', $uuid)->first();

    expect($resourceType)->not->toBeNull()
        ->and($resourceType->name)->toBe('Updated Agricium')
        ->and($resourceType->validate_default_cargo_box)->toBeTrue()
        ->and($resourceType->box_sizes_scu)->toBe([1, 2, 4])
        ->and(ResourceType::query()->count())->toBe(1);
});
