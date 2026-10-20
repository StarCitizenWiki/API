<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink;

use App\Jobs\Rsi\CommLink\Import\ImportCommLink;
use Symfony\Component\DomCrawler\Crawler;

final class ContentExtractor
{
    public function __construct(private readonly Crawler $crawler) {}

    public function getContent(): ?string
    {
        if ($this->crawler->filterXPath('//*[starts-with(local-name(), "g-")]')->count() > 0) {
            $blocks = (new JsonWalker)->collect($this->crawler)->textBlocks;

            return $blocks === [] ? null : implode("\n\n", $blocks);
        }

        return $this->classicContent();
    }

    private function classicContent(): ?string
    {
        $content = $this->introductionContent();

        if ($this->crawler->filter(ImportCommLink::SPECIAL_PAGE_SELECTOR)->count() > 0) {
            $this->crawler->filter(ImportCommLink::SPECIAL_PAGE_SELECTOR)->each(function (Crawler $node) use (&$content): void {
                $content .= ltrim($node->html() ?? '');
            });

            return trim($content) === '' ? null : $content;
        }

        $this->crawler->filter('.segment')->each(function (Crawler $node) use (&$content): void {
            $content .= ltrim($node->html() ?? '');
        });

        $this->crawler->filter(ImportCommLink::SUBSCRIBERS_SELECTOR.' .album-wrapper')->each(function (Crawler $node) use (&$content): void {
            $content .= ltrim($node->html() ?? '');
        });

        return trim($content) === '' ? null : $content;
    }

    private function introductionContent(): string
    {
        $content = '';

        $this->crawler->filterXPath('//g-introduction')->each(function (Crawler $node) use (&$content): void {
            $info = json_decode((string) $node->attr(':info'), true, 512);

            if (! is_array($info)) {
                return;
            }

            $out = [];

            if (is_string($info['title'] ?? null) && $info['title'] !== '') {
                $out[] = sprintf('<h1>%s</h1>', $info['title']);
            }

            if (is_string($info['subtitle'] ?? null) && $info['subtitle'] !== '') {
                $out[] = sprintf('<h2>%s</h2>', $info['subtitle']);
            }

            if (is_array($info['contents'] ?? null)) {
                $out[] = implode("\n", array_filter($info['contents'], is_string(...)));
            }

            $content .= implode("\n", $out);
        });

        return $content;
    }
}
