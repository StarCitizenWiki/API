<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 07.03.2017
 * Time: 11:54
 */

namespace App\Exceptions;

use Exception;

class MethodNotImplementedException extends \Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}