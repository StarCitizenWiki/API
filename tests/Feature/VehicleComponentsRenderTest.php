<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

/**
 * Smoke tests for vehicle component rendering.
 *
 * These tests verify that vehicle Blade components can be rendered without errors.
 * They do not validate detailed output, only ensure components load successfully.
 */
it('renders vehicle-breadcrumbs', function () {
    $data = [
        'name' => 'Test Vehicle',
        'manufacturer' => ['name' => 'Test Manufacturer'],
    ];

    $output = Blade::render('<x-vehicles.vehicle-breadcrumbs :vehicle="$data" :manufacturerCode="$code" />', ['data' => $data, 'code' => 'TEST']);

    expect($output)->toContain('Test Vehicle');
});

it('renders insurance-logistics-card', function () {
    $data = [
        'insurance' => [
            'claim_time' => 30,
            'expedite_time' => 15,
            'expedite_cost' => 1000,
        ],
    ];

    $output = Blade::render('<x-vehicles.insurance-logistics-card :vehicle="$data" />', ['data' => $data]);

    expect($output)->toContain('grid-cols-1 sm:grid-cols-2');
});

it('renders defense-systems-card', function () {
    $data = [
        'shield' => ['hp' => 500],
        'health' => 1000,
    ];

    $output = Blade::render('<x-vehicles.defense-systems-card :vehicle="$data" />', ['data' => $data]);

    expect($output)->toContain('grid-cols-1 sm:grid-cols-2');
});

it('renders propulsion-card', function () {
    $data = [
        'fuel' => ['capacity' => 1000],
        'quantum' => [
            'fuel_capacity' => 5000,
            'quantum_speed' => 200,
        ],
    ];

    $output = Blade::render('<x-vehicles.propulsion-card :vehicle="$data" />', ['data' => $data]);

    expect($output)->toContain('grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4');
});

it('renders flight-characteristics-card', function () {
    $data = [
        'speed' => ['scm' => 200, 'max' => 400],
        'agility' => ['pitch' => 50, 'yaw' => 40, 'roll' => 60],
        'afterburner' => ['regen_time' => 10, 'regen_delay' => 2],
    ];

    $output = Blade::render('<x-vehicles.flight-characteristics-card :vehicle="$data" />', ['data' => $data]);

    expect(trim($output))->not->toBe('');
});

it('renders cargo-inventory-card', function () {
    $data = [
        'cargo_grids' => [
            [
                'scu' => 100,
                'dimension' => ['width' => 2, 'height' => 2, 'depth' => 2],
                'position' => ['x' => 0, 'y' => 0, 'z' => 0],
            ],
        ],
    ];

    $output = Blade::render('<x-vehicles.cargo-inventory-card :vehicle="$data" />', ['data' => $data]);

    expect(trim($output))->not->toBe('');
});

it('renders quick-summary-card', function () {
    $data = [
        'name' => 'Test Ship',
        'size_class' => '1',
        'manufacturer' => ['name' => 'RSI'],
        'career' => 'Combat',
        'role' => 'Fighter',
        'classification' => 'Space Superiority',
        'is_vehicle' => true,
        'is_spaceship' => true,
        'is_gravlev' => false,
        'crew' => ['min' => 1, 'max' => 2],
        'cargo_capacity' => 10,
        'vehicle_inventory' => 100,
        'speed' => ['scm' => 200, 'max' => 400],
        'health' => 1000,
        'shield' => ['hp' => 500],
        'mass_total' => 50000,
        'dimension' => ['length' => 20, 'width' => 15, 'height' => 5],
        'signature' => ['ir_shields' => 100, 'em_shields' => 50],
        'cross_section_max' => 20,
        'msrp' => 100000,
        'production_status' => 'In Production',
    ];

    $output = Blade::render('<x-vehicles.quick-summary-card :vehicle="$data" />', ['data' => $data]);

    expect(trim($output))->not->toBe('');
});

it('renders dimensions-mass-card', function () {
    $data = [
        'dimension' => ['length' => 20, 'width' => 15, 'height' => 5],
        'cross_section' => ['length' => 20, 'width' => 15, 'height' => 5],
        'mass_total' => 50000,
    ];

    $output = Blade::render('<x-vehicles.dimensions-mass-card :vehicle="$data" />', ['data' => $data]);

    expect($output)->toContain('grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4');
});

it('renders core-specs-card', function () {
    $data = [
        'size_class' => '1',
        'mass_total' => 50000,
        'dimension' => ['length' => 20, 'width' => 15, 'height' => 5],
        'cargo_capacity' => 10,
        'crew' => ['min' => 1, 'max' => 2],
        'speed' => ['scm' => 200, 'max' => 400],
        'health' => 1000,
        'shield' => ['hp' => 500],
        'signature' => ['ir_shields' => 100, 'em_shields' => 50],
        'cross_section' => ['length' => 20, 'width' => 15, 'height' => 5],
        'quantum' => ['fuel_capacity' => 5000, 'quantum_speed' => 200],
        'is_spaceship' => true,
    ];

    $output = Blade::render('<x-vehicles.core-specs-card :vehicle="$data" />', ['data' => $data]);

    expect(trim($output))->not->toBe('');
});

it('renders hardpoints-components-card', function () {
    $data = [
        'ports' => [
            [
                'name' => 'Test Port',
                'size' => 'S1',
                'display_name' => 'Port Display',
            ],
        ],
    ];

    $output = Blade::render('<x-vehicles.hardpoints-components-card :vehicle="$data" />', ['data' => $data]);

    expect(trim($output))->not->toBe('');
});

it('renders parts-turrets-card', function () {
    $data = [
        'parts' => [
            ['name' => 'Test Part', 'type' => 'main', 'damage_max' => 100],
        ],
        'turrets' => [
            'manned' => [['name' => 'Test Turret']],
            'remote' => [],
        ],
    ];

    $output = Blade::render('<x-vehicles.parts-turrets-card :vehicle="$data" />', ['data' => $data]);

    expect($output)->toContain('hidden sm:table-cell');
});

it('renders systems-signatures-card', function () {
    $data = [
        'signature' => ['ir_shields' => 100, 'em_shields' => 50],
        'cooling' => ['heat' => 100],
        'power' => ['main_power' => 1000],
    ];

    $output = Blade::render('<x-vehicles.systems-signatures-card :vehicle="$data" />', ['data' => $data]);

    expect($output)->toContain('overflow-x-auto');
});

it('renders port-display subcomponent', function () {
    $port = [
        'name' => 'Test Port',
        'size' => 'S1',
        'display_name' => 'Test Port Display',
    ];

    $output = Blade::render('<x-port-display :port="$port" />', ['port' => $port]);

    expect($output)->toContain('Test Port');
    expect($output)->toContain('sm:justify-between');
});

it('renders port-display without deactivation when power pool is unlimited', function () {
    $powerPools = [
        'Shield' => [
            'type' => 'DynamicPowerPool',
            'item_type' => 'Shield',
            'size' => -1,
        ],
    ];

    $port = [
        'name' => 'hardpoint_shield_01',
        'equipped_item' => [
            'name' => 'Test Shield',
            'type' => 'Shield.UNDEFINED',
            'size' => 2,
        ],
    ];

    $output = Blade::render('<x-port-display :port="$port" :power-pools="$powerPools" />', ['port' => $port, 'powerPools' => $powerPools]);

    expect($output)->toContain('Test Shield')
        ->and($output)->not->toContain('Deactivated')
        ->and($output)->not->toContain('opacity-60')
        ->and($output)->not->toContain('bg-error/5');
});

it('renders port-display without deactivation for first shield in limited pool', function () {
    $powerPools = [
        'Shield' => [
            'type' => 'DynamicPowerPool',
            'item_type' => 'Shield',
            'size' => 2,
        ],
    ];

    $port = [
        'name' => 'hardpoint_shield_01',
        'equipped_item' => [
            'name' => 'Test Shield 1',
            'type' => 'Shield.UNDEFINED',
            'size' => 2,
        ],
    ];

    $output = Blade::render('<x-port-display :port="$port" :power-pools="$powerPools" />', ['port' => $port, 'powerPools' => $powerPools]);

    expect($output)->toContain('Test Shield 1')
        ->and($output)->not->toContain('Deactivated')
        ->and($output)->not->toContain('opacity-60');
});

it('renders port-display with deactivation for shield exceeding pool limit', function () {
    $powerPools = [
        'Shield' => [
            'type' => 'DynamicPowerPool',
            'item_type' => 'Shield',
            'size' => 1,
        ],
    ];

    $port = [
        'name' => 'hardpoint_shield_03',
        'equipped_item' => [
            'name' => 'Test Shield 3',
            'type' => 'Shield',
            'size' => 2,
        ],
    ];

    $output = Blade::render('<x-port-display :port="$port" :power-pools="$powerPools" :category-index="3" />', ['port' => $port, 'powerPools' => $powerPools]);

    expect($output)->toContain('Test Shield 3')
        ->and($output)->toContain('Deactivated')
        ->and($output)->toContain('opacity-60')
        ->and($output)->toContain('bg-error/5')
        ->and($output)->toContain('border-error/30');
});

it('does not deactivate non-shield components with shield pool', function () {
    $powerPools = [
        'Shield' => [
            'type' => 'DynamicPowerPool',
            'item_type' => 'Shield',
            'size' => 1,
        ],
    ];

    $port = [
        'name' => 'hardpoint_weapon_01',
        'equipped_item' => [
            'name' => 'Test Gun',
            'type' => 'WeaponGun.Gun',
            'size' => 2,
        ],
    ];

    $output = Blade::render('<x-port-display :port="$port" :power-pools="$powerPools" />', ['port' => $port, 'powerPools' => $powerPools]);

    expect($output)->toContain('Test Gun')
        ->and($output)->not->toContain('Deactivated')
        ->and($output)->not->toContain('opacity-60');
});

it('renders metadata-footer-card', function () {
    $data = [
        'uuid' => '12345678-1234-1234-1234-123456789012',
        'classification' => 'Space Superiority',
        'class_name' => 'TestShip_Class',
        'version' => '4.0.0',
    ];

    $output = Blade::render('<x-vehicles.metadata-footer-card :vehicle="$data" />', ['data' => $data]);

    expect($output)->toContain($data['uuid']);
});

it('renders purchase-variants-card', function () {
    $data = [
        'shipmatrix_name' => 'Test Ship',
        'msrp' => 100000,
        'skus' => [
            [
                'sku' => 'TEST-001',
                'price' => 100000,
                'imported_at' => '2024-01-01T12:00:00',
            ],
        ],
    ];

    $output = Blade::render('<x-vehicles.purchase-variants-card :vehicle="$data" />', ['data' => $data]);

    expect($output)->toContain('$100,000')
        ->and($output)->toContain('hidden sm:table-cell');
});
