<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.4.0-TEST',
        'channel' => 'test',
        'is_default' => true,
    ]);

    $this->vehicle = Vehicle::factory()->create();
});

it('returns empty uex_prices when no prices are stored', function (): void {
    VehicleData::factory()
        ->for($this->vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'uex_purchase_prices' => null,
            'uex_rental_prices' => null,
        ]);

    $response = $this->getJson("/api/v2/vehicles/{$this->vehicle->uuid}");

    $response->assertSuccessful();
    $response->assertJsonPath('data.uex_prices.purchase', []);
    $response->assertJsonPath('data.uex_prices.rental', []);
});

it('expands vehicle purchase prices with location data', function (): void {
    $location = StarmapLocation::factory()->create();

    $starLocation = StarmapLocation::factory()->create();
    $starLocationData = StarmapLocationData::factory()
        ->for($starLocation, 'location')
        ->for($this->gameVersion, 'gameVersion')
        ->create([
            'name' => 'Stanton',
        ]);

    $parentLocation = StarmapLocation::factory()->create();
    $parentLocationData = StarmapLocationData::factory()
        ->for($parentLocation, 'location')
        ->for($this->gameVersion, 'gameVersion')
        ->create([
            'name' => 'Hurston',
            'type_name' => 'Planet',
            'star_data_id' => $starLocationData->id,
        ]);

    $terminalLocationData = StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->gameVersion, 'gameVersion')
        ->create([
            'name' => 'Lorville',
            'slug' => 'lorville',
            'type_name' => 'Outpost',
            'parent_data_id' => $parentLocationData->id,
            'star_data_id' => $starLocationData->id,
        ]);

    VehicleData::factory()
        ->for($this->vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'uex_purchase_prices' => [
                [
                    'terminal_code' => 'LOR_HAB',
                    'terminal_name' => 'Lorville Hangars',
                    'starmap_location_uuid' => $location->uuid,
                    'starmap_location_data_id' => $terminalLocationData->id,
                    'price_buy' => 1520000.0,
                    'game_version' => '4.4.0-TEST',
                    'date_updated' => '2026-04-20T12:00:00Z',
                ],
            ],
            'uex_rental_prices' => null,
        ]);

    $response = $this->getJson("/api/v2/vehicles/{$this->vehicle->uuid}");

    $response->assertSuccessful();

    $purchase = $response->json('data.uex_prices.purchase');
    expect($purchase)->toHaveCount(1);

    $price = $purchase[0];
    expect($price['terminal_code'])->toBe('LOR_HAB')
        ->and($price['terminal_name'])->toBe('Lorville Hangars')
        ->and($price['price_buy'])->toBe(1520000)
        ->and($price['game_version'])->toBe('4.4.0-TEST')
        ->and($price)->not->toHaveKey('starmap_location_data_id')
        ->and($price['starmap_location']['name'])->toBe('Lorville')
        ->and($price['starmap_location']['slug'])->toBe('lorville')
        ->and($price['starmap_location']['type_name'])->toBe('Outpost')
        ->and($price['starmap_location']['parent_name'])->toBe('Hurston')
        ->and($price['starmap_location']['star_system_name'])->toBe('Stanton')
        ->and($price['link'])->toContain($location->uuid)
        ->and($price['web_url'])->toContain($location->uuid);

    expect($response->json('data.uex_prices.rental'))->toBe([]);
});

it('expands vehicle rental prices with price_rent field', function (): void {
    VehicleData::factory()
        ->for($this->vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'uex_purchase_prices' => null,
            'uex_rental_prices' => [
                [
                    'terminal_code' => 'ARC_ADM',
                    'terminal_name' => 'Area18 Admin',
                    'starmap_location_uuid' => null,
                    'starmap_location_data_id' => null,
                    'price_rent' => 45000.0,
                    'game_version' => '4.4.0-TEST',
                    'date_updated' => '2026-04-19T08:00:00Z',
                ],
            ],
        ]);

    $response = $this->getJson("/api/v2/vehicles/{$this->vehicle->uuid}");

    $response->assertSuccessful();

    $rental = $response->json('data.uex_prices.rental');
    expect($rental)->toHaveCount(1);
    expect($rental[0]['price_rent'])->toBe(45000)
        ->and($rental[0]['game_version'])->toBe('4.4.0-TEST')
        ->and($rental[0]['starmap_location'])->toBeNull()
        ->and($rental[0]['link'])->toBeNull()
        ->and($rental[0]['web_url'])->toBeNull();
});

it('sorts prices by star system ascending then date_updated descending', function (): void {
    $stantonLocation = StarmapLocation::factory()->create();
    $stantonStarLocation = StarmapLocation::factory()->create();
    $stantonStarData = StarmapLocationData::factory()
        ->for($stantonStarLocation, 'location')
        ->for($this->gameVersion, 'gameVersion')
        ->create(['name' => 'Stanton']);
    $stantonParentLocation = StarmapLocation::factory()->create();
    $stantonParentData = StarmapLocationData::factory()
        ->for($stantonParentLocation, 'location')
        ->for($this->gameVersion, 'gameVersion')
        ->create([
            'name' => 'Hurston',
            'star_data_id' => $stantonStarData->id,
        ]);
    $stantonTerminalData = StarmapLocationData::factory()
        ->for($stantonLocation, 'location')
        ->for($this->gameVersion, 'gameVersion')
        ->create([
            'name' => 'Lorville',
            'parent_data_id' => $stantonParentData->id,
            'star_data_id' => $stantonStarData->id,
        ]);

    $pyroLocation = StarmapLocation::factory()->create();
    $pyroStarLocation = StarmapLocation::factory()->create();
    $pyroStarData = StarmapLocationData::factory()
        ->for($pyroStarLocation, 'location')
        ->for($this->gameVersion, 'gameVersion')
        ->create(['name' => 'Pyro']);
    $pyroParentLocation = StarmapLocation::factory()->create();
    $pyroParentData = StarmapLocationData::factory()
        ->for($pyroParentLocation, 'location')
        ->for($this->gameVersion, 'gameVersion')
        ->create([
            'name' => 'Pyro Planet',
            'star_data_id' => $pyroStarData->id,
        ]);
    $pyroTerminalData = StarmapLocationData::factory()
        ->for($pyroLocation, 'location')
        ->for($this->gameVersion, 'gameVersion')
        ->create([
            'name' => 'Orison',
            'parent_data_id' => $pyroParentData->id,
            'star_data_id' => $pyroStarData->id,
        ]);

    VehicleData::factory()
        ->for($this->vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'uex_purchase_prices' => [
                [
                    'terminal_code' => 'PYRO',
                    'terminal_name' => 'Pyro Terminal',
                    'starmap_location_uuid' => $pyroLocation->uuid,
                    'starmap_location_data_id' => $pyroTerminalData->id,
                    'price_buy' => 3000.0,
                    'date_updated' => '2026-04-15T00:00:00Z',
                ],
                [
                    'terminal_code' => 'STANTON_OLD',
                    'terminal_name' => 'Stanton Old',
                    'starmap_location_uuid' => $stantonLocation->uuid,
                    'starmap_location_data_id' => $stantonTerminalData->id,
                    'price_buy' => 1000.0,
                    'date_updated' => '2026-01-01T00:00:00Z',
                ],
                [
                    'terminal_code' => 'STANTON_NEW',
                    'terminal_name' => 'Stanton New',
                    'starmap_location_uuid' => $stantonLocation->uuid,
                    'starmap_location_data_id' => $stantonTerminalData->id,
                    'price_buy' => 2000.0,
                    'date_updated' => '2026-04-01T00:00:00Z',
                ],
            ],
            'uex_rental_prices' => null,
        ]);

    $response = $this->getJson("/api/v2/vehicles/{$this->vehicle->uuid}");

    $purchase = $response->json('data.uex_prices.purchase');
    expect($purchase[0]['terminal_code'])->toBe('PYRO')
        ->and($purchase[1]['terminal_code'])->toBe('STANTON_NEW')
        ->and($purchase[2]['terminal_code'])->toBe('STANTON_OLD');
});
