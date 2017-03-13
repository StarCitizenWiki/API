<?php
/**
 * User: Hannes
 * Date: 13.03.2017
 * Time: 20:13
 */

namespace App\Exceptions;

use BadMethodCallException;
use Exception;

class InterfaceNotImplementedException extends BadMethodCallException
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}