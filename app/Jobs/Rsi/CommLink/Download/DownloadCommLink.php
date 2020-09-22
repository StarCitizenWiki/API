<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Download;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Downloads the Whole Page Content.
 */
class DownloadCommLink extends BaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const COMM_LINK_BASE_URL = 'https://robertsspaceindustries.com/comm-link';

    /**
     * @var int Post ID
     */
    private $postId;

    private $skipExisting;

    /**
     * Create a new job instance.
     *
     * @param int  $id
     * @param bool $skipExisting
     */
    public function __construct(int $id, bool $skipExisting = false)
    {
        $this->postId = $id;
        $this->skipExisting = $skipExisting;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if ($this->skipExisting && Storage::disk('comm_links')->exists($this->postId)) {
            app('Log')::debug(
                "Skipping existing Comm-Link {$this->postId}",
                [
                    'id' => $this->postId,
                ]
            );

            return;
        }

        app('Log')::info(
            "Downloading Comm-Link with ID {$this->postId}",
            [
                'id' => $this->postId,
            ]
        );

        if (null === self::$scraper) {
            $this->makeScraper(true);
        }

        $response = self::$scraper->request(
            'GET',
            sprintf('%s/%s/%d-IMPORT', self::COMM_LINK_BASE_URL, 'SCW', $this->postId)
        );

        try {
            $content = $this->cleanResponse($response->html());
        } catch (InvalidArgumentException $e) {
            $this->fail($e);

            return;
        }

        if (!Str::contains($content, ['id="post"', 'id="subscribers"', 'id="layout-system"'])) {
            app('Log')::info(
                "Comm-Link with ID {$this->postId} does not exist",
                [
                    'id' => $this->postId,
                ]
            );

            return;
        }

        $fileName = sprintf('%d/%s.html', $this->postId, Carbon::now()->format('Y-m-d_His'));

        Storage::disk('comm_links')->put($fileName, $content);
    }

    /**
     * Strips the X-RSI Token from the Page.
     *
     * @param string $content
     *
     * @return string
     */
    private function cleanResponse(string $content): string
    {
        return preg_replace('/\'token\'\s?\:\s?\'[A-Za-z0-9\+\:\-\_\/]+\'/', '\'token\' : \'\'', $content);
    }
}
