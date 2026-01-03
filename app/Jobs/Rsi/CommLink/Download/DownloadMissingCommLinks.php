<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Download;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class DownloadMissingCommLinks implements ShouldQueue
{
    use Queueable;

    public const FIRST_COMM_LINK_ID = 12663;

    public int $timeout = 120;

    public function handle(): void
    {
        Log::info('Starting Comm-Link missing download scan.');

        $response = Http::timeout(60)->get($this->hubUrl());

        if ($response->serverError()) {
            Log::warning('Comm-Link hub request failed with server error.', [
                'status' => $response->status(),
            ]);

            $this->release(300);

            return;
        }

        if ($response->clientError()) {
            Log::info('Comm-Link hub request failed with client error.', [
                'status' => $response->status(),
            ]);

            return;
        }

        $postIds = $this->extractPostIds($response->body());

        if ($postIds === []) {
            Log::info('Comm-Link hub returned no IDs.');
            $this->release(60);

            return;
        }

        $latestPostId = max($postIds);
        $latestDbId = CommLink::query()->max('cig_id') ?? self::FIRST_COMM_LINK_ID - 1;

        foreach ($postIds as $postId) {
            dispatch(new DownloadCommLink($postId, true));
        }

        $startId = max(self::FIRST_COMM_LINK_ID, $latestDbId + 1);
        for ($id = $startId; $id <= $latestPostId; $id++) {
            dispatch(new DownloadCommLink($id, true));
        }
    }

    private function hubUrl(): string
    {
        return rtrim((string) config('services.rsi_url'), '/').'/comm-link';
    }

    /**
     * @return array<int, int>
     */
    private function extractPostIds(string $body): array
    {
        $crawler = new Crawler;
        $crawler->addHtmlContent($body, 'UTF-8');

        $ids = $crawler->filter('#channel .hub-blocks .hub-block')
            ->each(function (Crawler $crawler): int {
                $href = $crawler->filter('a')->attr('href');

                if ($href === null) {
                    return 0;
                }

                $segments = explode('/', $href);
                $slug = end($segments) ?: '';
                $parts = explode('-', $slug);

                return (int) ($parts[0] ?? 0);
            });

        return collect($ids)
            ->filter(static fn (int $id) => $id >= self::FIRST_COMM_LINK_ID)
            ->values()
            ->all();
    }
}
