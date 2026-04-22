<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('finds previous patch version matching full base semver', function (): void {
    $current = GameVersion::factory()->create([
        'code' => '4.7.1-PTU.12345',
        'released_at' => '2026-04-22 00:00:00',
    ]);
    GameVersion::factory()->create([
        'code' => '4.7.1-LIVE.99999',
        'released_at' => '2026-04-20 00:00:00',
    ]);
    GameVersion::factory()->create([
        'code' => '4.7.1-LIVE.88888',
        'released_at' => '2026-04-18 00:00:00',
    ]);

    $result = $current->findPreviousPatchVersion();

    expect($result)->not->toBeNull()
        ->and($result->code)->toBe('4.7.1-LIVE.99999');
});

it('returns null from findPreviousPatchVersion when no matching base semver exists', function (): void {
    $current = GameVersion::factory()->create([
        'code' => '4.7.1-LIVE.12345',
        'released_at' => '2026-04-22 00:00:00',
    ]);

    $result = $current->findPreviousPatchVersion();

    expect($result)->toBeNull();
});

it('returns null from findPreviousPatchVersion for code without semver', function (): void {
    $current = GameVersion::factory()->create(['code' => 'test']);

    $result = $current->findPreviousPatchVersion();

    expect($result)->toBeNull();
});

it('findPreviousVersion prefers patch match over minor fallback', function (): void {
    $current = GameVersion::factory()->create([
        'code' => '4.7.1-PTU.12345',
        'released_at' => '2026-04-22 00:00:00',
    ]);
    GameVersion::factory()->create([
        'code' => '4.7.1-LIVE.99999',
        'released_at' => '2026-04-20 00:00:00',
    ]);
    GameVersion::factory()->create([
        'code' => '4.6.0-LIVE.55555',
        'released_at' => '2026-03-01 00:00:00',
    ]);

    $result = $current->findPreviousVersion();

    expect($result)->not->toBeNull()
        ->and($result->code)->toBe('4.7.1-LIVE.99999');
});

it('findPreviousVersion falls back to previous minor when no patch match', function (): void {
    $current = GameVersion::factory()->create([
        'code' => '4.7.1-LIVE.12345',
        'released_at' => '2026-04-22 00:00:00',
    ]);
    GameVersion::factory()->create([
        'code' => '4.6.0-LIVE.55555',
        'released_at' => '2026-03-01 00:00:00',
    ]);

    $result = $current->findPreviousVersion();

    expect($result)->not->toBeNull()
        ->and($result->code)->toBe('4.6.0-LIVE.55555');
});

it('findPreviousVersion returns null when no match at all', function (): void {
    $current = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE.12345',
        'released_at' => '2026-04-22 00:00:00',
    ]);

    $result = $current->findPreviousVersion();

    expect($result)->toBeNull();
});
