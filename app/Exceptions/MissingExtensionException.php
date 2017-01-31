<?php
/**
 * User: Hannes
 * Date: 28.01.2017
 * Time: 23:14
 */

namespace App\Exceptions;

use Exception;

class MissingExtensionException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}