<?php
/**
 * User: Hannes
 * Date: 21.02.2017
 * Time: 11:35
 */

namespace App\Exceptions;

use Exception;

/**
 * Class UserBlacklistedException
 * Exception to throw if a request is made by an blacklisted user
 *
 * @package App\Exceptions
 */
class UserBlacklistedException extends Exception
{
    /**
     * UserBlacklistedException constructor.
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