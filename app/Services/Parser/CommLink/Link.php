<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink;

use App\Jobs\Rsi\CommLink\Import\ImportCommLink;
use App\Models\Rsi\CommLink\Link\Link as LinkModel;
use App\Services\Parser\CommLink\AbstractBaseElement as BaseElement;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Extracts and Creates Link Models from Comm-Link Contents
 */
class Link extends BaseElement
{
    private Crawler $commLink;

    /**
     * @var array Link Data Array
     */
    private array $links = [];

    /**
     * Link constructor.
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
                $linkIds[] = LinkModel::query()->firstOrCreate(
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
        collect([
            ImportCommLink::POST_SELECTOR,
            ImportCommLink::SPECIAL_PAGE_SELECTOR,
        ])->each(function (string $selector) {
            $this->commLink->filter($selector)->filterXPath('//a')->each(
                function (Crawler $crawler) {
                    $href = $crawler->attr('href');

                    if ($href !== null && parse_url($href, PHP_URL_HOST) !== null) {
                        $this->links[] = [
                            'href' => $href,
                            'text' => $crawler->text(),
                        ];
                    }
                }
            );

            $this->commLink->filter($selector)->filterXPath('//iframe')->each(
                function (Crawler $crawler) {
                    $src = $crawler->attr('src');

                    if ($src !== null && parse_url($src, PHP_URL_HOST) !== null) {
                        if (parse_url($src, PHP_URL_SCHEME) === null) {
                            $src = 'https:'.$src;
                        }

                        $this->links[] = [
                            'href' => $src,
                            'text' => 'iframe',
                        ];
                    }
                }
            );
        });
    }
}
