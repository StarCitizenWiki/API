<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 14.03.2017
 * Time: 22:56
 */

namespace App\Exceptions;

use Exception;

/**
 * Class ExpiredException
 * Exception to throw if a ShortURL has expired
 *
 * @package App\Exceptions
 */
class ExpiredException extends Exception
{

}
