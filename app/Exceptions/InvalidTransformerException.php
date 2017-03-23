<?php
/**
 * User: Hannes
 * Date: 23.03.2017
 * Time: 15:09
 */

namespace App\Exceptions;

use Exception;

class InvalidTransformerException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}