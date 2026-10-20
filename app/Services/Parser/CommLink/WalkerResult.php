<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink;

final readonly class WalkerResult
{
    /**
     * @param  list<string>  $textBlocks
     * @param  list<string>  $images
     * @param  list<string>  $urls
     */
    public function __construct(
        public array $textBlocks = [],
        public array $images = [],
        public array $urls = [],
    ) {}
}
