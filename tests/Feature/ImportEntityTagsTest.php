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

it('fails when tags.json contains invalid JSON', function (): void {
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

    $tags = [
        'uuid-tag-1' => 'Tag One',
        'uuid-tag-2' => 'Tag Two',
        'uuid-tag-3' => 'Tag Three',
    ];

    Storage::disk('scunpacked')->put('tags.json', json_encode($tags, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-tags')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 3 entity tags (3 new, 0 updated). Skipped 0 invalid.');

    expect(EntityTag::count())->toBe(3);

    $tag1 = EntityTag::query()->where('uuid', 'uuid-tag-1')->first();
    expect($tag1)->not->toBeNull();
    expect($tag1->name)->toBe('Tag One');

    $tag2 = EntityTag::query()->where('uuid', 'uuid-tag-2')->first();
    expect($tag2)->not->toBeNull();
    expect($tag2->name)->toBe('Tag Two');

    $tag3 = EntityTag::query()->where('uuid', 'uuid-tag-3')->first();
    expect($tag3)->not->toBeNull();
    expect($tag3->name)->toBe('Tag Three');
});

it('upserts existing tags and updates names', function (): void {
    Storage::fake('scunpacked');

    // Pre-create a tag with old name
    EntityTag::query()->create([
        'uuid' => 'uuid-tag-1',
        'name' => 'Old Name',
    ]);

    $tags = [
        'uuid-tag-1' => 'Updated Name',
        'uuid-tag-2' => 'New Tag',
    ];

    Storage::disk('scunpacked')->put('tags.json', json_encode($tags, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-tags')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 2 entity tags (1 new, 1 updated). Skipped 0 invalid.');

    expect(EntityTag::count())->toBe(2);

    $tag1 = EntityTag::query()->where('uuid', 'uuid-tag-1')->first();
    expect($tag1->name)->toBe('Updated Name');

    $tag2 = EntityTag::query()->where('uuid', 'uuid-tag-2')->first();
    expect($tag2->name)->toBe('New Tag');
});

it('is idempotent and safe to run multiple times', function (): void {
    Storage::fake('scunpacked');

    $tags = [
        'uuid-tag-1' => 'Tag One',
        'uuid-tag-2' => 'Tag Two',
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

    $tags = [
        'uuid-tag-1' => 'Valid Tag',
        '' => 'Empty UUID',
        'uuid-tag-2' => '',
        'uuid-tag-3' => 'Another Valid Tag',
    ];

    Storage::disk('scunpacked')->put('tags.json', json_encode($tags, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-tags')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 2 entity tags (2 new, 0 updated). Skipped 2 invalid.');

    expect(EntityTag::count())->toBe(2);
    expect(EntityTag::query()->where('uuid', 'uuid-tag-1')->exists())->toBeTrue();
    expect(EntityTag::query()->where('uuid', 'uuid-tag-3')->exists())->toBeTrue();
});

it('trims whitespace from uuid and name', function (): void {
    Storage::fake('scunpacked');

    $tags = [
        '  uuid-tag-1  ' => '  Tag With Spaces  ',
    ];

    Storage::disk('scunpacked')->put('tags.json', json_encode($tags, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-tags')
        ->assertExitCode(Command::SUCCESS);

    $tag = EntityTag::query()->where('uuid', 'uuid-tag-1')->first();
    expect($tag)->not->toBeNull();
    expect($tag->name)->toBe('Tag With Spaces');
});

it('accepts custom path option', function (): void {
    Storage::fake('scunpacked');

    $tags = ['uuid-custom' => 'Custom Tag'];

    Storage::disk('scunpacked')->put('custom-tags.json', json_encode($tags, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-tags', ['--path' => 'custom-tags.json'])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 1 entity tags (1 new, 0 updated). Skipped 0 invalid.');

    expect(EntityTag::query()->where('uuid', 'uuid-custom')->exists())->toBeTrue();
});

it('handles large datasets with batching (>10,000 tags)', function (): void {
    Storage::fake('scunpacked');

    // Generate 15,000 tags to test batching (should process in 2 batches)
    $tags = [];
    for ($i = 1; $i <= 15000; $i++) {
        $tags["uuid-tag-{$i}"] = "Tag {$i}";
    }

    Storage::disk('scunpacked')->put('large-tags.json', json_encode($tags, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-tags', ['--path' => 'large-tags.json'])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 15000 entity tags (15000 new, 0 updated). Skipped 0 invalid.');

    expect(EntityTag::count())->toBe(15000);
    expect(EntityTag::query()->where('uuid', 'uuid-tag-1')->exists())->toBeTrue();
    expect(EntityTag::query()->where('uuid', 'uuid-tag-10000')->exists())->toBeTrue();
    expect(EntityTag::query()->where('uuid', 'uuid-tag-15000')->exists())->toBeTrue();
});

it('handles batching with mixed new and existing tags', function (): void {
    Storage::fake('scunpacked');

    // Pre-create 5,000 tags
    for ($i = 1; $i <= 5000; $i++) {
        EntityTag::query()->create([
            'uuid' => "uuid-existing-{$i}",
            'name' => "Old Name {$i}",
        ]);
    }

    // Generate 12,000 tags: 5,000 existing (to update) + 7,000 new
    $tags = [];
    for ($i = 1; $i <= 5000; $i++) {
        $tags["uuid-existing-{$i}"] = "Updated Name {$i}";
    }
    for ($i = 1; $i <= 7000; $i++) {
        $tags["uuid-new-{$i}"] = "New Tag {$i}";
    }

    Storage::disk('scunpacked')->put('mixed-tags.json', json_encode($tags, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-tags', ['--path' => 'mixed-tags.json'])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 12000 entity tags (7000 new, 5000 updated). Skipped 0 invalid.');

    expect(EntityTag::count())->toBe(12000);

    // Verify an existing tag was updated
    $updated = EntityTag::query()->where('uuid', 'uuid-existing-1')->first();
    expect($updated->name)->toBe('Updated Name 1');

    // Verify a new tag was created
    expect(EntityTag::query()->where('uuid', 'uuid-new-1')->exists())->toBeTrue();
});
