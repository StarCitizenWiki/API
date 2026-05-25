<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink;

use App\Models\Rsi\CommLink\Category;
use App\Models\Rsi\CommLink\Channel;
use App\Models\Rsi\CommLink\Series;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;

class Metadata extends AbstractBaseElement
{
    public const DEFAULT_CREATION_DATE = '2012-01-01 00:00:00';

    private const CHANNEL_SELECTOR = '.title-bar .title h1';

    private const CATEGORY_SELECTOR = '.title-bar .title h2';

    private const SERIES_SELECTOR = '.presented-by + div + h1';

    private const CREATED_AT_SELECTOR = '.title-section .details div:nth-of-type(3) p';

    private const RSI_DEFAULT_TITLE_ENDING = ' - Roberts Space Industries | Follow the development of Star Citizen and Squadron 42';

    private const SUBSCRIBER = 'Subscriber';

    private const MANUAL_SETTINGS = [
        '/Inside Star Citizen/' => [
            'channel' => 'Transmission',
            'category' => 'General',
            'series' => 'Inside Star Citizen',
        ],
        '/Star Citizen Live(?:\sGamedev)?/' => [
            'channel' => 'Transmission',
            'category' => 'General',
            'series' => 'Star Citizen LIVE',
        ],
        '/^Q\s?&\s?A:?.+/' => [
            'channel' => 'Engineering',
            'category' => 'Development',
            'series' => 'Concept Ship Q&A',
        ],
        '/.+Q\s?&\s?A$/' => [
            'channel' => 'Engineering',
            'category' => 'Development',
        ],
        '/Roadmap Roundup.+/' => [
            'channel' => 'Spectrum Dispatch',
            'category' => 'Lore',
            'series' => 'Roadmap Roundup',
        ],
        '/.+Subscriber Promotions$/' => [
            'channel' => 'Transmission',
            'category' => 'General',
            'series' => 'Subscriber Promotions',
        ],
        '/Calling All Devs/' => [
            'channel' => 'Transmission',
            'category' => 'General',
            'series' => 'Calling All Devs',
        ],
        '/^(Star Citizen )?(Alpha|Beta|Patch) v?[\d\.a-g]+\s?(?:Available!?)?$/' => [
            'channel' => 'Transmission',
            'category' => 'General',
            'series' => 'Release Info',
        ],
        '/(Alpha|Beta) - .+/' => [
            'channel' => 'Transmission',
            'category' => 'General',
            'series' => 'Release Info',
        ],
        '/^Design Notes:\s.+/' => [
            'channel' => 'Engineering',
            'category' => 'Development',
            'series' => 'Design Post',
        ],
        '/^Letter from the Chairman(?:\:\w+)?/' => [
            'channel' => 'Transmission',
            'category' => 'General',
            'series' => 'From the Chairman',
        ],
        '/^Bugsmashers!?/i' => [
            'channel' => 'Transmission',
            'category' => 'General',
            'series' => 'Bugsmashers',
        ],
        '/^Around the Verse.*?/' => [
            'channel' => 'Transmission',
            'category' => 'General',
            'series' => 'Around the Verse',
        ],
    ];

    private const NO_TITLE_FOUND = 'No Title Found';

    private Crawler $commLink;

    private Collection $metaData;

    public function __construct(Crawler $commLinkDocument)
    {
        $this->commLink = $commLinkDocument;
        $this->metaData = new Collection;
    }

    public function getMetaData(): Collection
    {
        $this->extractTitle();
        $this->extractCategory();
        $this->extractChannel();
        $this->extractSeries();
        $this->extractOriginalUrl();
        $this->extractCommentCount();
        $this->extractCreatedAt();

        $this->runManualFixes();

        return $this->metaData;
    }

    private function extractTitle(): void
    {
        if ($this->isSubscriberPage($this->commLink)) {
            $this->extractSubscriberPageTitle();

            return;
        }

        try {
            $title = $this->commLink->filterXPath('//title')->text();
        } catch (InvalidArgumentException $exception) {
            $title = self::NO_TITLE_FOUND;
        }

        $title = preg_replace([
            "/\r|\n/",
            '/\s+/',
        ], [
            '',
            ' ',
        ], str_replace(self::RSI_DEFAULT_TITLE_ENDING, '', $title)) ?? $title;

        $this->metaData->put('title', $this->cleanText($title));
    }

    private function extractSubscriberPageTitle(): void
    {
        try {
            $title = $this->commLink->filter('.title-section h2')->first()->text();
        } catch (InvalidArgumentException $exception) {
            $title = self::NO_TITLE_FOUND;
        }

        $this->metaData->put('title', $this->cleanText($title));
    }

    private function extractCategory(?string $category = null): void
    {
        $categoryId = 1;

        if ($category === null && $this->commLink->filter(self::CATEGORY_SELECTOR)->count() > 0) {
            $category = $this->commLink->filter(self::CATEGORY_SELECTOR)->text();

            if (! empty($category)) {
                $category = $this->cleanText($category);
            }
        }

        if (! empty($category)) {
            $categoryId = Category::query()->firstOrCreate([
                'name' => $category,
                'slug' => Str::slug($category, '-'),
            ])->id;
        }

        $this->metaData->put('category_id', $categoryId);
    }

    private function extractChannel(?string $channel = null): void
    {
        $channelId = 1;

        if ($channel === null && $this->commLink->filter(self::CHANNEL_SELECTOR)->count() > 0) {
            $channel = $this->commLink->filter(self::CHANNEL_SELECTOR)->text();

            if (! empty($channel)) {
                $channel = $this->cleanText($channel);
            }
        }

        if (! empty($channel)) {
            $channelId = Channel::query()->firstOrCreate([
                'name' => $channel,
                'slug' => Str::slug($channel, '-'),
            ])->id;
        }

        $this->metaData->put('channel_id', $channelId);
    }

    private function extractSeries(?string $series = null): void
    {
        $seriesId = 1;

        if ($series === null && $this->commLink->filter(self::SERIES_SELECTOR)->count() > 0) {
            $series = $this->commLink->filter(self::SERIES_SELECTOR)->text();

            if (! empty($series)) {
                $series = $this->cleanText($series);
            }
        }

        if (! empty($series)) {
            $seriesId = Series::query()->firstOrCreate([
                'name' => $series,
                'slug' => Str::slug($series, '-'),
            ])->id;
        }

        $this->metaData->put('series_id', $seriesId);
    }

    private function extractOriginalUrl(): void
    {
        if ($this->commLink->filter('meta[property="og:url"]')->count() === 0) {
            $this->metaData->put('url', null);

            return;
        }

        $this->metaData->put('url', $this->commLink->filter('meta[property="og:url"]')->attr('content'));
    }

    private function extractCommentCount(): void
    {
        if ($this->commLink->filter('.comment-count')->count() === 0) {
            $this->metaData->put('comment_count', 0);

            return;
        }

        $count = (int) $this->commLink->filter('.comment-count')->text();
        $this->metaData->put('comment_count', $count);
    }

    private function extractCreatedAt(): void
    {
        $date = null;

        if ($this->commLink->filter(self::CREATED_AT_SELECTOR)->count() > 0) {
            $date = $this->commLink->filter(self::CREATED_AT_SELECTOR)->text();
        }

        if ($date === null || $date === '') {
            $this->metaData->put('created_at', self::DEFAULT_CREATION_DATE);

            return;
        }

        try {
            $this->metaData->put('created_at', Carbon::parse($date)->format('Y-m-d H:i:s'));
        } catch (InvalidArgumentException $exception) {
            $this->metaData->put('created_at', self::DEFAULT_CREATION_DATE);
        }
    }

    private function runManualFixes(): void
    {
        $title = (string) $this->metaData->get('title');

        // Try URL-slug-based channel resolution when CSS selectors didn't match
        if ($this->metaData->get('channel_id') === 1) {
            $url = $this->metaData->get('url');

            if ($url !== null && preg_match('#/comm-link/([a-z0-9-]+)/#', $url, $matches) === 1) {
                $slug = $matches[1];
                $channel = Channel::query()->where('slug', $slug)->first();

                if ($channel !== null) {
                    $this->extractChannel($channel->name);
                }
            }
        }

        // MANUAL_SETTINGS still apply category/series even if channel was resolved from URL
        $channelResolved = $this->metaData->get('channel_id') !== 1;

        foreach (self::MANUAL_SETTINGS as $pattern => $settings) {
            if (preg_match($pattern, $title)) {
                if (! $channelResolved) {
                    $this->extractChannel($settings['channel'] ?? null);
                }

                $this->extractCategory($settings['category'] ?? null);
                $this->extractSeries($settings['series'] ?? null);

                break;
            }
        }

        $channel = optional(Channel::query()->find($this->metaData->get('channel_id')))->name;
        if ($channel === self::SUBSCRIBER) {
            $this->extractCategory('General');
            $this->extractSeries('Subscription');
        }
    }
}
