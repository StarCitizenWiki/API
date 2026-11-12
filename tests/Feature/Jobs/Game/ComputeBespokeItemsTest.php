<?php

declare(strict_types=1);

use App\Jobs\Game\ComputeBespokeItems;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Models\Game\VehicleData;

beforeEach(function (): void {
    $this->version = GameVersion::factory()->create([
        'code' => '4.1.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'Acme',
        'code' => 'ACME',
    ]);
});

describe('bespoke class-name tokens', function (): void {
    it('marks _Colonial_ items as bespoke', function (): void {
        $item = Item::factory()->create();
        ItemData::factory()
            ->for($item)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'class_name' => 'MRCK_TALN_Colonial_S3x8',
                'type' => 'MissileLauncher',
                'is_bespoke' => false,
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => [],
                    ],
                ],
            ]);

        new ComputeBespokeItems($this->version->id)->handle();

        expect(ItemData::where('class_name', 'MRCK_TALN_Colonial_S3x8')->first()->is_bespoke)->toBeTrue();
    });

    it('marks _PDC_ items as bespoke', function (): void {
        $item = Item::factory()->create();
        ItemData::factory()
            ->for($item)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'class_name' => 'Turret_PDC_SCItem_Template',
                'type' => 'Turret',
                'is_bespoke' => false,
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => [],
                    ],
                ],
            ]);

        new ComputeBespokeItems($this->version->id)->handle();

        expect(ItemData::where('class_name', 'Turret_PDC_SCItem_Template')->first()->is_bespoke)->toBeTrue();
    });

    it('does not mark universal items as bespoke', function (): void {
        $item = Item::factory()->create();
        ItemData::factory()
            ->for($item)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'class_name' => 'BEHR_LaserRepeater_S1',
                'type' => 'WeaponGun',
                'is_bespoke' => false,
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => [],
                    ],
                ],
            ]);

        new ComputeBespokeItems($this->version->id)->handle();

        expect(ItemData::where('class_name', 'BEHR_LaserRepeater_S1')->first()->is_bespoke)->toBeFalse();
    });
});

it('classifies a bespoke-token orphan item as bespoke', function (): void {
    $bespoke = ItemData::factory()->create([
        'game_version_id' => $this->version->id,
        'class_name' => 'WPN_Colonial_Test_Item',
        'is_player_relevant' => true,
    ]);

    (new ComputeBespokeItems($this->version->id))->handle();

    expect($bespoke->fresh()->is_bespoke)->toBeTrue();
});

it('does not rewrite unchanged rows on a repeat run', function (): void {
    $bespoke = ItemData::factory()->create([
        'game_version_id' => $this->version->id,
        'class_name' => 'WPN_Colonial_Test_Item',
        'is_player_relevant' => true,
    ]);
    $generic = ItemData::factory()->create([
        'game_version_id' => $this->version->id,
        'class_name' => 'Generic_Universal_Item',
        'is_player_relevant' => true,
    ]);

    (new ComputeBespokeItems($this->version->id))->handle();

    expect($bespoke->fresh()->is_bespoke)->toBeTrue()
        ->and($generic->fresh()->is_bespoke)->toBeFalse();

    $bespokeTs = $bespoke->fresh()->updated_at;
    $genericTs = $generic->fresh()->updated_at;

    $this->travel(1)->hour();

    // Re-run with identical input: both rows already match their target, so
    // neither should be rewritten (mirrors SyncItemCraftability's guard).
    (new ComputeBespokeItems($this->version->id))->handle();

    expect($bespoke->fresh()->updated_at->getTimestamp())->toBe($bespokeTs->getTimestamp())
        ->and($generic->fresh()->updated_at->getTimestamp())->toBe($genericTs->getTimestamp());
});

it('flips rows whose bespoke classification changed', function (): void {
    // Should NOT be bespoke, but is currently flagged bespoke with stale tags.
    $wasBespoke = ItemData::factory()->create([
        'game_version_id' => $this->version->id,
        'class_name' => 'Generic_Universal_Item',
        'is_player_relevant' => true,
        'is_bespoke' => true,
        'bespoke_vehicle_tags' => ['STALE'],
    ]);

    // Should be bespoke, but is currently flagged not bespoke.
    $wasGeneric = ItemData::factory()->create([
        'game_version_id' => $this->version->id,
        'class_name' => 'WPN_Colonial_Test_Item',
        'is_player_relevant' => true,
        'is_bespoke' => false,
        'bespoke_vehicle_tags' => null,
    ]);

    (new ComputeBespokeItems($this->version->id))->handle();

    expect($wasBespoke->fresh()->is_bespoke)->toBeFalse()
        ->and($wasBespoke->fresh()->bespoke_vehicle_tags)->toBeNull()
        ->and($wasGeneric->fresh()->is_bespoke)->toBeTrue();
});

it('chunks narrow JSON reads without changing classifications across versions', function (): void {
    $otherVersion = GameVersion::factory()->create();
    $loadout = [];
    for ($i = 0; $i < 101; $i++) {
        $name = 'Generic_Part_'.$i;
        ItemData::factory()->create([
            'game_version_id' => $this->version->id,
            'class_name' => $name,
            'data' => match ($i) {
                0 => ['stdItem' => ['RequiredTags' => ['locked']]],
                1 => ['stdItem' => ['RequiredTags' => null]],
                2 => 'null',
                3 => ['stdItem' => ['RequiredTags' => 'locked']],
                default => [],
            },
            'is_player_relevant' => true,
        ]);
        $loadout[] = ['ClassName' => $name, 'Editable' => true];
    }
    $loadout[] = ['ClassName' => 'Missing_Item', 'Editable' => false];
    $loadout[] = ['ClassName' => 'None', 'Loadout' => [['ClassName' => 'Nested_Part', 'Editable' => false]]];
    $nested = ItemData::factory()->create(['game_version_id' => $this->version->id, 'class_name' => 'Nested_Part', 'data' => []]);
    $orphan = ItemData::factory()->create(['game_version_id' => $this->version->id, 'class_name' => 'ACME_Falcon_Orphan', 'is_player_relevant' => true]);
    $foreign = ItemData::factory()->create(['game_version_id' => $otherVersion->id, 'class_name' => 'Generic_Part_0', 'is_bespoke' => false, 'data' => []]);
    for ($i = 0; $i < 101; $i++) {
        VehicleData::factory()->create([
            'game_version_id' => $this->version->id,
            'class_name' => $i === 0 ? 'ACME_Falcon_Mk1' : 'TEST_Ship_'.$i,
            'data' => match ($i) {
                0 => ['Loadout' => $loadout, 'unused' => str_repeat('x', 1000)],
                1 => ['Loadout' => null],
                2 => 'null',
                default => [],
            },
        ]);
    }
    VehicleData::factory()->create(['game_version_id' => $otherVersion->id, 'class_name' => 'OTHER_Ship', 'data' => ['Loadout' => [['ClassName' => 'Generic_Part_1', 'Editable' => false]]]]);

    DB::flushQueryLog();
    DB::enableQueryLog();
    (new ComputeBespokeItems($this->version->id))->handle();
    $queries = collect(DB::getQueryLog())->pluck('query');
    DB::disableQueryLog();

    $vehicleReads = $queries->filter(fn (string $sql): bool => str_contains($sql, 'from "game_vehicle_data"') && str_contains($sql, 'Loadout'));
    $itemReads = $queries->filter(fn (string $sql): bool => str_contains($sql, 'from "game_item_data"') && str_contains($sql, 'RequiredTags'));
    expect($vehicleReads->count())->toBe(2)
        ->and($itemReads->count())->toBe(2)
        ->and($queries->filter(function (string $sql): bool {
            if (! preg_match('/^select (.*?) from "game_(?:item|vehicle)_data"/i', $sql, $matches)) {
                return false;
            }

            return $matches[1] === '*' || (bool) preg_match('/(?:^|,\s*)"data"(?:\s*,|$)/', $matches[1]);
        }))->toBeEmpty();
    $tagged = ItemData::where('game_version_id', $this->version->id)->where('class_name', 'Generic_Part_0')->firstOrFail();
    $generic = ItemData::where('game_version_id', $this->version->id)->where('class_name', 'Generic_Part_1')->firstOrFail();
    expect($tagged->is_bespoke)->toBeTrue()->and($tagged->bespoke_vehicle_tags)->toBe(['ACME_Falcon', 'ACME_Falcon_Mk1'])
        ->and($generic->is_bespoke)->toBeFalse()
        ->and($nested->fresh()->bespoke_vehicle_tags)->toBe(['ACME_Falcon', 'ACME_Falcon_Mk1'])
        ->and($orphan->fresh()->bespoke_vehicle_tags)->toBe(['ACME_Falcon', 'ACME_Falcon_Mk1'])
        ->and($foreign->fresh()->is_bespoke)->toBeFalse();
    $genericRows = ItemData::where('game_version_id', $this->version->id)->where('class_name', 'like', 'Generic_Part_%')->where('class_name', '!=', 'Generic_Part_0')->get();
    expect($genericRows)->toHaveCount(100)
        ->and($genericRows->every(fn (ItemData $item): bool => ! $item->is_bespoke && $item->bespoke_vehicle_tags === null))->toBeTrue();
});
