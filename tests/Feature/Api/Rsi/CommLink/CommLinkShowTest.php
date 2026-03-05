<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\Category;
use App\Models\Rsi\CommLink\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('does not lazy load optional image relationships on comm-link show', function (): void {
    $channel = Channel::factory()->create([
        'name' => 'News',
        'slug' => 'news',
    ]);

    $category = Category::factory()->create([
        'name' => 'Update',
        'slug' => 'update',
    ]);

    $series = Series::factory()->create([
        'name' => 'Inside Star Citizen',
        'slug' => 'inside-star-citizen',
    ]);

    $commLink = CommLink::factory()->create([
        'cig_id' => 17648,
        'channel_id' => $channel->id,
        'category_id' => $category->id,
        'series_id' => $series->id,
    ]);

    $images = Image::factory()->count(12)->create();

    $commLink->images()->attach($images->pluck('id')->all());

    DB::flushQueryLog();
    DB::enableQueryLog();

    $response = $this->getJson(route('comm-links.show', ['id' => $commLink->cig_id]));

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $commLink->cig_id)
        ->assertJsonCount($images->count(), 'data.images')
        ->assertJsonMissingPath('data.images.0.tags')
        ->assertJsonMissingPath('data.images.0.comm_links')
        ->assertJsonMissingPath('data.images.0.duplicates')
        ->assertJsonMissingPath('data.images.0.base_image');

    $queries = collect(DB::getQueryLog())->pluck('query');

    $tagQueries = $queries
        ->filter(fn (string $query): bool => str_contains($query, 'from "comm_link_image_tags"'))
        ->count();

    $commLinkPivotQueries = $queries
        ->filter(fn (string $query): bool => str_contains($query, 'from "comm_links" inner join "comm_link_image"'))
        ->count();

    $duplicateImageQueries = $queries
        ->filter(fn (string $query): bool => str_contains($query, '"comm_link_images"."base_image_id" = ?'))
        ->count();

    expect($tagQueries)->toBe(0)
        ->and($commLinkPivotQueries)->toBe(0)
        ->and($duplicateImageQueries)->toBe(0)
        ->and($queries->count())->toBeLessThan(20);
});
