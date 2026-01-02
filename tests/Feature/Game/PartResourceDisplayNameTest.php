<?php

declare(strict_types=1);

use App\Http\Resources\Game\Vehicle\PartResource;

describe('PartResource display name generation', function () {
    it('handles single position prefix', function () {
        $resource = new PartResource(['Name' => 'LEFT_WING', 'DamageMax' => 1000]);
        $result = $resource->toArray(request());

        expect($result['display_name'])->toBe('Wing (left)');
    });

    it('handles multiple position words', function () {
        $resource = new PartResource(['Name' => 'FRONT_MID_LOWER_WING', 'DamageMax' => 1000]);
        $result = $resource->toArray(request());

        expect($result['display_name'])->toBe('Wing (front mid lower)');
    });

    it('handles no position prefix', function () {
        $resource = new PartResource(['Name' => 'NOSE', 'DamageMax' => 2500]);
        $result = $resource->toArray(request());

        expect($result['display_name'])->toBe('Nose');
    });

    it('handles position as entire name', function () {
        $resource = new PartResource(['Name' => 'LEFT', 'DamageMax' => 500]);
        $result = $resource->toArray(request());

        expect($result['display_name'])->toBe('Left');
    });

    it('handles right position prefix', function () {
        $resource = new PartResource(['Name' => 'RIGHT_ENGINE', 'DamageMax' => 3000]);
        $result = $resource->toArray(request());

        expect($result['display_name'])->toBe('Engine (right)');
    });

    it('handles tail position prefix', function () {
        $resource = new PartResource(['Name' => 'TAIL_SECTION', 'DamageMax' => 1500]);
        $result = $resource->toArray(request());

        expect($result['display_name'])->toBe('Section (tail)');
    });

    it('handles top position prefix', function () {
        $resource = new PartResource(['Name' => 'TOP_HATCH', 'DamageMax' => 800]);
        $result = $resource->toArray(request());

        expect($result['display_name'])->toBe('Hatch (top)');
    });

    it('handles bottom position prefix', function () {
        $resource = new PartResource(['Name' => 'BOTTOM_PANEL', 'DamageMax' => 600]);
        $result = $resource->toArray(request());

        expect($result['display_name'])->toBe('Panel (bottom)');
    });

    it('handles front position prefix', function () {
        $resource = new PartResource(['Name' => 'FRONT_SENSOR', 'DamageMax' => 400]);
        $result = $resource->toArray(request());

        expect($result['display_name'])->toBe('Sensor (front)');
    });

    it('handles mid position prefix', function () {
        $resource = new PartResource(['Name' => 'MID_SECTION', 'DamageMax' => 2000]);
        $result = $resource->toArray(request());

        expect($result['display_name'])->toBe('Section (mid)');
    });

    it('handles lower position prefix', function () {
        $resource = new PartResource(['Name' => 'LOWER_HULL', 'DamageMax' => 2500]);
        $result = $resource->toArray(request());

        expect($result['display_name'])->toBe('Hull (lower)');
    });

    it('handles upper position prefix', function () {
        $resource = new PartResource(['Name' => 'UPPER_DECK', 'DamageMax' => 1800]);
        $result = $resource->toArray(request());

        expect($result['display_name'])->toBe('Deck (upper)');
    });

    it('handles back position prefix', function () {
        $resource = new PartResource(['Name' => 'BACK_THRUSTER', 'DamageMax' => 1200]);
        $result = $resource->toArray(request());

        expect($result['display_name'])->toBe('Thruster (back)');
    });

    it('handles rear position prefix', function () {
        $resource = new PartResource(['Name' => 'REAR_STABILIZER', 'DamageMax' => 900]);
        $result = $resource->toArray(request());

        expect($result['display_name'])->toBe('Stabilizer (rear)');
    });

    it('handles multiple underscores in name without position', function () {
        $resource = new PartResource(['Name' => 'CARGO_BAY_DOOR', 'DamageMax' => 1000]);
        $result = $resource->toArray(request());

        expect($result['display_name'])->toBe('Cargo bay door');
    });

    it('handles complex multi-position name', function () {
        $resource = new PartResource(['Name' => 'FRONT_LOWER_LEFT_ENGINE', 'DamageMax' => 3500]);
        $result = $resource->toArray(request());

        expect($result['display_name'])->toBe('Engine (front lower left)');
    });

    it('handles null name', function () {
        $resource = new PartResource(['Name' => null, 'DamageMax' => 1000]);
        $result = $resource->resolve(request());

        expect($result['display_name'])->toBeNull();
    });

    it('preserves children structure', function () {
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

    it('handles body part name', function () {
        $resource = new PartResource(['Name' => 'BODY', 'DamageMax' => 5000]);
        $result = $resource->toArray(request());

        expect($result['display_name'])->toBe('Body');
    });
});
