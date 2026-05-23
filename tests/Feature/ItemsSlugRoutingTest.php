<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Symfony\Component\DomCrawler\Crawler;

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

it('resolves item by class_name via web route', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Behring',
        'code' => 'BEHR',
    ]);

    $item = Item::factory()->create([
        'slug' => 'behr-laser-cannon-s4',
        'translation' => ['en' => 'Laser cannon description'],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'BEHR LaserCannon S4',
            'class_name' => 'BEHR_LaserCannon_S4',
            'classification' => 'Ship.Weapon',
            'type' => 'WeaponGun',
            'sub_type' => 'Laser',
            'size' => 4,
            'data' => [
                'stdItem' => [
                    'Mass' => 2500,
                ],
            ],
        ]);

    $response = $this->get(route('web.items.show', 'BEHR_LaserCannon_S4'));

    $response->assertOk()
        ->assertSeeText('BEHR LaserCannon S4')
        ->assertSeeText('Behring');
});

it('uses slug in canonical url when accessed via class_name', function (): void {
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
        'slug' => 'heavy-armor-arms',
        'translation' => ['en' => 'Armor description'],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Heavy Armor Arms',
            'class_name' => 'cds_armor_heavy_arms_01_02_01',
            'classification' => 'FPS.Armor.Arms',
            'type' => 'Char_Armor_Arms',
            'sub_type' => 'Heavy',
            'size' => 1,
            'data' => [
                'stdItem' => [
                    'Mass' => 8.0,
                ],
            ],
        ]);

    $response = $this->get(route('web.items.show', 'cds_armor_heavy_arms_01_02_01'));

    $response->assertOk();

    $crawler = new Crawler($response->getContent());

    $canonicalUrl = $crawler->filter('link[rel="canonical"]')->attr('href');

    expect($canonicalUrl)->toBe(route('web.items.show', ['item' => $item->slug]));
});

it('resolves item by class_name when no slug exists', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'KnightBridge Arms',
        'code' => 'KBA',
    ]);

    $item = Item::factory()->create([
        'slug' => null,
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'MGA Assault',
            'class_name' => 'MGA_Assault',
            'classification' => 'FPS.Weapon.Rifle',
            'type' => 'WeaponPersonal',
            'sub_type' => 'Rifle',
            'size' => 2,
            'data' => [
                'stdItem' => [
                    'Mass' => 3.5,
                ],
            ],
        ]);

    $response = $this->get(route('web.items.show', 'MGA_Assault'));

    $response->assertOk()
        ->assertSeeText('MGA Assault')
        ->assertSeeText('KnightBridge Arms');
});

it('returns 404 for unknown slug', function (): void {
    $response = $this->get(route('web.items.show', 'nonexistent-item-slug'));

    $response->assertNotFound();
});
