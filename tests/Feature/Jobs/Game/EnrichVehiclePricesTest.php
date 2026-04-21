<?php

declare(strict_types=1);

use App\Jobs\Game\EnrichVehiclePrices;
use App\Models\Game\GameVersion;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

it('enriches vehicle prices from per-vehicle API', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true]);

    $starmapLocation = StarmapLocation::factory()->create(['uuid' => 'loc-veh-enrich-1']);
    $starmapLocationData = StarmapLocationData::factory()->create([
        'starmap_location_id' => $starmapLocation->id,
        'game_version_id' => $version->id,
        'name' => 'Area18',
    ]);

    $vehicleUuid = 'veh-uuid-enrich-1';
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
            'starmap_location_uuid' => 'loc-veh-enrich-1',
            'starmap_location_data_id' => $starmapLocationData->id,
            'price_buy' => 2000000,
            'date_updated' => '2023-11-14T22:13:20+00:00',
        ])
        ->and($vehicleData->uex_rental_prices)->toBeArray()
        ->and($vehicleData->uex_rental_prices)->toHaveCount(1)
        ->and($vehicleData->uex_rental_prices[0]['price_rent'])->toBe(20000);

    Log::shouldHaveReceived('info')->with('UEX vehicle prices enrichment chunk completed', [
        'count' => 1,
        'chunk_size' => 1,
        'game_version_id' => $version->id,
    ]);
});

it('uses reverse UUID override for mismatched vehicles', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true]);

    $wikiUuid = 'd8987dc2-340d-4312-8d4f-aee7e7fac823';
    $uexUuid = '42b72b92-7880-446d-8770-b158e4b0fd10';

    $vehicle = Vehicle::factory()->create(['uuid' => $wikiUuid]);
    $vehicleData = VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'uex_purchase_prices' => [
            ['terminal_code' => null, 'terminal_name' => 'T', 'price_buy' => 100, 'date_updated' => '2024-01-01T00:00:00+00:00'],
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

it('skips vehicles without existing prices', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true]);

    $vehicle = Vehicle::factory()->create(['uuid' => 'no-prices-uuid']);
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'uex_purchase_prices' => null,
        'uex_rental_prices' => null,
    ]);

    Http::fake([
        'api.uexcorp.uk/*' => Http::response(['data' => []]),
    ]);

    $job = new EnrichVehiclePrices($version->id, ['no-prices-uuid']);
    $job->handle();

    Log::shouldHaveReceived('info')->with('UEX vehicle prices enrichment chunk completed', [
        'count' => 0,
        'chunk_size' => 1,
        'game_version_id' => $version->id,
    ]);
});
