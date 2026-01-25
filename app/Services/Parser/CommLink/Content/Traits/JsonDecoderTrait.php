<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use JsonException;
use Symfony\Component\DomCrawler\Crawler;

trait JsonDecoderTrait
{
    /**
     * Decodes a JSON attribute from a crawler.
     */
    public function decodeJsonAttribute(Crawler $crawler, string $attribute): ?array
    {
        $content = $crawler->attr($attribute);

        if ($content === null) {
            return null;
        }

        return $this->decodeJsonString($content);
    }

    /**
     * Decodes a JSON string.
     */
    public function decodeJsonString(string $content): ?array
    {
        try {
            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }
    }
}
