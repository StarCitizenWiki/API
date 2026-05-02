<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\Item;
use App\Services\Game\SlugService;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->service = app(SlugService::class);
});

it('uses fallback when base name slugifies to placeholder', function (): void {
    // Names like '<= PLACEHOLDER ...' slugify to 'placeholder', not ''
    // The caller (updateSlug) detects this and passes 'item-{id}' instead
    expect(Str::slug('<= PLACEHOLDER ...'))->toBe('placeholder');

    $item = Item::factory()->create(['slug' => null]);

    // When updateSlug passes the corrected name, assignUniqueSlug uses it directly
    $slug = $this->service->assignUniqueSlug($item, "item-{$item->id}", "item-{$item->id}");

    expect($slug)->toBe("item-{$item->id}")
        ->and($item->fresh()->slug)->toBe("item-{$item->id}");
});

describe('assignUniqueSlug', function (): void {
    it('generates a slug from the base name', function (): void {
        $item = Item::factory()->create(['slug' => null]);

        $slug = $this->service->assignUniqueSlug($item, 'Super Cool Item', 'fallback-1');

        expect($slug)->toBe('super-cool-item')
            ->and($item->fresh()->slug)->toBe('super-cool-item');
    });

    it('uses fallback when base name produces empty slug', function (): void {
        $item = Item::factory()->create(['slug' => null]);

        $slug = $this->service->assignUniqueSlug($item, '!!!', "item-{$item->id}");

        expect($slug)->toBe("item-{$item->id}")
            ->and($item->fresh()->slug)->toBe("item-{$item->id}");
    });

    it('uses fallback when base name is empty string', function (): void {
        $item = Item::factory()->create(['slug' => null]);

        $slug = $this->service->assignUniqueSlug($item, '', "item-{$item->id}");

        expect($slug)->toBe("item-{$item->id}");
    });

    it('appends suffix when slug already exists in db', function (): void {
        Item::factory()->create(['slug' => 'existing-name']);
        $item = Item::factory()->create(['slug' => null]);

        $slug = $this->service->assignUniqueSlug($item, 'Existing Name', 'fallback-1');

        expect($slug)->toBe('existing-name-2')
            ->and($item->fresh()->slug)->toBe('existing-name-2');
    });

    it('increments suffix for multiple collisions', function (): void {
        Item::factory()->create(['slug' => 'taken']);
        Item::factory()->create(['slug' => 'taken-2']);
        Item::factory()->create(['slug' => 'taken-3']);
        $item = Item::factory()->create(['slug' => null]);

        $slug = $this->service->assignUniqueSlug($item, 'Taken', 'fallback-1');

        expect($slug)->toBe('taken-4');
    });

    it('preserves slug that model already owns', function (): void {
        $item = Item::factory()->create(['slug' => null]);

        $this->service->assignUniqueSlug($item, 'My Item', 'fallback-1');
        expect($item->fresh()->slug)->toBe('my-item');

        $sameItem = $item->fresh();
        $slug = $this->service->assignUniqueSlug($sameItem, 'My Item', 'fallback-1');

        expect($slug)->toBe('my-item');
    });

    it('works across different model types', function (): void {
        Item::factory()->create(['slug' => 'shared-slug']);
        $blueprint = Blueprint::factory()->create(['slug' => null]);

        $slug = $this->service->assignUniqueSlug($blueprint, 'Shared Slug', 'fallback-1');

        expect($slug)->toBe('shared-slug')
            ->and($blueprint->fresh()->slug)->toBe('shared-slug');
    });
});

describe('generateUniqueSlugForBatch', function (): void {
    it('returns the base slug when no collision', function (): void {
        $usedSlugs = [];

        $slug = $this->service->generateUniqueSlugForBatch('my-slug', $usedSlugs, Item::class);

        expect($slug)->toBe('my-slug')
            ->and($usedSlugs)->toContain('my-slug');
    });

    it('appends suffix when slug exists in local map', function (): void {
        $usedSlugs = ['my-slug'];

        $slug = $this->service->generateUniqueSlugForBatch('my-slug', $usedSlugs, Item::class);

        expect($slug)->toBe('my-slug-2')
            ->and($usedSlugs)->toContain('my-slug-2');
    });

    it('appends suffix when slug exists in database', function (): void {
        Item::factory()->create(['slug' => 'db-slug']);
        $usedSlugs = [];

        $slug = $this->service->generateUniqueSlugForBatch('db-slug', $usedSlugs, Item::class);

        expect($slug)->toBe('db-slug-2');
    });

    it('avoids both local map and database collisions', function (): void {
        Item::factory()->create(['slug' => 'dual-slug']);
        $usedSlugs = ['dual-slug-2'];

        $slug = $this->service->generateUniqueSlugForBatch('dual-slug', $usedSlugs, Item::class);

        expect($slug)->toBe('dual-slug-3');
    });

    it('tracks multiple generated slugs in local map', function (): void {
        $usedSlugs = [];

        $slug1 = $this->service->generateUniqueSlugForBatch('same-name', $usedSlugs, Item::class);
        $slug2 = $this->service->generateUniqueSlugForBatch('same-name', $usedSlugs, Item::class);
        $slug3 = $this->service->generateUniqueSlugForBatch('same-name', $usedSlugs, Item::class);

        expect($slug1)->toBe('same-name')
            ->and($slug2)->toBe('same-name-2')
            ->and($slug3)->toBe('same-name-3');
    });
});
