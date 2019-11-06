<?php

namespace App\Jobs\CommLink;

use App\Jobs\Rsi\CommLink\Parser\Element\Image;
use App\Jobs\Rsi\CommLink\Parser\ParseCommLink;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

class SyncImageIds implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var int Comm-Link ID
     */
    private $commLinkId;

    /**
     * @var string File in the Comm-Link ID Folder
     */
    private $file;

    /**
     * @var CommLink
     */
    private $commLinkModel;

    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * Create a new job instance.
     *
     * @param int      $id       Comm-Link ID
     * @param string   $file     Current File Name
     * @param CommLink $commLink Optional Comm-Link Model to update
     */
    public function __construct(int $id, string $file, CommLink $commLink)
    {
        $this->commLinkId = $id;
        $this->file = $file;
        $this->commLinkModel = $commLink;
    }

    /**
     * Execute the job.
     *
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        app('Log')::info(
            "Syncing Image ids for Comm-Link {$this->commLinkId}",
            [
                'id' => $this->commLinkId,
                'file' => $this->file,
            ]
        );

        $content = Storage::disk('comm_links')->get("{$this->commLinkId}/{$this->file}");
        $this->crawler = new Crawler();
        $this->crawler->addHtmlContent($content, 'UTF-8');

        $post = $this->crawler->filter(ParseCommLink::POST_SELECTOR);
        $subscribers = $this->crawler->filter(ParseCommLink::SUBSCRIBERS_SELECTOR);

        if (0 === $post->count() && 0 === $subscribers->count()) {
            app('Log')::info("Comm-Link with id {$this->commLinkId} has no content");

            return;
        }

        $this->syncImages($this->commLinkModel);
    }

    /**
     * Syncs extracted Comm-Link Image Ids.
     *
     * @param CommLink $commLink
     */
    private function syncImages(CommLink $commLink): void
    {
        $imageParser = new Image($this->crawler);
        $commLink->images()->sync($imageParser->getImageIds());
    }
}
