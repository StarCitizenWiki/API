<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders the ammunition card heading', function (): void {
    $output = Blade::render('<x-items.ammunition-card :ammunition="$data" />', [
        'data' => [
            'speed' => 1000,
            'lifetime' => 5,
        ],
    ]);

    expect($output)->toContain('Ammunition');
});

it('renders the resource network card heading', function (): void {
    $output = Blade::render('<x-items.resource-network-card :resourceNetwork="$data" :itemType="\'Cooler\'" />', [
        'data' => [
            'usage' => [
                'power' => [
                    'minimum' => 10,
                    'maximum' => 20,
                ],
            ],
        ],
    ]);

    expect($output)
        ->toContain('Resource Network')
        ->toContain('Power Usage')
        ->not->toContain('Coolant Usage');
});

it('renders the seat card heading', function (): void {
    $output = Blade::render('<x-items.seat-card :seat="$data" />', [
        'data' => [
            'seat_type' => 'HOTAS_C_L',
            'has_ejection' => false,
        ],
    ]);

    expect($output)
        ->toContain('Seat')
        ->toContain('Seat Type')
        ->toContain('Has Ejection');
});

it('renders the shield card heading', function (): void {
    $output = Blade::render('<x-items.shield-card :shield="$data" />', [
        'data' => [
            'max_health' => 1000,
        ],
    ]);

    expect($output)
        ->toContain('Shield')
        ->toContain('Max Health');
});

it('renders item breadcrumbs', function (): void {
    $output = Blade::render('<x-items.item-breadcrumbs />');

    expect($output)->toContain('All Items');
});
