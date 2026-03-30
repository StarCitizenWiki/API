<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders vehicle breadcrumbs with navigation labels', function (): void {
    $output = Blade::render('<x-vehicles.vehicle-breadcrumbs :vehicle="$data" :manufacturerCode="$code" />', [
        'data' => [
            'name' => 'Test Vehicle',
            'manufacturer' => [
                'name' => 'Test Manufacturer',
            ],
        ],
        'code' => 'TEST',
    ]);

    expect($output)
        ->toContain('All Vehicles')
        ->toContain('Test Manufacturer')
        ->toContain('Test Vehicle');
});

it('renders the port display with equipped item content', function (): void {
    $output = Blade::render('<x-port-display :port="$port" />', [
        'port' => [
            'name' => 'Test Port',
            'size' => 'S1',
            'equipped_item' => [
                'name' => 'Test Shield',
                'type' => 'Shield',
                'size' => 2,
            ],
        ],
    ]);

    expect($output)
        ->toContain('Test Port')
        ->toContain('Equippable Item Size')
        ->toContain('Equipped Item')
        ->toContain('Test Shield');
});

it('marks shield ports as deactivated when the power pool is exhausted', function (): void {
    $output = Blade::render('<x-port-display :port="$port" :power-pools="$powerPools" :category-index="3" />', [
        'port' => [
            'name' => 'hardpoint_shield_03',
            'equipped_item' => [
                'name' => 'Test Shield 3',
                'type' => 'Shield',
                'size' => 2,
            ],
        ],
        'powerPools' => [
            'Shield' => [
                'type' => 'DynamicPowerPool',
                'item_type' => 'Shield',
                'size' => 1,
            ],
        ],
    ]);

    expect($output)
        ->toContain('Test Shield 3')
        ->toContain('Deactivated');
});

it('renders purchase variants with price and sku content', function (): void {
    $output = Blade::render('<x-vehicles.purchase-variants-card :vehicle="$data" />', [
        'data' => [
            'shipmatrix_name' => 'Test Ship',
            'msrp' => 100000,
            'skus' => [
                [
                    'title' => 'TEST-001',
                    'price' => 100000,
                    'imported_at' => '12:00:00',
                ],
            ],
        ],
    ]);

    expect($output)
        ->toContain('Loaner & SKUs')
        ->toContain('MSRP')
        ->toContain('TEST-001')
        ->toContain('$100,000');
});
