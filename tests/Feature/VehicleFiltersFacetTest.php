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

describe('denormalized column facets', function (): void {
    it('returns shield_face_type facet from column', function (): void {
        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Ship A',
                'shield_face_type' => 'Bubble',
                'data' => [
                    'ShieldController' => ['FaceType' => 'Bubble'],
                ],
            ]);

        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Ship B',
                'shield_face_type' => 'FrontBack',
                'data' => [
                    'ShieldController' => ['FaceType' => 'FrontBack'],
                ],
            ]);

        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Ship C',
                'shield_face_type' => 'Bubble',
                'data' => [
                    'ShieldController' => ['FaceType' => 'Bubble'],
                ],
            ]);

        $response = $this->getJson(route('vehicles.filters', [
            'version' => $this->version->code,
        ]));

        $response->assertOk();
        expect($response->json('filters')['shield.face_type'])->toEqualCanonicalizing([
            ['value' => 'Bubble', 'label' => 'Bubble', 'count' => 2],
            ['value' => 'FrontBack', 'label' => 'FrontBack', 'count' => 1],
        ]);
    });

    it('returns max_medical_tier facet from column', function (): void {
        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Medical Ship A',
                'max_medical_tier' => 'T3',
                'data' => [
                    'Seating' => [
                        'MedicalBeds' => [
                            ['Tier' => 'T3', 'Count' => 1],
                        ],
                    ],
                ],
            ]);

        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Medical Ship B',
                'max_medical_tier' => 'T1',
                'data' => [
                    'Seating' => [
                        'MedicalBeds' => [
                            ['Tier' => 'T1', 'Count' => 2],
                        ],
                    ],
                ],
            ]);

        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Non-Medical Ship',
                'max_medical_tier' => null,
                'data' => [],
            ]);

        $response = $this->getJson(route('vehicles.filters', [
            'version' => $this->version->code,
        ]));

        $response->assertOk()
            ->assertJsonPath('filters.max_medical_tier', [
                ['value' => 'T1', 'label' => 'T1', 'count' => 1],
                ['value' => 'T3', 'label' => 'T3', 'count' => 1],
            ]);
    });

    it('excludes null max_medical_tier from facet values', function (): void {
        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'No Med Ship',
                'max_medical_tier' => null,
            ]);

        $response = $this->getJson(route('vehicles.filters', [
            'version' => $this->version->code,
        ]));

        $response->assertOk();
        expect($response->json('filters.max_medical_tier'))->toBe([]);
    });
});

describe('empty filter values', function (): void {
    beforeEach(function (): void {
        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Numeric Ship',
                'mass_total' => 50000,
                'crew_min' => 2,
                'max_medical_tier' => 'T3',
            ]);

        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Other Ship',
                'mass_total' => 12000,
                'crew_min' => 1,
                'max_medical_tier' => null,
            ]);
    });

    it('ignores an empty numeric filter on the index endpoint instead of 500ing', function (): void {
        $response = $this->getJson(route('v2.vehicles.index', [
            'version' => $this->version->code,
            'filter' => ['mass_total' => ''],
        ]));

        $response->assertOk();
        // Empty value applies no constraint, so both ships are returned.
        expect($response->json('data'))->toHaveCount(2);
    });

    it('ignores an empty aliased numeric filter', function (): void {
        $response = $this->getJson(route('v2.vehicles.index', [
            'version' => $this->version->code,
            'filter' => ['crew.min' => ''],
        ]));

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(2);
    });

    it('ignores an empty filter on the filters endpoint', function (): void {
        $response = $this->getJson(route('vehicles.filters', [
            'version' => $this->version->code,
            'filter' => ['max_medical_tier' => ''],
        ]));

        $response->assertOk();
        // Empty filter does not constrain the facet, so both tiers appear.
        expect($response->json('filters.max_medical_tier'))
            ->toBe([['value' => 'T3', 'label' => 'T3', 'count' => 1]]);
    });

    it('still applies a real numeric filter value', function (): void {
        $response = $this->getJson(route('v2.vehicles.index', [
            'version' => $this->version->code,
            'filter' => ['mass_total' => 50000],
        ]));

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1)
            ->and($response->json('data.0.mass_total'))->toBe(50000);
    });
});
