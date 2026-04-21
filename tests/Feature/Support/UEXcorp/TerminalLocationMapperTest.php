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

    expect($mapper->resolveUuidForTerminal(1))->toBe('11111111-1111-1111-1111-111111111111');
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

    expect($mapper->resolveUuidForTerminal(10))->toBe('22222222-2222-2222-2222-222222222222');
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

    expect($mapper->resolveUuidForTerminal(20))->toBe('33333333-3333-3333-3333-333333333333');
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

    expect($mapper->resolveUuidForTerminal(99))->toBeNull();
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

    $mapper->getMapping();
    $mapper->getMapping();

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

    expect($mapper->getMapping())->toBeEmpty();
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

    expect($mapper->getTerminalCode(1))->toBe('ARCL1')
        ->and($mapper->getTerminalCode(2))->toBe('UNMAT')
        ->and($mapper->getTerminalCode(999))->toBeNull();
});
