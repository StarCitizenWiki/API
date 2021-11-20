<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink;

use App\Services\Parser\CommLink\AbstractBaseElement as BaseElement;
use App\Models\Rsi\CommLink\Category\Category;
use App\Models\Rsi\CommLink\Channel\Channel;
use App\Models\Rsi\CommLink\Series\Series;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Comm-Link Metadata Parser
 */
class Metadata extends BaseElement
{
    /**
     * Default Creation Date no Date was found in the Comm-Link
     */
    public const DEFAULT_CREATION_DATE = '2012-01-01 00:00:00';

    /**
     * Channel CSS Selector
     */
    private const CHANNEL_SELECTOR = '.title-bar .title h1';

    /**
     * Category CSS Selector
     */
    private const CATEGORY_SELECTOR = '.title-bar .title h2';

    /**
     * Series CSS Selector
     */
    private const SERIES_SELECTOR = '.presented-by + div + h1';

    /**
     * Created At CSS Selector
     */
    private const CREATED_AT_SELECTOR = '.title-section .details div:nth-of-type(3) p';

    /**
     * Default Title Ending
     */
    private const RSI_DEFAULT_TITLE_ENDING = ' - Roberts Space Industries | Follow the development of Star Citizen and Squadron 42';

    /**
     * Subscriber Channel
     */
    private const SUBSCRIBER = 'Subscriber';

    /**
     * This maps a common Comm-Link Title to pre-defined Channel / Category / Series
     * As the "new" Layout Systems hides the top-bar that includes this information we need to manually set this...
     */
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
        // Q&A: ... Posts
        '/^Q\s?&\s?A:?.+/' => [
            'channel' => 'Engineering',
            'category' => 'Development',
            // TODO: Is this correct for all?
            'series' => 'Concept Ship Q&A',
        ],
        // ... Q&A Posts
        '/.+Q\s?&\s?A$/' => [
            'channel' => 'Engineering',
            'category' => 'Development',
        ],
        // Roadmap Roundup ... Posts
        '/Roadmap Roundup.+/' => [
            'channel' => 'Spectrum Dispatch',
            'category' => 'Lore',
            'series' => 'Roadmap Roundup',
        ],
        // ... Subscriber Promotions
        '/.+Subscriber Promotions$/' => [
            'channel' => 'Transmission',
            'category' => 'General',
            // This is not an official series
            'series' => 'Subscriber Promotions',
        ],
        '/Calling All Devs/' => [
            'channel' => 'Transmission',
            'category' => 'General',
            'series' => 'Calling All Devs',
        ],
        // Star Citizen Patch Infos ... Posts
        '/^(Star Citizen )?(Alpha|Beta|Patch) v?[\d\.a-g]+\s?(?:Available!?)?$/' => [
            'channel' => 'Transmission',
            'category' => 'General',
            // This is not an official series
            'series' => 'Release Info',
        ],
        // Alpha - ... Posts
        '/(Alpha|Beta) - .+/' => [
            'channel' => 'Transmission',
            'category' => 'General',
            // This is not an official series
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

    /**
     * Title used if no title could be found
     */
    private const NO_TITLE_FOUND = 'No Title Found';

    /**
     * @var Crawler
     */
    private Crawler $commLink;

    /**
     * @var Collection MetaData Collection
     */
    private Collection $metaData;

    /**
     * Metadata constructor.
     *
     * @param Crawler $commLinkDocument
     */
    public function __construct(Crawler $commLinkDocument)
    {
        $this->commLink = $commLinkDocument;
        $this->metaData = new Collection();
    }

    /**
     * @return Collection
     */
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

    /**
     * Extracts the Comm-Link title from the <title> Element
     */
    private function extractTitle(): void
    {
        if ($this->isSubscriberPage($this->commLink)) {
            $this->extractSubscriberPageTitle();

            return;
        }

        try {
            $title = $this->commLink->filterXPath('//title')->text();
        } catch (InvalidArgumentException $e) {
            $title = self::NO_TITLE_FOUND;
        }

        $title = preg_replace(
            [
                "/\r|\n/",
                '/\s+/',
            ],
            [
                '',
                ' ',
            ],
            str_replace(
                self::RSI_DEFAULT_TITLE_ENDING,
                '',
                $title
            )
        );

        $this->metaData->put('title', $this->cleanText($title));
    }

    /**
     * Extracts the Comm-Link title for a Subscriber Page
     */
    private function extractSubscriberPageTitle(): void
    {
        try {
            $title = $this->commLink->filter('.title-section h2')->first()->text();
        } catch (InvalidArgumentException $e) {
            $title = self::NO_TITLE_FOUND;
        }

        $this->metaData->put('title', $this->cleanText($title));
    }

    /**
     * Tries to Extract the Comm-Link Category
     * Defaults to 'Undefined' if not found
     *
     * @param string|null $category Manual category to use
     */
    private function extractCategory(?string $category = null): void
    {
        $categoryId = 1;

        if ($category === null && $this->commLink->filter(self::CATEGORY_SELECTOR)->count() > 0) {
            $category = $this->commLink->filter(self::CATEGORY_SELECTOR)->text();

            if (!empty($category)) {
                $category = $this->cleanText($category);
            }
        }

        if (!empty($category)) {
            $categoryId = Category::query()->firstOrCreate(
                [
                    'name' => $category,
                    'slug' => Str::slug($category, '-'),
                ]
            )->id;
        }

        $this->metaData->put('category_id', $categoryId);
    }

    /**
     * Tries to Extract the Comm-Link Channel
     * Defaults to 'Undefined' if not found
     *
     * @param string|null $channel Manual channel to use
     */
    private function extractChannel(?string $channel = null): void
    {
        $channelId = 1;

        // phpcs:ignore Generic.Files.LineLength.TooLong
        if ($channel === null && ($this->commLink->filter(self::CHANNEL_SELECTOR)->count() > 0 || $this->isSubscriberPage($this->commLink))) {
            if ($this->isSubscriberPage($this->commLink)) {
                $channel = self::SUBSCRIBER;
            } else {
                $channel = $this->commLink->filter(self::CHANNEL_SELECTOR)->text();
            }
        }

        if (!empty($channel)) {
            $channel = $this->cleanText($channel);

            $channelId = Channel::query()->firstOrCreate(
                [
                    'name' => $channel,
                    'slug' => Str::slug($channel, '-'),
                ]
            )->id;
        }

        $this->metaData->put('channel_id', $channelId);
    }

    /**
     * Tries to Extract the Comm-Link Series
     * Defaults to 'None' if not found
     *
     * @param string|null $series Manual series to use
     */
    private function extractSeries(?string $series = null): void
    {
        $seriesId = Series::query()->first()->id;

        if ($series === null && $this->commLink->filter(self::SERIES_SELECTOR)->count() > 0) {
            $series = $this->commLink->filter(self::SERIES_SELECTOR)->text();
        }

        if (!empty($series)) {
            $series = $this->cleanText($series);

            $seriesId = Series::query()->firstOrCreate(
                [
                    'name' => $series,
                    'slug' => Str::slug($series, '-'),
                ]
            )->id;
        }

        $this->metaData->put('series_id', $seriesId);
    }

    /**
     * Tries to get the original Comm-Link URL from the 'Add-Comment' Link
     */
    private function extractOriginalUrl(): void
    {
        $href = null;

        if ($this->commLink->filter('a.add-comment')->count() > 0) {
            $href = $this->commLink->filter('a.add-comment')->attr('href');
        }

        if (!empty($href)) {
            $href = $this->cleanText(str_replace('/connect?jumpto=', '', $href));
        }

        $this->metaData->put('url', $href);
    }

    /**
     * Tries to extract the Comment Count from the .title-section Element
     */
    private function extractCommentCount(): void
    {
        $count = 0;
        if ($this->commLink->filter('.comment-count')->count() > 0) {
            $count = (int)$this->commLink->filter('.comment-count')->first()->text();
        }

        $this->metaData->put('comment_count', $count);
    }

    /**
     * Tries to extract the Creation Date from the .title-section Element
     * Defaults to '2012-01-01 00:00:00' if no Date was found
     */
    private function extractCreatedAt(): void
    {
        $createdAt = self::DEFAULT_CREATION_DATE;

        if ($this->commLink->filter(self::CREATED_AT_SELECTOR)->count() > 0) {
            $createdAt = $this->commLink->filter(self::CREATED_AT_SELECTOR)->text();

            if (!empty($createdAt)) {
                $createdAt = Carbon::parse($createdAt)->toDateTimeString();
            }
        }

        $this->metaData->put('created_at', $createdAt);
    }

    /**
     * @see Metadata::MANUAL_SETTINGS
     */
    private function runManualFixes(): void
    {
        $title = $this->metaData->get('title');
        if ($title === self::NO_TITLE_FOUND) {
            return;
        }

        foreach (self::MANUAL_SETTINGS as $matcher => $data) {
            if (preg_match($matcher, $title) === 1) {
                if (isset($data['channel'])) {
                    $this->extractChannel($data['channel']);
                }
                if (isset($data['category'])) {
                    $this->extractCategory($data['category']);
                }
                if (isset($data['series'])) {
                    $this->extractSeries($data['series']);
                }

                return;
            }
        }
    }
}
