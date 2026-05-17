<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use App\Support\UEXcorp\TerminalLocationMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

it('maps terminal IDs to starmap location UUIDs via exact name match', function (): void {
    Log::spy();

    $location = StarmapLocation::factory()->create(['uuid' => '11111111-1111-1111-1111-111111111111']);
    StarmapLocationData::factory()->create([
        'starmap_location_id' => $location->id,
        'game_version_id' => GameVersion::factory()->create()->id,
        'name' => 'ARC-L1 Wide Forest Station',
    ]);

    Http::fake([
        'api.uexcorp.uk/*' => Http::response([
            'data' => [
                [
                    'id' => 1,
                    'displayname' => 'ARC-L1 Wide Forest Station',
                    'name' => 'Admin - ARC-L1',
                    'star_system_name' => 'Stanton',
                ],
            ],
        ]),
    ]);

    $mapper = new TerminalLocationMapper;

    expect($mapper->mapping->get(1))->toBe('11111111-1111-1111-1111-111111111111');
});

it('falls back to case-insensitive matching', function (): void {
    Log::spy();

    $location = StarmapLocation::factory()->create(['uuid' => '22222222-2222-2222-2222-222222222222']);
    StarmapLocationData::factory()->create([
        'starmap_location_id' => $location->id,
        'game_version_id' => GameVersion::factory()->create()->id,
        'name' => 'PRIVATE PROPERTY',
    ]);

    Http::fake([
        'api.uexcorp.uk/*' => Http::response([
            'data' => [
                [
                    'id' => 10,
                    'displayname' => 'Private Property',
                    'name' => 'Admin - Private Property',
                    'star_system_name' => 'Stanton',
                ],
            ],
        ]),
    ]);

    $mapper = new TerminalLocationMapper;

    expect($mapper->mapping->get(10))->toBe('22222222-2222-2222-2222-222222222222');
});

it('applies config overrides before name matching', function (): void {
    Log::spy();

    config(['uexcorp.terminal_location_overrides' => [
        'Area 18' => 'Area18',
    ]]);

    $location = StarmapLocation::factory()->create(['uuid' => '33333333-3333-3333-3333-333333333333']);
    StarmapLocationData::factory()->create([
        'starmap_location_id' => $location->id,
        'game_version_id' => GameVersion::factory()->create()->id,
        'name' => 'Area18',
    ]);

    Http::fake([
        'api.uexcorp.uk/*' => Http::response([
            'data' => [
                [
                    'id' => 20,
                    'displayname' => 'Area 18',
                    'name' => 'Admin - Area 18',
                    'star_system_name' => 'Stanton',
                ],
            ],
        ]),
    ]);

    $mapper = new TerminalLocationMapper;

    expect($mapper->mapping->get(20))->toBe('33333333-3333-3333-3333-333333333333');
});

it('returns null for unmatched terminals', function (): void {
    Log::spy();

    Http::fake([
        'api.uexcorp.uk/*' => Http::response([
            'data' => [
                [
                    'id' => 99,
                    'displayname' => 'Port Olisar',
                    'name' => 'Admin - Port Olisar',
                    'star_system_name' => 'Stanton',
                ],
            ],
        ]),
    ]);

    $mapper = new TerminalLocationMapper;

    expect($mapper->mapping->get(99))->toBeNull();
});

it('caches the mapping after first build', function (): void {
    Log::spy();

    $location = StarmapLocation::factory()->create(['uuid' => '44444444-4444-4444-4444-444444444444']);
    StarmapLocationData::factory()->create([
        'starmap_location_id' => $location->id,
        'game_version_id' => GameVersion::factory()->create()->id,
        'name' => 'Cached Station',
    ]);

    Http::fake([
        'api.uexcorp.uk/*' => Http::response([
            'data' => [
                [
                    'id' => 50,
                    'displayname' => 'Cached Station',
                    'name' => 'Admin - Cached',
                    'star_system_name' => 'Stanton',
                ],
            ],
        ]),
    ]);

    $mapper = new TerminalLocationMapper;

    $mapper->mapping;
    $mapper->mapping;

    Http::assertSentCount(1);
});

it('handles empty terminal API response gracefully', function (): void {
    Log::spy();

    Http::fake([
        'api.uexcorp.uk/*' => Http::response([
            'data' => [],
        ]),
    ]);

    $mapper = new TerminalLocationMapper;

    expect($mapper->mapping)->toBeEmpty();
});

it('disambiguates same-named locations across different star systems', function (): void {
    Log::spy();

    // "Nyx Gateway" exists in both Stanton System and Pyro System
    $stantonVersion = GameVersion::factory()->create();
    $pyroLocation = StarmapLocation::factory()->create(['uuid' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa']);
    $stantonLocation = StarmapLocation::factory()->create(['uuid' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb']);

    StarmapLocationData::factory()->create([
        'starmap_location_id' => $pyroLocation->id,
        'game_version_id' => $stantonVersion->id,
        'name' => 'Nyx Gateway',
        'system' => 'Pyro System',
    ]);
    StarmapLocationData::factory()->create([
        'starmap_location_id' => $stantonLocation->id,
        'game_version_id' => $stantonVersion->id,
        'name' => 'Nyx Gateway',
        'system' => 'Stanton System',
    ]);

    Http::fake([
        'api.uexcorp.uk/*' => Http::response([
            'data' => [
                [
                    'id' => 100,
                    'displayname' => 'Nyx Gateway',
                    'name' => 'Juice Bar - Nyx Gateway (Pyro)',
                    'star_system_name' => 'Pyro',
                ],
                [
                    'id' => 200,
                    'displayname' => 'Nyx Gateway',
                    'name' => 'Juice Bar - Nyx Gateway (Stanton)',
                    'star_system_name' => 'Stanton',
                ],
            ],
        ]),
    ]);

    $mapper = new TerminalLocationMapper($stantonVersion->id);

    // The Pyro terminal must resolve to the Pyro location, not Stanton
    expect($mapper->mapping->get(100))->toBe('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa')
        ->and($mapper->mapping->get(200))->toBe('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb');
});

it('disambiguates all gateway stations across systems', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create();

    $pyroGwInStanton = StarmapLocation::factory()->create(['uuid' => 'a0000000-0000-0000-0000-000000000001']);
    $nyxGwInStanton = StarmapLocation::factory()->create(['uuid' => 'a0000000-0000-0000-0000-000000000002']);
    $stantonGwInPyro = StarmapLocation::factory()->create(['uuid' => 'a0000000-0000-0000-0000-000000000003']);
    $nyxGwInPyro = StarmapLocation::factory()->create(['uuid' => 'a0000000-0000-0000-0000-000000000004']);
    $stantonGwInNyx = StarmapLocation::factory()->create(['uuid' => 'a0000000-0000-0000-0000-000000000005']);
    $pyroGwInNyx = StarmapLocation::factory()->create(['uuid' => 'a0000000-0000-0000-0000-000000000006']);

    StarmapLocationData::factory()->create(['starmap_location_id' => $pyroGwInStanton->id, 'game_version_id' => $version->id, 'name' => 'Pyro Gateway', 'system' => 'Stanton System']);
    StarmapLocationData::factory()->create(['starmap_location_id' => $nyxGwInStanton->id, 'game_version_id' => $version->id, 'name' => 'Nyx Gateway', 'system' => 'Stanton System']);
    StarmapLocationData::factory()->create(['starmap_location_id' => $stantonGwInPyro->id, 'game_version_id' => $version->id, 'name' => 'Stanton Gateway', 'system' => 'Pyro System']);
    StarmapLocationData::factory()->create(['starmap_location_id' => $nyxGwInPyro->id, 'game_version_id' => $version->id, 'name' => 'Nyx Gateway', 'system' => 'Pyro System']);
    StarmapLocationData::factory()->create(['starmap_location_id' => $stantonGwInNyx->id, 'game_version_id' => $version->id, 'name' => 'Stanton Gateway', 'system' => 'Nyx System']);
    StarmapLocationData::factory()->create(['starmap_location_id' => $pyroGwInNyx->id, 'game_version_id' => $version->id, 'name' => 'Pyro Gateway', 'system' => 'Nyx System']);

    Http::fake([
        'api.uexcorp.uk/*' => Http::response([
            'data' => [
                ['id' => 1, 'displayname' => 'Pyro Gateway', 'name' => 'test', 'star_system_name' => 'Stanton'],
                ['id' => 2, 'displayname' => 'Nyx Gateway', 'name' => 'test', 'star_system_name' => 'Stanton'],
                ['id' => 3, 'displayname' => 'Stanton Gateway', 'name' => 'test', 'star_system_name' => 'Pyro'],
                ['id' => 4, 'displayname' => 'Nyx Gateway', 'name' => 'test', 'star_system_name' => 'Pyro'],
                ['id' => 5, 'displayname' => 'Stanton Gateway', 'name' => 'test', 'star_system_name' => 'Nyx'],
                ['id' => 6, 'displayname' => 'Pyro Gateway', 'name' => 'test', 'star_system_name' => 'Nyx'],
            ],
        ]),
    ]);

    $mapper = new TerminalLocationMapper($version->id);

    expect($mapper->mapping->get(1))->toBe('a0000000-0000-0000-0000-000000000001')
        ->and($mapper->mapping->get(2))->toBe('a0000000-0000-0000-0000-000000000002')
        ->and($mapper->mapping->get(3))->toBe('a0000000-0000-0000-0000-000000000003')
        ->and($mapper->mapping->get(4))->toBe('a0000000-0000-0000-0000-000000000004')
        ->and($mapper->mapping->get(5))->toBe('a0000000-0000-0000-0000-000000000005')
        ->and($mapper->mapping->get(6))->toBe('a0000000-0000-0000-0000-000000000006');
});

it('resolves terminal codes by terminal ID', function (): void {
    Log::spy();

    $location = StarmapLocation::factory()->create(['uuid' => '55555555-5555-5555-5555-555555555555']);
    StarmapLocationData::factory()->create([
        'starmap_location_id' => $location->id,
        'game_version_id' => GameVersion::factory()->create()->id,
        'name' => 'ARC-L1 Wide Forest Station',
    ]);

    Http::fake([
        'api.uexcorp.uk/*' => Http::response([
            'data' => [
                [
                    'id' => 1,
                    'displayname' => 'ARC-L1 Wide Forest Station',
                    'name' => 'Admin - ARC-L1',
                    'code' => 'ARCL1',
                    'star_system_name' => 'Stanton',
                ],
                [
                    'id' => 2,
                    'displayname' => 'Unmatched Location',
                    'name' => 'Admin - Unmatched',
                    'code' => 'UNMAT',
                    'star_system_name' => 'Stanton',
                ],
            ],
        ]),
    ]);

    $mapper = new TerminalLocationMapper;

    expect($mapper->terminalCodes()->get(1))->toBe('ARCL1')
        ->and($mapper->terminalCodes()->get(2))->toBe('UNMAT')
        ->and($mapper->terminalCodes()->get(999))->toBeNull();
});

it('resolves unique location name using star_system_name', function (): void {
    Log::spy();

    $location = StarmapLocation::factory()->create(['uuid' => 'cccccccc-cccc-cccc-cccc-cccccccccccc']);
    StarmapLocationData::factory()->create([
        'starmap_location_id' => $location->id,
        'game_version_id' => GameVersion::factory()->create()->id,
        'name' => 'Port Tressler',
        'system' => 'Stanton System',
    ]);

    Http::fake([
        'api.uexcorp.uk/*' => Http::response([
            'data' => [
                [
                    'id' => 300,
                    'displayname' => 'Port Tressler',
                    'name' => 'Admin - Port Tressler',
                    'star_system_name' => 'Stanton',
                ],
            ],
        ]),
    ]);

    $mapper = new TerminalLocationMapper;

    expect($mapper->mapping->get(300))->toBe('cccccccc-cccc-cccc-cccc-cccccccccccc');
});
