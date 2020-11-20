<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Parser\Element;

use App\Jobs\Rsi\CommLink\Parser\Element\AbstractBaseElement as BaseElement;
use App\Jobs\Rsi\CommLink\Parser\ParseCommLink;
use App\Models\Rsi\CommLink\Image\Image as ImageModel;
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
        )->each(
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

        if ($this->isSpecialPage($this->commLink)) {
            $this->commLink->filterXPath('//template')->each(
                function (Crawler $crawler) {
                    preg_match_all(
                        "/'(https:\/\/(?:media\.)?robertsspaceindustries\.com.*?)'/",
                        $crawler->html(),
                        $matches
                    );

                    if (!empty($matches[1])) {
                        collect($matches[1])->each(
                            function ($src) {
                                $this->images[] = [
                                    'src' => trim($src),
                                    'alt' => '',
                                ];
                            }
                        );
                    }
                }
            );
        }
    }

    private function extractImgTags(): void
    {
        $filter = ParseCommLink::POST_SELECTOR;
        if ($this->isSubscriberPage($this->commLink)) {
            $filter = '#subscribers .album-wrapper';
        } elseif ($this->isSpecialPage($this->commLink)) {
            $filter = ParseCommLink::SPECIAL_PAGE_SELECTOR;
        }

        $this->commLink->filter($filter)->filterXPath('//img')->each(
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
        $filter = ParseCommLink::POST_SELECTOR;
        if ($this->isSubscriberPage($this->commLink)) {
            $filter = '#subscribers .album-wrapper';
        } elseif ($this->isSpecialPage($this->commLink)) {
            $filter = ParseCommLink::SPECIAL_PAGE_SELECTOR;
        }

        $this->commLink->filter($filter)->filterXPath('//c-feature')->each(
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

        if (!empty($matches[1])) {
            collect($matches[1])->each(
                function ($src) {
                    $this->images[] = [
                        'src' => trim($src),
                        'alt' => '',
                    ];
                }
            );
        }
    }

    private function extractCssBackgrounds(): void
    {
        preg_match_all(
            "/url\([\"'](\/media\/\w+\/\w+\/[\w\-.]+\.\w+)[\"']\)/",
            $this->commLink->filterXPath('//head')->html(),
            $matches
        );

        if (!empty($matches[1])) {
            collect($matches[1])->each(
                function ($src) {
                    $this->images[] = [
                        'src' => trim($src),
                        'alt' => '',
                    ];
                }
            );
        }
    }

    private function extractMediaImages(): void
    {
        preg_match_all(
            "/(https:\/\/media\.robertsspaceindustries\.com\/[\w]{13,16}\/\w+\.[\w]{2,6})/",
            $this->commLink->filterXPath('//body')->html(),
            $matches
        );

        if (!empty($matches[1])) {
            collect($matches[1])->each(
                function ($src) {
                    $this->images[] = [
                        'src' => trim($src),
                        'alt' => '',
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
