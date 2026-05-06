<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\Category;
use App\Models\Rsi\CommLink\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns the comm-link show response contract', function (): void {
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
        'comment_count' => 17,
        'images_count' => 12,
        'links_count' => 0,
    ]);

    $images = Image::factory()->count(12)->create();

    $commLink->images()->attach($images->pluck('id')->all());

    $response = $this->getJson(route('comm-links.show', ['id' => $commLink->cig_id]));

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $commLink->cig_id)
        ->assertJsonPath('data.api_url', route('comm-links.show', ['id' => $commLink->cig_id]))
        ->assertJsonPath('data.api_public_url', route('web.comm-links.show', $commLink->cig_id))
        ->assertJsonPath('data.channel', $channel->name)
        ->assertJsonPath('data.category', $category->name)
        ->assertJsonPath('data.series', $series->name)
        ->assertJsonPath('data.links_count', 0)
        ->assertJsonPath('data.comment_count', 17)
        ->assertJsonPath('data.created_at', $commLink->created_at->toIso8601String())
        ->assertJsonPath('data.images.0.id', $images->first()->id)
        ->assertJsonPath('data.images.0.api_url', route('comm-link-images.show', $images->first()->id))
        ->assertJsonPath('data.images.0.similar_url', route('comm-link-images.similar', $images->first()->id))
        ->assertJsonCount($images->count(), 'data.images')
        ->assertJsonPath('data.images.0.name', $images->first()->name)
        ->assertJsonPath('meta.prev_id', -1)
        ->assertJsonPath('meta.next_id', -1)
        ->assertJsonPath('meta.valid_relations.0', 'images')
        ->assertJsonPath('meta.valid_relations.1', 'links');
});
