<?php

declare(strict_types=1);

use Symfony\Component\DomCrawler\Crawler;

use function Tests\Support\createItemForShow;

it('resolves item by slug via web route', function (): void {
    [$item] = createItemForShow(
        itemOverrides: [
            'slug' => 'test-module',
            'translation' => ['en' => 'Test item description'],
        ],
        itemDataOverrides: [
            'name' => 'Test Module',
            'class_name' => 'test_module',
            'classification' => 'Test.Module',
            'type' => 'PowerPlant',
            'sub_type' => 'Small',
            'size' => 2,
            'data' => ['stdItem' => ['Mass' => 12.5]],
        ],
    );

    $response = $this->get(route('web.items.show', $item->slug));

    $response->assertOk()
        ->assertSeeText('Test Module')
        ->assertSeeText('Acme Works');
});

it('resolves item by uuid via web route (backwards compatibility)', function (): void {
    [$item] = createItemForShow(
        itemOverrides: [
            'slug' => 'compat-module',
            'translation' => ['en' => 'Compat item description'],
        ],
        itemDataOverrides: [
            'name' => 'Compat Module',
            'class_name' => 'compat_module',
            'classification' => 'Test.Module',
            'type' => 'PowerPlant',
            'sub_type' => 'Small',
            'size' => 1,
            'data' => ['stdItem' => ['Mass' => 5.0]],
        ],
    );

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk()
        ->assertSeeText('Compat Module')
        ->assertSeeText('Acme Works');
});

it('uses slug in canonical url when slug is present', function (): void {
    [$item] = createItemForShow(
        itemOverrides: [
            'slug' => 'canonical-module',
            'translation' => ['en' => 'Canonical item description'],
        ],
        itemDataOverrides: [
            'name' => 'Canonical Module',
            'class_name' => 'canonical_module',
            'classification' => 'Test.Module',
            'type' => 'PowerPlant',
            'sub_type' => 'Small',
            'size' => 1,
            'data' => ['stdItem' => ['Mass' => 5.0]],
        ],
    );

    $response = $this->get(route('web.items.show', $item->slug));

    $response->assertOk();

    $crawler = new Crawler($response->getContent());

    $canonicalUrl = $crawler->filter('link[rel="canonical"]')->attr('href');

    expect($canonicalUrl)->toBe(route('web.items.show', ['item' => $item->slug]));
});

it('resolves item by class_name via web route', function (): void {
    createItemForShow(
        itemOverrides: [
            'slug' => 'behr-laser-cannon-s4',
            'translation' => ['en' => 'Laser cannon description'],
        ],
        itemDataOverrides: [
            'name' => 'BEHR LaserCannon S4',
            'class_name' => 'BEHR_LaserCannon_S4',
            'classification' => 'Ship.Weapon',
            'type' => 'WeaponGun',
            'sub_type' => 'Laser',
            'size' => 4,
            'data' => ['stdItem' => ['Mass' => 2500]],
        ],
        manufacturerAttrs: ['name' => 'Behring', 'code' => 'BEHR'],
    );

    $response = $this->get(route('web.items.show', 'BEHR_LaserCannon_S4'));

    $response->assertOk()
        ->assertSeeText('BEHR LaserCannon S4')
        ->assertSeeText('Behring');
});

it('uses slug in canonical url when accessed via class_name', function (): void {
    [$item] = createItemForShow(
        itemOverrides: [
            'slug' => 'heavy-armor-arms',
            'translation' => ['en' => 'Armor description'],
        ],
        itemDataOverrides: [
            'name' => 'Heavy Armor Arms',
            'class_name' => 'cds_armor_heavy_arms_01_02_01',
            'classification' => 'FPS.Armor.Arms',
            'type' => 'Char_Armor_Arms',
            'sub_type' => 'Heavy',
            'size' => 1,
            'data' => ['stdItem' => ['Mass' => 8.0]],
        ],
    );

    $response = $this->get(route('web.items.show', 'cds_armor_heavy_arms_01_02_01'));

    $response->assertOk();

    $crawler = new Crawler($response->getContent());

    $canonicalUrl = $crawler->filter('link[rel="canonical"]')->attr('href');

    expect($canonicalUrl)->toBe(route('web.items.show', ['item' => $item->slug]));
});

it('resolves item by class_name when no slug exists', function (): void {
    createItemForShow(
        itemOverrides: ['slug' => null],
        itemDataOverrides: [
            'name' => 'MGA Assault',
            'class_name' => 'MGA_Assault',
            'classification' => 'FPS.Weapon.Rifle',
            'type' => 'WeaponPersonal',
            'sub_type' => 'Rifle',
            'size' => 2,
            'data' => ['stdItem' => ['Mass' => 3.5]],
        ],
        manufacturerAttrs: ['name' => 'KnightBridge Arms', 'code' => 'KBA'],
    );

    $response = $this->get(route('web.items.show', 'MGA_Assault'));

    $response->assertOk()
        ->assertSeeText('MGA Assault')
        ->assertSeeText('KnightBridge Arms');
});

it('returns 404 for unknown slug', function (): void {
    $response = $this->get(route('web.items.show', 'nonexistent-item-slug'));

    $response->assertNotFound();
});
