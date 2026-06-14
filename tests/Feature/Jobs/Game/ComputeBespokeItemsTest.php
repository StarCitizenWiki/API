<?php

declare(strict_types=1);

use App\Jobs\Game\ComputeBespokeItems;
use App\Models\Game\GameVersion;
use App\Models\Game\ItemData;

beforeEach(function (): void {
    $this->version = GameVersion::factory()->create();
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
    // A bespoke-token item (classified bespoke) and a generic item (not bespoke).
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
