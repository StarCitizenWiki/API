<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 15.03.2017
 * Time: 09:30
 */

namespace App\Exceptions;

use Exception;

class URLNotWhitelistedException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}