<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\Category;
use App\Models\Rsi\CommLink\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('searches comm-links by text query without bigint cast errors', function (): void {
    $category = Category::factory()->create();
    $series = Series::factory()->create();

    $publicChannel = Channel::factory()->create([
        'name' => 'News',
        'slug' => 'news',
    ]);
    $subscriberChannel = Channel::factory()->create([
        'name' => 'Subscriber',
        'slug' => 'subscriber',
    ]);

    $visibleCommLink = CommLink::factory()->create([
        'cig_id' => 24001,
        'title' => 'Flight model update',
        'category_id' => $category->id,
        'series_id' => $series->id,
        'channel_id' => $publicChannel->id,
    ]);

    $subscriberCommLink = CommLink::factory()->create([
        'cig_id' => 24002,
        'title' => 'Flight model deep dive',
        'category_id' => $category->id,
        'series_id' => $series->id,
        'channel_id' => $subscriberChannel->id,
    ]);

    $response = $this->postJson(route('comm-links.search'), [
        'query' => 'flight model',
    ]);

    $response->assertSuccessful()
        ->assertHeader('Deprecated', 'true');

    $ids = collect($response->json('data'))->pluck('id');

    expect($response->json('meta.deprecated'))->toBeTrue()
        ->and($ids)->toContain($visibleCommLink->cig_id)
        ->and($ids)->not->toContain($subscriberCommLink->cig_id);
});

it('searches comm-links by numeric cig id', function (): void {
    $category = Category::factory()->create();
    $series = Series::factory()->create();
    $channel = Channel::factory()->create([
        'name' => 'News',
        'slug' => 'news',
    ]);

    $targetCommLink = CommLink::factory()->create([
        'cig_id' => 25001,
        'title' => 'Roadmap roundup',
        'category_id' => $category->id,
        'series_id' => $series->id,
        'channel_id' => $channel->id,
    ]);

    CommLink::factory()->create([
        'cig_id' => 25002,
        'title' => 'Another comm-link',
        'category_id' => $category->id,
        'series_id' => $series->id,
        'channel_id' => $channel->id,
    ]);

    $response = $this->postJson(route('comm-links.search'), [
        'query' => (string) $targetCommLink->cig_id,
    ]);

    $response->assertSuccessful();

    expect(collect($response->json('data'))->pluck('id'))
        ->toContain($targetCommLink->cig_id);
});
