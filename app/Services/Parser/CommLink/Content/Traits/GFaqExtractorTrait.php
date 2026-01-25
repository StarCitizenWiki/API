<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use Symfony\Component\DomCrawler\Crawler;

trait GFaqExtractorTrait
{
    use JsonDecoderTrait;

    public function getFaq(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-faq')->each(function (Crawler $crawler) use (&$content): void {
            $questions = $this->decodeJsonAttribute($crawler, ':question-list');
            if ($questions === null) {
                return;
            }

            if (! is_array($questions)) {
                return;
            }

            $out = [];
            $index = 1;

            foreach ($questions as $question) {
                $title = $question['title'] ?? null;
                $questionContent = $question['content'] ?? null;

                if ($title === null || $questionContent === null) {
                    continue;
                }

                $out[] = sprintf('<h3>%d. %s</h3>', $index, $title);
                $out[] = $questionContent;
                $index++;
            }

            $content .= collect($out)->implode("\n");
        });

        return $content;
    }
}
