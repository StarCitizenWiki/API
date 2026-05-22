<?php

use App\Models\Game\GameVersion;
use App\Models\Game\ItemData;
use App\Models\Game\VehicleData;

describe('deleting a game version', function () {
    it('cascades to versioned data tables', function () {
        $version = GameVersion::factory()->create();

        ItemData::factory()->count(3)->create(['game_version_id' => $version->id]);
        VehicleData::factory()->count(2)->create(['game_version_id' => $version->id]);

        expect(ItemData::where('game_version_id', $version->id)->count())->toBe(3)
            ->and(VehicleData::where('game_version_id', $version->id)->count())->toBe(2);

        $version->delete();

        expect(ItemData::where('game_version_id', $version->id)->count())->toBe(0)
            ->and(VehicleData::where('game_version_id', $version->id)->count())->toBe(0);
    });

    it('does not delete data from other versions', function () {
        $keepVersion = GameVersion::factory()->create();
        $deleteVersion = GameVersion::factory()->create();

        ItemData::factory()->count(3)->create(['game_version_id' => $keepVersion->id]);
        ItemData::factory()->count(2)->create(['game_version_id' => $deleteVersion->id]);

        $deleteVersion->delete();

        expect(ItemData::where('game_version_id', $keepVersion->id)->count())->toBe(3)
            ->and(ItemData::where('game_version_id', $deleteVersion->id)->count())->toBe(0);
    });
});
