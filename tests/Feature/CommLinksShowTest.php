<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\Category;
use App\Models\Rsi\CommLink\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Link;
use App\Models\Rsi\CommLink\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the comm-link show view with api data', function (): void {
    $channel = Channel::factory()->create([
        'name' => 'News',
        'slug' => 'news',
    ]);

    $category = Category::factory()->create([
        'name' => 'Patch Notes',
        'slug' => 'patch-notes',
    ]);

    $series = Series::factory()->create([
        'name' => 'Alpha Updates',
        'slug' => 'alpha-updates',
    ]);

    $commLink = CommLink::factory()->create([
        'cig_id' => 13001,
        'title' => 'Alpha 4.0 Patch Notes',
        'translation' => ['en' => 'Update highlights for Alpha 4.0.'],
        'channel_id' => $channel->id,
        'category_id' => $category->id,
        'series_id' => $series->id,
    ]);

    $link = Link::factory()->create([
        'text' => 'Read on RSI',
        'href' => 'https://example.test/patch-notes',
    ]);

    $image = Image::factory()->create([
        'alt' => 'Alpha 4.0 banner',
    ]);

    $commLink->links()->attach($link->id);
    $commLink->images()->attach($image->id);

    $response = $this->get(route('web.comm-links.show', $commLink->cig_id));

    $response->assertOk()
        ->assertViewIs('comm-links.show')
        ->assertSee('Alpha 4.0 Patch Notes')
        ->assertSee('Update highlights for Alpha 4.0.')
        ->assertSee('Read on RSI')
        ->assertSee('Alpha 4.0 banner')
        ->assertSee('Metadata')
        ->assertSee('Links')
        ->assertSee('Images');
});
