<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\GameVersionAlias;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

it('creates a game version and normalizes scope casing', function (): void {
    $this->artisan('game:add-version', ['code' => '4.4.0-live.10753606'])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Game version "4.4.0-LIVE.10753606" created.');

    $version = GameVersion::query()->first();

    expect($version)->not->toBeNull()
        ->and($version->code)->toBe('4.4.0-LIVE.10753606')
        ->and($version->channel)->toBe('live')
        ->and($version->released_at)->toBeNull();
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

it('fails when the version already exists as an alias', function (): void {
    $target = GameVersion::query()->create([
        'code' => '4.4.1-LIVE.10753607',
        'channel' => 'live',
    ]);

    GameVersionAlias::query()->create([
        'code' => '4.4.0-LIVE.10753606',
        'game_version_id' => $target->id,
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

    expect($version)->not->toBeNull()
        ->and($version->released_at)->toEqual(Carbon::parse($releasedAt));
});

it('can mark the version as default', function (): void {
    $this->artisan('game:add-version', [
        'code' => '4.4.4-live.1',
        '--default' => true,
    ])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Game version "4.4.4-LIVE.1" created.');

    $version = GameVersion::query()->first();

    expect($version)->not->toBeNull()
        ->and($version->is_default)->toBeTrue();
});

it('replaces the existing default when requested', function (): void {
    GameVersion::query()->create([
        'code' => '4.4.0-LIVE.10753606',
        'channel' => 'live',
        'is_default' => true,
    ]);

    $this->artisan('game:add-version', [
        'code' => '4.4.1-ptu.2',
        '--default' => true,
    ])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Game version "4.4.1-PTU.2" created.');

    expect(GameVersion::query()->count())->toBe(2)
        ->and(GameVersion::query()->where('code', '4.4.1-PTU.2')->value('is_default'))->toBeTrue()
        ->and(GameVersion::query()->where('code', '4.4.0-LIVE.10753606')->value('is_default'))->toBeFalse();
});

it('creates a game version alias when alias-of is provided', function (): void {
    $target = GameVersion::factory()->create([
        'code' => '4.8.1-LIVE.11882409',
    ]);

    $this->artisan('game:add-version', [
        'code' => '4.8.0-live.11825000',
        '--alias-of' => '4.8.1-live.11882409',
    ])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Game version alias "4.8.0-LIVE.11825000" created for "4.8.1-LIVE.11882409".');

    $alias = GameVersionAlias::query()->first();

    expect($alias)->not->toBeNull()
        ->and($alias->code)->toBe('4.8.0-LIVE.11825000')
        ->and($alias->game_version_id)->toBe($target->id)
        ->and(GameVersion::query()->count())->toBe(1);
});

it('fails when the alias target format is invalid', function (): void {
    $this->artisan('game:add-version', [
        'code' => '4.8.0-LIVE.11825000',
        '--alias-of' => '4.8',
    ])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('Invalid target game version format. Expected Major.Minor.Patch.SCOPE.Buildnumber with scope LIVE, PTU, or EPTU (e.g. 4.4.0-LIVE.10753606).');

    expect(GameVersionAlias::query()->count())->toBe(0);
});

it('fails when the alias target version does not exist', function (): void {
    $this->artisan('game:add-version', [
        'code' => '4.8.0-LIVE.11825000',
        '--alias-of' => '4.8.1-LIVE.11882409',
    ])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('Target game version "4.8.1-LIVE.11882409" was not found.');

    expect(GameVersionAlias::query()->count())->toBe(0);
});
