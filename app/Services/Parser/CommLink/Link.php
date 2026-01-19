<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink;

use App\Jobs\Rsi\CommLink\Import\ImportCommLink;
use App\Models\Rsi\CommLink\Link as LinkModel;
use Symfony\Component\DomCrawler\Crawler;

class Link extends AbstractBaseElement
{
    private Crawler $commLink;

    /**
     * @var array<int, array{href: string, text: string}>
     */
    private array $links = [];

    public function __construct(Crawler $commLinkDocument)
    {
        $this->commLink = $commLinkDocument;
    }

    /**
     * @return array<int, int>
     */
    public function getLinkIds(): array
    {
        $this->extractLinks();

        $linkIds = [];

        collect($this->links)->each(function (array $link) use (&$linkIds): void {
            $linkIds[] = LinkModel::query()->firstOrCreate([
                'href' => $this->cleanText($link['href']),
                'text' => $this->cleanText($link['text']),
            ])->id;
        });

        return array_values(array_unique($linkIds));
    }

    private function extractLinks(): void
    {
        collect([
            ImportCommLink::POST_SELECTOR,
            ImportCommLink::SPECIAL_PAGE_SELECTOR,
        ])->each(function (string $selector): void {
            $this->commLink->filter($selector)->filterXPath('//a')->each(function (Crawler $crawler): void {
                $href = $crawler->attr('href');

                if ($href !== null && parse_url($href, PHP_URL_HOST) !== null) {
                    $this->links[] = [
                        'href' => $href,
                        'text' => $crawler->text(),
                    ];
                }
            });

            $this->commLink->filter($selector)->filterXPath('//iframe')->each(function (Crawler $crawler): void {
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
            });
        });
    }
}
