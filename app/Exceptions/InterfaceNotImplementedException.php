<?php
/**
 * User: Hannes
 * Date: 13.03.2017
 * Time: 20:13
 */

namespace App\Exceptions;

use BadMethodCallException;
use Exception;

/**
 * Class InterfaceNotImplementedException
 * Extension to throw if an required Interface is not implemented
 *
 * @package App\Exceptions
 */
class InterfaceNotImplementedException extends BadMethodCallException
{
    /**
     * InterfaceNotImplementedException constructor.
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
