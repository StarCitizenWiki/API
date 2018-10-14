<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 11.09.2018
 * Time: 17:38
 */

namespace App\Jobs\Rsi\CommLink\Parser\Element;

use App\Jobs\Rsi\CommLink\Parser\Element\AbstractBaseElement as BaseElement;
use App\Jobs\Rsi\CommLink\Parser\ParseCommLink;
use App\Models\Rsi\CommLink\Link\Link as LinkModel;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Extracts and Creates Link Models from Comm-Link Contents
 */
class Link extends BaseElement
{
    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    private $commLink;

    /**
     * @var array Link Data Array
     */
    private $links = [];

    /**
     * Link constructor.
     *
     * @param \Symfony\Component\DomCrawler\Crawler $commLinkDocument
     */
    public function __construct(Crawler $commLinkDocument)
    {
        $this->commLink = $commLinkDocument;
    }

    /**
     * Returns an array with link ids from the link table
     *
     * @return array Link IDs
     */
    public function getLinkIds(): array
    {
        $this->extractLinks();

        $linkIds = [];
        $contentLinks = collect($this->links);
        $contentLinks->each(
            function ($link) use (&$linkIds) {
                $linkIds[] = LinkModel::firstOrCreate(
                    [
                        'href' => $this->cleanText($link['href']),
                        'text' => $this->cleanText($link['text']),
                    ]
                )->id;
            }
        );

        return array_unique($linkIds);
    }


    /**
     * Extracts all <a> Elements from the Crawler
     * Saves href and Link Texts
     */
    private function extractLinks(): void
    {
        $this->commLink->filter(ParseCommLink::POST_SELECTOR)->filterXPath('//a')->each(
            function (Crawler $crawler) {
                $href = $crawler->attr('href');

                if (null !== $href && null !== parse_url($href, PHP_URL_HOST)) {
                    $this->links[] = [
                        'href' => $href,
                        'text' => $crawler->text(),
                    ];
                }
            }
        );

        $this->commLink->filter(ParseCommLink::POST_SELECTOR)->filterXPath('//iframe')->each(
            function (Crawler $crawler) {
                $src = $crawler->attr('src');

                if (null !== $src && null !== parse_url($src, PHP_URL_HOST)) {
                    if (null === parse_url($src, PHP_URL_SCHEME)) {
                        $src = 'https:'.$src;
                    }

                    $this->links[] = [
                        'href' => $src,
                        'text' => 'iframe',
                    ];
                }
            }
        );
    }
}
