<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Parser\Element;

use App\Jobs\Rsi\CommLink\Parser\Element\AbstractBaseElement as BaseElement;
use Closure;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Comm-Link Content Parser
 */
class Content extends BaseElement
{
    /**
     * @var Crawler
     */
    private Crawler $commLink;

    /**
     * Remove node closure
     *
     * @var Closure
     */
    private Closure $removeNode;

    /**
     * Content constructor.
     *
     * @param Crawler $commLinkDocument
     */
    public function __construct(Crawler $commLinkDocument)
    {
        $this->commLink = $commLinkDocument;

        $this->removeNode = static function (Crawler $crawler) {
            $node = $crawler->getNode(0);
            if ($node !== null) {
                $node->parentNode->removeChild($node);
            }

            return $crawler;
        };
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
        if ($this->isSpecialPage($this->commLink)) {
            $filter = '#layout-system';
        } elseif ($this->isSubscriberPage($this->commLink)) {
            //$filter = '.album-wrapper';

            return '';
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
        $content = $this->removeElements($content);

        // Remove empty p Tags
        $content = preg_replace('/<p><\/p>/m', '', $content);

        // Remove Multiline Breaks
        //$content = preg_replace('/^\s+/m', '', $content);

        // Replace multiple Whitespaces with one
        $content = preg_replace('/\s+/Sm', ' ', $content);

        // Remove all Tags except p, br and headings
        $content = trim(strip_tags($content, '<p><br><h1><h2><h3><h4><h5><h6>'));

        // Add New Line to ending heading tags
        $content = preg_replace('/<\/h([1-6])>/m', "</h$1>\n", $content);

        // Add New Lines to ending p tags
        $content = (string)str_replace('</p>', "</p>\n\n", $content);

        // Replace multiple br with one
        $content = preg_replace("/(?:<br>\n?){2,}+/m", '<br>', $content);

        // Replace br with new line
        $content = (string)str_replace('<br>', "\n", $content);

        // Remove all tags
        $content = strip_tags($content);

        // Remove Non breaking Spaces with normal space
        $content = (string)str_replace(['&nbsp;', "\xc2\xa0"], ' ', $content);

        // Replace multiple spaces with one
        $content = preg_replace('/[ \t]+/m', ' ', $content);

        // Trim each Start of Line
        $content = preg_replace('/^[ \t]+/m', '', $content);

        // Remove Trailing whitespace
        $content = preg_replace('/[ \t]+$/m', '', $content);

        return trim(html_entity_decode($content));
    }

    /**
     * Removes problematic HTML Elements
     *
     * @param string $html
     *
     * @return string
     */
    private function removeElements(string $html): string
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent($html);

        $crawler = $this->removeScriptElements($crawler);
        $crawler = $this->removeStoreSections($crawler);
        $crawler = $this->removeSupElements($crawler);
        $crawler = $this->removeCommentsContainer($crawler);
        $crawler = $this->removeAudioVideoElements($crawler);
        $crawler = $this->removeCommonElements($crawler);

        return $crawler->html();
    }

    /**
     * Removes all script Elements
     *
     * @param Crawler $crawler
     *
     * @return Crawler
     */
    private function removeScriptElements(Crawler $crawler): Crawler
    {
        $crawler->filter('script')->each($this->removeNode);

        return $crawler;
    }

    /**
     * Removes Store Sections from Special Ship Pages
     *
     * @param Crawler $crawler
     *
     * @return Crawler
     */
    private function removeStoreSections(Crawler $crawler): Crawler
    {
        $crawler->filter('section')->each(
            function (Crawler $crawler) {
                if (str_contains($crawler->text(), 'USD')) { //Disgusting
                    $node = $crawler->getNode(0);
                    if ($node !== null) {
                        $node->parentNode->removeChild($node);
                    }
                }
            }
        );

        return $crawler;
    }

    /**
     * Removes Annotation Elements
     *
     * @param Crawler $crawler
     *
     * @return Crawler
     */
    private function removeSupElements(Crawler $crawler): Crawler
    {
        $crawler->filter('sup')->each($this->removeNode);

        return $crawler;
    }

    /**
     * Removes the Comment container
     *
     * @param Crawler $crawler
     *
     * @return Crawler
     */
    private function removeCommentsContainer(Crawler $crawler): Crawler
    {
        $crawler->filter('.wrapper.force-one-column')->each($this->removeNode);

        return $crawler;
    }

    /**
     * Remove Audio/Video Elements
     *
     * @param Crawler $crawler
     *
     * @return Crawler
     */
    private function removeAudioVideoElements(Crawler $crawler): Crawler
    {
        $crawler->filter('audio')->each($this->removeNode);

        $crawler->filter('video')->each($this->removeNode);

        $crawler->filter('img')->each($this->removeNode);

        return $crawler;
    }

    /**
     * Remove Common Comm-Link Elements
     * .clearfix, .cboth (clear both), image links, .centerimage, hr, c-slider
     *
     * @param Crawler $crawler
     *
     * @return Crawler
     */
    private function removeCommonElements(Crawler $crawler): Crawler
    {
        $crawler->filter('.clearfix')->each($this->removeNode);

        $crawler->filter('.cboth')->each($this->removeNode);

        $crawler->filter('a.image')->each($this->removeNode);

        $crawler->filter('.centerimage')->each($this->removeNode);

        $crawler->filter('hr')->each($this->removeNode);

        $crawler->filter('c-slider')->each($this->removeNode);

        return $crawler;
    }
}
