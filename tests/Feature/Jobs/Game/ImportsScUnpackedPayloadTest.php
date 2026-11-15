<?php

declare(strict_types=1);

use App\Jobs\Game\ImportItemData;
use App\Jobs\Game\ImportMissionData;
use App\Jobs\Game\ImportVehicleData;
use App\Models\Game\GameVersion;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

it('ignores missing and empty payloads in each import job', function (string $jobClass, ?string $contents): void {
    Storage::fake('scunpacked');
    if ($contents !== null) {
        Storage::disk('scunpacked')->put('payload.json', $contents);
    }

    (new $jobClass(1, 'payload.json'))->handle();
    expect(true)->toBeTrue();
})->with([ImportItemData::class, ImportVehicleData::class, ImportMissionData::class])->with([null, '', " \n "]);

it('rolls back the mission and versioned row when the versioned row write fails', function (): void {
    Storage::fake('scunpacked');
    $version = GameVersion::factory()->create();
    $uuid = fake()->uuid();
    Storage::disk('scunpacked')->put('mission.json', json_encode(['UUID' => $uuid], JSON_THROW_ON_ERROR));

    DB::listen(function ($query): void {
        if (str_contains(strtolower($query->sql), 'insert into') && str_contains($query->sql, 'game_mission_data')) {
            throw new RuntimeException('injected failure');
        }
    });

    try {
        (new ImportMissionData($version->id, 'mission.json'))->handle();
        test()->fail('Expected the import to fail.');
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('injected failure');
    }

    expect(Mission::query()->where('uuid', $uuid)->exists())->toBeFalse()
        ->and(MissionData::query()->where('game_version_id', $version->id)->exists())->toBeFalse();
});
