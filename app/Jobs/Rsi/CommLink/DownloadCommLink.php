<?php declare(strict_types = 1);

namespace App\Jobs\Rsi\CommLink;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

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
        $scraper = new Client();
        $scraper->setClient($this->client);

        $response = $scraper->request('GET', self::COMM_LINK_BASE_URL.'/SCW/'.$this->postId.self::POST_SLUG);

        try {
            $content = $this->cleanResponse($response->html());
        } catch (\InvalidArgumentException $e) {
            $this->fail($e);

            return;
        }

        if (!str_contains($content, 'id="post"')) {
            app('Log')::info("Comm-Link with ID {$this->postId} does not exist");

            return;
        }

        $fileName = sprintf('%d/%s.html', $this->postId, Carbon::now()->format('Y-m-d_His'));

        if (Storage::disk('comm_links')->exists($this->postId)) {
            $file = scandir(Storage::disk('comm_links')->path($this->postId), SCANDIR_SORT_DESCENDING)[0];
            $localHtml = new Crawler(Storage::disk('comm_links')->get($this->postId.'/'.$file));

            if (!$this->contentHasChanged($localHtml, $response)) {
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

    /**
     * Compares the white space removed content of two '#bodyWrapper' Elements
     *
     * @param \Symfony\Component\DomCrawler\Crawler $localFile
     * @param \Symfony\Component\DomCrawler\Crawler $websiteFile
     *
     * @return bool
     */
    private function contentHasChanged(Crawler $localFile, Crawler $websiteFile)
    {
        try {
            $presentHash = md5($this->removeWhiteSpace($this->extractWrapperContent($localFile)));
        } catch (\InvalidArgumentException $e) {
            app('Log')::warning("Content of Local Comm-Link {$this->postId} has no Body Wrapper");
            $this->fail($e);

            return false;
        }

        try {
            $onlineHash = md5($this->removeWhiteSpace($this->extractWrapperContent($websiteFile)));
        } catch (\InvalidArgumentException $e) {
            app('Log')::warning("Content of Online Comm-Link {$this->postId} has no Body Wrapper");
            $this->fail($e);

            return false;
        }

        if ($presentHash === $onlineHash) {
            app('Log')::debug("Content of Comm-Link {$this->postId} has not changed");

            return false;
        }

        return true;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    private function removeWhiteSpace(string $content)
    {
        return preg_replace('/\s/', '', $content);
    }

    /**
     * Extracts the '#bodyWrapper' Element from a Crawler
     *
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     *
     * @return string
     */
    private function extractWrapperContent(Crawler $crawler)
    {
        return $crawler->filter('#bodyWrapper')->html();
    }
}
