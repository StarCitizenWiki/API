<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 11.09.2018
 * Time: 17:31
 */

namespace App\Jobs\Rsi\CommLink\Parser\Element;

use App\Jobs\Rsi\CommLink\Parser\Element\AbstractBaseElement as BaseElement;
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
        $content = $this->removeElements($content);

        // Remove empty p Tags
        $content = preg_replace('/<p><\/p>/', '', $content);

        // Remove Multiline Breaks
        //$content = preg_replace('/^\s+/m', '', $content);

        // Replace multiple Whitespaces with one
        $content = preg_replace('/\s+/S', " ", $content);

        // Remove all Tags except p, br and headings
        $content = trim(strip_tags($content, '<p><br><h1><h2><h3><h4><h5><h6>'));

        // Add New Line to ending heading tags
        $content = preg_replace('/<\/h([1-6])>/', "</h$1>\n", $content);

        // Add New Lines to ending p tags
        $content = str_replace('</p>', "</p>\n\n", $content);

        // Replace multiple br with one
        $content = preg_replace('/(?:<br>\n?){2,}+/m', '<br>', $content);

        // Replace br with new line
        $content = str_replace('<br>', "\n", $content);

        // Remove all tags
        $content = strip_tags($content);

        // Remove Non breaking Spaces with normal space
        $content = str_replace(['&nbsp;', "\xc2\xa0"], ' ', $content);

        // Replace multiple spaces with one
        $content = preg_replace('/\ +/', ' ', $content);

        // Trim each Start of Line
        $content = preg_replace('/^ +/m', '', $content);

        return trim(html_entity_decode($content));
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
        $crawler = $this->removeCommonElements($crawler);

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

        $crawler->filter('img')->each(
            function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            }
        );

        return $crawler;
    }

    /**
     * Remove Common Comm-Link Elements
     * .clearfix, .cboth (clear both), image links, .centerimage, hr, c-slider
     *
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    private function removeCommonElements(Crawler $crawler)
    {
        $crawler->filter('.clearfix')->each(
            function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            }
        );

        $crawler->filter('.cboth')->each(
            function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            }
        );

        $crawler->filter('a.image')->each(
            function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            }
        );

        $crawler->filter('.centerimage')->each(
            function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            }
        );

        $crawler->filter('hr')->each(
            function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            }
        );

        $crawler->filter('c-slider')->each(
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
