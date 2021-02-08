<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink;

use App\Jobs\Rsi\CommLink\Import\ImportCommLink;
use App\Models\Rsi\CommLink\CommLink;
use App\Services\Parser\CommLink\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

class SyncImageId implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var CommLink
     */
    private CommLink $commLink;

    /**
     * @var Crawler
     */
    private Crawler $crawler;

    /**
     * Create a new job instance.
     *
     * @param CommLink $commLink
     */
    public function __construct(CommLink $commLink)
    {
        $this->commLink = $commLink;
    }

    /**
     * Execute the job.
     *
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        app('Log')::info(
            "Syncing Image ids for Comm-Link {$this->commLink->cig_id}",
            [
                'id' => $this->commLink->cig_id,
                'file' => $this->commLink->file,
            ]
        );

        $content = Storage::disk('comm_links')->get("{$this->commLink->cig_id}/{$this->commLink->file}");
        $this->crawler = new Crawler();
        $this->crawler->addHtmlContent($content, 'UTF-8');

        $post = $this->crawler->filter(ImportCommLink::POST_SELECTOR);
        $subscribers = $this->crawler->filter(ImportCommLink::SUBSCRIBERS_SELECTOR);

        if (0 === $post->count() && 0 === $subscribers->count()) {
            app('Log')::info("Comm-Link with id {$this->commLink->cig_id} has no content");

            return;
        }

        $this->syncImages();
    }

    /**
     * Syncs extracted Comm-Link Image Ids.
     */
    private function syncImages(): void
    {
        $imageParser = new Image($this->crawler);
        $this->commLink->images()->sync($imageParser->getImageIds());
    }
}
