<?php

declare(strict_types=1);

use App\Jobs\Game\ImportVehicleData;
use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.4.0-TEST',
        'channel' => 'test',
        'is_default' => true,
    ]);

    $this->vehicle = Vehicle::factory()->create();

    $this->vehicleData = VehicleData::factory()
        ->for($this->vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Ship',
            'class_name' => 'TEST_Ship',
            'data' => [
                'Loadout' => [
                    [
                        'HardpointName' => 'hardpoint_weapon_left',
                        'Position' => 'left',
                        'ClassName' => 'WeaponMount_S1',
                        'MinSize' => 1,
                        'MaxSize' => 1,
                        'Type' => 'WeaponGun.Gun',
                        'ItemTypes' => [
                            ['Type' => 'WeaponGun', 'SubType' => 'Gun'],
                        ],
                        'Editable' => true,
                        'EditableChildren' => false,
                    ],
                ],
            ],
        ]);
});

it('does not treat wildcard characters as show route patterns', function (): void {
    $this->getJson('/api/vehicles/'.rawurlencode('%'))
        ->assertNotFound();
});

it('returns v2 hardpoint format when accessing api/v2/vehicles endpoint', function (): void {
    $response = $this->getJson("/api/v2/vehicles/{$this->vehicle->uuid}");

    $response->assertSuccessful();
    $json = $response->json('data');

    expect($json)->toHaveKey('hardpoints');
    $hardpoint = $json['hardpoints'][0];

    // V2 format should have min_size and max_size (NOT sizes object)
    expect($hardpoint)->toHaveKeys(['name', 'position', 'class_name', 'min_size', 'max_size', 'type', 'sub_type']);
    expect($hardpoint)->toHaveKey('min_size', 1);
    expect($hardpoint)->toHaveKey('max_size', 1);

    // V2 format should NOT have these keys
    expect($hardpoint)->not->toHaveKey('sizes');
    expect($hardpoint)->not->toHaveKey('editable');
    expect($hardpoint)->not->toHaveKey('uuid');
    expect($hardpoint)->not->toHaveKey('compatible_types');

    // V2 uses 'children' not 'ports'
    expect($hardpoint)->not->toHaveKey('ports');
});

it('returns v3 port format when accessing api/v3/vehicles endpoint', function (): void {
    $response = $this->getJson("/api/v3/vehicles/{$this->vehicle->uuid}");

    $response->assertSuccessful();
    $json = $response->json('data');

    expect($json)->toHaveKey('ports');
    $port = $json['ports'][0];

    // V3 format should have sizes object (NOT min_size/max_size directly)
    expect($port)->toHaveKey('sizes');
    expect($port['sizes'])->toHaveKeys(['min', 'max']);
    expect($port['sizes']['min'])->toBe(1);
    expect($port['sizes']['max'])->toBe(1);

    // V3 format should NOT have min_size/max_size at root level
    expect($port)->not->toHaveKey('min_size');
    expect($port)->not->toHaveKey('max_size');

    // V3 format should have these additional keys
    expect($port)->toHaveKey('editable', true);
    expect($port)->toHaveKey('compatible_types');
    expect($port)->toHaveKey('type', 'WeaponGun');
    expect($port)->toHaveKey('subtype', 'Gun');
});

it('links imported mounting positions to exactly one classified loadout port', function (): void {
    Storage::fake('scunpacked');
    $path = 'ships/vanguard.json';
    $dump = json_decode(file_get_contents(base_path('tests/Fixtures/mounting-positions/populated.json')), true, flags: JSON_THROW_ON_ERROR);
    $dump['Manufacturer']['UUID'] = $this->manufacturer->uuid;
    $dump['IsSpaceship'] = true;
    $dump['Crew'] = 1;
    $dump['MountingPositions'][1]['CombatStats'] += ['DpsTotal' => 999, 'IsPilotSlaveable' => false, 'Weapons' => []];
    $dump['Weaponry'] = [
        'PilotDps' => 450,
        'TurretDps' => 750,
        'FixedWeapons' => ['DpsTotal' => 450],
    ];
    $nestedPortId = 'loadout.100.loadout.0';
    $dump['Loadout'][] = [
        'PortId' => 'loadout.100',
        'Loadout' => [[
            'PortId' => $nestedPortId,
            'HardpointName' => 'hardpoint_vacant_tool',
            'MinSize' => 1,
            'MaxSize' => 2,
        ]],
    ];
    $dump['MountingPositions'][] = [
        'PortId' => $nestedPortId,
        'HardpointName' => 'hardpoint_vacant_tool',
        'Occupied' => false,
        'MinSize' => 1,
        'MaxSize' => 2,
        'Payload' => [],
        'OperatingStations' => [['Kind' => 'Pilot', 'SeatPortId' => 'loadout.36', 'Name' => 'Stale vacant station']],
    ];
    $url = "/api/v3/vehicles/{$dump['UUID']}";

    Storage::disk('scunpacked')->put($path, json_encode(Arr::except($dump, 'MountingPositions'), JSON_THROW_ON_ERROR));
    (new ImportVehicleData($this->gameVersion->id, $path))->handle();
    $baseline = $this->getJson($url)->assertSuccessful()->assertJsonMissingPath('data.mounting_positions')->json('data');

    Storage::disk('scunpacked')->put($path, json_encode($dump, JSON_THROW_ON_ERROR));
    (new ImportVehicleData($this->gameVersion->id, $path))->handle();

    $candidate = $this->getJson($url)->assertSuccessful()
        ->assertJsonStructure(['data' => ['mounting_positions' => [['port_id']]]])
        ->assertJsonCount(4, 'data.mounting_positions')
        ->assertJsonPath('data.mounting_positions.0.mount_type', 'Gimbal')
        ->assertJsonPath('data.mounting_positions.0.combat_stats', ['dps' => 450, 'sustained_dps' => 0])
        ->assertJsonPath('data.mounting_positions.0.payload.0.combat_stats', ['dps' => 450, 'sustained_dps' => 0])
        ->assertJsonPath('data.mounting_positions.1.turret_role', 'Manned')
        ->assertJsonPath('data.mounting_positions.1.operating_stations.0.name', 'Seat inside turret')
        ->assertJsonMissingPath('data.mounting_positions.1.control_routes')
        ->assertJsonMissingPath('data.mounting_positions.3.operating_stations')
        ->assertJsonPath('data.mounting_positions.1.combat_stats', ['dps' => 750, 'sustained_dps' => 672, 'alpha' => 90])
        ->assertJsonPath('data.mounting_positions.1.is_pilot_slaveable', false)
        ->assertJsonPath('data.mounting_positions.2.mount_type', 'Rack')
        ->assertJsonPath('data.mounting_positions.2.combat_stats', ['missile_damage' => 1600])
        ->assertJsonPath('data.mounting_positions.2.payload.0.combat_stats', ['missile_damage' => 1600])
        ->assertJsonMissingPath('data.mounting_positions.2.payload.1.combat_stats')
        ->assertJsonPath('data.mounting_positions.3.occupied', false)
        ->assertJsonPath('data.mounting_positions.3.payload', [])
        ->assertJsonMissingPath('data.mounting_positions.3.mount_type')
        ->assertJsonMissingPath('data.mounting_positions.1.legacy_turret')
        ->assertJsonMissingPath('data.turrets.utility')
        ->assertJsonPath('data.crew.min', 1)
        ->assertJsonPath('data.weaponry.turret_dps', 750)
        ->assertJsonPath('data.weaponry.fixed_weapons.dps_total', 450)
        ->json('data');

    $portsById = [];
    $pending = $candidate['ports'];
    while ($pending !== []) {
        $port = array_pop($pending);
        $portsById[$port['port_id']][] = $port;
        array_push($pending, ...($port['ports'] ?? []));
    }

    $positionIds = array_column($candidate['mounting_positions'], 'port_id');
    expect($positionIds)->toBe(array_column($dump['MountingPositions'], 'PortId'))
        ->and(array_unique($positionIds))->toBe($positionIds);

    foreach ($candidate['mounting_positions'] as $position) {
        $linkedPorts = $portsById[$position['port_id']] ?? [];
        expect($linkedPorts)->toHaveCount(1);
        expect($linkedPorts[0]['mount_type'] ?? null)->toBe($position['mount_type'] ?? null)
            ->and($linkedPorts[0]['turret_role'] ?? null)->toBe($position['turret_role'] ?? null);

        foreach ($position['payload'] as $payload) {
            expect($portsById[$payload['port_id']] ?? [])->toHaveCount(1);
        }

        foreach ($position['operating_stations'] ?? [] as $station) {
            expect($portsById[$station['seat_port_id']] ?? [])->toHaveCount(1);
        }
    }

    $gunPortId = $candidate['mounting_positions'][0]['payload'][0]['port_id'];
    expect($positionIds)->not->toContain($gunPortId)
        ->and($portsById[$gunPortId])->toHaveCount(1);

    expect(Arr::only($candidate, ['crew', 'turrets', 'weaponry']))
        ->toBe(Arr::only($baseline, ['crew', 'turrets', 'weaponry']));

    $this->getJson("/api/vehicles/{$dump['UUID']}")->assertSuccessful()
        ->assertJsonPath('data.mounting_positions', $candidate['mounting_positions'])
        ->assertJsonPath('data.ports', $candidate['ports']);

    $indexRows = $this->getJson('/api/v3/vehicles')->assertSuccessful()->json('data');
    $indexRow = collect($indexRows)->firstWhere('uuid', $dump['UUID']);
    expect($indexRow)->toBeArray()->not->toHaveKey('mounting_positions');

    $this->getJson("/api/v2/vehicles/{$dump['UUID']}")->assertSuccessful()
        ->assertJsonMissingPath('data.mounting_positions')
        ->assertJsonMissingPath('data.hardpoints.0.port_id');
});

it('distinguishes empty mounting positions from an older imported dump', function (string $fixture, bool $hasMountingPositions): void {
    Storage::fake('scunpacked');
    $path = 'ships/fixture.json';
    $dump = json_decode(file_get_contents(base_path("tests/Fixtures/mounting-positions/{$fixture}.json")), true, flags: JSON_THROW_ON_ERROR);
    $dump['UUID'] = fake()->uuid();
    $dump['Manufacturer']['UUID'] = $this->manufacturer->uuid;
    Storage::disk('scunpacked')->put($path, json_encode($dump, JSON_THROW_ON_ERROR));
    (new ImportVehicleData($this->gameVersion->id, $path))->handle();

    $response = $this->getJson("/api/v3/vehicles/{$dump['UUID']}")->assertSuccessful()
        ->assertJsonPath('data.turrets', ['manned' => [], 'remote' => [], 'pdc' => []]);

    if ($hasMountingPositions) {
        $response->assertJsonPath('data.mounting_positions', []);
    } else {
        $response->assertJsonMissingPath('data.mounting_positions');
    }
})->with([
    'new empty dump' => ['empty', true],
    'older dump' => ['old-dump', false],
]);

function importMountingPositionsFixture(string $fixture): string
{
    Storage::fake('scunpacked');
    $path = 'ships/fixture.json';
    $dump = json_decode(file_get_contents(base_path("tests/Fixtures/mounting-positions/{$fixture}.json")), true, flags: JSON_THROW_ON_ERROR);
    $dump['UUID'] = fake()->uuid();
    $dump['Manufacturer']['UUID'] = test()->manufacturer->uuid;
    Storage::disk('scunpacked')->put($path, json_encode($dump, JSON_THROW_ON_ERROR));
    (new ImportVehicleData(test()->gameVersion->id, $path))->handle();

    return $dump['UUID'];
}

describe('canonical and v3 route parity', function (): void {
    it('serves equivalent mounting positions, linked ports, and notices for every snapshot type', function (string $fixture): void {
        $uuid = importMountingPositionsFixture($fixture);

        $canonical = $this->getJson("/api/vehicles/{$uuid}")->assertSuccessful();
        $v3 = $this->getJson("/api/v3/vehicles/{$uuid}")->assertSuccessful();

        // Exact payload parity: the unversioned route is the v3 shape.
        expect($canonical->json('data.mounting_positions'))->toBe($v3->json('data.mounting_positions'))
            ->and($canonical->json('data.ports'))->toBe($v3->json('data.ports'))
            ->and($canonical->json('meta.deprecated_fields'))->toBe($v3->json('meta.deprecated_fields'))
            ->and($canonical->json('data.turrets'))->toBe($v3->json('data.turrets'));
    })->with([
        'populated dump' => ['populated'],
        'new empty dump' => ['empty'],
        'older dump' => ['old-dump'],
    ]);

    it('keeps v2 detail responses and the index on the legacy shape', function (): void {
        $uuid = importMountingPositionsFixture('populated');

        $v2 = $this->getJson("/api/v2/vehicles/{$uuid}")->assertSuccessful()
            ->assertJsonMissingPath('data.mounting_positions');
        expect($v2->json('meta.deprecated_fields'))->not->toHaveKeys(['turrets.manned', 'turrets.remote', 'turrets.pdc']);

        $indexRow = collect($this->getJson('/api/vehicles')->assertSuccessful()->json('data'))
            ->firstWhere('uuid', $uuid);
        expect($indexRow)->toBeArray()->not->toHaveKey('mounting_positions');
    });
});

describe('turret deprecation notices', function (): void {
    it('announces the deprecated turret summaries only where mounting_positions is served', function (string $fixture, bool $hasMountingPositions): void {
        $uuid = importMountingPositionsFixture($fixture);

        $response = $this->getJson("/api/v3/vehicles/{$uuid}")->assertSuccessful();

        // Pre-existing notices survive the new entries untouched.
        $notices = $response->json('meta.deprecated_fields');
        expect($notices)->toHaveKeys(['sizes', 'emission', 'mass', 'shield_hp', 'shield_face_type']);

        if ($hasMountingPositions) {
            expect($response->json('data.mounting_positions'))->toBeArray();
            expect($notices)
                ->toHaveKey('turrets.manned')
                ->and($notices['turrets.manned'])->toBe('Use mounting_positions on v3 and unversioned detail responses instead and join each row to its loadout port by port_id.')
                ->and($notices['turrets.remote'])->toBe($notices['turrets.manned'])
                ->and($notices['turrets.pdc'])->toBe($notices['turrets.manned']);
        } else {
            expect($notices)->not->toHaveKeys(['turrets.manned', 'turrets.remote', 'turrets.pdc']);
            $response->assertJsonMissingPath('data.mounting_positions');
        }
    })->with([
        'populated dump' => ['populated', true],
        'new empty dump' => ['empty', true],
        'older dump' => ['old-dump', false],
    ]);

    it('keeps v2 responses and the index without turret deprecation notices', function (): void {
        $uuid = importMountingPositionsFixture('populated');

        $v2 = $this->getJson("/api/v2/vehicles/{$uuid}")->assertSuccessful()
            ->assertJsonMissingPath('data.mounting_positions');

        // The notice keys are literal dotted strings, so nested JSON paths would false-pass.
        expect($v2->json('meta.deprecated_fields'))->not->toHaveKeys(['turrets.manned', 'turrets.remote', 'turrets.pdc']);

        $index = $this->getJson('/api/v3/vehicles')->assertSuccessful();
        expect($index->json('meta'))->not->toHaveKey('deprecated_fields');
        $row = collect($index->json('data'))->firstWhere('uuid', $uuid);
        expect($row)->toBeArray()->not->toHaveKey('mounting_positions');
    });

    it('returns the legacy turret summaries unchanged alongside the notices', function (): void {
        $noticesUuid = importMountingPositionsFixture('old-dump');
        $oldNotices = $this->getJson("/api/v3/vehicles/{$noticesUuid}")->assertSuccessful()->json('meta.deprecated_fields');

        Storage::fake('scunpacked');
        $path = 'ships/fixture.json';
        $dump = json_decode(file_get_contents(base_path('tests/Fixtures/mounting-positions/populated.json')), true, flags: JSON_THROW_ON_ERROR);
        $dump['UUID'] = fake()->uuid();
        $dump['Manufacturer']['UUID'] = $this->manufacturer->uuid;
        // The fixture only fills the manned bucket, so clone its turret into the other buckets to compare every bucket populated.
        foreach (['RemoteTurrets', 'PdcTurrets'] as $bucket) {
            $turret = Arr::except($dump['MannedTurrets'][0], ['PartName', 'HardpointName']);
            $turret['PartName'] = $turret['HardpointName'] = strtolower($bucket).'_hardpoint';
            $dump[$bucket] = [$turret];
        }
        Storage::disk('scunpacked')->put($path, json_encode($dump, JSON_THROW_ON_ERROR));
        (new ImportVehicleData($this->gameVersion->id, $path))->handle();

        $uuid = $dump['UUID'];
        $v3 = $this->getJson("/api/v3/vehicles/{$uuid}")->assertSuccessful();
        $v2 = $this->getJson("/api/v2/vehicles/{$uuid}")->assertSuccessful();

        $notices = $v3->json('meta.deprecated_fields');
        expect(Arr::except($notices, ['turrets.manned', 'turrets.remote', 'turrets.pdc']))->toBe($oldNotices);

        expect($v3->json('data.turrets'))->toBe($v2->json('data.turrets'))
            ->and($v3->json('data.turrets'))->toHaveKeys(['manned', 'remote', 'pdc'])
            ->and($v3->json('data.turrets.manned'))->not->toBeEmpty()
            ->and($v3->json('data.turrets.remote'))->not->toBeEmpty()
            ->and($v3->json('data.turrets.pdc'))->not->toBeEmpty()
            ->and($v3->json('data.turrets.manned.0'))->not->toHaveKey('legacy_turret');
    });
});

it('returns cargo limits when accessing versioned vehicles endpoints', function (string $versionPrefix): void {
    $this->vehicleData->update([
        'data' => array_merge($this->vehicleData->data, [
            'CargoGrids' => [
                [
                    'MinSize' => ['X' => 1.0, 'Y' => 1.0, 'Z' => 1.0],
                    'MaxSize' => ['X' => 2.0, 'Y' => 2.0, 'Z' => 2.0],
                ],
                [
                    'MinSize' => ['X' => 2.0, 'Y' => 2.0, 'Z' => 2.0],
                    'MaxSize' => ['X' => 4.0, 'Y' => 4.0, 'Z' => 4.0],
                ],
            ],
        ]),
    ]);

    $this->getJson("/{$versionPrefix}/vehicles/{$this->vehicle->uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.cargo_limits', [
            'min_size' => ['x' => 1, 'y' => 1, 'z' => 1],
            'min_scu_box' => 1,
            'max_size' => ['x' => 4, 'y' => 4, 'z' => 4],
            'max_scu_box' => 8,
        ]);
})->with(['api/v2', 'api/v3']);

it('returns v3 port format when accessing api/vehicles endpoint without version prefix', function (): void {
    $response = $this->getJson("/api/vehicles/{$this->vehicle->uuid}");

    $response->assertSuccessful();
    $json = $response->json('data');

    expect($json)->toHaveKey('ports');
    $port = $json['ports'][0];

    // Default should be v3 format with sizes object
    expect($port)->toHaveKey('sizes');
    expect($port['sizes'])->toHaveKeys(['min', 'max']);
});

it('returns nested children in v2 format', function (): void {
    $this->vehicleData->update([
        'data' => [
            'Loadout' => [
                [
                    'HardpointName' => 'parent_hardpoint',
                    'MinSize' => 2,
                    'MaxSize' => 2,
                    'Type' => 'Turret.',
                    'ItemTypes' => [],
                    'Loadout' => [
                        [
                            'HardpointName' => 'child_hardpoint',
                            'MinSize' => 1,
                            'MaxSize' => 1,
                            'Type' => 'WeaponGun.Gun',
                            'ItemTypes' => [],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $response = $this->getJson("/api/v2/vehicles/{$this->vehicle->uuid}");

    $response->assertSuccessful();
    $json = $response->json('data');

    $hardpoint = $json['hardpoints'][0];

    // V2 uses 'children' key
    expect($hardpoint)->toHaveKey('children');
    expect($hardpoint['children'])->toHaveCount(1);
    expect($hardpoint['children'][0])->toHaveKey('name', 'child_hardpoint');
});

it('returns nested ports in v3 format', function (): void {
    $this->vehicleData->update([
        'data' => [
            'Loadout' => [
                [
                    'HardpointName' => 'parent_port',
                    'MinSize' => 2,
                    'MaxSize' => 2,
                    'Type' => 'Turret.',
                    'ItemTypes' => [],
                    'Loadout' => [
                        [
                            'HardpointName' => 'child_port',
                            'MinSize' => 1,
                            'MaxSize' => 1,
                            'Type' => 'WeaponGun.Gun',
                            'ItemTypes' => [],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $response = $this->getJson("/api/v3/vehicles/{$this->vehicle->uuid}");

    $response->assertSuccessful();
    $json = $response->json('data');

    $port = $json['ports'][0];

    // V3 uses 'ports' key for nested items
    expect($port)->toHaveKey('ports');
    expect($port['ports'])->toHaveCount(1);
    expect($port['ports'][0])->toHaveKey('name', 'child_port');
});

it('toggles version query param in api link on vehicle show', function (bool $withVersion): void {
    $url = "/api/vehicles/{$this->vehicle->uuid}".($withVersion ? '?version=4.4.0-TEST' : '');

    $link = $this->getJson($url)->assertSuccessful()->json('data.link');

    if ($withVersion) {
        expect($link)->toContain('version=4.4.0-TEST');
    } else {
        expect($link)->not->toContain('version=');
    }
})->with(['with version' => true, 'without version' => false]);

it('toggles version query param in web url on vehicle show', function (bool $withVersion): void {
    $url = "/api/vehicles/{$this->vehicle->uuid}".($withVersion ? '?version=4.4.0-TEST' : '');

    $webUrl = $this->getJson($url)->assertSuccessful()->json('data.web_url');

    if ($withVersion) {
        expect($webUrl)->toContain('version=4.4.0-TEST');
    } else {
        expect($webUrl)->not->toContain('version=');
    }
})->with(['with version' => true, 'without version' => false]);

describe('ore_capacity', function (): void {
    it('returns ore_capacity when present in ship data', function (): void {
        $this->vehicleData->update([
            'data' => array_merge($this->vehicleData->data, [
                'OreCapacity' => 96,
            ]),
        ]);

        $response = $this->getJson("/api/vehicles/{$this->vehicle->uuid}");

        $response->assertSuccessful();
        expect($response->json('data.ore_capacity'))->toBe(96);
    });

    it('returns null for ore_capacity when not present in ship data', function (): void {
        $response = $this->getJson("/api/vehicles/{$this->vehicle->uuid}");

        $response->assertSuccessful();
        expect($response->json('data.ore_capacity'))->toBeNull();
    });
});
