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
