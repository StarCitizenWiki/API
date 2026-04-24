<?php

declare(strict_types=1);

use App\Jobs\Game\EnrichImages;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

it('stores image on item from primary source', function (): void {
    Log::spy();

    $item = Item::factory()->create();
    $version = GameVersion::factory()->create(['is_default' => true]);
    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'name' => 'Gladius',
    ]);

    Http::fake([
        'starcitizen.tools/*' => Http::response([
            'query' => [
                'pages' => [
                    123 => [
                        'pageid' => 123,
                        'title' => 'Gladius',
                        'pageimage' => 'Gladius_-_Flying_away.jpg',
                        'thumbnail' => [
                            'source' => 'https://media.starcitizen.tools/thumb/gladius.jpg',
                            'width' => 600,
                            'height' => 360,
                        ],
                        'original' => [
                            'source' => 'https://media.starcitizen.tools/gladius.jpg',
                            'width' => 3840,
                            'height' => 2304,
                        ],
                    ],
                ],
            ],
        ]),
        'star-citizen.wiki/*' => Http::response(['query' => ['pages' => []]]),
    ]);

    $job = new EnrichImages(Item::class, [$item->id => 'Gladius'], [$item->id => $item->uuid]);
    $job->handle();

    $item->refresh();

    expect($item->images)->toBeArray()
        ->and($item->images)->toHaveCount(1)
        ->and($item->images[0])->toMatchArray([
            'source' => 'starcitizen.tools',
            'thumbnail_url' => 'https://media.starcitizen.tools/thumb/gladius.jpg',
            'thumbnail_width' => 600,
            'thumbnail_height' => 360,
            'original_url' => 'https://media.starcitizen.tools/gladius.jpg',
            'original_width' => 3840,
            'original_height' => 2304,
        ]);
});

it('stores image on vehicle from primary source', function (): void {
    Log::spy();

    $vehicle = Vehicle::factory()->create();
    $version = GameVersion::factory()->create(['is_default' => true]);
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'display_name' => 'Avenger Titan',
    ]);

    Http::fake([
        'starcitizen.tools/*' => Http::response([
            'query' => [
                'pages' => [
                    456 => [
                        'pageid' => 456,
                        'title' => 'Avenger Titan',
                        'pageimage' => 'Avenger_Titan.jpg',
                        'thumbnail' => [
                            'source' => 'https://media.starcitizen.tools/thumb/avenger.jpg',
                            'width' => 600,
                            'height' => 400,
                        ],
                        'original' => [
                            'source' => 'https://media.starcitizen.tools/avenger.jpg',
                            'width' => 1920,
                            'height' => 1080,
                        ],
                    ],
                ],
            ],
        ]),
        'star-citizen.wiki/*' => Http::response(['query' => ['pages' => []]]),
    ]);

    $job = new EnrichImages(Vehicle::class, [$vehicle->id => 'Avenger Titan'], [$vehicle->id => $vehicle->uuid]);
    $job->handle();

    $vehicle->refresh();

    expect($vehicle->images)->toBeArray()
        ->and($vehicle->images)->toHaveCount(1)
        ->and($vehicle->images[0]['source'])->toBe('starcitizen.tools');
});

it('skips placeholder images from primary source', function (): void {
    Log::spy();

    $item = Item::factory()->create();
    $version = GameVersion::factory()->create(['is_default' => true]);
    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'name' => 'Adiva Jacket White',
    ]);

    Http::fake([
        'starcitizen.tools/*' => Http::response([
            'query' => [
                'pages' => [
                    100 => [
                        'pageid' => 100,
                        'title' => 'Adiva Jacket White',
                        'pageimage' => 'Placeholderv2.png',
                        'thumbnail' => [
                            'source' => 'https://media.starcitizen.tools/thumb/placeholder.png',
                            'width' => 600,
                            'height' => 600,
                        ],
                    ],
                ],
            ],
        ]),
        'star-citizen.wiki/*' => Http::response([
            'query' => [
                'pages' => [
                    200 => [
                        'pageid' => 200,
                        'title' => 'Adiva Jacket White',
                        'thumbnail' => [
                            'source' => 'https://cdn.star-citizen.wiki/thumb/adiva_white.jpg',
                            'width' => 381,
                            'height' => 381,
                        ],
                        'original' => [
                            'source' => 'https://cdn.star-citizen.wiki/adiva_white.jpg',
                            'width' => 800,
                            'height' => 800,
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $job = new EnrichImages(Item::class, [$item->id => 'Adiva Jacket White'], [$item->id => $item->uuid]);
    $job->handle();

    $item->refresh();

    expect($item->images)->toBeArray()
        ->and($item->images)->toHaveCount(1)
        ->and($item->images[0]['source'])->toBe('star-citizen.wiki');
});

it('falls back to secondary source when primary has no image', function (): void {
    Log::spy();

    $item = Item::factory()->create();
    $version = GameVersion::factory()->create(['is_default' => true]);
    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'name' => 'Test Item',
    ]);

    Http::fake([
        'starcitizen.tools/*' => Http::response([
            'query' => [
                'pages' => [
                    10 => [
                        'pageid' => 10,
                        'title' => 'Test Item',
                        'missing' => '',
                    ],
                ],
            ],
        ]),
        'star-citizen.wiki/*' => Http::response([
            'query' => [
                'pages' => [
                    20 => [
                        'pageid' => 20,
                        'title' => 'Test Item',
                        'pageimage' => 'Test_Item.jpg',
                        'thumbnail' => [
                            'source' => 'https://cdn.star-citizen.wiki/thumb/test.jpg',
                            'width' => 600,
                            'height' => 400,
                        ],
                        'original' => [
                            'source' => 'https://cdn.star-citizen.wiki/test.jpg',
                            'width' => 1920,
                            'height' => 1080,
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $job = new EnrichImages(Item::class, [$item->id => 'Test Item'], [$item->id => $item->uuid]);
    $job->handle();

    $item->refresh();

    expect($item->images)->toBeArray()
        ->and($item->images)->toHaveCount(1)
        ->and($item->images[0]['source'])->toBe('star-citizen.wiki');

    Http::assertSentCount(2);
});

it('falls back to direct source when wiki sources have no image', function (): void {
    Log::spy();

    $item = Item::factory()->create();
    $version = GameVersion::factory()->create(['is_default' => true]);
    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'name' => 'Obscure Item',
    ]);

    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAFklEQVQYV2P8z8BQz0BFwMgwqpBiNwDzFX5mBwAADsgEfVLeYQAAAABJRU5ErkJggg==');

    Http::fake([
        'starcitizen.tools/*' => Http::response([
            'query' => [
                'pages' => [
                    10 => [
                        'pageid' => 10,
                        'title' => 'Obscure Item',
                        'missing' => '',
                    ],
                ],
            ],
        ]),
        'star-citizen.wiki/*' => Http::response([
            'query' => [
                'pages' => [
                    20 => [
                        'pageid' => 20,
                        'title' => 'Obscure Item',
                        'missing' => '',
                    ],
                ],
            ],
        ]),
        "cstone.space/uifimages/{$item->uuid}.png" => Http::response($png, 200, ['Content-Type' => 'image/png']),
    ]);

    $job = new EnrichImages(Item::class, [$item->id => 'Obscure Item'], [$item->id => $item->uuid]);
    $job->handle();

    $item->refresh();

    expect($item->images)->toBeArray()
        ->and($item->images)->toHaveCount(1)
        ->and($item->images[0])->toMatchArray([
            'source' => 'cstone.space',
            'thumbnail_url' => "https://cstone.space/uifimages/{$item->uuid}.png",
            'thumbnail_width' => 10,
            'thumbnail_height' => 10,
            'original_url' => "https://cstone.space/uifimages/{$item->uuid}.png",
            'original_width' => 10,
            'original_height' => 10,
        ]);
});

it('sets empty array when all sources fail', function (): void {
    Log::spy();

    $item = Item::factory()->create();
    $version = GameVersion::factory()->create(['is_default' => true]);
    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'name' => 'Unknown Item',
    ]);

    Http::fake([
        'starcitizen.tools/*' => Http::response([
            'query' => [
                'pages' => [
                    10 => [
                        'pageid' => 10,
                        'title' => 'Unknown Item',
                        'missing' => '',
                    ],
                ],
            ],
        ]),
        'star-citizen.wiki/*' => Http::response([
            'query' => [
                'pages' => [
                    10 => [
                        'pageid' => 10,
                        'title' => 'Unknown Item',
                        'missing' => '',
                    ],
                ],
            ],
        ]),
        "cstone.space/uifimages/{$item->uuid}.png" => Http::response(status: 404),
    ]);

    $job = new EnrichImages(Item::class, [$item->id => 'Unknown Item'], [$item->id => $item->uuid]);
    $job->handle();

    $item->refresh();

    expect($item->images)->toBe([]);
});

it('chunks 50+ titles into multiple API calls', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true]);

    $idNameMap = [];
    for ($i = 1; $i <= 55; $i++) {
        $item = Item::factory()->create();
        ItemData::factory()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'name' => "Item {$i}",
        ]);
        $idNameMap[$item->id] = "Item {$i}";
    }

    $toolsCalls = 0;
    $wikiCalls = 0;

    Http::fake(function ($request) use (&$toolsCalls, &$wikiCalls) {
        $url = $request->url();

        if (str_contains($url, 'starcitizen.tools')) {
            $toolsCalls++;

            return Http::response(['query' => ['pages' => []]]);
        }

        if (str_contains($url, 'star-citizen.wiki')) {
            $wikiCalls++;

            return Http::response(['query' => ['pages' => []]]);
        }

        return Http::response(status: 404);
    });

    $job = new EnrichImages(Item::class, $idNameMap);
    $job->handle();

    expect($toolsCalls)->toBe(2); // ceil(55/50) = 2
    expect($wikiCalls)->toBe(2); // same for fallback
});
