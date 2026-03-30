<?php

declare(strict_types=1);

use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\Galactapedia\Category;
use App\Models\StarCitizen\Galactapedia\Tag;
use App\Models\StarCitizen\Galactapedia\Template;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->category = Category::factory()->create();
    $this->tag = Tag::factory()->create();
    $this->template = Template::factory()->create();

    $this->article1 = Article::factory()->create();
    $this->article2 = Article::factory()->create();

    $this->article1->categories()->attach($this->category);
    $this->article1->tags()->attach($this->tag);
    $this->article1->templates()->attach($this->template);
    $this->article1->related()->attach($this->article2);

    $this->article2->categories()->attach($this->category);
});

it('backfills article counts successfully', function () {
    expect($this->article1->fresh()->categories_count)->toBe(0)
        ->and($this->article1->fresh()->tags_count)->toBe(0)
        ->and($this->article1->fresh()->templates_count)->toBe(0)
        ->and($this->article1->fresh()->related_articles_count)->toBe(0)
        ->and($this->article2->fresh()->categories_count)->toBe(0)
        ->and($this->article2->fresh()->tags_count)->toBe(0);

    $this->artisan('galactapedia:backfill-counts')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Successfully updated 2 articles.');

    expect($this->article1->fresh()->categories_count)->toBe(1)
        ->and($this->article1->fresh()->tags_count)->toBe(1)
        ->and($this->article1->fresh()->templates_count)->toBe(1)
        ->and($this->article1->fresh()->related_articles_count)->toBe(1)
        ->and($this->article2->fresh()->categories_count)->toBe(1)
        ->and($this->article2->fresh()->tags_count)->toBe(0);
});

it('runs in dry-run mode without modifying data', function () {
    $this->artisan('galactapedia:backfill-counts --dry-run')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Dry run completed for 2 articles.')
        ->doesntExpectOutput('Successfully updated');

    expect($this->article1->fresh()->categories_count)->toBe(0)
        ->and($this->article1->fresh()->tags_count)->toBe(0);
});

it('handles chunk size option', function () {
    $this->artisan('galactapedia:backfill-counts --chunk=1')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Processed 1 articles...')
        ->expectsOutput('Processed 2 articles...')
        ->expectsOutput('Successfully updated 2 articles.');

    expect($this->article1->fresh()->categories_count)->toBe(1)
        ->and($this->article1->fresh()->tags_count)->toBe(1);
});

it('handles empty database', function () {
    Article::query()->delete();

    $this->artisan('galactapedia:backfill-counts')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Successfully updated 0 articles.');
});
