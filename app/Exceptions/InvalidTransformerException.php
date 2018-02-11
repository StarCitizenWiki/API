<?php declare(strict_types = 1);
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
 */
class InvalidTransformerException extends Exception
{

}
