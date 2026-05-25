<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Download;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DownloadMissingCommLinks implements ShouldQueue
{
    use Queueable;

    public const int FIRST_COMM_LINK_ID = 12663;

    private const string ID_PATTERN = '#/comm-link/(?:[a-z0-9-]+)?/(\d+)-#';

    /**
     * Sentinel string present in hub API responses past the last page.
     */
    private const string END_SENTINEL = 'no-results';

    public int $timeout = 600;

    /**
     * Paginate through the hub API to discover all Comm-Link IDs,
     * then dispatch download jobs for any missing IDs.
     */
    public function handle(): void
    {
        Log::info('Starting Comm-Link missing download scan via hub API.');

        $postIds = $this->collectAllIds();

        if ($postIds === []) {
            Log::info('Comm-Link hub API returned no IDs.');

            return;
        }

        Log::info('Comm-Link hub API scan complete.', [
            'total_ids' => count($postIds),
            'min_id' => min($postIds),
            'max_id' => max($postIds),
        ]);

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

    /**
     * Paginate through all hub API pages and collect unique Comm-Link IDs.
     *
     * @return array<int, int>
     */
    private function collectAllIds(): array
    {
        $allIds = [];
        $page = 1;

        while (true) {
            $data = $this->fetchPage($page);

            if ($data === null) {
                break;
            }

            if (str_contains($data, self::END_SENTINEL)) {
                break;
            }

            $pageIds = $this->extractIdsFromData($data);
            $allIds = [...$allIds, ...$pageIds];

            $page++;
            usleep(100_000);
        }

        return array_values(array_unique($allIds));
    }

    /**
     * Fetch a single page from the hub API.
     * Returns the HTML data string on success, or null on error.
     */
    private function fetchPage(int $page): ?string
    {
        $response = Http::timeout(60)->asForm()->post($this->hubApiUrl(), [
            'page' => $page,
        ]);

        if ($response->serverError()) {
            Log::warning('Comm-Link hub API request failed with server error.', [
                'page' => $page,
                'status' => $response->status(),
            ]);

            $this->release(300);

            return null;
        }

        if ($response->clientError()) {
            Log::info('Comm-Link hub API request failed with client error.', [
                'page' => $page,
                'status' => $response->status(),
            ]);

            return null;
        }

        if (! $response->json('success')) {
            Log::warning('Comm-Link hub API returned unsuccessful response.', [
                'page' => $page,
            ]);

            return null;
        }

        return $response->json('data', '');
    }

    private function hubApiUrl(): string
    {
        return rtrim((string) config('services.rsi_url'), '/').'/api/hub/getCommlinkItems';
    }

    /**
     * Extract CIG IDs from hub API HTML data.
     *
     * @return array<int, int>
     */
    private function extractIdsFromData(string $data): array
    {
        preg_match_all(self::ID_PATTERN, $data, $matches);

        return collect($matches[1] ?? [])
            ->map(static fn (string $id) => (int) $id)
            ->filter(static fn (int $id) => $id >= self::FIRST_COMM_LINK_ID)
            ->values()
            ->all();
    }
}
