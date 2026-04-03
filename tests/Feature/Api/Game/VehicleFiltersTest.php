<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->instance('env', 'production');
    app('cache')->setDefaultDriver('array');
    app()->forgetInstance('cache');
    app('cache')->forgetDriver(['array', 'database']);
    Cache::store('array')->flush();
});

it('returns filtered in-game vehicle facet values without caching the filtered response', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $destroyerManufacturer = Manufacturer::factory()->create([
        'name' => 'RSI',
        'code' => 'RSI',
    ]);
    $cargoManufacturer = Manufacturer::factory()->create([
        'name' => 'Drake',
        'code' => 'DRK',
    ]);

    VehicleData::factory()
        ->for(Vehicle::factory(), 'vehicle')
        ->for($version, 'gameVersion')
        ->for($destroyerManufacturer)
        ->create([
            'name' => 'Javelin',
            'career' => 'Destroyer',
            'role' => 'Capital Ship',
            'size' => 6,
            'data' => [
                'ShieldController' => [
                    'FaceType' => 'Bubble',
                ],
            ],
        ]);

    VehicleData::factory()
        ->for(Vehicle::factory(), 'vehicle')
        ->for($version, 'gameVersion')
        ->for($cargoManufacturer)
        ->create([
            'name' => 'Caterpillar',
            'career' => 'Cargo',
            'role' => 'Freighter',
            'size' => 5,
            'data' => [
                'ShieldController' => [
                    'FaceType' => 'FrontBack',
                ],
            ],
        ]);

    $response = $this->getJson(route('vehicles.filters', [
        'version' => $version->code,
        'filter' => ['career' => 'Destroyer'],
    ]));

    $response->assertOk()
        ->assertJsonPath('filters.manufacturer', [
            ['value' => 'RSI', 'label' => 'RSI', 'count' => 1],
        ])
        ->assertJsonPath('filters.career', [
            ['value' => 'Destroyer', 'label' => 'Destroyer', 'count' => 1],
        ])
        ->assertJsonPath('filters.role', [
            ['value' => 'Capital Ship', 'label' => 'Capital Ship', 'count' => 1],
        ]);

    expect(Cache::get('filters:index:vehicles'))->toBeNull();
});
