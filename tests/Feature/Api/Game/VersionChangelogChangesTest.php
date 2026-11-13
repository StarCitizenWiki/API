<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\VersionDiff;

describe('previous patch changelog', function (): void {
    beforeEach(function (): void {
        GameVersion::factory()->create(['code' => '4.9.0-LIVE.1', 'channel' => 'live', 'released_at' => '2026-07-01']);
        $this->previousPatch = GameVersion::factory()->create(['code' => '4.10.2-LIVE.1', 'channel' => 'live', 'released_at' => '2026-10-01']);
        $this->targetPatch = GameVersion::factory()->create(['code' => '4.10.3-LIVE.1', 'channel' => 'live', 'released_at' => '2026-11-01', 'is_default' => true]);
        VersionDiff::factory()->count(3)
            ->sequence(fn ($sequence) => ['entity_id' => 3 - $sequence->index])
            ->create(['from_version_id' => $this->previousPatch->id, 'to_version_id' => $this->targetPatch->id, 'change_type' => 'added']);
    });

    it('returns the previous patch and its summary', function (): void {
        $this->getJson('/api/game-versions/'.$this->targetPatch->code.'/changelog')
            ->assertOk()
            ->assertJsonPath('data.from_version.code', $this->previousPatch->code)
            ->assertJsonPath('data.to_version.code', $this->targetPatch->code)
            ->assertJsonPath('data.summary.item.added', 3);
    });

    it('returns changes in a stable order across pages', function (): void {
        $url = '/api/game-versions/'.$this->targetPatch->code.'/changelog/changes?page[size]=2';

        $this->getJson($url.'&page[number]=1')
            ->assertOk()
            ->assertJsonPath('data.0.entity_id', 3)
            ->assertJsonPath('data.1.entity_id', 2);

        $this->getJson($url.'&page[number]=2')
            ->assertOk()
            ->assertJsonPath('data.0.entity_id', 1);
    });
});

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
