<?php

namespace App\Services\ImageHash\Implementations\PDQHash;

class MalformedPDQHashException extends \Exception
{
    public function __construct(string $hexstring, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
