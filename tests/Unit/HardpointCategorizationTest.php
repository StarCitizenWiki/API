<?php

declare(strict_types=1);

use App\Http\Resources\Game\Vehicle\PortResource;

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
});
