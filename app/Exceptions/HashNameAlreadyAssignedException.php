<?php
/**
 * User: Hannes
 * Date: 14.03.2017
 * Time: 22:56
 */

namespace App\Exceptions;

use Exception;

/**
 * Class HashNameAlreadyAssignedException
 * Exception to throw if a HashName for a ShortURL is already in use
 *
 * @package App\Exceptions
 */
class HashNameAlreadyAssignedException extends Exception
{
    /**
     * HashNameAlreadyAssignedException constructor.
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