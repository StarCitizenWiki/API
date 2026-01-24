<?php

declare(strict_types=1);

use App\Services\Game\WeaponSnapshotService;

beforeEach(function () {
    $this->service = app(WeaponSnapshotService::class);
});

test('it returns all zeros for empty loadout', function () {
    $result = $this->service->compute([]);

    expect($result)->toBe([
        'pilot_guns_count' => 0,
        'turrets_manned_count' => 0,
        'turrets_remote_count' => 0,
        'turret_weapon_guns_count' => 0,
        'missile_rack_count' => 0,
        'missile_count' => 0,
        'countermeasures_count' => 0,
    ]);
});

test('it counts pilot guns without turrets', function () {
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

    expect($result['pilot_guns_count'])->toBe(3)
        ->and($result['turrets_manned_count'])->toBe(0)
        ->and($result['turrets_remote_count'])->toBe(0)
        ->and($result['turret_weapon_guns_count'])->toBe(0);
});

test('it counts turret guns with manned turret', function () {
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

    expect($result['pilot_guns_count'])->toBe(0)
        ->and($result['turrets_manned_count'])->toBe(1)
        ->and($result['turrets_remote_count'])->toBe(0)
        ->and($result['turret_weapon_guns_count'])->toBe(2);
});

test('it counts turret guns with remote turret', function () {
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

    expect($result['pilot_guns_count'])->toBe(0)
        ->and($result['turrets_manned_count'])->toBe(0)
        ->and($result['turrets_remote_count'])->toBe(1)
        ->and($result['turret_weapon_guns_count'])->toBe(1);
});

test('it excludes gimbal mounts from turret counts by hardpoint name', function () {
    $loadout = [
        [
            'Type' => 'Turret.GimbalMount',
            'HardpointName' => 'hardpoint_gimbal_mount',
            'ClassName' => 'VHCL_GimbalMount_S3',
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

    expect($result['pilot_guns_count'])->toBe(1)
        ->and($result['turrets_manned_count'])->toBe(0)
        ->and($result['turrets_remote_count'])->toBe(0)
        ->and($result['turret_weapon_guns_count'])->toBe(0);
});

test('it excludes gimbal mounts from turret counts by class name', function () {
    $loadout = [
        [
            'Type' => 'Turret.Mount',
            'HardpointName' => 'hardpoint_mount',
            'ClassName' => 'VHCL_Mount_Gimbal_S3',
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

    expect($result['pilot_guns_count'])->toBe(1)
        ->and($result['turrets_manned_count'])->toBe(0)
        ->and($result['turrets_remote_count'])->toBe(0)
        ->and($result['turret_weapon_guns_count'])->toBe(0);
});

test('it excludes gimbal mounts from turret counts by subtype', function () {
    $loadout = [
        [
            'Type' => 'Turret.Gimbal',
            'HardpointName' => 'hardpoint_mount',
            'ClassName' => 'VHCL_Mount_S3',
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

    expect($result['pilot_guns_count'])->toBe(1)
        ->and($result['turrets_manned_count'])->toBe(0)
        ->and($result['turrets_remote_count'])->toBe(0)
        ->and($result['turret_weapon_guns_count'])->toBe(0);
});

test('it counts mixed pilot and turret guns', function () {
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

    expect($result['pilot_guns_count'])->toBe(1)
        ->and($result['turrets_manned_count'])->toBe(1)
        ->and($result['turret_weapon_guns_count'])->toBe(2);
});

test('it counts missile racks', function () {
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

    expect($result['missile_rack_count'])->toBe(2)
        ->and($result['missile_count'])->toBe(2);
});

test('it counts countermeasure launchers', function () {
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

    expect($result['countermeasures_count'])->toBe(2);
});

test('it handles deeply nested structures', function () {
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

    expect($result['pilot_guns_count'])->toBe(1);
});

test('it handles missing Type field gracefully', function () {
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

    expect($result['pilot_guns_count'])->toBe(1);
});

test('it handles malformed Type field without dot separator', function () {
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

    expect($result['pilot_guns_count'])->toBe(1);
});

test('it classifies UtilityTurret as manned when appropriate', function () {
    $loadout = [
        [
            'Type' => 'UtilityTurret.Manned',
            'HardpointName' => 'hardpoint_utility_turret',
            'ClassName' => 'CRUS_UtilityTurret_Manned',
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

    expect($result['turrets_manned_count'])->toBe(1)
        ->and($result['turret_weapon_guns_count'])->toBe(1);
});

test('it classifies UtilityTurret as remote when appropriate', function () {
    $loadout = [
        [
            'Type' => 'UtilityTurret.Remote',
            'HardpointName' => 'hardpoint_utility_turret',
            'ClassName' => 'CRUS_UtilityTurret_Remote',
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

    expect($result['turrets_remote_count'])->toBe(1)
        ->and($result['turret_weapon_guns_count'])->toBe(1);
});

test('it classifies TurretBase as manned when appropriate', function () {
    $loadout = [
        [
            'Type' => 'TurretBase.Manned',
            'HardpointName' => 'hardpoint_turret_base',
            'ClassName' => 'AEGS_TurretBase_Manned',
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

    expect($result['turrets_manned_count'])->toBe(1)
        ->and($result['turret_weapon_guns_count'])->toBe(1);
});

test('it classifies TurretBase as remote when appropriate', function () {
    $loadout = [
        [
            'Type' => 'TurretBase.Remote',
            'HardpointName' => 'hardpoint_turret_base',
            'ClassName' => 'AEGS_TurretBase_Remote',
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

    expect($result['turrets_remote_count'])->toBe(1)
        ->and($result['turret_weapon_guns_count'])->toBe(1);
});

test('it classifies turret without manned or remote indicators as remote', function () {
    $loadout = [
        [
            'Type' => 'Turret.Unknown',
            'HardpointName' => 'hardpoint_turret',
            'ClassName' => 'MISC_Turret',
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

    expect($result['turrets_manned_count'])->toBe(0)
        ->and($result['turrets_remote_count'])->toBe(1)
        ->and($result['turret_weapon_guns_count'])->toBe(1);
});
