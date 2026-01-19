<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink;

use App\Jobs\Rsi\CommLink\Import\ImportCommLink;
use App\Models\Rsi\CommLink\Image\Image as ImageModel;
use JsonException;
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

        $contentImages = collect($this->images);
        $contentImages->filter(static function (array $image): bool {
            $host = parse_url($image['src'], PHP_URL_HOST);

            return $host === null || in_array($host, self::RSI_DOMAINS, true);
        })
            ->filter(function (array $image): bool {
                $extension = pathinfo(parse_url($image['src'], PHP_URL_PATH), PATHINFO_EXTENSION);

                return $extension !== null && $extension !== '';
            })
            ->each(function (array $image) use (&$imageIds): void {
                $src = self::cleanImgSource($image['src']);

                $imageIds[] = ImageModel::query()->firstOrCreate([
                    'src' => $this->cleanText($src),
                    'alt' => $this->cleanText($image['alt'] ?? ''),
                    'dir' => self::getDirHash($src),
                ])->id;
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
        $this->extractGElementImages();
        $this->extractAlexandriaImages();

        if ($this->isSpecialPage($this->commLink)) {
            $this->commLink->filterXPath('//template')->each(function (Crawler $crawler): void {
                preg_match_all(
                    "/'(https:\/\/(?:media\.)?robertsspaceindustries\.com.*?)'/",
                    $crawler->html() ?? '',
                    $matches
                );

                $this->addImages($matches);
            });
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

    private function extractGElementImages(): void
    {
        $defaultImageElements = [
            ':image',
            ':picture',
            ':simple-image',
            ':background-image',
            ':main-responsive-image',
        ];

        $elements = [
            'g-banner' => $defaultImageElements,
            'g-illustration' => $defaultImageElements,
            'g-feature' => $defaultImageElements,
            'g-trailer' => $defaultImageElements,
            'g-slideshow' => ':pictures',
            'g-banner-advanced' => [':content', ':media'],
        ];

        foreach ($elements as $path => $attr) {
            $this->commLink
                ->filter($this->getFilterSelector())->filterXPath(sprintf('//%s', $path))
                ->each(function (Crawler $crawler) use ($path, $attr): void {
                    $attributes = $attr;
                    if (! is_array($attributes)) {
                        $attributes = [$attr];
                    }

                    foreach ($attributes as $attribute) {
                        $images = trim($crawler->attr($attribute) ?? '');
                        $data = [];

                        try {
                            $images = json_decode($images, true, 512, JSON_THROW_ON_ERROR);
                        } catch (JsonException $exception) {
                            continue;
                        }

                        if ($path === 'g-banner-advanced') {
                            if ($attribute === ':content' && isset($images['logo']['picture'])) {
                                $images = $images['logo']['picture'];
                            } elseif ($attribute === ':media' && isset($images['background']['picture'])) {
                                $images = $images['background']['picture'];
                            }
                        }

                        if (! is_array($images)) {
                            $images = [$images];
                        }

                        foreach ($images as $image) {
                            if (isset($image['format']['webp']) || isset($image['format']['originalFormat'])) {
                                $data = [
                                    'src' => $image['format']['webp']['max'] ?? $image['format']['source'] ?? null,
                                    'alt' => $image['alt'] ?? $path,
                                ];
                            }

                            if (isset($image['max']) || isset($image['source'])) {
                                $data = [
                                    'src' => $image['max'] ?? $image['source'],
                                    'alt' => $image['alt'] ?? $path,
                                ];
                            }

                            if (isset($image['originalFormat']) || isset($image['webp'])) {
                                $data = [
                                    'src' => $image['webp']['max'] ?? $image['originalFormat']['max'],
                                    'alt' => $image['alt'] ?? $path,
                                ];
                            }

                            if (! empty($data) && $data['src'] !== null) {
                                $this->images[] = $data;
                            }
                        }
                    }
                });
        }
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

    private function extractAlexandriaImages(): void
    {
        $this->commLink->filterXPath('//g-platform-client-component')->each(function (Crawler $component): void {
            try {
                $properties = $component->attr(':properties');
                if (! empty($properties)) {
                    $this->extractImagesFromProperties($properties);
                }
            } catch (\InvalidArgumentException $exception) {
                // Ignore invalid component.
            }
        });

        $this->commLink->filterXPath('//g-banner-advanced')->each(function (Crawler $banner): void {
            try {
                $mediaAttr = $banner->attr(':media');
                if (! empty($mediaAttr)) {
                    $mediaJson = json_decode($mediaAttr, true);
                    if (isset($mediaJson['background']['picture']['originalFormat']['max'])) {
                        $this->images[] = [
                            'src' => trim($mediaJson['background']['picture']['originalFormat']['max']),
                        ];
                    }
                }
            } catch (\InvalidArgumentException $exception) {
                // Ignore invalid banner.
            }
        });
    }

    private function extractImagesFromProperties(string $properties): void
    {
        $json = json_decode($properties, true);

        if (! $json || ! isset($json['componentProps'])) {
            return;
        }

        if (isset($json['componentProps']['layers']) && is_array($json['componentProps']['layers'])) {
            foreach ($json['componentProps']['layers'] as $layer) {
                if (isset($layer['backgroundImage']['heapImage']['source'])) {
                    $this->images[] = [
                        'src' => trim($layer['backgroundImage']['heapImage']['source']),
                    ];
                }

                if (isset($layer['backgroundImage']['optimizedImage']['originalFormat']['max'])) {
                    $this->images[] = [
                        'src' => trim($layer['backgroundImage']['optimizedImage']['originalFormat']['max']),
                    ];
                }
            }
        }

        if (isset($json['componentProps']['image'])) {
            if (isset($json['componentProps']['image']['source'])) {
                $this->images[] = [
                    'src' => trim($json['componentProps']['image']['source']),
                ];
            }

            if (isset($json['componentProps']['image']['originalFormat']['max'])) {
                $this->images[] = [
                    'src' => trim($json['componentProps']['image']['originalFormat']['max']),
                ];
            }
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
