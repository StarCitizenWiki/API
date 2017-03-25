<?php
/**
 * User: Hannes
 * Date: 29.01.2017
 * Time: 13:20
 */

namespace App\Exceptions;

use Exception;

/**
 * Class InvalidDataException
 * General Exception to throw if Data is invalid
 *
 * @package App\Exceptions
 */
class InvalidDataException extends Exception
{
    /**
     * InvalidDataException constructor.
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
