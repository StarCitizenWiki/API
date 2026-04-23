<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\DomCrawler\Crawler;

uses(RefreshDatabase::class);

it('resolves item by slug via web route', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Acme Works',
        'code' => 'ACME',
    ]);

    $item = Item::factory()->create([
        'slug' => 'test-module',
        'translation' => ['en' => 'Test item description'],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Test Module',
            'class_name' => 'test_module',
            'classification' => 'Test.Module',
            'type' => 'PowerPlant',
            'sub_type' => 'Small',
            'size' => 2,
            'data' => [
                'stdItem' => [
                    'Mass' => 12.5,
                ],
            ],
        ]);

    $response = $this->get(route('web.items.show', $item->slug));

    $response->assertOk()
        ->assertSeeText('Test Module')
        ->assertSeeText('Acme Works');
});

it('resolves item by uuid via web route (backwards compatibility)', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Acme Works',
        'code' => 'ACME',
    ]);

    $item = Item::factory()->create([
        'slug' => 'compat-module',
        'translation' => ['en' => 'Compat item description'],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Compat Module',
            'class_name' => 'compat_module',
            'classification' => 'Test.Module',
            'type' => 'PowerPlant',
            'sub_type' => 'Small',
            'size' => 1,
            'data' => [
                'stdItem' => [
                    'Mass' => 5.0,
                ],
            ],
        ]);

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk()
        ->assertSeeText('Compat Module')
        ->assertSeeText('Acme Works');
});

it('uses slug in canonical url when slug is present', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Acme Works',
        'code' => 'ACME',
    ]);

    $item = Item::factory()->create([
        'slug' => 'canonical-module',
        'translation' => ['en' => 'Canonical item description'],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Canonical Module',
            'class_name' => 'canonical_module',
            'classification' => 'Test.Module',
            'type' => 'PowerPlant',
            'sub_type' => 'Small',
            'size' => 1,
            'data' => [
                'stdItem' => [
                    'Mass' => 5.0,
                ],
            ],
        ]);

    $response = $this->get(route('web.items.show', $item->slug));

    $response->assertOk();

    $crawler = new Crawler($response->getContent());

    $canonicalUrl = $crawler->filter('link[rel="canonical"]')->attr('href');

    expect($canonicalUrl)->toBe(route('web.items.show', ['item' => $item->slug]));
});

it('returns 404 for unknown slug', function (): void {
    $response = $this->get(route('web.items.show', 'nonexistent-item-slug'));

    $response->assertNotFound();
});
