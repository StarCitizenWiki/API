<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\GameVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->defaultVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $this->requestedVersion = GameVersion::factory()->create([
        'code' => '4.0.0-PTU',
        'channel' => 'ptu',
        'released_at' => now()->subDay(),
        'is_default' => false,
    ]);
});

it('renders the blueprints index route', function (): void {
    $defaultBlueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($defaultBlueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'key' => 'BP_CRAFT_FS9',
            'output_name' => 'FS-9 LMG',
            'output_class' => 'behr_lmg_ballistic_01',
            'craft_time_seconds' => 240,
            'is_available_by_default' => true,
            'data' => [
                'output' => [
                    'uuid' => fake()->uuid(),
                    'name' => 'FS-9 LMG',
                    'class' => 'behr_lmg_ballistic_01',
                ],
                'tiers' => [
                    [
                        'craft_time_seconds' => 240,
                        'requirements' => [
                            'kind' => 'root',
                            'children' => [
                                ['kind' => 'resource', 'uuid' => fake()->uuid(), 'name' => 'Iron', 'quantity_scu' => 2.0],
                                ['kind' => 'resource', 'uuid' => fake()->uuid(), 'name' => 'Titanium', 'quantity_scu' => 1.0],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    BlueprintData::factory()
        ->for(Blueprint::factory(), 'blueprint')
        ->for($this->requestedVersion, 'gameVersion')
        ->create([
            'key' => 'BP_CRAFT_P4',
            'output_name' => 'P4-AR',
            'output_class' => 'klwe_ar_ballistic_01',
        ]);

    $response = $this->get(route('web.blueprints.index'));

    $response->assertOk()
        ->assertViewHas('pageTitle', 'Blueprints')
        ->assertViewHas('pageSize', 25)
        ->assertViewHas('initialTableData', function (array $tableData): bool {
            return data_get($tableData, 'data.0.output_name') === 'FS-9 LMG'
                && ! collect(data_get($tableData, 'data', []))
                    ->pluck('output_name')
                    ->contains('P4-AR');
        })
        ->assertSeeText('Blueprints')
        ->assertSee(route('web.blueprints.search'), false)
        ->assertSee(route('blueprints.index'), false);
});

it('renders blueprints for the requested version on the web route', function (): void {
    BlueprintData::factory()
        ->for(Blueprint::factory(), 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'key' => 'BP_CRAFT_DEFAULT',
            'output_name' => 'Default Output',
            'output_class' => 'default_output',
        ]);

    $requestedBlueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($requestedBlueprint, 'blueprint')
        ->for($this->requestedVersion, 'gameVersion')
        ->create([
            'key' => 'BP_CRAFT_REQUESTED',
            'output_name' => 'Requested Output',
            'output_class' => 'requested_output',
        ]);

    $response = $this
        ->withSession(['game_version_code' => $this->defaultVersion->code])
        ->get(route('web.blueprints.index', ['version' => $this->requestedVersion->code]));

    $response->assertOk()
        ->assertViewHas('pageTitle', 'Blueprints')
        ->assertViewHas('initialTableData', function (array $tableData): bool {
            return data_get($tableData, 'data.0.output_name') === 'Requested Output'
                && ! collect(data_get($tableData, 'data', []))
                    ->pluck('output_name')
                    ->contains('Default Output');
        })
        ->assertSee(route('web.blueprints.search', ['version' => $this->requestedVersion->code]), false)
        ->assertSee(route('blueprints.index', ['version' => $this->requestedVersion->code]), false);
});
