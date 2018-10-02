<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 11.09.2018
 * Time: 17:38
 */

namespace App\Jobs\Rsi\CommLink\Parser\Element;

use App\Jobs\Rsi\CommLink\Parser\Element\AbstractBaseElement as BaseElement;
use App\Jobs\Rsi\CommLink\Parser\ParseCommLink;
use App\Models\Rsi\CommLink\Image\Image as ImageModel;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Extracts and Creates Image Models from Comm Link Contents
 */
class Image extends BaseElement
{
    private const RSI_DOMAINS = [
        'robertsspaceindustries.com',
        'media.robertsspaceindustries.com',
    ];

    private const RSI_MEDIA_DIR_HASH_LENGTH = 14;

    /**
     * Post Background CSS Selector
     */
    private const POST_BACKGROUND = '#post-background';

    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    private $commLink;

    /**
     * @var array Image Data Array
     */
    private $images = [];

    /**
     * Image constructor.
     *
     * @param \Symfony\Component\DomCrawler\Crawler $commLinkDocument
     */
    public function __construct(Crawler $commLinkDocument)
    {
        $this->commLink = $commLinkDocument;
    }

    /**
     * Returns an array with image ids from the image table
     *
     * @return array Image IDs
     */
    public function getImageIds(): array
    {
        $this->extractImages();
        $imageIDs = [];

        $contentImages = collect($this->images);
        $contentImages->filter(
            function ($image) {
                $host = parse_url($image['src'], PHP_URL_HOST);

                return null === $host || in_array($host, self::RSI_DOMAINS);
            }
        )->each(
            function ($image) use (&$imageIDs) {
                $src = $this->cleanImgSource($image['src']);

                $imageIDs[] = ImageModel::firstOrCreate(
                    [
                        'src' => $this->cleanText($src),
                        'alt' => $this->cleanText($image['alt']),
                        'dir' => $this->getDirHash($src),
                    ]
                )->id;
            }
        );

        return array_unique($imageIDs);
    }

    /**
     * Extracts all <img> Elements from the Crawler
     * Saves src and alt attributes
     */
    private function extractImages(): void
    {
        $this->commLink->filter(ParseCommLink::POST_SELECTOR)->filterXPath('//img')->each(
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

        if ($this->commLink->filter(self::POST_BACKGROUND)->count() > 0) {
            $background = $this->commLink->filter(self::POST_BACKGROUND);
            $src = $background->attr('style');

            if (null !== $src && !empty($src)) {
                if (preg_match('/(\/media\/.*\.\w+)/', $src, $src)) {
                    $src = $src[1];
                }

                $this->images[] = [
                    'src' => trim($src),
                    'alt' => self::POST_BACKGROUND,
                ];
            }
        }
    }

    /**
     * Cleans the IMG SRC
     *
     * @param string $src IMG SRC
     *
     * @return string
     */
    private function cleanImgSource(string $src): string
    {
        $src = parse_url($src, PHP_URL_PATH);

        $src = str_replace(['%20', '%0A'], '', $src);

        $pattern = '/media\/(\w+)\/(\w+)\//';
        $src = preg_replace($pattern, 'media/$1/source/', $src);

        $src = str_replace('//', '/', $src);
        $src = trim(ltrim($src, '/'));

        return "/{$src}";
    }

    /**
     * Try to get Original RSI Hash
     *
     * @param string $src
     *
     * @return null|string
     */
    private function getDirHash(string $src): ?string
    {
        $dir = str_replace('/media/', '', $src);
        $dir = explode('/', $dir);

        if (isset($dir[0]) && strlen($dir[0]) === self::RSI_MEDIA_DIR_HASH_LENGTH) {
            return $dir[0];
        }

        return null;
    }
}
