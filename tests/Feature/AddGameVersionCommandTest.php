<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\GameVersionAlias;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VersionDiff;
use Illuminate\Console\Command;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

describe('compute version diff', function (): void {
    beforeEach(function (): void {
        $this->fromVersion = GameVersion::factory()->create();
        $this->toVersion = GameVersion::factory()->create();
        $this->diffAttributes = [
            'from_version_id' => $this->fromVersion->id,
            'to_version_id' => $this->toVersion->id,
        ];
        $this->commandArguments = [
            'version' => $this->toVersion->code,
            '--from' => $this->fromVersion->code,
        ];
    });

    it('rejects an invalid type without deleting existing diffs', function (): void {
        $diff = VersionDiff::factory()->create($this->diffAttributes);

        $this->artisan('game:compute-version-diff', $this->commandArguments + ['--type' => 'invalid'])
            ->expectsOutput('Unknown entity type "invalid". Available: item, vehicle, blueprint, mission, starmap_location')
            ->assertExitCode(Command::FAILURE);

        expect(VersionDiff::query()->find($diff->id))->not->toBeNull();
    });

    it('recomputes only the selected type and preserves other types', function (): void {
        $itemDiff = VersionDiff::factory()->create($this->diffAttributes + ['entity_type' => Item::class]);
        $vehicleDiff = VersionDiff::factory()->create($this->diffAttributes + ['entity_type' => Vehicle::class]);
        $item = ItemData::factory()->create(['game_version_id' => $this->toVersion->id]);

        $this->artisan('game:compute-version-diff', $this->commandArguments + ['--type' => 'item'])
            ->expectsConfirmation(sprintf('Diff for %s -> %s already exists. Recompute?', $this->fromVersion->code, $this->toVersion->code), 'yes')
            ->assertExitCode(Command::SUCCESS);

        expect(VersionDiff::query()->find($itemDiff->id))->toBeNull()
            ->and(VersionDiff::query()->find($vehicleDiff->id))->not->toBeNull()
            ->and(VersionDiff::query()->where($this->diffAttributes)->where('entity_type', Item::class)->where('entity_id', $item->item_id)->value('change_type'))->toBe('added');
    });

    it('computes a type without prompting when only another type has diffs', function (): void {
        $vehicleDiff = VersionDiff::factory()->create($this->diffAttributes + ['entity_type' => Vehicle::class]);
        $item = ItemData::factory()->create(['game_version_id' => $this->toVersion->id]);

        $this->artisan('game:compute-version-diff', $this->commandArguments + ['--type' => 'item'])
            ->assertExitCode(Command::SUCCESS);

        expect(VersionDiff::query()->find($vehicleDiff->id))->not->toBeNull()
            ->and(VersionDiff::query()->where($this->diffAttributes)->where('entity_type', Item::class)->where('entity_id', $item->item_id)->value('change_type'))->toBe('added');
    });

    it('recomputes all types when no type is selected', function (): void {
        $itemDiff = VersionDiff::factory()->create($this->diffAttributes + ['entity_type' => Item::class]);
        $vehicleDiff = VersionDiff::factory()->create($this->diffAttributes + ['entity_type' => Vehicle::class]);
        $otherVersion = GameVersion::factory()->create();
        $otherPairDiff = VersionDiff::factory()->create([
            'from_version_id' => $this->fromVersion->id,
            'to_version_id' => $otherVersion->id,
        ]);

        $this->artisan('game:compute-version-diff', $this->commandArguments)
            ->expectsConfirmation(sprintf('Diff for %s -> %s already exists. Recompute?', $this->fromVersion->code, $this->toVersion->code), 'yes')
            ->assertExitCode(Command::SUCCESS);

        expect(VersionDiff::query()->find($itemDiff->id))->toBeNull()
            ->and(VersionDiff::query()->find($vehicleDiff->id))->toBeNull()
            ->and(VersionDiff::query()->find($otherPairDiff->id))->not->toBeNull();
    });

    it('inserts additions and removals in bounded batches', function (string $changeType): void {
        $versionId = $changeType === 'added' ? $this->toVersion->id : $this->fromVersion->id;
        $manufacturer = Manufacturer::factory()->create();
        ItemData::factory()->count(501)->create([
            'game_version_id' => $versionId,
            'manufacturer_id' => $manufacturer->id,
        ]);
        $batchSizes = [];
        DB::listen(function (QueryExecuted $query) use (&$batchSizes): void {
            if (str_starts_with($query->sql, 'insert into "game_version_diffs"')) {
                $batchSizes[] = count($query->bindings) / 7;
            }
        });

        $this->artisan('game:compute-version-diff', $this->commandArguments + ['--type' => 'item'])
            ->assertExitCode(Command::SUCCESS);

        expect($batchSizes)->toBe([500, 1])
            ->and(VersionDiff::query()->where($this->diffAttributes)->where('change_type', $changeType)->count())->toBe(501);
    })->with(['added', 'removed']);
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
