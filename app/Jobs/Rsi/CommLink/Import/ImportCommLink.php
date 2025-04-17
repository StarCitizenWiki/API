<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Import;

use App\Jobs\Rsi\CommLink\Download\DownloadCommLink;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\CommLinksChanged;
use App\Models\System\Language;
use App\Services\Parser\CommLink\Content;
use App\Services\Parser\CommLink\Image;
use App\Services\Parser\CommLink\Link;
use App\Services\Parser\CommLink\Metadata;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Parses the HTML File and extracts all needed Data.
 */
class ImportCommLink implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Comm-Link Post CSS Selector.
     */
    public const POST_SELECTOR = '#post';

    /**
     * Comm-Link Post CSS Selector.
     */
    public const SUBSCRIBERS_SELECTOR = '#subscribers';

    public const SPECIAL_PAGE_SELECTOR = '#layout-system';

    /**
     * Alexandria components selector
     */
    public const ALEXANDRIA_SELECTOR = 'g-platform-client-component, g-banner-advanced, g-navigation-sales';

    /**
     * @var int Comm-Link ID
     */
    private int $commLinkId;

    /**
     * @var string File in the Comm-Link ID Folder
     */
    private string $file;

    private ?CommLink $commLinkModel;

    /**
     * True if the given file content should be imported into the comm link model.
     */
    private bool $forceImport;

    private Crawler $crawler;

    private const ALEXANDRIA_URL_PATTERN = 'https://robertsspaceindustries.com/alexandria/html';

    /**
     * Create a new job instance.
     *
     * @param  int  $id  Comm-Link ID
     * @param  string  $file  Current File Name
     * @param  CommLink|null  $commLink  Optional Comm-Link Model to update
     * @param  bool  $forceImport  Flag to Force Import from the current file
     */
    public function __construct(int $id, string $file, ?CommLink $commLink = null, bool $forceImport = false)
    {
        $this->commLinkId = $id;
        $this->file = $file;
        $this->commLinkModel = $commLink;
        $this->forceImport = $forceImport;
    }

    /**
     * Execute the job.
     *
     *
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        app('Log')::info(
            "Parsing Comm-Link with ID {$this->commLinkId}",
            [
                'id' => $this->commLinkId,
                'file' => $this->file,
                'comm_link_already_in_db' => $this->commLinkModel !== null,
                'force_import' => $this->forceImport === true,
            ]
        );

        $content = Storage::disk('comm_links')->get($this->filePath());
        if ($content === null) {
            throw new FileNotFoundException;
        }

        // Check and process dynamic content if needed
        if ($this->containsDynamicContent($content)) {
            try {
                $content = $this->processDynamicContent($content);
                // Save the updated content
                Storage::disk('comm_links')->put($this->filePath(), $content);
                app('Log')::info(
                    "Successfully processed dynamic content for Comm-Link {$this->commLinkId}",
                    [
                        'file' => $this->file,
                    ]
                );
            } catch (Exception $e) {
                app('Log')::error(
                    "Failed to process dynamic content for Comm-Link {$this->commLinkId}",
                    [
                        'error' => $e->getMessage(),
                        'file' => $this->file,
                    ]
                );
            }
        }

        $this->crawler = new Crawler;
        $this->crawler->addHtmlContent($content);

        $post = $this->crawler->filter(self::POST_SELECTOR);
        $subscribers = $this->crawler->filter(self::SUBSCRIBERS_SELECTOR);
        $specialPage = $this->crawler->filter(self::SPECIAL_PAGE_SELECTOR);
        $alexandriaComponents = $this->crawler->filter(self::ALEXANDRIA_SELECTOR);

        // Check if we have any content to parse
        if (
            $post->count() === 0 &&
            $subscribers->count() === 0 &&
            $specialPage->count() === 0 &&
            $alexandriaComponents->count() === 0
        ) {
            app('Log')::info("Comm-Link with id {$this->commLinkId} has no content");

            return;
        }

        if ($this->commLinkModel === null || $this->forceImport) {
            $this->createCommLink(); // Updates or Creates
        } else {
            $this->checkCommLinkForChanges();
        }
    }

    /**
     * Check if the content contains dynamic content loading
     */
    private function containsDynamicContent(string $content): bool
    {
        return str_contains($content, self::ALEXANDRIA_URL_PATTERN);
    }

    /**
     * Process and replace dynamic content
     *
     * @throws Exception
     */
    private function processDynamicContent(string $content): string
    {

        $processedContent = $content;

        $crawler = new Crawler;
        $crawler->addHtmlContent($content, 'UTF-8');
        $toReplace = [];
        $crawler->filter('script')->each(function (Crawler $node, $i) use (&$toReplace) {
            if (str_contains($node->html(), self::ALEXANDRIA_URL_PATTERN)) {
                $toReplace[] = $node->html();
            }
        });

        $alexandriaContent = '';

        foreach ($toReplace as $scriptTag) {
            if (! preg_match('/https:\/\/robertsspaceindustries\.com\/alexandria\/html[^"\'\s]*/i', $scriptTag, $urlMatches)) {
                app('Log')::warning(
                    "Failed to extract Alexandria URL from script tag in Comm-Link {$this->commLinkId}",
                    [
                        'script_tag' => substr($scriptTag, 0, 150).'...',
                    ]
                );

                continue;
            }

            $alexandriaUrl = $urlMatches[0];

            try {
                $client = new Client([
                    'timeout' => 30,
                ]);

                $response = $client->get($alexandriaUrl);

                if ($response->getStatusCode() !== 200) {
                    throw new RuntimeException('Alexandria endpoint returned status code: '.$response->getStatusCode());
                }

                $dynamicContent = $response->getBody()->getContents();

                if (empty($dynamicContent)) {
                    throw new RuntimeException('Alexandria endpoint returned empty content');
                }

                $processedContent = str_replace($scriptTag, '', $processedContent);

                $alexandriaContent .= $dynamicContent;

                app('Log')::debug(
                    "Successfully replaced Alexandria content in Comm-Link {$this->commLinkId}",
                    [
                        'url' => $alexandriaUrl,
                        'content_length' => strlen($dynamicContent),
                    ]
                );
            } catch (GuzzleException $e) {
                throw new RuntimeException("Failed to fetch dynamic content from {$alexandriaUrl}: {$e->getMessage()}");
            }
        }

        return preg_replace('/<div id="layout-system".*?>.*?<\/div>/s', sprintf('<div id="layout-system">%s</div>', $alexandriaContent), $processedContent);
    }

    /**
     * @return string Path to Comm-Link File
     */
    private function filePath(): string
    {
        return "{$this->commLinkId}/{$this->file}";
    }

    /**
     * Updates or Creates a Comm-Link Model and populates it.
     */
    private function createCommLink(): void
    {
        $data = $this->getCommLinkData();
        // Use the file time for new comm-links
        $data['created_at'] = $data['created_at_file'];

        /** @var CommLink $commLink */
        $commLink = CommLink::updateOrCreate(
            [
                'cig_id' => $this->commLinkId,
            ],
            $data
        );

        $this->addEnglishCommLinkTranslation($commLink);
        $this->syncImageIds($commLink);
        $this->syncLinkIds($commLink);

        CommLinksChanged::create(
            [
                'comm_link_id' => $commLink->id,
                'had_content' => false,
                'type' => 'creation',
            ]
        );
    }

    /**
     * Creates the Comm-Link Dara Array from Metadata.
     */
    private function getCommLinkData(): array
    {
        $metaData = (new Metadata($this->crawler))->getMetaData();

        $metaData->put(
            'created_at_file',
            $this->createTimestampFromFile(
                $this->getFirstCommLinkFileName()
            ) ?? Metadata::DEFAULT_CREATION_DATE
        );

        return [
            'title' => $metaData->get('title'),
            'comment_count' => $metaData->get('comment_count'),
            'url' => $metaData->get('url'),
            'file' => $this->file,
            'channel_id' => $metaData->get('channel_id'),
            'category_id' => $metaData->get('category_id'),
            'series_id' => $metaData->get('series_id'),
            'created_at' => $metaData->get('created_at'),
            'created_at_file' => $metaData->get('created_at_file'),
        ];
    }

    /**
     * Adds or Updates the default english Translation to the Comm-Link.
     */
    private function addEnglishCommLinkTranslation(CommLink $commLink): void
    {
        $contentParser = new Content($this->crawler);
        $commLink->translations()->updateOrCreate(
            [
                'locale_code' => Language::ENGLISH,
            ],
            [
                'translation' => $contentParser->getContent(),
                'proofread' => true,
            ]
        );
    }

    /**
     * Syncs extracted Comm-Link Image Ids.
     */
    private function syncImageIds(CommLink $commLink): void
    {
        $imageParser = new Image($this->crawler);
        $commLink->images()->sync($imageParser->getImageIds());
    }

    /**
     * Syncs extracted Comm-Link Link Ids.
     */
    private function syncLinkIds(CommLink $commLink): void
    {
        $linkParser = new Link($this->crawler);
        $commLink->links()->sync($linkParser->getLinkIds());
    }

    /**
     * Checks if Content of current Comm-Link has Changed
     * Updates Metadata.
     */
    private function checkCommLinkForChanges(): void
    {
        $data = $this->getCommLinkData();

        if ($this->contentHasChanged()) {
            $hadContent = true;
            if (optional($this->commLinkModel->english())->translation === null) {
                $hadContent = false;
            } else {
                // Don't update the current File if Content has Changed and Translation is not null
                unset($data['file']);
            }

            $this->addEnglishCommLinkTranslation($this->commLinkModel);

            CommLinksChanged::create(
                [
                    'comm_link_id' => $this->commLinkModel->id,
                    'had_content' => $hadContent,
                    'type' => 'update',
                ]
            );
        }

        $dateMetadata = Carbon::parse($data['created_at']);
        $dateMetadataFile = Carbon::parse($data['created_at_file']);
        $dateMetadataFile->setSecond(0);
        $dateMetadataFile->setMinutes(0);

        if ($dateMetadata->diffInHours($dateMetadataFile) <= 24 || $data['created_at'] === Metadata::DEFAULT_CREATION_DATE) {
            $data['created_at'] = $dateMetadataFile;
        }

        $this->commLinkModel->update($data);
        $this->syncImageIds($this->commLinkModel);
        $this->syncLinkIds($this->commLinkModel);
    }

    /**
     * Checks if Local Content is Equal to DB Content.
     */
    private function contentHasChanged(): bool
    {
        $contentParser = new Content($this->crawler);

        return $contentParser->getContent() !== (optional($this->commLinkModel->english())->translation ?? '');
    }

    /**
     * Returns the first file in a comm-link folder or null
     */
    private function getFirstCommLinkFileName(): ?string
    {
        $filename = Arr::first(Storage::disk(DownloadCommLink::DISK)->allFiles($this->commLinkId));

        return str_replace(sprintf('%d/', $this->commLinkId), '', $filename);
    }

    /**
     * Creates a timestamp from a comm-link filename
     */
    private function createTimestampFromFile(string $file): ?string
    {
        try {
            return Carbon::createFromFormat('Y-m-d_His\.\h\t\m\l', $file)->format('Y-m-d H:i:s');
        } catch (InvalidFormatException $e) {
            return null;
        }
    }
}
