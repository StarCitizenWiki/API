<?php
/**
 * User: Hannes
 * Date: 21.02.2017
 * Time: 11:35
 */

namespace App\Exceptions;

use Exception;

class UserBlacklistedException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}