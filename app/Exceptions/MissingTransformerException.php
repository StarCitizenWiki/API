<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 08.03.2017
 * Time: 13:46
 */

namespace App\Exceptions;

use Exception;

/**
 * Class MissingTransformerException
 * Exteption to throw if a transformer is not set but used
 *
 * @package App\Exceptions
 */
class MissingTransformerException extends Exception
{
    /**
     * MissingTransformerException constructor.
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
