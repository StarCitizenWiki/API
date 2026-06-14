<?php

declare(strict_types=1);

use App\Http\Resources\Game\ItemSpecification\SuitArmorResource;
use App\Models\Game\ItemData;

describe('deriveType', function (): void {
    it('derives the type from the item name', function (): void {
        $item = new ItemData([
            'name' => 'Avenger Field Jacket',
            'type' => null,
            'class_name' => 'test_jacket',
            'classification' => 'FPS.Clothing.Torso_1',
            'data' => ['stdItem' => ['SuitArmor' => []]],
        ]);

        $result = (new SuitArmorResource($item))->resolve();

        expect($result['type'])->toBe('Jacket');
    });

    it('derives the type from the item type when the name carries no token', function (): void {
        $item = new ItemData([
            'name' => 'Mystery Garment',
            'type' => 'Char_Armor_Helmet',
            'class_name' => 'test_helmet',
            'classification' => 'FPS.Clothing.Helmet',
            'data' => ['stdItem' => ['SuitArmor' => []]],
        ]);

        $result = (new SuitArmorResource($item))->resolve();

        expect($result['type'])->toBe('Helmet');
    });

    it('returns null type without triggering a null-string deprecation when type is null (regression)', function (): void {
        // `game_item_data.type` is nullable. When it is null and the item name
        // carries no recognised token, deriveType() falls through to the inner
        // match that reads $itemType. Promoting deprecations to exceptions
        // proves the code no longer passes null to str_contains().
        $item = new ItemData([
            'name' => 'Test Undersuit',
            'type' => null,
            'class_name' => 'test_undersuit',
            'classification' => 'FPS.Clothing.Torso_1',
            'data' => ['stdItem' => ['SuitArmor' => ['DamageResistance' => ['Impact' => 0.5]]]],
        ]);

        set_error_handler(static function (int $errno, string $errstr): never {
            throw new ErrorException($errstr, $errno);
        }, E_DEPRECATED | E_USER_DEPRECATED | E_WARNING);

        try {
            $result = (new SuitArmorResource($item))->resolve();

            expect($result['type'])->toBeNull();
        } finally {
            restore_error_handler();
        }
    });
});

describe('deriveGarmentType', function (): void {
    it('parses the garment type token from the class_name', function (): void {
        $item = new ItemData([
            'name' => 'Workwear Apron',
            'type' => null,
            'class_name' => 'cbd_apparel_torso_01_apron_01_01',
            'classification' => 'FPS.Clothing.Torso_1',
            'data' => ['stdItem' => ['SuitArmor' => []]],
        ]);

        $result = (new SuitArmorResource($item))->resolve();

        expect($result['garment_type'])->toBe('Apron');
    });

    it('returns null garment_type when the class_name has no token', function (): void {
        $item = new ItemData([
            'name' => 'Mystery Garment',
            'type' => null,
            'class_name' => 'unknown_thing_01',
            'classification' => 'FPS.Clothing.Torso_1',
            'data' => ['stdItem' => ['SuitArmor' => []]],
        ]);

        $result = (new SuitArmorResource($item))->resolve();

        expect($result['garment_type'])->toBeNull();
    });
});
