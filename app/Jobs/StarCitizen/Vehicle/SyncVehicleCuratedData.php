<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Vehicle;

use App\Models\Game\Vehicle;
use App\Services\Wiki\VehicleInfoboxParser;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncVehicleCuratedData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    private const string DEFAULT_API_URL = 'https://starcitizen.tools/api.php';

    private const int TITLES_PER_BATCH = 50;

    private const array CATEGORIES = ['Category:Ships', 'Category:Ground vehicles'];

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startedAt = microtime(true);
        $parser = new VehicleInfoboxParser;

        $stats = [
            'scanned' => 0,
            'pages_with_content' => 0,
            'no_template_skipped' => 0,
            'unchanged_skipped' => 0,
            'redirects_skipped' => 0,
            'no_uuid_skipped' => 0,
            'uuid_unmatched' => 0,
            'upserted' => 0,
            'errors' => 0,
        ];

        $titles = $this->enumerateTitles();

        $stats['scanned'] = $titles->count();

        foreach ($titles->chunk(self::TITLES_PER_BATCH) as $batch) {
            $body = $this->apiGet([
                'action' => 'query',
                'prop' => 'revisions',
                'rvslots' => 'main',
                'rvprop' => 'content|ids',
                'redirects' => 1,
                'format' => 'json',
                'formatversion' => 2,
                'titles' => $batch->implode('|'),
            ]);

            $stats['redirects_skipped'] += count($body['query']['redirects'] ?? []);

            foreach ($body['query']['pages'] ?? [] as $page) {
                $this->processPage($page, $parser, $stats);
            }
        }

        $stats['duration'] = round(microtime(true) - $startedAt, 2);

        Log::info('Vehicle curated data sync completed', $stats);
    }

    /**
     * Enumerate all page titles in the ship categories, deduplicated.
     *
     * @return Collection<int, string>
     */
    private function enumerateTitles(): Collection
    {
        $titles = collect();

        foreach (self::CATEGORIES as $category) {
            $continue = null;

            do {
                $query = [
                    'action' => 'query',
                    'generator' => 'categorymembers',
                    'gcmtitle' => $category,
                    'gcmtype' => 'page',
                    'gcmlimit' => 500,
                    'format' => 'json',
                    'formatversion' => 2,
                ];

                if ($continue !== null) {
                    $query['gcmcontinue'] = $continue;
                }

                $body = $this->apiGet($query);

                $titles = $titles->merge(collect($body['query']['pages'] ?? [])->pluck('title'));

                $continue = $body['continue']['gcmcontinue'] ?? null;
            } while ($continue !== null);
        }

        return $titles->unique()->values();
    }

    /**
     * Fetch, parse and upsert a single wiki page
     *
     * @param  array<string, mixed>  $page
     * @param  array<string, int|float>  $stats
     */
    private function processPage(array $page, VehicleInfoboxParser $parser, array &$stats): void
    {
        $content = $page['revisions'][0]['slots']['main']['content'] ?? null;
        $revisionId = $page['revisions'][0]['revid'] ?? null;

        // Missing/invalid pages come back without revisions.
        if (! is_string($content) || $revisionId === null) {
            return;
        }

        $stats['pages_with_content']++;

        try {
            $params = $parser->parse($content);

            if ($params === []) {
                $stats['no_template_skipped']++;

                return;
            }

            $uuid = $params['uuid'] ?? null;

            if (! is_string($uuid) || $uuid === '') {
                Log::debug('Vehicle curated data page without uuid param', ['title' => $page['title'] ?? '']);
                $stats['no_uuid_skipped']++;

                return;
            }

            $vehicle = Vehicle::query()->where('uuid', $uuid)->first();

            if ($vehicle === null) {
                Log::warning('Vehicle curated data uuid has no game vehicle', [
                    'title' => $page['title'] ?? '',
                    'uuid' => $uuid,
                ]);
                $stats['uuid_unmatched']++;

                return;
            }

            $existing = $vehicle->curatedData;

            if ($existing !== null && (int) $existing->wiki_revision_id === (int) $revisionId) {
                $stats['unchanged_skipped']++;

                return;
            }

            $vehicle->curatedData()->updateOrCreate(
                [],
                [
                    ...$this->sanitizeParams($params),
                    'wiki_page_title' => (string) ($page['title'] ?? ''),
                    'wiki_revision_id' => (int) $revisionId,
                    'raw' => $params,
                    'synced_at' => now(),
                ],
            );

            $stats['upserted']++;
        } catch (Throwable $e) {
            $stats['errors']++;
            Log::warning('Vehicle curated data page failed', [
                'title' => $page['title'] ?? '',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Map wiki params to columns
     *
     * @param  array<string, string|null>  $params
     * @return array<string, mixed>
     */
    private function sanitizeParams(array $params): array
    {
        return [
            'trailer_url' => $this->sanitizeText($params['trailerurl'] ?? null),
            'original_pledge_price' => $this->sanitizeCost($params['originalpledgecost'] ?? null),
            'original_warbond_price' => $this->sanitizeCost($params['originalwarbondcost'] ?? null),
            'added_in_version' => $this->sanitizeText($params['addedinversion'] ?? null),
            'concept_date' => $this->sanitizeDate($params['conceptdate'] ?? null),
            'sale_date' => $this->sanitizeDate($params['saledate'] ?? null),
            'retire_date' => $this->sanitizeDate($params['retiredate'] ?? null),
            'pledge_availability' => $this->sanitizeText($params['pledgeavailability'] ?? null),
            'qa_urls' => $this->sanitizeUrlList($params['qaurl'] ?? null),
            'brochure_url' => $this->sanitizeText($params['brochureurl'] ?? null),
            'presentation_urls' => $this->sanitizeUrlList($params['presentationurl'] ?? null),
            'whitleys_guide_url' => $this->sanitizeText($params['whitleysguideurl'] ?? null),
            'galactapedia_url' => $this->sanitizeText($params['galactapediaurl'] ?? null),
        ];
    }

    private function sanitizeText(?string $value): ?string
    {
        if ($value === null || $this->isNestedTemplate($value)) {
            return null;
        }

        return trim(preg_replace('/\[\[[^]]*]]/', '', $value));
    }

    private function sanitizeCost(?string $value): ?int
    {
        if ($value === null || ! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function sanitizeDate(?string $value): ?Carbon
    {
        if ($value === null || $this->isNestedTemplate($value) || preg_match('/^\d+$/', $value) === 1) {
            return null;
        }

        try {
            $date = Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }

        return $date->year >= 2100 ? null : $date;
    }

    /**
     * `;`-separated multi-URL params
     *
     * @return array<int, string>|null
     */
    private function sanitizeUrlList(?string $value): ?array
    {
        if ($value === null) {
            return null;
        }

        return collect(explode(';', $value))
            ->map(fn (string $url): string => trim($url))
            ->filter(fn (string $url): bool => $url !== '')
            ->values()
            ->all();
    }

    /**
     * Nested templates like `{{SDA|2665}}`
     */
    private function isNestedTemplate(string $value): bool
    {
        return str_starts_with($value, '{{');
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function apiGet(array $query): array
    {
        $response = Http::withUserAgent((string) config('services.rsi_user_agent'))
            ->timeout(60)
            ->get($this->apiUrl(), $query)
            ->throw();

        usleep((int) config('images.throttle_microseconds', 200_000));

        return $response->json() ?? [];
    }

    private function apiUrl(): string
    {
        return collect(config('images.sources'))
            ->firstWhere('name', 'starcitizen.tools')['api_url'] ?? self::DEFAULT_API_URL;
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Vehicle curated data sync job failed', [
            'message' => $exception->getMessage(),
        ]);
    }
}
