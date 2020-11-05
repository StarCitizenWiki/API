<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Download;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use App\Models\Rsi\CommLink\CommLink;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Download all missing Comm-Links based on the last DB entry.
 * Extracts the highest Comm-Link-Id from 'https://robertsspaceindustries.com/comm-link'
 * And Dispatches download-jobs for ID - DB_ID
 *
 * If No Comm-Link was found in the DB, the first Comm-Link ID (12663) will be used.
 *
 * Existing Comm-Links are skipped.
 */
class DownloadMissingCommLinks extends BaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const FIRST_COMM_LINK_ID = 12663;
    public const COMM_LINK_BASE_URL = 'https://robertsspaceindustries.com/comm-link';

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info('Starting Missing Comm-Links Download Job');

        $this->initClient();
        // $this->getRsiAuthCookie();

        $postIDs = [];

        $crawler = new Crawler();

        try {
            $response = self::$client->get(self::COMM_LINK_BASE_URL);
        } catch (GuzzleException $e) {
            app('Log')::error('Could not connect to RSI, retrying in 5 minutes.');
            $this->release(300);

            return;
        }

        $crawler->addHtmlContent((string)$response->getBody(), 'UTF-8');
        $crawler->filter('#channel .hub-blocks .hub-block')->each(
            function (Crawler $crawler) use (&$postIDs) {
                $link = $crawler->filter('a');
                $postIDs[] = $this->extractLatestPostId($link);
            }
        );

        if (empty($postIDs)) {
            app('Log')::info('Could not retrieve latest Comm-Link ID, retrying in 1 minute.');
            $this->release(60);

            return;
        }

        $latestPostId = max($postIDs);

        app('Log')::info(
            "Latest Comm-Link ID is: {$latestPostId}",
            [
                'id' => $latestPostId,
            ]
        );

        try {
            $dbIds = CommLink::query()
                ->select('cig_id')
                ->take(count($postIDs))
                ->orderByDesc('cig_id')
                ->get()
                ->pluck('cig_id');
        } catch (ModelNotFoundException $e) {
            $dbIds = collect([self::FIRST_COMM_LINK_ID - 1]);
        }

        $missing = collect($postIDs)->diff($dbIds);

        $dbId = $dbIds->max();
        $dbId++;

        app('Log')::info(
            "Latest DB Comm-Link ID is: {$dbId}",
            [
                'id' => $dbId,
            ]
        );

        $missing->each(
            function (int $id) {
                dispatch(new DownloadCommLink($id, true));
            }
        );

        for ($id = $dbId; $id <= $latestPostId; $id++) {
            if (!$missing->contains($id)) {
                dispatch(new DownloadCommLink($id, true));
            }
        }
    }

    /**
     * Extract latest Comm-Link id from Website
     *
     * @param Crawler $link
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

        return (int)$linkHref[0];
    }
}
