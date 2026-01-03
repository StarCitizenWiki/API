<?php

declare(strict_types=1);

namespace App\Services\ImageHash;

final class PdqHashResult
{
    public function __construct(
        public readonly PdqHash $hash,
        public readonly int $quality
    ) {}

    public function toBitString(): string
    {
        return $this->hash->toBitString();
    }
}
