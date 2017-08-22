<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 14.03.2017
 * Time: 22:56
 */

namespace App\Exceptions;

use Exception;

/**
 * Class HashNameAlreadyAssignedException
 * Exception to throw if a HashName for a ShortUrl is already in use
 *
 * @package App\Exceptions
 */
class HashNameAlreadyAssignedException extends Exception
{

}
