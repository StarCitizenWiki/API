<?php

declare(strict_types=1);

use App\Models\Game\StarmapLocationData;

it('formats designation from entity tag name with Roman numerals', function (string $tagName, ?string $expected): void {
    expect(StarmapLocationData::formatDesignation($tagName))->toBe($expected);
})->with([
    'Stanton1' => ['Stanton1', 'Stanton I'],
    'Stanton2' => ['Stanton2', 'Stanton II'],
    'Stanton3' => ['Stanton3', 'Stanton III'],
    'Stanton4' => ['Stanton4', 'Stanton IV'],
    'Pyro5c' => ['Pyro5c', 'Pyro Vc'],
    'ARC1' => ['ARC1', 'ARC I'],
    'StantonIV_MicroTech' => ['StantonIV_MicroTech', null],
    'ARC_L1' => ['ARC_L1', null],
    'Stanton-Pyro' => ['Stanton-Pyro', null],
    '' => ['', null],
]);

it('returns null designation when entity tag is not loaded', function (): void {
    $locationData = new StarmapLocationData;

    expect($locationData->designation)->toBeNull();
});
