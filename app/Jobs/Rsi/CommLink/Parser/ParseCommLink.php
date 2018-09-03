<?php declare(strict_types = 1);

namespace App\Jobs\Rsi\CommLink\Parser;

use App\Models\Rsi\CommLink\Category\Category;
use App\Models\Rsi\CommLink\Channel\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Link\Link;
use App\Models\Rsi\CommLink\Series\Series;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Parses the HTML File and extracts all needed Data
 */
class ParseCommLink implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Default Creation Date no Date was found in the Comm Link
     */
    private const DEFAULT_CREATION_DATE = '2012-01-01 00:00:00';

    /**
     * @var int Comm Link ID
     */
    private $commLinkId;

    /**
     * @var string File in the Comm Link ID Folder
     */
    private $file;

    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    private $crawler;

    /**
     * @var array Array with Links and Images from the Comm Link
     */
    private $commLinkContent = [
        'images' => [],
        'links' => [],
    ];

    /**
     * Create a new job instance.
     *
     * @param int    $id
     * @param string $file
     */
    public function __construct(int $id, string $file)
    {
        $this->commLinkId = $id;
        $this->file = $file;
    }

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $content = Storage::disk('comm_links')->get($this->filePath());
        $this->crawler = new Crawler();
        $this->crawler->addHtmlContent($content, 'UTF-8');

        $content = $this->crawler->filter('#post')->first();

        try {
            $content->text();
        } catch (\InvalidArgumentException $e) {
            app('Log')::info("Comm-Link with id {$this->commLinkId} has no content");

            return;
        }

        $this->extractImages();
        $this->extractLinks();
        $commentCount = $this->getCommentCount();
        $createdAt = $this->getCreatedAt();

        /** @var \App\Models\Rsi\CommLink\CommLink $commLink */
        $commLink = CommLink::updateOrCreate(
            [
                'cig_id' => $this->commLinkId,
            ],
            [
                'title' => $this->getTitle(),
                'comment_count' => $commentCount,
                'file' => $this->file,
                'channel_id' => $this->getChannel(),
                'category_id' => $this->getCategory(),
                'series_id' => $this->getSeries(),
                'created_at' => $createdAt,
            ]
        );

        $commLink->translations()->updateOrCreate(
            [
                'locale_code' => 'en_EN',
            ],
            [
                'translation' => $this->getContent(),
            ]
        );

        $commLink->images()->sync($this->getImageIds());
        $commLink->links()->sync($this->getLinkIds());
    }

    private function getImageIds(): array
    {
        $imageIds = [];
        $images = collect($this->commLinkContent['images']);
        $images->each(
            function ($image) use (&$imageIds) {
                $src = $image['src'];
                $pattern = '/media\/(\w+)\/(\w+)\//';
                $src = preg_replace($pattern, '/media/$1/source/', $src);

                if (null === parse_url($src, PHP_URL_HOST)) {
                    $src = config('api.rsi_url').$src;
                }

                $imageIds[] = Image::firstOrCreate(
                    [
                        'src' => $src,
                        'alt' => $image['alt'],
                    ]
                )->id;
            }
        );

        return $imageIds;
    }

    private function getLinkIds(): array
    {
        $linkIds = [];
        $links = collect($this->commLinkContent['links']);
        $links->each(
            function ($link) use (&$linkIds) {
                $linkIds[] = Link::firstOrCreate(
                    [
                        'href' => $link['href'],
                        'text' => $link['text'],
                    ]
                )->id;
            }
        );

        return $linkIds;
    }

    /**
     * Tries to extract the Creation Date from the .title-section Element
     * Defaults to '2012-01-01 00:00:00' if no Date was found
     *
     * @return string Parsed Date
     */
    private function getCreatedAt()
    {
        try {
            return Carbon::parse(
                $this->crawler->filter('.title-section .details div:nth-of-type(3) p')->text()
            )->toDateTimeString();
        } catch (\InvalidArgumentException $e) {
            app('Log')::debug("Comm-Link with id {$this->commLinkId} has no Creation Date");
        }

        return self::DEFAULT_CREATION_DATE;
    }

    /**
     * Tries to extract the Comment Count from the .title-section Element
     *
     * @return int Comment Count
     */
    private function getCommentCount(): int
    {
        try {
            return intval($this->crawler->filter('.comment-count')->first()->text());
        } catch (\InvalidArgumentException $e) {
            app('Log')::debug("Comm-Link with id {$this->commLinkId} has no Comments");
        }

        return 0;
    }

    /**
     * @return string Path to Comm Link File
     */
    private function filePath()
    {
        return "{$this->commLinkId}/{$this->file}";
    }

    /**
     * Tries to Extract the Comm Link Channel
     * Defaults to 'Undefined' if not found
     *
     * @return int Channel ID
     */
    private function getChannel()
    {
        try {
            $channel = $this->crawler->filter('.title-bar .title h2')->text();
        } catch (\InvalidArgumentException $e) {
            // Populated by Seed
            return 1;
        }

        if (empty($channel)) {
            // Populated by Seed
            return 1;
        }

        return Channel::firstOrCreate(
            [
                'name' => $channel,
            ]
        )->id;
    }

    /**
     * Tries to Extract the Comm Link Category
     * Defaults to 'Undefined' if not found
     *
     * @return int Category ID
     */
    private function getCategory()
    {
        try {
            $category = $this->crawler->filter('.title-bar .title h1')->text();
        } catch (\InvalidArgumentException $e) {
            // Populated by Seed
            return 1;
        }

        if (empty($category)) {
            // Populated by Seed
            return 1;
        }

        return Category::firstOrCreate(
            [
                'name' => $category,
            ]
        )->id;
    }

    /**
     * Tries to Extract the Comm Link Series
     * Defaults to 'None' if not found
     *
     * @return int Series ID
     */
    private function getSeries()
    {
        try {
            $series = $this->crawler->filter('.presented-by + div + h1')->text();
        } catch (\InvalidArgumentException $e) {
            // Populated by Seed
            return 1;
        }

        if (empty($series)) {
            // Populated by Seed
            return 1;
        }

        return Series::firstOrCreate(
            [
                'name' => $series,
            ]
        )->id;
    }

    /**
     * Extracts the Comm Link title from the <title> Element
     *
     * @return string Comm Link Title
     */
    private function getTitle()
    {
        try {
            $title = $this->crawler->filterXPath('//title')->text();
        } catch (\InvalidArgumentException $e) {
            return 'No Title Found';
        }

        return trim(
            preg_replace(
                [
                    "/\r|\n/",
                    '/\s+/',
                ],
                [
                    '',
                    ' ',
                ],
                explode('-', $title)[0]
            )
        );
    }

    /**
     * Tries to extract the Comm Link Content as Text
     *
     * @param string $filter Content Element class/id
     *
     * @return string
     */
    private function getContent(string $filter = '.segment')
    {
        if ($this->isSpecialPage()) {
            $filter = '#layout-system';
        }

        $content = '';

        try {
            $this->crawler->filter($filter)->each(
                function (Crawler $crawler) use (&$content) {
                    $content .= $crawler->text();
                }
            );
        } catch (\InvalidArgumentException $e) {
            app('Log')::info("Comm-Link with id {$this->commLinkId} has no Content in {$filter}.");
        }

        return base64_encode(
            trim(
                preg_replace(
                    [
                        '/\R+/',
                    ],
                    [
                        "\n",
                    ],
                    $content
                )
            )
        );
    }

    /**
     * Checks if Comm Link Page is a Ship Page
     * Ship Pages are wrapped in a '#layout-system' Div
     *
     * @return bool
     */
    private function isSpecialPage(): bool
    {
        return ($this->crawler->filter('#layout-system')->count() === 1) ? true : false;
    }

    /**
     * Extracts all <a> Elements from the Crawler
     * Saves href and Link Texts
     */
    private function extractLinks()
    {
        $this->crawler->filter('#post')->filterXPath('//a')->each(
            function (Crawler $crawler) {
                $href = $crawler->attr('href');

                if (null !== $href && null !== parse_url($href, PHP_URL_HOST)) {
                    $this->commLinkContent['links'][] = [
                        'href' => trim($href),
                        'text' => trim($crawler->text()),
                    ];
                }
            }
        );
    }

    /**
     * Extracts all <img> Elements from the Crawler
     * Saves src and alt attributes
     */
    private function extractImages()
    {
        $this->crawler->filter('#post')->filterXPath('//img')->each(
            function (Crawler $crawler) {
                $src = $crawler->attr('src');

                if (null !== $src && !empty($src)) {
                    $this->commLinkContent['images'][] = [
                        'src' => ltrim(trim($src), '/'),
                        'alt' => trim($crawler->attr('alt') ?? ''),
                    ];
                }
            }
        );
    }
}
