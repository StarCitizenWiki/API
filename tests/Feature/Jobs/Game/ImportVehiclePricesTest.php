<?php

declare(strict_types=1);

use App\Jobs\Game\ImportItemPrices;
use App\Models\Game\GameVersion;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

it('imports vehicle purchase and rental prices', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true]);

    $vehicleUuid = '11111111-2222-3333-4444-555566667777';
    $vehicle = Vehicle::factory()->create(['uuid' => $vehicleUuid]);
    $vehicleData = VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
    ]);

    $starmapLocation = StarmapLocation::factory()->create(['uuid' => 'loc-uuid-v1']);
    $starmapLocationData = StarmapLocationData::factory()->create([
        'starmap_location_id' => $starmapLocation->id,
        'game_version_id' => $version->id,
        'name' => 'Vehicle Terminal Station',
    ]);

    Http::fake(function ($request) use ($vehicleUuid) {
        if (str_contains($request->url(), 'items_prices_all')) {
            return Http::response(['data' => []]);
        }

        if (str_contains($request->url(), '/vehicles_purchases_prices_all')) {
            return Http::response([
                'data' => [
                    [
                        'id_vehicle' => 10,
                        'id_terminal' => 50,
                        'vehicle_name' => 'Test Ship',
                        'terminal_name' => 'Vehicle Terminal',
                        'price_buy' => 5000000,
                        'date_modified' => 1700000000,
                    ],
                ],
            ]);
        }

        if (str_contains($request->url(), '/vehicles_rentals_prices_all')) {
            return Http::response([
                'data' => [
                    [
                        'id_vehicle' => 10,
                        'id_terminal' => 50,
                        'vehicle_name' => 'Test Ship',
                        'terminal_name' => 'Vehicle Terminal',
                        'price_rent' => 50000,
                        'date_modified' => 1700000100,
                    ],
                ],
            ]);
        }

        if (str_contains($request->url(), '/vehicles') && ! str_contains($request->url(), 'vehicles_')) {
            return Http::response([
                'data' => [
                    [
                        'id' => 10,
                        'uuid' => $vehicleUuid,
                        'name' => 'Test Ship',
                    ],
                ],
            ]);
        }

        if (str_contains($request->url(), 'terminals')) {
            return Http::response([
                'data' => [
                    [
                        'id' => 50,
                        'displayname' => 'Vehicle Terminal Station',
                        'name' => 'Admin - Vehicle Terminal',
                        'code' => 'VT50',
                        'star_system_name' => 'Stanton',
                    ],
                ],
            ]);
        }

        return Http::response(status: 404);
    });

    $job = new ImportItemPrices($version->id);
    $job->handle();

    $vehicleData->refresh();

    expect($vehicleData->uex_purchase_prices)->toBeArray()
        ->and($vehicleData->uex_purchase_prices)->toHaveCount(1)
        ->and($vehicleData->uex_purchase_prices[0])->toMatchArray([
            'terminal_id' => 50,
            'terminal_code' => 'VT50',
            'terminal_name' => 'Vehicle Terminal',
            'starmap_location_uuid' => 'loc-uuid-v1',
            'starmap_location_data_id' => $starmapLocationData->id,
            'price_buy' => 5000000,
            'date_updated' => '2023-11-14T22:13:20+00:00',
        ])
        ->and($vehicleData->uex_rental_prices)->toBeArray()
        ->and($vehicleData->uex_rental_prices)->toHaveCount(1)
        ->and($vehicleData->uex_rental_prices[0])->toMatchArray([
            'terminal_id' => 50,
            'terminal_code' => 'VT50',
            'terminal_name' => 'Vehicle Terminal',
            'starmap_location_uuid' => 'loc-uuid-v1',
            'starmap_location_data_id' => $starmapLocationData->id,
            'price_rent' => 50000,
            'date_updated' => '2023-11-14T22:15:00+00:00',
        ]);

    Log::shouldHaveReceived('info')->with('UEX vehicle prices imported', [
        'count' => 1,
        'game_version_id' => $version->id,
    ]);
});

it('applies vehicle UUID overrides for mismatched vehicles', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true]);

    $wikiUuid = 'd8987dc2-340d-4312-8d4f-aee7e7fac823';
    $uexUuid = '42b72b92-7880-446d-8770-b158e4b0fd10';

    $vehicle = Vehicle::factory()->create(['uuid' => $wikiUuid]);
    $vehicleData = VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
    ]);

    Http::fake(function ($request) use ($uexUuid) {
        if (str_contains($request->url(), 'items_prices_all')) {
            return Http::response(['data' => []]);
        }

        if (str_contains($request->url(), '/vehicles_purchases_prices_all')) {
            return Http::response([
                'data' => [
                    [
                        'id_vehicle' => 99,
                        'id_terminal' => 1,
                        'vehicle_name' => 'Aurora Mk I ES',
                        'terminal_name' => 'Terminal',
                        'price_buy' => 100000,
                        'date_modified' => 1700000000,
                    ],
                ],
            ]);
        }

        if (str_contains($request->url(), '/vehicles_rentals_prices_all')) {
            return Http::response(['data' => []]);
        }

        if (str_contains($request->url(), '/vehicles') && ! str_contains($request->url(), 'vehicles_')) {
            return Http::response([
                'data' => [
                    [
                        'id' => 99,
                        'uuid' => $uexUuid,
                        'name' => 'Aurora Mk I ES',
                    ],
                ],
            ]);
        }

        if (str_contains($request->url(), 'terminals')) {
            return Http::response(['data' => []]);
        }

        return Http::response(status: 404);
    });

    $job = new ImportItemPrices($version->id);
    $job->handle();

    $vehicleData->refresh();

    expect($vehicleData->uex_purchase_prices)->toBeArray()
        ->and($vehicleData->uex_purchase_prices)->toHaveCount(1)
        ->and($vehicleData->uex_purchase_prices[0]['price_buy'])->toBe(100000);
});

it('applies name-to-UUID overrides for vehicles missing in UEX', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true]);

    $wikiUuid = '1bed9058-e284-4c0f-b561-6ba57ab4f99d';

    $vehicle = Vehicle::factory()->create(['uuid' => $wikiUuid]);
    $vehicleData = VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
    ]);

    Http::fake(function ($request) {
        if (str_contains($request->url(), 'items_prices_all')) {
            return Http::response(['data' => []]);
        }

        if (str_contains($request->url(), '/vehicles_purchases_prices_all')) {
            return Http::response([
                'data' => [
                    [
                        'id_vehicle' => 42,
                        'id_terminal' => 1,
                        'vehicle_name' => 'C8R Pisces Rescue',
                        'terminal_name' => 'Terminal',
                        'price_buy' => 750000,
                        'date_modified' => 1700000000,
                    ],
                ],
            ]);
        }

        if (str_contains($request->url(), '/vehicles_rentals_prices_all')) {
            return Http::response(['data' => []]);
        }

        if (str_contains($request->url(), '/vehicles') && ! str_contains($request->url(), 'vehicles_')) {
            return Http::response([
                'data' => [
                    [
                        'id' => 42,
                        'uuid' => null,
                        'name' => 'C8R Pisces Rescue',
                    ],
                ],
            ]);
        }

        if (str_contains($request->url(), 'terminals')) {
            return Http::response(['data' => []]);
        }

        return Http::response(status: 404);
    });

    $job = new ImportItemPrices($version->id);
    $job->handle();

    $vehicleData->refresh();

    expect($vehicleData->uex_purchase_prices)->toBeArray()
        ->and($vehicleData->uex_purchase_prices)->toHaveCount(1)
        ->and($vehicleData->uex_purchase_prices[0]['price_buy'])->toBe(750000);
});

it('skips vehicles without VehicleData for the game version', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true]);

    $vehicleUuid = 'skip-uuid-1234';
    Vehicle::factory()->create(['uuid' => $vehicleUuid]);

    Http::fake(function ($request) use ($vehicleUuid) {
        if (str_contains($request->url(), 'items_prices_all')) {
            return Http::response(['data' => []]);
        }

        if (str_contains($request->url(), '/vehicles_purchases_prices_all')) {
            return Http::response([
                'data' => [
                    [
                        'id_vehicle' => 10,
                        'id_terminal' => 1,
                        'vehicle_name' => 'Skip Ship',
                        'terminal_name' => 'Terminal',
                        'price_buy' => 100,
                        'date_modified' => 1700000000,
                    ],
                ],
            ]);
        }

        if (str_contains($request->url(), '/vehicles_rentals_prices_all')) {
            return Http::response(['data' => []]);
        }

        if (str_contains($request->url(), '/vehicles') && ! str_contains($request->url(), 'vehicles_')) {
            return Http::response([
                'data' => [
                    [
                        'id' => 10,
                        'uuid' => $vehicleUuid,
                        'name' => 'Skip Ship',
                    ],
                ],
            ]);
        }

        if (str_contains($request->url(), 'terminals')) {
            return Http::response(['data' => []]);
        }

        return Http::response(status: 404);
    });

    $job = new ImportItemPrices($version->id);
    $job->handle();

    Log::shouldHaveReceived('info')->with('UEX vehicle prices imported', [
        'count' => 0,
        'game_version_id' => $version->id,
    ]);
});
