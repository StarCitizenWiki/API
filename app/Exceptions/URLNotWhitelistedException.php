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

}
