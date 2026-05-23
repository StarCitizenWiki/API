<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\Category;
use App\Models\Rsi\CommLink\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Link;
use App\Models\Rsi\CommLink\Series;
use Illuminate\Console\Command;

beforeEach(function (): void {
    $this->channel = Channel::query()->create([
        'name' => 'Test Channel',
        'slug' => 'test-channel',
    ]);

    $this->category = Category::query()->create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    $this->series = Series::query()->create([
        'name' => 'Test Series',
        'slug' => 'test-series',
    ]);

    $this->images = Image::factory(3)->create();
    $this->links = Link::factory(2)->create();

    $this->commLink1 = CommLink::query()->create([
        'cig_id' => 1,
        'title' => 'Test Comm-Link 1',
        'comment_count' => 0,
        'images_count' => 0,
        'links_count' => 0,
        'url' => '/comm-link/SCW/1-API',
        'file' => '2020-01-01_000000.html',
        'channel_id' => $this->channel->id,
        'category_id' => $this->category->id,
        'series_id' => $this->series->id,
    ]);

    $this->commLink2 = CommLink::query()->create([
        'cig_id' => 2,
        'title' => 'Test Comm-Link 2',
        'comment_count' => 0,
        'images_count' => 0,
        'links_count' => 0,
        'url' => '/comm-link/SCW/2-API',
        'file' => '2020-01-01_000000.html',
        'channel_id' => $this->channel->id,
        'category_id' => $this->category->id,
        'series_id' => $this->series->id,
    ]);

    $this->commLink1->images()->sync($this->images->take(2)->pluck('id'));
    $this->commLink1->links()->sync($this->links->pluck('id'));

    $this->commLink2->images()->sync($this->images->take(1)->pluck('id'));
});

it('backfills comm-link counts successfully', function () {
    expect($this->commLink1->fresh()->images_count)->toBe(0)
        ->and($this->commLink1->fresh()->links_count)->toBe(0)
        ->and($this->commLink2->fresh()->images_count)->toBe(0)
        ->and($this->commLink2->fresh()->links_count)->toBe(0);

    $this->artisan('comm-link:backfill-counts')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Successfully updated 2 comm-links.');

    expect($this->commLink1->fresh()->images_count)->toBe(2)
        ->and($this->commLink1->fresh()->links_count)->toBe(2)
        ->and($this->commLink2->fresh()->images_count)->toBe(1)
        ->and($this->commLink2->fresh()->links_count)->toBe(0);
});

it('runs in dry-run mode without modifying data', function () {
    $this->artisan('comm-link:backfill-counts --dry-run')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Dry run completed for 2 comm-links.')
        ->doesntExpectOutput('Successfully updated');

    expect($this->commLink1->fresh()->images_count)->toBe(0)
        ->and($this->commLink1->fresh()->links_count)->toBe(0);
});

it('handles chunk size option', function () {
    $this->artisan('comm-link:backfill-counts --chunk=1')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Successfully updated 2 comm-links.');

    expect($this->commLink1->fresh()->images_count)->toBe(2)
        ->and($this->commLink1->fresh()->links_count)->toBe(2);
});

it('handles empty database', function () {
    CommLink::query()->delete();

    $this->artisan('comm-link:backfill-counts')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Successfully updated 0 comm-links.');
});
