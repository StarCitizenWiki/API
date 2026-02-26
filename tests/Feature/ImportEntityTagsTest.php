<?php

declare(strict_types=1);

use App\Models\Game\EntityTag;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('fails when tags.json is missing', function (): void {
    Storage::fake('scunpacked');

    $this->artisan('game:import-tags')
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('tags.json not found in scunpacked storage.');
});

it('fails when tags.json contains invalid json', function (): void {
    Storage::fake('scunpacked');

    Storage::disk('scunpacked')->put('tags.json', 'invalid json {');

    $this->artisan('game:import-tags')
        ->assertExitCode(Command::FAILURE)
        ->expectsOutputToContain('Failed to decode tags.json');
});

it('warns when tags.json is empty', function (): void {
    Storage::fake('scunpacked');

    Storage::disk('scunpacked')->put('tags.json', json_encode([], JSON_THROW_ON_ERROR));

    $this->artisan('game:import-tags')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('No tags found in file.');

    expect(EntityTag::count())->toBe(0);
});

it('imports all tags from tags.json', function (): void {
    Storage::fake('scunpacked');

    $tag1 = fake()->uuid();
    $tag2 = fake()->uuid();
    $tag3 = fake()->uuid();

    $tags = [
        $tag1 => 'Tag One',
        $tag2 => 'Tag Two',
        $tag3 => 'Tag Three',
    ];

    Storage::disk('scunpacked')->put('tags.json', json_encode($tags, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-tags')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 3 entity tags (3 new, 0 updated). Skipped 0 invalid.');

    expect(EntityTag::count())->toBe(3);

    $tag1 = EntityTag::query()->where('uuid', $tag1)->first();
    expect($tag1)->not->toBeNull();
    expect($tag1->name)->toBe('Tag One');

    $tag2 = EntityTag::query()->where('uuid', $tag2)->first();
    expect($tag2)->not->toBeNull();
    expect($tag2->name)->toBe('Tag Two');

    $tag3 = EntityTag::query()->where('uuid', $tag3)->first();
    expect($tag3)->not->toBeNull();
    expect($tag3->name)->toBe('Tag Three');
});

it('upserts existing tags and updates names', function (): void {
    Storage::fake('scunpacked');

    $tag1 = fake()->uuid();
    $tag2 = fake()->uuid();

    // Pre-create a tag with old name
    EntityTag::query()->create([
        'uuid' => $tag1,
        'name' => 'Old Name',
    ]);

    $tags = [
        $tag1 => 'Updated Name',
        $tag2 => 'New Tag',
    ];

    Storage::disk('scunpacked')->put('tags.json', json_encode($tags, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-tags')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 2 entity tags (1 new, 1 updated). Skipped 0 invalid.');

    expect(EntityTag::count())->toBe(2);

    $tag1 = EntityTag::query()->where('uuid', $tag1)->first();
    expect($tag1->name)->toBe('Updated Name');

    $tag2 = EntityTag::query()->where('uuid', $tag2)->first();
    expect($tag2->name)->toBe('New Tag');
});

it('is idempotent and safe to run multiple times', function (): void {
    Storage::fake('scunpacked');

    $tag1 = fake()->uuid();
    $tag2 = fake()->uuid();

    $tags = [
        $tag1 => 'Tag One',
        $tag2 => 'Tag Two',
    ];

    Storage::disk('scunpacked')->put('tags.json', json_encode($tags, JSON_THROW_ON_ERROR));

    // First run
    $this->artisan('game:import-tags')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 2 entity tags (2 new, 0 updated). Skipped 0 invalid.');

    expect(EntityTag::count())->toBe(2);

    // Second run - all should be updates
    $this->artisan('game:import-tags')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 2 entity tags (0 new, 2 updated). Skipped 0 invalid.');

    expect(EntityTag::count())->toBe(2);
});

it('skips invalid tag entries', function (): void {
    Storage::fake('scunpacked');

    $tag1 = fake()->uuid();
    $tag2 = fake()->uuid();
    $tag3 = fake()->uuid();

    $tags = [
        $tag1 => 'Valid Tag',
        '' => 'Empty UUID',
        $tag2 => '',
        $tag3 => 'Another Valid Tag',
    ];

    Storage::disk('scunpacked')->put('tags.json', json_encode($tags, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-tags')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 2 entity tags (2 new, 0 updated). Skipped 2 invalid.');

    expect(EntityTag::count())->toBe(2)
        ->and(EntityTag::query()->where('uuid', $tag1)->exists())->toBeTrue()
        ->and(EntityTag::query()->where('uuid', $tag3)->exists())->toBeTrue();
});

it('trims whitespace from uuid and name', function (): void {
    Storage::fake('scunpacked');

    $tag1 = fake()->uuid();

    $tags = [
        "  $tag1  " => '  Tag With Spaces  ',
    ];

    Storage::disk('scunpacked')->put('tags.json', json_encode($tags, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-tags')
        ->assertExitCode(Command::SUCCESS);

    $tag = EntityTag::query()->where('uuid', $tag1)->first();
    expect($tag)->not->toBeNull();
    expect($tag->name)->toBe('Tag With Spaces');
});

it('accepts custom path option', function (): void {
    Storage::fake('scunpacked');

    $tag1 = fake()->uuid();

    $tags = [$tag1 => 'Custom Tag'];

    Storage::disk('scunpacked')->put('custom-tags.json', json_encode($tags, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-tags', ['--path' => 'custom-tags.json'])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 1 entity tags (1 new, 0 updated). Skipped 0 invalid.');

    expect(EntityTag::query()->where('uuid', $tag1)->exists())->toBeTrue();
});

it('handles large datasets with batching (>10,000 tags)', function (): void {
    Storage::fake('scunpacked');

    $tag1 = fake()->uuid();
    // Generate 15,000 tags to test batching (should process in 2 batches)
    $tags = [
        $tag1 => 'Tag One',
    ];
    for ($i = 1; $i < 15000; $i++) {
        $tags[fake()->uuid()] = "Tag {$i}";
    }

    Storage::disk('scunpacked')->put('large-tags.json', json_encode($tags, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-tags', ['--path' => 'large-tags.json'])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 15000 entity tags (15000 new, 0 updated). Skipped 0 invalid.');

    expect(EntityTag::count())->toBe(15000)
        ->and(EntityTag::query()->where('uuid', $tag1)->exists())->toBeTrue();
});
