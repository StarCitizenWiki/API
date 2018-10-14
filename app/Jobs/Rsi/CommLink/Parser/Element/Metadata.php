<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 11.09.2018
 * Time: 17:52
 */

namespace App\Jobs\Rsi\CommLink\Parser\Element;

use App\Jobs\Rsi\CommLink\Parser\Element\AbstractBaseElement as BaseElement;
use App\Models\Rsi\CommLink\Category\Category;
use App\Models\Rsi\CommLink\Channel\Channel;
use App\Models\Rsi\CommLink\Series\Series;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Comm-Link Metadata Parser
 */
class Metadata extends BaseElement
{
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
     * Default Creation Date no Date was found in the Comm-Link
     */
    private const DEFAULT_CREATION_DATE = '2012-01-01 00:00:00';

    /**
     * Default Title Ending
     */
    private const RSI_DEFAULT_TITLE_ENDING = ' - Roberts Space Industries | Follow the development of Star Citizen and Squadron 42';

    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    private $commLink;

    /**
     * @var \Illuminate\Support\Collection MetaData Collection
     */
    private $metaData;

    /**
     * Metadata constructor.
     *
     * @param \Symfony\Component\DomCrawler\Crawler $commLinkDocument
     */
    public function __construct(Crawler $commLinkDocument)
    {
        $this->commLink = $commLinkDocument;
        $this->metaData = new Collection();
    }

    /**
     * @return \Illuminate\Support\Collection
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

        return $this->metaData;
    }

    /**
     * Extracts the Comm-Link title from the <title> Element
     */
    private function extractTitle(): void
    {
        try {
            $title = $this->commLink->filterXPath('//title')->text();
        } catch (\InvalidArgumentException $e) {
            $title = 'No Title Found';
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
     * Tries to extract the Comment Count from the .title-section Element
     */
    private function extractCommentCount(): void
    {
        $count = 0;
        if ($this->commLink->filter('.comment-count')->count() > 0) {
            $count = intval($this->commLink->filter('.comment-count')->first()->text());
        }

        $this->metaData->put('comment_count', $count);
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
     * Tries to Extract the Comm-Link Channel
     * Defaults to 'Undefined' if not found
     */
    private function extractChannel(): void
    {
        $channelId = 1;

        if ($this->commLink->filter(self::CHANNEL_SELECTOR)->count() > 0) {
            $channel = $this->commLink->filter(self::CHANNEL_SELECTOR)->text();

            if (!empty($channel)) {
                $channel = $this->cleanText($channel);

                $channelId = Channel::firstOrCreate(
                    [
                        'name' => $channel,
                        'slug' => str_slug($channel, '-'),
                    ]
                )->id;
            }
        }

        $this->metaData->put('channel_id', $channelId);
    }

    /**
     * Tries to Extract the Comm-Link Category
     * Defaults to 'Undefined' if not found
     */
    private function extractCategory(): void
    {
        $categoryId = 1;

        if ($this->commLink->filter(self::CATEGORY_SELECTOR)->count() > 0) {
            $category = $this->commLink->filter(self::CATEGORY_SELECTOR)->text();

            if (!empty($category)) {
                $category = $this->cleanText($category);

                $categoryId = Category::firstOrCreate(
                    [
                        'name' => $category,
                        'slug' => str_slug($category, '-'),
                    ]
                )->id;
            }
        }

        $this->metaData->put('category_id', $categoryId);
    }

    /**
     * Tries to Extract the Comm-Link Series
     * Defaults to 'None' if not found
     */
    private function extractSeries(): void
    {
        $seriesId = Series::first()->id;

        if ($this->commLink->filter(self::SERIES_SELECTOR)->count() > 0) {
            $series = $this->commLink->filter(self::SERIES_SELECTOR)->text();

            if (!empty($series)) {
                $series = $this->cleanText($series);

                $seriesId = Series::firstOrCreate(
                    [
                        'name' => $series,
                        'slug' => str_slug($series, '-'),
                    ]
                )->id;
            }
        }

        $this->metaData->put('series_id', $seriesId);
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
}
