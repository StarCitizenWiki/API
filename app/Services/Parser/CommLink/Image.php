<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink;

use App\Jobs\Rsi\CommLink\Import\ImportCommLink;
use App\Models\Rsi\CommLink\Image\Image as ImageModel;
use Symfony\Component\DomCrawler\Crawler;

class Image extends AbstractBaseElement
{
    private const RSI_DOMAINS = [
        'robertsspaceindustries.com',
        'media.robertsspaceindustries.com',
    ];

    private const POST_BACKGROUND = '#post-background';

    private Crawler $commLink;

    /**
     * @var array<int, array{src: string, alt?: string}>
     */
    private array $images = [];

    public function __construct(Crawler $commLinkDocument)
    {
        $this->commLink = $commLinkDocument;
    }

    /**
     * @return array<int, int>
     */
    public function getImageIds(): array
    {
        $this->extractImages();
        $imageIds = [];

        $contentImages = collect($this->images)
            ->filter(static function (array $image): bool {
                $host = parse_url($image['src'], PHP_URL_HOST);

                return $host === null || in_array($host, self::RSI_DOMAINS, true);
            })
            ->filter(function (array $image): bool {
                $extension = pathinfo(parse_url($image['src'], PHP_URL_PATH), PATHINFO_EXTENSION);

                return $extension !== null && $extension !== '';
            })
            ->map(function (array $image): array {
                $src = self::cleanImgSource($image['src']);

                return [
                    'src' => $this->cleanText($src),
                    'alt' => $this->cleanText($image['alt'] ?? ''),
                    'dir' => self::getDirHash($src),
                ];
            })
            ->groupBy('src')
            ->map(static function ($images): array {
                return $images->first(static fn (array $image): bool => $image['alt'] !== '')
                    ?? $images->first();
            })
            ->values();

        $contentImages
            ->each(function (array $image) use (&$imageIds): void {
                $imageIds[] = ImageModel::query()->firstOrCreate($image)->id;
            });

        return array_values(array_unique($imageIds));
    }

    private function extractImages(): void
    {
        $this->extractImgTags();
        $this->extractCFeatureTemplateImages();
        $this->extractPostBackground();
        $this->extractSourceAttrs();
        $this->extractCssBackgrounds();
        $this->extractMediaImages();
        $this->extractRsiImages();
        $this->extractWalkerImages();
        $this->extractOgImage();
    }

    private function extractSourceAttrs(): void
    {
        preg_match_all(
            "/source:\s?'(https:\/\/(?:media\.)?robertsspaceindustries\.com.*?)'/",
            $this->commLink->html() ?? '',
            $matches
        );

        $this->addImages($matches);
    }

    private function extractCssBackgrounds(): void
    {
        preg_match_all(
            "/url\([\"']?((?:https:\/\/(?:media\.)?robertsspaceindustries\.com)?\/(?:\w{13,16}\/\w+|media\/\w{13,16}\/\w+\/[\w\-.]+)+\.\w{2,6})[\"']?\)/",
            $this->commLink->filterXPath('//head')->html() ?? '',
            $matches
        );

        $this->addImages($matches);
    }

    private function extractMediaImages(): void
    {
        preg_match_all(
            "/(https:\/\/media\.robertsspaceindustries\.com\/\w{13,16}\/\w+\.\w{2,6})/",
            $this->commLink->filterXPath('//body')->html() ?? '',
            $matches
        );

        $this->addImages($matches);
    }

    private function extractRsiImages(): void
    {
        preg_match_all(
            "/(https:\/\/robertsspaceindustries\.com\/media\/\w{13,16}\/\w+\/[\w\-.]+\.\w{2,6})/",
            $this->commLink->filterXPath('//body')->html() ?? '',
            $matches
        );

        $this->addImages($matches);
    }

    private function extractWalkerImages(): void
    {
        foreach ((new JsonWalker)->collect($this->commLink)->images as $src) {
            $this->images[] = [
                'src' => $src,
            ];
        }
    }

    private function extractImgTags(): void
    {
        $this->commLink->filter($this->getFilterSelector())->filterXPath('//img')->each(function (Crawler $crawler): void {
            $src = $crawler->attr('src');

            if ($src !== null && $src !== '') {
                $this->images[] = [
                    'src' => trim($src),
                    'alt' => $crawler->attr('alt') ?? '',
                ];
            }
        });
    }

    private function extractCFeatureTemplateImages(): void
    {
        $this->commLink->filter($this->getFilterSelector())->filterXPath('//c-feature')->each(function (Crawler $crawler): void {
            $src = trim($crawler->attr('background-url') ?? '');

            if ($src === '') {
                return;
            }

            $this->images[] = [
                'src' => $src,
                'alt' => 'c-feature',
            ];
        });
    }

    private function extractPostBackground(): void
    {
        if ($this->commLink->filter(self::POST_BACKGROUND)->count() === 0) {
            return;
        }

        $background = $this->commLink->filter(self::POST_BACKGROUND);
        $src = $background->attr('style');

        if ($src === null || $src === '') {
            return;
        }

        if (preg_match('/(\/media\/.*\.\w+)/', $src, $matches)) {
            $src = $matches[1];
        }

        if ($src !== '') {
            $this->images[] = [
                'src' => trim($src),
                'alt' => self::POST_BACKGROUND,
            ];
        }
    }

    private function extractOgImage(): void
    {
        $metaTags = $this->commLink->filterXPath('//meta[@property="og:image"]');

        if ($metaTags->count() === 0) {
            return;
        }

        $content = $metaTags->attr('content');

        if ($content !== null && trim($content) !== '') {
            $this->images[] = [
                'src' => trim($content),
                'alt' => 'og:image',
            ];
        }
    }

    private function getFilterSelector(): string
    {
        $filter = ImportCommLink::POST_SELECTOR;

        if ($this->isSubscriberPage($this->commLink)) {
            $filter = '#subscribers .album-wrapper';
        } elseif ($this->isSpecialPage($this->commLink)) {
            $filter = ImportCommLink::SPECIAL_PAGE_SELECTOR;
        }

        return $filter;
    }

    /**
     * @param  array<int, array<int, string>>  $matches
     */
    private function addImages(array $matches, string $alt = ''): void
    {
        if (! empty($matches[1])) {
            collect($matches[1])->each(function (string $src) use ($alt): void {
                $this->images[] = [
                    'src' => trim($src),
                    'alt' => $alt,
                ];
            });
        }
    }

    public static function cleanImgSource(string $src): string
    {
        $srcUrlPath = parse_url($src, PHP_URL_PATH);
        $srcUrlPath = str_replace(['%20', '%0A'], '', $srcUrlPath);

        if (parse_url($src, PHP_URL_HOST) === self::RSI_DOMAINS[1]) {
            $pattern = '/(\w+)\/(?:\w+)\.(\w+)/';
            $replacement = '$1/source.$2';
        } else {
            $pattern = '/media\/(\w+)\/(\w+)\//';
            $replacement = 'media/$1/source/';
        }

        $srcUrlPath = preg_replace($pattern, $replacement, $srcUrlPath ?? '') ?? $srcUrlPath;
        $srcUrlPath = str_replace('//', '/', $srcUrlPath);
        $srcUrlPath = trim(ltrim($srcUrlPath, '/'));

        return "/{$srcUrlPath}";
    }

    public static function getDirHash(string $src): ?string
    {
        $src = substr($src, 1);
        $dir = str_replace('media/', '', $src);
        $dir = explode('/', $dir);

        return $dir[0] ?? null;
    }
}
