<?php

declare(strict_types=1);

use App\Models\Game\EntityTag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

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

it('links parent tags even when a child precedes its parent in the file', function (): void {
    Storage::fake('scunpacked');

    $grandchild = fake()->uuid();
    $child = fake()->uuid();
    $parent = fake()->uuid();

    // Forward-ordered chain: each row references a parent declared later in the
    // file, mirroring the real scunpacked data that broke single-pass import.
    $tags = [
        $grandchild => ['name' => 'Grandchild', 'parent_uuid' => $child],
        $child => ['name' => 'Child', 'parent_uuid' => $parent],
        $parent => ['name' => 'Parent', 'parent_uuid' => null],
    ];

    Storage::disk('scunpacked')->put('tags.json', json_encode($tags, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-tags')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 3 entity tags (3 new, 0 updated). Skipped 0 invalid.');

    expect(EntityTag::count())->toBe(3)
        ->and(EntityTag::where('uuid', $grandchild)->value('parent_uuid'))->toBe($child)
        ->and(EntityTag::where('uuid', $child)->value('parent_uuid'))->toBe($parent)
        ->and(EntityTag::where('uuid', $parent)->value('parent_uuid'))->toBeNull();
});

it('leaves parent_uuid null when the referenced parent is not in the file', function (): void {
    Storage::fake('scunpacked');

    $child = fake()->uuid();
    $orphan = fake()->uuid();

    $tags = [
        $child => ['name' => 'Child Tag', 'parent_uuid' => $orphan],
    ];

    Storage::disk('scunpacked')->put('tags.json', json_encode($tags, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-tags')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 1 entity tags (1 new, 0 updated). Skipped 0 invalid.');

    $childTag = EntityTag::query()->where('uuid', $child)->first();
    expect($childTag)->not->toBeNull()
        ->and($childTag->parent_uuid)->toBeNull();
});

it('imports large datasets across the 10,000-row boundary', function (): void {
    Storage::fake('scunpacked');

    $tags = [];
    for ($i = 1; $i <= 10001; $i++) {
        $tags[sprintf('00000000-0000-0000-0000-%012d', $i)] = sprintf('Tag %d', $i);
    }

    Storage::disk('scunpacked')->put('large-tags.json', json_encode($tags, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-tags', ['--path' => 'large-tags.json'])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 10001 entity tags (10001 new, 0 updated). Skipped 0 invalid.');

    expect(EntityTag::count())->toBe(10001)
        ->and(EntityTag::query()->where('uuid', '00000000-0000-0000-0000-000000000001')->exists())->toBeTrue()
        ->and(EntityTag::query()->where('uuid', '00000000-0000-0000-0000-000000005000')->exists())->toBeTrue()
        ->and(EntityTag::query()->where('uuid', '00000000-0000-0000-0000-000000010001')->exists())->toBeTrue();
});
