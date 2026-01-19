<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

use Symfony\Component\DomCrawler\Crawler;

interface ContentExtractorInterface
{
    public function __construct(Crawler $page);

    public function getContent(): string;

    public static function getFilter(): string;

    /**
     * @return array{0: bool, 1: int}
     */
    public static function canParse(Crawler $page): array;
}
