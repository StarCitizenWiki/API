<?php declare(strict_types = 1);

namespace App\Jobs\Rsi\CommLink;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use Carbon\Carbon;
use GuzzleHttp\Exception\ConnectException;
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

    const POST_SLUG = '-IMPORT';
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
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $this->initClient();

        try {
            $response = $this->client->get(
                self::COMM_LINK_BASE_URL.'/SCW/'.$this->postId.self::POST_SLUG
            );
        } catch (ConnectException $e) {
            $this->fail($e);

            return;
        }

        $content = $this->cleanResponse($response->getBody()->getContents());

        if (!str_contains($content, 'id="post"')) {
            app('Log')::info("Comm-Link with ID {$this->postId} does not exist");

            return;
        }

        $fileName = sprintf('%d/%s.html', $this->postId, Carbon::now()->format('Y-m-d_His'));

        if (Storage::disk('comm_links')->exists($this->postId)) {
            $file = scandir(Storage::disk('comm_links')->path($this->postId), SCANDIR_SORT_DESCENDING)[0];
            $presentHash = md5(preg_replace('/\s/', '', Storage::disk('comm_links')->get($this->postId.'/'.$file)));

            // CIG gibt teils Versionen mit unterschiedlichem Whitespace heraus, daher Vergleich ohne Whitespace
            if ($presentHash === md5(preg_replace('/\s/', '', $content))) {
                app('Log')::debug("Content of Comm-Link {$this->postId} has not changed");

                return;
            }

            app('Log')::info("Content of Comm-Link {$this->postId} has changed since last download");
        }

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
