<?php

declare(strict_types=1);

use App\Http\Resources\Game\Vehicle\PartResource;

describe('PartResource damage limits enrichment', function () {
    beforeEach(function (): void {
        PartResource::setDamageLimitsLookup([]);
    });

    it('includes destruction_damage when part has a matching entry', function (): void {
        PartResource::setDamageLimitsLookup([
            'Body' => ['destruction_damage' => 3600000],
        ]);

        $resource = new PartResource(['Name' => 'Body', 'DamageMax' => 3600000]);
        $result = $resource->resolve(request());

        expect($result)->toHaveKey('destruction_damage');
        expect($result['destruction_damage'])->toBe(3600000);
        expect($result)->not->toHaveKey('detach_damage');
    });

    it('includes detach_damage when part has a matching entry', function (): void {
        PartResource::setDamageLimitsLookup([
            'LEFT_WING' => ['detach_damage' => 600],
        ]);

        $resource = new PartResource(['Name' => 'LEFT_WING', 'DamageMax' => 600]);
        $result = $resource->resolve(request());

        expect($result)->toHaveKey('detach_damage');
        expect($result['detach_damage'])->toBe(600);
        expect($result)->not->toHaveKey('destruction_damage');
    });

    it('includes both destruction and detach damage when both exist', function (): void {
        PartResource::setDamageLimitsLookup([
            'Body' => ['destruction_damage' => 3600000, 'detach_damage' => 3000],
        ]);

        $resource = new PartResource(['Name' => 'Body', 'DamageMax' => 3600000]);
        $result = $resource->resolve(request());

        expect($result)->toHaveKey('destruction_damage');
        expect($result)->toHaveKey('detach_damage');
        expect($result['destruction_damage'])->toBe(3600000);
        expect($result['detach_damage'])->toBe(3000);
    });

    it('does not include damage fields when part has no matching entry', function (): void {
        PartResource::setDamageLimitsLookup([
            'Body' => ['destruction_damage' => 3600000],
        ]);

        $resource = new PartResource(['Name' => 'Nose', 'DamageMax' => 2750000]);
        $result = $resource->resolve(request());

        expect($result)->not->toHaveKey('destruction_damage');
        expect($result)->not->toHaveKey('detach_damage');
    });

    it('enriches nested children with damage limits', function (): void {
        PartResource::setDamageLimitsLookup([
            'Body' => ['destruction_damage' => 3600000],
            'LEFT_WING' => ['detach_damage' => 600],
        ]);

        $resource = new PartResource([
            'Name' => 'Body',
            'DamageMax' => 3600000,
            'Children' => [
                ['Name' => 'LEFT_WING', 'DamageMax' => 600],
            ],
        ]);
        $result = $resource->resolve(request());

        expect($result['destruction_damage'])->toBe(3600000);
        $children = $result['children']->resolve(request());
        expect($children[0])->toHaveKey('detach_damage');
        expect($children[0]['detach_damage'])->toBe(600);
    });

    it('handles HP differing from detach damage', function (): void {
        PartResource::setDamageLimitsLookup([
            'tail_wing' => ['detach_damage' => 1600],
        ]);

        $resource = new PartResource(['Name' => 'tail_wing', 'DamageMax' => 1]);
        $result = $resource->resolve(request());

        expect($result)->toHaveKey('detach_damage');
        expect($result['detach_damage'])->toBe(1600);
        expect($result['damage_max'])->toBe(1);
    });

    it('handles null name gracefully', function (): void {
        PartResource::setDamageLimitsLookup([
            'Body' => ['destruction_damage' => 3600000],
        ]);

        $resource = new PartResource(['Name' => null, 'DamageMax' => 0]);
        $result = $resource->resolve(request());

        expect($result)->not->toHaveKey('destruction_damage');
        expect($result)->not->toHaveKey('detach_damage');
    });
});
