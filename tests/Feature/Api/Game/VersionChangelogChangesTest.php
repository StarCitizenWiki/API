<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\VersionDiff;

describe('changes', function (): void {
    beforeEach(function (): void {
        $this->previousVersion = GameVersion::factory()->create([
            'code' => '4.7.2-LIVE.88888888',
            'channel' => 'live',
            'released_at' => now()->subWeek(),
            'is_hidden' => false,
        ]);

        $this->version = GameVersion::factory()->create([
            'code' => '4.8.0-LIVE.99999999',
            'channel' => 'live',
            'is_default' => true,
            'released_at' => now(),
            'is_hidden' => false,
        ]);

        VersionDiff::factory()->create([
            'from_version_id' => $this->previousVersion->id,
            'to_version_id' => $this->version->id,
            'entity_type' => Item::class,
            'entity_id' => 1,
            'change_type' => 'modified',
        ]);
    });

    it('returns rows matching the entity type key', function (): void {
        $response = $this->getJson('/api/game-versions/'.$this->version->code.'/changelog/changes?filter[entity_type]=item');

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.entity_id'))->toBe(1);
    });

    it('rejects an unknown entity type key', function (): void {
        $this->getJson('/api/game-versions/'.$this->version->code.'/changelog/changes?filter[entity_type]=bogus')
            ->assertStatus(422);
    });

    it('emits the entity type key instead of the morph class', function (): void {
        $response = $this->getJson('/api/game-versions/'.$this->version->code.'/changelog/changes');

        $response->assertOk();
        expect($response->json('data.0.entity_type'))->toBe('item');
    });
});
