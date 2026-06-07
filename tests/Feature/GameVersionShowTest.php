<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\GameVersionAlias;

describe('GET /api/game-versions/{identifier}', function (): void {
    it('returns a game version by code', function (): void {
        $version = GameVersion::factory()->create([
            'code' => '4.7.0-LIVE.11518367',
            'channel' => 'LIVE',
        ]);

        $response = $this->getJson("/api/game-versions/{$version->code}");

        $response->assertOk();
        $response->assertJsonPath('data.code', $version->code);
        $response->assertJsonPath('data.channel', 'LIVE');
    });

    it('returns a game version by code case-insensitively', function (): void {
        $version = GameVersion::factory()->create([
            'code' => '4.7.0-LIVE.11518367',
        ]);

        $response = $this->getJson('/api/game-versions/4.7.0-live.11518367');

        $response->assertOk();
        $response->assertJsonPath('data.code', $version->code);
    });

    it('returns the resolved game version for an alias', function (): void {
        $version = GameVersion::factory()->create([
            'code' => '4.8.1-LIVE.11882409',
        ]);

        GameVersionAlias::query()->create([
            'code' => '4.8.0-LIVE.11825000',
            'game_version_id' => $version->id,
        ]);

        $response = $this->getJson('/api/game-versions/4.8.0-live.11825000');

        $response->assertOk();
        $response->assertJsonPath('data.code', $version->code);
    });

    it('returns 404 for unknown version code', function (): void {
        $response = $this->getJson('/api/game-versions/nonexistent');

        $response->assertNotFound();
    });
});
