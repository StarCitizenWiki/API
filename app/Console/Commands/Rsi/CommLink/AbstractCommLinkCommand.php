<?php

declare(strict_types=1);

namespace App\Console\Commands\Rsi\CommLink;

use App\Console\Commands\AbstractQueueCommand;
use InvalidArgumentException;

abstract class AbstractCommLinkCommand extends AbstractQueueCommand
{
    public const FIRST_COMM_LINK_ID = 12663;

    /**
     * If command has no 'offset' argument, 12663 will be returned
     * If the offset is greater than 0 but lower than 12663, the offset will be added to the first id
     *
     * @return int The parsed offset
     */
    protected function parseOffset(): int
    {
        $offset = (int)$this->argument('offset');

        if ($offset <= 0) {
            return self::FIRST_COMM_LINK_ID;
        }

        if ($offset > 0 && $offset < self::FIRST_COMM_LINK_ID) {
            $offset = self::FIRST_COMM_LINK_ID + $offset;
        }

        return $offset;
    }
}
