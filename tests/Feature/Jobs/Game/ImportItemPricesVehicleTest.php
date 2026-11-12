<?php

declare(strict_types=1);

use App\Jobs\Game\EnrichVehiclePrices;
use App\Jobs\Game\ImportItemPrices;
use App\Models\Game\GameVersion;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

it('imports vehicle purchase and rental prices', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true]);

    $vehicleUuid = '11111111-2222-3333-4444-555566667777';
    $vehicle = Vehicle::factory()->create(['uuid' => $vehicleUuid]);
    $vehicleData = VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
    ]);

    $locationUuid = 'a3a3a3a3-2222-4333-8444-555566667781';
    $starmapLocation = StarmapLocation::factory()->create(['uuid' => $locationUuid]);
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
            'starmap_location_uuid' => $locationUuid,
            'starmap_location_data_id' => $starmapLocationData->id,
            'price_buy' => 5000000,
            'game_version' => $version->code,
            'date_updated' => '2023-11-14T22:13:20+00:00',
        ])
        ->and($vehicleData->uex_rental_prices)->toBeArray()
        ->and($vehicleData->uex_rental_prices)->toHaveCount(1)
        ->and($vehicleData->uex_rental_prices[0])->toMatchArray([
            'terminal_id' => 50,
            'terminal_code' => 'VT50',
            'terminal_name' => 'Vehicle Terminal',
            'starmap_location_uuid' => $locationUuid,
            'starmap_location_data_id' => $starmapLocationData->id,
            'price_rent' => 50000,
            'game_version' => $version->code,
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

    $vehicleUuid = 'a3a3a3a3-2222-4333-8444-555566667782';
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

it('fails the import when a vehicle bulk price endpoint errors and leaves existing prices untouched', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true]);

    $vehicleUuid = '11111111-2222-3333-4444-555566667783';
    $vehicle = Vehicle::factory()->create(['uuid' => $vehicleUuid]);
    $vehicleData = VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'uex_purchase_prices' => [
            [
                'terminal_id' => 51,
                'terminal_code' => 'OLDPUR',
                'terminal_name' => 'Existing Purchase Terminal',
                'starmap_location_uuid' => null,
                'starmap_location_data_id' => null,
                'price_buy' => 800000,
                'game_version' => $version->code,
                'date_updated' => '2024-01-01T00:00:00+00:00',
            ],
        ],
        'uex_rental_prices' => [
            [
                'terminal_id' => 52,
                'terminal_code' => 'OLDREN',
                'terminal_name' => 'Existing Rental Terminal',
                'starmap_location_uuid' => null,
                'starmap_location_data_id' => null,
                'price_rent' => 40000,
                'game_version' => $version->code,
                'date_updated' => '2024-01-01T00:00:00+00:00',
            ],
        ],
    ]);

    Http::fake(function ($request) use ($vehicleUuid) {
        if (str_contains($request->url(), 'items_prices_all')) {
            return Http::response(['data' => []]);
        }

        if (str_contains($request->url(), '/vehicles_purchases_prices_all')) {
            return Http::response(status: 500);
        }

        if (str_contains($request->url(), '/vehicles_rentals_prices_all')) {
            return Http::response(['data' => [
                [
                    'id_vehicle' => 30,
                    'id_terminal' => 52,
                    'vehicle_name' => 'Existing Ship',
                    'terminal_name' => 'Existing Rental Terminal',
                    'price_rent' => 55000,
                    'date_modified' => 1700000100,
                ],
            ]]);
        }

        if (str_contains($request->url(), '/vehicles') && ! str_contains($request->url(), 'vehicles_')) {
            return Http::response(['data' => [
                ['id' => 30, 'uuid' => $vehicleUuid, 'name' => 'Existing Ship'],
            ]]);
        }

        if (str_contains($request->url(), 'terminals')) {
            return Http::response(['data' => []]);
        }

        return Http::response(status: 404);
    });

    $job = new ImportItemPrices($version->id);

    $thrown = null;
    try {
        $job->handle();
    } catch (Throwable $exception) {
        $thrown = $exception;
    }

    $purchasePrices = $vehicleData->refresh()->uex_purchase_prices;
    $rentalPrices = $vehicleData->refresh()->uex_rental_prices;

    expect($purchasePrices)->toBeArray()
        ->and($purchasePrices)->toHaveCount(1)
        ->and($purchasePrices[0])->toMatchArray([
            'terminal_code' => 'OLDPUR',
            'terminal_name' => 'Existing Purchase Terminal',
            'price_buy' => 800000,
        ])
        ->and($rentalPrices)->toBeArray()
        ->and($rentalPrices)->toHaveCount(1)
        ->and($rentalPrices[0])->toMatchArray([
            'terminal_code' => 'OLDREN',
            'terminal_name' => 'Existing Rental Terminal',
            'price_rent' => 40000,
        ]);

    expect($thrown)->toBeInstanceOf(RequestException::class);

    Log::shouldHaveReceived('error')->with('UEX vehicle purchase prices API failed', [
        'status' => 500,
        'game_version_id' => $version->id,
    ]);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('updates vehicle prices when the UEX source data changes', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true]);

    $vehicleUuid = '11111111-2222-3333-4444-555566667778';
    $vehicle = Vehicle::factory()->create(['uuid' => $vehicleUuid]);
    $vehicleData = VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
    ]);

    $priceBuy = 5000000;

    Http::fake(function ($request) use ($vehicleUuid, &$priceBuy) {
        if (str_contains($request->url(), 'items_prices_all')) {
            return Http::response(['data' => []]);
        }

        if (str_contains($request->url(), '/vehicles_purchases_prices_all')) {
            return Http::response(['data' => [
                [
                    'id_vehicle' => 11,
                    'id_terminal' => 50,
                    'vehicle_name' => 'Test Ship',
                    'terminal_name' => 'Vehicle Terminal',
                    'price_buy' => $priceBuy,
                    'date_modified' => 1700000000,
                ],
            ]]);
        }

        if (str_contains($request->url(), '/vehicles_rentals_prices_all')) {
            return Http::response(['data' => []]);
        }

        if (str_contains($request->url(), '/vehicles') && ! str_contains($request->url(), 'vehicles_')) {
            return Http::response(['data' => [
                ['id' => 11, 'uuid' => $vehicleUuid, 'name' => 'Test Ship'],
            ]]);
        }

        if (str_ends_with($request->url(), '/terminals')) {
            return Http::response(['data' => []]);
        }

        return Http::response(status: 404);
    });

    Carbon::setTestNow('2024-01-01 10:00:00');
    (new ImportItemPrices($version->id))->handle();

    $firstUpdatedAt = $vehicleData->refresh()->updated_at;

    $priceBuy = 9999999;

    Carbon::setTestNow('2024-01-01 11:00:00');
    (new ImportItemPrices($version->id))->handle();

    $vehicleData->refresh();

    expect($vehicleData->updated_at->getTimestamp())->toBeGreaterThan($firstUpdatedAt->getTimestamp())
        ->and($vehicleData->uex_purchase_prices[0]['price_buy'])->toBe(9999999);
});

describe('mapped row shape', function (): void {
    it('maps vehicle purchase and rental rows with the exact full field set', function (): void {
        Log::spy();

        $version = GameVersion::factory()->create(['is_default' => true]);

        $vehicleUuid = '21111111-2222-3333-4444-555566667777';
        $vehicle = Vehicle::factory()->create(['uuid' => $vehicleUuid]);
        $vehicleData = VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $version->id,
        ]);

        $locationUuid = 'b3b3b3b3-2222-4333-8444-555566667781';
        $starmapLocation = StarmapLocation::factory()->create(['uuid' => $locationUuid]);
        $starmapLocationData = StarmapLocationData::factory()->create([
            'starmap_location_id' => $starmapLocation->id,
            'game_version_id' => $version->id,
            'name' => 'Shape Vehicle Station',
        ]);

        Http::fake(function ($request) use ($vehicleUuid) {
            if (str_contains($request->url(), 'items_prices_all')) {
                return Http::response(['data' => []]);
            }

            if (str_contains($request->url(), '/vehicles_purchases_prices_all')) {
                return Http::response(['data' => [
                    [
                        'id_vehicle' => 21,
                        'id_terminal' => 60,
                        'vehicle_name' => 'Shape Ship',
                        'terminal_name' => 'Shape Vehicle Terminal',
                        'price_buy' => 4321,
                        'date_modified' => 1700000000,
                    ],
                ]]);
            }

            if (str_contains($request->url(), '/vehicles_rentals_prices_all')) {
                return Http::response(['data' => [
                    [
                        'id_vehicle' => 21,
                        'id_terminal' => 60,
                        'vehicle_name' => 'Shape Ship',
                        'terminal_name' => 'Shape Vehicle Terminal',
                        'price_rent' => 432,
                        'date_modified' => 1700000100,
                    ],
                ]]);
            }

            if (str_contains($request->url(), '/vehicles') && ! str_contains($request->url(), 'vehicles_')) {
                return Http::response(['data' => [
                    [
                        'id' => 21,
                        'uuid' => $vehicleUuid,
                        'name' => 'Shape Ship',
                    ],
                ]]);
            }

            if (str_contains($request->url(), 'terminals')) {
                return Http::response(['data' => [
                    [
                        'id' => 60,
                        'displayname' => 'Shape Vehicle Station',
                        'name' => 'Admin - Shape Vehicle Terminal',
                        'code' => 'SHAPE60',
                        'star_system_name' => 'Stanton',
                    ],
                ]]);
            }

            return Http::response(status: 404);
        });

        (new ImportItemPrices($version->id))->handle();

        $vehicleData->refresh();

        $purchaseRow = $vehicleData->uex_purchase_prices[0];
        $rentalRow = $vehicleData->uex_rental_prices[0];

        // jsonb storage normalizes key order, so assert the full key set and values instead of ordering.
        expect($purchaseRow)->toBeArray()
            ->and(array_keys($purchaseRow))->toHaveCount(9)
            ->and($purchaseRow)->toMatchArray([
                'terminal_id' => 60,
                'terminal_code' => 'SHAPE60',
                'terminal_name' => 'Shape Vehicle Terminal',
                'starmap_location_uuid' => $locationUuid,
                'starmap_location_data_id' => $starmapLocationData->id,
                'price_buy' => 4321,
                'game_version' => $version->code,
                'date_updated' => '2023-11-14T22:13:20+00:00',
                'uex_link' => 'https://uexcorp.space/vehicles/home/list/in_game_sell/?id_terminal=60',
            ])
            ->and($rentalRow)->toBeArray()
            ->and(array_keys($rentalRow))->toHaveCount(9)
            ->and($rentalRow)->toMatchArray([
                'terminal_id' => 60,
                'terminal_code' => 'SHAPE60',
                'terminal_name' => 'Shape Vehicle Terminal',
                'starmap_location_uuid' => $locationUuid,
                'starmap_location_data_id' => $starmapLocationData->id,
                'price_rent' => 432,
                'game_version' => $version->code,
                'date_updated' => '2023-11-14T22:15:00+00:00',
                'uex_link' => 'https://uexcorp.space/vehicles/home/list/in_game_rent/?id_terminal=60',
            ]);
    });
});

it('preserves matched vehicle prices when the terminal request fails', function (): void {
    $version = GameVersion::factory()->create(['is_default' => true]);
    $vehicle = Vehicle::factory()->create();
    $purchasePrices = [[
        'terminal_id' => 50,
        'terminal_code' => 'EXIST',
        'terminal_name' => 'Existing Terminal',
        'starmap_location_uuid' => 'aaa11111-2222-3333-4444-555566667777',
        'starmap_location_data_id' => 123,
        'price_buy' => 900,
    ]];
    $rentalPrices = [[
        'terminal_id' => 50,
        'terminal_code' => 'EXIST',
        'terminal_name' => 'Existing Terminal',
        'starmap_location_uuid' => 'aaa11111-2222-3333-4444-555566667777',
        'starmap_location_data_id' => 123,
        'price_rent' => 90,
    ]];
    $vehicleData = VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'uex_purchase_prices' => $purchasePrices,
        'uex_rental_prices' => $rentalPrices,
    ]);

    $vehicleData->refresh();
    $purchasePrices = $vehicleData->uex_purchase_prices;
    $rentalPrices = $vehicleData->uex_rental_prices;

    Http::fake(function ($request) use ($vehicle) {
        $url = $request->url();
        if (str_contains($url, 'items_prices_all') || str_contains($url, 'vehicles_rentals_prices_all')) {
            return Http::response(['data' => []]);
        }
        if (str_contains($url, 'vehicles_purchases_prices_all')) {
            return Http::response(['data' => [[
                'id_vehicle' => 10,
                'id_terminal' => 50,
                'vehicle_name' => 'Test Ship',
                'terminal_name' => 'Existing Terminal',
                'price_buy' => 100,
                'date_modified' => 1700000000,
            ]]]);
        }
        if (str_contains($url, 'terminals')) {
            return Http::response(status: 500);
        }
        if (str_contains($url, '/vehicles')) {
            return Http::response(['data' => [['id' => 10, 'uuid' => $vehicle->uuid, 'name' => 'Test Ship']]]);
        }

        return Http::response(status: 404);
    });

    $exception = null;
    try {
        (new ImportItemPrices($version->id))->handle();
    } catch (RequestException $e) {
        $exception = $e;
    }

    $vehicleData->refresh();
    expect($vehicleData->uex_purchase_prices)->toBe($purchasePrices)
        ->and($vehicleData->uex_rental_prices)->toBe($rentalPrices)
        ->and($exception)->toBeInstanceOf(RequestException::class)
        ->and($exception->response->status())->toBe(500);
    Http::assertSent(fn ($request) => str_contains($request->url(), 'terminals'));
});

it('filters vehicle versions before keeping the latest purchase and rental per terminal', function (): void {
    $version = GameVersion::factory()->create(['code' => '4.7.1-LIVE']);
    $vehicle = Vehicle::factory()->create();
    $data = VehicleData::factory()->create(['vehicle_id' => $vehicle->id, 'game_version_id' => $version->id]);
    Http::fake(function ($request) use ($vehicle) {
        $url = $request->url();
        if (str_contains($url, 'vehicles_purchases_prices_all') || str_contains($url, 'vehicles_rentals_prices_all')) {
            $field = str_contains($url, 'purchases') ? 'price_buy' : 'price_rent';
            $rows = [];
            foreach ([[1, '4.6.1', 100, 10], [1, '4.7.0', 200, 20], [1, '3.24.0', 999, 99], [2, '4.6.0', 300, 30], [3, 'unknown', 400, 40], [4, null, 500, 50]] as [$terminal, $apiVersion, $date, $price]) {
                $rows[] = ['id_vehicle' => 10, 'id_terminal' => $terminal, 'terminal_name' => 'Terminal', $field => $price, 'date_modified' => $date, 'game_version' => $apiVersion];
            }

            return Http::response(['data' => $rows]);
        }
        if (str_ends_with($url, '/vehicles')) {
            return Http::response(['data' => [['id' => 10, 'uuid' => $vehicle->uuid, 'name' => 'Ship']]]);
        }

        return Http::response(['data' => []]);
    });
    (new ImportItemPrices($version->id, '4.6.3-LIVE'))->handle();
    $data->refresh();
    foreach (['uex_purchase_prices' => 'price_buy', 'uex_rental_prices' => 'price_rent'] as $column => $field) {
        $prices = collect($data->$column)->keyBy('terminal_id');
        expect($prices->keys()->sort()->values()->all())->toBe([1, 2, 4])
            ->and($prices[1][$field])->toBe(20)
            ->and($prices[1]['game_version'])->toBe('4.7.1-LIVE')
            ->and($prices[2]['game_version'])->toBe('4.6.3-LIVE')
            ->and($prices[4]['game_version'])->toBe('4.7.1-LIVE');
    }
});

it('clears rejected vehicle price types independently and preserves absent types', function (): void {
    $version = GameVersion::factory()->create(['code' => '4.7.1-LIVE']);
    $vehicle = Vehicle::factory()->create();
    $other = Vehicle::factory()->create();
    $seed = [['price_buy' => 999]];
    $data = VehicleData::factory()->create(['vehicle_id' => $vehicle->id, 'game_version_id' => $version->id, 'uex_purchase_prices' => $seed, 'uex_rental_prices' => $seed]);
    $absent = VehicleData::factory()->create(['vehicle_id' => $other->id, 'game_version_id' => $version->id, 'uex_purchase_prices' => $seed, 'uex_rental_prices' => $seed]);
    Http::fake(function ($request) use ($vehicle, $other) {
        $url = $request->url();
        if (str_ends_with($url, '/vehicles')) {
            return Http::response(['data' => [['id' => 10, 'uuid' => $vehicle->uuid], ['id' => 11, 'uuid' => $other->uuid]]]);
        }
        if (str_contains($url, 'vehicles_purchases_prices_all')) {
            return Http::response(['data' => [['id_vehicle' => 10, 'game_version' => '4.70.0']]]);
        }

        return Http::response(['data' => []]);
    });
    (new ImportItemPrices($version->id))->handle();
    expect($data->refresh()->uex_purchase_prices)->toBe([])->and($data->uex_rental_prices)->toBe($seed)
        ->and($absent->refresh()->uex_purchase_prices)->toBe($seed)->and($absent->uex_rental_prices)->toBe($seed);
});

it('keeps source order for equal dates across vehicle aliases', function (): void {
    $version = GameVersion::factory()->create(['code' => '4.7.1-LIVE']);
    $vehicle = Vehicle::factory()->create();
    $data = VehicleData::factory()->create(['vehicle_id' => $vehicle->id, 'game_version_id' => $version->id]);
    Http::fake(function ($request) use ($vehicle) {
        $url = $request->url();
        if (str_ends_with($url, '/vehicles')) {
            return Http::response(['data' => [['id' => 10, 'uuid' => $vehicle->uuid], ['id' => 11, 'uuid' => $vehicle->uuid]]]);
        }
        if (str_contains($url, 'vehicles_purchases_prices_all')) {
            return Http::response(['data' => [
                ['id_vehicle' => 11, 'id_terminal' => 1, 'terminal_name' => 'T', 'price_buy' => 11, 'date_modified' => 10],
                ['id_vehicle' => 10, 'id_terminal' => 1, 'terminal_name' => 'T', 'price_buy' => 10, 'date_modified' => 10],
            ]]);
        }

        return Http::response(['data' => []]);
    });
    (new ImportItemPrices($version->id))->handle();
    expect($data->refresh()->uex_purchase_prices[0]['price_buy'])->toBe(11);
});

it('preserves bulk alias terminals through dispatched vehicle enrichment', function (bool $removedAlias): void {
    $version = GameVersion::factory()->create(['code' => '4.7.1-LIVE']);
    $vehicle = Vehicle::factory()->create();
    $data = VehicleData::factory()->create(['vehicle_id' => $vehicle->id, 'game_version_id' => $version->id]);
    config(['uexcorp.vehicle_uuid_overrides' => ['alias-one' => $vehicle->uuid, 'alias-two' => $vehicle->uuid]]);
    $vehicleRequests = 0;
    Http::fake(function ($request) use ($removedAlias, &$vehicleRequests) {
        $url = $request->url();
        if (str_ends_with($url, '/vehicles')) {
            $vehicleRequests++;
            $vehicles = [['id' => 10, 'uuid' => 'alias-one'], ['id' => 11, 'uuid' => 'alias-two']];

            return Http::response(['data' => $removedAlias && $vehicleRequests > 1 ? [$vehicles[1]] : $vehicles]);
        }
        if (str_contains($url, 'vehicles_purchases_prices_all')) {
            return Http::response(['data' => [
                ['id_vehicle' => 10, 'id_terminal' => 1, 'terminal_name' => 'One', 'price_buy' => 10, 'date_modified' => 10],
                ['id_vehicle' => 11, 'id_terminal' => 2, 'terminal_name' => 'Two', 'price_buy' => 20, 'date_modified' => 20],
            ]]);
        }
        if (str_contains($url, 'vehicles_purchases_prices?')) {
            return Http::response(['data' => [['id_terminal' => 2, 'terminal_name' => 'Two', 'price_buy' => 20, 'date_modified' => 20]]]);
        }

        return Http::response(['data' => []]);
    });
    (new ImportItemPrices($version->id))->handle();
    expect(collect($data->refresh()->uex_purchase_prices)->pluck('terminal_id')->sort()->values()->all())->toBe([1, 2]);
    Bus::fake();
    App\Console\Commands\Game\ImportItemPrices::dispatchEnrichmentBatches($version, 50);
    Bus::assertBatched(function ($batch): bool {
        foreach ($batch->jobs as $job) {
            if ($job instanceof EnrichVehiclePrices) {
                $job->handle();

                return true;
            }
        }

        return false;
    });
    expect(collect($data->refresh()->uex_purchase_prices)->pluck('terminal_id')->sort()->values()->all())->toBe([1, 2]);
})->with([false, true]);
