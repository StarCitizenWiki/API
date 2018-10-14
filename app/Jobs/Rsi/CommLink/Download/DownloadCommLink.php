<?php declare(strict_types = 1);

namespace App\Jobs\Rsi\CommLink\Download;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Downloads the Whole Page Content
 */
class DownloadCommLink extends BaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const COMM_LINK_BASE_URL = 'https://robertsspaceindustries.com/comm-link';

    /**
     * @var int Post ID
     */
    private $postId;

    /**
     * Create a new job instance.
     *
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->postId = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info(
            "Downloading Comm-Link with ID {$this->postId}",
            [
                'id' => $this->postId,
            ]
        );

        $this->initClient();
        $scraper = new Client();
        $scraper->setClient($this->client);

        $response = $scraper->request(
            'GET',
            sprintf('%s/%s/%d-IMPORT', self::COMM_LINK_BASE_URL, 'SCW', $this->postId)
        );

        try {
            $content = $this->cleanResponse($response->html());
        } catch (\InvalidArgumentException $e) {
            $this->fail($e);

            return;
        }

        if (!str_contains($content, 'id="post"')) {
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
     * Strips the X-RSI Token from the Page
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
