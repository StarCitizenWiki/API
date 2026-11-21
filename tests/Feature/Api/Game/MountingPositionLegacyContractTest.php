<?php

declare(strict_types=1);

use App\Jobs\Game\ImportVehicleData;
use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.4.0-TEST',
        'channel' => 'test',
        'is_default' => true,
    ]);
});

/**
 * Vanguard projection plus the shared-contract keys the dump emits:
 * PortDisplayName / PartDisplayName / PartName / InstalledSize on loadout
 * entries (nested adapters included) and LegacyBucket / LegacyOrdinal /
 * LegacySize on mounting positions. All legacy summary facts stay internal:
 * the API drops them because mounting_positions serves current state only;
 * turrets.* serves the exact legacy values unchanged.
 * Older Galaxy Cargo routes must not become operating-station claims.
 */
function contractDump(): array
{
    $dump = json_decode(
        file_get_contents(base_path('tests/Fixtures/mounting-positions/populated.json')),
        true,
        flags: JSON_THROW_ON_ERROR
    );

    $dump['IsSpaceship'] = true;
    $dump['Crew'] = 1;

    // ImportVehicleData memoizes vehicles by UUID in a static cache that outlives
    // the per-test transaction rollback, so each test imports a fresh identity.
    $dump['UUID'] = fake()->uuid();

    // Vacant legacy member (Idris-style tail turret): legacy bucket membership
    // without an installed mount type or role, size reproduced by max_size.
    $dump['Loadout'][] = [
        'PortId' => 'loadout.201',
        'HardpointName' => 'hardpoint_rear_turret_tail',
        'PortDisplayName' => 'port_NameTailTurret',
        'PartName' => 'hardpoint_rear_turret_tail',
        'CompatibleTypes' => [['Type' => 'TurretBase', 'SubTypes' => ['MannedTurret']]],
        'MinSize' => 5,
        'MaxSize' => 5,
    ];
    $dump['MountingPositions'][] = [
        'PortId' => 'loadout.201',
        'HardpointName' => 'hardpoint_rear_turret_tail',
        'Occupied' => false,
        'MinSize' => 5,
        'MaxSize' => 5,
        'Payload' => [],
        'LegacyBucket' => 'MannedTurrets',
        'LegacyOrdinal' => 1,
    ];
    $dump['MannedTurrets'][] = [
        'PartName' => 'hardpoint_rear_turret_tail',
        'HardpointName' => 'hardpoint_rear_turret_tail',
        'DisplayName' => 'port_NameTailTurret',
        'Size' => 5,
        'WeaponSizes' => [5],
        'PayloadSizes' => [5],
        'PayloadTypes' => [],
        'Gimballed' => false,
        'Turret' => false,
        'Fixed' => true,
    ];

    // An untranslated part label must not fall back to the port label.
    $dump['Loadout'][] = [
        'PortId' => 'loadout.202',
        'HardpointName' => 'hardpoint_unresolved_turret',
        'PortDisplayName' => 'Turret Port',
        'PartDisplayName' => null,
        'PartName' => 'hardpoint_unresolved_turret',
        'MinSize' => 0,
        'MaxSize' => 5,
    ];
    $dump['MountingPositions'][] = [
        'PortId' => 'loadout.202',
        'HardpointName' => 'hardpoint_unresolved_turret',
        'Occupied' => false,
        'MinSize' => 0,
        'MaxSize' => 5,
        'Payload' => [],
        'LegacyBucket' => 'MannedTurrets',
        'LegacyOrdinal' => 2,
        'LegacySize' => 0,
    ];
    $dump['MannedTurrets'][] = [
        'PartName' => 'hardpoint_unresolved_turret',
        'HardpointName' => 'hardpoint_unresolved_turret',
        'Size' => 0,
        'Gimballed' => false,
        'Turret' => false,
        'Fixed' => true,
    ];

    $dump['Loadout'][] = [
        'PortId' => 'loadout.203',
        'HardpointName' => 'hardpoint_size_zero',
        'PortDisplayName' => null,
        'Type' => 'Decal.UNDEFINED',
        'InstalledSize' => 0,
    ];

    // Older technical routes are deliberately retained to guard against fallback claims.
    $dump['MountingPositions'] = [
        ...$dump['MountingPositions'],
        ...array_map(static fn (array $position): array => Arr::except($position, ['CombatStats', 'LegacyBucket', 'LegacyOrdinal']), json_decode(
            file_get_contents(base_path('tests/Fixtures/mounting-positions/galaxy-cargo-control-routes.json')),
            true,
            flags: JSON_THROW_ON_ERROR
        )),
    ];

    $turretIndex = array_find_key($dump['Loadout'], fn (array $port): bool => $port['PortId'] === 'loadout.36');
    $dump['Loadout'][$turretIndex]['Loadout'][1]['PortDisplayName'] = '';
    $dump['MannedTurrets'][0]['Mounts'][1]['DisplayName'] = '';

    // Test-only legacy summary input must never leak into the public output.
    $dump['MountingPositions'][1]['LegacyTurret'] = ['Bucket' => 'MannedTurrets', 'Ordinal' => 0, 'Summary' => ['Size' => 6]];

    return $dump;
}

function importContractDump(array $dump): array
{
    $dump['Manufacturer']['UUID'] = test()->manufacturer->uuid;

    Storage::fake('scunpacked');
    Storage::disk('scunpacked')->put('ships/contract.json', json_encode($dump, JSON_THROW_ON_ERROR));
    (new ImportVehicleData(test()->gameVersion->id, 'ships/contract.json'))->handle();

    return test()->getJson("/api/v3/vehicles/{$dump['UUID']}")->assertSuccessful()->json('data');
}

/**
 * @return array<string, list<array<string, mixed>>>
 */
function portsById(array $ports, array &$map = []): array
{
    foreach ($ports as $port) {
        if (isset($port['port_id'])) {
            $map[$port['port_id']][] = $port;
        }
        portsById($port['ports'] ?? [], $map);
    }

    return $map;
}

it('maps snapshot port labels and installed sizes onto explicit v3 detail ports', function (): void {
    $dump = contractDump();
    $data = importContractDump($dump);
    $map = portsById($data['ports']);

    expect($map['loadout.36'])->toHaveCount(1);
    $turret = $map['loadout.36'][0];
    expect($turret['port_display_name'])->toBe('port_NameTurret02')
        ->and($turret['part_name'])->toBe('hardpoint_turret')
        ->and($turret['installed_size'])->toBe(6)
        ->and($turret)->not->toHaveKey('part_display_name');

    // Nested adapter: declared size 3 exceeds the port capacity 2 (legacy
    // mounts[].size case) and the localized label matches the legacy mount row.
    expect($map['loadout.36.loadout.0'])->toHaveCount(1);
    $adapter = $map['loadout.36.loadout.0'][0];
    expect($adapter['port_display_name'])->toBe('Weapon - Top Left')
        ->and($adapter['installed_size'])->toBe(3)
        ->and($adapter['sizes'])->toBe(['min' => 2, 'max' => 2]);

    // Snapshot-only resolution: the adapter is not in the item database, yet
    // its size and label are carried without a database lookup.
    expect($adapter['equipped_item'])->toBeNull();
    expect($map['loadout.36.loadout.1'][0]['port_display_name'])->toBe('');

    expect($map['loadout.2.loadout.0'][0]['installed_size'])->toBe(5)
        ->and($map['loadout.66'][0]['installed_size'])->toBe(4);

    // Entries without the snapshot keys stay without the mapped fields.
    expect($map['loadout.36.loadout.0.loadout.0.loadout.0'][0])->not->toHaveKeys(['port_display_name', 'part_name', 'installed_size']);

    expect($map['loadout.202'][0])->toHaveKey('part_display_name')
        ->and($map['loadout.202'][0]['part_display_name'])->toBeNull();
    expect($map['loadout.203'][0])->toHaveKey('port_display_name')
        ->and($map['loadout.203'][0]['port_display_name'])->toBeNull()
        ->and($map['loadout.203'][0]['installed_size'])->toBe(0);

    $this->getJson("/api/v2/vehicles/{$dump['UUID']}")->assertSuccessful()
        ->assertJsonMissingPath('data.hardpoints.0.port_display_name')
        ->assertJsonMissingPath('data.hardpoints.0.installed_size');

    $canonical = $this->getJson("/api/vehicles/{$dump['UUID']}")->assertSuccessful();
    expect($canonical->json('data.ports'))->toBe($data['ports'])
        ->and($canonical->json('data.mounting_positions'))->toBe($data['mounting_positions']);

    $indexRow = collect($this->getJson('/api/v3/vehicles')->assertSuccessful()->json('data'))
        ->firstWhere('uuid', $dump['UUID']);
    foreach ($indexRow['ports'] ?? [] as $port) {
        expect($port)->not->toHaveKeys(['port_display_name', 'part_display_name', 'part_name', 'installed_size']);
    }
});

it('keeps legacy dumper facts out of mounting positions while carrying current classification', function (): void {
    $data = importContractDump(contractDump());
    $positions = collect($data['mounting_positions'])->keyBy('port_id');

    // Legacy members of every shape - occupied, vacant, size-override - and
    // non-members alike expose no legacy field.
    foreach ($data['mounting_positions'] as $position) {
        expect($position)->not->toHaveKeys(['legacy_bucket', 'legacy_ordinal', 'legacy_size', 'legacy_turret']);
    }

    $turret = $positions['loadout.36'];
    expect($turret['mount_type'])->toBe('Turret')
        ->and($turret['turret_role'])->toBe('Manned')
        ->and($turret['installed_size'])->toBe(6)
        ->and($turret['max_size'])->toBe(6);

    // Vacant legacy member: current classification stays absent.
    $vacant = $positions['loadout.201'];
    expect($vacant['occupied'])->toBeFalse()
        ->and($vacant)->not->toHaveKey('mount_type')
        ->and($vacant)->not->toHaveKey('turret_role');

    // Snapshot order is preserved, including duplicate hardpoint names.
    expect(array_column($data['mounting_positions'], 'port_id'))
        ->toBe(array_column(contractDump()['MountingPositions'], 'PortId'));
});

it('serves exact legacy turret facts from turrets.manned only', function (): void {
    $dump = contractDump();
    $data = importContractDump($dump);
    $positions = collect($data['mounting_positions'])->keyBy('port_id');

    // turrets.* is the only place with the legacy facts: order, labels,
    // sizes and per-mount rows match the dump input exactly.
    $legacy = $data['turrets']['manned'];
    expect($legacy)->toHaveCount(count($dump['MannedTurrets']));

    foreach (array_values($dump['MannedTurrets']) as $index => $expected) {
        $row = $legacy[$index];
        expect($row['hardpoint_name'])->toBe($expected['HardpointName'])
            ->and($row['part_name'])->toBe($expected['PartName'])
            ->and($row['display_name'])->toBe($expected['DisplayName'] ?? ($expected['Name'] ?? null))
            ->and($row['size'])->toBe($expected['Size']);

        $expectedMounts = $expected['Mounts'] ?? [];
        expect($row['mounts'])->toHaveCount(count($expectedMounts));
        foreach ($expectedMounts as $mountIndex => $mount) {
            expect($row['mounts'][$mountIndex]['hardpoint_name'])->toBe($mount['HardpointName'])
                ->and($row['mounts'][$mountIndex]['display_name'])->toBe($mount['DisplayName'])
                ->and($row['mounts'][$mountIndex]['mount_type'])->toBe($mount['MountType'])
                ->and($row['mounts'][$mountIndex]['size'])->toBe($mount['Size']);
        }
    }

    // The same hardpoints on mounting_positions carry current facts only:
    // the occupied Vanguard turret classifies and sizes the installed item,
    // while the vacant tail turret stays in turrets.manned without any
    // classification on its position.
    expect($positions['loadout.36'])->not->toHaveKeys(['legacy_bucket', 'legacy_size'])
        ->and($positions['loadout.201'])->not->toHaveKeys(['legacy_bucket', 'legacy_size']);
});

it('publishes verified operating stations without exposing technical routes', function (): void {
    $dump = contractDump();
    $dump['MountingPositions'][0]['OperatingStations'] = [
        ['Kind' => 'Pilot', 'SeatPortId' => 'loadout.49', 'Name' => 'Pilot seat', 'ControllerPortId' => 'internal'],
        ['Kind' => 'Crew', 'SeatPortId' => 'loadout.64', 'Name' => 'Utility operator seat'],
    ];
    $dump['MountingPositions'][1]['OperatingStations'] = [];

    $data = importContractDump($dump);

    expect($data['mounting_positions'][0]['operating_stations'])->toBe([
        ['kind' => 'Pilot', 'seat_port_id' => 'loadout.49', 'name' => 'Pilot seat'],
        ['kind' => 'Crew', 'seat_port_id' => 'loadout.64', 'name' => 'Utility operator seat'],
    ])->and($data['mounting_positions'][1]['operating_stations'])->toBe([]);
    foreach ($data['mounting_positions'] as $position) {
        expect($position)->not->toHaveKey('control_routes');
    }
    expect(collect($data['mounting_positions'])->keyBy('port_id')['loadout.74.loadout.6'])
        ->not->toHaveKey('operating_stations');
});

it('does not turn older module aim or payload routes into operating stations', function (): void {
    $data = importContractDump(contractDump());
    $positions = collect($data['mounting_positions'])->keyBy('port_id');

    foreach (['loadout.74.loadout.6', 'loadout.74.loadout.11', 'loadout.6'] as $portId) {
        expect($positions[$portId])->not->toHaveKeys(['control_routes', 'operating_stations']);
    }
    expect($positions['loadout.36']['operating_stations'])->toBe([
        ['kind' => 'InTurret', 'seat_port_id' => 'loadout.36', 'name' => 'Seat inside turret'],
    ]);
});
