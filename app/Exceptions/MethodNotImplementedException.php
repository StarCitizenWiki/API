<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hannes
 * Date: 07.03.2017
 * Time: 11:54
 */

namespace App\Exceptions;

use Exception;

/**
 * Class MethodNotImplementedException
 * Exception to throw if a called Method is not implemented or not overridden
 */
class MethodNotImplementedException extends Exception
{

}
