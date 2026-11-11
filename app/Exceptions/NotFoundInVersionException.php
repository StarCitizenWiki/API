<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class NotFoundInVersionException extends NotFoundHttpException
{
    /**
     * @param  string  $requestedVersion  The version code the client requested (raw param, or the default code when omitted)
     * @param  list<string>  $availableVersions
     */
    public function __construct(
        public readonly string $requestedVersion,
        public readonly array $availableVersions,
        string $message,
    ) {
        parent::__construct($message);
    }
}
