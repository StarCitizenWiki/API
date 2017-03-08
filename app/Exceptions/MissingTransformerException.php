<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 08.03.2017
 * Time: 13:46
 */

namespace App\Exceptions;

use Exception;

class MissingTransformerException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}