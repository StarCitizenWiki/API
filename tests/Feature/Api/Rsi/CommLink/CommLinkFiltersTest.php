<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\Category;
use App\Models\Rsi\CommLink\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Series;

it('filters comm-link facet values by publication year without an ambiguous column error', function (): void {
    $category = Category::factory()->create();
    $channel = Channel::factory()->create();
    $series = Series::factory()->create();

    CommLink::factory()->create([
        'category_id' => $category->id,
        'channel_id' => $channel->id,
        'series_id' => $series->id,
        'created_at' => '2024-06-01 12:00:00',
    ]);

    CommLink::factory()->create([
        'category_id' => $category->id,
        'channel_id' => $channel->id,
        'series_id' => $series->id,
        'created_at' => '2023-06-01 12:00:00',
    ]);

    $this->getJson(route('comm-links.filters', ['filter' => ['created_at' => '2024']]))
        ->assertOk()
        ->assertJsonPath('filters.category.0.value', $category->name);
});
