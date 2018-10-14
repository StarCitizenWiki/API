<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 11.09.2018
 * Time: 17:31
 */

namespace App\Jobs\Rsi\CommLink\Parser\Element;

use App\Jobs\Rsi\CommLink\Parser\Element\AbstractBaseElement as BaseElement;
use DOMDocument;
use DOMXPath;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Comm-Link Content Parser
 */
class Content extends BaseElement
{
    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    private $commLink;

    /**
     * Content constructor.
     *
     * @param \Symfony\Component\DomCrawler\Crawler $commLinkDocument
     */
    public function __construct(Crawler $commLinkDocument)
    {
        $this->commLink = $commLinkDocument;
    }

    /**
     * Tries to extract the Comm-Link Content as Text
     *
     * @param string $filter Content Element class/id
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getContent(string $filter = '.segment'): string
    {
        if ($this->isSpecialPage()) {
            $filter = '#layout-system';
        }

        $content = '';

        $this->commLink->filter($filter)->each(
            function (Crawler $crawler) use (&$content) {
                $content .= ltrim($crawler->html());
            }
        );

        return empty($content) ? '' : $this->cleanContent($content);
    }

    /**
     * Removes some Tags, converts newlines to br
     *
     * @param string $content
     *
     * @return string
     */
    private function cleanContent(string $content): string
    {
        $content = preg_replace('/^[\s\r\n]+/m', '', $content);
        //$content = $this->stripTagAttributes($content);
        $content = $this->removeElements($content);

        $content = preg_replace('/\s+/S', " ", $content);
        $content = trim(strip_tags($content, '<p><br><h1><h2><h3><h4><h5><h6>'));
        $content = preg_replace('/<\/h([1-6])>/', "</h$1>\n\n", $content);
        $content = preg_replace('/<p><\/p>/', '', $content);
        $content = str_replace('</p>', "</p>\n\n", $content);
        $content = preg_replace('/(?:<br>\n?){2,}+/m', '<br>', $content);
        $content = str_replace('<br>', "\n", $content);

        $content = strip_tags($content);
        $content = preg_replace('/^ +/m', '', $content);
        $content = str_replace(['&nbsp;', "\xc2\xa0"], '', $content);

        return trim($content);
    }

    /**
     * Removes all Attributes from Tags, so only pure tags are left
     *
     * @param string $html
     *
     * @return string Cleaned html
     */
    private function stripTagAttributes(string $html): string
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;

        libxml_use_internal_errors(true);
        try {
            $success = $dom->loadHTML(
                mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'),
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );
        } catch (\ErrorException $e) {
            return '';
        }

        libxml_use_internal_errors(false);

        if (!$success) {
            return '';
        }

        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//@*');
        foreach ($nodes as $node) {
            $node->parentNode->removeAttribute($node->nodeName);
        }

        return $dom->saveHTML();
    }

    /**
     * Removes problematic HTML Elements
     *
     * @param string $html
     *
     * @return string
     */
    private function removeElements(string $html)
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent($html);

        $crawler = $this->removeScriptElements($crawler);
        $crawler = $this->removeStoreSections($crawler);
        $crawler = $this->removeSupElements($crawler);
        $crawler = $this->removeCommentsContainer($crawler);
        $crawler = $this->removeAudioVideoElements($crawler);

        return $crawler->html();
    }

    /**
     * Removes all script Elements
     *
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    private function removeScriptElements(Crawler $crawler)
    {
        $crawler->filter('script')->each(
            function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            }
        );

        return $crawler;
    }

    /**
     * Removes Store Sections from Special Ship Pages
     *
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    private function removeStoreSections(Crawler $crawler)
    {
        $crawler->filter('section')->each(
            function (Crawler $crawler) {
                if (str_contains($crawler->text(), 'USD')) { //Disgusting
                    $node = $crawler->getNode(0);
                    $node->parentNode->removeChild($node);
                }
            }
        );

        return $crawler;
    }

    /**
     * Removes Annotation Elements
     *
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    private function removeSupElements(Crawler $crawler)
    {
        $crawler->filter('sup')->each(
            function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            }
        );

        return $crawler;
    }

    /**
     * Removes the Comment container
     *
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    private function removeCommentsContainer(Crawler $crawler)
    {
        $crawler->filter('.wrapper.force-one-column')->each(
            function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            }
        );

        return $crawler;
    }

    /**
     * Remove Audio/Video Elements
     *
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    private function removeAudioVideoElements(Crawler $crawler)
    {
        $crawler->filter('audio')->each(
            function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            }
        );

        $crawler->filter('video')->each(
            function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            }
        );

        return $crawler;
    }

    /**
     * Checks if Comm-Link Page is a Ship Page
     * Ship Pages are wrapped in a '#layout-system' Div
     *
     * @return bool
     */
    private function isSpecialPage(): bool
    {
        return $this->commLink->filter('#layout-system')->count() === 1;
    }
}
