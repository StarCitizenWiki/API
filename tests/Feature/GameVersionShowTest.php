<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

    it('returns 404 for unknown version code', function (): void {
        $response = $this->getJson('/api/game-versions/nonexistent');

        $response->assertNotFound();
    });
});
