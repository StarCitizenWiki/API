<?php declare(strict_types = 1);

namespace App\Jobs\Rsi\CommLink;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use App\Jobs\Rsi\CommLink\Parser\ParseCommLinkDownload;
use App\Models\Rsi\CommLink\CommLink;
use Goutte\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Download all missing Comm-Links based on last DB entry
 */
class DownloadMissingCommLinks extends BaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const FIRST_COMM_LINK_ID = 12663;
    const COMM_LINK_BASE_URL = 'https://robertsspaceindustries.com/comm-link';

    /**
     * @var \Goutte\Client
     */
    private $scraper;

    /**
     * Create a new job instance.
     *
     * @param \Goutte\Client $client
     */
    public function __construct(Client $client)
    {
        $this->scraper = $client;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->initClient();
        $this->scraper->setClient($this->client);

        /** @var \Symfony\Component\DomCrawler\Crawler $crawler */
        $crawler = $this->scraper->request('GET', self::COMM_LINK_BASE_URL);
        $latestPost = $crawler->filter('.hub-blocks > a')->first();
        $latestPostId = $this->extractLatestPostId($latestPost);

        try {
            $dbId = CommLink::orderByDesc('cig_id')->firstOrFail()->cig_id;
        } catch (ModelNotFoundException $e) {
            $dbId = self::FIRST_COMM_LINK_ID;
        }

        for ($id = $dbId; $id <= $latestPostId; $id++) {
            dispatch(new DownloadCommLink(($id)))->onQueue('comm_links');
        }

        dispatch(new ParseCommLinkDownload($dbId));
    }

    /**
     * Extract latest Comm-Link id from Website
     *
     * @param \Symfony\Component\DomCrawler\Crawler $link
     *
     * @return int
     */
    private function extractLatestPostId(Crawler $link): int
    {
        $linkHref = $link->attr('href');
        $linkHref = explode('/', $linkHref);
        $linkHref = end($linkHref);
        $linkHref = explode('-', $linkHref);

        return intval($linkHref[0]);
    }
}
