<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;

function starmapLocationChain(GameVersion $version, string $starName, string $parentName, string $terminalName, string $parentType = 'Planet', string $terminalType = 'Outpost'): array
{
    $starLocation = StarmapLocation::factory()->create();
    $starData = StarmapLocationData::factory()
        ->for($starLocation, 'location')
        ->for($version, 'gameVersion')
        ->create(['name' => $starName]);

    $parentLocation = StarmapLocation::factory()->create();
    $parentData = StarmapLocationData::factory()
        ->for($parentLocation, 'location')
        ->for($version, 'gameVersion')
        ->create([
            'name' => $parentName,
            'type_name' => $parentType,
            'star_data_id' => $starData->id,
        ]);

    $terminalLocation = StarmapLocation::factory()->create(['slug' => strtolower(str_replace(' ', '-', $terminalName))]);
    $terminalData = StarmapLocationData::factory()
        ->for($terminalLocation, 'location')
        ->for($version, 'gameVersion')
        ->create([
            'name' => $terminalName,
            'type_name' => $terminalType,
            'parent_data_id' => $parentData->id,
            'star_data_id' => $starData->id,
        ]);

    return ['location' => $terminalLocation, 'data' => $terminalData];
}

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
    ['location' => $location, 'data' => $terminalLocationData] = starmapLocationChain($this->gameVersion, 'Stanton', 'Hurston', 'Lorville');

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
        ->and($price['starmap_location']['link'])->toContain($location->uuid)
        ->and($price['starmap_location']['web_url'])->toContain($location->uuid)
        ->and($response->json('data.uex_prices.rental'))->toBe([]);

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
    expect($rental)->toHaveCount(1)
        ->and($rental[0]['price_rent'])->toBe(45000)
        ->and($rental[0]['game_version'])->toBe('4.4.0-TEST')
        ->and($rental[0]['starmap_location'])->toBeNull();
});

it('sorts prices by star system ascending then date_updated descending', function (): void {
    ['location' => $stantonLocation, 'data' => $stantonTerminalData] = starmapLocationChain($this->gameVersion, 'Stanton', 'Hurston', 'Lorville');
    ['location' => $pyroLocation, 'data' => $pyroTerminalData] = starmapLocationChain($this->gameVersion, 'Pyro', 'Pyro Planet', 'Orison');

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
