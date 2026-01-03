<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink;

use App\Services\Parser\CommLink\Content\ContentExtractorFactory;
use Closure;
use Symfony\Component\DomCrawler\Crawler;

class Content extends AbstractBaseElement
{
    private Crawler $commLink;

    private Closure $removeNode;

    public function __construct(Crawler $commLinkDocument)
    {
        $this->commLink = $commLinkDocument;

        $this->removeNode = static function (Crawler $crawler): Crawler {
            $node = $crawler->getNode(0);
            if ($node !== null) {
                $node->parentNode?->removeChild($node);
            }

            return $crawler;
        };
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getContent(): string
    {
        $content = ContentExtractorFactory::getParserFromCrawler($this->commLink)?->getContent();

        return empty($content) ? '' : $this->cleanContent($content);
    }

    private function cleanContent(string $content): string
    {
        $content = $this->removeElements($content);

        $content = str_replace(' ', ' ', $content);
        $content = (string) str_replace(['&nbsp;', "\xc2\xa0"], ' ', $content);

        $content = preg_replace('/<p>\s*?<\/p>/m', '', $content) ?? $content;

        $content = preg_replace('/\s+/Sm', ' ', $content) ?? $content;

        $content = trim(strip_tags($content, '<p><li><br><h1><h2><h3><h4><h5><h6>'));

        $content = preg_replace('/<\/h([1-6])>/m', "</h$1>\n", $content) ?? $content;

        $content = (string) str_replace('</p>', "</p>\n\n", $content);

        $content = preg_replace("/(?:<br>\n?){2,}+/m", '<br>', $content) ?? $content;

        $content = (string) str_replace('<br>', "\n", $content);

        $content = (string) str_replace('</li>', "</li>\n\n", $content);

        $content = strip_tags($content);

        $content = preg_replace('/[ \t]+/m', ' ', $content) ?? $content;

        $content = preg_replace('/^[ \t]+/m', '', $content) ?? $content;

        $content = preg_replace('/[ \t]+$/m', '', $content) ?? $content;

        return trim(html_entity_decode($content));
    }

    private function removeElements(string $html): string
    {
        $crawler = new Crawler;
        $crawler->addHtmlContent($html);

        $crawler = $this->removeScriptStyleElements($crawler);
        $crawler = $this->removeStoreSections($crawler);
        $crawler = $this->removeSupElements($crawler);
        $crawler = $this->removeCommentsContainer($crawler);
        $crawler = $this->removeAudioVideoElements($crawler);
        $crawler = $this->removeCommonElements($crawler);

        return $crawler->html() ?? '';
    }

    private function removeScriptStyleElements(Crawler $crawler): Crawler
    {
        $remover = $this->removeNode;

        $crawler->filter('script')->each($this->removeNode);
        $crawler->filter('style')->each($this->removeNode);

        $crawler->filter('component')->each(function (Crawler $crawler) use ($remover): void {
            if ($crawler->attr('is') === 'script') {
                $remover($crawler);
            }
        });

        return $crawler;
    }

    private function removeStoreSections(Crawler $crawler): Crawler
    {
        $crawler->filter('section')->each(function (Crawler $crawler): void {
            if (str_contains($crawler->text(), 'USD')) {
                $node = $crawler->getNode(0);
                if ($node !== null) {
                    $node->parentNode?->removeChild($node);
                }
            }
        });

        return $crawler;
    }

    private function removeSupElements(Crawler $crawler): Crawler
    {
        $crawler->filter('sup')->each($this->removeNode);

        return $crawler;
    }

    private function removeCommentsContainer(Crawler $crawler): Crawler
    {
        $crawler->filter('.wrapper.force-one-column')->each($this->removeNode);

        return $crawler;
    }

    private function removeAudioVideoElements(Crawler $crawler): Crawler
    {
        $crawler->filter('audio')->each($this->removeNode);
        $crawler->filter('video')->each($this->removeNode);
        $crawler->filter('img')->each($this->removeNode);

        return $crawler;
    }

    private function removeCommonElements(Crawler $crawler): Crawler
    {
        $crawler->filter('.clearfix')->each($this->removeNode);
        $crawler->filter('.cboth')->each($this->removeNode);
        $crawler->filter('.centerimage')->each($this->removeNode);
        $crawler->filter('hr')->each($this->removeNode);
        $crawler->filter('c-slider')->each($this->removeNode);

        return $crawler;
    }
}
