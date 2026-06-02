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

    public int $timeout = 120;

    public function handle(): void
    {
        Log::info('Starting Comm-Link missing download scan via latest hub API page.');

        $data = $this->fetchLatestPage();

        if ($data === null) {
            return;
        }

        $postIds = $this->extractIdsFromData($data);

        if ($postIds === []) {
            Log::info('Comm-Link hub API returned no IDs.');

            return;
        }

        $candidateIds = $this->candidateDownloadIds($postIds);

        Log::info('Comm-Link latest hub API page scan complete.', [
            'api_ids' => count($postIds),
            'candidate_ids' => count($candidateIds),
            'max_api_id' => max($postIds),
        ]);

        foreach ($candidateIds as $postId) {
            dispatch(new DownloadCommLink($postId, true));
        }
    }

    /**
     * Fetch the latest hub API page.
     * Returns the HTML data string on success, or null on error.
     */
    private function fetchLatestPage(): ?string
    {
        $response = Http::timeout(60)->asForm()->post($this->hubApiUrl(), [
            'page' => 1,
        ]);

        if ($response->serverError()) {
            Log::warning('Comm-Link hub API request failed with server error.', [
                'page' => 1,
                'status' => $response->status(),
            ]);

            $this->release(300);

            return null;
        }

        if ($response->clientError()) {
            Log::info('Comm-Link hub API request failed with client error.', [
                'page' => 1,
                'status' => $response->status(),
            ]);

            return null;
        }

        if (! $response->json('success')) {
            Log::warning('Comm-Link hub API returned unsuccessful response.', [
                'page' => 1,
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
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Build the bounded set of IDs to try in the frequent missing-download job.
     *
     * This includes IDs from the latest API page that are absent from the DB and
     * the numeric gap between the current max DB ID and latest listed API ID.
     *
     * @param  array<int, int>  $apiIds
     * @return array<int, int>
     */
    private function candidateDownloadIds(array $apiIds): array
    {
        $apiIds = collect($apiIds)->unique()->values();
        $latestApiId = $apiIds->max();

        if ($latestApiId === null) {
            return [];
        }

        $existingApiIds = CommLink::query()
            ->whereIn('cig_id', $apiIds)
            ->pluck('cig_id');

        $missingApiIds = $apiIds->diff($existingApiIds);
        $latestDbId = CommLink::query()->max('cig_id') ?? self::FIRST_COMM_LINK_ID - 1;
        $gapStartId = max(self::FIRST_COMM_LINK_ID, $latestDbId + 1);
        $gapIds = $gapStartId <= $latestApiId ? range($gapStartId, $latestApiId) : [];

        return $missingApiIds
            ->merge($gapIds)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
