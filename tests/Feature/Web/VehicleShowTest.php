<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Dom\HTMLDocument;
use Dom\XPath;

it('renders nested hardpoints on the vehicle page', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.2.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Aegis Dynamics',
        'code' => 'AEGS',
    ]);

    $vehicle = Vehicle::factory()->create([
        'uuid' => '7e3b7dd7-7a1b-4dcf-8d7b-7b7bcb3f8b76',
    ]);

    VehicleData::factory()
        ->for($vehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'shipmatrix_id' => null,
            'name' => 'Test Vehicle',
            'display_name' => 'Test Vehicle',
            'class_name' => 'TEST_VEHICLE',
            'career' => 'Exploration',
            'role' => 'Scout',
            'is_vehicle' => true,
            'is_gravlev' => false,
            'is_spaceship' => true,
            'size' => 3,
            'data' => [
                'Length' => 20,
                'Width' => 10,
                'Height' => 5,
                'Mass' => 10000,
                'MassLoadout' => 12000,
                'MassTotal' => 22000,
                'CrossSection' => [
                    'X' => 8,
                    'Y' => 6,
                    'Z' => 4,
                ],
                'Insurance' => [
                    'StandardClaimTime' => 12.5,
                    'ExpeditedClaimTime' => 4.5,
                    'ExpeditedCost' => 1250,
                ],
                'Loadout' => [
                    [
                        'HardpointName' => 'S1',
                        'Type' => 'Weapon.Turret',
                        'MinSize' => 1,
                        'MaxSize' => 1,
                        'Loadout' => [
                            [
                                'HardpointName' => 'S1-1',
                                'Type' => 'Weapon.Gun',
                                'MinSize' => 1,
                                'MaxSize' => 1,
                                'Loadout' => [],
                            ],
                        ],
                    ],
                ],
                'MannedTurrets' => [
                    [
                        'Size' => 2,
                        'Turret' => true,
                        'Fixed' => true,
                        'WeaponSizes' => [2, 2],
                    ],
                ],
                'RemoteTurrets' => [
                    [
                        'Size' => 3,
                        'Gimballed' => true,
                        'WeaponSizes' => [1, 1],
                    ],
                ],
                'CargoGrids' => [
                    [
                        'SCU' => 8,
                        'X' => 2,
                        'Y' => 4,
                        'Z' => 1,
                        'IsOpenContainer' => true,
                    ],
                ],
            ],
        ]);

    $response = $this->get(route('web.vehicles.show', $vehicle->uuid));

    $response->assertStatus(200)
        ->assertSeeText('Combat & Systems')
        ->assertSeeText('Turrets')
        ->assertSeeText('Manned')
        ->assertSeeText('Remote')
        ->assertSeeText('Test Vehicle')
        ->assertSeeText('Gimballed')
        ->assertSeeText('Fixed')
        ->assertSeeText('Dimensions & Mass')
        ->assertSeeText('Cargo Grids')
        ->assertSeeText('Insurance')
        ->assertSeeText('Expedite')
        ->assertSeeText('Open')
        ->assertSeeText('S1')
        ->assertSeeText('S1 1');
});

it('renders mounting positions on the vehicle detail page', function (string $snapshot): void {
    $version = GameVersion::factory()->create(['is_default' => true]);
    $vehicle = Vehicle::factory()->create();
    $dump = json_decode(file_get_contents(base_path('tests/Fixtures/mounting-positions/populated.json')), true, flags: JSON_THROW_ON_ERROR);
    $dump['Loadout'][0]['Loadout'][0]['PortDisplayName'] = 'Nose cannon socket';
    $dump['Loadout'][0]['PortDisplayName'] = 'Nose gimbal socket';
    $dump['Loadout'][] = [
        'PortId' => 'test.vacant', 'PortDisplayName' => 'Vacant tool socket', 'MinSize' => 2, 'MaxSize' => 4,
    ];
    $dump['MountingPositions'][] = [
        'PortId' => 'test.vacant', 'Occupied' => false, 'MinSize' => 2, 'MaxSize' => 4, 'Payload' => [],
        'OperatingStations' => [['Kind' => 'Pilot', 'SeatPortId' => 'test.aim', 'Name' => 'Stale vacant station']],
    ];
    $dump['MountingPositions'][0]['IsPilotSlaveable'] = true;
    $dump['MountingPositions'][1]['ControlRoutes'] = [
        ['Kind' => 'Controller', 'Operator' => 'Pilot', 'Control' => 'Aim', 'SeatPortId' => 'test.aim'],
        ['Kind' => 'Controller', 'Operator' => 'Crew', 'Control' => 'Payload', 'SeatPortId' => 'test.seat'],
        ['Kind' => 'Controller', 'Operator' => 'Crew', 'SeatPortId' => 'test.reserve'],
    ];
    $dump['MountingPositions'][1]['OperatingStations'] = [
        ['Kind' => 'InTurret', 'SeatPortId' => 'loadout.36', 'Name' => 'Seat inside turret'],
        ['Kind' => 'Pilot', 'SeatPortId' => 'test.aim', 'Name' => 'Pilot seat'],
        ['Kind' => 'Crew', 'SeatPortId' => 'test.seat', 'Name' => 'Utility operator seat'],
        ['Kind' => 'Crew', 'SeatPortId' => 'test.reserve', 'Name' => 'Reserve seat'],
    ];
    $dump['MountingPositions'][0]['ControlRoutes'] = [
        ['Kind' => 'Controller', 'Operator' => 'Pilot', 'SeatPortId' => 'test.aim'],
    ];
    array_push($dump['Loadout'],
        ['PortId' => 'test.aim', 'PortDisplayName' => 'Pilot seat'],
        ['PortId' => 'test.seat', 'PortDisplayName' => 'Legacy utility port label'],
        ['PortId' => 'test.reserve', 'PortDisplayName' => 'Reserve seat'],
    );
    $dump['Loadout'][] = ['PortId' => 'test.pdc', 'PortDisplayName' => 'port_NameTurret05', 'HardpointName' => 'hardpoint_left_lower_turret'];
    $dump['MountingPositions'][] = ['PortId' => 'test.pdc', 'MountType' => 'Turret', 'TurretRole' => 'Pdc', 'Occupied' => true, 'Payload' => [], 'OperatingStations' => []];

    if ($snapshot === 'empty') {
        $dump['MountingPositions'] = [];
    } elseif ($snapshot === 'older') {
        unset($dump['MountingPositions']);
    } elseif ($snapshot === 'no turrets') {
        $dump['MountingPositions'] = array_values(array_filter($dump['MountingPositions'], fn (array $position): bool => ($position['MountType'] ?? null) !== 'Turret'));
    }

    VehicleData::factory()->for($vehicle)->for($version, 'gameVersion')->create(['data' => $dump]);

    $response = $this->get(route('web.vehicles.show', $vehicle->uuid))->assertOk();

    $document = HTMLDocument::createFromString($response->getContent(), LIBXML_NOERROR | Dom\HTML_NO_DEFAULT_NS);
    $xpath = new XPath($document);
    $card = '//section[@aria-labelledby="mounting-positions-heading"]';
    $legacyHeading = '//h2[normalize-space(.)="Turrets"]';

    if ($snapshot === 'older') {
        expect($xpath->query($card))->toHaveCount(0)
            ->and($xpath->query($legacyHeading))->toHaveCount(1);

        return;
    }

    expect($xpath->query($card))->toHaveCount(1)
        ->and($xpath->query($legacyHeading))->toHaveCount(0);

    if ($snapshot === 'empty') {
        expect($xpath->evaluate("normalize-space({$card}//p)"))->toBe('No mounting positions');

        return;
    }

    $firstTab = $snapshot === 'no turrets' ? 'Gimbals' : 'Turrets';
    expect($xpath->evaluate("string(({$card}//input[@role='tab'])[1]/@aria-label)"))->toBe($firstTab)
        ->and($xpath->query("{$card}//input[@checked]"))->toHaveCount(1)
        ->and($xpath->query("{$card}//input[@aria-label='{$firstTab}'][@checked]"))->toHaveCount(1);

    if ($snapshot === 'no turrets') {
        expect($xpath->query("{$card}//input[@aria-label='Turrets']"))->toHaveCount(0);

        return;
    }

    $gimbal = "{$card}//*[@id='mounting-panel-Gimbal']//tbody/tr";
    $vacant = "{$card}//*[@id='mounting-panel-Vacant']//tbody/tr";
    $turretRows = "{$card}//*[@id='mounting-panel-Turret']//tbody/tr[th[@scope='row']]";
    $manned = "({$turretRows})[1]";
    $pdc = "({$turretRows})[2]";

    expect($xpath->query($gimbal))->toHaveCount(1)
        ->and($xpath->evaluate("normalize-space({$gimbal}/th/div[1])"))->toBe('Nose gimbal socket')
        ->and($xpath->evaluate("normalize-space({$gimbal}/th/div[2])"))->toBe('Mount_Gimbal_S5')
        ->and($xpath->evaluate("normalize-space({$gimbal}/th//li)"))->toContain('Nose cannon socket:', 'Deadbolt V Cannon')
        ->and($xpath->query("{$gimbal}/th//a"))->toHaveCount(0)
        ->and($xpath->query("{$vacant}/th//a"))->toHaveCount(0)
        ->and($xpath->evaluate("normalize-space({$gimbal}/td[3])"))->toBe('Unknown')
        ->and($xpath->evaluate("normalize-space({$gimbal}/td[4])"))->toBe('DPS: 450.0 Sustained DPS: 0.0')
        ->and($xpath->evaluate("normalize-space({$gimbal}/th//li)"))->toContain('DPS: 450.0', 'Sustained DPS: 0.0')
        ->and($xpath->evaluate("normalize-space({$card}//*[@id='mounting-panel-Rack']//tbody/tr/td[4])"))->toBe("Missile damage: 1\u{00a0}600.0")
        ->and($xpath->evaluate("normalize-space({$vacant}/th)"))->toBe('Vacant tool socket')
        ->and($xpath->evaluate("normalize-space({$vacant}/td[1])"))->toBe('-')
        ->and($xpath->evaluate("normalize-space({$vacant}/td[2])"))->toBe('S2-S4')
        ->and($xpath->evaluate("normalize-space({$vacant}/td[3])"))->toBe('-')
        ->and($xpath->evaluate("normalize-space({$manned}/td[3]/div[1])"))->toBe('Seat inside turret')
        ->and($xpath->evaluate("normalize-space({$manned}/td[3]/div[2])"))->toBe('Pilot seat')
        ->and($xpath->evaluate("normalize-space({$manned}/td[3]/div[3])"))->toBe('Utility operator seat')
        ->and($xpath->evaluate("normalize-space({$manned}/td[3]/div[4])"))->toBe('Reserve seat')
        ->and($xpath->evaluate("normalize-space({$manned}/td[4])"))->toContain('DPS: 750.0', 'Sustained DPS: 672.0', 'Alpha: 90.0')
        ->and($xpath->evaluate("normalize-space({$pdc}/th)"))->toBe('Left Lower Turret')
        ->and($xpath->evaluate("normalize-space({$pdc}/td[3])"))->toBe('Unknown');

    $response->assertSeeText('Operated from')
        ->assertDontSeeText('Via controller')
        ->assertDontSeeText('Controlled by')
        ->assertDontSeeText('Stale vacant station')
        ->assertDontSeeText('Legacy utility port label');

    $response->assertDontSee('test.seat')->assertDontSee('test.vacant');
})->with(['populated', 'empty', 'older', 'no turrets']);
