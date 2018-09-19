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

        $imageIds = [];
        $contentImages = collect($this->images);
        $contentImages->each(
            function ($image) use (&$imageIds) {
                $src = $image['src'];
                $pattern = '/media\/(\w+)\/(\w+)\//';
                $src = preg_replace($pattern, 'media/$1/source/', $src);

                if (null === parse_url($src, PHP_URL_HOST)) {
                    $src = config('api.rsi_url').'/'.$src;
                }

                $imageIds[] = ImageModel::firstOrCreate(
                    [
                        'src' => $this->cleanText($src),
                        'alt' => $this->cleanText($image['alt']),
                    ]
                )->id;
            }
        );

        return array_unique($imageIds);
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
                        'src' => ltrim(trim($src), '/'),
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
                    'src' => ltrim(trim($src), '/'),
                    'alt' => self::POST_BACKGROUND,
                ];
            }
        }
    }
}
