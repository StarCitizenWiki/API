<?php

declare(strict_types=1);

use App\Jobs\Game\ComputeItemVariantGroups;
use App\Models\Game\GameVersion;
use App\Models\Game\ItemData;
use App\Models\Game\VariantGroup;

beforeEach(function (): void {
    $this->version = GameVersion::factory()->create();
});

it('does not rewrite rows whose base_id is already null during the bulk reset', function (): void {
    // Non-player-relevant items are only ever touched by the bulk base_id
    // reset, so they isolate the change-guard behaviour.
    $nullBase = ItemData::factory()->create([
        'game_version_id' => $this->version->id,
        'is_player_relevant' => false,
        'base_id' => null,
    ]);

    // A pre-existing variant group triggers the `$hadPreviousGroups` reset path.
    VariantGroup::query()->create([
        'game_version_id' => $this->version->id,
        'set_name' => 'Test Set',
    ]);

    $nullBaseTs = $nullBase->fresh()->updated_at;

    $this->travel(1)->hour();

    (new ComputeItemVariantGroups($this->version->id))->handle();

    // The row already has base_id = null, so the guarded reset must skip it.
    expect($nullBase->fresh()->base_id)->toBeNull()
        ->and($nullBase->fresh()->updated_at->getTimestamp())->toBe($nullBaseTs->getTimestamp());
});

it('clears rows that carry a stale base_id', function (): void {
    $nullBase = ItemData::factory()->create([
        'game_version_id' => $this->version->id,
        'is_player_relevant' => false,
        'base_id' => null,
    ]);
    $setBase = ItemData::factory()->create([
        'game_version_id' => $this->version->id,
        'is_player_relevant' => false,
        'base_id' => $nullBase->id,
    ]);

    VariantGroup::query()->create([
        'game_version_id' => $this->version->id,
        'set_name' => 'Test Set',
    ]);

    new ComputeItemVariantGroups($this->version->id)->handle();

    // The row had a base_id and is not part of any computed group, so it must be cleared
    expect($setBase->fresh()->base_id)->toBeNull();
});
