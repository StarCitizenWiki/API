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
        $content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);
        $content = strip_tags($content, '<p><br><table><tbody><td><tfoot><th><thead><tr>');
        $content = $this->stripTagAttributes($content);
        $content = trim(
            preg_replace(
                ['/\R+/', '/[\ |\t]+/'],
                ["\n", ' '],
                $content
            )
        );
        $content = nl2br($content, false);
        $content = preg_replace('/\<p\>(?:(?:\&nbsp\;|\ | )*(?:<br\s*\/?>)*\s*)?\<\/p\>/i', '', $content);
        $content = preg_replace('/(?:\<br>\s?)+/i', '<br>', $content);
        $content = preg_replace('/^\s*(?:<br\s*\/?>\s*)*/is', '', $content);

        return preg_replace('/\s*(?:<br\s*\/?>\s*)*$/is', '', $content);
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

        libxml_use_internal_errors(true);
        $success = $dom->loadHTML(
            mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
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
