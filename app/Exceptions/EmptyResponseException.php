<?php
/**
 * User: Hannes
 * Date: 24.01.2017
 * Time: 22:19
 */

namespace App\Exceptions;


use Exception;

class EmptyResponseException extends \Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}