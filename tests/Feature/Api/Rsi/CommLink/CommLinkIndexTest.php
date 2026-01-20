<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\Category;
use App\Models\Rsi\CommLink\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Link;
use App\Models\Rsi\CommLink\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sorts by images and links count and filters by publication date', function (): void {
    $category = Category::factory()->create();
    $channel = Channel::factory()->create();
    $series = Series::factory()->create();

    $baseAttributes = [
        'category_id' => $category->id,
        'channel_id' => $channel->id,
        'series_id' => $series->id,
    ];

    $commLink2024May = CommLink::factory()->create($baseAttributes + [
        'created_at' => '2024-05-01 12:00:00',
    ]);

    $commLink2024MayTwo = CommLink::factory()->create($baseAttributes + [
        'created_at' => '2024-05-02 12:00:00',
    ]);

    $commLink2024June = CommLink::factory()->create($baseAttributes + [
        'created_at' => '2024-06-01 12:00:00',
    ]);

    $commLink2023 = CommLink::factory()->create($baseAttributes + [
        'created_at' => '2023-06-01 12:00:00',
    ]);

    $images = Image::factory()->count(2)->create();
    $commLink2024May->images()->attach($images->pluck('id'));
    $commLink2024May->update(['images_count' => 2]);

    $linkOne = Link::factory()->create();
    $linkTwo = Link::factory()->create();

    $commLink2023->links()->attach([$linkOne->id]);
    $commLink2023->update(['links_count' => 1]);

    $commLink2024May->links()->attach([$linkOne->id, $linkTwo->id]);
    $commLink2024May->update(['links_count' => 2]);

    $this->getJson(route('comm-links.index', ['sort' => '-images_count']))
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $commLink2024May->cig_id);

    $this->getJson(route('comm-links.index', ['sort' => '-links_count']))
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $commLink2024May->cig_id);

    $yearResponse = $this->getJson(route('comm-links.index', [
        'filter' => [
            'created_at' => '2024',
        ],
    ]));

    $yearResponse->assertSuccessful();

    $yearIds = collect($yearResponse->json('data'))->pluck('id');

    expect($yearIds)->toContain($commLink2024May->cig_id)
        ->toContain($commLink2024June->cig_id)
        ->not->toContain($commLink2023->cig_id);

    $monthResponse = $this->getJson(route('comm-links.index', [
        'filter' => [
            'created_at' => '2024-05',
        ],
    ]));

    $monthResponse->assertSuccessful();

    $monthIds = collect($monthResponse->json('data'))->pluck('id');

    expect($monthIds)->toContain($commLink2024May->cig_id)
        ->toContain($commLink2024MayTwo->cig_id)
        ->not->toContain($commLink2024June->cig_id);

    $dateResponse = $this->getJson(route('comm-links.index', [
        'filter' => [
            'created_at' => '2024-05-01',
        ],
    ]));

    $dateResponse->assertSuccessful();

    $dateIds = collect($dateResponse->json('data'))->pluck('id');

    expect($dateIds)->toContain($commLink2024May->cig_id)
        ->not->toContain($commLink2024MayTwo->cig_id)
        ->not->toContain($commLink2024June->cig_id);
});
