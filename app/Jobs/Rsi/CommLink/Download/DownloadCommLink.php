<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Download;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
    public const DISK = 'comm_links';

    /**
     * @var int Post ID
     */
    private int $commLinkId = 0;

    private bool $skipExisting = false;

    /**
     * Create a new job instance.
     *
     * @param int  $commLinkId
     * @param bool $skipExisting
     */
    public function __construct(int $commLinkId, bool $skipExisting = false)
    {
        $this->commLinkId = $commLinkId;
        $this->skipExisting = $skipExisting;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if ($this->skipExisting && Storage::disk(self::DISK)->exists($this->commLinkId)) {
            app('Log')::debug(
                "Skipping existing Comm-Link {$this->commLinkId}",
                [
                    'id' => $this->commLinkId,
                ]
            );

            return;
        }

        app('Log')::info(
            "Downloading Comm-Link with ID {$this->commLinkId}",
            [
                'id' => $this->commLinkId,
            ]
        );

        $response = $this->makeClient()->get(
            sprintf('%s/%s/%d-IMPORT', self::COMM_LINK_BASE_URL, 'SCW', $this->commLinkId)
        );

        if (!$response->successful()) {
            $this->fail(new RequestException($response));

            return;
        }

        $content = $this->removeRsiToken($response->body());

        if (!Str::contains($content, ['id="post"', 'id="subscribers"', 'id="layout-system"'])) {
            app('Log')::info(
                "Comm-Link with ID {$this->commLinkId} does not exist",
                [
                    'id' => $this->commLinkId,
                ]
            );

            return;
        }

        $this->writeFile($content);
    }

    /**
     * Strips the X-RSI Token from the Page.
     *
     * @param string $content
     *
     * @return string
     */
    private function removeRsiToken(string $content): string
    {
        return preg_replace('/\'token\'\s?\:\s?\'[A-Za-z0-9\+\:\-\_\/]+\'/', '\'token\' : \'\'', $content);
    }

    /**
     * Write the Comm-Link to disk
     *
     * @param string $content
     */
    private function writeFile(string $content): void
    {
        Storage::disk(self::DISK)->put(
            sprintf('%d/%s.html', $this->commLinkId, Carbon::now()->format('Y-m-d_His')),
            $content
        );
    }
}
