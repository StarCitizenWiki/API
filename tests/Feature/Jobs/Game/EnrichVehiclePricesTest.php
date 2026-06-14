<?php

declare(strict_types=1);

use App\Jobs\Game\EnrichVehiclePrices;
use App\Models\Game\GameVersion;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

it('enriches vehicle prices from per-vehicle API', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true, 'code' => '4.7.1']);

    $locationUuid = 'a1a1a1a1-2222-4333-8444-555566667781';
    $starmapLocation = StarmapLocation::factory()->create(['uuid' => $locationUuid]);
    $starmapLocationData = StarmapLocationData::factory()->create([
        'starmap_location_id' => $starmapLocation->id,
        'game_version_id' => $version->id,
        'name' => 'Area18',
    ]);

    $vehicleUuid = '11111111-2222-4333-8444-555566667780';
    $vehicle = Vehicle::factory()->create(['uuid' => $vehicleUuid]);
    $vehicleData = VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'uex_purchase_prices' => [
            [
                'terminal_id' => 99,
                'terminal_code' => null,
                'terminal_name' => 'Old Terminal',
                'starmap_location_uuid' => null,
                'starmap_location_data_id' => null,
                'price_buy' => 1000000,
                'game_version' => '4.7.1',
                'date_updated' => '2024-01-01T00:00:00+00:00',
            ],
        ],
    ]);

    Http::fake(function ($request) {
        if (str_contains($request->url(), 'vehicles_purchases_prices?uuid')) {
            return Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'id_terminal' => 107,
                        'terminal_name' => 'New Terminal - Area18',
                        'terminal_code' => 'NTA18',
                        'price_buy' => 2000000,
                        'game_version' => '4.7.1',
                        'date_modified' => 1700000000,
                    ],
                ],
            ]);
        }

        if (str_contains($request->url(), 'vehicles_rentals_prices?uuid')) {
            return Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'id_terminal' => 107,
                        'terminal_name' => 'New Terminal - Area18',
                        'terminal_code' => 'NTA18',
                        'price_rent' => 20000,
                        'game_version' => '4.7.1',
                        'date_modified' => 1700000100,
                    ],
                ],
            ]);
        }

        if (str_contains($request->url(), 'terminals')) {
            return Http::response([
                'data' => [
                    [
                        'id' => 107,
                        'displayname' => 'Area18',
                        'name' => 'New Terminal - Area 18',
                        'code' => 'NTA18',
                        'star_system_name' => 'Stanton',
                    ],
                ],
            ]);
        }

        return Http::response(status: 404);
    });

    $job = new EnrichVehiclePrices($version->id, [$vehicleUuid]);
    $job->handle();

    $vehicleData->refresh();

    expect($vehicleData->uex_purchase_prices)->toBeArray()
        ->and($vehicleData->uex_purchase_prices)->toHaveCount(1)
        ->and($vehicleData->uex_purchase_prices[0])->toMatchArray([
            'terminal_id' => 107,
            'terminal_code' => 'NTA18',
            'terminal_name' => 'New Terminal - Area18',
            'starmap_location_uuid' => $locationUuid,
            'starmap_location_data_id' => $starmapLocationData->id,
            'price_buy' => 2000000,
            'game_version' => '4.7.1',
            'date_updated' => '2023-11-14T22:13:20+00:00',
        ])
        ->and($vehicleData->uex_rental_prices)->toBeArray()
        ->and($vehicleData->uex_rental_prices)->toHaveCount(1)
        ->and($vehicleData->uex_rental_prices[0]['price_rent'])->toBe(20000)
        ->and($vehicleData->uex_rental_prices[0]['game_version'])->toBe('4.7.1');

    Log::shouldHaveReceived('info')->with('UEX vehicle prices enrichment chunk completed', [
        'count' => 1,
        'chunk_size' => 1,
        'game_version_id' => $version->id,
    ]);
});

it('uses reverse UUID override for mismatched vehicles', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true, 'code' => '4.7.1']);

    $wikiUuid = 'd8987dc2-340d-4312-8d4f-aee7e7fac823';
    $uexUuid = '42b72b92-7880-446d-8770-b158e4b0fd10';

    $vehicle = Vehicle::factory()->create(['uuid' => $wikiUuid]);
    $vehicleData = VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'uex_purchase_prices' => [
            ['terminal_code' => null, 'terminal_name' => 'T', 'price_buy' => 100, 'game_version' => '4.7.1', 'date_updated' => '2024-01-01T00:00:00+00:00'],
        ],
    ]);

    $capturedUrls = [];

    Http::fake(function ($request) use (&$capturedUrls, $uexUuid) {
        $url = $request->url();
        $capturedUrls[] = $url;

        if (str_contains($url, 'vehicles_purchases_prices?uuid')) {
            expect($url)->toContain("uuid={$uexUuid}");

            return Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'id_terminal' => 1,
                        'terminal_name' => 'T',
                        'terminal_code' => 'T1',
                        'price_buy' => 200,
                        'game_version' => '4.7.1',
                        'date_modified' => 1700000000,
                    ],
                ],
            ]);
        }

        if (str_contains($url, 'vehicles_rentals_prices?uuid')) {
            return Http::response(['data' => []]);
        }

        if (str_contains($url, 'terminals')) {
            return Http::response(['data' => []]);
        }

        return Http::response(status: 404);
    });

    $job = new EnrichVehiclePrices($version->id, [$wikiUuid], [$wikiUuid => $uexUuid]);
    $job->handle();

    $vehicleData->refresh();

    expect($vehicleData->uex_purchase_prices[0]['price_buy'])->toBe(200);
});

it('skips blank UUID overrides so UEX does not return all vehicle prices', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true, 'code' => '4.7.1']);

    $wikiUuid = 'b616b3ad-123c-40f2-80bd-b8f4109633aa';
    $vehicle = Vehicle::factory()->create(['uuid' => $wikiUuid]);
    $vehicleData = VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'uex_purchase_prices' => [
            [
                'terminal_id' => 149,
                'terminal_code' => 'NDLOR',
                'terminal_name' => 'New Deal Lorville',
                'starmap_location_uuid' => null,
                'starmap_location_data_id' => null,
                'price_buy' => 1005480,
                'game_version' => '4.7.1',
                'date_updated' => '2024-01-01T00:00:00+00:00',
            ],
        ],
    ]);

    Http::fake(function ($request) {
        $url = $request->url();

        if (str_contains($url, 'terminals') || str_contains($url, 'vehicles_rentals_prices')) {
            return Http::response(['data' => []]);
        }

        if (str_contains($url, 'vehicles_purchases_prices')) {
            return Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'id_terminal' => 149,
                        'terminal_name' => 'New Deal - Teasa Spaceport - Lorville',
                        'terminal_code' => 'NDLOR',
                        'price_buy' => 34466600,
                        'game_version' => '4.7.1',
                        'date_modified' => 1700000000,
                    ],
                ],
            ]);
        }

        return Http::response(status: 404);
    });

    // Empty UUID in map, no ID map, vehicle should be skipped entirely
    $job = new EnrichVehiclePrices($version->id, [$wikiUuid], [$wikiUuid => ''], []);
    $job->handle();

    $vehicleData->refresh();

    expect($vehicleData->uex_purchase_prices)->toHaveCount(1)
        ->and($vehicleData->uex_purchase_prices[0]['price_buy'])->toBe(1005480);

    Log::shouldHaveReceived('info')->with('UEX vehicle prices enrichment chunk completed', [
        'count' => 0,
        'chunk_size' => 1,
        'game_version_id' => $version->id,
    ]);
});

it('enriches vehicle prices using id_vehicle fallback for empty-UUID vehicles', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true, 'code' => '4.7.1']);

    $wikiUuid = 'b616b3ad-123c-40f2-80bd-b8f4109633aa';
    $uexId = 251; // Golem's UEX id
    $vehicle = Vehicle::factory()->create(['uuid' => $wikiUuid]);
    $vehicleData = VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'uex_purchase_prices' => [
            [
                'terminal_id' => 149,
                'terminal_code' => 'NDLOR',
                'terminal_name' => 'New Deal Lorville',
                'starmap_location_uuid' => null,
                'starmap_location_data_id' => null,
                'price_buy' => 1005480,
                'game_version' => '4.7.1',
                'date_updated' => '2024-01-01T00:00:00+00:00',
            ],
        ],
    ]);

    $capturedQueryParams = [];

    Http::fake(function ($request) use (&$capturedQueryParams) {
        $url = $request->url();

        // Capture query params to verify id_vehicle is used
        if (str_contains($url, 'vehicles_purchases_prices')) {
            parse_str(parse_url($url, PHP_URL_QUERY) ?: '', $params);
            $capturedQueryParams = $params;

            return Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'id_terminal' => 149,
                        'terminal_name' => 'New Deal - Teasa Spaceport - Lorville',
                        'terminal_code' => 'NDLOR',
                        'price_buy' => 1005480,
                        'game_version' => '4.7.1',
                        'date_modified' => 1700000000,
                    ],
                ],
            ]);
        }

        if (str_contains($url, 'vehicles_rentals_prices')) {
            return Http::response(['data' => []]);
        }

        return Http::response(status: 404);
    });

    // Empty UUID in map, but provide UEX id for fallback
    $job = new EnrichVehiclePrices($version->id, [$wikiUuid], [$wikiUuid => ''], [$wikiUuid => $uexId]);
    $job->handle();

    // Verify id_vehicle was used, not uuid
    expect($capturedQueryParams)->toHaveKey('id_vehicle')
        ->and($capturedQueryParams['id_vehicle'])->toBe('251')
        ->and($capturedQueryParams)->not->toHaveKey('uuid');

    $vehicleData->refresh();

    expect($vehicleData->uex_purchase_prices)->toHaveCount(1)
        ->and($vehicleData->uex_purchase_prices[0]['price_buy'])->toBe(1005480);

    Log::shouldHaveReceived('info')->with('UEX vehicle prices enrichment chunk completed', [
        'count' => 1,
        'chunk_size' => 1,
        'game_version_id' => $version->id,
    ]);
});

it('skips vehicles without existing prices', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true]);

    $vehicle = Vehicle::factory()->create(['uuid' => 'a1a1a1a1-2222-4333-8444-555566667782']);
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'uex_purchase_prices' => null,
        'uex_rental_prices' => null,
    ]);

    Http::fake([
        'api.uexcorp.uk/*' => Http::response(['data' => []]),
    ]);

    $job = new EnrichVehiclePrices($version->id, ['a1a1a1a1-2222-4333-8444-555566667782']);
    $job->handle();

    Log::shouldHaveReceived('info')->with('UEX vehicle prices enrichment chunk completed', [
        'count' => 0,
        'chunk_size' => 1,
        'game_version_id' => $version->id,
    ]);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('updates vehicle prices when the enriched source data changes', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true, 'code' => '4.7.1']);

    $vehicleUuid = '11111111-2222-3333-4444-555566667784';
    $vehicle = Vehicle::factory()->create(['uuid' => $vehicleUuid]);
    $vehicleData = VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'uex_purchase_prices' => [
            ['terminal_name' => 'Old Terminal', 'price_buy' => 1, 'game_version' => '4.7.1', 'date_updated' => '2024-01-01T00:00:00+00:00'],
        ],
    ]);

    $priceBuy = 2000000;

    Http::fake(function ($request) use (&$priceBuy) {
        if (str_contains($request->url(), 'vehicles_purchases_prices?uuid')) {
            return Http::response(['data' => [
                ['id' => 1, 'id_terminal' => 107, 'terminal_name' => 'New Terminal', 'terminal_code' => 'NT', 'price_buy' => $priceBuy, 'game_version' => '4.7.1', 'date_modified' => 1700000000],
            ]]);
        }

        if (str_contains($request->url(), 'vehicles_rentals_prices?uuid')) {
            return Http::response(['data' => []]);
        }

        return Http::response(status: 404);
    });

    Carbon::setTestNow('2024-01-01 10:00:00');
    (new EnrichVehiclePrices($version->id, [$vehicleUuid]))->handle();

    $firstUpdatedAt = $vehicleData->refresh()->updated_at;

    $priceBuy = 9999999;

    Carbon::setTestNow('2024-01-01 11:00:00');
    (new EnrichVehiclePrices($version->id, [$vehicleUuid]))->handle();

    $vehicleData->refresh();

    expect($vehicleData->updated_at->getTimestamp())->toBeGreaterThan($firstUpdatedAt->getTimestamp())
        ->and($vehicleData->uex_purchase_prices[0]['price_buy'])->toBe(9999999);
});
