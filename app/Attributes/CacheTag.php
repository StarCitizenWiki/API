<?php

declare(strict_types=1);

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class CacheTag
{
    /**
     * @param  string[]  $tags
     */
    public readonly array $tags;

    public function __construct(
        string ...$tags,
    ) {
        $this->tags = $tags;
    }
}
