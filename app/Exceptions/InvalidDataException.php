<?php
/**
 * User: Hannes
 * Date: 29.01.2017
 * Time: 13:20
 */

namespace App\Exceptions;

use Exception;

class InvalidDataException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}