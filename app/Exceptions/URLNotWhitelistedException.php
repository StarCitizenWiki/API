<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 15.03.2017
 * Time: 09:30
 */

namespace App\Exceptions;

use Exception;

/**
 * Class URLNotWhitelistedException
 * Exception to throw if a not whitelisted url should get shortened
 *
 * @package App\Exceptions
 */
class URLNotWhitelistedException extends Exception
{
    /**
     * URLNotWhitelistedException constructor.
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