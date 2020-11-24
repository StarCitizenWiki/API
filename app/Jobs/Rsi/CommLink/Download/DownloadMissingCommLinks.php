<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Download;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use App\Models\Rsi\CommLink\CommLink;
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

        $response = $this->makeClient()->get(self::COMM_LINK_BASE_URL);

        if (!$response->successful()) {
            app('Log')::error('Could not connect to RSI, retrying in 5 minutes.');
            $this->release(300);

            return;
        }

        $postIds = $this->extractPostIds($response->body());

        if (empty($postIds)) {
            app('Log')::info('Could not retrieve latest Comm-Link ID, retrying in 1 minute.');
            $this->release(60);

            return;
        }

        $latestPostId = max($postIds);

        app('Log')::info(
            "Latest Comm-Link ID is: {$latestPostId}",
            [
                'id' => $latestPostId,
            ]
        );

        $this->downloadCommLinks($postIds);
    }

    /**
     * Extracts Post ids from html
     *
     * @param string $body
     *
     * @return array Ids
     */
    private function extractPostIds(string $body): array
    {
        $postIds = [];

        $crawler = new Crawler();

        $crawler->addHtmlContent($body, 'UTF-8');
        $crawler->filter('#channel .hub-blocks .hub-block')
            ->each(
                function (Crawler $crawler) use (&$postIds) {
                    $link = $crawler->filter('a');
                    $postIds[] = $this->extractIdFromLink($link);
                }
            );

        return $postIds;
    }

    /**
     * Extract latest Comm-Link id from Website
     *
     * @param Crawler $link
     *
     * @return int
     */
    private function extractIdFromLink(Crawler $link): int
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

    /**
     * Dispatches download jobs for all missing ids
     *
     * @param array $postIDs
     */
    private function downloadCommLinks(array $postIDs): void
    {
        $latestPostId = max($postIDs);

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

        $missing->each(
            function (int $id) {
                dispatch(new DownloadCommLink($id, true));
            }
        );

        $dbId = $dbIds->max();
        if ($dbId > 0) {
            app('Log')::info(
                "Latest DB Comm-Link ID is: {$dbId}",
                [
                    'id' => $dbId,
                ]
            );
            $dbId++;
        } else {
            app('Log')::info('No Comm-Links in DB found');
            $dbId = self::FIRST_COMM_LINK_ID;
        }

        for ($id = $dbId; $id <= $latestPostId; $id++) {
            if (!$missing->contains($id)) {
                dispatch(new DownloadCommLink($id, true));
            }
        }
    }
}
