<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hannes
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
