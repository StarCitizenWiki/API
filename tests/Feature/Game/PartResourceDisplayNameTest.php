<?php

declare(strict_types=1);

use App\Http\Resources\Game\Vehicle\PartResource;

describe('PartResource display name generation', function () {
    it('handles name permutations', function (string $name, int $damageMax, string $expectedDisplayName): void {
        $resource = new PartResource(['Name' => $name, 'DamageMax' => $damageMax]);
        $result = $resource->toArray(request());

        expect($result['display_name'])->toBe($expectedDisplayName);
    })->with([
        'single position prefix' => ['LEFT_WING', 1000, 'Wing (left)'],
        'multiple position words' => ['FRONT_MID_LOWER_WING', 1000, 'Wing (front mid lower)'],
        'no position prefix' => ['NOSE', 2500, 'Nose'],
        'position as entire name' => ['LEFT', 500, 'Left'],
        'right position prefix' => ['RIGHT_ENGINE', 3000, 'Engine (right)'],
        'tail position prefix' => ['TAIL_SECTION', 1500, 'Section (tail)'],
        'top position prefix' => ['TOP_HATCH', 800, 'Hatch (top)'],
        'bottom position prefix' => ['BOTTOM_PANEL', 600, 'Panel (bottom)'],
        'front position prefix' => ['FRONT_SENSOR', 400, 'Sensor (front)'],
        'mid position prefix' => ['MID_SECTION', 2000, 'Section (mid)'],
        'lower position prefix' => ['LOWER_HULL', 2500, 'Hull (lower)'],
        'upper position prefix' => ['UPPER_DECK', 1800, 'Deck (upper)'],
        'back position prefix' => ['BACK_THRUSTER', 1200, 'Thruster (back)'],
        'rear position prefix' => ['REAR_STABILIZER', 900, 'Stabilizer (rear)'],
        'multiple underscores without position' => ['CARGO_BAY_DOOR', 1000, 'Cargo bay door'],
        'complex multi-position name' => ['FRONT_LOWER_LEFT_ENGINE', 3500, 'Engine (front lower left)'],
        'body part name' => ['BODY', 5000, 'Body'],
    ]);

    it('handles null name', function (): void {
        $resource = new PartResource(['Name' => null, 'DamageMax' => 1000]);
        $result = $resource->resolve(request());

        expect($result['display_name'])->toBeNull();
    });

    it('preserves children structure', function (): void {
        $resource = new PartResource([
            'Name' => 'LEFT_WING',
            'DamageMax' => 1000,
            'Children' => [
                ['Name' => 'LEFT_WING_TIP', 'DamageMax' => 500],
            ],
        ]);
        $result = $resource->resolve(request());

        expect($result)->toHaveKey('children');
        $children = $result['children']->resolve(request());
        expect($children)->toBeArray();
        expect($children)->toHaveCount(1);
        expect($children[0]['display_name'])->toBe('Wing tip (left)');
    });
});
