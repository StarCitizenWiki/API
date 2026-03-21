<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Import;

use App\Models\Rsi\CommLink\CommLink;
use App\Models\System\Language;
use App\Services\Parser\CommLink\Content;
use App\Services\Parser\CommLink\Image;
use App\Services\Parser\CommLink\Link;
use App\Services\Parser\CommLink\Metadata;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;

class ImportCommLink implements ShouldQueue
{
    use Batchable;
    use Queueable;

    public const POST_SELECTOR = '#post';

    public const SUBSCRIBERS_SELECTOR = '#subscribers';

    public const SPECIAL_PAGE_SELECTOR = '#layout-system';

    private const ALEXANDRIA_SELECTOR = 'g-platform-client-component, g-banner-advanced, g-navigation-sales';

    private const ALEXANDRIA_URL_PATTERN = 'https://robertsspaceindustries.com/alexandria/html';

    public int $timeout = 120;

    private Crawler $crawler;

    public function __construct(
        public readonly int $commLinkId,
        public readonly string $file,
        public readonly bool $forceImport = false,
    ) {}

    /**
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        Log::info('Parsing Comm-Link import file.', [
            'id' => $this->commLinkId,
            'file' => $this->file,
            'force_import' => $this->forceImport,
        ]);

        $content = Storage::disk('comm_links')->get($this->filePath());

        if ($content === null) {
            throw new FileNotFoundException;
        }

        if ($this->containsDynamicContent($content)) {
            try {
                $content = $this->processDynamicContent($content);
                Storage::disk('comm_links')->put($this->filePath(), $content);
            } catch (Exception $exception) {
                Log::warning('Failed to process dynamic Comm-Link content.', [
                    'id' => $this->commLinkId,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        $this->crawler = new Crawler;
        $this->crawler->addHtmlContent($content);

        $post = $this->crawler->filter(self::POST_SELECTOR);
        $subscribers = $this->crawler->filter(self::SUBSCRIBERS_SELECTOR);
        $specialPage = $this->crawler->filter(self::SPECIAL_PAGE_SELECTOR);
        $alexandriaComponents = $this->crawler->filter(self::ALEXANDRIA_SELECTOR);

        if (
            $post->count() === 0 &&
            $subscribers->count() === 0 &&
            $specialPage->count() === 0 &&
            $alexandriaComponents->count() === 0
        ) {
            Log::info('Comm-Link import skipped due to empty content.', [
                'id' => $this->commLinkId,
            ]);

            return;
        }

        $commLink = CommLink::query()->where('cig_id', $this->commLinkId)->first();

        if ($commLink === null || $this->forceImport) {
            $this->createCommLink();

            return;
        }

        $this->updateCommLink($commLink);
    }

    private function filePath(): string
    {
        return sprintf('%d/%s', $this->commLinkId, $this->file);
    }

    private function containsDynamicContent(string $content): bool
    {
        return str_contains($content, self::ALEXANDRIA_URL_PATTERN);
    }

    /**
     * @throws Exception
     */
    private function processDynamicContent(string $content): string
    {
        $crawler = new Crawler;
        $crawler->addHtmlContent($content, 'UTF-8');

        $scripts = [];
        $crawler->filter('script')->each(function (Crawler $node) use (&$scripts): void {
            $html = $node->html();

            if ($html !== null && str_contains($html, self::ALEXANDRIA_URL_PATTERN)) {
                $scripts[] = $html;
            }
        });

        if ($scripts === []) {
            return $content;
        }

        $alexandriaContent = '';
        $processedContent = $content;

        foreach ($scripts as $scriptTag) {
            if (! preg_match('/https:\/\/robertsspaceindustries\.com\/alexandria\/html[^"\'\s]*/i', $scriptTag, $matches)) {
                continue;
            }

            $alexandriaUrl = $matches[0];

            $response = Http::timeout(30)->get($alexandriaUrl);

            if (! $response->successful()) {
                throw new RuntimeException('Alexandria endpoint returned status code: '.$response->status());
            }

            $dynamicContent = $response->body();

            if ($dynamicContent === '') {
                throw new RuntimeException('Alexandria endpoint returned empty content');
            }

            $processedContent = str_replace($scriptTag, '', $processedContent);
            $alexandriaContent .= $dynamicContent;
        }

        return preg_replace(
            '/<div id="layout-system".*?>.*?<\/div>/s',
            sprintf('<div id="layout-system">%s</div>', $alexandriaContent),
            $processedContent
        ) ?? $processedContent;
    }

    private function createCommLink(): void
    {
        $data = $this->getCommLinkData();
        $data['created_at'] = $data['created_at_file'] ?? $data['created_at'];

        try {
            $commLink = CommLink::query()->updateOrCreate(
                ['cig_id' => $this->commLinkId],
                $data
            );
        } catch (UniqueConstraintViolationException $e) {
            // Check if this is a primary key constraint violation
            if (str_contains($e->getMessage(), 'comm_links_pkey')) {
                Log::error('CommLink import failed due to sequence out of sync', [
                    'cig_id' => $this->commLinkId,
                    'error' => $e->getMessage(),
                    'hint' => 'Run: php artisan data:migrate --group=CommLinks --sync-sequences --force',
                ]);
            }

            throw $e;
        }

        $this->addEnglishTranslation($commLink);
        $this->syncImageIds($commLink);
        $this->syncLinkIds($commLink);
        $this->updateCounts($commLink);
    }

    private function updateCommLink(CommLink $commLink): void
    {
        $data = $this->getCommLinkData();

        if ($this->contentHasChanged($commLink)) {
            $this->addEnglishTranslation($commLink);
        } else {
            unset($data['file']);
        }

        $dateMetadata = Carbon::parse($data['created_at']);
        $dateMetadataFile = Carbon::parse($data['created_at_file'] ?? $data['created_at']);
        $dateMetadataFile->setSecond(0);
        $dateMetadataFile->setMinute(0);

        if ($dateMetadata->diffInHours($dateMetadataFile) <= 24 || $data['created_at'] === Metadata::DEFAULT_CREATION_DATE) {
            $data['created_at'] = $dateMetadataFile;
        }

        $commLink->update($data);
        $this->syncImageIds($commLink);
        $this->syncLinkIds($commLink);
        $this->updateCounts($commLink);
    }

    /**
     * @return array<string, mixed>
     */
    private function getCommLinkData(): array
    {
        $metaData = (new Metadata($this->crawler))->getMetaData();

        $metaData->put(
            'created_at_file',
            $this->createTimestampFromFile($this->getFirstCommLinkFileName()) ?? Metadata::DEFAULT_CREATION_DATE
        );

        return [
            'title' => $metaData->get('title'),
            'comment_count' => $metaData->get('comment_count'),
            'url' => $metaData->get('url'),
            'file' => $this->file,
            'channel_id' => $metaData->get('channel_id'),
            'category_id' => $metaData->get('category_id'),
            'series_id' => $metaData->get('series_id'),
            'created_at' => $metaData->get('created_at'),
            'created_at_file' => $metaData->get('created_at_file'),
        ];
    }

    private function addEnglishTranslation(CommLink $commLink): void
    {
        $contentParser = new Content($this->crawler);

        $content = $contentParser->getContent();

        if ($content !== null && $content !== '') {
            $commLink->setTranslation('translation', Language::ENGLISH, $content);
            $commLink->save();
        }
    }

    private function syncImageIds(CommLink $commLink): void
    {
        $imageParser = new Image($this->crawler);
        $commLink->images()->sync($imageParser->getImageIds());
    }

    private function syncLinkIds(CommLink $commLink): void
    {
        $linkParser = new Link($this->crawler);
        $commLink->links()->sync($linkParser->getLinkIds());
    }

    private function updateCounts(CommLink $commLink): void
    {
        $commLink->images_count = $commLink->images()->count();
        $commLink->links_count = $commLink->links()->count();
        $commLink->save();
    }

    private function contentHasChanged(CommLink $commLink): bool
    {
        $contentParser = new Content($this->crawler);
        $currentTranslation = $commLink->getTranslation('translation', Language::ENGLISH, false);

        return $contentParser->getContent() !== ($currentTranslation ?? '');
    }

    private function getFirstCommLinkFileName(): ?string
    {
        $files = Storage::disk('comm_links')->allFiles((string) $this->commLinkId);

        if ($files === []) {
            return null;
        }

        sort($files);
        $filename = Arr::first($files);

        return $filename === null
            ? null
            : str_replace(sprintf('%d/', $this->commLinkId), '', $filename);
    }

    private function createTimestampFromFile(?string $file): ?string
    {
        if ($file === null) {
            return null;
        }

        $base = str_replace('.html', '', $file);

        try {
            return Carbon::createFromFormat('Y-m-d_His', $base)->format('Y-m-d H:i:s');
        } catch (InvalidFormatException $exception) {
            return null;
        }
    }
}
