<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;

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

it('findPreviousVersion ignores newer patch builds before falling back to previous minor', function (): void {
    $current = GameVersion::factory()->create([
        'code' => '4.8.0-LIVE.11825000',
        'released_at' => '2026-05-14 00:00:00',
    ]);
    GameVersion::factory()->create([
        'code' => '4.8.0-LIVE.11875683',
        'released_at' => '2026-05-20 00:00:00',
    ]);
    GameVersion::factory()->create([
        'code' => '4.7.2-LIVE.11674325',
        'released_at' => '2026-05-01 00:00:00',
    ]);

    $result = $current->findPreviousVersion();

    expect($result)->not->toBeNull()
        ->and($result->code)->toBe('4.7.2-LIVE.11674325');
});

it('findPreviousPatchVersion ignores newer patch builds', function (): void {
    $current = GameVersion::factory()->create([
        'code' => '4.8.0-LIVE.11825000',
        'released_at' => '2026-05-14 00:00:00',
    ]);
    GameVersion::factory()->create([
        'code' => '4.8.0-LIVE.11875683',
        'released_at' => '2026-05-20 00:00:00',
    ]);
    GameVersion::factory()->create([
        'code' => '4.8.0-LIVE.11810000',
        'released_at' => '2026-05-10 00:00:00',
    ]);

    $result = $current->findPreviousPatchVersion();

    expect($result)->not->toBeNull()
        ->and($result->code)->toBe('4.8.0-LIVE.11810000');
});

it('findPreviousVersion returns null when no match at all', function (): void {
    $current = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE.12345',
        'released_at' => '2026-04-22 00:00:00',
    ]);

    $result = $current->findPreviousVersion();

    expect($result)->toBeNull();
});

describe('versionFamily', function (): void {
    it('extracts major.minor from a full version code', function (): void {
        expect(GameVersion::versionFamily('4.8.0-LIVE.11875683'))->toBe('4.8');
    });

    it('extracts major.minor from a 5.x version', function (): void {
        expect(GameVersion::versionFamily('5.0.0-LIVE.99999'))->toBe('5.0');
    });

    it('returns null for a code without semver', function (): void {
        expect(GameVersion::versionFamily('garbage'))->toBeNull();
    });
});

describe('patchFamily', function (): void {
    it('extracts major.minor.patch from a full version code', function (): void {
        expect(GameVersion::patchFamily('4.7.2-LIVE.11674325'))->toBe('4.7.2');
    });

    it('extracts major.minor.patch from a 5.x version', function (): void {
        expect(GameVersion::patchFamily('5.0.0-LIVE.99999'))->toBe('5.0.0');
    });

    it('returns null for a code without semver', function (): void {
        expect(GameVersion::patchFamily('garbage'))->toBeNull();
    });
});

describe('findPreviousVersionFamily', function (): void {
    it('finds latest version from a different family', function (): void {
        $current = GameVersion::factory()->create([
            'code' => '4.8.0-LIVE.11825000',
            'released_at' => '2026-05-14 00:00:00',
        ]);
        GameVersion::factory()->create([
            'code' => '4.8.0-LIVE.11875683',
            'released_at' => '2026-05-20 00:00:00',
        ]);
        GameVersion::factory()->create([
            'code' => '4.7.2-LIVE.11674325',
            'released_at' => '2026-05-01 00:00:00',
        ]);
        GameVersion::factory()->create([
            'code' => '4.7.0-LIVE.11592622',
            'released_at' => '2026-03-29 00:00:00',
        ]);

        $result = $current->findPreviousVersionFamily();

        expect($result)->not->toBeNull()
            ->and($result->code)->toBe('4.7.2-LIVE.11674325');
    });

    it('handles CIG skipping a minor version', function (): void {
        $current = GameVersion::factory()->create([
            'code' => '5.0.0-LIVE.99999',
            'released_at' => '2026-07-01 00:00:00',
        ]);
        GameVersion::factory()->create([
            'code' => '4.8.0-LIVE.11825000',
            'released_at' => '2026-05-14 00:00:00',
        ]);
        GameVersion::factory()->create([
            'code' => '4.6.0-LIVE.11218823',
            'released_at' => '2026-02-19 00:00:00',
        ]);

        $result = $current->findPreviousVersionFamily();

        expect($result)->not->toBeNull()
            ->and($result->code)->toBe('4.8.0-LIVE.11825000');
    });

    it('returns null when no other family exists', function (): void {
        $current = GameVersion::factory()->create([
            'code' => '4.8.0-LIVE.11825000',
            'released_at' => '2026-05-14 00:00:00',
        ]);

        $result = $current->findPreviousVersionFamily();

        expect($result)->toBeNull();
    });

    it('ignores same-family builds even if newer', function (): void {
        $current = GameVersion::factory()->create([
            'code' => '4.8.0-LIVE.11825000',
            'released_at' => '2026-05-14 00:00:00',
        ]);
        GameVersion::factory()->create([
            'code' => '4.8.0-LIVE.11875683',
            'released_at' => '2026-05-20 00:00:00',
        ]);
        GameVersion::factory()->create([
            'code' => '4.7.2-LIVE.11674325',
            'released_at' => '2026-05-01 00:00:00',
        ]);

        $result = $current->findPreviousVersionFamily();

        expect($result)->not->toBeNull()
            ->and($result->code)->toBe('4.7.2-LIVE.11674325');
    });

    it('returns null for a code without semver', function (): void {
        $current = GameVersion::factory()->create([
            'code' => 'garbage',
            'released_at' => '2026-05-14 00:00:00',
        ]);

        $result = $current->findPreviousVersionFamily();

        expect($result)->toBeNull();
    });
});
