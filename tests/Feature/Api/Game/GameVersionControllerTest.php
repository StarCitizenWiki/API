<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\GameVersionAlias;

it('lists all game versions with default sort order', function (): void {
    $newestVersion = GameVersion::factory()->create([
        'code' => '3.24.1-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $olderVersion = GameVersion::factory()->create([
        'code' => '3.24.0-LIVE',
        'channel' => 'live',
        'released_at' => now()->subDays(7),
        'is_default' => false,
    ]);

    $response = $this->getJson('/api/game-versions');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.code', $newestVersion->code)
        ->assertJsonPath('data.0.channel', $newestVersion->channel)
        ->assertJsonPath('data.0.released_at', $newestVersion->released_at?->toIso8601String())
        ->assertJsonPath('data.0.is_default', true)
        ->assertJsonPath('data.1.code', $olderVersion->code)
        ->assertJsonPath('data.1.channel', $olderVersion->channel)
        ->assertJsonPath('data.1.released_at', $olderVersion->released_at?->toIso8601String())
        ->assertJsonPath('data.1.is_default', false)
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.valid_relations', []);
});

it('does not list game version aliases', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.8.1-LIVE.11882409',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    GameVersionAlias::query()->create([
        'code' => '4.8.0-LIVE.11825000',
        'game_version_id' => $version->id,
    ]);

    $response = $this->getJson('/api/game-versions');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.code', '4.8.1-LIVE.11882409');
});

it('filters game versions by channel', function (): void {
    GameVersion::factory()->create([
        'code' => '3.24.1-LIVE',
        'channel' => 'live',
        'is_default' => true,
    ]);

    GameVersion::factory()->create([
        'code' => '3.24.1-PTU',
        'channel' => 'ptu',
        'is_default' => false,
    ]);

    GameVersion::factory()->create([
        'code' => '3.24.2-EPTU',
        'channel' => 'eptu',
        'is_default' => false,
    ]);

    $response = $this->getJson('/api/game-versions?filter[channel]=ptu');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.channel', 'ptu');
});

it('filters game versions by is_default', function (): void {
    GameVersion::factory()->create([
        'code' => '3.24.1-LIVE',
        'channel' => 'live',
        'is_default' => true,
    ]);

    GameVersion::factory()->create([
        'code' => '3.24.0-LIVE',
        'channel' => 'live',
        'is_default' => false,
    ]);

    $response = $this->getJson('/api/game-versions?filter[is_default]=1');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.is_default', true);
});

it('filters game versions by exact code', function (): void {
    GameVersion::factory()->create([
        'code' => '3.24.1-LIVE',
        'channel' => 'live',
        'is_default' => true,
    ]);

    GameVersion::factory()->create([
        'code' => '3.24.0-LIVE',
        'channel' => 'live',
        'is_default' => false,
    ]);

    $response = $this->getJson('/api/game-versions?filter[code]=3.24.1-LIVE');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.code', '3.24.1-LIVE');
});

it('sorts game versions by code ascending', function (): void {
    GameVersion::factory()->create(['code' => '3.24.2-LIVE']);
    GameVersion::factory()->create(['code' => '3.24.0-LIVE']);
    GameVersion::factory()->create(['code' => '3.24.1-LIVE']);

    $response = $this->getJson('/api/game-versions?sort=code');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.code', '3.24.0-LIVE')
        ->assertJsonPath('data.1.code', '3.24.1-LIVE')
        ->assertJsonPath('data.2.code', '3.24.2-LIVE');
});

it('sorts game versions by code descending', function (): void {
    GameVersion::factory()->create(['code' => '3.24.0-LIVE']);
    GameVersion::factory()->create(['code' => '3.24.1-LIVE']);
    GameVersion::factory()->create(['code' => '3.24.2-LIVE']);

    $response = $this->getJson('/api/game-versions?sort=-code');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.code', '3.24.2-LIVE')
        ->assertJsonPath('data.1.code', '3.24.1-LIVE')
        ->assertJsonPath('data.2.code', '3.24.0-LIVE');
});

it('sorts game versions by channel', function (): void {
    GameVersion::factory()->create(['channel' => 'ptu', 'code' => '3.24.1-PTU']);
    GameVersion::factory()->create(['channel' => 'live', 'code' => '3.24.1-LIVE']);
    GameVersion::factory()->create(['channel' => 'eptu', 'code' => '3.24.1-EPTU']);

    $response = $this->getJson('/api/game-versions?sort=channel');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.channel', 'eptu')
        ->assertJsonPath('data.1.channel', 'live')
        ->assertJsonPath('data.2.channel', 'ptu');
});

it('sorts game versions by released_at ascending', function (): void {
    GameVersion::factory()->create([
        'code' => '3.24.0',
        'released_at' => now()->subDays(14),
    ]);
    GameVersion::factory()->create([
        'code' => '3.24.1',
        'released_at' => now()->subDays(7),
    ]);
    GameVersion::factory()->create([
        'code' => '3.24.2',
        'released_at' => now(),
    ]);

    $response = $this->getJson('/api/game-versions?sort=released_at');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.code', '3.24.0')
        ->assertJsonPath('data.1.code', '3.24.1')
        ->assertJsonPath('data.2.code', '3.24.2');
});

it('shows the default game version via /default endpoint', function (): void {
    GameVersion::factory()->create([
        'code' => '3.24.0-LIVE',
        'is_default' => false,
    ]);

    $defaultVersion = GameVersion::factory()->create([
        'code' => '3.24.1-LIVE',
        'is_default' => true,
    ]);

    $response = $this->getJson('/api/game-versions/default');

    $response->assertSuccessful()
        ->assertJsonPath('data.code', $defaultVersion->code)
        ->assertJsonPath('data.is_default', true);
});

it('returns the default game version even when a newer version exists', function (): void {
    GameVersion::factory()->create([
        'code' => '3.24.0-LIVE',
        'is_default' => false,
        'released_at' => now()->subDay(),
    ]);

    $defaultVersion = GameVersion::factory()->create([
        'code' => '3.24.1-LIVE',
        'is_default' => true,
        'released_at' => now()->subDays(2),
    ]);

    GameVersion::factory()->create([
        'code' => '3.24.2-LIVE',
        'is_default' => false,
        'released_at' => now(),
    ]);

    $response = $this->getJson('/api/game-versions/default');

    $response->assertSuccessful()
        ->assertJsonPath('data.code', $defaultVersion->code)
        ->assertJsonPath('data.is_default', true);
});

it('returns 404 when no default version exists', function (): void {
    GameVersion::factory()->create([
        'code' => '3.24.1-LIVE',
        'is_default' => false,
    ]);

    $response = $this->getJson('/api/game-versions/default');

    $response->assertNotFound();
});

it('paginates game versions with custom page size', function (): void {
    GameVersion::factory()->count(15)->create();

    $response = $this->getJson('/api/game-versions?page[size]=5');

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.per_page', 5)
        ->assertJsonPath('meta.current_page', 1);
});
