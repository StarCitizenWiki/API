<?php

declare(strict_types=1);

use App\Support\Filters\MissionScopeMapping;

it('classifies Unaffiliated_Generator with Manfred debug name as Investigation', function (string $debugName): void {
    $row = (object) [
        'mission_type' => null,
        'generator_class' => 'Unaffiliated_Generator',
        'debug_name' => $debugName,
    ];

    expect(MissionScopeMapping::scopeForRow($row))->toBe(MissionScopeMapping::INVESTIGATION);
})->with([
    'Manfred_Search',
    'Manfred_Foo',
    'Manfred',
]);

it('classifies Unaffiliated_Generator without Manfred debug name as Assassination', function (?string $debugName): void {
    $row = (object) [
        'mission_type' => null,
        'generator_class' => 'Unaffiliated_Generator',
        'debug_name' => $debugName,
    ];

    expect(MissionScopeMapping::scopeForRow($row))->toBe(MissionScopeMapping::ASSASSINATION);
})->with([
    'GillysPilotSchool_Mission01',
    'Unaffiliated_Pyro_RegionC_VH_Salvage',
    null,
]);

it('classifies Unaffiliated_Generator with Priority mission type as Security regardless of debug name', function (): void {
    $row = (object) [
        'mission_type' => 'Priority',
        'generator_class' => 'Unaffiliated_Generator',
        'debug_name' => 'Manfred_Search',
    ];

    expect(MissionScopeMapping::scopeForRow($row))->toBe(MissionScopeMapping::SECURITY);
});

it('classifies Ruto_Generator as Assassination', function (): void {
    $row = (object) [
        'mission_type' => null,
        'generator_class' => 'Ruto_Generator',
        'debug_name' => 'Ruto_Stanton1_Illegal_DestroyItems_Narcotics_Easy',
    ];

    expect(MissionScopeMapping::scopeForRow($row))->toBe(MissionScopeMapping::ASSASSINATION);
});

it('classifies Bounty Hunter missions', function (): void {
    $row = (object) [
        'mission_type' => 'Bounty Hunter',
        'generator_class' => null,
        'debug_name' => null,
    ];

    expect(MissionScopeMapping::scopeForRow($row))->toBe(MissionScopeMapping::BOUNTY_HUNTER);
});

it('classifies Hauling missions', function (): void {
    $row = (object) [
        'mission_type' => 'Hauling',
        'generator_class' => null,
        'debug_name' => null,
    ];

    expect(MissionScopeMapping::scopeForRow($row))->toBe(MissionScopeMapping::HAULING);
});

it('classifies Mining missions', function (): void {
    $row = (object) [
        'mission_type' => 'Ship Mining',
        'generator_class' => null,
        'debug_name' => null,
    ];

    expect(MissionScopeMapping::scopeForRow($row))->toBe(MissionScopeMapping::MINING);
});

it('classifies Investigation missions by mission type', function (): void {
    $row = (object) [
        'mission_type' => 'Investigation',
        'generator_class' => null,
        'debug_name' => null,
    ];

    expect(MissionScopeMapping::scopeForRow($row))->toBe(MissionScopeMapping::INVESTIGATION);
});
