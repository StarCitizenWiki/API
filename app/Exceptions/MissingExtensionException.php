<?php
/**
 * User: Hannes
 * Date: 28.01.2017
 * Time: 23:14
 */

namespace App\Exceptions;

use Exception;

/**
 * Class MissingExtensionException
 * Exception to throw if a required PHP Extension is missing
 *
 * @package App\Exceptions
 */
class MissingExtensionException extends Exception
{
    /**
     * MissingExtensionException constructor.
     *
     * @param string         $message  Message
     * @param int            $code     Code
     * @param Exception|null $previous Exception
     */
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
