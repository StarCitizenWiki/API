<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Rsi\CommLink\Category;
use App\Models\Rsi\CommLink\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns comm-link filter values with counts', function (): void {
    GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $category = Category::factory()->create(['name' => 'Updates']);
    $channel = Channel::factory()->create(['name' => 'News']);
    $series = Series::factory()->create(['name' => 'Ship Shape']);

    CommLink::factory()->create([
        'category_id' => $category->id,
        'channel_id' => $channel->id,
        'series_id' => $series->id,
    ]);

    CommLink::factory()->create([
        'category_id' => $category->id,
        'channel_id' => $channel->id,
        'series_id' => $series->id,
    ]);

    $response = $this->getJson(route('comm-links.filters'));

    $response->assertSuccessful();

    $filters = $response->json('filters');

    expect(collect($filters['category'])->contains(fn (array $row) => $row['value'] === 'Updates' && $row['count'] === 2))->toBeTrue()
        ->and(collect($filters['channel'])->contains(fn (array $row) => $row['value'] === 'News' && $row['count'] === 2))->toBeTrue()
        ->and(collect($filters['series'])->contains(fn (array $row) => $row['value'] === 'Ship Shape' && $row['count'] === 2))->toBeTrue();
});
