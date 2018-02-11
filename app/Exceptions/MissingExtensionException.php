<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 28.01.2017
 * Time: 23:14
 */

namespace App\Exceptions;

use Exception;

/**
 * Class MissingExtensionException
 * Exception to throw if a required PHP Extension is missing
 */
class MissingExtensionException extends Exception
{

}
