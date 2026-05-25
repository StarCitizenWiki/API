<?php

declare(strict_types=1);

use App\Http\Resources\Game\Vehicle\PortResource;
use App\Support\Game\HardpointCategory;
use App\Support\Game\HardpointRow;

describe('hardpoint name-based categorization fallback', function () {
    it('categorizes weapon rack as Other, not Weapons', function () {
        $resource = new PortResource([
            'HardpointName' => 'hardpoint_weapon_rack',
            'Type' => '.',
            'ItemTypes' => [],
            'Loadout' => [],
        ]);

        $result = $resource->resolve(request());

        expect($result['category_label'])->toBe('Other');
    });

    it('categorizes quantum interdiction generator as QED, not Quantum Drives', function () {
        $resource = new PortResource([
            'HardpointName' => 'hardpoint_quantum_interdiction_generator',
            'Type' => '.',
            'ItemTypes' => [],
            'Loadout' => [],
        ]);

        $result = $resource->resolve(request());

        expect($result['category_label'])->toBe('QED');
    });

    it('categorizes quantum drive as Quantum Drives', function () {
        $resource = new PortResource([
            'HardpointName' => 'hardpoint_quantum_drive',
            'Type' => '.',
            'ItemTypes' => [],
            'Loadout' => [],
        ]);

        $result = $resource->resolve(request());

        expect($result['category_label'])->toBe('Quantum Drives');
    });

    it('categorizes weapon hardpoints as Weapons', function () {
        $resource = new PortResource([
            'HardpointName' => 'hardpoint_weapon_nose',
            'Type' => '.',
            'ItemTypes' => [],
            'Loadout' => [],
        ]);

        $result = $resource->resolve(request());

        expect($result['category_label'])->toBe('Weapons');
    });

    it('categorizes turret console as Crew Stations, not Turrets', function () {
        $resource = new PortResource([
            'HardpointName' => 'hardpoint_turret_console_left',
            'Type' => '.',
            'ItemTypes' => [],
            'Loadout' => [],
        ]);

        $result = $resource->resolve(request());

        expect($result['category_label'])->toBe('Crew Stations');
    });

    it('categorizes turret console access as Crew Stations, not Turrets', function () {
        $resource = new PortResource([
            'HardpointName' => 'hardpoint_turret_console_right_access',
            'Type' => '.',
            'ItemTypes' => [],
            'Loadout' => [],
        ]);

        $result = $resource->resolve(request());

        expect($result['category_label'])->toBe('Crew Stations');
    });

    it('categorizes actual turret hardpoints as Turrets', function () {
        $resource = new PortResource([
            'HardpointName' => 'hardpoint_turret_pilot',
            'Type' => '.',
            'ItemTypes' => [],
            'Loadout' => [],
        ]);

        $result = $resource->resolve(request());

        expect($result['category_label'])->toBe('Turrets');
    });
});

describe('hardpoint row stats', function () {
    it('uses fuel tank drain rate as the discharge secondary', function (): void {
        $row = HardpointRow::make([
            'name' => 'fuel_tank_port',
            'type' => 'FuelTank',
            'sizes' => [
                'min' => 1,
                'max' => 1,
            ],
            'equipped_item_uuid' => 'fuel-tank-uuid',
            'equipped_item' => [
                'uuid' => 'fuel-tank-uuid',
                'name' => 'Hydrogen Fuel Tank',
                'type' => 'FuelTank',
                'size' => 1,
                'fuel_tank' => [
                    'capacity' => 5000,
                    'discharge_rate' => 5000,
                    'drain_rate' => 12.5,
                ],
            ],
        ]);

        expect($row['primary_stat'])->toBe(5000)
            ->and($row['secondary_stats'])->toContain('12.5 discharge')
            ->and($row['secondary_stats'])->not->toContain('5k discharge');
    });
});

describe('hardpoint category layout', function () {
    it('configures every category emitted by vehicle port resources', function () {
        foreach (PortResource::categoryOrder() as $category) {
            expect(HardpointCategory::all())->toContain($category);
        }
    });

    it('derives primary categories from configured columns', function () {
        $columnCategories = collect(HardpointCategory::columns())->flatten()->values()->all();

        expect(HardpointCategory::primary())->toBe($columnCategories)
            ->and($columnCategories)->toHaveCount(count(array_unique($columnCategories)));
    });
});
