<?php

declare(strict_types=1);

namespace App\Services\ImageHash;

use InvalidArgumentException;

final class MalformedPdqHashException extends InvalidArgumentException
{
    public function __construct(string $hash, string $message)
    {
        parent::__construct(sprintf('Malformed PDQ hash "%s": %s', $hash, $message));
    }
}
