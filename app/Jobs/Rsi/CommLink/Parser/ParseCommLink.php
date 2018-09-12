<?php declare(strict_types = 1);

namespace App\Jobs\Rsi\CommLink\Parser;

use App\Jobs\Rsi\CommLink\Parser\Element\Content;
use App\Jobs\Rsi\CommLink\Parser\Element\Image;
use App\Jobs\Rsi\CommLink\Parser\Element\Link;
use App\Jobs\Rsi\CommLink\Parser\Element\Metadata;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\CommLinkChanged;
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
     * Comm Link Post CSS Selector
     */
    const POST_SELECTOR = '#post';

    /**
     * @var int Comm Link ID
     */
    private $commLinkId;

    /**
     * @var string File in the Comm Link ID Folder
     */
    private $file;

    /**
     * @var \App\Models\Rsi\CommLink\CommLink
     */
    private $commLinkModel;

    /**
     * True if the given file content should be imported into the comm link model
     *
     * @var bool
     */
    private $forceImport;

    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    private $crawler;

    /**
     * Create a new job instance.
     *
     * @param int                                    $id          Comm Link ID
     * @param string                                 $file        Current File Name
     * @param \App\Models\Rsi\CommLink\CommLink|null $commLink    Optional Comm Link Model to update
     * @param bool                                   $forceImport Flag to Force Import from current file
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
     * @return void
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $content = Storage::disk('comm_links')->get($this->filePath());
        $this->crawler = new Crawler();
        $this->crawler->addHtmlContent($content, 'UTF-8');

        $content = $this->crawler->filter(self::POST_SELECTOR);

        if ($content->count() === 0) {
            app('Log')::info("Comm-Link with id {$this->commLinkId} has no content");

            return;
        }

        if (null === $this->commLinkModel || $this->forceImport) {
            $this->createCommLink(); // Updates or Creates
        } else {
            $this->checkCommLinkForChanges();
        }
    }

    /**
     * Updates or Creates a Comm Link Model and populates it
     */
    private function createCommLink()
    {
        /** @var \App\Models\Rsi\CommLink\CommLink $commLink */
        $commLink = CommLink::updateOrCreate(
            [
                'cig_id' => $this->commLinkId,
            ],
            $this->getCommLinkData()
        );

        $this->addEnglishCommLinkTranslation($commLink);
        $this->syncImageIds($commLink);
        $this->syncLinkIds($commLink);

        CommLinkChanged::create(
            [
                'comm_link_id' => $this->commLinkModel->id,
                'had_content' => false,
                'type' => 'creation',
            ]
        );
    }

    /**
     * Creates the Comm Link Dara Array from Metadata
     *
     * @return array
     */
    private function getCommLinkData()
    {
        $metaData = (new Metadata($this->crawler))->getMetaData();

        return [
            'title' => $metaData->get('title'),
            'comment_count' => $metaData->get('comment_count'),
            'url' => $metaData->get('url'),
            'file' => $this->file,
            'channel_id' => $metaData->get('channel_id'),
            'category_id' => $metaData->get('category_id'),
            'series_id' => $metaData->get('series_id'),
            'created_at' => $metaData->get('created_at'),
        ];
    }

    /**
     * Adds or Updates the default english Translation to the Comm Link
     *
     * @param \App\Models\Rsi\CommLink\CommLink $commLink
     */
    private function addEnglishCommLinkTranslation(CommLink $commLink)
    {
        $contentParser = new Content($this->crawler);
        $commLink->translations()->updateOrCreate(
            [
                'locale_code' => 'en_EN',
            ],
            [
                'translation' => $contentParser->getContent(),
            ]
        );
    }

    /**
     * Syncs extracted Comm Link Image Ids
     *
     * @param \App\Models\Rsi\CommLink\CommLink $commLink
     */
    private function syncImageIds(CommLink $commLink)
    {
        $imageParser = new Image($this->crawler);
        $commLink->images()->sync($imageParser->getImageIds());
    }

    /**
     * Syncs extrated Comm Link Link Ids
     *
     * @param \App\Models\Rsi\CommLink\CommLink $commLink
     */
    private function syncLinkIds(CommLink $commLink)
    {
        $linkParser = new Link($this->crawler);
        $commLink->links()->sync($linkParser->getLinkIds());
    }

    /**
     * @return string Path to Comm Link File
     */
    private function filePath(): string
    {
        return "{$this->commLinkId}/{$this->file}";
    }

    /**
     * Checks if Content of current Comm Link has Changed
     * Updates Metadata
     */
    private function checkCommLinkForChanges()
    {
        $data = $this->getCommLinkData();

        if ($this->contentHasChanged()) {
            $hadContent = true;
            if (null === optional($this->commLinkModel->english())->translation) {
                $this->addEnglishCommLinkTranslation($this->commLinkModel);
                $hadContent = false;
            } else {
                // Don't update the current File if Content has Changed and Translation is not null
                unset($data['file']);
            }

            CommLinkChanged::create(
                [
                    'comm_link_id' => $this->commLinkModel->id,
                    'had_content' => $hadContent,
                    'type' => 'update',
                ]
            );
        }

        $this->commLinkModel->update($data);
        $this->syncImageIds($this->commLinkModel);
        $this->syncLinkIds($this->commLinkModel);
    }

    /**
     * Checks if Local Content is Equal to DB Content
     *
     * @return bool
     */
    private function contentHasChanged()
    {
        $contentParser = new Content($this->crawler);

        return $contentParser->getContent() !== optional($this->commLinkModel->english())->translation;
    }
}
