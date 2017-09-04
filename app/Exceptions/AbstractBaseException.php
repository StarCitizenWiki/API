<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 04.09.2017
 * Time: 11:47
 */

namespace App\Exceptions;

use App\Traits\ChecksIfRequestWantsJsonTrait as ChecksIfRequestWantsJson;
use Exception;

/**
 * Class BaseException
 * @package App\Exceptions
 */
abstract class AbstractBaseException extends Exception
{
    use ChecksIfRequestWantsJson;

    public const DEFAULT_MESSAGE = '';

    protected function prepareMessage()
    {
        if (empty($this->message)) {
            $this->message = static::DEFAULT_MESSAGE;
        }
    }
}
