<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 21.02.2017
 * Time: 11:35
 */

namespace App\Exceptions;

use Exception;

/**
 * Class UserBlacklistedException
 * Exception to throw if a request is made by an blacklisted user
 *
 * @package App\Exceptions
 */
class UserBlacklistedException extends Exception
{

}
