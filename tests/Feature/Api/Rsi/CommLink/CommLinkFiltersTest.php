<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Rsi\CommLink\Category;
use App\Models\Rsi\CommLink\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->instance('env', 'production');
    app('cache')->setDefaultDriver('array');
    app()->forgetInstance('cache');
    app('cache')->forgetDriver(['array', 'database']);
    Cache::store('array')->flush();
});

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

    $this->getJson(route('comm-links.filters'))
        ->assertOk()
        ->assertExactJson([
            'filters' => [
                'category' => [
                    ['value' => 'Updates', 'label' => 'Updates', 'count' => 2],
                ],
                'channel' => [
                    ['value' => 'News', 'label' => 'News', 'count' => 2],
                ],
                'series' => [
                    ['value' => 'Ship Shape', 'label' => 'Ship Shape', 'count' => 2],
                ],
            ],
        ]);
});

it('returns filtered comm-link facet values without caching the filtered response', function (): void {
    $updates = Category::factory()->create(['name' => 'Updates']);
    $guides = Category::factory()->create(['name' => 'Guides']);
    $news = Channel::factory()->create(['name' => 'News']);
    $spectrum = Channel::factory()->create(['name' => 'Spectrum']);
    $shipShape = Series::factory()->create(['name' => 'Ship Shape']);
    $monthly = Series::factory()->create(['name' => 'Monthly Report']);

    CommLink::factory()->create([
        'category_id' => $updates->id,
        'channel_id' => $news->id,
        'series_id' => $shipShape->id,
    ]);

    CommLink::factory()->create([
        'category_id' => $guides->id,
        'channel_id' => $spectrum->id,
        'series_id' => $monthly->id,
    ]);

    $response = $this->getJson(route('comm-links.filters', [
        'filter' => ['channel' => 'News'],
    ]));

    $response->assertOk()
        ->assertExactJson([
            'filters' => [
                'category' => [
                    ['value' => 'Updates', 'label' => 'Updates', 'count' => 1],
                ],
                'channel' => [
                    ['value' => 'News', 'label' => 'News', 'count' => 1],
                ],
                'series' => [
                    ['value' => 'Ship Shape', 'label' => 'Ship Shape', 'count' => 1],
                ],
            ],
        ]);

    expect(Cache::get('filters:index:comm-links'))->toBeNull();
});
