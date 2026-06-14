<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('scunpacked');

    $this->version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $this->resource = Commodity::factory()->create(['uuid' => fake()->uuid()]);
});

function runBlueprintImport(string $versionCode, array $payload, string $file = 'blueprints.json'): void
{
    Storage::disk('scunpacked')->put($file, json_encode($payload, JSON_THROW_ON_ERROR));

    test()->artisan('game:import-blueprints', ['version' => $versionCode])
        ->assertExitCode(Command::SUCCESS);
}

it('creates blueprint data on first import', function (): void {
    $payload = [blueprintPayload($this->resource->uuid)];

    runBlueprintImport($this->version->code, $payload);

    $blueprint = Blueprint::query()->where('uuid', $payload[0]['UUID'])->first();
    expect($blueprint)->not->toBeNull();

    $blueprintData = BlueprintData::query()
        ->where('blueprint_id', $blueprint->id)
        ->where('game_version_id', $this->version->id)
        ->first();

    expect($blueprintData)->not->toBeNull()
        ->and($blueprintData->output_name)->toBe('Test Output')
        ->and($blueprintData->craft_time_seconds)->toBe(120)
        ->and($blueprintData->is_available_by_default)->toBeTrue()
        ->and($blueprintData->data)->toBeArray();
});

it('rewrites the row only when data actually changes', function (): void {
    $payload = [blueprintPayload($this->resource->uuid)];

    runBlueprintImport($this->version->code, $payload);

    // Simulate a genuine data change in the source blueprint.
    $payload[0]['Output']['Name'] = 'Updated Output Name';
    $payload[0]['Output']['Class'] = 'updated_output_class';
    runBlueprintImport($this->version->code, $payload);

    $blueprintData = BlueprintData::query()
        ->where('game_version_id', $this->version->id)
        ->first();

    // The guard must let a genuine change through and persist it.
    expect($blueprintData->output_name)->toBe('Updated Output Name')
        ->and($blueprintData->output_class)->toBe('updated_output_class');
});

/**
 * Build a minimal, valid blueprint payload.
 */
function blueprintPayload(string $resourceUuid): array
{
    return [
        'UUID' => '33333333-3333-3333-3333-333333333333',
        'Key' => 'BP_CRAFT_TEST',
        'Kind' => 'creation',
        'CategoryUUID' => '44444444-4444-4444-4444-444444444444',
        'Output' => [
            'UUID' => '55555555-5555-5555-5555-555555555555',
            'Class' => 'test_output_class',
            'Type' => 'WeaponPersonal',
            'Name' => 'Test Output',
        ],
        'Availability' => [
            'Default' => true,
        ],
        'Tiers' => [
            [
                'TierIndex' => 0,
                'CraftTimeSeconds' => 120,
                'Requirements' => [
                    'Kind' => 'root',
                    'Children' => [
                        [
                            'Kind' => 'resource',
                            'UUID' => $resourceUuid,
                            'Name' => 'Test Resource',
                            'QuantityScu' => 1,
                            'MinQuality' => 0,
                        ],
                    ],
                ],
            ],
        ],
        'Dismantle' => [
            'TimeSeconds' => 10,
            'Efficiency' => 0.5,
            'Returns' => [],
        ],
    ];
}
