<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

describe('GET /api/locations/positions', function () {
    beforeEach(function () {
        $this->defaultVersion = GameVersion::factory()->create([
            'code' => '4.1.0-LIVE',
            'channel' => 'live',
            'is_default' => true,
            'released_at' => now(),
        ]);

        Storage::fake('scunpacked');
        Cache::flush();
    });

    it('returns entities and connections from the data file', function () {
        $payload = [
            'entities' => [
                ['uuid' => 'aaa', 'name' => 'Hurston', 'type' => 'Planet', 'system' => 'stanton', 'parent_uuid' => null, 'x' => 0, 'y' => 0, 'z' => 0],
                ['uuid' => 'bbb', 'name' => 'Crusader', 'type' => 'Planet', 'system' => 'stanton', 'parent_uuid' => null, 'x' => 100, 'y' => 0, 'z' => 0],
            ],
            'connections' => [
                ['entry_uuid' => 'aaa', 'exit_uuid' => 'bbb', 'entry_system' => 'stanton', 'exit_system' => 'stanton', 'fuel_cost' => 0, 'size_class' => 'unknown'],
            ],
        ];

        Storage::disk('scunpacked')->put('starmap_positions.json', json_encode($payload, JSON_THROW_ON_ERROR));

        $response = $this->getJson('/api/locations/positions');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonCount(1, 'connections')
            ->assertJsonPath('data.0.name', 'Crusader')
            ->assertJsonPath('connections.0.entry_uuid', 'aaa');
    });

    it('filters by type', function () {
        $payload = [
            'entities' => [
                ['uuid' => 'aaa', 'name' => 'Hurston', 'type' => 'Planet', 'system' => 'stanton', 'parent_uuid' => null, 'x' => 0, 'y' => 0, 'z' => 0],
                ['uuid' => 'bbb', 'name' => 'Crusader Station', 'type' => 'Manmade', 'system' => 'stanton', 'parent_uuid' => null, 'x' => 50, 'y' => 0, 'z' => 0],
            ],
            'connections' => [],
        ];

        Storage::disk('scunpacked')->put('starmap_positions.json', json_encode($payload, JSON_THROW_ON_ERROR));

        $response = $this->getJson('/api/locations/positions?filter[type]=Planet');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Hurston');
    });

    it('returns 503 when data file is missing', function () {
        $response = $this->getJson('/api/locations/positions');

        $response->assertStatus(503);
    });
});
