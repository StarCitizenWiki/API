<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class EnrichImages implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    private const int API_CHUNK_SIZE = 50;

    private const int THUMBNAIL_SIZE = 600;

    private const int THROTTLE_MICROSECONDS = 200_000;

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<int, string>  $idNameMap  model ID => display name
     * @param  array<int, string>  $idUuidMap  model ID => UUID
     */
    public function __construct(
        private readonly string $modelClass,
        private readonly array $idNameMap,
        private readonly array $idUuidMap = [],
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $sources = config('images.sources', []);
        $placeholderPatterns = config('images.placeholder_patterns', []);

        $resolved = [];
        $unresolvedIds = array_keys($this->idNameMap);

        foreach ($sources as $source) {
            if ($unresolvedIds === []) {
                break;
            }

            $remaining = [];
            foreach ($unresolvedIds as $id) {
                $remaining[$id] = $this->idNameMap[$id];
            }

            $chunkResults = match ($source['type'] ?? 'mediawiki') {
                'direct' => $this->queryDirect($source, $remaining),
                default => $this->queryMediaWiki($source, $remaining, $placeholderPatterns),
            };

            foreach ($chunkResults as $id => $imageData) {
                $resolved[$id] = $imageData;
            }

            $unresolvedIds = array_diff($unresolvedIds, array_keys($resolved));
        }

        $this->persistResults($resolved, $unresolvedIds);
    }

    /**
     * @param  array{api_url: string, name: string}  $source
     * @param  array<int, string>  $idNameMap
     * @param  array<int, string>  $placeholderPatterns
     * @return array<int, array{source: string, thumbnail_url: string, thumbnail_width: int, thumbnail_height: int, original_url: string|null, original_width: int|null, original_height: int|null}>
     */
    private function queryMediaWiki(array $source, array $idNameMap, array $placeholderPatterns): array
    {
        $results = [];
        $nameToId = array_flip($idNameMap);

        $chunks = collect($idNameMap)->chunk(self::API_CHUNK_SIZE);

        foreach ($chunks as $chunk) {
            if ($this->batch()?->cancelled()) {
                break;
            }

            $titles = implode('|', $chunk->values()->toArray());

            $response = Http::timeout(30)->get($source['api_url'], [
                'action' => 'query',
                'prop' => 'pageimages',
                'piprop' => 'thumbnail|name|original',
                'pithumbsize' => self::THUMBNAIL_SIZE,
                'titles' => $titles,
                'format' => 'json',
            ]);

            if (! $response->successful()) {
                Log::warning('Wiki images API request failed', [
                    'source' => $source['name'],
                    'status' => $response->status(),
                ]);

                $this->throttle();

                continue;
            }

            $pages = $response->json('query.pages', []);

            if (! is_array($pages)) {
                $this->throttle();

                continue;
            }

            foreach ($pages as $page) {
                if (! is_array($page)) {
                    continue;
                }

                $pageTitle = $page['title'] ?? null;

                if ($pageTitle === null) {
                    continue;
                }

                $pageImage = $page['pageimage'] ?? null;

                if ($pageImage !== null && $this->isPlaceholder($pageImage, $placeholderPatterns)) {
                    continue;
                }

                $thumbnail = $page['thumbnail'] ?? null;
                $original = $page['original'] ?? null;

                if (! is_array($thumbnail) || ! isset($thumbnail['source'])) {
                    continue;
                }

                $modelId = $nameToId[$pageTitle] ?? null;

                if ($modelId === null) {
                    continue;
                }

                $results[$modelId] = [
                    'source' => $source['name'],
                    'thumbnail_url' => $thumbnail['source'],
                    'thumbnail_width' => $thumbnail['width'] ?? self::THUMBNAIL_SIZE,
                    'thumbnail_height' => $thumbnail['height'] ?? 0,
                    'original_url' => is_array($original) ? ($original['source'] ?? null) : null,
                    'original_width' => is_array($original) ? ($original['width'] ?? null) : null,
                    'original_height' => is_array($original) ? ($original['height'] ?? null) : null,
                ];
            }

            $this->throttle();
        }

        return $results;
    }

    /**
     * @param  array{base_url: string, name: string}  $source
     * @param  array<int, string>  $idNameMap
     * @return array<int, array{source: string, thumbnail_url: string, thumbnail_width: int, thumbnail_height: int, original_url: string, original_width: int, original_height: int}>
     */
    private function queryDirect(array $source, array $idNameMap): array
    {
        $results = [];
        $baseUrl = rtrim($source['base_url'], '/');

        foreach ($idNameMap as $id => $name) {
            if ($this->batch()?->cancelled()) {
                break;
            }

            $uuid = $this->idUuidMap[$id] ?? null;

            if ($uuid === null) {
                continue;
            }

            $url = "{$baseUrl}/{$uuid}.png";

            $response = Http::timeout(30)->get($url);

            if (! $response->successful()) {
                $this->throttle();

                continue;
            }

            $dimensions = @getimagesizefromstring($response->body());

            if ($dimensions === false) {
                Log::warning('Unable to determine image dimensions from direct source', [
                    'source' => $source['name'],
                    'url' => $url,
                ]);

                $this->throttle();

                continue;
            }

            $results[$id] = [
                'source' => $source['name'],
                'thumbnail_url' => $url,
                'thumbnail_width' => $dimensions[0],
                'thumbnail_height' => $dimensions[1],
                'original_url' => $url,
                'original_width' => $dimensions[0],
                'original_height' => $dimensions[1],
            ];

            $this->throttle();
        }

        return $results;
    }

    /**
     * @param  array<int, string>  $placeholderPatterns
     */
    private function isPlaceholder(string $pageImage, array $placeholderPatterns): bool
    {
        foreach ($placeholderPatterns as $pattern) {
            if (preg_match($pattern, $pageImage)) {
                return true;
            }
        }

        return false;
    }

    private function throttle(): void
    {
        $microseconds = (int) config('images.throttle_microseconds', self::THROTTLE_MICROSECONDS);

        if ($microseconds > 0) {
            usleep($microseconds);
        }
    }

    /**
     * @param  array<int, array{source: string, thumbnail_url: string, thumbnail_width: int, thumbnail_height: int, original_url: string|null, original_width: int|null, original_height: int|null}>  $resolved
     * @param  array<int>  $unresolvedIds
     */
    private function persistResults(array $resolved, array $unresolvedIds): void
    {
        foreach ($resolved as $id => $imageData) {
            ($this->modelClass)::where('id', $id)->update(['images' => [$imageData]]);
        }

        if ($unresolvedIds !== []) {
            ($this->modelClass)::whereIn('id', $unresolvedIds)->update(['images' => []]);
        }

        Log::info('Image enrichment chunk completed', [
            'model' => $this->modelClass,
            'resolved' => count($resolved),
            'unresolved' => count($unresolvedIds),
            'chunk_size' => count($this->idNameMap),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Image enrichment job failed', [
            'model' => $this->modelClass,
            'chunk_size' => count($this->idNameMap),
            'message' => $exception->getMessage(),
        ]);
    }
}
