<?php

declare(strict_types=1);

use App\Jobs\Game\ComputeBespokeItems;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;

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