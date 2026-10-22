<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Models\Game\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SyncPaintImages implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    private const string DEFAULT_API_URL = 'https://starcitizen.tools/api.php';

    private const string SOURCE = 'starcitizen.tools/paints';

    private const int TITLES_PER_BATCH = 50;

    private const int THUMBNAIL_WIDTH = 600;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startedAt = microtime(true);

        $stats = [
            'pages' => 0,
            'sections' => 0,
            'uuids_found' => 0,
            'uuids_unmatched' => 0,
            'files_missing' => 0,
            'items_updated' => 0,
            'errors' => 0,
        ];

        $titles = $this->enumerateSubpages();

        $candidatesByUuid = [];

        foreach ($titles->chunk(self::TITLES_PER_BATCH) as $batch) {
            $body = $this->apiGet([
                'action' => 'query',
                'prop' => 'revisions',
                'rvslots' => 'main',
                'rvprop' => 'content',
                'redirects' => 1,
                'format' => 'json',
                'formatversion' => 2,
                'titles' => $batch->implode('|'),
            ]);

            foreach ($body['query']['pages'] ?? [] as $page) {
                $content = $page['revisions'][0]['slots']['main']['content'] ?? null;

                if (! is_string($content)) {
                    continue;
                }

                $stats['pages']++;

                try {
                    $this->parsePaintSections($content, $candidatesByUuid, $stats);
                } catch (Throwable $e) {
                    $stats['errors']++;
                    Log::warning('Paint page parse failed', [
                        'title' => $page['title'] ?? '',
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }

        $stats['uuids_found'] = count($candidatesByUuid);

        $resolved = $this->resolveFiles($candidatesByUuid, $stats);

        $this->writeImages($candidatesByUuid, $resolved, $stats);

        $stats['duration'] = round(microtime(true) - $startedAt, 2);

        Log::info('Paint images sync completed', $stats);
    }

    /**
     * Search all `{series}/Paints` subpages, following the search offset.
     *
     * @return Collection<int, string>
     */
    private function enumerateSubpages(): Collection
    {
        $titles = collect();
        $offset = null;

        do {
            $query = [
                'action' => 'query',
                'list' => 'search',
                'srsearch' => 'intitle:/Paints',
                'srlimit' => 100,
                'format' => 'json',
                'formatversion' => 2,
            ];

            if ($offset !== null) {
                $query['sroffset'] = $offset;
            }

            $body = $this->apiGet($query);

            foreach ($body['query']['search'] ?? [] as $result) {
                $title = $result['title'] ?? null;

                if (is_string($title) && str_ends_with($title, '/Paints') && substr_count($title, '/') === 1) {
                    $titles[] = $title;
                }
            }

            $offset = $body['continue']['sroffset'] ?? null;
        } while ($offset !== null);

        return $titles->unique()->values();
    }

    /**
     * Split the page into heading sections and collect every UUID with its ordered file candidates
     *
     * @param  array<string, array<int, string>>  $candidatesByUuid
     * @param  array<string, int|float>  $stats
     */
    private function parsePaintSections(string $content, array &$candidatesByUuid, array &$stats): void
    {
        preg_match_all('/^(={2,5})\s*(.+?)\s*\1\s*$/m', $content, $headings, PREG_OFFSET_CAPTURE);

        $headingCount = count($headings[0]);

        foreach ($headings[0] as $i => $iValue) {
            $sectionStart = $iValue[1] + strlen($iValue[0]);
            $sectionEnd = $i + 1 < $headingCount ? $headings[0][$i + 1][1] : strlen($content);
            $section = substr($content, $sectionStart, $sectionEnd - $sectionStart);

            $uuids = $this->extractUuids($section);

            if ($uuids === []) {
                continue;
            }

            $stats['sections']++;

            $candidates = array_values(array_unique($this->orderCandidates($this->extractSectionFiles($section))));

            foreach ($uuids as $uuid) {
                $candidatesByUuid[$uuid] ??= $candidates;
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function extractUuids(string $section): array
    {
        preg_match_all('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', $section, $matches);

        return array_map(
            static fn (string $uuid): string => strtolower($uuid),
            $matches[0],
        );
    }

    /**
     * Collects every file title in the section
     *
     * @return array<int, array{file: string, column: string|null}>
     */
    private function extractSectionFiles(string $section): array
    {
        $files = [];
        $headers = [];
        $column = 0;
        $inTable = false;

        foreach (explode("\n", $section) as $line) {
            $line = ltrim($line);

            if (str_starts_with($line, '{|')) {
                $inTable = true;
                $headers = [];
                $column = 0;

                continue;
            }

            if (str_starts_with($line, '|}')) {
                $inTable = false;

                continue;
            }

            if (! $inTable) {
                foreach ($this->fileTitlesInLine($line) as $file) {
                    $files[] = ['file' => $file, 'column' => null];
                }

                continue;
            }

            // Captions and row separators restart the column walk
            if (str_starts_with($line, '|+') || str_starts_with($line, '|-')) {
                $column = 0;

                continue;
            }

            $isHeader = str_starts_with($line, '!');

            if (! $isHeader && ! str_starts_with($line, '|')) {
                continue;
            }

            $cell = ltrim($line, '!|');

            if ($isHeader) {
                $headers[$column] = $cell;
            }

            foreach ($this->fileTitlesInLine($cell) as $file) {
                $files[] = ['file' => $file, 'column' => $headers[$column] ?? null];
            }

            $column++;
        }

        return $files;
    }

    /**
     * @return array<int, string>
     */
    private function fileTitlesInLine(string $text): array
    {
        preg_match_all('/(?:File|Image):([^\]|]+)/i', $text, $matches);

        $files = [];

        foreach ($matches[1] as $file) {
            $file = str_replace('_', ' ', trim($file));

            if ($file === '' || $this->isPlaceholder($file)) {
                continue;
            }

            $files[] = $file;
        }

        return $files;
    }

    private function isPlaceholder(string $file): bool
    {
        foreach (config('images.placeholder_patterns', []) as $pattern) {
            if (preg_match($pattern, $file)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, array{file: string, column: string|null}>  $taggedFiles
     * @return array<int, string>
     */
    private function orderCandidates(array $taggedFiles): array
    {
        $isometric = [];
        $rest = [];
        $schemes = [];

        foreach ($taggedFiles as ['file' => $file, 'column' => $column]) {
            $columnLower = $column !== null ? mb_strtolower($column) : null;
            $fileLower = mb_strtolower($file);

            $isIsometric = $columnLower !== null ? str_contains($columnLower, 'isometric') : str_contains($fileLower, 'isometric');
            $isScheme = $columnLower !== null ? str_contains($columnLower, 'paint scheme') : str_contains($fileLower, 'paint scheme');

            if ($isIsometric) {
                $isometric[] = $file;
            } elseif ($isScheme) {
                $schemes[] = $file;
            } else {
                $rest[] = $file;
            }
        }

        return [...$isometric, ...$rest, ...$schemes];
    }

    /**
     * Resolve the unique candidate files across all pages, keyed by the returned page title (including the `File:` prefix).
     *
     * @param  array<string, array<int, string>>  $candidatesByUuid
     * @param  array<string, int|float>  $stats
     * @return array<string, array<string, mixed>>
     */
    private function resolveFiles(array $candidatesByUuid, array &$stats): array
    {
        $resolved = [];

        $titles = collect($candidatesByUuid)
            ->flatten()
            ->unique()
            ->values();

        foreach ($titles->chunk(self::TITLES_PER_BATCH) as $batch) {
            $body = $this->apiGet([
                'action' => 'query',
                'prop' => 'imageinfo',
                'iiprop' => 'url|size',
                'iiurlwidth' => self::THUMBNAIL_WIDTH,
                'redirects' => 1,
                'format' => 'json',
                'formatversion' => 2,
                'titles' => $batch->map(fn (string $title): string => 'File:'.$title)->implode('|'),
            ]);

            foreach ($body['query']['pages'] ?? [] as $page) {
                if (isset($page['missing']) || ! isset($page['imageinfo'][0])) {
                    $stats['files_missing']++;

                    continue;
                }

                $resolved[(string) $page['title']] = $page['imageinfo'][0];
            }
        }

        return $resolved;
    }

    /**
     * Write all resolved image entries per UUID with a known item, ranked order preserved
     *
     * @param  array<string, array<int, string>>  $candidatesByUuid
     * @param  array<string, array<string, mixed>>  $resolved
     * @param  array<string, int|float>  $stats
     */
    private function writeImages(array $candidatesByUuid, array $resolved, array &$stats): void
    {
        $knownUuids = Item::query()->whereIn('uuid', array_keys($candidatesByUuid))->pluck('uuid')->all();

        $stats['uuids_unmatched'] = count($candidatesByUuid) - count($knownUuids);

        foreach ($candidatesByUuid as $uuid => $candidates) {
            if (! in_array($uuid, $knownUuids, true)) {
                continue;
            }

            $entries = $this->entriesFor($candidates, $resolved);

            if ($entries === []) {
                continue;
            }

            if (Item::query()->where('uuid', $uuid)->update(['images' => $entries]) > 0) {
                $stats['items_updated']++;
            }
        }
    }

    /**
     * @param  array<int, string>  $candidates
     * @param  array<string, array<string, mixed>>  $resolved
     * @return array<int, array<string, mixed>>
     */
    private function entriesFor(array $candidates, array $resolved): array
    {
        $entries = [];

        foreach ($candidates as $candidate) {
            $info = $resolved['File:'.$candidate] ?? null;

            if ($info === null) {
                continue;
            }

            $entries[] = [
                'source' => self::SOURCE,
                'thumbnail_url' => $this->withoutQuery($info['thumburl'] ?? $info['url']),
                'thumbnail_width' => $info['thumbwidth'] ?? self::THUMBNAIL_WIDTH,
                'thumbnail_height' => $info['thumbheight'] ?? 0,
                'original_url' => $this->withoutQuery($info['url']),
                'original_width' => $info['width'] ?? null,
                'original_height' => $info['height'] ?? null,
            ];
        }

        return $entries;
    }

    private function withoutQuery(string $url): string
    {
        $before = strstr($url, '?', true);

        return $before === false ? $url : $before;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function apiGet(array $query): array
    {
        $response = Http::withUserAgent((string) config('services.rsi_user_agent'))
            ->timeout(60)
            ->get($this->apiUrl(), [...$query, 'maxlag' => 5])
            ->throw();

        usleep((int) config('images.throttle_microseconds', 200_000));

        $body = $response->json() ?? [];

        if (isset($body['error'])) {
            throw new RuntimeException('Wiki API error: '.(string) ($body['error']['info'] ?? $body['error']['code'] ?? 'unknown'));
        }

        return $body;
    }

    private function apiUrl(): string
    {
        return collect(config('images.sources'))
            ->firstWhere('name', 'starcitizen.tools')['api_url'] ?? self::DEFAULT_API_URL;
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Paint images sync job failed', [
            'message' => $exception->getMessage(),
        ]);
    }
}
