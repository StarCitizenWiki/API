<?php

declare(strict_types=1);

use App\Services\Game\WeaponSnapshotService;

beforeEach(function (): void {
    $this->service = app(WeaponSnapshotService::class);
});

it('returns all zeros for empty loadout', function (): void {
    $result = $this->service->compute([]);

    expect($result)->toBe(weaponSnapshot());
});

it('counts pilot guns without turrets', function (): void {
    $loadout = [
        [
            'Type' => 'WeaponGun.Gun',
            'HardpointName' => 'hardpoint_weapon_left',
            'ClassName' => 'KSAR_BallisticGatling_S2',
        ],
        [
            'Type' => 'WeaponGun.Gun',
            'HardpointName' => 'hardpoint_weapon_right',
            'ClassName' => 'KSAR_BallisticGatling_S2',
        ],
        [
            'Type' => 'WeaponGun.Gun',
            'HardpointName' => 'hardpoint_nose',
            'ClassName' => 'KLWE_LaserRepeater_S3',
        ],
    ];

    $result = $this->service->compute($loadout);

    expect($result)->toBe(weaponSnapshot([
        'pilot_guns_count' => 3,
    ]));
});

it('counts turret guns with manned turret', function (): void {
    $loadout = [
        [
            'Type' => 'Turret.MannedTurret',
            'HardpointName' => 'hardpoint_turret_manned',
            'ClassName' => 'AEGS_Retaliator_Manned_Turret_Top',
            'Loadout' => [
                [
                    'Type' => 'WeaponGun.Gun',
                    'HardpointName' => 'hardpoint_gun_left',
                    'ClassName' => 'BEHR_LaserCannon_S3',
                ],
                [
                    'Type' => 'WeaponGun.Gun',
                    'HardpointName' => 'hardpoint_gun_right',
                    'ClassName' => 'BEHR_LaserCannon_S3',
                ],
            ],
        ],
    ];

    $result = $this->service->compute($loadout);

    expect($result)->toBe(weaponSnapshot([
        'turrets_manned_count' => 1,
        'turret_weapon_guns_count' => 2,
    ]));
});

it('counts turret guns with remote turret', function (): void {
    $loadout = [
        [
            'Type' => 'Turret.RemoteTurret',
            'HardpointName' => 'hardpoint_turret_remote',
            'ClassName' => 'AEGS_Hammerhead_Remote_Turret',
            'Loadout' => [
                [
                    'Type' => 'WeaponGun.Gun',
                    'HardpointName' => 'hardpoint_gun_left',
                    'ClassName' => 'BEHR_LaserCannon_S4',
                ],
            ],
        ],
    ];

    $result = $this->service->compute($loadout);

    expect($result)->toBe(weaponSnapshot([
        'turrets_remote_count' => 1,
        'turret_weapon_guns_count' => 1,
    ]));
});

it('excludes gimbal mounts from turret counts', function (
    string $type,
    string $hardpointName,
    string $className
): void {
    $loadout = [
        [
            'Type' => $type,
            'HardpointName' => $hardpointName,
            'ClassName' => $className,
            'Loadout' => [
                [
                    'Type' => 'WeaponGun.Gun',
                    'HardpointName' => 'hardpoint_gun',
                    'ClassName' => 'BEHR_LaserCannon_S2',
                ],
            ],
        ],
    ];

    $result = $this->service->compute($loadout);

    expect($result)->toBe(weaponSnapshot([
        'pilot_guns_count' => 1,
    ]));
})->with([
    'gimbal identified by hardpoint name' => ['Turret.GimbalMount', 'hardpoint_gimbal_mount', 'VHCL_GimbalMount_S3'],
    'gimbal identified by class name' => ['Turret.Mount', 'hardpoint_mount', 'VHCL_Mount_Gimbal_S3'],
    'gimbal identified by subtype' => ['Turret.Gimbal', 'hardpoint_mount', 'VHCL_Mount_S3'],
]);

it('counts mixed pilot and turret guns', function (): void {
    $loadout = [
        [
            'Type' => 'WeaponGun.Gun',
            'HardpointName' => 'hardpoint_weapon_nose',
            'ClassName' => 'KLWE_LaserRepeater_S4',
        ],
        [
            'Type' => 'Turret.MannedTurret',
            'HardpointName' => 'hardpoint_turret_top',
            'ClassName' => 'CNOU_Nomad_Manned_Turret',
            'Loadout' => [
                [
                    'Type' => 'WeaponGun.Gun',
                    'HardpointName' => 'hardpoint_gun_left',
                    'ClassName' => 'BEHR_LaserCannon_S2',
                ],
                [
                    'Type' => 'WeaponGun.Gun',
                    'HardpointName' => 'hardpoint_gun_right',
                    'ClassName' => 'BEHR_LaserCannon_S2',
                ],
            ],
        ],
    ];

    $result = $this->service->compute($loadout);

    expect($result)->toBe(weaponSnapshot([
        'pilot_guns_count' => 1,
        'turrets_manned_count' => 1,
        'turret_weapon_guns_count' => 2,
    ]));
});

it('counts missile racks', function (): void {
    $loadout = [
        [
            'Type' => 'MissileLauncher.MissileRack',
            'HardpointName' => 'hardpoint_missile_left',
            'ClassName' => 'BEHR_MissileRack_S2',
            'Loadout' => [
                [
                    'Type' => 'Missile.Missile',
                    'HardpointName' => 'missile_1',
                    'ClassName' => 'TALN_Missile_S2',
                ],
            ],
        ],
        [
            'Type' => 'MissileLauncher.MissileRack',
            'HardpointName' => 'hardpoint_missile_right',
            'ClassName' => 'BEHR_MissileRack_S2',
            'Loadout' => [
                [
                    'Type' => 'Missile.Missile',
                    'HardpointName' => 'missile_1',
                    'ClassName' => 'TALN_Missile_S2',
                ],
            ],
        ],
    ];

    $result = $this->service->compute($loadout);

    expect($result)->toBe(weaponSnapshot([
        'missile_rack_count' => 2,
        'missile_count' => 2,
    ]));
});

it('counts countermeasure launchers', function (): void {
    $loadout = [
        [
            'Type' => 'WeaponDefensive.CountermeasureLauncher',
            'HardpointName' => 'hardpoint_countermeasure_left',
            'ClassName' => 'AEGS_CountermeasureLauncher',
        ],
        [
            'Type' => 'WeaponDefensive.CountermeasureLauncher',
            'HardpointName' => 'hardpoint_countermeasure_right',
            'ClassName' => 'AEGS_CountermeasureLauncher',
        ],
    ];

    $result = $this->service->compute($loadout);

    expect($result)->toBe(weaponSnapshot([
        'countermeasures_count' => 2,
    ]));
});

it('handles deeply nested structures', function (): void {
    $loadout = [
        [
            'Type' => 'Container.Level1',
            'HardpointName' => 'level_1',
            'ClassName' => 'Level1',
            'Loadout' => [
                [
                    'Type' => 'Container.Level2',
                    'HardpointName' => 'level_2',
                    'ClassName' => 'Level2',
                    'Loadout' => [
                        [
                            'Type' => 'Container.Level3',
                            'HardpointName' => 'level_3',
                            'ClassName' => 'Level3',
                            'Loadout' => [
                                [
                                    'Type' => 'Container.Level4',
                                    'HardpointName' => 'level_4',
                                    'ClassName' => 'Level4',
                                    'Loadout' => [
                                        [
                                            'Type' => 'WeaponGun.Gun',
                                            'HardpointName' => 'deep_gun',
                                            'ClassName' => 'DeepGun',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $result = $this->service->compute($loadout);

    expect($result)->toBe(weaponSnapshot([
        'pilot_guns_count' => 1,
    ]));
});

it('handles missing type field gracefully', function (): void {
    $loadout = [
        [
            'HardpointName' => 'hardpoint_unknown',
            'ClassName' => 'UnknownClass',
        ],
        [
            'Type' => 'WeaponGun.Gun',
            'HardpointName' => 'hardpoint_weapon',
            'ClassName' => 'ValidGun',
        ],
    ];

    $result = $this->service->compute($loadout);

    expect($result)->toBe(weaponSnapshot([
        'pilot_guns_count' => 1,
    ]));
});

it('handles malformed type field without dot separator', function (): void {
    $loadout = [
        [
            'Type' => 'WeaponGunNoDot',
            'HardpointName' => 'hardpoint_weapon',
            'ClassName' => 'MalformedGun',
        ],
        [
            'Type' => 'WeaponGun.Gun',
            'HardpointName' => 'hardpoint_weapon_valid',
            'ClassName' => 'ValidGun',
        ],
    ];

    $result = $this->service->compute($loadout);

    expect($result)->toBe(weaponSnapshot([
        'pilot_guns_count' => 1,
    ]));
});

it('classifies turret variants as manned or remote', function (
    string $type,
    string $hardpointName,
    string $className,
    array $expectedSnapshot
): void {
    $loadout = [
        [
            'Type' => $type,
            'HardpointName' => $hardpointName,
            'ClassName' => $className,
            'Loadout' => [
                [
                    'Type' => 'WeaponGun.Gun',
                    'HardpointName' => 'hardpoint_gun',
                    'ClassName' => 'BEHR_LaserCannon_S2',
                ],
            ],
        ],
    ];

    $result = $this->service->compute($loadout);

    expect($result)->toBe(weaponSnapshot($expectedSnapshot));
})->with([
    'utility turret manned' => ['UtilityTurret.Manned', 'hardpoint_utility_turret', 'CRUS_UtilityTurret_Manned', ['turrets_manned_count' => 1, 'turret_weapon_guns_count' => 1]],
    'utility turret remote' => ['UtilityTurret.Remote', 'hardpoint_utility_turret', 'CRUS_UtilityTurret_Remote', ['turrets_remote_count' => 1, 'turret_weapon_guns_count' => 1]],
    'turret base manned' => ['TurretBase.Manned', 'hardpoint_turret_base', 'AEGS_TurretBase_Manned', ['turrets_manned_count' => 1, 'turret_weapon_guns_count' => 1]],
    'turret base remote' => ['TurretBase.Remote', 'hardpoint_turret_base', 'AEGS_TurretBase_Remote', ['turrets_remote_count' => 1, 'turret_weapon_guns_count' => 1]],
    'turret without indicator defaults remote' => ['Turret.Unknown', 'hardpoint_turret', 'MISC_Turret', ['turrets_remote_count' => 1, 'turret_weapon_guns_count' => 1]],
]);

it('ignores malformed child loadouts while still counting parent ports', function (): void {
    $loadout = [
        [
            'Type' => 'Turret.RemoteTurret',
            'HardpointName' => 'hardpoint_turret_remote',
            'ClassName' => 'AEGS_Hammerhead_Remote_Turret',
            'Loadout' => 'invalid-loadout',
        ],
    ];

    $result = $this->service->compute($loadout);

    expect($result)->toBe(weaponSnapshot([
        'turrets_remote_count' => 1,
    ]));
});

/**
 * @param  array<string, int>  $overrides
 * @return array<string, int>
 */
function weaponSnapshot(array $overrides = []): array
{
    return array_replace([
        'pilot_guns_count' => 0,
        'turrets_manned_count' => 0,
        'turrets_remote_count' => 0,
        'turret_weapon_guns_count' => 0,
        'missile_rack_count' => 0,
        'missile_count' => 0,
        'countermeasures_count' => 0,
    ], $overrides);
}
