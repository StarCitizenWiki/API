<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 07.03.2017
 * Time: 11:54
 */

namespace App\Exceptions;

use Exception;

/**
 * Class MethodNotImplementedException
 * Exception to throw if a called Method is not implemented or not overridden
 *
 * @package App\Exceptions
 */
class MethodNotImplementedException extends \Exception
{
    /**
     * MethodNotImplementedException constructor.
     *
     * @param string         $message  Message
     * @param int            $code     Code
     * @param Exception|null $previous Exception
     */
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}