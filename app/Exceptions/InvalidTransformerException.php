<?php
/**
 * User: Hannes
 * Date: 23.03.2017
 * Time: 15:09
 */

namespace App\Exceptions;

use Exception;

/**
 * Class InvalidTransformerException
 * Exception to throw if a Repository should transform data
 * with an invalid Transformer (mostly thrown if transformer is null)
 *
 * @package App\Exceptions
 */
class InvalidTransformerException extends Exception
{
    /**
     * InvalidTransformerException constructor.
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
