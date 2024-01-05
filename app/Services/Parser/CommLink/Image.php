<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink;

use App\Jobs\Rsi\CommLink\Import\ImportCommLink;
use App\Models\Rsi\CommLink\Image\Image as ImageModel;
use App\Services\Parser\CommLink\AbstractBaseElement as BaseElement;
use JsonException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Extracts and Creates Image Models from Comm-Link Contents.
 */
class Image extends BaseElement
{
    private const RSI_DOMAINS = [
        'robertsspaceindustries.com',
        'media.robertsspaceindustries.com',
    ];

    /**
     * Post Background CSS Selector.
     */
    private const POST_BACKGROUND = '#post-background';

    /**
     * @var Crawler
     */
    private Crawler $commLink;

    /**
     * @var array Image Data Array
     */
    private array $images = [];

    /**
     * Image constructor.
     *
     * @param Crawler $commLinkDocument
     */
    public function __construct(Crawler $commLinkDocument)
    {
        $this->commLink = $commLinkDocument;
    }

    /**
     * Returns an array with image ids from the image table.
     *
     * @return array Image IDs
     */
    public function getImageIds(): array
    {
        $this->extractImages();
        $imageIDs = [];

        $contentImages = collect($this->images);
        $contentImages->filter(
            static function ($image) {
                $host = parse_url($image['src'], PHP_URL_HOST);

                return null === $host || in_array($host, self::RSI_DOMAINS, true);
            }
        )
            ->filter(
                function (array $image) {
                    $extension = pathinfo(parse_url($image['src'], PHP_URL_PATH), PATHINFO_EXTENSION);

                    return $extension !== null && $extension !== '';
                }
            )
            ->each(
                function ($image) use (&$imageIDs) {
                    $src = self::cleanImgSource($image['src']);

                    $imageIDs[] = ImageModel::query()->firstOrCreate(
                        [
                            'src' => $this->cleanText($src),
                            'alt' => $this->cleanText($image['alt']),
                            'dir' => self::getDirHash($src),
                        ]
                    )->id;
                }
            );

        return array_unique($imageIDs);
    }

    /**
     * Extracts all <img> Elements from the Crawler
     * Saves src and alt attributes.
     */
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

        if ($this->isSpecialPage($this->commLink)) {
            $this->commLink->filterXPath('//template')->each(
                function (Crawler $crawler) {
                    preg_match_all(
                        "/'(https:\/\/(?:media\.)?robertsspaceindustries\.com.*?)'/",
                        $crawler->html(),
                        $matches
                    );

                    $this->addImages($matches);
                }
            );
        }
    }

    private function extractImgTags(): void
    {
        $this->commLink->filter($this->getFilterSelector())->filterXPath('//img')->each(
            function (Crawler $crawler) {
                $src = $crawler->attr('src');

                if (null !== $src && !empty($src)) {
                    $this->images[] = [
                        'src' => trim($src),
                        'alt' => $crawler->attr('alt') ?? '',
                    ];
                }
            }
        );
    }

    private function extractCFeatureTemplateImages(): void
    {
        $this->commLink->filter($this->getFilterSelector())->filterXPath('//c-feature')->each(
            function (Crawler $crawler) {
                $src = trim($crawler->attr('background-url') ?? '');

                if (empty($src)) {
                    return;
                }

                $this->images[] = [
                    'src' => $src,
                    'alt' => 'c-feature',
                ];
            }
        );
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
                ->each(
                    function (Crawler $crawler) use ($path, $attr) {
                        $attributes = $attr;
                        if (!is_array($attributes)) {
                            $attributes = [$attr];
                        }

                        foreach ($attributes as $attr) {
                            $images = trim($crawler->attr($attr) ?? '');
                            $data = [];

                            try {
                                $images = json_decode($images, true, 512, JSON_THROW_ON_ERROR);
                            } catch (JsonException $e) {
                                continue;
                            }

                            if ($path === 'g-banner-advanced') {
                                if ($attr === ':content' && isset($images['logo']['picture'])) {
                                    $images = $images['logo']['picture'];
                                } elseif ($attr === ':media' && isset($images['background']['picture'])) {
                                    $images = $images['background']['picture'];
                                }
                            }

                            if (!is_array($images)) {
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

                                if (!empty($data) && $data['src'] !== null) {
                                    $this->images[] = $data;
                                }
                            }
                        }
                    }
                );
        }
    }

    private function extractPostBackground(): void
    {
        if ($this->commLink->filter(self::POST_BACKGROUND)->count() > 0) {
            $background = $this->commLink->filter(self::POST_BACKGROUND);
            $src = $background->attr('style');

            if (null !== $src && !empty($src)) {
                if (preg_match('/(\/media\/.*\.\w+)/', $src, $src)) {
                    $src = $src[1];
                }

                if (!empty($src)) {
                    $this->images[] = [
                        'src' => trim($src),
                        'alt' => self::POST_BACKGROUND,
                    ];
                }
            }
        }
    }

    private function extractSourceAttrs(): void
    {
        preg_match_all(
            "/source:\s?'(https:\/\/(?:media\.)?robertsspaceindustries\.com.*?)'/",
            $this->commLink->html(),
            $matches
        );

        $this->addImages($matches);
    }

    /**
     * Extract CSS image urls from media.robertsspaceindustries.com or robertsspaceindustries.com
     */
    private function extractCssBackgrounds(): void
    {
        //phpcs:disable
        preg_match_all(
            "/url\([\"']?((?:https:\/\/(?:media\.)?robertsspaceindustries\.com)?\/(?:\w{13,16}\/\w+|media\/\w{13,16}\/\w+\/[\w\-.]+)+\.\w{2,6})[\"']?\)/",
            $this->commLink->filterXPath('//head')->html(),
            $matches
        );
        //phpcs:enable

        $this->addImages($matches);
    }

    /**
     * Extract images from media.robertsspaceindustries.com
     */
    private function extractMediaImages(): void
    {
        preg_match_all(
            "/(https:\/\/media\.robertsspaceindustries\.com\/\w{13,16}\/\w+\.\w{2,6})/",
            $this->commLink->filterXPath('//body')->html(),
            $matches
        );

        $this->addImages($matches);
    }

    /**
     * robertsspaceincustries.com/media
     */
    private function extractRsiImages(): void
    {
        preg_match_all(
            "/(https:\/\/robertsspaceindustries\.com\/media\/\w{13,16}\/\w+\/[\w\-.]+\.\w{2,6})/",
            $this->commLink->filterXPath('//body')->html(),
            $matches
        );

        $this->addImages($matches);
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
     * Adds all found image matches
     *
     * @param array $matches
     * @param string $alt
     */
    private function addImages(array $matches, string $alt = ''): void
    {
        if (!empty($matches[1])) {
            collect($matches[1])->each(
                function ($src) use ($alt) {
                    $this->images[] = [
                        'src' => trim($src),
                        'alt' => $alt,
                    ];
                }
            );
        }
    }

    /**
     * Cleans the IMG SRC.
     *
     * @param string $src IMG SRC
     *
     * @return string
     */
    public static function cleanImgSource(string $src): string
    {
        $srcUrlPath = parse_url($src, PHP_URL_PATH);
        $srcUrlPath = str_replace(['%20', '%0A'], '', $srcUrlPath);

        // if host is media.robertsspaceindustries.com
        if (parse_url($src, PHP_URL_HOST) === self::RSI_DOMAINS[1]) {
            $pattern = '/(\w+)\/(?:\w+)\.(\w+)/';
            $replacement = '$1/source.$2';
        } else {
            $pattern = '/media\/(\w+)\/(\w+)\//';
            $replacement = 'media/$1/source/';
        }

        $srcUrlPath = preg_replace($pattern, $replacement, $srcUrlPath);

        $srcUrlPath = str_replace('//', '/', $srcUrlPath);
        $srcUrlPath = trim(ltrim($srcUrlPath, '/'));

        return "/{$srcUrlPath}";
    }

    /**
     * Try to get Original RSI Hash.
     *
     * @param string $src
     *
     * @return string|null
     */
    public static function getDirHash(string $src): ?string
    {
        $src = substr($src, 1);
        $dir = str_replace('media/', '', $src);
        $dir = explode('/', $dir);

        return $dir[0] ?? null;
    }
}
