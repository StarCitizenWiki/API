<?php

declare(strict_types=1);

use App\Http\Resources\Game\ItemSpecification\ClothingResource;
use App\Http\Resources\Game\ItemSpecification\SuitArmorResource;

it('clothing resource extracts gforce_resistance scalar', function (): void {
    $resource = new ClothingResource([
        'classification' => 'FPS.Clothing.Torso',
        'type' => 'Char_Clothing_Torso_1.UNDEFINED',
        'name' => 'Test Jacket',
        'data' => [
            'stdItem' => [
                'GForceResistance' => ['Value' => -0.125],
                'TemperatureResistance' => ['Minimum' => -10, 'Maximum' => 40],
            ],
        ],
    ]);

    $result = $resource->resolve();

    expect($result['gforce_resistance'])->toBe(-0.125);
});

it('clothing resource returns null gforce_resistance when absent', function (): void {
    $resource = new ClothingResource([
        'classification' => 'FPS.Clothing.Torso',
        'type' => 'Char_Clothing_Torso_1.UNDEFINED',
        'name' => 'Test Jacket',
        'data' => [
            'stdItem' => [
                'TemperatureResistance' => ['Minimum' => -10, 'Maximum' => 40],
            ],
        ],
    ]);

    $result = $resource->resolve();

    expect($result['gforce_resistance'])->toBeNull();
});

it('suit armor resource extracts gforce_resistance scalar', function (): void {
    $resource = new SuitArmorResource([
        'classification' => 'FPS.Armor.Core',
        'type' => 'Char_Armor_Torso.Light',
        'name' => 'Test Armor Core',
        'data' => [
            'stdItem' => [
                'GForceResistance' => ['Value' => 0.9],
                'SuitArmor' => [
                    'DamageResistance' => [
                        'Impact' => 0.5,
                    ],
                ],
            ],
        ],
    ]);

    $result = $resource->resolve();

    expect($result['gforce_resistance'])->toBe(0.9);
});

it('suit armor resource returns null gforce_resistance when absent', function (): void {
    $resource = new SuitArmorResource([
        'classification' => 'FPS.Armor.Core',
        'type' => 'Char_Armor_Torso.Light',
        'name' => 'Test Armor Core',
        'data' => [
            'stdItem' => [
                'SuitArmor' => [
                    'DamageResistance' => [
                        'Impact' => 0.5,
                    ],
                ],
            ],
        ],
    ]);

    $result = $resource->resolve();

    expect($result['gforce_resistance'])->toBeNull();
});
