<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Resources\HasDepositFormatting;

class ResolveMiningTypeTest
{
    use HasDepositFormatting;

    public static function testMiningType(string $groupName): array
    {
        return self::resolveMiningType($groupName);
    }
}

it('resolves mining type from group name', function (string $groupName, string $expectedLabel, int $expectedSortOrder): void {
    $result = ResolveMiningTypeTest::testMiningType($groupName);

    expect($result)->toBe(['label' => $expectedLabel, 'sort_order' => $expectedSortOrder]);
})->with([
    'SpaceShip_Mineables' => ['SpaceShip_Mineables', 'Ship Mining', 0],
    'GroundVehicle_Mineables' => ['GroundVehicle_Mineables', 'Vehicle Mining', 1],
    'FPS_Mineables' => ['FPS_Mineables', 'FPS Mining', 2],
    'FPS mineables' => ['FPS mineables', 'FPS Mining', 2],
    'Harvestables' => ['Harvestables', 'Harvestables', 3],
    'Havestables' => ['Havestables', 'Harvestables', 3],
    'Plants' => ['Plants', 'Harvestables', 3],
    'Salvage' => ['Salvage', 'Salvage', 4],
    'SalvageScrap' => ['SalvageScrap', 'Salvage', 4],
    'SalvageableDebris_Foo' => ['SalvageableDebris_Foo', 'Salvage', 4],
]);

it('falls back to raw group name for unknown groups', function (): void {
    $result = ResolveMiningTypeTest::testMiningType('SomeUnknownGroup');

    expect($result)->toBe(['label' => 'SomeUnknownGroup', 'sort_order' => 99]);
});
