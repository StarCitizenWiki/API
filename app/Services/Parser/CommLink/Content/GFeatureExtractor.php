<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

use JsonException;
use Symfony\Component\DomCrawler\Crawler;

final class GFeatureExtractor implements ContentExtractorInterface
{
    private Crawler $page;

    public function __construct(Crawler $page)
    {
        $this->page = $page;
    }

    /**
     * {@inheritDoc}
     */
    public function getContent(): string
    {
        $content = $this->getIntroduction();

        $this->page->filterXPath('//g-features')->each(
            function (Crawler $crawler) use (&$content) {
                $this->getGFeaturesIntro($crawler, $content);

                $crawler->filterXPath(self::getFilter())->each(function (Crawler $crawler) use (&$content) {
                    $crawler->filterXPath('//template')->each(function (Crawler $crawler) use (&$content) {
                        $slot = $crawler->attr('slot');
                        switch ($slot) {
                            case 'title':
                                $content .= sprintf('<h2>%s</h2>', $crawler->text());
                                break;

                            case 'body':
                                $content .= $crawler->html();
                                break;

                            default:
                                break;
                        }
                    });
                });
            }
        );

        return $content;
    }

    /**
     * {@inheritDoc}
     */
    public static function getFilter(): string
    {
        return '//g-feature';
    }

    /**
     * {@inheritDoc}
     */
    public static function canParse(Crawler $page): array
    {
        $count = $page->filterXPath(self::getFilter())->count();

        return [
            $count > 0,
            $count,
        ];
    }

    /**
     * Intro above the content
     *
     * @return string
     */
    private function getIntroduction(): string
    {
        $content = '';

        $this->page->filterXPath('//g-introduction')->each(
            function (Crawler $crawler) use (&$content) {
                $info = $crawler->attr(':info');
                if ($info === null) {
                    return;
                }

                try {
                    $info = json_decode($info, true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    return;
                }

                $out = [];
                if (isset($info['title']) && !empty($info['title'])) {
                    $out[] = sprintf('<h1>%s</h1>', $info['title']);
                }
                if (isset($info['subtitle']) && !empty($info['subtitle'])) {
                    $out[] = sprintf('<h2>%s</h2>', $info['subtitle']);
                }
                if (isset($info['contents']) && !empty($info['contents'])) {
                    $out[] = collect($info['contents'])->implode("\n");
                }

                $content .= collect($out)->implode("\n");
            }
        );

        return $content;
    }

    /**
     * <g-features> Intro
     *
     * @param Crawler $crawler
     * @param string $content
     */
    private function getGFeaturesIntro(Crawler $crawler, string &$content): void
    {
        if ($crawler->attr(':is-header-declared') === true) {
            $crawler->filterXPath('//template')->each(function (Crawler $crawler) use (&$content) {
                if ($crawler->attr('slot') === 'title') {
                    $content .= sprintf('<h1>%s</h1>', $crawler->text());
                }
                if ($crawler->attr('slot') === 'subtitle') {
                    $content .= sprintf('<h2>%s</h2>', $crawler->text());
                }
                if ($crawler->attr('slot') === 'introduction') {
                    $content .= $crawler->html();
                }
            });
        }
    }
}
