<?php
/**
 * User: Hannes
 * Date: 14.03.2017
 * Time: 22:56
 */

namespace App\Exceptions;

use Exception;

class HashNameAlreadyAssignedException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}