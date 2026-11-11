<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;

beforeEach(function (): void {
    $this->version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE.1',
        'channel' => 'testing',
        'is_default' => true,
    ]);

    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);
});

function createVehicleDataWithMass(Manufacturer $manufacturer, GameVersion $version, float $massTotal): VehicleData
{
    return VehicleData::factory()
        ->for(Vehicle::factory(), 'vehicle')
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => "Ship {$massTotal}",
            'mass_total' => $massTotal,
        ]);
}

describe('numeric range operators', function (): void {
    it('filters with a greater-than operator', function (): void {
        createVehicleDataWithMass($this->manufacturer, $this->version, 1000);
        createVehicleDataWithMass($this->manufacturer, $this->version, 5000);

        $response = $this->getJson('/api/vehicles?filter[mass_total]=>1000');

        $response->assertOk();
        expect($response->json('meta.total'))->toBe(1);
    });

    it('filters with a greater-than-or-equal operator', function (): void {
        createVehicleDataWithMass($this->manufacturer, $this->version, 1000);
        createVehicleDataWithMass($this->manufacturer, $this->version, 5000);

        $response = $this->getJson('/api/vehicles?filter[mass_total]=>=5000');

        $response->assertOk();
        expect($response->json('meta.total'))->toBe(1);
    });

    it('filters with a less-than operator', function (): void {
        createVehicleDataWithMass($this->manufacturer, $this->version, 1000);
        createVehicleDataWithMass($this->manufacturer, $this->version, 5000);

        $response = $this->getJson('/api/vehicles?filter[mass_total]=<5000');

        $response->assertOk();
        expect($response->json('meta.total'))->toBe(1);
    });

    it('filters with a not-equal operator', function (): void {
        createVehicleDataWithMass($this->manufacturer, $this->version, 1000);
        createVehicleDataWithMass($this->manufacturer, $this->version, 5000);

        $response = $this->getJson('/api/vehicles?filter[mass_total]=<>1000');

        $response->assertOk();
        expect($response->json('meta.total'))->toBe(1);
    });

    it('combines plain values in comma lists with AND, not whereIn OR', function (): void {
        createVehicleDataWithMass($this->manufacturer, $this->version, 1000);
        createVehicleDataWithMass($this->manufacturer, $this->version, 5000);
        createVehicleDataWithMass($this->manufacturer, $this->version, 9000);

        // Breaking change vs. the old whereIn filter: 1000 AND 5000 matches nothing.
        expect($this->getJson('/api/vehicles?filter[mass_total]=1000,5000')->json('meta.total'))->toBe(0);
        // A single plain value still matches exactly.
        expect($this->getJson('/api/vehicles?filter[mass_total]=1000')->json('meta.total'))->toBe(1);
    });

    it('combines operators in comma lists with AND (range intersection)', function (): void {
        createVehicleDataWithMass($this->manufacturer, $this->version, 1000);
        createVehicleDataWithMass($this->manufacturer, $this->version, 5000);
        createVehicleDataWithMass($this->manufacturer, $this->version, 9000);

        expect($this->getJson('/api/vehicles?filter[mass_total]=>=5000,<=6000')->json('meta.total'))->toBe(1);
    });

    it('combines exact values and operators with AND', function (): void {
        createVehicleDataWithMass($this->manufacturer, $this->version, 1000);
        createVehicleDataWithMass($this->manufacturer, $this->version, 5000);
        createVehicleDataWithMass($this->manufacturer, $this->version, 9000);

        // whereIn(1000) AND > 8000 matches nothing.
        expect($this->getJson('/api/vehicles?filter[mass_total]=1000,>8000')->json('meta.total'))->toBe(0);
    });

    it('supports operators on nested property filters', function (): void {
        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create(['name' => 'Small Crew', 'crew_min' => 1, 'crew_max' => 2]);

        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create(['name' => 'Big Crew', 'crew_min' => 4, 'crew_max' => 8]);

        $response = $this->getJson('/api/vehicles?filter[crew.min]=>3');

        $response->assertOk();
        expect($response->json('meta.total'))->toBe(1);
    });
});

describe('filter input validation', function (): void {
    it('rejects non-numeric values on numeric filters', function (): void {
        $this->getJson('/api/vehicles?filter[mass_total]=abc')->assertStatus(422);
    });

    it('rejects trailing garbage after an operator', function (): void {
        $this->getJson('/api/vehicles?filter[mass_total]=>abc')->assertStatus(422);
    });

    it('rejects junk on boolean filters', function (): void {
        $this->getJson('/api/vehicles?filter[is_vehicle]=maybe')->assertStatus(422);
    });

    it('accepts true and false on boolean filters', function (): void {
        createVehicleDataWithMass($this->manufacturer, $this->version, 1000);

        $this->getJson('/api/vehicles?filter[is_spaceship]=true')->assertOk();
        $this->getJson('/api/vehicles?filter[is_spaceship]=false')->assertOk();
        $this->getJson('/api/vehicles?filter[is_spaceship]=1')->assertOk();
        $this->getJson('/api/vehicles?filter[is_spaceship]=0')->assertOk();
    });

    it('rejects junk on dotted filter params', function (): void {
        $this->getJson('/api/vehicles?filter[crew.min]=abc')->assertStatus(422);
        $this->getJson('/api/vehicles?filter[signature.em_quantum]=>oops')->assertStatus(422);
    });

    it('accepts decimal values on double columns', function (): void {
        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create(['name' => 'Half Full', 'cargo_capacity' => 10.5]);

        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create(['name' => 'Full', 'cargo_capacity' => 99.9]);

        expect($this->getJson('/api/vehicles?filter[cargo_capacity]=>10.6')->json('meta.total'))->toBe(1);
    });

    it('rejects decimals on integer columns', function (): void {
        $this->getJson('/api/vehicles?filter[mass_total]=1000.5')->assertStatus(422);
    });

    it('rejects scientific notation on integer columns', function (): void {
        $this->getJson('/api/vehicles?filter[mass_total]=1e3')->assertStatus(422);
        $this->getJson('/api/vehicles?filter[mass_total]=1E3')->assertStatus(422);
    });

    it('accepts scientific notation on double columns', function (): void {
        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create(['name' => 'Exponent Ship', 'cargo_capacity' => 1000]);

        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create(['name' => 'Mantissa Ship', 'cargo_capacity' => 150]);

        expect($this->getJson('/api/vehicles?filter[cargo_capacity]=1e3')->json('meta.total'))->toBe(1);
        expect($this->getJson('/api/vehicles?filter[cargo_capacity]=1.5E2')->json('meta.total'))->toBe(1);
    });

    it('rejects empty numeric filter values', function (): void {
        $this->getJson('/api/vehicles?filter[mass_total]=')->assertStatus(422);
    });

    it('returns 422 not 500 for values outside the int4 column range', function (): void {
        createVehicleDataWithMass($this->manufacturer, $this->version, 1000);

        // mass_total is int4: Postgres would reject out-of-range literals at bind time with a 500.
        $this->getJson('/api/vehicles?filter[mass_total]=2147483647')->assertOk();
        $this->getJson('/api/vehicles?filter[mass_total]=-2147483648')->assertOk();
        $this->getJson('/api/vehicles?filter[mass_total]=2147483648')->assertStatus(422);
        $this->getJson('/api/vehicles?filter[mass_total]=-2147483649')->assertStatus(422);
        $this->getJson('/api/vehicles?filter[mass_total]=>2147483648')->assertStatus(422);
        $this->getJson('/api/vehicles?filter[mass_total]=1000,2147483648')->assertStatus(422);
    });
});

describe('filters endpoint validation', function (): void {
    it('rejects junk on the facet endpoint too', function (): void {
        $this->getJson('/api/vehicles/filters?filter[mass_total]=abc')->assertStatus(422);
        $this->getJson('/api/missions/filters?filter[illegal]=maybe')->assertStatus(422);
    });

    it('keeps serving facets for unvalidated string filters', function (): void {
        createVehicleDataWithMass($this->manufacturer, $this->version, 1000);

        $this->getJson('/api/vehicles/filters?filter[max_medical_tier]=')->assertOk();
    });
});
