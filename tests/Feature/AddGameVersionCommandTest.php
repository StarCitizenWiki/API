<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('creates a game version and normalizes scope casing', function (): void {
    $this->artisan('game:add-version', ['code' => '4.4.0-live.10753606'])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Game version "4.4.0-LIVE.10753606" created.');

    $version = GameVersion::query()->first();

    expect($version)->not->toBeNull();
    expect($version->code)->toBe('4.4.0-LIVE.10753606');
    expect($version->channel)->toBe('live');
    expect($version->released_at)->toBeNull();
});

it('fails when the version format is invalid', function (): void {
    $this->artisan('game:add-version', ['code' => '4.4'])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('Invalid game version format. Expected Major.Minor.Patch.SCOPE.Buildnumber with scope LIVE, PTU, or EPTU (e.g. 4.4.0-LIVE.10753606).');

    expect(GameVersion::query()->count())->toBe(0);
});

it('fails when the version already exists (case-insensitive)', function (): void {
    GameVersion::query()->create([
        'code' => '4.4.0-LIVE.10753606',
        'channel' => 'live',
    ]);

    $this->artisan('game:add-version', ['code' => '4.4.0-live.10753606'])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('Game version "4.4.0-LIVE.10753606" already exists.');

    expect(GameVersion::query()->count())->toBe(1);
});

it('fails when scope is not allowed', function (): void {
    $this->artisan('game:add-version', ['code' => '4.4.0-dev.1'])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('Invalid game version format. Expected Major.Minor.Patch.SCOPE.Buildnumber with scope LIVE, PTU, or EPTU (e.g. 4.4.0-LIVE.10753606).');

    expect(GameVersion::query()->count())->toBe(0);
});

it('stores released_at when provided', function (): void {
    $releasedAt = '2025-12-06 10:30';

    $this->artisan('game:add-version', [
        'code' => '4.4.0-ptu.10753606',
        '--released-at' => $releasedAt,
    ])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Game version "4.4.0-PTU.10753606" created.');

    $version = GameVersion::query()->first();

    expect($version)->not->toBeNull();
    expect($version->released_at)->toEqual(Carbon::parse($releasedAt));
});
