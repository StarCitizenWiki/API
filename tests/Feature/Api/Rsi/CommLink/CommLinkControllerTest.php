<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Rsi\CommLink\Category;
use App\Models\Rsi\CommLink\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Link;
use App\Models\Rsi\CommLink\Series;
use App\Models\System\Language;
use Illuminate\Support\Facades\Cache;

describe('index', function (): void {
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

    it('filters comm-links by article content', function (): void {
        $category = Category::factory()->create();
        $channel = Channel::factory()->create([
            'name' => 'News',
            'slug' => 'news',
        ]);
        $series = Series::factory()->create();

        $attributes = [
            'category_id' => $category->id,
            'channel_id' => $channel->id,
            'series_id' => $series->id,
        ];

        $matchingCommLink = CommLink::factory()->create($attributes + [
            'title' => 'Quantum Systems Update',
        ]);
        $matchingCommLink->setTranslation('translation', Language::ENGLISH, 'The latest quantum jump drive calibration guide.');
        $matchingCommLink->save();

        $nonMatchingCommLink = CommLink::factory()->create($attributes + [
            'title' => 'Cargo and Trade Update',
        ]);
        $nonMatchingCommLink->setTranslation('translation', Language::ENGLISH, 'Cargo manifests and trade lane updates.');
        $nonMatchingCommLink->save();

        $response = $this->getJson(route('comm-links.index', [
            'filter' => [
                'content' => 'quantum jump drive',
            ],
        ]));

        $response->assertSuccessful();

        $ids = collect($response->json('data'))->pluck('id');

        expect($ids)->toContain($matchingCommLink->cig_id)
            ->not->toContain($nonMatchingCommLink->cig_id);
    });
});

describe('show', function (): void {
    it('returns the comm-link show response contract', function (): void {
        $channel = Channel::factory()->create([
            'name' => 'News',
            'slug' => 'news',
        ]);

        $category = Category::factory()->create([
            'name' => 'Update',
            'slug' => 'update',
        ]);

        $series = Series::factory()->create([
            'name' => 'Inside Star Citizen',
            'slug' => 'inside-star-citizen',
        ]);

        $commLink = CommLink::factory()->create([
            'cig_id' => 17648,
            'channel_id' => $channel->id,
            'category_id' => $category->id,
            'series_id' => $series->id,
            'comment_count' => 17,
            'images_count' => 12,
            'links_count' => 0,
        ]);

        $images = Image::factory()->count(12)->create();

        $commLink->images()->attach($images->pluck('id')->all());

        $response = $this->getJson(route('comm-links.show', ['id' => $commLink->cig_id]));

        $response->assertSuccessful()
            ->assertJsonPath('data.id', $commLink->cig_id)
            ->assertJsonPath('data.api_url', route('comm-links.show', ['id' => $commLink->cig_id]))
            ->assertJsonPath('data.api_public_url', route('web.comm-links.show', $commLink->cig_id))
            ->assertJsonPath('data.channel', $channel->name)
            ->assertJsonPath('data.category', $category->name)
            ->assertJsonPath('data.series', $series->name)
            ->assertJsonPath('data.links_count', 0)
            ->assertJsonPath('data.comment_count', 17)
            ->assertJsonPath('data.created_at', $commLink->created_at->toIso8601String())
            ->assertJsonPath('data.images.0.id', $images->first()->id)
            ->assertJsonPath('data.images.0.api_url', route('comm-link-images.show', $images->first()->id))
            ->assertJsonPath('data.images.0.similar_url', route('comm-link-images.similar', $images->first()->id))
            ->assertJsonCount($images->count(), 'data.images')
            ->assertJsonPath('data.images.0.name', $images->first()->name)
            ->assertJsonPath('meta.prev_id', -1)
            ->assertJsonPath('meta.next_id', -1)
            ->assertJsonPath('meta.valid_relations.0', 'images')
            ->assertJsonPath('meta.valid_relations.1', 'links');
    });
});

describe('search', function (): void {
    it('searches comm-links by text query without bigint cast errors', function (): void {
        $category = Category::factory()->create();
        $series = Series::factory()->create();

        $publicChannel = Channel::factory()->create([
            'name' => 'News',
            'slug' => 'news',
        ]);
        $subscriberChannel = Channel::factory()->create([
            'name' => 'Subscriber',
            'slug' => 'subscriber',
        ]);

        $visibleCommLink = CommLink::factory()->create([
            'cig_id' => 24001,
            'title' => 'Flight model update',
            'category_id' => $category->id,
            'series_id' => $series->id,
            'channel_id' => $publicChannel->id,
        ]);

        $subscriberCommLink = CommLink::factory()->create([
            'cig_id' => 24002,
            'title' => 'Flight model deep dive',
            'category_id' => $category->id,
            'series_id' => $series->id,
            'channel_id' => $subscriberChannel->id,
        ]);

        $response = $this->postJson(route('comm-links.search'), [
            'query' => 'flight model',
        ]);

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertHeader('Deprecated', 'true');

        $ids = collect($response->json('data'))->pluck('id');

        expect($response->json('meta.deprecated'))->toBeTrue()
            ->and($ids->all())->toBe([$visibleCommLink->cig_id])
            ->and($ids)->not->toContain($subscriberCommLink->cig_id);
    });

    it('searches comm-links by numeric cig id', function (): void {
        $category = Category::factory()->create();
        $series = Series::factory()->create();
        $channel = Channel::factory()->create([
            'name' => 'News',
            'slug' => 'news',
        ]);

        $targetCommLink = CommLink::factory()->create([
            'cig_id' => 25001,
            'title' => 'Roadmap roundup',
            'category_id' => $category->id,
            'series_id' => $series->id,
            'channel_id' => $channel->id,
        ]);

        CommLink::factory()->create([
            'cig_id' => 25002,
            'title' => 'Another comm-link',
            'category_id' => $category->id,
            'series_id' => $series->id,
            'channel_id' => $channel->id,
        ]);

        $response = $this->postJson(route('comm-links.search'), [
            'query' => (string) $targetCommLink->cig_id,
        ]);

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data');

        expect(collect($response->json('data'))->pluck('id')->all())
            ->toBe([$targetCommLink->cig_id]);
    });
});

describe('filters endpoint', function (): void {
    beforeEach(function (): void {
        app()->instance('env', 'production');
        app('cache')->setDefaultDriver('array');
        app()->forgetInstance('cache');
        app('cache')->forgetDriver(['array', 'database']);
        Cache::store('array')->flush();
    });

    it('returns comm-link filter values with counts', function (): void {
        GameVersion::factory()->create([
            'code' => '3.25.0-LIVE',
            'channel' => 'live',
            'is_default' => true,
            'released_at' => now(),
        ]);

        $category = Category::factory()->create(['name' => 'Updates']);
        $channel = Channel::factory()->create(['name' => 'News']);
        $series = Series::factory()->create(['name' => 'Ship Shape']);

        CommLink::factory()->create([
            'category_id' => $category->id,
            'channel_id' => $channel->id,
            'series_id' => $series->id,
        ]);

        CommLink::factory()->create([
            'category_id' => $category->id,
            'channel_id' => $channel->id,
            'series_id' => $series->id,
        ]);

        $this->getJson(route('comm-links.filters'))
            ->assertOk()
            ->assertExactJson([
                'filters' => [
                    'category' => [
                        ['value' => 'Updates', 'label' => 'Updates', 'count' => 2],
                    ],
                    'channel' => [
                        ['value' => 'News', 'label' => 'News', 'count' => 2],
                    ],
                    'series' => [
                        ['value' => 'Ship Shape', 'label' => 'Ship Shape', 'count' => 2],
                    ],
                ],
            ]);
    });

    it('returns filtered comm-link facet values without caching the filtered response', function (): void {
        $updates = Category::factory()->create(['name' => 'Updates']);
        $guides = Category::factory()->create(['name' => 'Guides']);
        $news = Channel::factory()->create(['name' => 'News']);
        $spectrum = Channel::factory()->create(['name' => 'Spectrum']);
        $shipShape = Series::factory()->create(['name' => 'Ship Shape']);
        $monthly = Series::factory()->create(['name' => 'Monthly Report']);

        CommLink::factory()->create([
            'category_id' => $updates->id,
            'channel_id' => $news->id,
            'series_id' => $shipShape->id,
        ]);

        CommLink::factory()->create([
            'category_id' => $guides->id,
            'channel_id' => $spectrum->id,
            'series_id' => $monthly->id,
        ]);

        $response = $this->getJson(route('comm-links.filters', [
            'filter' => ['channel' => 'News'],
        ]));

        $response->assertOk()
            ->assertExactJson([
                'filters' => [
                    'category' => [
                        ['value' => 'Updates', 'label' => 'Updates', 'count' => 1],
                    ],
                    'channel' => [
                        ['value' => 'News', 'label' => 'News', 'count' => 1],
                    ],
                    'series' => [
                        ['value' => 'Ship Shape', 'label' => 'Ship Shape', 'count' => 1],
                    ],
                ],
            ]);

        expect(Cache::get('filters:index:comm-links'))->toBeNull();
    });
});
