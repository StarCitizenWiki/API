<?php declare(strict_types = 1);

namespace App\Jobs\Rsi\CommLink\Download;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info('Starting Missing Comm-Links Download Job');

        $this->initClient(false);
        $this->getRsiAuthCookie();

        self::$scraper = new Client();
        self::$scraper->setClient(self::$client);
        $this->addGuzzleCookiesToScraper(self::$scraper);

        $postIDs = [];

        /** @var \Symfony\Component\DomCrawler\Crawler $crawler */
        $crawler = self::$scraper->request('GET', self::COMM_LINK_BASE_URL);
        $crawler->filter('#channel .hub-blocks .hub-block')->each(
            function (Crawler $crawler) use (&$postIDs) {
                $link = $crawler->filter('a');
                $postIDs[] = $this->extractLatestPostId($link);
            }
        );

        $latestPostId = max($postIDs);

        app('Log')::info(
            "Latest Comm-Link ID is: {$latestPostId}",
            [
                'id' => $latestPostId,
            ]
        );

        try {
            $dbId = CommLink::orderByDesc('cig_id')->firstOrFail()->cig_id++;
        } catch (ModelNotFoundException $e) {
            $dbId = self::FIRST_COMM_LINK_ID;
        }

        app('Log')::info(
            "Latest DB Comm-Link ID is: {$dbId}",
            [
                'id' => $dbId,
            ]
        );

        for ($id = $dbId; $id <= $latestPostId; $id++) {
            dispatch(new DownloadCommLink($id));
        }
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

        if (null === $linkHref) {
            return 0;
        }

        $linkHref = explode('/', $linkHref);
        $linkHref = end($linkHref);
        $linkHref = explode('-', $linkHref);

        return intval($linkHref[0]);
    }
}
