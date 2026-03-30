<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\Category;
use App\Models\Rsi\CommLink\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Link;
use App\Models\Rsi\CommLink\Series;
use App\Models\System\Language;
use App\Services\ApiJsonRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

function normalizedIncludes(Request $request): array
{
    return collect(explode(',', (string) $request->query('include', '')))
        ->map(static fn (string $include): string => trim($include))
        ->filter()
        ->sort()
        ->values()
        ->all();
}

/**
 * @return object{calls: array<int, array{path: string, include: array<int, string>}>}
 */
function recordApiJsonRequests(): object
{
    $apiJsonRequest = new class(app('router')) extends ApiJsonRequest
    {
        /** @var array<int, array{path: string, include: array<int, string>}> */
        public array $calls = [];

        public function request(string $path, Request $request): array
        {
            $this->calls[] = [
                'path' => $path,
                'include' => normalizedIncludes($request),
            ];

            return parent::request($path, $request);
        }
    };

    app()->instance(ApiJsonRequest::class, $apiJsonRequest);

    return $apiJsonRequest;
}

it('renders comm-link details from the API show endpoint with normalized includes', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-03-30 12:00:00'));

    try {
        Language::factory()->create(['code' => Language::ENGLISH]);
        Language::factory()->create(['code' => Language::GERMAN]);

        $apiJsonRequest = recordApiJsonRequests();

        $channel = Channel::factory()->create([
            'name' => 'Live',
            'slug' => 'live',
        ]);

        $category = Category::factory()->create([
            'name' => 'Update',
            'slug' => 'update',
        ]);

        $series = Series::factory()->create([
            'name' => 'Patch Notes',
            'slug' => 'patch-notes',
        ]);

        $prevCommLink = CommLink::factory()->create([
            'cig_id' => 12999,
            'title' => 'Previous Comm-Link',
            'channel_id' => $channel->id,
            'category_id' => $category->id,
            'series_id' => $series->id,
        ]);

        $commLink = CommLink::factory()->create([
            'cig_id' => 13001,
            'title' => 'Alpha 4.0 Patch Notes',
            'url' => 'https://robertsspaceindustries.com/comm-link/transmission/13001-Alpha-4-0-Patch-Notes',
            'channel_id' => $channel->id,
            'category_id' => $category->id,
            'series_id' => $series->id,
            'comment_count' => 42,
            'images_count' => 1,
            'links_count' => 2,
            'created_at' => Carbon::parse('2026-03-30 10:00:00'),
            'translation' => [
                'en' => "Line one\nLine two",
                'de' => "Zeile eins\nZeile zwei",
            ],
        ]);

        $nextCommLink = CommLink::factory()->create([
            'cig_id' => 13002,
            'title' => 'Next Comm-Link',
            'channel_id' => $channel->id,
            'category_id' => $category->id,
            'series_id' => $series->id,
        ]);

        $primaryLink = Link::factory()->create([
            'text' => 'Read on RSI',
            'href' => 'https://example.test/patch-notes',
        ]);

        $archiveLink = Link::factory()->create([
            'text' => 'Patch notes archive',
            'href' => 'https://example.test/archive',
        ]);

        $commLink->links()->attach([$primaryLink->id, $archiveLink->id]);

        $image = Image::factory()->create([
            'src' => '/i/alpha-banner/alpha-banner.webp',
            'alt' => 'Alpha banner',
        ]);

        $image->metadata()->create([
            'size' => 1024,
            'mime' => 'image/webp',
            'last_modified' => Carbon::parse('2026-03-30 10:00:00'),
        ]);

        $commLink->images()->attach($image->id);

        $response = $this->get(route('web.comm-links.show', [
            'id' => $commLink->cig_id,
            'include' => 'links',
        ]));

        expect($apiJsonRequest->calls)->toBe([
            [
                'path' => route('comm-links.show', ['id' => $commLink->cig_id], false),
                'include' => ['images', 'links'],
            ],
        ]);

        $response->assertOk()
            ->assertViewIs('comm-links.show')
            ->assertViewHas('pageTitle', $commLink->title)
            ->assertViewHas('commLink', function (array $commLinkData) use ($archiveLink, $commLink, $image, $primaryLink): bool {
                return $commLinkData['id'] === $commLink->cig_id
                    && $commLinkData['created_at_human'] === '2 hours ago'
                    && $commLinkData['created_at'] === $commLink->created_at->toIso8601String()
                    && collect($commLinkData['links'])->pluck('href')->sort()->values()->all() === [
                        $archiveLink->href,
                        $primaryLink->href,
                    ]
                    && collect($commLinkData['images'])->pluck('id')->all() === [$image->id];
            })
            ->assertViewHas('commLinkMeta', fn (array $commLinkMeta): bool => $commLinkMeta['prev_id'] === $prevCommLink->cig_id
                && $commLinkMeta['next_id'] === $nextCommLink->cig_id)
            ->assertSeeText($commLink->title)
            ->assertSeeText('Live')
            ->assertSeeText('Update')
            ->assertSeeText('Patch Notes')
            ->assertSeeText('English')
            ->assertSeeText('German')
            ->assertSeeText('Line one')
            ->assertSeeText('Line two')
            ->assertSeeText('Zeile eins')
            ->assertSeeText('Zeile zwei')
            ->assertSeeText('Read on RSI')
            ->assertSeeText('Patch notes archive')
            ->assertSeeText('Alpha banner')
            ->assertSeeText('42')
            ->assertSeeText('2 hours ago')
            ->assertSeeText($commLink->created_at->toIso8601String())
            ->assertSee(route('web.comm-links.index'), false)
            ->assertSee(route('web.comm-links.show', $prevCommLink->cig_id), false)
            ->assertSee(route('web.comm-links.show', $nextCommLink->cig_id), false)
            ->assertSee(route('web.comm-links.images.show', $image->id), false)
            ->assertSee(route('comm-links.show', ['id' => $commLink->cig_id]), false)
            ->assertSee('href="https://example.test/patch-notes"', false)
            ->assertSee('href="https://example.test/archive"', false)
            ->assertSee('href="https://robertsspaceindustries.com/comm-link/transmission/13001-Alpha-4-0-Patch-Notes"', false)
            ->assertDontSeeText('No content available.')
            ->assertDontSeeText('No translations available.')
            ->assertDontSeeText('No links available.')
            ->assertDontSeeText('No images available.');
    } finally {
        Carbon::setTestNow();
    }
});

it('renders empty states and disabled navigation when the comm-link has no related content', function (): void {
    $apiJsonRequest = recordApiJsonRequests();

    $channel = Channel::factory()->create([
        'name' => 'News',
        'slug' => 'news',
    ]);

    $category = Category::factory()->create([
        'name' => 'Lore',
        'slug' => 'lore',
    ]);

    $series = Series::factory()->create([
        'name' => 'Spectrum Dispatch',
        'slug' => 'spectrum-dispatch',
    ]);

    $commLink = CommLink::factory()->create([
        'cig_id' => 13002,
        'title' => 'Empty Comm-Link',
        'channel_id' => $channel->id,
        'category_id' => $category->id,
        'series_id' => $series->id,
    ]);

    $response = $this->get(route('web.comm-links.show', [
        'id' => $commLink->cig_id,
        'include' => 'images',
    ]));

    expect($apiJsonRequest->calls)->toBe([
        [
            'path' => route('comm-links.show', ['id' => $commLink->cig_id], false),
            'include' => ['images', 'links'],
        ],
    ]);

    $response->assertOk()
        ->assertViewIs('comm-links.show')
        ->assertViewHas('pageTitle', $commLink->title)
        ->assertViewHas('commLink', fn (array $commLinkData): bool => $commLinkData['id'] === $commLink->cig_id
            && $commLinkData['links'] === []
            && $commLinkData['images'] === [])
        ->assertSeeText($commLink->title)
        ->assertSeeText('No translations available.')
        ->assertSeeText('No links available.')
        ->assertSeeText('No images available.')
        ->assertSee('data-testid="comm-link-prev-button"', false)
        ->assertSee('data-testid="comm-link-next-button"', false)
        ->assertDontSee('data-testid="comm-link-prev-link"', false)
        ->assertDontSee('data-testid="comm-link-next-link"', false);
});

it('returns not found for missing comm-links', function (): void {
    $this->get(route('web.comm-links.show', 999999))
        ->assertNotFound();
});
